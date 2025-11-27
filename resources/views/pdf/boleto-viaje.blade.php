<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleto de Viaje - {{ $numero_boleto }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #f3f4f6;
            padding: 20px;
        }
        .boleto {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .boleto-header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        .boleto-header::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 30px;
            background: white;
            border-radius: 50%;
        }
        .logo {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .numero-boleto {
            background: rgba(255,255,255,0.2);
            display: inline-block;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
        }
        .boleto-body {
            padding: 30px;
        }
        .destino-info {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px dashed #e5e7eb;
            margin-bottom: 20px;
        }
        .destino-nombre {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 5px;
        }
        .destino-lugar {
            color: #6b7280;
            font-size: 14px;
        }
        .viaje-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .viaje-col {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 15px;
            border-right: 1px solid #e5e7eb;
        }
        .viaje-col:last-child {
            border-right: none;
        }
        .viaje-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 5px;
        }
        .viaje-value {
            font-size: 16px;
            font-weight: bold;
            color: #374151;
        }
        .pasajero-section {
            background: #f9fafb;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .pasajero-nombre {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 5px;
        }
        .pasajero-detalle {
            color: #6b7280;
            font-size: 12px;
        }
        .itinerario {
            margin-top: 20px;
        }
        .itinerario-title {
            font-size: 14px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4f46e5;
        }
        .dia-item {
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #4f46e5;
        }
        .dia-numero {
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 5px;
        }
        .dia-actividades {
            color: #6b7280;
            font-size: 11px;
        }
        .boleto-footer {
            background: #f3f4f6;
            padding: 20px;
            text-align: center;
            border-top: 2px dashed #e5e7eb;
        }
        .footer-text {
            color: #6b7280;
            font-size: 10px;
            margin-bottom: 5px;
        }
        .footer-text strong {
            color: #374151;
        }
        .qr-placeholder {
            width: 80px;
            height: 80px;
            background: #e5e7eb;
            margin: 10px auto;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #9ca3af;
        }
        .importante {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        .importante-title {
            color: #92400e;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .importante-text {
            color: #78350f;
            font-size: 10px;
        }
        .validez {
            text-align: center;
            padding: 15px;
            background: #d1fae5;
            border-radius: 8px;
            margin-top: 15px;
        }
        .validez-text {
            color: #065f46;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="boleto">
        <div class="boleto-header">
            <div class="logo">Tendencias Tours SRL</div>
            <p style="opacity: 0.9; font-size: 12px;">Agencia de Viajes y Turismo</p>
            <div class="numero-boleto">{{ $numero_boleto }}</div>
        </div>

        <div class="boleto-body">
            <!-- Destino -->
            <div class="destino-info">
                <div class="destino-nombre">{{ $venta->viaje->planViaje->nombre }}</div>
                <div class="destino-lugar">
                    {{ $venta->viaje->planViaje->destino->nombre_lugar }} - 
                    {{ $venta->viaje->planViaje->destino->ciudad }}, 
                    {{ $venta->viaje->planViaje->destino->pais }}
                </div>
            </div>

            <!-- Información del Viaje -->
            <div class="viaje-info">
                <div class="viaje-col">
                    <div class="viaje-label">Fecha Salida</div>
                    <div class="viaje-value">{{ $venta->viaje->fecha_salida->format('d/m/Y') }}</div>
                </div>
                <div class="viaje-col">
                    <div class="viaje-label">Fecha Retorno</div>
                    <div class="viaje-value">{{ $venta->viaje->fecha_retorno->format('d/m/Y') }}</div>
                </div>
                <div class="viaje-col">
                    <div class="viaje-label">Duración</div>
                    <div class="viaje-value">{{ $venta->viaje->planViaje->duracion_dias }} días</div>
                </div>
            </div>

            <!-- Información del Pasajero -->
            <div class="pasajero-section">
                <div class="section-title">Pasajero</div>
                <div class="pasajero-nombre">{{ $venta->cliente->name }}</div>
                <div class="pasajero-detalle">
                    @if($venta->cliente->ci_nit)
                    CI/NIT: {{ $venta->cliente->ci_nit }} | 
                    @endif
                    Email: {{ $venta->cliente->email }}
                    @if($venta->cliente->telefono)
                    | Tel: {{ $venta->cliente->telefono }}
                    @endif
                </div>
            </div>

            <!-- Itinerario -->
            @if($venta->viaje->planViaje->actividadesDiarias->count() > 0)
            <div class="itinerario">
                <div class="itinerario-title">Itinerario del Viaje</div>
                @php
                    $actividadesPorDia = $venta->viaje->planViaje->actividadesDiarias->groupBy('dia_numero');
                @endphp
                @foreach($actividadesPorDia as $dia => $actividades)
                <div class="dia-item">
                    <div class="dia-numero">Día {{ $dia }}</div>
                    <div class="dia-actividades">
                        @foreach($actividades as $actividad)
                            {{ $actividad->hora_inicio ? $actividad->hora_inicio->format('H:i') . ' - ' : '' }}{{ $actividad->descripcion_actividad }}@if(!$loop->last) | @endif
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Información Importante -->
            <div class="importante">
                <div class="importante-title">Información Importante</div>
                <div class="importante-text">
                    - Presente este boleto impreso o en formato digital al momento del embarque.<br>
                    - Llegue al punto de encuentro 30 minutos antes de la hora de salida.<br>
                    - Lleve consigo un documento de identidad válido.<br>
                    - El equipaje permitido es de máximo 20kg por persona.
                </div>
            </div>

            <!-- Validez -->
            <div class="validez">
                <div class="validez-text">BOLETO VÁLIDO - PAGADO EN SU TOTALIDAD</div>
            </div>
        </div>

        <div class="boleto-footer">
            <div class="footer-text"><strong>Tendencias Tours SRL</strong></div>
            <div class="footer-text">Av. Principal #123, Santa Cruz de la Sierra, Bolivia</div>
            <div class="footer-text">Tel: +591 3 333 4444 | info@tendenciastours.com</div>
            <div class="footer-text" style="margin-top: 10px;">
                Emitido: {{ $fecha_emision }} | Venta #{{ $venta->id }}
            </div>
        </div>
    </div>
</body>
</html>
