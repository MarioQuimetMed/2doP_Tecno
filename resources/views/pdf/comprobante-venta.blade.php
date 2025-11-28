<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Venta - {{ $numero_comprobante }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 5px;
        }
        .subtitle {
            color: #666;
            font-size: 14px;
        }
        .comprobante-numero {
            background: #4f46e5;
            color: white;
            padding: 10px 20px;
            display: inline-block;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 16px;
            font-weight: bold;
        }
        .fecha-emision {
            margin-top: 10px;
            color: #666;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background: #f3f4f6;
            padding: 8px 12px;
            font-weight: bold;
            color: #374151;
            border-left: 4px solid #4f46e5;
            margin-bottom: 10px;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            padding: 5px 10px;
            width: 40%;
            color: #666;
            border-bottom: 1px solid #eee;
        }
        .info-value {
            display: table-cell;
            padding: 5px 10px;
            font-weight: 500;
            border-bottom: 1px solid #eee;
        }
        .viaje-card {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .viaje-titulo {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .viaje-destino {
            opacity: 0.9;
            margin-bottom: 15px;
        }
        .viaje-detalles {
            display: table;
            width: 100%;
        }
        .viaje-detalle {
            display: table-cell;
            text-align: center;
            padding: 10px;
        }
        .viaje-detalle-label {
            opacity: 0.8;
            font-size: 10px;
            text-transform: uppercase;
        }
        .viaje-detalle-value {
            font-size: 14px;
            font-weight: bold;
        }
        .totales {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        .total-label {
            display: table-cell;
            width: 70%;
        }
        .total-value {
            display: table-cell;
            text-align: right;
            font-weight: 500;
        }
        .total-final {
            border-top: 2px solid #4f46e5;
            padding-top: 10px;
            margin-top: 10px;
        }
        .total-final .total-label,
        .total-final .total-value {
            font-size: 16px;
            font-weight: bold;
            color: #4f46e5;
        }
        .estado-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .estado-pendiente { background: #fef3c7; color: #92400e; }
        .estado-parcial { background: #dbeafe; color: #1e40af; }
        .estado-completado { background: #d1fae5; color: #065f46; }
        .estado-anulado { background: #fee2e2; color: #991b1b; }
        .pagos-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .pagos-table th {
            background: #f3f4f6;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #6b7280;
        }
        .pagos-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        .footer p {
            margin-bottom: 5px;
        }
        .qr-code {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Tendencias Tours SRL</div>
        <div class="subtitle">Agencia de Viajes y Turismo</div>
        <div class="comprobante-numero">{{ $numero_comprobante }}</div>
        <div class="fecha-emision">Emitido el {{ $fecha_emision }}</div>
    </div>

    <!-- Información del Viaje -->
    <div class="viaje-card">
        <div class="viaje-titulo">{{ $venta->viaje->planViaje->nombre }}</div>
        <div class="viaje-destino">
            {{ $venta->viaje->planViaje->destino->nombre_lugar }}, 
            {{ $venta->viaje->planViaje->destino->ciudad }}, 
            {{ $venta->viaje->planViaje->destino->pais }}
        </div>
        <div class="viaje-detalles">
            <div class="viaje-detalle">
                <div class="viaje-detalle-label">Fecha Salida</div>
                <div class="viaje-detalle-value">{{ $venta->viaje->fecha_salida->format('d/m/Y') }}</div>
            </div>
            <div class="viaje-detalle">
                <div class="viaje-detalle-label">Fecha Retorno</div>
                <div class="viaje-detalle-value">{{ $venta->viaje->fecha_retorno->format('d/m/Y') }}</div>
            </div>
            <div class="viaje-detalle">
                <div class="viaje-detalle-label">Duración</div>
                <div class="viaje-detalle-value">{{ $venta->viaje->planViaje->duracion_dias }} días</div>
            </div>
        </div>
    </div>

    <!-- Información del Cliente -->
    <div class="section">
        <div class="section-title">Datos del Cliente</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nombre Completo:</div>
                <div class="info-value">{{ $venta->cliente->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $venta->cliente->email }}</div>
            </div>
            @if($venta->cliente->ci_nit)
            <div class="info-row">
                <div class="info-label">CI / NIT:</div>
                <div class="info-value">{{ $venta->cliente->ci_nit }}</div>
            </div>
            @endif
            @if($venta->cliente->telefono)
            <div class="info-row">
                <div class="info-label">Teléfono:</div>
                <div class="info-value">{{ $venta->cliente->telefono }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Información de la Venta -->
    <div class="section">
        <div class="section-title">Detalle de la Venta</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Fecha de Venta:</div>
                <div class="info-value">{{ $venta->fecha_venta->format('d/m/Y H:i') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Vendedor:</div>
                <div class="info-value">{{ $venta->vendedor->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tipo de Pago:</div>
                <div class="info-value">{{ $venta->tipo_pago->label() }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Estado:</div>
                <div class="info-value">
                    <span class="estado-badge estado-{{ strtolower($venta->estado_pago->value) }}">
                        {{ $venta->estado_pago->label() }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Totales -->
    <div class="totales">
        <div class="total-row">
            <div class="total-label">Precio por persona:</div>
            <div class="total-value">${{ number_format($venta->viaje->planViaje->precio_base, 2) }}</div>
        </div>
        <div class="total-row">
            <div class="total-label">Cantidad de personas:</div>
            <div class="total-value">{{ intval($venta->monto_total / $venta->viaje->planViaje->precio_base) }}</div>
        </div>
        <div class="total-row total-final">
            <div class="total-label">TOTAL A PAGAR:</div>
            <div class="total-value">${{ number_format($venta->monto_total, 2) }}</div>
        </div>
        <div class="total-row">
            <div class="total-label">Monto Pagado:</div>
            <div class="total-value" style="color: #059669;">${{ number_format($venta->montoPagado(), 2) }}</div>
        </div>
        <div class="total-row">
            <div class="total-label">Saldo Pendiente:</div>
            <div class="total-value" style="color: #dc2626;">${{ number_format($venta->saldoPendiente(), 2) }}</div>
        </div>
    </div>

    <!-- Historial de Pagos -->
    @if($venta->pagos->count() > 0)
    <div class="section" style="margin-top: 20px;">
        <div class="section-title">Historial de Pagos</div>
        <table class="pagos-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Método</th>
                    <th>Referencia</th>
                    <th style="text-align: right;">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->pagos as $pago)
                <tr>
                    <td>{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : 'Pendiente' }}</td>
                    <td>{{ $pago->metodo_pago->label() }}</td>
                    <td>{{ $pago->referencia_comprobante ?? '-' }}</td>
                    <td style="text-align: right; font-weight: bold;">${{ number_format($pago->monto_pagado, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p><strong>Tendencias Tours SRL</strong></p>
        <p>Dirección: Av. Principal #123, Santa Cruz de la Sierra, Bolivia</p>
        <p>Teléfono: +591 3 333 4444 | Email: info@tendenciastours.com</p>
        <p style="margin-top: 10px;">Este documento es un comprobante válido de su transacción.</p>
        <p>Gracias por confiar en nosotros para su viaje.</p>
    </div>
</body>
</html>
