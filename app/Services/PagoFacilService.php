<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class PagoFacilService
{
    private string $baseUrl;
    private string $tokenService;
    private string $tokenSecret;

    public function __construct()
    {
        $this->baseUrl = 'https://masterqr.pagofacil.com.bo/api/services/v2';
        $this->tokenService = config('services.pagofacil.token_service');
        $this->tokenSecret = config('services.pagofacil.token_secret');
    }

    /**
     * Obtener token de autenticación (con caché)
     */
    public function getAccessToken(): string
    {
        // Intentar obtener token del caché
        $token = Cache::get('pagofacil_access_token');

        if ($token) {
            return $token;
        }

        // Si no hay token en caché, autenticarse
        return $this->authenticate();
    }

    /**
     * Autenticarse en la API de PagoFácil
     */
    /**
     * Autenticarse en la API de PagoFácil
     */
    private function authenticate(): string
    {
        try {
            Log::info('🔌 PagoFácil: Iniciando autenticación...');

            $response = Http::timeout(60)->withHeaders([
                'tcTokenService' => $this->tokenService,
                'tcTokenSecret' => $this->tokenSecret,
            ])->post("{$this->baseUrl}/login");

            $data = $response->json();
            
            // Log de respuesta (ocultando datos sensibles si es necesario)
            Log::info('🔌 PagoFácil: Respuesta de login recibida', ['status' => $data['status'] ?? 'unknown', 'error' => $data['error'] ?? 'unknown']);

            if (($data['error'] ?? 1) !== 0 || ($data['status'] ?? 0) !== 1) {
                Log::error('❌ PagoFácil: Falló la autenticación', ['response' => $data]);
                throw new Exception("Error de autenticación: " . ($data['message'] ?? 'Respuesta desconocida'));
            }

            $accessToken = $data['values']['accessToken'];
            $expiresInMinutes = $data['values']['expiresInMinutes'];

            Log::info('✅ PagoFácil: Autenticación exitosa. Token obtenido.');

            // Guardar token en caché (restar 5 minutos por seguridad)
            Cache::put(
                'pagofacil_access_token',
                $accessToken,
                now()->addMinutes($expiresInMinutes - 5)
            );

            return $accessToken;

        } catch (Exception $e) {
            Log::error('❌ PagoFácil: Excepción en autenticación', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generar código QR para un pago
     * 
     * @param array $params Parámetros de la transacción
     * @return array Datos del QR generado
     */
    public function generateQR(array $params): array
    {
        try {
            Log::info('📤 PagoFácil: Solicitando generación de QR', ['paymentNumber' => $params['paymentNumber']]);

            // Forzar obtención de token (el método getAccessToken maneja el caché o re-login)
            $token = $this->getAccessToken();

            $response = Http::timeout(60)->withHeaders([
                'Authorization' => "Bearer {$token}",
            ])->post("{$this->baseUrl}/generate-qr", $params);

            $data = $response->json();

            Log::info('📥 PagoFácil: Respuesta de generación QR recibida');

            if (($data['error'] ?? 1) !== 0) {
                Log::error('❌ PagoFácil: Error al generar QR', ['response' => $data]);
                throw new Exception("Error API PagoFácil: " . ($data['message'] ?? 'Error desconocido'));
            }

            Log::info('✅ PagoFácil: QR generado exitosamente', [
                'transaction_id' => $data['values']['transactionId'] ?? 'N/A',
            ]);

            return $data['values'];

        } catch (Exception $e) {
            Log::error('❌ PagoFácil: Excepción al generar QR', [
                'error' => $e->getMessage(),
                'params' => $params,
            ]);
            throw $e;
        }
    }

    /**
     * Método queryTransaction eliminado porque el endpoint no está disponible.
     * La verificación de estado se realiza vía Callback (Webhook) y consulta a BD local.
     */

    /**
     * Preparar parámetros para generar QR de un pago
     * 
     * @param \App\Models\Venta $venta Venta asociada
     * @param float $amount Monto a pagar
     * @param string $paymentNumber Número único de pago (ej: PAGO-123)
     * @return array
     */
    public function prepareQRParams($venta, float $amount, string $paymentNumber): array
    {
        // Usar URL del .env si está configurada (para ngrok en desarrollo)
        // Si no, usar la generada automáticamente por Laravel (para producción)
        $callbackUrl = config('services.pagofacil.callback_url') 
            ?? route('pagofacil.callback');

        // AMBIENTE DE PRUEBA: Forzar monto a 0.1 BOB
        $amountTest = 0.1;
        
        return [
            'paymentMethod' => 4, // QR
            'clientName' => $venta->cliente->name,
            'documentType' => 1, // CI
            'documentId' => $venta->cliente->ci_nit ?? 'S/N',
            'phoneNumber' => '79871000',
            'email' => $venta->cliente->email,
            'paymentNumber' => $paymentNumber, // Ya viene con el prefijo desde el controlador
            'amount' => $amountTest, // Monto de prueba: 0.1 BOB
            'currency' => 2, // BOB
            'clientCode' => (string) $venta->cliente->id,
            'callbackUrl' => $callbackUrl,
            'orderDetail' => [
                [
                    'serial' => 1,
                    'product' => "Viaje: {$venta->viaje->planViaje->nombre} (PRUEBA)",
                    'quantity' => 1,
                    'price' => $amountTest,
                    'discount' => 0,
                    'total' => $amountTest,
                ]
            ],
        ];
    }
}
