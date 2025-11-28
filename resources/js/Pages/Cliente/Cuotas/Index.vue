<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref, computed, onUnmounted } from "vue";
import { useAppUrl } from "@/Composables/useAppUrl";
import {
    CurrencyDollarIcon,
    QrCodeIcon,
    CheckCircleIcon,
    ClockIcon,
    XCircleIcon,
    XMarkIcon,
} from "@heroicons/vue/24/outline";
import axios from "axios";

const props = defineProps({
    cuotas: Array,
});

const { resolveUrl } = useAppUrl();

// Estado del modal y pago
const showModal = ref(false);
const currentPago = ref(null);
const pollingInterval = ref(null);
const paymentStatus = ref("PENDING");
const qrImage = ref(null);
const loadingQr = ref(false);

const isPaid = computed(() => paymentStatus.value === "COMPLETED");
const isPending = computed(() => paymentStatus.value === "PENDING");

// Función para iniciar el pago
const iniciarPago = async (cuota) => {
    loadingQr.value = true;
    showModal.value = true;
    currentPago.value = null;
    qrImage.value = null;
    paymentStatus.value = "PENDING";

    try {
        const response = await axios.post(
            resolveUrl(`cliente/cuotas/${cuota.id}/generar-qr`)
        );

        if (response.data.success) {
            currentPago.value = response.data;
            qrImage.value = response.data.qr_image;

            // Iniciar polling
            startPolling(response.data.pago_id);
        } else {
            alert("Error al generar QR: " + response.data.message);
            showModal.value = false;
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Error al generar QR. Intente nuevamente.");
        showModal.value = false;
    } finally {
        loadingQr.value = false;
    }
};

const startPolling = (pagoId) => {
    if (pollingInterval.value) clearInterval(pollingInterval.value);

    pollingInterval.value = setInterval(async () => {
        try {
            const response = await axios.get(
                resolveUrl(`cliente/pagos/${pagoId}/status`)
            );

            paymentStatus.value = response.data.status;

            if (response.data.status === "COMPLETED") {
                clearInterval(pollingInterval.value);
                // Recargar la página después de unos segundos para actualizar la lista
                setTimeout(() => {
                    closeModal();
                    router.reload({ only: ["cuotas"] });
                }, 3000);
            }
        } catch (error) {
            console.error("Error polling status:", error);
        }
    }, 3000);
};

const closeModal = () => {
    showModal.value = false;
    if (pollingInterval.value) clearInterval(pollingInterval.value);
    currentPago.value = null;
};

onUnmounted(() => {
    if (pollingInterval.value) clearInterval(pollingInterval.value);
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value);
};
</script>

<template>
    <Head title="Mis Cuotas" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Mis Cuotas Pendientes
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="overflow-x-auto">
                            <table
                                class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                        >
                                            Cuota
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                        >
                                            Vencimiento
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                        >
                                            Viaje
                                        </th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                        >
                                            Monto
                                        </th>
                                        <th
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                        >
                                            Estado
                                        </th>
                                        <th
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                        >
                                            Acción
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                                >
                                    <tr
                                        v-for="cuota in cuotas"
                                        :key="cuota.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            Cuota #{{ cuota.numero_cuota }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            {{ cuota.fecha_vencimiento }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            {{ cuota.destino }}
                                            <span class="text-xs block">{{
                                                cuota.viaje_fecha
                                            }}</span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-900 dark:text-gray-100"
                                        >
                                            {{
                                                formatCurrency(
                                                    cuota.saldo_pendiente
                                                )
                                            }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-center"
                                        >
                                            <span
                                                :class="[
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                    cuota.estado === 'PAGADO'
                                                        ? 'bg-green-100 text-green-800'
                                                        : cuota.estado ===
                                                          'VENCIDO'
                                                        ? 'bg-red-100 text-red-800'
                                                        : 'bg-yellow-100 text-yellow-800',
                                                ]"
                                            >
                                                {{ cuota.estado }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium"
                                        >
                                            <button
                                                v-if="cuota.puede_pagar"
                                                @click="iniciarPago(cuota)"
                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-bold flex items-center justify-center mx-auto"
                                            >
                                                <QrCodeIcon
                                                    class="h-5 w-5 mr-1"
                                                />
                                                Pagar con QR
                                            </button>
                                            <span
                                                v-else
                                                class="text-gray-400 flex items-center justify-center"
                                            >
                                                <CheckCircleIcon
                                                    class="h-5 w-5 mr-1"
                                                />
                                                Pagado
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="cuotas.length === 0">
                                        <td
                                            colspan="6"
                                            class="px-6 py-10 text-center text-gray-500 dark:text-gray-400"
                                        >
                                            No tienes cuotas pendientes.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Pago QR -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
            >
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true"
                    @click="closeModal"
                ></div>

                <span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen"
                    aria-hidden="true"
                    >&#8203;</span
                >

                <div
                    class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                >
                    <div
                        class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4"
                    >
                        <div class="absolute top-0 right-0 pt-4 pr-4">
                            <button
                                @click="closeModal"
                                type="button"
                                class="bg-white dark:bg-gray-800 rounded-md text-gray-400 hover:text-gray-500 focus:outline-none"
                            >
                                <span class="sr-only">Cerrar</span>
                                <XMarkIcon class="h-6 w-6" />
                            </button>
                        </div>

                        <div
                            class="sm:flex sm:items-start w-full justify-center"
                        >
                            <div
                                class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full"
                            >
                                <h3
                                    class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 text-center mb-4"
                                    id="modal-title"
                                >
                                    Escanear para Pagar
                                </h3>

                                <div class="mt-2 flex flex-col items-center">
                                    <div
                                        v-if="loadingQr"
                                        class="flex flex-col items-center justify-center p-10"
                                    >
                                        <div
                                            class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"
                                        ></div>
                                        <p
                                            class="mt-4 text-gray-500 dark:text-gray-400"
                                        >
                                            Generando código QR...
                                        </p>
                                    </div>

                                    <div v-else-if="qrImage" class="relative">
                                        <img
                                            :src="`data:image/png;base64,${qrImage}`"
                                            alt="Código QR"
                                            class="w-64 h-64 object-contain mx-auto"
                                        />

                                        <!-- Overlay de éxito -->
                                        <div
                                            v-if="isPaid"
                                            class="absolute inset-0 bg-white/90 dark:bg-gray-800/90 flex flex-col items-center justify-center rounded-lg"
                                        >
                                            <CheckCircleIcon
                                                class="h-20 w-20 text-green-500 mb-2"
                                            />
                                            <p
                                                class="text-xl font-bold text-green-600"
                                            >
                                                ¡Pago Exitoso!
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        v-if="!loadingQr && qrImage && !isPaid"
                                        class="mt-4 text-center"
                                    >
                                        <p
                                            class="text-sm text-gray-500 dark:text-gray-400 mb-2"
                                        >
                                            Monto a pagar:
                                            <span
                                                class="font-bold text-gray-900 dark:text-gray-100"
                                                >{{
                                                    formatCurrency(
                                                        currentPago?.monto
                                                    )
                                                }}</span
                                            >
                                        </p>
                                        <div
                                            class="flex items-center justify-center text-blue-600 dark:text-blue-400 animate-pulse"
                                        >
                                            <ClockIcon class="h-4 w-4 mr-1" />
                                            <span class="text-sm"
                                                >Esperando confirmación...</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
