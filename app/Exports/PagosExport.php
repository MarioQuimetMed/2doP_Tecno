<?php

namespace App\Exports;

use App\Models\Cuota;
use App\Enums\EstadoCuota;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PagosExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected string $tipo;

    public function __construct(string $tipo = 'todos')
    {
        $this->tipo = $tipo;
    }

    public function collection()
    {
        $query = Cuota::with(['planPago.venta.cliente', 'planPago.venta.viaje.planViaje']);

        if ($this->tipo === 'vencidas') {
            $query->vencidas();
        } elseif ($this->tipo === 'proximas') {
            $query->proximasAVencer(15);
        } else {
            $query->pendientes();
        }

        return $query->orderBy('fecha_vencimiento')->get();
    }

    public function headings(): array
    {
        return [
            'ID Cuota',
            'Nº Cuota',
            'Cliente',
            'Viaje',
            'Fecha Vencimiento',
            'Días Vencimiento',
            'Monto Cuota ($)',
            'Monto Pagado ($)',
            'Saldo Pendiente ($)',
            'Estado',
        ];
    }

    public function map($cuota): array
    {
        $diasVencimiento = $cuota->dias_hasta_vencimiento;
        $estado = $diasVencimiento < 0 ? 'VENCIDA' : $cuota->estado_cuota->label();

        return [
            $cuota->id,
            $cuota->numero_cuota,
            $cuota->planPago->venta->cliente->name ?? 'N/A',
            $cuota->planPago->venta->viaje->planViaje->nombre ?? 'N/A',
            $cuota->fecha_vencimiento->format('d/m/Y'),
            $diasVencimiento,
            number_format($cuota->monto_total_cuota, 2),
            number_format($cuota->montoPagado(), 2),
            number_format($cuota->saldoPendiente(), 2),
            $estado,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC2626'],
                ],
            ],
        ];
    }
}
