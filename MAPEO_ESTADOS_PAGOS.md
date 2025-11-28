# 🔄 Mapeo de Estados de Pago - PagoFácil

## Estados de PagoFácil (API)

PagoFácil envía un campo `Estado` numérico en el Callback:

| Estado (Número) | Significado          | Estado Interno | Descripción                                                               |
| --------------- | -------------------- | -------------- | ------------------------------------------------------------------------- |
| **1**           | En proceso/pendiente | `PENDING`      | El QR ha sido generado pero aún no se ha completado el pago               |
| **2**           | Pagado               | `PAID`         | ✅ El pago se completó exitosamente                                       |
| **4**           | Anulado              | `CANCELLED`    | ❌ No se recibió dinero o el código QR expiró                             |
| **5**           | Revisión             | `REVIEW`       | ⚠️ PagoFácil no pudo notificar por callback, requiere verificación manual |

---

## Flujo de Estados

### 1. Generación del QR

```
Estado Inicial: PENDING
```

-   Se crea el registro `Pago` en la BD con `payment_status = 'PENDING'`
-   Se genera el QR desde PagoFácil API
-   El usuario ve el QR en pantalla

### 2. Usuario escanea y paga

```
PagoFácil procesa el pago...
```

### 3. Callback de PagoFácil

```http
POST /pagofacil/callback
{
    "PedidoID": "grupo15sa_PAGO-123-abc",
    "Estado": 2,  // ← Número entero
    "Fecha": "2025-11-27",
    "Hora": "15:45:00",
    "MetodoPago": "QR"
}
```

**Nuestro backend mapea:**

```php
Estado 2 → 'PAID'
```

**Actualiza la BD:**

```sql
UPDATE pagos SET payment_status = 'PAID', fecha_pago = NOW() WHERE id = ...
```

### 4. Polling del Frontend

```javascript
// Cada 3 segundos
GET /api/pagos/{id}/status

// Respuesta:
{
    "payment_status": "PAID",
    "is_paid": true,
    "is_pending": false,
    ...
}
```

### 5. Redirección

```
Usuario ve: "✅ ¡Pago Completado!"
Redirige a: /ventas/{id}
```

---

## Implementación

### Backend: `PagoFacilCallbackController.php`

```php
private function mapEstadoPago(int $estado): string
{
    return match($estado) {
        1 => 'PENDING',      // En proceso/pendiente
        2 => 'PAID',         // Pagado ✅
        4 => 'CANCELLED',    // Anulado/Expirado ❌
        5 => 'REVIEW',       // En revisión ⚠️
        default => 'PENDING'
    };
}
```

### Base de Datos: Tabla `pagos`

```sql
payment_status VARCHAR(255) DEFAULT 'PENDING'
```

Valores posibles:

-   `PENDING`
-   `PAID`
-   `CANCELLED`
-   `REVIEW`
-   `EXPIRED` (opcional, para QRs vencidos por tiempo)

### Frontend: `MostrarQR.vue`

```javascript
const isPaid = computed(() => paymentStatus.value === "PAID");
const isPending = computed(() => paymentStatus.value === "PENDING");
const isCancelled = computed(() => paymentStatus.value === "CANCELLED");
const isReview = computed(() => paymentStatus.value === "REVIEW");
```

---

## Casos Especiales

### Estado 5 - REVIEW

Este estado indica que PagoFácil **no pudo llamar a tu callback**. Posibles causas:

1. **Tu servidor estaba caído** cuando intentaron notificar
2. **ngrok se desconectó** (en desarrollo)
3. **Firewall bloqueó** la petición de PagoFácil
4. **URL callback incorrecta** en el `.env`

**Solución:**

-   Verifica el log de PagoFácil en su panel
-   Consulta manualmente el estado de la transacción
-   Actualiza el pago a `PAID` si corresponde

### Estado 4 - CANCELLED vs EXPIRED

-   **CANCELLED**: PagoFácil reporta que el pago fue cancelado o el QR expiró
-   **EXPIRED**: (Opcional) Puedes implementar un cronjob que marque QRs vencidos basándose en `qr_expiration_date`

---

## Debugging

### Ver logs del callback

```bash
tail -f storage/logs/laravel.log
```

Busca:

```
🔌 PagoFácil: Callback recibido
Estado PagoFácil mapeado: {"estado_pagofacil":2,"estado_interno":"PAID"}
Pago actualizado exitosamente
```

### Probar callback manualmente (Postman)

```http
POST https://tu-ngrok-url.ngrok-free.app/pagofacil/callback
Content-Type: application/json

{
    "PedidoID": "grupo15sa_PAGO-7-test123",
    "Fecha": "2025-11-27",
    "Hora": "15:45:00",
    "MetodoPago": "QR",
    "Estado": 2
}
```

### Consultar estado desde terminal

```bash
php artisan tinker

$pago = \App\Models\Pago::find(1);
echo $pago->payment_status; // PAID, PENDING, etc.
```

---

## Checklist de Integración

-   [x] Mapeo de estados numéricos a strings
-   [x] Callback recibe `Estado` como integer
-   [x] Frontend detecta todos los posibles estados
-   [x] Logs detallados para debugging
-   [x] Alertas visuales para cada estado
-   [x] Redirección automática cuando `is_paid = true`

---

## Referencias

-   **Documentación PagoFácil:** [Link si está disponible]
-   **Callback Controller:** `app/Http/Controllers/PagoFacilCallbackController.php`
-   **API Status:** `app/Http/Controllers/Api/PagoStatusController.php`
-   **Frontend QR:** `resources/js/Pages/Admin/Pagos/MostrarQR.vue`
