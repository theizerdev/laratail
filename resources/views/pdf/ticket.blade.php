<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket {{ $order->numero }}</title>
    <style>
        @page {
            margin: 8px;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 9px;
            color: #000;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .bold {
            font-weight: bold;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .info-table, .items-table, .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td, .items-table td, .totals-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .items-table th {
            text-align: left;
            font-weight: bold;
            border-bottom: 1px dashed #000;
            padding-bottom: 2px;
        }
        .footer {
            margin-top: 15px;
            font-size: 8px;
        }
    </style>
</head>
<body>

    <div class="text-center">
        <div class="title">{{ $empresa ? $empresa->razon_social : 'EMPRESA DEMO' }}</div>
        <div>RIF: {{ $empresa ? $empresa->documento : 'J-00000000-0' }}</div>
        <div>Tlf: {{ $empresa ? $empresa->telefono : 'N/A' }}</div>
        <div>{{ $sucursal ? $sucursal->nombre : 'Sede Central' }}</div>
    </div>

    <div class="divider"></div>

    <table class="info-table">
        <tr>
            <td class="bold">Ticket:</td>
            <td class="text-right">{{ $order->numero }}</td>
        </tr>
        <tr>
            <td class="bold">Fecha:</td>
            <td class="text-right">{{ $order->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="bold">Cajero:</td>
            <td class="text-right">{{ $order->user ? $order->user->name : 'POS' }}</td>
        </tr>
        @if($customer)
            <tr>
                <td class="bold">Cliente:</td>
                <td class="text-right">{{ $customer->nombre }}</td>
            </tr>
            <tr>
                <td class="bold">Doc:</td>
                <td class="text-right">{{ $customer->documento }}</td>
            </tr>
        @endif
    </table>

    <div class="divider"></div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Detalle</th>
                <th class="text-center" style="width: 15%;">Cant</th>
                <th class="text-right" style="width: 25%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->nombre_producto }}</td>
                    <td class="text-center">{{ $item->cantidad }}</td>
                    <td class="text-right">${{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table class="totals-table">
        <tr>
            <td>Subtotal:</td>
            <td class="text-right">${{ number_format($order->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>Descuento:</td>
            <td class="text-right">-${{ number_format($order->descuento, 2) }}</td>
        </tr>
        <tr>
            <td>Impuesto (16%):</td>
            <td class="text-right">${{ number_format($order->impuesto, 2) }}</td>
        </tr>
        <tr class="bold">
            <td>TOTAL:</td>
            <td class="text-right">${{ number_format($order->total, 2) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="info-table">
        <tr>
            <td class="bold">Método Pago:</td>
            <td class="text-right" style="text-transform: uppercase;">{{ str_replace('_', ' ', $order->metodo_pago ?: 'efectivo') }}</td>
        </tr>
        @if($order->referencia_pago)
            <tr>
                <td class="bold">Ref:</td>
                <td class="text-right">{{ $order->referencia_pago }}</td>
            </tr>
        @endif
    </table>

    <div class="divider"></div>

    <div class="text-center footer">
        <span class="bold">¡GRACIAS POR SU COMPRA!</span><br>
        Conserve su ticket para cambios.<br>
        Desarrollado por Laratail ERP.
    </div>

</body>
</html>
