<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #DC2626;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 20px;
            color: #DC2626;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 11px;
        }
        .periodo {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .periodo strong {
            color: #DC2626;
        }
        .estadisticas {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            background: #f8f9fa;
            border-right: 1px solid #ddd;
        }
        .stat-box:last-child {
            border-right: none;
        }
        .stat-box .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }
        .stat-box .value {
            font-size: 16px;
            font-weight: bold;
            color: #DC2626;
            margin-top: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px 6px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #DC2626;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success {
            background: #059669;
            color: white;
        }
        .badge-warning {
            background: #D97706;
            color: white;
        }
        .badge-danger {
            background: #DC2626;
            color: white;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 9px;
        }
        .total-row {
            font-weight: bold;
            background: #f0f0f0 !important;
        }
        .total-row td {
            border-top: 2px solid #DC2626;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>TENDENCIAS TOURS SRL</h1>
        <p>Reporte de Ventas por Período</p>
    </div>

    <div class="periodo">
        <strong>Período:</strong> {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} 
        al {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}
    </div>

    <div class="estadisticas">
        <div class="stat-box">
            <div class="label">Total Ventas</div>
            <div class="value">{{ $estadisticas['total_ventas'] }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Monto Total</div>
            <div class="value">${{ number_format($estadisticas['monto_total'], 2) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Promedio Venta</div>
            <div class="value">${{ number_format($estadisticas['promedio_venta'], 2) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Completadas</div>
            <div class="value">{{ $estadisticas['completadas'] }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th>Viaje/Destino</th>
                <th class="text-center">Pasajeros</th>
                <th class="text-right">Monto</th>
                <th class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $venta)
            <tr>
                <td>#{{ $venta->id }}</td>
                <td>{{ $venta->fecha_venta->format('d/m/Y') }}</td>
                <td>{{ $venta->cliente->name ?? 'N/A' }}</td>
                <td>{{ $venta->vendedor->name ?? 'N/A' }}</td>
                <td>
                    {{ $venta->viaje->planViaje->nombre ?? 'N/A' }}<br>
                    <small>{{ $venta->viaje->planViaje->destino->nombre_lugar ?? '' }}</small>
                </td>
                <td class="text-center">{{ $venta->cantidad_pasajeros }}</td>
                <td class="text-right">${{ number_format($venta->monto_total, 2) }}</td>
                <td class="text-center">
                    @php
                        $estadoClass = match($venta->estado_pago->value ?? '') {
                            'completado' => 'badge-success',
                            'pendiente' => 'badge-warning',
                            'cancelado' => 'badge-danger',
                            default => 'badge-warning'
                        };
                    @endphp
                    <span class="badge {{ $estadoClass }}">
                        {{ $venta->estado_pago->label() ?? 'N/A' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No hay ventas en este período</td>
            </tr>
            @endforelse
            @if($ventas->count() > 0)
            <tr class="total-row">
                <td colspan="5"><strong>TOTAL</strong></td>
                <td class="text-center"><strong>{{ $ventas->sum('cantidad_pasajeros') }}</strong></td>
                <td class="text-right"><strong>${{ number_format($ventas->sum('monto_total'), 2) }}</strong></td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Generado el {{ $fecha_generacion }} | Tendencias Tours SRL - Sistema de Gestión</p>
        <p>Este documento es un reporte interno y confidencial.</p>
    </div>
</body>
</html>
