<?php

namespace App\Exports;

use App\Models\Viaje;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OcupacionViajesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected ?string $estado;
    protected ?string $fechaInicio;
    protected ?string $fechaFin;

    public function __construct(?string $estado = null, ?string $fechaInicio = null, ?string $fechaFin = null)
    {
        $this->estado = $estado;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function collection()
    {
        return Viaje::with(['planViaje.destino'])
            ->when($this->estado, fn($q) => $q->where('estado_viaje', $this->estado))
            ->when($this->fechaInicio, fn($q) => $q->where('fecha_salida', '>=', $this->fechaInicio))
            ->when($this->fechaFin, fn($q) => $q->where('fecha_salida', '<=', $this->fechaFin))
            ->orderBy('fecha_salida', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Plan de Viaje',
            'Destino',
            'Fecha Salida',
            'Fecha Retorno',
            'Cupos Totales',
            'Cupos Vendidos',
            'Cupos Disponibles',
            '% Ocupación',
            'Estado',
        ];
    }

    public function map($viaje): array
    {
        return [
            $viaje->id,
            $viaje->planViaje->nombre ?? 'N/A',
            $viaje->planViaje->destino->nombre_completo ?? 'N/A',
            $viaje->fecha_salida->format('d/m/Y'),
            $viaje->fecha_retorno->format('d/m/Y'),
            $viaje->cupos_totales,
            $viaje->cupos_vendidos,
            $viaje->cupos_disponibles,
            $viaje->porcentaje_ocupacion . '%',
            $viaje->estado_viaje->label(),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '7C3AED'],
                ],
            ],
        ];
    }
}
