<?php

namespace App\Livewire\Admin\Ventas\Cotizaciones;

use App\Livewire\Admin\Ventas\Pedidos\Create as PedidosCreate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Pais;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Crear Cotización')]
class Create extends PedidosCreate
{
    public function mount(): void
    {
        $this->estado = 'borrador';
    }

    public function save(): void
    {
        $this->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::create([
                'numero' => 'COT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'customer_id' => $this->customer_id,
                'user_id' => auth()->id(),
                'tipo' => 'cotizacion',
                'estado' => $this->estado,
                'estado_pago' => 'pendiente',
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
                'notas_cliente' => $this->notas_cliente ?: null,
                'notas_internas' => $this->notas_internas ?: null,
            ]);

            foreach ($this->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
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
            session()->flash('success', 'Cotización creada: ' . $order->numero);
            $this->redirect(route('admin.cotizaciones'), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error: ' . $e->getMessage());
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

        return view('livewire.admin.ventas.cotizaciones.create', [
            'customers' => $customers,
            'products' => $products,
            'paises' => $paises,
        ]);
    }
}
