<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    ArrowLeftIcon,
    BanknotesIcon,
    CreditCardIcon,
    QrCodeIcon,
    BuildingLibraryIcon,
    DocumentTextIcon,
    UserIcon,
    MapPinIcon,
    CalendarDaysIcon,
    CheckCircleIcon,
    PlusIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    venta: Object,
    metodosPago: Array,
    resumen: Object,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-BO", {
        year: "numeric",
        month: "short",
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
    }).format(value || 0);
};

const getMetodoLabel = (metodo) => {
    const obj = props.metodosPago.find((m) => m.value === metodo);
    return obj?.label || metodo;
};

const getMetodoIcon = (metodo) => {
    const icons = {
        EFECTIVO: BanknotesIcon,
        TARJETA: CreditCardIcon,
        QR: QrCodeIcon,
        TRANSFERENCIA: BuildingLibraryIcon,
    };
    return icons[metodo] || BanknotesIcon;
};

const getMetodoBgClass = (metodo) => {
    const classes = {
        EFECTIVO: "bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400",
        TARJETA: "bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400",
        QR: "bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400",
        TRANSFERENCIA: "bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400",
    };
    return classes[metodo] || "bg-gray-100 text-gray-600";
};
</script>

<template>
    <Head :title="`Historial de Pagos - Venta #${venta.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <Link
                        :href="route('ventas.show', venta.id)"
                        class="mr-4 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        <ArrowLeftIcon class="h-5 w-5 text-gray-500" />
                    </Link>
                    <BanknotesIcon class="h-6 w-6 mr-2 text-green-600" />
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Historial de Pagos - Venta #{{ venta.id }}
                    </h2>
                </div>
                <Link
                    v-if="resumen.saldo_pendiente > 0"
                    :href="route('pagos.create', { venta_id: venta.id })"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition"
                >
                    <PlusIcon class="h-4 w-4 mr-1" />
                    Nuevo Pago
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <!-- Información de la venta -->
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg p-6 text-white mb-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-xl font-bold mb-1">{{ venta.viaje?.plan_viaje?.nombre }}</h3>
                            <div class="flex items-center text-indigo-100 mb-2">
                                <MapPinIcon class="h-5 w-5 mr-1" />
                                {{ venta.viaje?.plan_viaje?.destino?.nombre_lugar }}, {{ venta.viaje?.plan_viaje?.destino?.ciudad }}
                            </div>
                            <div class="flex items-center text-indigo-100">
                                <UserIcon class="h-5 w-5 mr-1" />
                                {{ venta.cliente?.name }} ({{ venta.cliente?.email }})
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-indigo-200 text-sm">Fecha de Venta</p>
                            <p class="font-semibold">{{ formatDate(venta.fecha_venta) }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Resumen financiero -->
                    <div class="lg:col-span-1">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 sticky top-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                Resumen Financiero
                            </h3>

                            <div class="space-y-4">
                                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Monto Total</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ formatCurrency(resumen.monto_total) }}
                                    </p>
                                </div>

                                <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                    <p class="text-sm text-green-600 dark:text-green-400">Total Pagado</p>
                                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                        {{ formatCurrency(resumen.monto_pagado) }}
                                    </p>
                                </div>

                                <div class="p-4 rounded-lg" :class="resumen.saldo_pendiente > 0 ? 'bg-red-50 dark:bg-red-900/20' : 'bg-green-50 dark:bg-green-900/20'">
                                    <p class="text-sm" :class="resumen.saldo_pendiente > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                        Saldo Pendiente
                                    </p>
                                    <p class="text-2xl font-bold" :class="resumen.saldo_pendiente > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'">
                                        {{ formatCurrency(resumen.saldo_pendiente) }}
                                    </p>
                                </div>

                                <!-- Barra de progreso -->
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-500 dark:text-gray-400">Progreso</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ resumen.porcentaje_pagado }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                                        <div
                                            class="h-3 rounded-full transition-all duration-500"
                                            :class="resumen.porcentaje_pagado >= 100 ? 'bg-green-500' : 'bg-blue-500'"
                                            :style="{ width: Math.min(resumen.porcentaje_pagado, 100) + '%' }"
                                        ></div>
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-gray-200 dark:border-gray-700 text-center">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ resumen.cantidad_pagos }} pagos registrados
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de pagos -->
                    <div class="lg:col-span-2">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                    Detalle de Pagos
                                </h3>

                                <div v-if="venta.pagos?.length" class="space-y-4">
                                    <div
                                        v-for="(pago, index) in venta.pagos"
                                        :key="pago.id"
                                        class="relative p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:shadow-md transition"
                                    >
                                        <!-- Número de pago -->
                                        <div class="absolute -left-3 -top-3 w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center font-bold text-sm">
                                            {{ venta.pagos.length - index }}
                                        </div>

                                        <div class="flex items-start justify-between ml-4">
                                            <div class="flex-1">
                                                <div class="flex items-center mb-2">
                                                    <span
                                                        :class="[
                                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mr-2',
                                                            getMetodoBgClass(pago.metodo_pago)
                                                        ]"
                                                    >
                                                        <component :is="getMetodoIcon(pago.metodo_pago)" class="h-3 w-3 mr-1" />
                                                        {{ getMetodoLabel(pago.metodo_pago) }}
                                                    </span>
                                                    <span v-if="pago.cuota" class="text-xs text-gray-500 dark:text-gray-400">
                                                        Cuota #{{ pago.cuota.numero_cuota }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                                    <CalendarDaysIcon class="h-4 w-4 mr-1" />
                                                    {{ formatDateTime(pago.fecha_pago) }}
                                                </div>

                                                <div v-if="pago.referencia_comprobante" class="mt-2 text-sm">
                                                    <span class="text-gray-500 dark:text-gray-400">Referencia: </span>
                                                    <span class="font-mono text-gray-900 dark:text-gray-100">{{ pago.referencia_comprobante }}</span>
                                                </div>
                                            </div>

                                            <div class="text-right">
                                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                                    {{ formatCurrency(pago.monto_pagado) }}
                                                </p>
                                                <a
                                                    :href="route('pagos.comprobante', pago.id)"
                                                    target="_blank"
                                                    class="inline-flex items-center text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 mt-2"
                                                >
                                                    <DocumentTextIcon class="h-4 w-4 mr-1" />
                                                    Comprobante
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="text-center py-12">
                                    <BanknotesIcon class="h-16 w-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" />
                                    <p class="text-gray-500 dark:text-gray-400">No hay pagos registrados para esta venta</p>
                                    <Link
                                        :href="route('pagos.create', { venta_id: venta.id })"
                                        class="inline-flex items-center mt-4 text-green-600 dark:text-green-400 hover:text-green-800 font-medium"
                                    >
                                        <PlusIcon class="h-5 w-5 mr-1" />
                                        Registrar primer pago
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Cuotas pendientes (si es crédito) -->
                        <div v-if="venta.plan_pago?.cuotas?.length" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-6">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                    Cuotas del Plan de Pago
                                </h3>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">#</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Vencimiento</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Monto</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr v-for="cuota in venta.plan_pago.cuotas" :key="cuota.id">
                                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ cuota.numero_cuota }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ formatDate(cuota.fecha_vencimiento) }}</td>
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(cuota.monto_total_cuota) }}</td>
                                                <td class="px-4 py-3">
                                                    <span
                                                        :class="[
                                                            'inline-flex px-2 py-0.5 rounded-full text-xs font-medium',
                                                            cuota.estado_cuota === 'PAGADO' ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' :
                                                            cuota.estado_cuota === 'VENCIDO' ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' :
                                                            'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200'
                                                        ]"
                                                    >
                                                        <CheckCircleIcon v-if="cuota.estado_cuota === 'PAGADO'" class="h-3 w-3 mr-1" />
                                                        {{ cuota.estado_cuota }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
