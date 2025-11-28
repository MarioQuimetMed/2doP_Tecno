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
            'ruta' => '/dashboard',
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
            'ruta' => '/destinos',
            'icono' => 'MapPinIcon',
            'orden' => 1,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'parent_id' => $gestionViajes->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Planes de Viaje',
            'ruta' => '/planes-viaje',
            'icono' => 'CalendarIcon',
            'orden' => 2,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'parent_id' => $gestionViajes->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Viajes Programados',
            'ruta' => '/viajes',
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
            'ruta' => '/ventas',
            'icono' => 'DocumentTextIcon',
            'orden' => 1,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'parent_id' => $ventas->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Planes de Pago',
            'ruta' => '/planes-pago',
            'icono' => 'CreditCardIcon',
            'orden' => 2,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'parent_id' => $ventas->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Dashboard Cobranzas',
            'ruta' => '/planes-pago/dashboard',
            'icono' => 'BanknotesIcon',
            'orden' => 3,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'parent_id' => $ventas->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Pagos',
            'ruta' => '/pagos',
            'icono' => 'CurrencyDollarIcon',
            'orden' => 4,
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
            'ruta' => '/usuarios',
            'icono' => 'UsersIcon',
            'orden' => 1,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'parent_id' => $admin->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Roles',
            'ruta' => '/roles',
            'icono' => 'ShieldCheckIcon',
            'orden' => 2,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'parent_id' => $admin->id,
            'rol_id' => $propietarioRol->id,
            'titulo' => 'Bitácora',
            'ruta' => '/bitacora',
            'icono' => 'ClipboardDocumentListIcon',
            'orden' => 3,
            'activo' => true,
        ]);

        // ===== MENÚ PARA VENDEDOR =====
        
        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'rol_id' => $vendedorRol->id,
            'titulo' => 'Dashboard',
            'ruta' => '/dashboard',
            'icono' => 'ChartBarIcon',
            'orden' => 1,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'rol_id' => $vendedorRol->id,
            'titulo' => 'Mis Ventas',
            'ruta' => '/vendedor/mis-ventas',
            'icono' => 'ShoppingCartIcon',
            'orden' => 2,
            'activo' => true,
        ]);

        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'rol_id' => $vendedorRol->id,
            'titulo' => 'Clientes',
            'ruta' => '/vendedor/clientes',
            'icono' => 'UsersIcon',
            'orden' => 3,
            'activo' => true,
        ]);

        // ===== MENÚ PARA CLIENTE =====
        
        MenuItem::create([
            'menu_id' => $menuPrincipal->id,
            'rol_id' => $clienteRol->id,
            'titulo' => 'Inicio',
            'ruta' => '/cliente/inicio',
            'icono' => 'HomeIcon',
            'orden' => 1,
            'activo' => true,
        ]);
    }
}
