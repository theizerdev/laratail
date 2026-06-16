<?php

namespace App\Livewire\Admin\Inventario\OrdenesCompra;

use App\Models\InventoryMovement;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Recibir Mercancía')]
class Recibir extends Component
{
    public int $orderId;
    public array $recepcionItems = [];
    public string $notas = '';

    public function mount(int $id): void
    {
        $this->orderId = $id;
        $order = PurchaseOrder::with('items')->findOrFail($id);

        if (!$order->pendiente_recepcion) {
            session()->flash('error', 'Esta orden no está pendiente de recepción.');
            $this->redirect(route('admin.ordenes-compra.edit', $id), navigate: true);
            return;
        }

        $this->recepcionItems = $order->items->map(fn($item) => [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'nombre_producto' => $item->nombre_producto,
            'sku' => $item->sku,
            'cantidad_pedida' => $item->cantidad_pedida,
            'cantidad_recibida_previa' => $item->cantidad_recibida,
            'pendiente' => $item->pendiente,
            'cantidad_recibir' => $item->pendiente,
        ])->toArray();
    }

    public function recibirTodo(): void
    {
        foreach ($this->recepcionItems as &$item) {
            $item['cantidad_recibir'] = $item['pendiente'];
        }
    }

    public function limpiarTodo(): void
    {
        foreach ($this->recepcionItems as &$item) {
            $item['cantidad_recibir'] = 0;
        }
    }

    public function confirmarRecepcion(): void
    {
        $hasReceived = false;
        foreach ($this->recepcionItems as $item) {
            if ($item['cantidad_recibir'] > 0) {
                $hasReceived = true;
                break;
            }
        }

        if (!$hasReceived) {
            session()->flash('error', 'Debes recibir al menos un producto.');
            return;
        }

        try {
            DB::transaction(function () {
                $order = PurchaseOrder::with('items')->findOrFail($this->orderId);

                foreach ($this->recepcionItems as $recvItem) {
                    if ($recvItem['cantidad_recibir'] <= 0) continue;

                    $poItem = $order->items()->findOrFail($recvItem['id']);
                    $newReceived = $poItem->cantidad_recibida + $recvItem['cantidad_recibir'];
                    $poItem->update(['cantidad_recibida' => min($newReceived, $poItem->cantidad_pedida)]);

                    // Create inventory movement
                    InventoryMovement::registrar([
                        'product_id' => $poItem->product_id,
                        'tipo' => 'entrada',
                        'cantidad' => $recvItem['cantidad_recibir'],
                        'costo_unitario' => $poItem->costo_unitario,
                        'referencia' => $order->numero,
                        'motivo' => 'Recepción de Orden de Compra' . ($this->notas ? ': ' . $this->notas : ''),
                    ]);
                }

                // Update order status
                $order->refresh();
                $allReceived = $order->items->every(fn($i) => $i->completado);
                $anyReceived = $order->items->some(fn($i) => $i->cantidad_recibida > 0);

                if ($allReceived) {
                    $order->cambiarEstado('recibida');
                } elseif ($anyReceived) {
                    $order->cambiarEstado('recibida_parcial');
                }
            });

            session()->flash('success', 'Mercancía recibida correctamente.');
            $this->redirect(route('admin.ordenes-compra.edit', $this->orderId), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al recibir mercancía: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $order = PurchaseOrder::with(['supplier', 'items'])->findOrFail($this->orderId);

        $totalRecibir = collect($this->recepcionItems)->sum('cantidad_recibir');

        return view('livewire.admin.inventario.ordenes-compra.recibir', [
            'order' => $order,
            'totalRecibir' => $totalRecibir,
        ]);
    }
}
