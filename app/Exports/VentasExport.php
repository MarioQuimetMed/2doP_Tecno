<?php

namespace App\Exports;

use App\Models\Venta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VentasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected string $fechaInicio;
    protected string $fechaFin;

    public function __construct(string $fechaInicio, string $fechaFin)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function collection()
    {
        return Venta::with(['cliente', 'vendedor', 'viaje.planViaje.destino'])
            ->whereBetween('fecha_venta', [$this->fechaInicio, $this->fechaFin])
            ->orderBy('fecha_venta', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha Venta',
            'Cliente',
            'Vendedor',
            'Destino',
            'Viaje (Fecha Salida)',
            'Tipo Pago',
            'Estado Pago',
            'Monto Total ($)',
            'Monto Pagado ($)',
            'Saldo Pendiente ($)',
        ];
    }

    public function map($venta): array
    {
        return [
            $venta->id,
            $venta->fecha_venta->format('d/m/Y'),
            $venta->cliente->name ?? 'N/A',
            $venta->vendedor->name ?? 'N/A',
            $venta->viaje->planViaje->destino->nombre_completo ?? 'N/A',
            $venta->viaje->fecha_salida->format('d/m/Y'),
            $venta->tipo_pago->label(),
            $venta->estado_pago->label(),
            number_format($venta->monto_total, 2),
            number_format($venta->montoPagado(), 2),
            number_format($venta->saldoPendiente(), 2),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'],
                ],
            ],
        ];
    }
}
