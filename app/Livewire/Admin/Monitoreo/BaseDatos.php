<?php

namespace App\Livewire\Admin\Monitoreo;

use App\Models\Backup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
#[Title('Base de Datos')]
class BaseDatos extends Component
{
    use WithFileUploads;

    // Tab and wizard state
    public string $activeTab = 'export';
    public int $exportStep = 1;
    public int $importStep = 1;
    public string $password = '';
    public $uploadedFile = null;
    public string $importFileName = '';
    public int $importFileSize = 0;
    public array $importValidationResults = [];
    public int $importProgress = 0;
    public int $totalTables = 0;
    public string $estimatedFileSize = '';
    public bool $showPasswordModal = false;
    public string $pendingAction = '';
    public bool $isImporting = false;

    // Export progress
    public int $exportProgress = 0;
    public bool $isExporting = false;
    public string $exportFileName = '';

    // Messages
    public string $successMessage = '';
    public string $errorMessage = '';
    public string $downloadUrl = '';

    // Export options
    public array $exportOptions = [
        'include_structure' => true,
        'include_data' => true,
        'add_drop_table' => true,
        'add_if_not_exists' => true,
        'compress' => false,
    ];

    // Import options
    public array $importOptions = [
        'drop_existing' => false,
        'ignore_errors' => false,
        'skip_foreign_key_checks' => true,
    ];

    // Available tables
    public array $availableTables = [];

    // Recent backups
    public array $recentBackups = [];

    protected $rules = [
        'password' => 'required',
        'uploadedFile' => 'nullable|file|mimes:sql,gz|max:102400',
    ];

    public function mount(): void
    {
        $this->loadAvailableTables();
        $this->calculateEstimatedSize();
        $this->loadRecentBackups();
    }

    // ==================== TAB NAVIGATION ====================

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetExport();
        $this->resetImport();
    }

    // ==================== WIZARD NAVIGATION ====================

    public function nextExportStep(): void
    {
        if ($this->exportStep < 3) {
            $this->exportStep++;
        }
    }

    public function previousExportStep(): void
    {
        if ($this->exportStep > 1) {
            $this->exportStep--;
        }
    }

    public function resetExport(): void
    {
        $this->exportStep = 1;
        $this->exportProgress = 0;
        $this->isExporting = false;
        $this->exportFileName = '';
    }

    public function nextImportStep(): void
    {
        if ($this->importStep < 3) {
            $this->importStep++;
        }
    }

    public function previousImportStep(): void
    {
        if ($this->importStep > 1) {
            $this->importStep--;
        }
    }

    public function resetImport(): void
    {
        $this->importStep = 1;
        $this->uploadedFile = null;
        $this->importValidationResults = [];
        $this->importProgress = 0;
        $this->isImporting = false;
        $this->importFileName = '';
        $this->importFileSize = 0;
    }

    // ==================== PASSWORD VERIFICATION ====================

    public function requestPasswordVerification(string $action): void
    {
        $this->pendingAction = $action;
        $this->password = '';
        $this->showPasswordModal = true;
        $this->dispatch('show-password-modal');
    }

    public function verifyPassword(): bool
    {
        $this->validate([
            'password' => 'required',
        ]);

        if (! Hash::check($this->password, auth()->user()->password)) {
            $this->addError('password', 'La contraseña es incorrecta');

            return false;
        }

        // Close modal FIRST before executing action
        $this->showPasswordModal = false;
        $this->password = '';
        $this->dispatch('hide-password-modal');

        if ($this->pendingAction === 'export') {
            try {
                $this->executeExport();
            } catch (\Exception $e) {
                $this->errorMessage = 'Error al exportar: '.$e->getMessage();
                Log::error('Database export error: '.$e->getMessage());
            }
        } elseif ($this->pendingAction === 'import') {
            try {
                $this->executeImport();
            } catch (\Exception $e) {
                $this->errorMessage = 'Error en importación: '.$e->getMessage();
                Log::error('Database import error: '.$e->getMessage());
            }
        }

        return true;
    }

    // ==================== EXPORT FUNCTIONS ====================

    public function executeExport(): void
    {
        $this->exportStep = 4;
        $this->isExporting = true;
        $this->exportProgress = 10;
        $this->successMessage = '';
        $this->errorMessage = '';
        $this->downloadUrl = '';

        try {
            $databaseName = DB::connection()->getDatabaseName();
            $fileName = 'backup_'.str_replace('_', '-', $databaseName).'_'.now()->format('Y-m-d_His');

            $this->exportProgress = 30;

            $sqlContent = $this->generateCompleteSQLDump();

            $this->exportProgress = 70;

            // Compress if requested
            if ($this->exportOptions['compress']) {
                $fileName .= '.sql.gz';
                $sqlContent = gzencode($sqlContent);
            } else {
                $fileName .= '.sql';
            }

            $this->exportProgress = 90;

            // Save to storage temporarily
            $filePath = 'backups/'.$fileName;
            Storage::disk('local')->put($filePath, $sqlContent);

            $this->exportProgress = 100;

            // Log the export action
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'evento' => 'backup',
                    'archivo' => $fileName,
                    'usuario_nombre' => auth()->user()->name,
                    'ip_address' => request()->ip(),
                ])
                ->log('Exportación de base de datos: '.$fileName);

            // Save backup record
            $fullPath = Storage::disk('local')->path($filePath);
            $backup = Backup::create([
                'filename' => $fileName,
                'original_name' => $fileName,
                'file_size' => file_exists($fullPath) ? filesize($fullPath) : 0,
                'disk' => 'local',
                'path' => $filePath,
                'compressed' => $this->exportOptions['compress'],
                'user_id' => auth()->id(),
                'status' => 'completed',
            ]);
            $this->loadRecentBackups();

            // Generate download URL using backup ID
            $this->downloadUrl = route('admin.monitoreo.backup-download', ['backup' => $backup->id]);

            $this->exportFileName = $fileName;
            $this->isExporting = false;
            $this->successMessage = 'Exportación completada. La descarga comenzará automáticamente.';

            // Trigger download via JavaScript
            $this->dispatch('trigger-download', url: $this->downloadUrl);

        } catch (\Exception $e) {
            $this->errorMessage = 'Error al exportar: '.$e->getMessage();
            Log::error('Database export error: '.$e->getMessage());
            $this->isExporting = false;
            throw $e;
        }
    }

    private function generateCompleteSQLDump(): string
    {
        $output = [];

        // Header
        $output[] = '-- ============================================';
        $output[] = '-- Exportación de Base de Datos Completa';
        $output[] = '-- ============================================';
        $output[] = '-- Fecha: '.now()->format('Y-m-d H:i:s');
        $output[] = '-- Usuario: '.(auth()->check() ? auth()->user()->name : 'Sistema');
        $output[] = '-- Base de datos: '.DB::connection()->getDatabaseName();
        $output[] = '-- ============================================';
        $output[] = '';
        $output[] = 'SET FOREIGN_KEY_CHECKS = 0;';
        $output[] = "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';";
        $output[] = 'START TRANSACTION;';
        $output[] = "SET time_zone = '+00:00';";
        $output[] = '';

        // Tables
        $tablesToExport = $this->availableTables;
        $totalTables = count($tablesToExport);
        $processed = 0;

        foreach ($tablesToExport as $table => $label) {
            $processed++;
            $this->exportProgress = 30 + (int) ($processed / $totalTables * 40);

            $output = array_merge($output, $this->getTableSQL($table));
        }

        // Commit
        $output[] = '';
        $output[] = 'COMMIT;';
        $output[] = '';
        $output[] = 'SET FOREIGN_KEY_CHECKS = 1;';

        return implode("\n", $output);
    }

    private function getTableSQL(string $tableName): array
    {
        $output = [];

        try {
            // Table structure
            if ($this->exportOptions['include_structure']) {
                $output[] = '--';
                $output[] = "-- Estructura de tabla para `{$tableName}`";
                $output[] = '--';

                if ($this->exportOptions['add_drop_table']) {
                    $output[] = "DROP TABLE IF EXISTS `{$tableName}`;";
                }

                $createTable = DB::selectOne("SHOW CREATE TABLE `{$tableName}`");
                $createStatement = $createTable->{'Create Table'};

                if ($this->exportOptions['add_if_not_exists']) {
                    $createStatement = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $createStatement);
                }

                $output[] = $createStatement.';';
                $output[] = '';
            }

            // Table data
            if ($this->exportOptions['include_data']) {
                $output[] = '--';
                $output[] = "-- Volcado de datos para la tabla `{$tableName}`";
                $output[] = '--';

                $records = DB::table($tableName)->get();

                if ($records->isNotEmpty()) {
                    foreach ($records as $record) {
                        $data = (array) $record;
                        $columns = array_keys($data);
                        $values = array_map(function ($value) {
                            if ($value === null) {
                                return 'NULL';
                            } elseif (is_numeric($value)) {
                                return $value;
                            } elseif (is_bool($value)) {
                                return $value ? 1 : 0;
                            } else {
                                return "'".addslashes($value)."'";
                            }
                        }, array_values($data));

                        $sql = "INSERT INTO `{$tableName}` (`".implode('`, `', $columns)."`) VALUES (".implode(', ', $values).');';
                        $output[] = $sql;
                    }
                }

                $output[] = '';
            }

        } catch (\Exception $e) {
            $output[] = "-- Error al procesar la tabla {$tableName}: ".$e->getMessage();
            $output[] = '';
            Log::warning("Error exporting table {$tableName}: ".$e->getMessage());
        }

        return $output;
    }

    // ==================== IMPORT FUNCTIONS ====================

    public function updatedUploadedFile(): void
    {
        if ($this->uploadedFile) {
            $this->importFileName = $this->uploadedFile->getClientOriginalName();
            $this->importFileSize = $this->uploadedFile->getSize();
        }
    }

    public function validateImportFile(): void
    {
        $this->importStep = 2;

        if (! $this->uploadedFile) {
            $this->addError('uploadedFile', 'Por favor selecciona un archivo');

            return;
        }

        // Validate file
        $validator = Validator::make([
            'file' => $this->uploadedFile,
        ], [
            'file' => 'required|file|mimes:sql,gz|max:102400',
        ]);

        if ($validator->fails()) {
            $this->addError('uploadedFile', $validator->errors()->first('file'));
            $this->importStep = 1;

            return;
        }

        // Read and validate content
        try {
            $content = file_get_contents($this->uploadedFile->getRealPath());

            // Handle gzip
            if ($this->uploadedFile->getClientOriginalExtension() === 'gz') {
                $content = gzdecode($content);
                if ($content === false) {
                    $this->addError('uploadedFile', 'El archivo gzip está corrupto');
                    $this->importStep = 1;

                    return;
                }
            }

            // Check if valid SQL
            if (stripos($content, 'CREATE TABLE') === false && stripos($content, 'INSERT INTO') === false) {
                $this->addError('uploadedFile', 'El archivo no parece ser un archivo SQL válido');
                $this->importStep = 1;

                return;
            }

            // Analyze file
            $this->importValidationResults = $this->analyzeSQLFile($content);

        } catch (\Exception $e) {
            $this->addError('uploadedFile', 'Error al leer el archivo: '.$e->getMessage());
            $this->importStep = 1;
        }
    }

    private function analyzeSQLFile(string $content): array
    {
        $results = [
            'total_tables' => 0,
            'total_statements' => 0,
            'has_structure' => false,
            'has_data' => false,
            'tables' => [],
            'warnings' => [],
        ];

        // Count CREATE TABLE statements
        $createMatches = [];
        preg_match_all('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $content, $createMatches);
        $results['total_tables'] = count(array_unique($createMatches[1]));
        $results['tables'] = array_unique($createMatches[1]);
        $results['has_structure'] = $results['total_tables'] > 0;

        // Count INSERT statements
        $insertMatches = [];
        preg_match_all('/INSERT\s+INTO\s+`?(\w+)`?/i', $content, $insertMatches);
        $results['has_data'] = count($insertMatches[1]) > 0;

        // Total statements (approximate)
        $results['total_statements'] = substr_count($content, ';');

        // Warnings
        if (stripos($content, 'DROP TABLE') !== false) {
            $results['warnings'][] = 'El archivo contiene sentencias DROP TABLE - Esto eliminará tablas existentes';
        }

        if (stripos($content, 'TRUNCATE') !== false) {
            $results['warnings'][] = 'El archivo contiene sentencias TRUNCATE - Esto vaciará tablas';
        }

        return $results;
    }

    public function executeImport(): void
    {
        $this->importStep = 4;
        $this->isImporting = true;
        $this->importProgress = 10;
        $this->successMessage = '';
        $this->errorMessage = '';

        try {
            // Disable foreign key checks first
            if ($this->importOptions['skip_foreign_key_checks']) {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            }

            // Disable unique checks and autocommit for better performance
            DB::statement('SET UNIQUE_CHECKS = 0');
            DB::statement('SET AUTOCOMMIT = 0');

            $content = file_get_contents($this->uploadedFile->getRealPath());

            // Handle gzip
            if ($this->uploadedFile->getClientOriginalExtension() === 'gz') {
                $content = gzdecode($content);
            }

            // Parse and execute statements
            $statements = $this->parseSQLStatements($content);
            $executed = 0;
            $total = count($statements);
            $errors = [];

            $this->importProgress = 30;

            // Execute statements without wrapping in a single transaction
            // DDL statements (DROP, CREATE) cannot be in transactions in MySQL
            foreach ($statements as $index => $statement) {
                if (empty(trim($statement))) {
                    continue;
                }

                try {
                    DB::statement($statement);
                    $executed++;
                } catch (\Exception $e) {
                    if (! $this->importOptions['ignore_errors']) {
                        // Re-enable settings before throwing
                        if ($this->importOptions['skip_foreign_key_checks']) {
                            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
                        }
                        DB::statement('SET UNIQUE_CHECKS = 1');
                        DB::statement('SET AUTOCOMMIT = 1');
                        throw $e;
                    }
                    $errors[] = "Statement {$index}: ".$e->getMessage();
                }

                $this->importProgress = 30 + (int) ($executed / max(1, $total) * 70);
            }

            // Commit any pending changes
            DB::statement('COMMIT');

            // Re-enable settings
            if ($this->importOptions['skip_foreign_key_checks']) {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            }
            DB::statement('SET UNIQUE_CHECKS = 1');
            DB::statement('SET AUTOCOMMIT = 1');

            $this->importProgress = 100;

            // Log the import action
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'evento' => 'import',
                    'archivo' => $this->importFileName,
                    'sentencias_ejecutadas' => $executed,
                    'total_sentencias' => $total,
                    'errores' => count($errors),
                    'usuario_nombre' => auth()->user()->name,
                    'ip_address' => request()->ip(),
                ])
                ->log('Importación de base de datos: '.$this->importFileName);

            if (count($errors) > 0) {
                $this->successMessage = "Importación completada con advertencias: {$executed} sentencias ejecutadas, ".count($errors).' errores ignorados';
            } else {
                $this->successMessage = "Importación exitosa: {$executed} sentencias ejecutadas";
            }

        } catch (\Exception $e) {
            // Ensure settings are re-enabled on error
            try {
                if ($this->importOptions['skip_foreign_key_checks']) {
                    DB::statement('SET FOREIGN_KEY_CHECKS = 1');
                }
                DB::statement('SET UNIQUE_CHECKS = 1');
                DB::statement('SET AUTOCOMMIT = 1');
            } catch (\Exception $cleanupError) {
                Log::error('Error cleaning up import settings: '.$cleanupError->getMessage());
            }

            $this->errorMessage = 'Error en importación: '.$e->getMessage();
            Log::error('Database import error: '.$e->getMessage());
            $this->isImporting = false;
            throw $e;
        }
    }

    private function parseSQLStatements(string $content): array
    {
        $statements = [];
        $current = '';
        $delimiter = ';';

        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines
            if (empty($line)) {
                continue;
            }

            // Skip comments
            if (strpos($line, '--') === 0 || strpos($line, '/*') === 0) {
                continue;
            }

            // Check for DELIMITER change
            if (stripos($line, 'DELIMITER') === 0) {
                $delimiter = trim(str_ireplace('DELIMITER', '', $line));
                continue;
            }

            $current .= $line."\n";

            // Check if statement is complete
            if (substr(trim($line), -strlen($delimiter)) === $delimiter) {
                $statement = trim(str_replace($delimiter, '', $current));
                if (! empty($statement)) {
                    $statements[] = $statement;
                }
                $current = '';
            }
        }

        return $statements;
    }

    // ==================== UTILITY FUNCTIONS ====================

    public function loadAvailableTables(): void
    {
        $this->availableTables = [];
        $tables = DB::select('SHOW TABLES');
        $databaseName = DB::connection()->getDatabaseName();
        $key = 'Tables_in_'.$databaseName;

        $excludedTables = [
            'migrations', 'password_resets', 'password_reset_tokens',
            'personal_access_tokens', 'cache', 'cache_locks', 'jobs',
            'job_batches', 'failed_jobs', 'sessions', 'activity_log',
        ];

        foreach ($tables as $table) {
            $tableName = $table->$key;
            if (! in_array($tableName, $excludedTables)) {
                $this->availableTables[$tableName] = $this->formatTableName($tableName);
            }
        }

        asort($this->availableTables);
    }

    public function calculateEstimatedSize(): void
    {
        $totalSize = 0;

        foreach ($this->availableTables as $table => $label) {
            try {
                $stats = DB::selectOne("SELECT
                    data_length + index_length as total_size,
                    table_rows
                FROM information_schema.TABLES
                WHERE table_schema = '".DB::connection()->getDatabaseName()."'
                AND table_name = '{$table}'");

                $totalSize += $stats->total_size ?? 0;
            } catch (\Exception $e) {
                // Skip if error
            }
        }

        $this->estimatedFileSize = $this->formatBytes($totalSize);
        $this->totalTables = count($this->availableTables);
    }

    private function formatTableName(string $tableName): string
    {
        return ucwords(str_replace('_', ' ', $tableName));
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }

    // ==================== BACKUP HISTORY ====================

    public function loadRecentBackups(): void
    {
        $this->recentBackups = Backup::with('user')
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'filename' => $b->filename,
                'human_size' => $b->human_size,
                'compressed' => $b->compressed,
                'user_name' => $b->user?->name ?? 'Sistema',
                'created_at' => $b->created_at->format('d/m/Y H:i'),
                'created_at_diff' => $b->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    public function deleteBackup(int $id): void
    {
        $backup = Backup::findOrFail($id);

        // Delete file from storage
        if (Storage::disk($backup->disk)->exists($backup->path)) {
            Storage::disk($backup->disk)->delete($backup->path);
        }

        // Delete record
        $backup->delete();

        $this->loadRecentBackups();
        $this->successMessage = 'Respaldo eliminado correctamente.';
    }

    // ─── Render ─────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.admin.monitoreo.base-datos');
    }
}
