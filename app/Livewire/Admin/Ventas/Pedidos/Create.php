<?php

namespace App\Livewire\Admin\Ventas\Pedidos;

use App\Models\Customer;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pais;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Crear Pedido')]
class Create extends Component
{
    // Customer
    public ?int $customer_id = null;
    public string $customerSearch = '';

    // Items
    public array $items = [];
    public string $productSearch = '';
    public ?int $selectedProductId = null;
    public int $addItemCantidad = 1;

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
    public string $estado = 'pendiente';
    public string $estado_pago = 'pendiente';

    protected array $itemRules = [
        'items.*.nombre' => 'required|string',
        'items.*.cantidad' => 'required|integer|min:1',
        'items.*.precio_unitario' => 'required|numeric|min:0',
        'items.*.descuento' => 'nullable|numeric|min:0',
        'items.*.impuesto' => 'nullable|numeric|min:0',
    ];

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

    public function updatedItems(): void
    {
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
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'estado' => 'required|string',
            'estado_pago' => 'required|string',
            'metodo_pago' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::create([
                'numero' => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'customer_id' => $this->customer_id,
                'user_id' => auth()->id(),
                'tipo' => 'venta',
                'estado' => $this->estado,
                'estado_pago' => $this->estado_pago,
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
            session()->flash('success', 'Pedido creado correctamente: ' . $order->numero);
            $this->redirect(route('admin.pedidos'), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al crear el pedido: ' . $e->getMessage());
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

        return view('livewire.admin.ventas.pedidos.create', [
            'customers' => $customers,
            'products' => $products,
            'paises' => $paises,
        ]);
    }
}
