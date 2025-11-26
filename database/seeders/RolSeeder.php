<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'Propietario',
            ],
            [
                'nombre' => 'Vendedor',
            ],
            [
                'nombre' => 'Cliente',
            ],
        ];

        foreach ($roles as $rol) {
            Rol::create($rol);
        }
    }
}
