<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import Modal from "@/Components/Modal.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import {
    ArrowLeftIcon,
    ShoppingCartIcon,
    UserIcon,
    CalendarDaysIcon,
    MapPinIcon,
    CurrencyDollarIcon,
    CreditCardIcon,
    BanknotesIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
    DocumentTextIcon,
    TicketIcon,
    PlusIcon,
    PrinterIcon,
    ExclamationTriangleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    venta: Object,
    infoCuotas: Object,
    estadosPago: Array,
    metodosPago: Array,
});

// Modal para registrar pago
const showPagoModal = ref(false);
const cuotaSeleccionada = ref(null);

const pagoForm = useForm({
    monto: 0,
    metodo_pago: 'EFECTIVO',
    referencia: '',
    cuota_id: null,
});

const openPagoModal = (cuota = null) => {
    cuotaSeleccionada.value = cuota;
    pagoForm.cuota_id = cuota?.id || null;
    pagoForm.monto = cuota ? cuota.monto_total_cuota : props.venta.saldo_pendiente;
    pagoForm.metodo_pago = 'EFECTIVO';
    pagoForm.referencia = '';
    showPagoModal.value = true;
};

const closePagoModal = () => {
    showPagoModal.value = false;
    cuotaSeleccionada.value = null;
    pagoForm.reset();
};

const submitPago = () => {
    pagoForm.post(route('ventas.registrar-pago', props.venta.id), {
        preserveScroll: true,
        onSuccess: () => {
            closePagoModal();
        },
    });
};

const cancelarVenta = () => {
    if (confirm('¿Está seguro de cancelar esta venta? Esta acción liberará los cupos reservados.')) {
        router.delete(route('ventas.destroy', props.venta.id));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-BO", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleDateString("es-BO", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value);
};

const getEstadoClasses = (estado) => {
    const colorMap = {
        PENDIENTE: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
        PARCIAL: 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
        COMPLETADO: 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
        ANULADO: 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
    };
    return colorMap[estado] || 'bg-gray-100 text-gray-800';
};

const getEstadoCuotaClasses = (estado) => {
    const colorMap = {
        PENDIENTE: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
        PAGADO: 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
        VENCIDO: 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
    };
    return colorMap[estado] || 'bg-gray-100 text-gray-800';
};

const getEstadoLabel = (estado) => {
    const estadoObj = props.estadosPago.find(e => e.value === estado);
    return estadoObj?.label || estado;
};

const metodoRequiereReferencia = computed(() => {
    const metodo = props.metodosPago.find(m => m.value === pagoForm.metodo_pago);
    return metodo?.requiereReferencia || false;
});

const getMetodoLabel = (metodo) => {
    const obj = props.metodosPago.find(m => m.value === metodo);
    return obj?.label || metodo;
};
</script>

<template>
    <Head :title="`Venta #${venta.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <Link
                        :href="route('ventas.index')"
                        class="mr-4 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        <ArrowLeftIcon class="h-5 w-5 text-gray-500" />
                    </Link>
                    <ShoppingCartIcon class="h-6 w-6 mr-2 text-indigo-600" />
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Venta #{{ venta.id }}
                    </h2>
                    <span
                        :class="[
                            'ml-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                            getEstadoClasses(venta.estado_pago)
                        ]"
                    >
                        {{ getEstadoLabel(venta.estado_pago) }}
                    </span>
                </div>
                <div class="flex space-x-2">
                    <a
                        :href="route('ventas.comprobante', venta.id)"
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                    >
                        <DocumentTextIcon class="h-4 w-4 mr-1" />
                        Comprobante
                    </a>
                    <a
                        v-if="venta.estado_pago === 'COMPLETADO'"
                        :href="route('ventas.boleto', venta.id)"
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition"
                    >
                        <TicketIcon class="h-4 w-4 mr-1" />
                        Boleto
                    </a>
                    <button
                        v-if="venta.estado_pago !== 'COMPLETADO' && venta.estado_pago !== 'ANULADO'"
                        @click="openPagoModal()"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition"
                    >
                        <PlusIcon class="h-4 w-4 mr-1" />
                        Registrar Pago
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Columna principal -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Información del Viaje -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                    <CalendarDaysIcon class="h-5 w-5 mr-2 text-indigo-600" />
                                    Información del Viaje
                                </h3>
                                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg p-6 text-white">
                                    <h4 class="text-xl font-bold mb-2">{{ venta.viaje?.plan_viaje?.nombre }}</h4>
                                    <div class="flex items-center text-indigo-100 mb-4">
                                        <MapPinIcon class="h-5 w-5 mr-1" />
                                        {{ venta.viaje?.plan_viaje?.destino?.nombre_lugar }}, {{ venta.viaje?.plan_viaje?.destino?.ciudad }}
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-indigo-200 text-sm">Fecha de Salida</p>
                                            <p class="font-semibold">{{ formatDate(venta.viaje?.fecha_salida) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-indigo-200 text-sm">Fecha de Retorno</p>
                                            <p class="font-semibold">{{ formatDate(venta.viaje?.fecha_retorno) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-indigo-200 text-sm">Duración</p>
                                            <p class="font-semibold">{{ venta.viaje?.plan_viaje?.duracion_dias }} días</p>
                                        </div>
                                        <div>
                                            <p class="text-indigo-200 text-sm">Precio por Persona</p>
                                            <p class="font-semibold">{{ formatCurrency(venta.viaje?.plan_viaje?.precio_base) }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Itinerario resumido -->
                                <div v-if="venta.viaje?.plan_viaje?.actividades_diarias?.length" class="mt-4">
                                    <h5 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Itinerario</h5>
                                    <div class="space-y-2 max-h-48 overflow-y-auto">
                                        <div
                                            v-for="(actividades, dia) in Object.groupBy(venta.viaje.plan_viaje.actividades_diarias, 'dia_numero')"
                                            :key="dia"
                                            class="p-2 bg-gray-50 dark:bg-gray-700/50 rounded"
                                        >
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Día {{ dia }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ actividades.map(a => a.descripcion_actividad).join(' - ') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Plan de Pagos (si es crédito) -->
                        <div v-if="venta.tipo_pago === 'CREDITO' && venta.plan_pago" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                                        <CreditCardIcon class="h-5 w-5 mr-2 text-blue-600" />
                                        Plan de Pagos
                                    </h3>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ venta.plan_pago.cantidad_cuotas }} cuotas - {{ venta.plan_pago.tasa_interes }}% interés
                                    </div>
                                </div>

                                <!-- Resumen de cuotas -->
                                <div v-if="infoCuotas" class="grid grid-cols-4 gap-4 mb-4">
                                    <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
                                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ infoCuotas.pagadas }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Pagadas</p>
                                    </div>
                                    <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg text-center">
                                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ infoCuotas.pendientes }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Pendientes</p>
                                    </div>
                                    <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg text-center">
                                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ infoCuotas.vencidas }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Vencidas</p>
                                    </div>
                                    <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg text-center">
                                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ infoCuotas.total }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                                    </div>
                                </div>

                                <!-- Tabla de cuotas -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">#</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Vencimiento</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Capital</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Interés</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr v-for="cuota in venta.plan_pago.cuotas" :key="cuota.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ cuota.numero_cuota }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ formatDate(cuota.fecha_vencimiento) }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ formatCurrency(cuota.monto_capital) }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ formatCurrency(cuota.monto_interes) }}</td>
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(cuota.monto_total_cuota) }}</td>
                                                <td class="px-4 py-3">
                                                    <span :class="['inline-flex px-2 py-0.5 rounded-full text-xs font-medium', getEstadoCuotaClasses(cuota.estado_cuota)]">
                                                        {{ cuota.estado_cuota }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <button
                                                        v-if="cuota.estado_cuota !== 'PAGADO'"
                                                        @click="openPagoModal(cuota)"
                                                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 text-sm font-medium"
                                                    >
                                                        Pagar
                                                    </button>
                                                    <CheckCircleIcon v-else class="h-5 w-5 text-green-500 inline" />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Historial de Pagos -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                    <BanknotesIcon class="h-5 w-5 mr-2 text-green-600" />
                                    Historial de Pagos
                                </h3>

                                <div v-if="venta.pagos?.length" class="space-y-3">
                                    <div
                                        v-for="pago in venta.pagos"
                                        :key="pago.id"
                                        class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg"
                                    >
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-100 dark:bg-green-800 flex items-center justify-center">
                                                <CheckCircleIcon class="h-5 w-5 text-green-600 dark:text-green-400" />
                                            </div>
                                            <div class="ml-3">
                                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                                    {{ formatCurrency(pago.monto_pagado) }}
                                                </p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ getMetodoLabel(pago.metodo_pago) }}
                                                    <span v-if="pago.referencia_comprobante" class="ml-1">
                                                        - Ref: {{ pago.referencia_comprobante }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDateTime(pago.fecha_pago) }}
                                            <p v-if="pago.cuota_id" class="text-xs">
                                                Cuota #{{ pago.cuota?.numero_cuota }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <BanknotesIcon class="h-12 w-12 mx-auto mb-2 text-gray-300 dark:text-gray-600" />
                                    <p>No hay pagos registrados</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna lateral -->
                    <div class="space-y-6">
                        <!-- Resumen financiero -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                    <CurrencyDollarIcon class="h-5 w-5 mr-2 text-green-600" />
                                    Resumen
                                </h3>

                                <div class="space-y-3">
                                    <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                        <span class="text-gray-600 dark:text-gray-400">Tipo de Pago:</span>
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                                                venta.tipo_pago === 'CONTADO' 
                                                    ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200' 
                                                    : 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200'
                                            ]"
                                        >
                                            {{ venta.tipo_pago === 'CONTADO' ? 'Contado' : 'Crédito' }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                        <span class="text-gray-600 dark:text-gray-400">Monto Total:</span>
                                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ formatCurrency(venta.monto_total) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-700">
                                        <span class="text-gray-600 dark:text-gray-400">Pagado:</span>
                                        <span class="font-semibold text-green-600 dark:text-green-400">{{ formatCurrency(venta.monto_pagado) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-gray-600 dark:text-gray-400">Saldo Pendiente:</span>
                                        <span
                                            :class="[
                                                'font-bold text-lg',
                                                venta.saldo_pendiente > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'
                                            ]"
                                        >
                                            {{ formatCurrency(venta.saldo_pendiente) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Barra de progreso -->
                                <div class="mt-4">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-500 dark:text-gray-400">Progreso de pago</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ venta.porcentaje_pagado }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                        <div
                                            class="h-3 rounded-full transition-all duration-500"
                                            :class="[
                                                venta.porcentaje_pagado >= 100 ? 'bg-green-500' :
                                                venta.porcentaje_pagado > 0 ? 'bg-blue-500' : 'bg-gray-400'
                                            ]"
                                            :style="{ width: Math.min(venta.porcentaje_pagado, 100) + '%' }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Cliente -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                    <UserIcon class="h-5 w-5 mr-2 text-indigo-600" />
                                    Cliente
                                </h3>
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                        <UserIcon class="h-6 w-6 text-white" />
                                    </div>
                                    <div class="ml-4">
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ venta.cliente?.name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ venta.cliente?.email }}</p>
                                        <p v-if="venta.cliente?.telefono" class="text-sm text-gray-500 dark:text-gray-400">Tel: {{ venta.cliente?.telefono }}</p>
                                        <p v-if="venta.cliente?.ci_nit" class="text-sm text-gray-500 dark:text-gray-400">CI/NIT: {{ venta.cliente?.ci_nit }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Vendedor -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                    <UserIcon class="h-5 w-5 mr-2 text-purple-600" />
                                    Vendedor
                                </h3>
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center">
                                        <UserIcon class="h-6 w-6 text-white" />
                                    </div>
                                    <div class="ml-4">
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ venta.vendedor?.name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ venta.vendedor?.email }}</p>
                                    </div>
                                </div>
                                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                                    <ClockIcon class="h-4 w-4 inline mr-1" />
                                    Fecha de venta: {{ formatDateTime(venta.fecha_venta) }}
                                </p>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div v-if="venta.estado_pago !== 'ANULADO' && venta.pagos?.length === 0" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                    <ExclamationTriangleIcon class="h-5 w-5 mr-2 text-red-600" />
                                    Zona de Peligro
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    Cancelar esta venta liberará los cupos reservados. Esta acción no se puede deshacer.
                                </p>
                                <DangerButton @click="cancelarVenta" class="w-full justify-center">
                                    <XCircleIcon class="h-4 w-4 mr-1" />
                                    Cancelar Venta
                                </DangerButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Registrar Pago -->
        <Modal :show="showPagoModal" @close="closePagoModal">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    {{ cuotaSeleccionada ? `Pagar Cuota #${cuotaSeleccionada.numero_cuota}` : 'Registrar Pago' }}
                </h3>

                <form @submit.prevent="submitPago" class="space-y-4">
                    <div>
                        <InputLabel for="monto" value="Monto a Pagar *" />
                        <div class="relative mt-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                            <TextInput
                                id="monto"
                                type="number"
                                step="0.01"
                                min="0.01"
                                :max="venta.saldo_pendiente"
                                class="pl-8 block w-full"
                                v-model.number="pagoForm.monto"
                            />
                        </div>
                        <InputError :message="pagoForm.errors.monto" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="metodo_pago" value="Método de Pago *" />
                        <select
                            id="metodo_pago"
                            v-model="pagoForm.metodo_pago"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option v-for="metodo in metodosPago" :key="metodo.value" :value="metodo.value">
                                {{ metodo.label }}
                            </option>
                        </select>
                        <InputError :message="pagoForm.errors.metodo_pago" class="mt-2" />
                    </div>

                    <div v-if="metodoRequiereReferencia">
                        <InputLabel for="referencia" value="Número de Referencia / Comprobante" />
                        <TextInput
                            id="referencia"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="pagoForm.referencia"
                            placeholder="Ej: Número de transacción"
                        />
                        <InputError :message="pagoForm.errors.referencia" class="mt-2" />
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <SecondaryButton @click="closePagoModal" type="button">
                            Cancelar
                        </SecondaryButton>
                        <PrimaryButton :disabled="pagoForm.processing">
                            {{ pagoForm.processing ? 'Procesando...' : 'Registrar Pago' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
