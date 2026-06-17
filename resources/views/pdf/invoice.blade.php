<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $invoice->tipo_label }} {{ $invoice->numero }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-logo {
            width: 50%;
            vertical-align: top;
        }
        .header-logo .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #10b981;
        }
        .header-info {
            width: 50%;
            text-align: right;
            vertical-align: top;
        }
        .invoice-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
            margin-bottom: 5px;
        }
        .invoice-number {
            font-size: 14px;
            color: #ef4444;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details-col {
            width: 50%;
            vertical-align: top;
            border: 1px solid #e5e7eb;
            padding: 10px;
            border-radius: 8px;
        }
        .details-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            color: #6b7280;
            margin-bottom: 5px;
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 3px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px;
            border-bottom: 2px solid #e5e7eb;
            text-align: left;
        }
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        .totals-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .totals-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f3f4f6;
        }
        .totals-table .totals-label {
            color: #6b7280;
            font-weight: bold;
        }
        .totals-table .totals-value {
            text-align: right;
            font-weight: bold;
        }
        .totals-table .total-row {
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            border-bottom: 2px solid #e5e7eb;
            font-size: 12px;
        }
        .totals-table .total-row td {
            color: #111827;
        }
        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            color: #9ca3af;
            font-size: 8px;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="header-logo">
                <div class="company-name">{{ $empresa ? $empresa->razon_social : 'EMPRESA DEMO' }}</div>
                <div style="margin-top: 5px; color: #6b7280;">
                    Documento/RIF: {{ $empresa ? $empresa->documento : 'J-00000000-0' }}<br>
                    Dirección: {{ $empresa ? $empresa->direccion : 'Dirección Fiscal' }}<br>
                    Teléfono: {{ $empresa ? $empresa->telefono : 'N/A' }} | Email: {{ $empresa ? $empresa->email : 'N/A' }}
                </div>
            </td>
            <td class="header-info">
                <div class="invoice-title">{{ $invoice->tipo_label }}</div>
                <div class="invoice-number">{{ $invoice->numero }}</div>
                <div style="color: #6b7280;">
                    Fecha de Emisión: {{ $invoice->fecha_emision ? $invoice->fecha_emision->format('d/m/Y') : now()->format('d/m/Y') }}<br>
                    Estado: <span style="text-transform: uppercase; font-weight: bold; color: {{ $invoice->estado === 'emitida' ? '#10b981' : '#ef4444' }}">{{ $invoice->estado }}</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="details-table">
        <tr>
            <td class="details-col" style="padding-right: 10px;">
                <div class="details-title">Cliente</div>
                <strong>{{ $customer ? $customer->nombre : 'Cliente Genérico' }}</strong><br>
                Documento/Razón: {{ $customer ? $customer->documento : 'V-00000000-0' }}<br>
                Teléfono: {{ $customer ? $customer->telefono : 'N/A' }}<br>
                Email: {{ $customer ? $customer->email : 'N/A' }}<br>
                Dirección: {{ $customer ? $customer->direccion : 'N/A' }}
            </td>
            <td class="details-col" style="padding-left: 10px;">
                <div class="details-title">Información Adicional</div>
                Origen/Pedido: {{ $invoice->order ? $invoice->order->numero : 'Venta Directa' }}<br>
                Moneda Base: {{ config('app.currency', 'USD') }}<br>
                Sucursal: {{ $sucursal ? $sucursal->nombre : 'Sede Central' }}<br>
                Notas: {{ $invoice->notas ?: 'Sin observaciones adicionales.' }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">SKU</th>
                <th style="width: 45%;">Producto</th>
                <th class="text-center" style="width: 10%;">Cant</th>
                <th class="text-right" style="width: 12%;">P. Unit</th>
                <th class="text-right" style="width: 13%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->sku ?: 'N/A' }}</td>
                    <td>{{ $item->nombre_producto }}</td>
                    <td class="text-center">{{ $item->cantidad }}</td>
                    <td class="text-right">${{ number_format($item->precio_unitario, 2) }}</td>
                    <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td class="totals-label">Subtotal</td>
            <td class="totals-value">${{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="totals-label">Impuesto (16%)</td>
            <td class="totals-value">${{ number_format($invoice->impuesto, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td class="totals-label">Total</td>
            <td class="totals-value">${{ number_format($invoice->total, 2) }}</td>
        </tr>
    </table>

    @if(config('app.currency') === 'VES' || (isset($invoice->order) && $invoice->order->metodo_pago === 'transferencia'))
        <div style="clear: both; margin-top: 40px; padding: 10px; border: 1px dashed #ccc; border-radius: 6px; width: 50%;">
            <strong style="font-size: 10px; text-transform: uppercase;">Instrucciones de Pago</strong>
            <p style="margin: 5px 0 0 0; color: #6b7280; font-size: 10px;">
                Para transferencias nacionales o Zelle, favor reportar su pago adjuntando la referencia correcta en su panel de cliente o al correo de soporte.
            </p>
        </div>
    @endif

    <div class="footer">
        {{ $empresa ? $empresa->razon_social : 'EMPRESA' }} · Sistema Laratail ERP · Emitido por {{ auth()->check() ? auth()->user()->name : 'Sistema' }}
    </div>

</body>
</html>
