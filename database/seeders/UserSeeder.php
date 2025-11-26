<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $propietarioRol = Rol::where('nombre', 'Propietario')->first();
        $vendedorRol = Rol::where('nombre', 'Vendedor')->first();
        $clienteRol = Rol::where('nombre', 'Cliente')->first();

        // Usuario Propietario
        User::create([
            'rol_id' => $propietarioRol->id,
            'name' => 'Propietario Admin',
            'email' => 'propietario@agencia.com',
            'password' => Hash::make('password'),
            'telefono' => '70000001',
            'ci_nit' => '1234567',
            'email_verified_at' => now(),
        ]);

        // Usuario Vendedor
        User::create([
            'rol_id' => $vendedorRol->id,
            'name' => 'Juan Vendedor',
            'email' => 'vendedor@agencia.com',
            'password' => Hash::make('password'),
            'telefono' => '70000002',
            'ci_nit' => '7654321',
            'email_verified_at' => now(),
        ]);

        // Usuario Cliente
        User::create([
            'rol_id' => $clienteRol->id,
            'name' => 'María Cliente',
            'email' => 'cliente@agencia.com',
            'password' => Hash::make('password'),
            'telefono' => '70000003',
            'ci_nit' => '9876543',
            'email_verified_at' => now(),
        ]);
    }
}
