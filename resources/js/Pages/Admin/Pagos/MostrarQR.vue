<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted, computed } from "vue";
import { useAppUrl } from "@/Composables/useAppUrl";
import axios from "axios";
import {
    QrCodeIcon,
    CheckCircleIcon,
    ClockIcon,
    XCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    pago: Object,
});

const { resolveUrl } = useAppUrl();

const pollingInterval = ref(null);
const paymentStatus = ref(props.pago.payment_status);
const timeElapsed = ref(0);

const isPaid = computed(() => paymentStatus.value === "COMPLETED");
const isPending = computed(() => paymentStatus.value === "PENDING");
const isExpired = computed(() => paymentStatus.value === "EXPIRED");
const isCancelled = computed(() => paymentStatus.value === "CANCELLED");
const isReview = computed(() => paymentStatus.value === "REVIEW");

// Polling para verificar el estado del pago
const checkPaymentStatus = async () => {
    try {
        // /inf513/grupo15sa/2doP_Tecno/public/
        const response = await axios.get(
            resolveUrl(`api/pagos/${props.pago.id}/status`)
        );

        const data = response.data;

        paymentStatus.value = data.payment_status;

        // Si el pago está completado, redirigir a la venta
        if (data.is_paid) {
            clearInterval(pollingInterval.value);
            setTimeout(() => {
                router.visit(resolveUrl(`ventas/${props.pago.venta_id}`), {
                    preserveState: false,
                });
            }, 2000); // Esperar 2 segundos antes de redirigir
        }
    } catch (error) {
        console.error("Error al consultar estado:", error);
    }
};

// Timer para mostrar tiempo transcurrido
const updateTimer = () => {
    timeElapsed.value++;
};

onMounted(() => {
    // Polling cada 3 segundos
    pollingInterval.value = setInterval(checkPaymentStatus, 3000);

    // Timer visual cada segundo
    setInterval(updateTimer, 1000);
});

onUnmounted(() => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
    }
});

const formatTime = (seconds) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, "0")}`;
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value);
};
</script>

<template>
    <Head title="Pago con QR" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Pago con Código QR
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Estado del Pago -->
                <div
                    v-if="isPaid"
                    class="mb-6 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded"
                >
                    <div class="flex items-center">
                        <CheckCircleIcon class="h-6 w-6 text-green-500 mr-3" />
                        <div>
                            <p
                                class="text-lg font-semibold text-green-800 dark:text-green-200"
                            >
                                ¡Pago Completado!
                            </p>
                            <p
                                class="text-sm text-green-700 dark:text-green-300"
                            >
                                Redirigiendo a la venta...
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-else-if="isExpired"
                    class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded"
                >
                    <div class="flex items-center">
                        <XCircleIcon class="h-6 w-6 text-red-500 mr-3" />
                        <div>
                            <p
                                class="text-lg font-semibold text-red-800 dark:text-red-200"
                            >
                                Código QR Expirado
                            </p>
                            <p class="text-sm text-red-700 dark:text-red-300">
                                Por favor, genera un nuevo código QR.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-else-if="isCancelled"
                    class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded"
                >
                    <div class="flex items-center">
                        <XCircleIcon class="h-6 w-6 text-red-500 mr-3" />
                        <div>
                            <p
                                class="text-lg font-semibold text-red-800 dark:text-red-200"
                            >
                                Pago Cancelado
                            </p>
                            <p class="text-sm text-red-700 dark:text-red-300">
                                El pago no se completó o el código QR expiró.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-else-if="isReview"
                    class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 p-4 rounded"
                >
                    <div class="flex items-center">
                        <ClockIcon class="h-6 w-6 text-yellow-500 mr-3" />
                        <div>
                            <p
                                class="text-lg font-semibold text-yellow-800 dark:text-yellow-200"
                            >
                                Pago en Revisión
                            </p>
                            <p
                                class="text-sm text-yellow-700 dark:text-yellow-300"
                            >
                                El pago está siendo verificado. Por favor,
                                contacta con soporte si esto persiste.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="mb-6 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <ClockIcon
                                class="h-6 w-6 text-blue-500 mr-3 animate-pulse"
                            />
                            <div>
                                <p
                                    class="text-lg font-semibold text-blue-800 dark:text-blue-200"
                                >
                                    Esperando Pago...
                                </p>
                                <p
                                    class="text-sm text-blue-700 dark:text-blue-300"
                                >
                                    Escanea el código QR para completar el pago
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p
                                class="text-2xl font-mono font-bold text-blue-800 dark:text-blue-200"
                            >
                                {{ formatTime(timeElapsed) }}
                            </p>
                            <p class="text-xs text-blue-600 dark:text-blue-400">
                                tiempo transcurrido
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Código QR -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="p-8">
                        <!-- Información de la Venta -->
                        <div class="mb-8 text-center">
                            <h3
                                class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2"
                            >
                                {{ pago.venta?.viaje?.plan_viaje?.nombre }}
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">
                                Cliente: {{ pago.venta?.cliente?.name }}
                            </p>
                            <div
                                class="inline-block bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-lg"
                            >
                                <p class="text-sm opacity-90">Monto a pagar</p>
                                <p class="text-3xl font-bold">Bs. 0.10</p>
                                <p class="text-xs opacity-75">
                                    (Monto de prueba)
                                </p>
                            </div>
                        </div>

                        <!-- QR Code -->
                        <div class="flex justify-center mb-8">
                            <div class="bg-white p-6 rounded-xl shadow-lg">
                                <div v-if="pago.qr_base64" class="relative">
                                    <img
                                        :src="`data:image/png;base64,${pago.qr_base64}`"
                                        alt="Código QR"
                                        class="w-80 h-80 object-contain"
                                    />
                                    <div
                                        v-if="isPaid"
                                        class="absolute inset-0 bg-green-500/20 flex items-center justify-center rounded"
                                    >
                                        <CheckCircleIcon
                                            class="h-32 w-32 text-green-500"
                                        />
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="w-80 h-80 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded"
                                >
                                    <QrCodeIcon
                                        class="h-24 w-24 text-gray-400"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Instrucciones -->
                        <div
                            class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6 mb-6"
                        >
                            <h4
                                class="font-semibold text-gray-900 dark:text-gray-100 mb-3"
                            >
                                📱 Instrucciones para pagar:
                            </h4>
                            <ol
                                class="space-y-2 text-sm text-gray-700 dark:text-gray-300"
                            >
                                <li class="flex items-start">
                                    <span
                                        class="inline-block w-6 h-6 bg-indigo-600 text-white rounded-full text-center mr-2 flex-shrink-0"
                                        >1</span
                                    >
                                    <span
                                        >Abre tu app de
                                        <strong>Tigo Money</strong>,
                                        <strong>Banco</strong> o aplicación de
                                        pagos QR</span
                                    >
                                </li>
                                <li class="flex items-start">
                                    <span
                                        class="inline-block w-6 h-6 bg-indigo-600 text-white rounded-full text-center mr-2 flex-shrink-0"
                                        >2</span
                                    >
                                    <span
                                        >Selecciona la opción
                                        <strong>"Pagar con QR"</strong> o
                                        <strong>"Escanear QR"</strong></span
                                    >
                                </li>
                                <li class="flex items-start">
                                    <span
                                        class="inline-block w-6 h-6 bg-indigo-600 text-white rounded-full text-center mr-2 flex-shrink-0"
                                        >3</span
                                    >
                                    <span
                                        >Escanea el código QR mostrado
                                        arriba</span
                                    >
                                </li>
                                <li class="flex items-start">
                                    <span
                                        class="inline-block w-6 h-6 bg-indigo-600 text-white rounded-full text-center mr-2 flex-shrink-0"
                                        >4</span
                                    >
                                    <span
                                        >Confirma el pago en tu aplicación</span
                                    >
                                </li>
                                <li class="flex items-start">
                                    <span
                                        class="inline-block w-6 h-6 bg-indigo-600 text-white rounded-full text-center mr-2 flex-shrink-0"
                                        >5</span
                                    >
                                    <span
                                        >Esta página se actualizará
                                        automáticamente cuando se confirme el
                                        pago</span
                                    >
                                </li>
                            </ol>
                        </div>

                        <!-- Links Alternativos -->
                        <div
                            v-if="pago.checkout_url || pago.deep_link"
                            class="grid grid-cols-1 md:grid-cols-2 gap-4"
                        >
                            <a
                                v-if="pago.checkout_url"
                                :href="pago.checkout_url"
                                target="_blank"
                                class="flex items-center justify-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
                            >
                                <svg
                                    class="h-5 w-5 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                    />
                                </svg>
                                Abrir en Navegador
                            </a>
                            <a
                                v-if="pago.deep_link"
                                :href="pago.deep_link"
                                class="flex items-center justify-center px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition"
                            >
                                <svg
                                    class="h-5 w-5 mr-2"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
                                    />
                                </svg>
                                Abrir en App
                            </a>
                        </div>

                        <!-- Info Técnica (Debug - Ocultar en producción) -->
                        <div
                            v-if="false"
                            class="mt-6 p-4 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono"
                        >
                            <p><strong>ID Pago:</strong> {{ pago.id }}</p>
                            <p>
                                <strong>Transaction ID:</strong>
                                {{ pago.company_transaction_id }}
                            </p>
                            <p><strong>Estado:</strong> {{ paymentStatus }}</p>
                            <p>
                                <strong>Expira:</strong>
                                {{ pago.qr_expiration_date }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
