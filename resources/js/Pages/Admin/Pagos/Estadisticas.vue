<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import {
    ArrowLeftIcon,
    ChartBarIcon,
    CurrencyDollarIcon,
    BanknotesIcon,
    CreditCardIcon,
    QrCodeIcon,
    BuildingLibraryIcon,
    CalendarDaysIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    pagosPorDia: Array,
    pagosPorMetodo: Array,
    totales: Object,
    filtros: Object,
});

const fechaInicio = ref(props.filtros.fecha_inicio);
const fechaFin = ref(props.filtros.fecha_fin);

const applyFilters = () => {
    router.get(
        "/pagos/estadisticas",
        {
            fecha_inicio: fechaInicio.value,
            fecha_fin: fechaFin.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value || 0);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-BO", {
        month: "short",
        day: "numeric",
    });
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
        EFECTIVO: "bg-green-500",
        TARJETA: "bg-blue-500",
        QR: "bg-purple-500",
        TRANSFERENCIA: "bg-orange-500",
    };
    return classes[metodo] || "bg-gray-500";
};

// Calcular el máximo para las barras del gráfico
const maxPagoDia = computed(() => {
    if (!props.pagosPorDia?.length) return 0;
    return Math.max(...props.pagosPorDia.map((p) => parseFloat(p.total)));
});

const totalMetodos = computed(() => {
    if (!props.pagosPorMetodo?.length) return 0;
    return props.pagosPorMetodo.reduce(
        (sum, m) => sum + parseFloat(m.total),
        0
    );
});
</script>

<template>
    <Head title="Estadísticas de Pagos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link
                    :href="'/pagos'"
                    class="mr-4 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-500" />
                </Link>
                <ChartBarIcon class="h-6 w-6 mr-2 text-indigo-600" />
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Estadísticas de Pagos
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtro de fechas -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4 mb-6"
                >
                    <div class="flex flex-col md:flex-row gap-4 items-end">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                            >
                                Fecha Inicio
                            </label>
                            <input
                                type="date"
                                v-model="fechaInicio"
                                @change="applyFilters"
                                class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                            >
                                Fecha Fin
                            </label>
                            <input
                                type="date"
                                v-model="fechaFin"
                                @change="applyFilters"
                                class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Tarjetas de resumen -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Total Recaudado
                                </p>
                                <p
                                    class="text-3xl font-bold text-gray-900 dark:text-gray-100"
                                >
                                    {{ formatCurrency(totales.monto_total) }}
                                </p>
                            </div>
                            <div
                                class="p-3 bg-green-100 dark:bg-green-900/30 rounded-full"
                            >
                                <CurrencyDollarIcon
                                    class="h-10 w-10 text-green-600 dark:text-green-400"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Cantidad de Pagos
                                </p>
                                <p
                                    class="text-3xl font-bold text-gray-900 dark:text-gray-100"
                                >
                                    {{ totales.cantidad_pagos }}
                                </p>
                            </div>
                            <div
                                class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full"
                            >
                                <BanknotesIcon
                                    class="h-10 w-10 text-blue-600 dark:text-blue-400"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Promedio por Pago
                                </p>
                                <p
                                    class="text-3xl font-bold text-gray-900 dark:text-gray-100"
                                >
                                    {{ formatCurrency(totales.promedio_pago) }}
                                </p>
                            </div>
                            <div
                                class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-full"
                            >
                                <ChartBarIcon
                                    class="h-10 w-10 text-purple-600 dark:text-purple-400"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Gráfico de pagos por día -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center"
                        >
                            <CalendarDaysIcon
                                class="h-5 w-5 mr-2 text-indigo-600"
                            />
                            Pagos por Día
                        </h3>

                        <div v-if="pagosPorDia?.length" class="space-y-3">
                            <div
                                v-for="dia in pagosPorDia"
                                :key="dia.fecha"
                                class="flex items-center"
                            >
                                <div
                                    class="w-20 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    {{ formatDate(dia.fecha) }}
                                </div>
                                <div class="flex-1 mx-4">
                                    <div
                                        class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-6 relative"
                                    >
                                        <div
                                            class="bg-gradient-to-r from-green-500 to-emerald-600 h-6 rounded-full transition-all duration-500 flex items-center justify-end pr-2"
                                            :style="{
                                                width:
                                                    maxPagoDia > 0
                                                        ? (parseFloat(
                                                              dia.total
                                                          ) /
                                                              maxPagoDia) *
                                                              100 +
                                                          '%'
                                                        : '0%',
                                            }"
                                        >
                                            <span
                                                v-if="
                                                    parseFloat(dia.total) /
                                                        maxPagoDia >
                                                    0.3
                                                "
                                                class="text-xs text-white font-medium"
                                            >
                                                {{ formatCurrency(dia.total) }}
                                            </span>
                                        </div>
                                        <span
                                            v-if="
                                                parseFloat(dia.total) /
                                                    maxPagoDia <=
                                                0.3
                                            "
                                            class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-600 dark:text-gray-400 font-medium"
                                        >
                                            {{ formatCurrency(dia.total) }}
                                        </span>
                                    </div>
                                </div>
                                <div
                                    class="w-12 text-right text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ dia.cantidad }}
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="text-center py-8 text-gray-500 dark:text-gray-400"
                        >
                            <ChartBarIcon
                                class="h-12 w-12 mx-auto mb-2 text-gray-300"
                            />
                            <p>No hay datos para el período seleccionado</p>
                        </div>
                    </div>

                    <!-- Gráfico de pagos por método -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <h3
                            class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center"
                        >
                            <CreditCardIcon
                                class="h-5 w-5 mr-2 text-blue-600"
                            />
                            Pagos por Método
                        </h3>

                        <div v-if="pagosPorMetodo?.length" class="space-y-4">
                            <!-- Gráfico de dona simulado con barras -->
                            <div
                                class="flex h-8 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700"
                            >
                                <div
                                    v-for="metodo in pagosPorMetodo"
                                    :key="metodo.metodo"
                                    :class="getMetodoBgClass(metodo.metodo)"
                                    :style="{
                                        width:
                                            totalMetodos > 0
                                                ? (parseFloat(metodo.total) /
                                                      totalMetodos) *
                                                      100 +
                                                  '%'
                                                : '0%',
                                    }"
                                    class="transition-all duration-500"
                                    :title="`${metodo.label}: ${formatCurrency(
                                        metodo.total
                                    )}`"
                                ></div>
                            </div>

                            <!-- Leyenda -->
                            <div class="space-y-3 mt-6">
                                <div
                                    v-for="metodo in pagosPorMetodo"
                                    :key="metodo.metodo"
                                    class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                                >
                                    <div class="flex items-center">
                                        <div
                                            :class="[
                                                'w-4 h-4 rounded mr-3',
                                                getMetodoBgClass(metodo.metodo),
                                            ]"
                                        ></div>
                                        <component
                                            :is="getMetodoIcon(metodo.metodo)"
                                            class="h-5 w-5 mr-2 text-gray-500"
                                        />
                                        <span
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                            >{{ metodo.label }}</span
                                        >
                                    </div>
                                    <div class="text-right">
                                        <p
                                            class="font-bold text-gray-900 dark:text-gray-100"
                                        >
                                            {{ formatCurrency(metodo.total) }}
                                        </p>
                                        <p
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            {{ metodo.cantidad }} pagos
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="text-center py-8 text-gray-500 dark:text-gray-400"
                        >
                            <CreditCardIcon
                                class="h-12 w-12 mx-auto mb-2 text-gray-300"
                            />
                            <p>No hay datos para el período seleccionado</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
