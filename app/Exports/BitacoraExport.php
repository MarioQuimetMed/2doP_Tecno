<?php

namespace App\Exports;

use App\Models\Bitacora;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BitacoraExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected ?string $fechaInicio;
    protected ?string $fechaFin;
    protected ?string $accion;
    protected ?int $userId;

    public function __construct(
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        ?string $accion = null,
        ?int $userId = null
    ) {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->accion = $accion;
        $this->userId = $userId;
    }

    public function collection()
    {
        $query = Bitacora::with('usuario');

        if ($this->fechaInicio) {
            $query->where('created_at', '>=', $this->fechaInicio . ' 00:00:00');
        }

        if ($this->fechaFin) {
            $query->where('created_at', '<=', $this->fechaFin . ' 23:59:59');
        }

        if ($this->accion) {
            $query->where('accion', $this->accion);
        }

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fecha y Hora',
            'Usuario',
            'Acción',
            'Tabla',
            'Registro ID',
            'IP',
            'Descripción',
        ];
    }

    public function map($bitacora): array
    {
        return [
            $bitacora->id,
            $bitacora->created_at->format('d/m/Y H:i:s'),
            $bitacora->usuario->name ?? 'Sistema',
            $bitacora->accion,
            $bitacora->tabla ?? '-',
            $bitacora->registro_id ?? '-',
            $bitacora->ip,
            $bitacora->descripcion ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '374151'],
                ],
            ],
        ];
    }
}
