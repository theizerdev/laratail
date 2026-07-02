<?php

namespace App\Livewire\Admin\Ventas\Pedidos;

use App\Models\Customer;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pais;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Editar Pedido')]
class Edit extends Component
{
    public Order $order;

    // Customer
    public ?int $customer_id = null;
    public string $customerSearch = '';

    // Items
    public array $items = [];

    // Totals
    public float $subtotal = 0;
    public float $descuento = 0;
    public float $impuesto = 0;
    public float $envio = 0;
    public float $total = 0;

    // Cupón
    public string $codigo_cupon = '';
    public ?int $coupon_id = null;

    // Envío
    public string $direccion_envio = '';
    public string $ciudad_envio = '';
    public string $estado_envio = '';
    public string $codigo_postal_envio = '';
    public ?int $pais_envio_id = null;
    public string $metodo_envio = '';

    // Pago
    public string $metodo_pago = '';
    public string $referencia_pago = '';

    // Notas
    public string $notas_cliente = '';
    public string $notas_internas = '';

    // Estado
    public string $estado = '';
    public string $estado_pago = '';
    public ?int $asignado_a = null;

    // Add item
    public string $productSearch = '';
    public ?int $selectedProductId = null;
    public int $addItemCantidad = 1;

    public function mount(int $id): void
    {
        $this->order = Order::findOrFail($id);
        $this->customer_id = $this->order->customer_id;
        $this->estado = $this->order->estado;
        $this->estado_pago = $this->order->estado_pago;
        $this->asignado_a = $this->order->asignado_a;
        $this->subtotal = (float) $this->order->subtotal;
        $this->descuento = (float) $this->order->descuento;
        $this->impuesto = (float) $this->order->impuesto;
        $this->envio = (float) $this->order->envio;
        $this->total = (float) $this->order->total;
        $this->coupon_id = $this->order->coupon_id;
        $this->codigo_cupon = $this->order->codigo_cupon ?? '';
        $this->direccion_envio = $this->order->direccion_envio ?? '';
        $this->ciudad_envio = $this->order->ciudad_envio ?? '';
        $this->estado_envio = $this->order->estado_envio ?? '';
        $this->codigo_postal_envio = $this->order->codigo_postal_envio ?? '';
        $this->pais_envio_id = $this->order->pais_envio_id;
        $this->metodo_envio = $this->order->metodo_envio ?? '';
        $this->metodo_pago = $this->order->metodo_pago ?? '';
        $this->referencia_pago = $this->order->referencia_pago ?? '';
        $this->notas_cliente = $this->order->notas_cliente ?? '';
        $this->notas_internas = $this->order->notas_internas ?? '';

        $this->items = $this->order->items->map(fn($item) => [
            'product_id' => $item->product_id,
            'product_variant_id' => $item->product_variant_id,
            'nombre' => $item->nombre_producto,
            'sku' => $item->sku,
            'cantidad' => $item->cantidad,
            'precio_unitario' => (float) $item->precio_unitario,
            'descuento' => (float) $item->descuento,
            'impuesto' => (float) $item->impuesto,
            'subtotal' => (float) $item->subtotal,
        ])->toArray();
    }

    public function addItem(): void
    {
        if (!$this->selectedProductId) return;

        $product = Product::with(['category', 'brand'])->find($this->selectedProductId);
        if (!$product) return;

        // Check if product already in cart → increment quantity
        foreach ($this->items as $index => $existing) {
            if (($existing['product_id'] ?? null) === $product->id && empty($existing['product_variant_id'])) {
                $this->items[$index]['cantidad'] += $this->addItemCantidad;
                $this->items[$index]['subtotal'] = $this->items[$index]['cantidad'] * $this->items[$index]['precio_unitario']
                    - ($this->items[$index]['descuento'] ?? 0) + ($this->items[$index]['impuesto'] ?? 0);
                $this->selectedProductId = null;
                $this->addItemCantidad = 1;
                $this->productSearch = '';
                $this->recalcular();
                return;
            }
        }

        $this->items[] = [
            'product_id' => $product->id,
            'product_variant_id' => null,
            'nombre' => $product->nombre,
            'sku' => $product->sku,
            'imagen' => $product->imagen_principal,
            'categoria' => $product->category?->nombre ?? '',
            'marca' => $product->brand?->nombre ?? '',
            'stock' => $product->stock_total,
            'cantidad' => $this->addItemCantidad,
            'precio_unitario' => $product->precio_final,
            'precio_original' => (float) $product->precio,
            'descuento' => 0,
            'impuesto' => 0,
            'subtotal' => $this->addItemCantidad * $product->precio_final,
        ];

        $this->selectedProductId = null;
        $this->addItemCantidad = 1;
        $this->productSearch = '';
        $this->recalcular();
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->recalcular();
    }

    public function incrementItem(int $index): void
    {
        if (!isset($this->items[$index])) return;
        $this->items[$index]['cantidad']++;
        $this->recalcular();
    }

    public function decrementItem(int $index): void
    {
        if (!isset($this->items[$index])) return;
        if ($this->items[$index]['cantidad'] <= 1) {
            $this->removeItem($index);
            return;
        }
        $this->items[$index]['cantidad']--;
        $this->recalcular();
    }

    public function recalcular(): void
    {
        foreach ($this->items as $i => $item) {
            $this->items[$i]['subtotal'] = ($item['cantidad'] * $item['precio_unitario']) - ($item['descuento'] ?? 0) + ($item['impuesto'] ?? 0);
        }
        $this->subtotal = collect($this->items)->sum(fn($i) => ($i['cantidad'] * $i['precio_unitario']) - ($i['descuento'] ?? 0) + ($i['impuesto'] ?? 0));
        $this->total = max(0, $this->subtotal + $this->envio - $this->descuento);
    }

    public function aplicarCupon(): void
    {
        $coupon = Coupon::where('codigo', $this->codigo_cupon)->first();
        if ($coupon && $coupon->es_valido) {
            $this->coupon_id = $coupon->id;
            $this->descuento = $coupon->calcularDescuento($this->subtotal);
            $this->recalcular();
            session()->flash('cupon_msg', 'Cupón aplicado: -' . number_format($this->descuento, 2));
        } else {
            session()->flash('cupon_error', 'Cupón inválido o expirado.');
        }
    }

    public function save(): void
    {
        $this->validate([
            'items' => 'required|array|min:1',
            'estado' => 'required|string',
            'estado_pago' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            // Verificar si el estado cambió para enviar notificaciones
            $estado_cambio = $this->order->estado !== $this->estado;
            $nuevo_estado = $this->estado;
            
            // Verificar si se asignó un empleado
            $empleado_asignado = $this->order->asignado_a !== $this->asignado_a && $this->asignado_a !== null;
            $empleado = \App\Models\User::find($this->asignado_a);

            $this->order->update([
                'customer_id' => $this->customer_id,
                'estado' => $this->estado,
                'estado_pago' => $this->estado_pago,
                'asignado_a' => $this->asignado_a,
                'subtotal' => $this->subtotal,
                'descuento' => $this->descuento,
                'impuesto' => $this->impuesto,
                'envio' => $this->envio,
                'total' => $this->total,
                'coupon_id' => $this->coupon_id,
                'codigo_cupon' => $this->codigo_cupon ?: null,
                'direccion_envio' => $this->direccion_envio ?: null,
                'ciudad_envio' => $this->ciudad_envio ?: null,
                'estado_envio' => $this->estado_envio ?: null,
                'codigo_postal_envio' => $this->codigo_postal_envio ?: null,
                'pais_envio_id' => $this->pais_envio_id,
                'metodo_envio' => $this->metodo_envio ?: null,
                'metodo_pago' => $this->metodo_pago ?: null,
                'referencia_pago' => $this->referencia_pago ?: null,
                'notas_cliente' => $this->notas_cliente ?: null,
                'notas_internas' => $this->notas_internas ?: null,
            ]);

            // Rebuild items
            $this->order->items()->delete();
            foreach ($this->items as $item) {
                OrderItem::create([
                    'order_id' => $this->order->id,
                    'product_id' => $item['product_id'] ?? null,
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'nombre_producto' => $item['nombre'],
                    'sku' => $item['sku'] ?? null,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'descuento' => $item['descuento'] ?? 0,
                    'impuesto' => $item['impuesto'] ?? 0,
                    'subtotal' => ($item['cantidad'] * $item['precio_unitario']) - ($item['descuento'] ?? 0) + ($item['impuesto'] ?? 0),
                ]);
            }

            DB::commit();
            
            // Enviar notificaciones si el estado cambió o se asignó un empleado
            try {
                $customer = $this->order->customer;
                $whatsappService = new \App\Services\WhatsAppService($this->order->empresa_id);
                
                // Si se asignó un empleado, notificarlo
                if ($empleado_asignado && $empleado) {
                    // Obtener teléfono del empleado
                    $telefono_empleado = preg_replace('/[^0-9]/', '', $empleado->telefono ?? '');
                    if (!empty($telefono_empleado)) {
                        if (strlen($telefono_empleado) === 10) {
                            $telefono_empleado = '58' . $telefono_empleado;
                        }
                        
                        // Mensaje para el empleado asignado
                        $mensaje_empleado = "🔔 *NUEVO PEDIDO ASIGNADO*\n\n";
                        $mensaje_empleado .= "Has sido asignado al pedido *{$this->order->numero}*\n\n";
                        $mensaje_empleado .= "📋 Detalles:\n";
                        $mensaje_empleado .= "• Cliente: " . ($customer?->nombre_completo ?? 'Cliente no registrado') . "\n";
                        $mensaje_empleado .= "• Total: $" . number_format($this->order->total, 2) . "\n";
                        $mensaje_empleado .= "• Dirección: {$this->order->direccion_envio}, {$this->order->ciudad_envio}\n";
                        $mensaje_empleado .= "\n⏰ Por favor, procesa este pedido a la brevedad.";
                        
                        try {
                            $whatsappService->sendMessage($telefono_empleado, $mensaje_empleado);
                        } catch (\Exception $e) {
                            report($e);
                        }
                    }
                }
                
                // Si el estado cambió, notificar al cliente
                if ($estado_cambio && $customer) {
                    $telefono_cliente = preg_replace('/[^0-9]/', '', $customer->whatsapp ?? $customer->telefono ?? '');
                    if (!empty($telefono_cliente)) {
                        if (strlen($telefono_cliente) === 10) {
                            $telefono_cliente = '58' . $telefono_cliente;
                        }
                        
                        // Mensajes según el nuevo estado
                        $estados_mensajes = [
                            'confirmado' => [
                                'titulo' => '✅ Pedido Confirmado',
                                'mensaje' => "Tu pedido ha sido confirmado y estamos preparándolo."
                            ],
                            'procesando' => [
                                'titulo' => '🔄 Pedido en Proceso',
                                'mensaje' => "Estamos procesando tu pedido para enviarlo pronto."
                            ],
                            'enviado' => [
                                'titulo' => '🚚 Pedido Enviado',
                                'mensaje' => "¡Tu pedido ha sido enviado! Pronto lo recibirás."
                            ],
                            'entregado' => [
                                'titulo' => '🎉 Pedido Entregado',
                                'mensaje' => "Tu pedido ha sido entregado exitosamente. ¡Gracias por tu compra!"
                            ],
                            'cancelado' => [
                                'titulo' => '❌ Pedido Cancelado',
                                'mensaje' => "Lamentamos informarte que tu pedido ha sido cancelado. Contactanos para más información."
                            ]
                        ];
                        
                        if (isset($estados_mensajes[$nuevo_estado])) {
                            $info_estado = $estados_mensajes[$nuevo_estado];
                            $mensaje_cliente = "{$info_estado['titulo']}\n\n";
                            $mensaje_cliente .= "Hola *{$customer->nombre_completo}*,\n\n";
                            $mensaje_cliente .= "{$info_estado['mensaje']}\n\n";
                            $mensaje_cliente .= "📋 Pedido: *{$this->order->numero}*\n";
                            $mensaje_cliente .= "💰 Total: $" . number_format($this->order->total, 2) . "\n";
                            $mensaje_cliente .= "\nSi tienes alguna pregunta, no dudes en contactarnos.";
                            
                            try {
                                $whatsappService->sendMessage($telefono_cliente, $mensaje_cliente);
                            } catch (\Exception $e) {
                                report($e);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                report($e);
            }
            
            session()->flash('success', 'Pedido actualizado correctamente.');
            $this->redirect(route('admin.pedidos'), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al actualizar el pedido: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $customers = Customer::where('activo', true)
            ->when($this->customerSearch, fn($q) => $q->where('nombre', 'like', "%{$this->customerSearch}%"))
            ->limit(10)->get();

        $products = Product::with(['category', 'brand'])
            ->where('status', true)
            ->when($this->productSearch, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nombre', 'like', "%{$this->productSearch}%")
                        ->orWhere('sku', 'like', "%{$this->productSearch}%")
                        ->orWhereHas('category', fn($c) => $c->where('nombre', 'like', "%{$this->productSearch}%"))
                        ->orWhereHas('brand', fn($b) => $b->where('nombre', 'like', "%{$this->productSearch}%"));
                });
            })
            ->limit(8)->get();

        $paises = Pais::orderBy('nombre')->get();
        $empleados = \App\Models\User::where('activo', true)->orderBy('name')->get(['id', 'name', 'telefono']);

        return view('livewire.admin.ventas.pedidos.edit', [
            'customers' => $customers,
            'products' => $products,
            'paises' => $paises,
            'empleados' => $empleados,
        ]);
    }
}