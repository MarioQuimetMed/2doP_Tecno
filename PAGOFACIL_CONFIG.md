# Integración con PagoFácil - Variables de Entorno

Agrega estas variables a tu archivo `.env`:

```env
# PagoFácil - Pagos con QR
PAGOFACIL_TOKEN_SERVICE=tu_token_service_aqui
PAGOFACIL_TOKEN_SECRET=tu_token_secret_aqui

# URL del Callback (IMPORTANTE)
# En desarrollo con ngrok:
PAGOFACIL_CALLBACK_URL=https://84bc5c3d85df.ngrok-free.app/pagofacil/callback

# En producción (comenta la de desarrollo y descomenta esta):
# PAGOFACIL_CALLBACK_URL=https://tecnowebg15savip.tech/pagofacil/callback

# Si no configuras PAGOFACIL_CALLBACK_URL, usará automáticamente:
# - En desarrollo: http://2dop_tecno.test/pagofacil/callback (NO funcionará con PagoFácil)
# - En producción: https://tu-dominio.com/pagofacil/callback (funcionará si está bien configurado)
```

## ¿Dónde encontrar estas credenciales?

Las credenciales `tcTokenService` y `tcTokenSecret` te las proporciona PagoFácil cuando te registras en su plataforma.

## Uso en el Sistema

Una vez configuradas las credenciales, podrás:

1. **Generar QR** para pagos desde la página de una venta
2. **Consultar el estado** de transacciones
3. **Recibir notificaciones** automáticas cuando se complete un pago

## URL de Callback

La URL de callback que debes registrar en PagoFácil es:

```
https://tu-dominio.com/pagofacil/callback
```

En desarrollo local (Laravel Herd):

```
http://2dop_tecno.test/pagofacil/callback
```

**Nota:** Para testing en local, necesitarás un servicio como **ngrok** para exponer tu servidor local a internet y que PagoFácil pueda enviar notificaciones.

# Integración con PagoFácil - Variables de Entorno

Agrega estas variables a tu archivo `.env`:

```env
# PagoFácil - Pagos con QR
PAGOFACIL_TOKEN_SERVICE=tu_token_service_aqui
PAGOFACIL_TOKEN_SECRET=tu_token_secret_aqui

# URL del Callback (IMPORTANTE)
# En desarrollo con ngrok:
PAGOFACIL_CALLBACK_URL=https://84bc5c3d85df.ngrok-free.app/pagofacil/callback

# En producción (comenta la de desarrollo y descomenta esta):
# PAGOFACIL_CALLBACK_URL=https://tecnowebg15savip.tech/pagofacil/callback

# Si no configuras PAGOFACIL_CALLBACK_URL, usará automáticamente:
# - En desarrollo: http://2dop_tecno.test/pagofacil/callback (NO funcionará con PagoFácil)
# - En producción: https://tu-dominio.com/pagofacil/callback (funcionará si está bien configurado)
```

## ¿Dónde encontrar estas credenciales?

Las credenciales `tcTokenService` y `tcTokenSecret` te las proporciona PagoFácil cuando te registras en su plataforma.

## Uso en el Sistema

Una vez configuradas las credenciales, podrás:

1. **Generar QR** para pagos desde la página de una venta
2. **Consultar el estado** de transacciones
3. **Recibir notificaciones** automáticas cuando se complete un pago

## URL de Callback

La URL de callback que debes registrar en PagoFácil es:

```
https://tu-dominio.com/pagofacil/callback
```

En desarrollo local (Laravel Herd):

# Integración con PagoFácil - Variables de Entorno

Agrega estas variables a tu archivo `.env`:

```env
# PagoFácil - Pagos con QR
PAGOFACIL_TOKEN_SERVICE=tu_token_service_aqui
PAGOFACIL_TOKEN_SECRET=tu_token_secret_aqui

# URL del Callback (IMPORTANTE)
# En desarrollo con ngrok:
PAGOFACIL_CALLBACK_URL=https://84bc5c3d85df.ngrok-free.app/pagofacil/callback

# En producción (comenta la de desarrollo y descomenta esta):
# PAGOFACIL_CALLBACK_URL=https://tecnowebg15savip.tech/pagofacil/callback

# Si no configuras PAGOFACIL_CALLBACK_URL, usará automáticamente:
# - En desarrollo: http://2dop_tecno.test/pagofacil/callback (NO funcionará con PagoFácil)
# - En producción: https://tu-dominio.com/pagofacil/callback (funcionará si está bien configurado)
```

## ¿Dónde encontrar estas credenciales?

Las credenciales `tcTokenService` y `tcTokenSecret` te las proporciona PagoFácil cuando te registras en su plataforma.

## Uso en el Sistema

Una vez configuradas las credenciales, podrás:

1. **Generar QR** para pagos desde la página de una venta
2. **Consultar el estado** de transacciones
3. **Recibir notificaciones** automáticas cuando se complete un pago

## URL de Callback

La URL de callback que debes registrar en PagoFácil es:

```
https://tu-dominio.com/pagofacil/callback
```

En desarrollo local (Laravel Herd):

```
http://2dop_tecno.test/pagofacil/callback
```

**Nota:** Para testing en local, necesitarás un servicio como **ngrok** para exponer tu servidor local a internet y que PagoFácil pueda enviar notificaciones.

# Integración con PagoFácil - Variables de Entorno

Agrega estas variables a tu archivo `.env`:

```env
# PagoFácil - Pagos con QR
PAGOFACIL_TOKEN_SERVICE=tu_token_service_aqui
PAGOFACIL_TOKEN_SECRET=tu_token_secret_aqui

# URL del Callback (IMPORTANTE)
# En desarrollo con ngrok:
PAGOFACIL_CALLBACK_URL=https://84bc5c3d85df.ngrok-free.app/pagofacil/callback

# En producción (comenta la de desarrollo y descomenta esta):
# PAGOFACIL_CALLBACK_URL=https://tecnowebg15savip.tech/pagofacil/callback

# Si no configuras PAGOFACIL_CALLBACK_URL, usará automáticamente:
# - En desarrollo: http://2dop_tecno.test/pagofacil/callback (NO funcionará con PagoFácil)
# - En producción: https://tu-dominio.com/pagofacil/callback (funcionará si está bien configurado)
```

## ¿Dónde encontrar estas credenciales?

Las credenciales `tcTokenService` y `tcTokenSecret` te las proporciona PagoFácil cuando te registras en su plataforma.

## Uso en el Sistema

Una vez configuradas las credenciales, podrás:

1. **Generar QR** para pagos desde la página de una venta
2. **Consultar el estado** de transacciones
3. **Recibir notificaciones** automáticas cuando se complete un pago

## URL de Callback

La URL de callback que debes registrar en PagoFácil es:

```
https://tu-dominio.com/pagofacil/callback
```

En desarrollo local (Laravel Herd):

```
http://2dop_tecno.test/pagofacil/callback
```

**Nota:** Para testing en local, necesitarás un servicio como **ngrok** para exponer tu servidor local a internet y que PagoFácil pueda enviar notificaciones.

## Configuración de ngrok para Desarrollo Local con Herd

### ✅ Comando que FUNCIONA con Laravel Herd:

```bash
ngrok http 127.0.0.1:80 --host-header="2dop_tecno.test"
```

**Explicación:**

-   `127.0.0.1:80` - Puerto donde Herd está escuchando
-   `--host-header="2dop_tecno.test"` - Header necesario para que Herd identifique el sitio correcto

### Ejemplo de Salida:

```
Forwarding  https://bcf2ce806967.ngrok-free.app -> http://127.0.0.1:80
```

### Configurar URL del Callback

Una vez que ngrok esté corriendo, copia la URL y agrégala a tu `.env`:

```env
PAGOFACIL_CALLBACK_URL=https://bcf2ce806967.ngrok-free.app/pagofacil/callback
```

**Importante:** Esta URL cambia cada vez que reinicias ngrok (plan gratuito).

### Verificar que Funciona

Prueba el endpoint de test:

```
GET https://bcf2ce806967.ngrok-free.app/pagofacil/test
```

Deberías recibir:

```json
{
    "message": "¡Ngrok funciona correctamente!",
    "timestamp": "2025-11-27 15:08:00",
    "app": "2doP_Tecno"
}
```

### Probar el Callback

```bash
# Desde PowerShell
Invoke-WebRequest -Uri "https://bcf2ce806967.ngrok-free.app/pagofacil/callback" `
  -Method POST `
  -ContentType "application/json" `
  -Body '{"PedidoID":"test","Fecha":"2025-11-27","Hora":"15:00:00","MetodoPago":"QR","Estado":2}'
```

Deberías recibir (si el PedidoID no existe):

```json
{
    "error": 1,
    "status": 0,
    "message": "Pago no encontrado",
    "values": false
}
```

**Nota:** Mientras ngrok esté corriendo, PagoFácil podrá enviar notificaciones a tu máquina local.
