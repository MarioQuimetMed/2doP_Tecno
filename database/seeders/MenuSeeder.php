<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $propietarioRol = Rol::where('nombre', 'Propietario')->first();
        $vendedorRol = Rol::where('nombre', 'Vendedor')->first();
        $clienteRol = Rol::where('nombre', 'Cliente')->first();

        // Crear menú principal
        $menuPrincipal = Menu::create([
            'nombre' => 'Menú Principal',
            'descripcion' => 'Menú principal del sistema',
            'activo' => true,
        ]);

        // ===== MENÚ PARA PROPIETARIO =====
        
        // Dashboard
        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Dashboard',
            'ruta' => 'dashboard',
            'icono' => 'ChartBarIcon',
            'orden' => 1,
            'activo' => true,
        ]);

        // Gestión de Viajes
        $gestionViajes = MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Gestión de Viajes',
            'ruta' => null,
            'icono' => 'GlobeAltIcon',
            'orden' => 2,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'parent_id' => $gestionViajes->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Destinos',
            'ruta' => 'destinos.index',
            'icono' => 'MapPinIcon',
            'orden' => 1,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'parent_id' => $gestionViajes->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Planes de Viaje',
            'ruta' => 'planes-viaje.index',
            'icono' => 'CalendarIcon',
            'orden' => 2,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'parent_id' => $gestionViajes->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Viajes Programados',
            'ruta' => 'viajes.index',
            'icono' => 'TruckIcon',
            'orden' => 3,
            'activo' => true,
        ]);

        // Ventas
        $ventas = MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Ventas',
            'ruta' => null,
            'icono' => 'ShoppingCartIcon',
            'orden' => 3,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'parent_id' => $ventas->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Todas las Ventas',
            'ruta' => 'ventas.index',
            'icono' => 'DocumentTextIcon',
            'orden' => 1,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'parent_id' => $ventas->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Pagos',
            'ruta' => 'pagos.index',
            'icono' => 'CurrencyDollarIcon',
            'orden' => 2,
            'activo' => true,
        ]);

        // Administración
        $admin = MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Administración',
            'ruta' => null,
            'icono' => 'CogIcon',
            'orden' => 4,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'parent_id' => $admin->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Usuarios',
            'ruta' => 'usuarios.index',
            'icono' => 'UsersIcon',
            'orden' => 1,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'parent_id' => $admin->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Roles',
            'ruta' => 'roles.index',
            'icono' => 'ShieldCheckIcon',
            'orden' => 2,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'parent_id' => $admin->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Bitácora',
            'ruta' => 'bitacora.index',
            'icono' => 'ClipboardDocumentListIcon',
            'orden' => 3,
            'activo' => true,
        ]);

        // ===== MENÚ PARA VENDEDOR =====
        
        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'rol_id' => $vendedorRol->id,
            'titulo' => 'Dashboard',
            'ruta' => 'dashboard',
            'icono' => 'ChartBarIcon',
            'orden' => 1,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'rol_id' => $vendedorRol->id,
            'titulo' => 'Mis Ventas',
            'ruta' => 'ventas.mis-ventas',
            'icono' => 'ShoppingCartIcon',
            'orden' => 2,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'rol_id' => $vendedorRol->id,
            'titulo' => 'Viajes Disponibles',
            'ruta' => 'viajes.disponibles',
            'icono' => 'GlobeAltIcon',
            'orden' => 3,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'rol_id' => $vendedorRol->id,
            'titulo' => 'Clientes',
            'ruta' => 'clientes.index',
            'icono' => 'UsersIcon',
            'orden' => 4,
            'activo' => true,
        ]);

        // ===== MENÚ PARA CLIENTE =====
        
        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'rol_id' => $clienteRol->id,
            'titulo' => 'Inicio',
            'ruta' => 'cliente.inicio',
            'icono' => 'HomeIcon',
            'orden' => 1,
            'activo' => true,
        ]);
    }
}
