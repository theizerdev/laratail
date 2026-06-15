<?php

namespace App\Livewire\Admin\Monitoreo;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

#[Layout('components.layouts.admin')]
#[Title('Monitoreo del Sistema')]
class Index extends Component
{
    use WithPagination;

    public string $activeTab = 'servidor';

    // Login history filters
    public string $loginFilterUser = '';
    public string $loginFilterAction = '';
    public string $loginFilterDateFrom = '';
    public string $loginFilterDateTo = '';

    // Activity log filters
    public string $activityFilterUser = '';
    public string $activityFilterModel = '';
    public string $activityFilterEvent = '';
    public string $activityFilterDateFrom = '';
    public string $activityFilterDateTo = '';

    // Expanded activity row
    public ?int $expandedActivity = null;

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function toggleExpand(int $id): void
    {
        $this->expandedActivity = $this->expandedActivity === $id ? null : $id;
    }

    public function resetLoginFilters(): void
    {
        $this->reset(['loginFilterUser', 'loginFilterAction', 'loginFilterDateFrom', 'loginFilterDateTo']);
        $this->resetPage();
    }

    public function resetActivityFilters(): void
    {
        $this->reset(['activityFilterUser', 'activityFilterModel', 'activityFilterEvent', 'activityFilterDateFrom', 'activityFilterDateTo']);
        $this->resetPage();
    }

    // ─── Server Stats ─────────────────────────────────────────────────

    private function getServerStats(): array
    {
        // PHP info
        $phpVersion = PHP_VERSION;
        $laravelVersion = app()->version();

        // Memory
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = ini_get('memory_limit');

        // Disk
        $diskFree = disk_free_space(base_path());
        $diskTotal = disk_total_space(base_path());
        $diskUsed = $diskTotal - $diskFree;
        $diskPercent = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 1) : 0;

        // Database size
        $dbSize = $this->getDatabaseSize();

        // Sessions
        $activeSessions = DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(30)->timestamp)
            ->count();

        $totalUsers = User::count();
        $activeUserIds = DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(30)->timestamp)
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');
        $activeUsers = User::whereIn('id', $activeUserIds)->count();

        // Cache
        $cacheDriver = config('cache.default');

        // Activity log count
        $totalActivities = Activity::count();
        $todayActivities = Activity::whereDate('created_at', today())->count();

        // Uptime
        $serverUptime = 'N/A';
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $serverUptime = round($load[0], 2) . ' / ' . round($load[1], 2) . ' / ' . round($load[2], 2);
        }

        // OS
        $os = PHP_OS;

        return [
            'php_version' => $phpVersion,
            'laravel_version' => $laravelVersion,
            'os' => $os,
            'memory_usage' => $this->formatBytes($memoryUsage),
            'memory_peak' => $this->formatBytes($memoryPeak),
            'memory_limit' => $memoryLimit,
            'disk_free' => $this->formatBytes($diskFree),
            'disk_total' => $this->formatBytes($diskTotal),
            'disk_used' => $this->formatBytes($diskUsed),
            'disk_percent' => $diskPercent,
            'db_size' => $dbSize,
            'active_sessions' => $activeSessions,
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'cache_driver' => $cacheDriver,
            'total_activities' => $totalActivities,
            'today_activities' => $todayActivities,
            'server_load' => $serverUptime,
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug') ? 'Activo' : 'Inactivo',
        ];
    }

    private function getDatabaseSize(): string
    {
        try {
            $dbName = config('database.connections.mysql.database');
            $result = DB::select("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables
                WHERE table_schema = ?
            ", [$dbName]);

            return ($result[0]->size_mb ?? 0) . ' MB';
        } catch (\Exception $e) {
            // Fallback for SQLite or other drivers
            try {
                $tables = DB::select('SHOW TABLE STATUS');
                $total = 0;
                foreach ($tables as $table) {
                    $total += ($table->Data_length ?? 0) + ($table->Index_length ?? 0);
                }
                return round($total / 1024 / 1024, 2) . ' MB';
            } catch (\Exception $e2) {
                return 'N/A';
            }
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    // ─── Login History ────────────────────────────────────────────────

    private function getLoginHistory()
    {
        $query = Activity::query()
            ->where(function ($q) {
                $q->where('description', 'like', '%sesión%')
                  ->orWhere('description', 'like', '%sesion%')
                  ->orWhere('properties->evento', 'login')
                  ->orWhere('properties->evento', 'logout')
                  ->orWhere('properties->evento', 'login_failed');
            });

        if ($this->loginFilterUser) {
            $query->where('properties->usuario_nombre', 'like', '%' . $this->loginFilterUser . '%');
        }

        if ($this->loginFilterAction) {
            $query->where('properties->evento', $this->loginFilterAction);
        }

        if ($this->loginFilterDateFrom) {
            $query->whereDate('created_at', '>=', $this->loginFilterDateFrom);
        }

        if ($this->loginFilterDateTo) {
            $query->whereDate('created_at', '<=', $this->loginFilterDateTo);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15, pageName: 'login_page');
    }

    // ─── Activity Log ────────────────────────────────────────────────

    private function getActivityLog()
    {
        $query = Activity::query()
            ->whereNotNull('subject_type')
            ->where(function ($q) {
                $q->whereNull('properties->evento')
                  ->orWhereNotIn('properties->evento', ['login', 'logout', 'login_failed']);
            });

        if ($this->activityFilterUser) {
            $query->where('properties->usuario_nombre', 'like', '%' . $this->activityFilterUser . '%');
        }

        if ($this->activityFilterModel) {
            $query->where('subject_type', 'like', '%' . $this->activityFilterModel . '%');
        }

        if ($this->activityFilterEvent) {
            $query->where('event', $this->activityFilterEvent);
        }

        if ($this->activityFilterDateFrom) {
            $query->whereDate('created_at', '>=', $this->activityFilterDateFrom);
        }

        if ($this->activityFilterDateTo) {
            $query->whereDate('created_at', '<=', $this->activityFilterDateTo);
        }

        return $query->orderBy('created_at', 'desc')->paginate(15, pageName: 'activity_page');
    }

    // ─── Render ───────────────────────────────────────────────────────

    public function render()
    {
        $users = User::orderBy('name')->get();
        $serverStats = $this->getServerStats();
        $loginHistory = $this->getLoginHistory();
        $activityLog = $this->getActivityLog();

        // Unique model types for filter
        $modelTypes = Activity::query()
            ->whereNotNull('subject_type')
            ->select('subject_type')
            ->distinct()
            ->pluck('subject_type')
            ->map(fn ($m) => class_basename($m))
            ->unique()
            ->sort()
            ->values();

        return view('livewire.admin.monitoreo.index', [
            'users' => $users,
            'serverStats' => $serverStats,
            'loginHistory' => $loginHistory,
            'activityLog' => $activityLog,
            'modelTypes' => $modelTypes,
        ]);
    }
}
