<?php

namespace App\Livewire\Admin\Pagos\Pagos;

use App\Models\Order;
use App\Models\Payment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Pagos')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterEstado = 'all';
    public string $filterMetodo = 'all';

    // Modal state
    public bool $showModal = false;
    public bool $showDetailModal = false;
    public ?int $detailId = null;
    public ?int $editingId = null;

    // Form fields
    public ?int $order_id = null;
    public float $amount = 0;
    public string $metodo_pago = 'efectivo';
    public string $referencia = '';
    public string $fecha_pago = '';
    public string $estado = 'completado';
    public string $notas = '';

    // Order search
    public string $orderSearch = '';
    public bool $showOrderDropdown = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterEstado(): void
    {
        $this->resetPage();
    }

    public function updatingFilterMetodo(): void
    {
        $this->resetPage();
    }

    public function searchOrders(): void
    {
        $this->showOrderDropdown = strlen($this->orderSearch) >= 2;
    }

    public function selectOrder(int $id): void
    {
        $order = Order::findOrFail($id);
        $this->order_id = $order->id;
        $this->orderSearch = $order->numero . ' — $' . number_format($order->total, 2);
        $this->showOrderDropdown = false;

        if (!$this->editingId) {
            $pendiente = $order->total - $order->payments()->where('estado', 'completado')->sum('amount');
            $this->amount = max(0, $pendiente);
        }
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $payment = Payment::findOrFail($id);
        $this->editingId = $payment->id;
        $this->order_id = $payment->order_id;
        $this->amount = (float) $payment->amount;
        $this->metodo_pago = $payment->metodo_pago;
        $this->referencia = $payment->referencia ?? '';
        $this->fecha_pago = $payment->fecha_pago?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i');
        $this->estado = $payment->estado;
        $this->notas = $payment->notas ?? '';
        $this->orderSearch = $payment->order?->numero ?? '';
        $this->showModal = true;
    }

    public function openDetail(int $id): void
    {
        $this->detailId = $id;
        $this->showDetailModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|string',
            'fecha_pago' => 'required',
            'estado' => 'required|in:pendiente,completado,fallido,reembolsado',
        ]);

        $data = [
            'order_id' => $this->order_id,
            'amount' => $this->amount,
            'metodo_pago' => $this->metodo_pago,
            'referencia' => $this->referencia,
            'fecha_pago' => $this->fecha_pago,
            'estado' => $this->estado,
            'notas' => $this->notas,
            'user_id' => auth()->id(),
        ];

        if ($this->editingId) {
            Payment::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Pago actualizado correctamente.');
        } else {
            Payment::create($data);
            session()->flash('success', 'Pago registrado correctamente.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->order_id = null;
        $this->amount = 0;
        $this->metodo_pago = 'efectivo';
        $this->referencia = '';
        $this->fecha_pago = now()->format('Y-m-d\TH:i');
        $this->estado = 'completado';
        $this->notas = '';
        $this->orderSearch = '';
    }

    public function render()
    {
        $query = Payment::with(['order.customer', 'user'])
            ->latest();

        if ($this->filterEstado !== 'all') {
            $query->where('estado', $this->filterEstado);
        }

        if ($this->filterMetodo !== 'all') {
            $query->where('metodo_pago', $this->filterMetodo);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('referencia', 'like', "%{$this->search}%")
                    ->orWhereHas('order', fn($q2) => $q2->where('numero', 'like', "%{$this->search}%"));
            });
        }

        $payments = $query->paginate(15);

        $stats = [
            'total_cobrado' => Payment::where('estado', 'completado')->sum('amount'),
            'total_pendiente' => Payment::where('estado', 'pendiente')->sum('amount'),
            'total_reembolsado' => Payment::where('estado', 'reembolsado')->sum('amount'),
            'total_pagos' => Payment::count(),
        ];

        return view('livewire.admin.pagos.pagos.index', [
            'payments' => $payments,
            'stats' => $stats,
            'detailPayment' => $this->detailId ? Payment::with(['order.customer', 'user'])->find($this->detailId) : null,
        ]);
    }
}
