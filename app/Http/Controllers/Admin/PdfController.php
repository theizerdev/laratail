<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\PdfService;

class PdfController extends Controller
{
    /**
     * Descargar PDF de Factura
     */
    public function downloadInvoice(int $id)
    {
        $invoice = Invoice::findOrFail($id);
        
        // El middleware global ya aplica la configuración regional correspondiente.
        $pdf = PdfService::generateInvoice($invoice);
        
        return $pdf->download("{$invoice->numero}.pdf");
    }

    /**
     * Descargar o ver PDF de Ticket POS (80mm)
     */
    public function downloadTicket(int $id)
    {
        $order = Order::findOrFail($id);

        $pdf = PdfService::generateTicket($order);

        // Se retorna stream para que el navegador lo abra directamente y facilite la impresión en impresora térmica
        return $pdf->stream("TICKET-{$order->numero}.pdf");
    }
}
