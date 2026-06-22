<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SystemMonitorService
{
    /**
     * Get database metrics (size, tables, connections).
     */
    public function getDatabaseMetrics(): array
    {
        return Cache::remember('monitoreo_database_metrics', 30, function () {
            $tables = DB::select('SHOW TABLE STATUS');
            $size = 0;
            $tableCount = count($tables);
            
            $tableStats = [];
            $engines = [];

            foreach ($tables as $table) {
                $tableSize = $table->Data_length + $table->Index_length;
                $size += $tableSize;
                
                $tableStats[] = [
                    'name' => $table->Name,
                    'engine' => $table->Engine,
                    'rows' => $table->Rows,
                    'size_bytes' => $tableSize,
                    'size_mb' => round($tableSize / 1024 / 1024, 2),
                    'created_at' => $table->Create_time,
                    'updated_at' => $table->Update_time,
                ];

                if ($table->Engine) {
                    if (!isset($engines[$table->Engine])) {
                        $engines[$table->Engine] = 0;
                    }
                    $engines[$table->Engine]++;
                }
            }

            // Sort by size desc for top tables
            usort($tableStats, function($a, $b) {
                return $b['size_bytes'] <=> $a['size_bytes'];
            });
            $topTables = array_slice($tableStats, 0, 5);

            // Sort by update time desc for recent tables
            usort($tableStats, function($a, $b) {
                $timeA = strtotime($a['updated_at'] ?? $a['created_at'] ?? '2000-01-01');
                $timeB = strtotime($b['updated_at'] ?? $b['created_at'] ?? '2000-01-01');
                return $timeB <=> $timeA;
            });
            $recentTables = array_slice($tableStats, 0, 5);

            // Get active connections
            $threadsConnected = DB::select("SHOW GLOBAL STATUS LIKE 'Threads_connected'");
            $connections = $threadsConnected[0]->Value ?? 0;

            // Get max connections
            $maxConnectionsQuery = DB::select("SHOW VARIABLES LIKE 'max_connections'");
            $maxConnections = $maxConnectionsQuery[0]->Value ?? 100;

            // Get uptime
            $uptimeQuery = DB::select("SHOW GLOBAL STATUS LIKE 'Uptime'");
            $uptime = $uptimeQuery[0]->Value ?? 0;

            // Format size in MB
            $sizeMb = round($size / 1024 / 1024, 2);

            return [
                'size_mb' => $sizeMb,
                'tables_count' => $tableCount,
                'active_connections' => (int)$connections,
                'max_connections' => (int)$maxConnections,
                'uptime_seconds' => (int)$uptime,
                'top_tables' => $topTables,
                'recent_tables' => $recentTables,
                'engines' => $engines,
            ];
        });
    }

    /**
     * Get server metrics (CPU, RAM, Disk).
     */
    public function getServerMetrics(): array
    {
        return Cache::remember('monitoreo_server_metrics', 10, function () {
            // Disk usage
            $diskTotal = disk_total_space(base_path());
            $diskFree = disk_free_space(base_path());
            $diskUsed = $diskTotal - $diskFree;
            $diskUsagePercent = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 2) : 0;

            // RAM and CPU (OS dependent)
            $cpuLoad = 0;
            $ramTotal = 0;
            $ramFree = 0;
            $ramUsagePercent = 0;

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows (Laragon environment)
                try {
                    // Get CPU load using WMIC
                    $wmicCpu = shell_exec('wmic cpu get loadpercentage /value 2>nul');
                    if ($wmicCpu && preg_match('/LoadPercentage=(\d+)/i', $wmicCpu, $matches)) {
                        $cpuLoad = (float)$matches[1];
                    }

                    // Get RAM using WMIC
                    $wmicRam = shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value 2>nul');
                    if ($wmicRam && preg_match('/FreePhysicalMemory=(\d+)/i', $wmicRam, $freeMatch) && preg_match('/TotalVisibleMemorySize=(\d+)/i', $wmicRam, $totalMatch)) {
                        $ramTotal = (float)$totalMatch[1] * 1024; // Convert KB to Bytes
                        $ramFree = (float)$freeMatch[1] * 1024;
                        $ramUsed = $ramTotal - $ramFree;
                        $ramUsagePercent = $ramTotal > 0 ? round(($ramUsed / $ramTotal) * 100, 2) : 0;
                    }
                } catch (\Exception $e) {
                    // Fallback if wmic is blocked or unavailable
                    $cpuLoad = 0;
                }
            } else {
                // Linux / Unix
                // CPU
                $load = sys_getloadavg();
                if ($load) {
                    // sys_getloadavg returns 1, 5, 15 min load.
                    // To get a percentage, we'd need core count, but we can return the 1-min load.
                    $cpuLoad = round($load[0] * 100, 2); // Approximate 
                }

                // RAM from /proc/meminfo
                if (is_readable('/proc/meminfo')) {
                    $meminfo = file_get_contents('/proc/meminfo');
                    if (preg_match('/MemTotal:\s+(\d+)\s+kB/', $meminfo, $totalMatch) &&
                        preg_match('/MemAvailable:\s+(\d+)\s+kB/', $meminfo, $availableMatch)) {
                        $ramTotal = (float)$totalMatch[1] * 1024;
                        $ramFree = (float)$availableMatch[1] * 1024;
                        $ramUsed = $ramTotal - $ramFree;
                        $ramUsagePercent = $ramTotal > 0 ? round(($ramUsed / $ramTotal) * 100, 2) : 0;
                    }
                }
            }

            // PHP Memory limit and usage
            $phpMemoryUsage = memory_get_usage(true);
            $phpMemoryLimit = ini_get('memory_limit');
            $phpMaxExecutionTime = ini_get('max_execution_time');
            $phpUploadMaxFilesize = ini_get('upload_max_filesize');
            $phpPostMaxSize = ini_get('post_max_size');
            
            return [
                'cpu_load_percent' => $cpuLoad,
                'ram_total_bytes' => $ramTotal,
                'ram_free_bytes' => $ramFree,
                'ram_usage_percent' => $ramUsagePercent,
                'disk_total_bytes' => $diskTotal,
                'disk_free_bytes' => $diskFree,
                'disk_usage_percent' => $diskUsagePercent,
                'php_memory_usage_bytes' => $phpMemoryUsage,
                'php_memory_limit' => $phpMemoryLimit,
                'php_max_execution_time' => $phpMaxExecutionTime,
                'php_upload_max_filesize' => $phpUploadMaxFilesize,
                'php_post_max_size' => $phpPostMaxSize,
                'os' => PHP_OS,
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido',
            ];
        });
    }
}
