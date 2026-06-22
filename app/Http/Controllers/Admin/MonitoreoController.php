<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SystemMonitorService;
use App\Models\SessionHistory;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MonitoreoController extends Controller
{
    protected $monitorService;

    public function __construct(SystemMonitorService $monitorService)
    {
        $this->monitorService = $monitorService;
    }

    public function getDatabaseMetrics()
    {
        return response()->json([
            'success' => true,
            'data' => $this->monitorService->getDatabaseMetrics(),
        ]);
    }

    public function getServerMetrics()
    {
        return response()->json([
            'success' => true,
            'data' => $this->monitorService->getServerMetrics(),
        ]);
    }

    public function getSessions(Request $request)
    {
        $query = SessionHistory::with(['user:id,name,email', 'empresa:id,razon_social', 'sucursal:id,nombre']);

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('empresa_id') && $request->empresa_id) {
            $query->where('empresa_id', $request->empresa_id);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('ip_address', 'like', "%{$search}%");
        }

        $sessions = $query->latest('login_at')->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $sessions,
        ]);
    }

    public function getAuditoria(Request $request)
    {
        $query = Activity::query();

        // Si quisieras cargar relaciones como el causante (causer) o el sujeto (subject),
        // podrías hacerlo, pero activitylog maneja las properties internamente.

        // Search in description or properties
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('description', 'like', "%{$search}%")
                  ->orWhere('properties', 'like', "%{$search}%");
        }

        if ($request->has('event') && $request->event) {
            $query->where('event', $request->event);
        }

        $logs = $query->latest('created_at')->paginate($request->per_page ?? 15);

        // Modificar para asegurarse que las propiedades se envíen bien estructuradas
        $logs->getCollection()->transform(function ($log) {
            return [
                'id' => $log->id,
                'log_name' => $log->log_name,
                'description' => $log->description,
                'event' => $log->event,
                'subject_type' => $log->subject_type,
                'subject_id' => $log->subject_id,
                'causer_type' => $log->causer_type,
                'causer_id' => $log->causer_id,
                'properties' => $log->properties,
                'created_at' => $log->created_at,
                'updated_at' => $log->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    public function exportDatabase(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            return response()->json(['success' => false, 'message' => 'Contraseña incorrecta'], 403);
        }

        $includeStructure = filter_var($request->input('include_structure', true), FILTER_VALIDATE_BOOLEAN);
        $includeData = filter_var($request->input('include_data', true), FILTER_VALIDATE_BOOLEAN);
        $dropTable = filter_var($request->input('drop_table', false), FILTER_VALIDATE_BOOLEAN);
        $ifNotExists = filter_var($request->input('if_not_exists', false), FILTER_VALIDATE_BOOLEAN);
        $compress = filter_var($request->input('compress', false), FILTER_VALIDATE_BOOLEAN);

        try {
            $databaseName = DB::getDatabaseName();
            $fileName = 'backup_' . str_replace('_', '-', $databaseName) . '_' . date('Y-m-d_His');

            $sqlContent = $this->generateCompleteSQLDump($includeStructure, $includeData, $dropTable, $ifNotExists);

            if ($compress) {
                $fileName .= '.sql.gz';
                $sqlContent = gzencode($sqlContent);
            } else {
                $fileName .= '.sql';
            }

            $filePath = storage_path('app/' . $fileName);
            file_put_contents($filePath, $sqlContent);

            activity()
                ->causedBy(auth()->user())
                ->log('Exportación de base de datos: ' . $fileName);

            return response()->download($filePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Database export error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al exportar la base de datos: ' . $e->getMessage()], 500);
        }
    }

    private function generateCompleteSQLDump($includeStructure, $includeData, $dropTable, $ifNotExists)
    {
        $output = [];
        $output[] = "-- ============================================";
        $output[] = "-- Exportación de Base de Datos Completa";
        $output[] = "-- ============================================";
        $output[] = "-- Fecha: " . date('Y-m-d H:i:s');
        $output[] = "-- Usuario: " . (auth()->check() ? auth()->user()->name : 'Sistema');
        $output[] = "-- Base de datos: " . DB::getDatabaseName();
        $output[] = "-- ============================================";
        $output[] = "";
        $output[] = "SET FOREIGN_KEY_CHECKS = 0;";
        $output[] = "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';";
        $output[] = "START TRANSACTION;";
        $output[] = "SET time_zone = '+00:00';";
        $output[] = "";

        $tables = DB::select('SHOW TABLES');
        $key = 'Tables_in_' . DB::getDatabaseName();

        foreach ($tables as $table) {
            $tableName = $table->$key;
            $output = array_merge($output, $this->getTableSQL($tableName, $includeStructure, $includeData, $dropTable, $ifNotExists));
        }

        $output[] = "";
        $output[] = "COMMIT;";
        $output[] = "";
        $output[] = "SET FOREIGN_KEY_CHECKS = 1;";

        return implode("\n", $output);
    }

    private function getTableSQL($tableName, $includeStructure, $includeData, $dropTable, $ifNotExists)
    {
        $output = [];
        try {
            if ($includeStructure) {
                $output[] = "--";
                $output[] = "-- Estructura de tabla para `{$tableName}`";
                $output[] = "--";

                if ($dropTable) {
                    $output[] = "DROP TABLE IF EXISTS `{$tableName}`;";
                }

                $createTable = DB::selectOne("SHOW CREATE TABLE `{$tableName}`");
                $createStatement = $createTable->{'Create Table'};

                if ($ifNotExists) {
                    $createStatement = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $createStatement);
                }

                $output[] = $createStatement . ";";
                $output[] = "";
            }

            if ($includeData) {
                $output[] = "--";
                $output[] = "-- Volcado de datos para la tabla `{$tableName}`";
                $output[] = "--";

                $records = DB::table($tableName)->get();

                if ($records->isNotEmpty()) {
                    foreach ($records as $record) {
                        $data = (array) $record;
                        $columns = array_keys($data);
                        $values = array_map(function($value) {
                            if ($value === null) {
                                return 'NULL';
                            } elseif (is_numeric($value)) {
                                return $value;
                            } elseif (is_bool($value)) {
                                return $value ? 1 : 0;
                            } else {
                                return "'" . addslashes($value) . "'";
                            }
                        }, array_values($data));

                        $sql = "INSERT INTO `{$tableName}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");";
                        $output[] = $sql;
                    }
                }
                $output[] = "";
            }
        } catch (\Exception $e) {
            $output[] = "-- Error al procesar la tabla {$tableName}: " . $e->getMessage();
            $output[] = "";
        }

        return $output;
    }

    public function importDatabase(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'file' => 'required|file|mimes:sql,gz,txt|max:102400',
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            return response()->json(['success' => false, 'message' => 'Contraseña incorrecta'], 403);
        }

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            DB::statement('SET UNIQUE_CHECKS = 0');
            DB::statement('SET AUTOCOMMIT = 0');

            $content = file_get_contents($file->getRealPath());

            if ($file->getClientOriginalExtension() === 'gz' || $file->getClientMimeType() === 'application/gzip') {
                $content = gzdecode($content);
            }

            $statements = $this->parseSQLStatements($content);
            $executed = 0;
            $errors = [];

            foreach ($statements as $index => $statement) {
                if (empty(trim($statement))) continue;

                try {
                    DB::statement($statement);
                    $executed++;
                } catch (\Exception $e) {
                    $errors[] = "Statement {$index}: " . $e->getMessage();
                }
            }

            DB::statement('COMMIT');
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            DB::statement('SET UNIQUE_CHECKS = 1');
            DB::statement('SET AUTOCOMMIT = 1');

            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'file' => $fileName,
                    'statements_executed' => $executed,
                    'errors_count' => count($errors)
                ])
                ->log('Importación de base de datos: ' . $fileName);

            return response()->json([
                'success' => true, 
                'message' => "Importación exitosa: {$executed} sentencias ejecutadas.",
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
                DB::statement('SET UNIQUE_CHECKS = 1');
                DB::statement('SET AUTOCOMMIT = 1');
            } catch (\Exception $cleanupError) {
                Log::error('Error cleaning up import settings: ' . $cleanupError->getMessage());
            }

            return response()->json(['success' => false, 'message' => 'Error en importación: ' . $e->getMessage()], 500);
        }
    }

    private function parseSQLStatements($content)
    {
        $statements = [];
        $current = '';
        $delimiter = ';';
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            if (strpos($line, '--') === 0 || strpos($line, '/*') === 0) continue;
            if (stripos($line, 'DELIMITER') === 0) {
                $delimiter = trim(str_ireplace('DELIMITER', '', $line));
                continue;
            }

            $current .= $line . "\n";
            if (substr(trim($line), -strlen($delimiter)) === $delimiter) {
                $statement = trim(str_replace($delimiter, '', $current));
                if (!empty($statement)) {
                    $statements[] = $statement;
                }
                $current = '';
            }
        }

        return $statements;
    }
}
