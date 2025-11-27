# 🚀 Guía de Despliegue en Servidor Apache (httpd)

## 📋 Información del Servidor

-   **URL Base:** `http://mail.tecnoweb.org.bo/inf513/grupo15sa/proyecto2`
-   **Servidor:** Apache (httpd)
-   **Framework:** Laravel 12 + Inertia.js + Vue 3

---

## 🎯 Estructura Correcta en el Servidor

```
/var/www/html/inf513/grupo15sa/proyecto2/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/           ← Este es el DocumentRoot
│   ├── index.php
│   ├── build/        ← Assets compilados de Vite
│   └── .htaccess
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
└── artisan
```

**IMPORTANTE:** La carpeta `public/` debe ser accesible desde la web, el resto NO.

---

## 📝 Pasos para Desplegar

### 1. **Preparar el Proyecto Localmente**

```bash
# En tu máquina local (Windows)
cd "D:\MarioUniv\10mo\Tecno will\2doParcial\2doP_Tecno"

# Instalar dependencias de producción
composer install --optimize-autoloader --no-dev

# Compilar assets para producción
npm run build

# Limpiar cachés
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 2. **Configurar `.env` para Producción**

Crea un archivo `.env.production` con estos valores:

```env
APP_NAME="2doP_Tecno"
APP_ENV=production
APP_KEY=base64:TU_APP_KEY_AQUI
APP_DEBUG=false
APP_TIMEZONE=America/La_Paz
APP_URL=http://mail.tecnoweb.org.bo/inf513/grupo15sa/proyecto2

# Base de datos del servidor
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=db_grupo15sa
DB_USERNAME=grupo15sa
DB_PASSWORD=tu_password_del_servidor

# Cache y sesiones
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# PagoFácil (Producción)
PAGOFACIL_TOKEN_SERVICE=tu_token_service
PAGOFACIL_TOKEN_SECRET=tu_token_secret
PAGOFACIL_CALLBACK_URL=http://mail.tecnoweb.org.bo/inf513/grupo15sa/proyecto2/pagofacil/callback
```

### 3. **Subir Archivos al Servidor**

```bash
# Conectar por SSH o FTP
# Subir TODA la carpeta del proyecto a:
# /var/www/html/inf513/grupo15sa/proyecto2/

# Estructura final:
/var/www/html/inf513/grupo15sa/proyecto2/
├── (todos los archivos de Laravel)
└── public/
```

### 4. **Configurar Permisos en el Servidor**

```bash
# Conectar por SSH al servidor
ssh grupo15sa@mail.tecnoweb.org.bo

# Ir a la carpeta del proyecto
cd /var/www/html/inf513/grupo15sa/proyecto2

# Dar permisos de escritura a storage y bootstrap/cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Proteger el archivo .env
chmod 600 .env
```

### 5. **Configurar Apache (.htaccess)**

Crea/modifica `.htaccess` en la raíz del proyecto:

**`/var/www/html/inf513/grupo15sa/proyecto2/.htaccess`**

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

**`/var/www/html/inf513/grupo15sa/proyecto2/public/.htaccess`**

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 6. **Ejecutar Migraciones en el Servidor**

```bash
# En el servidor, dentro de la carpeta del proyecto
cd /var/www/html/inf513/grupo15sa/proyecto2

# Ejecutar migraciones
php artisan migrate --force

# Ejecutar seeders (si es necesario)
php artisan db:seed --force

# Cachear configuración para mejor performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🌐 Acceso a la Aplicación

Una vez configurado, tu aplicación estará disponible en:

```
http://mail.tecnoweb.org.bo/inf513/grupo15sa/proyecto2
```

**NO necesitas acceder a `/public`** porque el `.htaccess` redirige automáticamente.

---

## ⚙️ ¿Cómo Funciona el Backend?

**NO necesitas "ejecutar" el backend** como en desarrollo. En producción:

1. **Apache (httpd)** maneja las peticiones HTTP
2. **mod_php** ejecuta PHP automáticamente
3. **Laravel** procesa las rutas a través de `public/index.php`
4. **Inertia.js** sirve las vistas Vue compiladas

**No hay `php artisan serve` en producción.** Apache hace ese trabajo.

---

## 🔧 Comandos Útiles en el Servidor

```bash
# Ver logs de errores
tail -f storage/logs/laravel.log

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimizar para producción
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ver rutas disponibles
php artisan route:list
```

---

## 📦 Actualizar el Proyecto

Cuando hagas cambios:

```bash
# Local
npm run build
git add .
git commit -m "Actualización"
git push

# Servidor
cd /var/www/html/inf513/grupo15sa/proyecto2
git pull
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan optimize
```

---

## 🐛 Solución de Problemas Comunes

### Error 500 - Internal Server Error

```bash
# Verificar permisos
chmod -R 775 storage bootstrap/cache

# Ver logs
tail -f storage/logs/laravel.log

# Limpiar caché
php artisan optimize:clear
```

### Assets (CSS/JS) no cargan

Verifica en `.env`:

```env
APP_URL=http://mail.tecnoweb.org.bo/inf513/grupo15sa/proyecto2
ASSET_URL=http://mail.tecnoweb.org.bo/inf513/grupo15sa/proyecto2
```

### Rutas no funcionan (404)

Verifica que `mod_rewrite` esté habilitado:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Base de datos no conecta

Verifica credenciales en `.env` y que PostgreSQL esté corriendo:

```bash
sudo systemctl status postgresql
```

---

## ✅ Checklist de Despliegue

-   [ ] `composer install --optimize-autoloader --no-dev`
-   [ ] `npm run build`
-   [ ] Configurar `.env` con datos de producción
-   [ ] Subir archivos al servidor
-   [ ] Configurar permisos (775 en storage)
-   [ ] Crear `.htaccess` en raíz y public
-   [ ] `php artisan migrate --force`
-   [ ] `php artisan optimize`
-   [ ] Verificar que `APP_URL` sea correcta
-   [ ] Probar acceso desde navegador

---

## 🎯 Resumen

| Aspecto      | Desarrollo                 | Producción                                               |
| ------------ | -------------------------- | -------------------------------------------------------- |
| **Servidor** | Herd / `php artisan serve` | Apache (httpd)                                           |
| **URL**      | `http://2dop_tecno.test`   | `http://mail.tecnoweb.org.bo/inf513/grupo15sa/proyecto2` |
| **Assets**   | `npm run dev` (Vite)       | `npm run build` (compilados)                             |
| **Debug**    | `APP_DEBUG=true`           | `APP_DEBUG=false`                                        |
| **Caché**    | Sin caché                  | Con caché (`optimize`)                                   |
| **Backend**  | `php artisan serve`        | Apache + mod_php                                         |

¡Listo para producción! 🚀
