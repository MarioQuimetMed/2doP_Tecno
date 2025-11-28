<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pago - {{ $numero_comprobante }}</title>
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
            border-bottom: 2px solid #059669;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 5px;
        }
        .subtitle {
            color: #666;
            font-size: 14px;
        }
        .comprobante-numero {
            background: #059669;
            color: white;
            padding: 10px 20px;
            display: inline-block;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 16px;
            font-weight: bold;
        }
        .tipo-comprobante {
            color: #059669;
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            text-transform: uppercase;
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
            border-left: 4px solid #059669;
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
        .pago-card {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .pago-monto {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .pago-metodo {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        .pago-referencia {
            font-size: 14px;
            opacity: 0.8;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
        }
        .pago-fecha {
            font-size: 12px;
            opacity: 0.7;
            margin-top: 10px;
        }
        .success-icon {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-icon::before {
            content: "✓";
            font-size: 32px;
            font-weight: bold;
        }
        .detalles-venta {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        .detalle-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .detalle-label {
            display: table-cell;
            width: 50%;
            color: #6b7280;
        }
        .detalle-value {
            display: table-cell;
            text-align: right;
            font-weight: 500;
        }
        .cuota-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 12px;
            border-radius: 6px;
            margin-top: 15px;
        }
        .cuota-info-title {
            color: #1e40af;
            font-weight: bold;
            margin-bottom: 8px;
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
        .sello-pagado {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            border: 4px solid rgba(5, 150, 105, 0.3);
            color: rgba(5, 150, 105, 0.3);
            padding: 10px 30px;
            font-size: 36px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 10px;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Tendencias Tours SRL</div>
        <div class="subtitle">Agencia de Viajes y Turismo</div>
        <div class="tipo-comprobante">Comprobante de Pago</div>
        <div class="comprobante-numero">{{ $numero_comprobante }}</div>
        <div class="fecha-emision">Emitido el {{ $fecha_emision }}</div>
    </div>

    <!-- Detalle del Pago -->
    <div class="pago-card">
        <div class="success-icon"></div>
        <div class="pago-monto">${{ number_format($pago->monto_pagado, 2) }}</div>
        <div class="pago-metodo">{{ $pago->metodo_pago->label() }}</div>
        @if($pago->referencia_comprobante)
        <div class="pago-referencia">
            Referencia: {{ $pago->referencia_comprobante }}
        </div>
        @endif
        <div class="pago-fecha">
            Fecha de pago: {{ $pago->fecha_pago->format('d/m/Y H:i:s') }}
        </div>
    </div>

    <!-- Información del Cliente -->
    <div class="section">
        <div class="section-title">Datos del Cliente</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nombre Completo:</div>
                <div class="info-value">{{ $pago->venta->cliente->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $pago->venta->cliente->email }}</div>
            </div>
            @if($pago->venta->cliente->ci_nit)
            <div class="info-row">
                <div class="info-label">CI / NIT:</div>
                <div class="info-value">{{ $pago->venta->cliente->ci_nit }}</div>
            </div>
            @endif
            @if($pago->venta->cliente->telefono)
            <div class="info-row">
                <div class="info-label">Teléfono:</div>
                <div class="info-value">{{ $pago->venta->cliente->telefono }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Información de la Venta -->
    <div class="section">
        <div class="section-title">Información del Viaje</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Viaje:</div>
                <div class="info-value">{{ $pago->venta->viaje->planViaje->nombre ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Destino:</div>
                <div class="info-value">
                    {{ $pago->venta->viaje->planViaje->destino->nombre_lugar ?? 'N/A' }}, 
                    {{ $pago->venta->viaje->planViaje->destino->ciudad ?? '' }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha de Salida:</div>
                <div class="info-value">{{ $pago->venta->viaje->fecha_salida->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Nº de Venta:</div>
                <div class="info-value">TT-{{ str_pad($pago->venta->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>
    </div>

    <!-- Si es pago de cuota específica -->
    @if($pago->cuota)
    <div class="cuota-info">
        <div class="cuota-info-title">Pago de Cuota</div>
        <div class="detalle-row">
            <div class="detalle-label">Número de Cuota:</div>
            <div class="detalle-value">{{ $pago->cuota->numero_cuota }} de {{ $pago->cuota->planPago->cantidad_cuotas }}</div>
        </div>
        <div class="detalle-row">
            <div class="detalle-label">Fecha de Vencimiento:</div>
            <div class="detalle-value">{{ $pago->cuota->fecha_vencimiento->format('d/m/Y') }}</div>
        </div>
        <div class="detalle-row">
            <div class="detalle-label">Monto de la Cuota:</div>
            <div class="detalle-value">${{ number_format($pago->cuota->monto_total_cuota, 2) }}</div>
        </div>
    </div>
    @endif

    <!-- Resumen del Estado de la Venta -->
    <div class="detalles-venta">
        <div class="detalle-row">
            <div class="detalle-label">Monto Total de la Venta:</div>
            <div class="detalle-value">${{ number_format($pago->venta->monto_total, 2) }}</div>
        </div>
        <div class="detalle-row">
            <div class="detalle-label">Total Pagado hasta la fecha:</div>
            <div class="detalle-value" style="color: #059669;">${{ number_format($pago->venta->montoPagado(), 2) }}</div>
        </div>
        <div class="detalle-row">
            <div class="detalle-label">Saldo Pendiente:</div>
            <div class="detalle-value" style="color: {{ $pago->venta->saldoPendiente() > 0 ? '#dc2626' : '#059669' }};">
                ${{ number_format($pago->venta->saldoPendiente(), 2) }}
            </div>
        </div>
    </div>

    <div class="footer">
        <p><strong>Tendencias Tours SRL</strong></p>
        <p>Dirección: Av. Principal #123, Santa Cruz de la Sierra, Bolivia</p>
        <p>Teléfono: +591 3 333 4444 | Email: info@tendenciastours.com</p>
        <p style="margin-top: 15px;">Este documento es un comprobante válido de su pago.</p>
        <p>Conserve este comprobante para cualquier consulta o reclamo.</p>
        <p style="margin-top: 10px; font-style: italic;">¡Gracias por su pago!</p>
    </div>
</body>
</html>
