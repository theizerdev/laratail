<?php

namespace App\Livewire\Admin\Integraciones;

use App\Models\Empresa;
use App\Services\WhatsAppService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('WhatsApp CRM')]
class WhatsAppCrm extends Component
{
    // Config
    public ?int $empresaId = null;
    public string $apiKey = '';
    public string $apiUrl = '';

    // Connection status
    public ?string $connectionStatus = null;
    public ?string $qrCodeData = null;
    public ?string $connectedPhone = null;
    public ?string $statusMessage = null;
    public ?string $lastSeen = null;

    // Debounce: avoid transient connecting→connected flips
    #[Locked]
    public string $lastStableStatus = 'disconnected';
    #[Locked]
    public int $unstablePollCount = 0;
    #[Locked]
    public int $maxUnstablePolls = 2; // require 2 consecutive non-connected polls before flipping

    // Stats
    public array $stats = [
        'sent' => 0,
        'delivered' => 0,
        'failed' => 0,
        'pending' => 0,
        'total' => 0,
        'today' => 0,
    ];

    public array $dailyStats = [];
    public array $topRecipients = [];
    public array $recentMessages = [];
    public array $recentActivity = [];

    protected function rules(): array
    {
        return [
            'empresaId' => 'required|exists:empresas,id',
            'apiKey' => 'required|string',
            'apiUrl' => 'required|url',
        ];
    }

    function mount()
    {
        $this->empresaId = auth()->user()->empresa_id;
        $this->updatedEmpresaId();
        // Check live API status on page load (like Conexion.php)
        $this->refreshStatus();
    }

    private function getService(): WhatsAppService
    {
        return WhatsAppService::forCredentials([
            'empresa_id' => $this->empresaId,
            'api_key' => $this->apiKey,
            'api_url' => $this->apiUrl,
        ]);
    }

    // ─── Connection ────────────────────────────────────────────────────

    public function refreshStatus(): void
    {
        if (!$this->empresaId || !$this->apiKey) {
            $this->connectionStatus = 'not_configured';
            return;
        }

        $service = $this->getService();
        $status = $service->getStatus();

        if ($status) {
            // Handle service unavailable (ConnectionException from WhatsAppService)
            if (isset($status['_error'])) {
                $this->connectionStatus = 'service_unavailable';
                $this->statusMessage = 'No se puede conectar al servicio de WhatsApp. Verifique que el servidor este activo.';
                return;
            }

            // The API returns 'connectionState', not 'status'
            $rawState = $status['connectionState'] ?? $status['status'] ?? 'unknown';
            if ($rawState === 'unknown' && isset($status['isConnected'])) {
                $rawState = $status['isConnected'] ? 'connected' : 'disconnected';
            }
            $rawNewStatus = $this->mapStatus($rawState);

            // ── Debounce logic ──
            // If we were connected and the API briefly returns connecting/reconnecting,
            // don't flip the UI immediately. Wait for MAX_UNSTABLE_POLLS consecutive polls.
            if ($this->lastStableStatus === 'connected' && $rawNewStatus === 'connecting') {
                $this->unstablePollCount++;
                if ($this->unstablePollCount < $this->maxUnstablePolls) {
                    // Keep showing connected, transient state
                    $this->connectionStatus = 'connected';
                    $this->statusMessage = $status['message'] ?? $this->statusMessage;
                    $this->lastSeen = $status['lastSeen'] ?? $this->lastSeen;
                    $this->loadDashboardStats($service);
                    return;
                }
                // Persisted connecting state, accept it
                $this->unstablePollCount = 0;
            } else {
                // Stable or different transition, reset counter
                $this->unstablePollCount = 0;
            }

            $this->connectionStatus = $rawNewStatus;
            $this->statusMessage = $status['message'] ?? null;
            $this->lastSeen = $status['lastSeen'] ?? null;

            // Track stable status for debounce reference
            if ($rawNewStatus === 'connected') {
                $this->lastStableStatus = 'connected';
            } elseif ($rawNewStatus !== 'connecting') {
                $this->lastStableStatus = $rawNewStatus;
            }

            // Extract user data when connected
            if ($this->connectionStatus === 'connected' && isset($status['user'])) {
                $user = $status['user'];
                if (isset($user['id'])) {
                    $this->connectedPhone = explode(':', $user['id'])[0];
                }
                $this->qrCodeData = null;
            }

            // Handle QR from status response or fetch separately
            if ($this->connectionStatus === 'connecting' || $this->connectionStatus === 'qr_ready') {
                $qrValue = $status['qrCode'] ?? $status['qr'] ?? null;
                if ($qrValue) {
                    if (str_starts_with($qrValue, 'data:image')) {
                        $this->qrCodeData = $qrValue;
                    } else {
                        $this->fetchQR();
                    }
                } else {
                    $this->fetchQR();
                }
            }

            // Update empresa record
            if ($this->empresaId) {
                $empresa = Empresa::find($this->empresaId);
                if ($empresa) {
                    $empresa->update([
                        'whatsapp_status' => $this->connectionStatus,
                        'whatsapp_phone' => $this->connectedPhone,
                        'whatsapp_last_connected' => $this->connectionStatus === 'connected' ? now() : $empresa->whatsapp_last_connected,
                    ]);
                }
            }

            // Load dashboard stats when connected
            if ($this->connectionStatus === 'connected') {
                $this->loadDashboardStats($service);
            }
        } else {
            $this->connectionStatus = 'disconnected';
            $this->statusMessage = 'No se pudo conectar con el servidor de WhatsApp.';
        }
    }

    public function checkConnection(): void
    {
        $this->refreshStatus();
    }

    public function refreshDashboard(): void
    {
        $this->refreshStatus();
    }

    private function mapStatus(string $apiStatus): string
    {
        return match ($apiStatus) {
            'connected', 'open' => 'connected',
            'connecting', 'reconnecting' => 'connecting',
            'qr', 'qr_ready' => 'qr_ready',
            'service_unavailable' => 'service_unavailable',
            'error' => 'error',
            default => 'disconnected',
        };
    }

    public function fetchQR(): void
    {
        if (!$this->empresaId || !$this->apiKey) {
            return;
        }

        $service = $this->getService();
        $qr = $service->getQRCode();

        if ($qr && ($qr['success'] ?? false) && isset($qr['qr'])) {
            $this->qrCodeData = $qr['qr'];
        }
    }

    public function connectWhatsApp(): void
    {
        if (!$this->empresaId || !$this->apiKey) {
            $this->dispatch('show-toast', type: 'error', message: 'Configure la empresa y API key primero.');
            return;
        }

        $this->qrCodeData = null;
        $this->connectionStatus = 'connecting';

        $service = $this->getService();
        $result = $service->connect();

        // The API returns { success: true, message: 'Connection initiated' }
        // Connection state changes are async, so we keep 'connecting' and poll
        if ($result && ($result['success'] ?? false)) {
            $this->dispatch('show-toast', type: 'success', message: 'Iniciando conexion. Espere el codigo QR...');
            $this->fetchQR();
        } else {
            $this->connectionStatus = 'disconnected';
            $this->dispatch('show-toast', type: 'error', message: 'Error al iniciar la conexion. Verifique el servicio.');
        }
    }

    public function disconnectWhatsApp(): void
    {
        $service = $this->getService();
        $result = $service->disconnect();

        if ($result && ($result['success'] ?? false)) {
            $this->connectionStatus = 'disconnected';
            $this->connectedPhone = null;
            $this->qrCodeData = null;
            $this->dispatch('show-toast', type: 'success', message: 'WhatsApp desconectado.');
        } else {
            $this->dispatch('show-toast', type: 'error', message: 'Error al desconectar.');
        }
    }

    public function reconnectWhatsApp(): void
    {
        $this->connectionStatus = 'connecting';
        $this->qrCodeData = null;

        $service = $this->getService();
        $result = $service->reconnect();

        if ($result && ($result['success'] ?? false)) {
            $this->dispatch('show-toast', type: 'success', message: 'Reconectando WhatsApp...');
            $this->fetchQR();
        } else {
            $this->dispatch('show-toast', type: 'error', message: 'Error al reconectar.');
        }
    }

    public function removeSession(): void
    {
        $service = $this->getService();
        $result = $service->removeSession();

        if ($result) {
            $this->connectionStatus = 'disconnected';
            $this->connectedPhone = null;
            $this->qrCodeData = null;
            $this->dispatch('show-toast', type: 'success', message: 'Sesion eliminada correctamente.');
        } else {
            $this->dispatch('show-toast', type: 'error', message: 'Error al eliminar la sesion.');
        }
    }

    // ─── Config ────────────────────────────────────────────────────────

    public function saveConfig(): void
    {
        $this->validate([
            'empresaId' => 'required|exists:empresas,id',
            'apiKey' => 'required|string',
            'apiUrl' => 'required|url',
        ]);

        $empresa = Empresa::findOrFail($this->empresaId);
        $empresa->update([
            'whatsapp_api_key' => $this->apiKey,
            'whatsapp_api_url' => $this->apiUrl,
            'whatsapp_active' => true,
        ]);

        $this->dispatch('show-toast', type: 'success', message: 'Configuracion guardada correctamente.');
        $this->refreshStatus();
    }

    public function updatedEmpresaId(): void
    {
        if ($this->empresaId) {
            $empresa = Empresa::find($this->empresaId);
            if ($empresa) {
                $this->apiKey = $empresa->whatsapp_api_key ?? '';
                $this->apiUrl = $empresa->whatsapp_api_url ?? 'http://82.165.213.124:8092';
                $this->connectionStatus = $empresa->whatsapp_status ?? 'disconnected';
                $this->connectedPhone = $empresa->whatsapp_phone;
            }
        } else {
            $this->apiKey = '';
            $this->apiUrl = 'http://82.165.213.124:8092';
            $this->connectionStatus = null;
            $this->connectedPhone = null;
        }
    }

    public function generateApiKey(): void
    {
        $this->apiKey = bin2hex(random_bytes(32));
    }

    // ─── Dashboard Stats (from API) ───────────────────────────────────

    private function loadDashboardStats(WhatsAppService $service): void
    {
        // getStats() returns computed stats + recent messages from the messages endpoint
        $data = $service->getStats();

        if ($data && is_array($data)) {
            $apiStats = $data['stats'] ?? [];
            $this->stats = [
                'sent' => $apiStats['sent'] ?? 0,
                'delivered' => $apiStats['delivered'] ?? 0,
                'failed' => $apiStats['failed'] ?? 0,
                'pending' => $apiStats['pending'] ?? 0,
                'total' => $apiStats['total'] ?? 0,
                'today' => $apiStats['today'] ?? 0,
            ];

            $this->recentMessages = $data['messages'] ?? [];

            // Compute top recipients from recent messages
            $this->topRecipients = collect($this->recentMessages)
                ->groupBy('to')
                ->map(function ($msgs, $phone) {
                    return [
                        'phone' => $phone,
                        'name' => $msgs->first()['to'] ?? $phone,
                        'total_messages' => $msgs->count(),
                    ];
                })
                ->sortByDesc('total_messages')
                ->take(5)
                ->values()
                ->toArray();

            // Compute recent activity (last messages as activity feed)
            $this->recentActivity = collect($this->recentMessages)
                ->take(8)
                ->map(function ($msg) {
                    $phone = substr($msg['to'] ?? '', -4);
                    $status = $msg['status'] ?? 'unknown';
                    $action = match ($status) {
                        'sent' => "Mensaje enviado a ***{$phone}",
                        'delivered' => "Mensaje entregado a ***{$phone}",
                        'failed' => "Fallo envio a ***{$phone}",
                        'pending' => "Mensaje pendiente para ***{$phone}",
                        default => "Actividad con ***{$phone}",
                    };
                    return [
                        'action' => $action,
                        'status' => $status,
                        'time' => isset($msg['createdAt']) ? \Carbon\Carbon::parse($msg['createdAt'])->diffForHumans() : '',
                    ];
                })
                ->toArray();
        }
    }

    public function render()
    {
        $empresas = Empresa::where('status', true)->orderBy('razon_social')->get();

        return view('livewire.admin.integraciones.whatsapp', [
            'empresas' => $empresas,
        ]);
    }
}
