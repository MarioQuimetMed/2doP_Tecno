# ✅ Sistema de Pagos con QR - INTEGRADO EN WIZARD

## 🎯 Flujo Completo ACTUALIZADO

### Opción 1: Crear Venta con Pago QR (NUEVO - Integrado en Wizard)

```
┌─────────────────────────────────────────────┐
│    /ventas/create - Wizard de 4 Pasos       │
└─────────────────────────────────────────────┘

PASO 1: 🗺️  Seleccionar Viaje
PASO 2: 👤 Seleccionar/Crear Cliente
PASO 3: 💳 Configurar Tipo de Pago
PASO 4: ✅ Confirmar Venta
       ├─ Checkbox "Deseo pagar ahora"
       ├─ Método de pago:
       │  ├─ EFECTIVO
       │  ├─ TRANSFERENCIA
       │  └─ CÓDIGO QR ← ✨ NUEVA FUNCIONALIDAD
       │
       └─ Si elige QR + Submit:
          ├─ Backend crea la venta
          ├─ Genera QR automáticamente
          ├─ Redirige a /pagos/{id}/mostrar-qr
          ├─ Muestra QR con polling (cada 3s)
          └─ Al pagar → Redirige a venta completada
```

### Opción 2: Generar QR desde Venta Existente

```
/ventas/{id} (Show)
    ↓
Click en "Pagar con QR"
    ↓
Redirige a /pagos/{id}/mostrar-qr
    ↓
Muestra QR + Polling
    ↓
Al pagar → Vuelve a venta completada
```

## 📊 Comparación de Flujos

| Característica  | Wizard Integrado               | Generar QR Manual          |
| --------------- | ------------------------------ | -------------------------- |
| **Cuándo usar** | Al crear nueva venta           | Venta ya existente         |
| **Pasos**       | Parte del wizard (4 pasos)     | 1 click desde venta        |
| **Experiencia** | Fluida, sin interrupciones     | Requiere volver a la venta |
| **Ideal para**  | Venta nueva con pago inmediato | Pagos posteriores          |

## 🔧 Cómo Funciona (Backend)

### VentaController@store (Modificado)

```php
// Si el método de pago inicial es QR:
if ($request->pago_inicial['metodo'] === 'QR') {
    // 1. Crea venta
    // 2. Llama a generarPagoConQR()
    //    ├─ Crea registro Pago (PENDING)
    //    ├─ Llama a PagoFacilService
    //    ├─ Obtiene QR base64
    //    └─ Guarda en BD
    // 3. Redirige a /pagos/{id}/mostrar-qr
}
```

### PagoFacilService

-   **NO usa librería externa para mostrar QR**
-   PagoFácil devuelve `qrBase64` (string)
-   Frontend lo muestra directamente:
    ```vue
    <img :src="`data:image/png;base64,${qr_base64}`" />
    ```

## 📝 Sin Librerías Adicionales

**¿Por qué no necesitamos librería?**

-   PagoFácil ya genera el QR como imagen base64
-   Solo necesitamos mostrarlo en un tag `<img>`
-   El formato `data:image/png;base64,{string}` es estándar HTML

## 🎨 Ejemplo de Uso

### 1. Usuario Crea Venta

```
Wizard → Paso 4 → Marca "Pagar ahora"
                 → Selecciona "Código QR"
                 → Click "Confirmar Venta"
```

### 2. Sistema Procesa

```
✅ Venta creada (#123)
✅ Pago pendiente creado
✅ QR generado (0.1 BOB - prueba)
✅ PaymentNumber: "grupo15sa_PAGO-123-abc..."
↓
REDIRIGE a: /pagos/{pago_id}/mostrar-qr
```

### 3. Pantalla de QR

```
┌──────────────────────────────────┐
│  Código QR para Pago             │
│  ┌─────────────┐                 │
│  │   [QR IMG]  │  ← Base64        │
│  └─────────────┘                 │
│                                   │
│  Bs. 0.10 (prueba)               │
│  ⏱️  Esperando pago... 0:23      │
│  🔄 Polling cada 3s              │
└──────────────────────────────────┘
```

### 4. Cliente Paga

```
📱 Escanea QR con app
💰 Paga Bs. 0.10
✅ PagoFácil confirma
📨 Envía POST al callback
🔄 Sistema actualiza estado
✨ Frontend detecta pago
🎉 Redirige a venta completada
```

## ✅ Ventajas de la Integración

1. ✅ **Experiencia fluida** - No sale del wizard
2. ✅ **Un solo flujo** - Crear y pagar en un paso
3. ✅ **Auto-redireccionamiento** - Detecta y avanza solo
4. ✅ **Polling automático** - No necesita refrescar
5. ✅ **Sin librerías** - Usa imagen base64 directa
6. ✅ **Tiempo real** - Actualiza cada 3 segundos

## 🔐 Configuración Requerida

```env
# .env
PAGOFACIL_TOKEN_SERVICE=...
PAGOFACIL_TOKEN_SECRET=...
PAGOFACIL_CALLBACK_URL=https://tu-ngrok.ngrok-free.app/pagofacil/callback
```

## 🚀 TODO Implementado

-   [x] Enum MetodoPago con QR
-   [x] VentaController detecta método QR
-   [x] Genera QR automáticamente al crear venta
-   [x] Redirige a pantalla de QR
-   [x] Polling cada 3 segundos
-   [x] Auto-redirección al pagar
-   [x] Callback numérico (Estado 1,2,4,5)
-   [x] Monto prueba 0.1 BOB
-   [x] Prefijo "grupo15sa\_"
-   [x] Mostrar QR con base64 (sin librería)

¡TODO FUNCIONANDO! 🎉
