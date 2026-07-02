<?php

namespace App\Livewire\Empleado\Pedido;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Show extends Component
{
    public Order $order;
    public string $estado = '';
    public string $numero_seguimiento = '';
    public bool $token_valido = true;

    public function mount(int $id, string $token): void
    {
        $this->order = Order::findOrFail($id);

        // Verificar que el token sea válido y no haya expirado
        if ($this->order->empleado_token !== $token || $this->order->empleado_token_expires_at < now()) {
            $this->token_valido = false;
            return;
        }

        $this->estado = $this->order->estado;
        $this->numero_seguimiento = $this->order->numero_seguimiento ?? '';
    }

    /**
         * Transiciones de estado permitidas para evitar cambios inválidos
         * @var array<string, array<int, string>>
         */
        protected array $transiciones_validas = [
            'asignado' => ['procesando', 'cancelado'],
            'procesando' => ['enviado'],
            'enviado' => ['entregado', 'devuelto'],
            'entregado' => [],
            'cancelado' => [],
        ];

        public function actualizarEstado(): void
        {
            if (!$this->token_valido) return;

            // Validar que la transición de estado es permitida
            if (!isset($this->transiciones_validas[$this->order->estado]) ||
                !in_array($this->estado, $this->transiciones_validas[$this->order->estado])) {
                session()->flash('error', 'No puedes cambiar el pedido de "'.$this->order->estado_label.'" a "'.$this->estado.'" directamente.');
                return;
            }

            $this->validate([
                'estado' => 'required|string|in:asignado,procesando,enviado,entregado,cancelado,devuelto',
                'numero_seguimiento' => 'nullable|string|max:255',
            ]);

        DB::beginTransaction();
        try {
            // Verificar si el estado cambió para enviar notificaciones
            $estado_cambio = $this->order->estado !== $this->estado;
            $nuevo_estado = $this->estado;

            $this->order->update([
                'estado' => $this->estado,
                'numero_seguimiento' => $this->numero_seguimiento ?: null,
                'fecha_envio' => $this->estado === 'enviado' ? now() : $this->order->fecha_envio,
                'fecha_entrega' => $this->estado === 'entregado' ? now() : $this->order->fecha_entrega,
            ]);

            DB::commit();

            // Enviar notificación al cliente si el estado cambió
            try {
                $customer = $this->order->customer;
                $whatsappService = new \App\Services\WhatsAppService($this->order->empresa_id);

                if ($estado_cambio && $customer) {
                    $telefono_cliente = preg_replace('/[^0-9]/', '', $customer->whatsapp ?? $customer->telefono ?? '');
                    if (!empty($telefono_cliente)) {
                        if (strlen($telefono_cliente) === 10) {
                            $telefono_cliente = '58' . $telefono_cliente;
                        }

                        // Mensajes según el nuevo estado (mantenemos la misma lógica que en el admin)
                        $estados_mensajes = [
                            'confirmado' => [
                                'titulo' => '✅ Pedido Confirmado',
                                'mensaje' => "Tu pedido ha sido confirmado y estamos preparándolo."
                            ],
                            'procesando' => [
                                'titulo' => '🔄 Pedido en Procesamiento',
                                'mensaje' => "Tu pedido está siendo preparado para envío."
                            ],
                            'enviado' => [
                                'titulo' => '🚚 Pedido Enviado',
                                'mensaje' => "¡Tu pedido ha sido enviado! " . ($this->numero_seguimiento ? "Número de seguimiento: *{$this->numero_seguimiento}*" : "Pronto lo recibirás.")
                            ],
                            'entregado' => [
                                'titulo' => '📦 Pedido Entregado',
                                'mensaje' => "¡Tu pedido ha sido entregado! Gracias por tu compra."
                            ],
                            'cancelado' => [
                                'titulo' => '❌ Pedido Cancelado',
                                'mensaje' => "Lamentablemente tu pedido ha sido cancelado. Contactanos para más información."
                            ],
                        ];

                        if (isset($estados_mensajes[$nuevo_estado])) {
                            $mensaje = "*{$estados_mensajes[$nuevo_estado]['titulo']}*\n\n";
                            $mensaje .= "Pedido: *{$this->order->numero}*\n";
                            $mensaje .= $estados_mensajes[$nuevo_estado]['mensaje'];

                            try {
                                $whatsappService->sendMessage($telefono_cliente, $mensaje);
                            } catch (\Exception $e) {
                                report($e);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                report($e);
            }

            session()->flash('message', 'Estado del pedido actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error al actualizar el pedido.');
            report($e);
        }
    }

    public function getUbicacionMapsProperty(): ?string
    {

        if ($this->order->latitud && $this->order->longitud) {
            return "https://www.google.com/maps?q={$this->order->latitud},{$this->order->longitud}";
        }

        if ($this->order->direccion_envio && $this->order->ciudad_envio) {
            $direccion = urlencode("{$this->order->direccion_envio}, {$this->order->ciudad_envio}");
            return "https://www.google.com/maps/search/{$direccion}";
        }

        return null;
    }

    public function render()
    {
        return view('livewire.empleado.pedido.show')
            ->layout('components.layouts.guest');
    }
}
