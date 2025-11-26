# 📋 ANÁLISIS DE CONSIGNAS Y PLAN DE IMPLEMENTACIÓN

## 🎯 CONSIGNAS DEL PROYECTO

### **Consigna 3: Menú Dinámico (Base de Datos)** ✅

**Requisito:** El menú de navegación debe cargarse desde la base de datos, no estar hardcodeado.

**Implementación:**

-   ✅ Crear tabla `menus` con estructura jerárquica
-   ✅ Crear tabla `menu_items` con relación padre-hijo
-   ✅ Asociar items de menú con roles (permisos)
-   ✅ Componente Vue que renderice el menú dinámicamente
-   ✅ Middleware para filtrar items según rol del usuario

**Tablas necesarias:**

```sql
- menus (id, nombre, descripcion)
- menu_items (id, menu_id, parent_id, titulo, ruta, icono, orden, rol_id)
```

---

### **Consigna 4: MVC-MVVM (Laravel-Inertia)** ✅

**Requisito:** Arquitectura MVC en backend y MVVM en frontend.

**Implementación:**

-   ✅ **Backend (MVC):**
    -   Models: Eloquent ORM (ya implementado)
    -   Views: Componentes Inertia
    -   Controllers: Lógica de negocio
-   ✅ **Frontend (MVVM):**
    -   Model: Props de Inertia
    -   View: Templates Vue
    -   ViewModel: Composition API / Reactive data

**Estado:** ✅ Ya implementado con Laravel + Inertia + Vue 3

---

### **Consigna 5: Temas y Accesibilidad** 🎨

**Requisito:** 3 temas + modo día/noche automático + accesibilidad

**Implementación:**

#### **5.1 Sistema de Temas**

-   ✅ Tema Niños (colores vibrantes, fuentes grandes, iconos divertidos)
-   ✅ Tema Jóvenes (moderno, gradientes, animaciones)
-   ✅ Tema Adultos (profesional, elegante, minimalista)
-   ✅ Modo Día/Noche automático según hora del cliente

#### **5.2 Accesibilidad**

-   ✅ Selector de tamaño de fuente (pequeño, normal, grande, extra grande)
-   ✅ Selector de contraste (normal, alto contraste)
-   ✅ Persistencia de preferencias en localStorage
-   ✅ Cumplir WCAG 2.1 nivel AA

**Tablas necesarias:**

```sql
- temas (id, nombre, descripcion, css_variables)
- preferencias_usuario (user_id, tema_id, tamaño_fuente, alto_contraste)
```

**Archivos CSS:**

```
resources/css/
├── themes/
│   ├── ninos.css
│   ├── jovenes.css
│   ├── adultos.css
│   ├── dia.css
│   └── noche.css
└── accessibility.css
```

---

### **Consigna 6: Validación en Español** ✅

**Requisito:** Todas las validaciones con mensajes en español.

**Implementación:**

-   ✅ Configurar Laravel para español
-   ✅ Crear archivos de traducción personalizados
-   ✅ Validación en backend (FormRequests)
-   ✅ Validación en frontend (Vue + Vuelidate/VeeValidate)
-   ✅ Mensajes de error consistentes

**Archivos necesarios:**

```
resources/lang/es/
├── validation.php
├── auth.php
├── passwords.php
└── messages.php
```

---

### **Consigna 7: Contador de Visitas por Página** 📊

**Requisito:** Mostrar número de visitas en el pie de cada página.

**Implementación:**

-   ✅ Tabla `visitas_pagina` para registrar cada visita
-   ✅ Middleware que registre visitas automáticamente
-   ✅ Componente Footer que muestre el contador
-   ✅ Caché para optimizar consultas

**Tabla necesaria:**

```sql
- visitas_pagina (id, ruta, ip, user_id, user_agent, created_at)
```

**Funcionalidad:**

```php
// Middleware que registra cada visita
// Componente Vue que muestra: "Esta página ha sido visitada 1,234 veces"
```

---

### **Consigna 8: Estadísticas del Negocio y Acceso** 📈

**Requisito:** Dashboard con estadísticas y tabla de Bitácora.

**Implementación:**

#### **8.1 Tabla Bitácora (Auditoría)**

```sql
bitacora (
    id,
    user_id,              // Usuario que realizó la acción
    accion,               // Tipo: CREATE, UPDATE, DELETE, LOGIN, LOGOUT
    tabla,                // Tabla afectada
    registro_id,          // ID del registro afectado
    datos_anteriores,     // JSON con datos antes del cambio
    datos_nuevos,         // JSON con datos después del cambio
    ip,                   // IP del usuario
    user_agent,           // Navegador
    created_at
)
```

#### **8.2 Estadísticas del Negocio**

-   ✅ Total de ventas por período
-   ✅ Ventas por vendedor
-   ✅ Destinos más vendidos
-   ✅ Ocupación de viajes
-   ✅ Estado de pagos (pendientes, completados)
-   ✅ Cuotas vencidas
-   ✅ Gráficos interactivos (Chart.js / ApexCharts)

#### **8.3 Estadísticas de Acceso**

-   ✅ Usuarios activos
-   ✅ Páginas más visitadas
-   ✅ Horarios de mayor tráfico
-   ✅ Dispositivos utilizados
-   ✅ Acciones registradas en bitácora

**Componentes Vue:**

```
Pages/Admin/
├── Dashboard.vue          // Dashboard principal
├── Estadisticas/
│   ├── Ventas.vue
│   ├── Accesos.vue
│   └── Bitacora.vue
└── Charts/
    ├── VentasChart.vue
    ├── VisitasChart.vue
    └── OcupacionChart.vue
```

---

### **Consigna 9: Búsqueda Global** 🔍

**Requisito:** Buscador en el encabezado de la página principal.

**Implementación:**

-   ✅ Componente de búsqueda global en Navbar
-   ✅ Búsqueda en múltiples tablas (destinos, planes, viajes)
-   ✅ Resultados en tiempo real (debounce)
-   ✅ Autocompletado con sugerencias
-   ✅ Filtros avanzados

**Funcionalidad:**

```vue
<SearchBar
    :searchables="['destinos', 'planes_viaje', 'viajes']"
    placeholder="Buscar destinos, planes, viajes..."
/>
```

---

## 🗄️ TABLAS ADICIONALES NECESARIAS

### **1. Tabla: menus**

```php
Schema::create('menus', function (Blueprint $table) {
    $table->id();
    $table->string('nombre', 50);
    $table->string('descripcion')->nullable();
    $table->boolean('activo')->default(true);
    $table->timestamps();
});
```

### **2. Tabla: menu_items**

```php
Schema::create('menu_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('menu_id')->constrained()->onDelete('cascade');
    $table->foreignId('parent_id')->nullable()->constrained('menu_items')->onDelete('cascade');
    $table->foreignId('rol_id')->nullable()->constrained('rols')->onDelete('set null');
    $table->string('titulo', 50);
    $table->string('ruta')->nullable();
    $table->string('icono', 50)->nullable();
    $table->integer('orden')->default(0);
    $table->boolean('activo')->default(true);
    $table->timestamps();
});
```

### **3. Tabla: temas**

```php
Schema::create('temas', function (Blueprint $table) {
    $table->id();
    $table->string('nombre', 50);
    $table->string('descripcion')->nullable();
    $table->json('css_variables'); // Colores, fuentes, etc.
    $table->string('tipo'); // ninos, jovenes, adultos
    $table->timestamps();
});
```

### **4. Tabla: preferencias_usuario**

```php
Schema::create('preferencias_usuario', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('tema_id')->nullable()->constrained('temas')->onDelete('set null');
    $table->enum('tamaño_fuente', ['pequeño', 'normal', 'grande', 'extra_grande'])->default('normal');
    $table->boolean('alto_contraste')->default(false);
    $table->boolean('modo_oscuro_auto')->default(true);
    $table->timestamps();
});
```

### **5. Tabla: visitas_pagina**

```php
Schema::create('visitas_pagina', function (Blueprint $table) {
    $table->id();
    $table->string('ruta');
    $table->string('ip', 45);
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
    $table->text('user_agent')->nullable();
    $table->timestamps();

    $table->index(['ruta', 'created_at']);
});
```

### **6. Tabla: bitacora** ⭐

```php
Schema::create('bitacora', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
    $table->enum('accion', ['CREATE', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT', 'VIEW', 'EXPORT']);
    $table->string('tabla', 50)->nullable();
    $table->unsignedBigInteger('registro_id')->nullable();
    $table->json('datos_anteriores')->nullable();
    $table->json('datos_nuevos')->nullable();
    $table->string('ip', 45);
    $table->text('user_agent')->nullable();
    $table->text('descripcion')->nullable();
    $table->timestamps();

    $table->index(['user_id', 'created_at']);
    $table->index(['tabla', 'registro_id']);
    $table->index('accion');
});
```

---

## 📊 RESUMEN DE IMPLEMENTACIÓN

### **Fase 1: Base de Datos** ✅ (COMPLETADO)

-   ✅ Tablas del negocio (11 tablas)
-   ✅ Migraciones ejecutadas
-   ✅ Modelos Eloquent configurados

### **Fase 2: Tablas Adicionales** (SIGUIENTE)

-   [ ] Crear migración para `menus`
-   [ ] Crear migración para `menu_items`
-   [ ] Crear migración para `temas`
-   [ ] Crear migración para `preferencias_usuario`
-   [ ] Crear migración para `visitas_pagina`
-   [ ] Crear migración para `bitacora` ⭐

### **Fase 3: Seeders**

-   [ ] Seeders para roles y usuarios
-   [ ] Seeders para menús dinámicos
-   [ ] Seeders para temas
-   [ ] Seeders para datos del negocio

### **Fase 4: Backend**

-   [ ] Middleware de visitas
-   [ ] Middleware de bitácora
-   [ ] Trait para auditoría automática
-   [ ] Controladores para estadísticas

### **Fase 5: Frontend**

-   [ ] Sistema de temas
-   [ ] Componente de búsqueda global
-   [ ] Dashboard de estadísticas
-   [ ] Menú dinámico
-   [ ] Contador de visitas en footer

### **Fase 6: Accesibilidad**

-   [ ] Selector de tamaño de fuente
-   [ ] Modo alto contraste
-   [ ] Modo día/noche automático
-   [ ] Validaciones en español

---

## 🎯 PRÓXIMO PASO

¿Quieres que proceda a crear las **6 tablas adicionales** (menus, menu_items, temas, preferencias_usuario, visitas_pagina, bitacora)?

Esto nos permitirá cumplir con todas las consignas del proyecto.
