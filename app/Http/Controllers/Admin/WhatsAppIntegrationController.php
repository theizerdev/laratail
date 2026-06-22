<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Services\WhatsAppService;

class WhatsAppIntegrationController extends Controller
{
    private function getService($empresa)
    {
        return WhatsAppService::forCredentials([
            'empresa_id' => $empresa->id,
            'api_key' => $empresa->whatsapp_api_key,
            'api_url' => $empresa->whatsapp_api_url ?? config('whatsapp.api_url', 'http://82.165.213.124:8092'),
        ]);
    }

    private function resolveEmpresaId(Request $request)
    {
        return $request->input('empresa_id') ?: $request->user()->empresa_id;
    }

    public function getConfig(Request $request)
    {
        $empresaId = $this->resolveEmpresaId($request);
        if (!$empresaId) {
            return response()->json(['error' => 'No se ha seleccionado empresa'], 400);
        }

        $empresa = Empresa::find($empresaId);
        $empresas = Empresa::where('status', true)->orderBy('razon_social')->get(['id', 'razon_social']);
        
        return response()->json([
            'empresas' => $empresas,
            'empresa_id' => $empresa->id,
            'empresa_name' => $empresa->razon_social,
            'api_key' => $empresa->whatsapp_api_key ?? '',
            'api_url' => $empresa->whatsapp_api_url ?? 'http://82.165.213.124:8092',
            'status' => $empresa->whatsapp_status ?? 'disconnected',
            'phone' => $empresa->whatsapp_phone,
            'last_connected' => $empresa->whatsapp_last_connected,
        ]);
    }

    public function saveConfig(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string',
            'api_url' => 'required|url',
        ]);

        $empresaId = $this->resolveEmpresaId($request);
        if (!$empresaId) {
            return response()->json(['error' => 'No se ha seleccionado empresa'], 400);
        }

        $empresa = Empresa::findOrFail($empresaId);
        $empresa->update([
            'whatsapp_api_key' => $request->api_key,
            'whatsapp_api_url' => $request->api_url,
            'whatsapp_active' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Configuración guardada correctamente.']);
    }

    public function getStatus(Request $request)
    {
        $empresaId = $this->resolveEmpresaId($request);
        if (!$empresaId) {
            return response()->json(['status' => 'not_configured', 'qr' => null, 'stats' => null]);
        }

        $empresa = Empresa::findOrFail($empresaId);

        if (!$empresa->whatsapp_api_key) {
            return response()->json([
                'status' => 'not_configured',
                'qr' => null,
                'stats' => null,
            ]);
        }

        $service = $this->getService($empresa);
        $statusData = $service->getStatus();
        
        if ($statusData) {
            if (isset($statusData['_error'])) {
                return response()->json([
                    'status' => 'service_unavailable',
                    'message' => 'No se puede conectar al servicio de WhatsApp. Verifique que el servidor este activo.',
                    'qr' => null,
                    'stats' => null,
                ]);
            }

            $rawState = $statusData['connectionState'] ?? $statusData['status'] ?? 'unknown';
            if ($rawState === 'unknown' && isset($statusData['isConnected'])) {
                $rawState = $statusData['isConnected'] ? 'connected' : 'disconnected';
            }

            $mappedStatus = $this->mapStatus($rawState);
            $connectedPhone = null;

            if ($mappedStatus === 'connected' && isset($statusData['user']['id'])) {
                $connectedPhone = explode(':', $statusData['user']['id'])[0];
            }

            $empresa->update([
                'whatsapp_status' => $mappedStatus,
                'whatsapp_phone' => $connectedPhone ?? $empresa->whatsapp_phone,
                'whatsapp_last_connected' => $mappedStatus === 'connected' ? now() : $empresa->whatsapp_last_connected,
            ]);

            $qrCodeData = null;
            if ($mappedStatus === 'connecting' || $mappedStatus === 'qr_ready') {
                $qrValue = $statusData['qrCode'] ?? $statusData['qr'] ?? null;
                if ($qrValue && str_starts_with($qrValue, 'data:image')) {
                    $qrCodeData = $qrValue;
                } else {
                    $qrData = $service->getQRCode();
                    if ($qrData && ($qrData['success'] ?? false) && isset($qrData['qr'])) {
                        $qrCodeData = $qrData['qr'];
                    }
                }
            }

            // Dashboard stats
            $dashboardData = [];
            $recentMessages = [];
            if ($mappedStatus === 'connected') {
                if (method_exists($service, 'getStats')) {
                    $dashboardData = $service->getStats();
                }
                if (method_exists($service, 'getMessages')) {
                    $msgsResult = $service->getMessages(['limit' => 10]);
                    if ($msgsResult && isset($msgsResult['messages'])) {
                        $recentMessages = $msgsResult['messages'];
                    }
                }
            }

            return response()->json([
                'status' => $mappedStatus,
                'message' => $statusData['message'] ?? null,
                'lastSeen' => $statusData['lastSeen'] ?? null,
                'phone' => $connectedPhone,
                'qr' => $qrCodeData,
                'dashboard' => $dashboardData,
                'messages' => $recentMessages,
            ]);
        }

        return response()->json([
            'status' => 'disconnected',
            'message' => 'No se pudo conectar con el servidor de WhatsApp.',
            'qr' => null,
            'stats' => null,
        ]);
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

    public function connect(Request $request)
    {
        $empresaId = $this->resolveEmpresaId($request);
        $empresa = Empresa::findOrFail($empresaId);
        $service = $this->getService($empresa);
        $result = $service->connect();

        if ($result && ($result['success'] ?? false)) {
            return response()->json(['success' => true, 'message' => 'Iniciando conexion. Espere el codigo QR...']);
        }
        return response()->json(['success' => false, 'message' => 'Error al iniciar la conexion.']);
    }

    public function disconnect(Request $request)
    {
        $empresaId = $this->resolveEmpresaId($request);
        $empresa = Empresa::findOrFail($empresaId);
        $service = $this->getService($empresa);
        $result = $service->disconnect();
        
        if ($result && ($result['success'] ?? false)) {
            $empresa->update(['whatsapp_status' => 'disconnected', 'whatsapp_phone' => null]);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Error al desconectar.']);
    }

    public function reconnect(Request $request)
    {
        $empresaId = $this->resolveEmpresaId($request);
        $empresa = Empresa::findOrFail($empresaId);
        $service = $this->getService($empresa);
        $result = $service->reconnect();

        if ($result && ($result['success'] ?? false)) {
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Error al reconectar.']);
    }

    public function removeSession(Request $request)
    {
        $empresaId = $this->resolveEmpresaId($request);
        $empresa = Empresa::findOrFail($empresaId);
        $service = $this->getService($empresa);
        $result = $service->removeSession();

        if ($result) {
            $empresa->update(['whatsapp_status' => 'disconnected', 'whatsapp_phone' => null]);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Error al eliminar la sesión.']);
    }
}
