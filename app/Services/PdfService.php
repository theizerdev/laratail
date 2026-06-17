<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    /**
     * Generar PDF para una Factura o Proforma (A4/Carta)
     */
    public static function generateInvoice(Invoice $invoice)
    {
        $invoice->load(['items', 'empresa', 'sucursal', 'order.customer']);
        
        $data = [
            'invoice' => $invoice,
            'empresa' => $invoice->empresa ?: (auth()->check() ? auth()->user()->empresa : null),
            'sucursal' => $invoice->sucursal ?: (auth()->check() ? auth()->user()->sucursal : null),
            'customer' => $invoice->order ? $invoice->order->customer : null,
            'items' => $invoice->items,
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data);
        
        // Formato carta/A4 vertical
        $pdf->setPaper('letter', 'portrait');
        
        return $pdf;
    }

    /**
     * Generar PDF para un Ticket POS (Formato térmico de 80mm)
     */
    public static function generateTicket(Order $order)
    {
        $order->load(['items', 'empresa', 'sucursal', 'customer']);

        $data = [
            'order' => $order,
            'empresa' => $order->empresa ?: (auth()->check() ? auth()->user()->empresa : null),
            'sucursal' => $order->sucursal ?: (auth()->check() ? auth()->user()->sucursal : null),
            'customer' => $order->customer,
            'items' => $order->items,
        ];

        $pdf = Pdf::loadView('pdf.ticket', $data);

        // Formato rollo térmico: 80mm de ancho (aprox 226pt) por largo dinámico o alto fijo de 1000pt
        // 80mm = 226.7pt, la altura la ponemos amplia (ej. 700pt)
        $pdf->setPaper([0, 0, 226.7, 700], 'portrait');

        return $pdf;
    }
}
