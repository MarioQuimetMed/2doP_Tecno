<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from "chart.js";
import { Line, Bar } from "vue-chartjs";
import {
    ShoppingCartIcon,
    CalendarDaysIcon,
    UserGroupIcon,
    CurrencyDollarIcon,
    ArrowTrendingUpIcon,
    GlobeAmericasIcon,
    PlusCircleIcon,
    EyeIcon,
} from "@heroicons/vue/24/outline";

// Registrar componentes de Chart.js
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const props = defineProps({
    stats: Object,
    ventasPorMes: Array,
    ultimasVentas: Array,
    viajesDisponibles: Array,
});

// Configuración del gráfico de líneas (Mis ventas por mes)
const ventasChartData = computed(() => ({
    labels: props.ventasPorMes?.map((v) => v.label) || [],
    datasets: [
        {
            label: "Mis Ventas ($)",
            data: props.ventasPorMes?.map((v) => v.monto) || [],
            borderColor: "rgb(59, 130, 246)",
            backgroundColor: "rgba(59, 130, 246, 0.1)",
            fill: true,
            tension: 0.4,
        },
    ],
}));

const ventasChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: "top",
        },
        title: {
            display: true,
            text: "Mis Ventas por Mes",
        },
    },
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value || 0);
};

const getEstadoColor = (estado) => {
    const colores = {
        Pendiente: "bg-amber-100 text-amber-800",
        Parcial: "bg-blue-100 text-blue-800",
        Completado: "bg-emerald-100 text-emerald-800",
        Anulado: "bg-red-100 text-red-800",
    };
    return colores[estado] || "bg-gray-100 text-gray-800";
};
</script>

<template>
    <Head title="Dashboard Vendedor" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Panel de Vendedor
                </h2>
                <Link
                    href="/ventas/create"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition"
                >
                    <PlusCircleIcon class="h-4 w-4 mr-2" />
                    Nueva Venta
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6"
                >
                    <!-- Mis Ventas Totales -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400"
                            >
                                <ShoppingCartIcon class="h-8 w-8" />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Mis Ventas Totales
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.mis_ventas }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Ventas Mes -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-400"
                            >
                                <CalendarDaysIcon class="h-8 w-8" />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Ventas este Mes
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.ventas_mes }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Ingresos del Mes -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400"
                            >
                                <CurrencyDollarIcon class="h-8 w-8" />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Ingresos Mes
                                </p>
                                <p
                                    class="text-xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ formatCurrency(stats.ingresos_mes) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Comisión Estimada -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-amber-100 dark:bg-amber-900 text-amber-600 dark:text-amber-400"
                            >
                                <ArrowTrendingUpIcon class="h-8 w-8" />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Comisión (5%)
                                </p>
                                <p
                                    class="text-xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ formatCurrency(stats.comision_mes) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Viajes Disponibles -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-rose-100 dark:bg-rose-900 text-rose-600 dark:text-rose-400"
                            >
                                <UserGroupIcon class="h-8 w-8" />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Viajes Disponibles
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.viajes_disponibles }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfico y Tablas -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Gráfico de Ventas -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="h-80">
                            <Line
                                v-if="ventasPorMes?.length"
                                :data="ventasChartData"
                                :options="ventasChartOptions"
                            />
                            <div
                                v-else
                                class="flex items-center justify-center h-full text-gray-400"
                            >
                                <p>No hay datos de ventas disponibles</p>
                            </div>
                        </div>
                    </div>

                    <!-- Últimas Ventas -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center"
                        >
                            <ShoppingCartIcon class="h-5 w-5 mr-2" />
                            Mis Últimas Ventas
                        </h3>
                        <div class="space-y-3 max-h-64 overflow-y-auto">
                            <div
                                v-for="venta in ultimasVentas"
                                :key="venta.id"
                                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                            >
                                <div>
                                    <p
                                        class="font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ venta.cliente }}
                                    </p>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ venta.destino }} -
                                        {{ venta.fecha }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p
                                        class="font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ formatCurrency(venta.monto) }}
                                    </p>
                                    <span
                                        :class="[
                                            'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                            getEstadoColor(venta.estado),
                                        ]"
                                    >
                                        {{ venta.estado }}
                                    </span>
                                </div>
                            </div>
                            <p
                                v-if="!ultimasVentas?.length"
                                class="text-center text-gray-400 py-4"
                            >
                                No hay ventas registradas
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Viajes Disponibles para Vender -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                >
                    <div class="flex justify-between items-center mb-4">
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center"
                        >
                            <GlobeAmericasIcon class="h-5 w-5 mr-2" />
                            Viajes Disponibles para Vender
                        </h3>
                        <Link
                            href="/vendedor/viajes-disponibles"
                            class="text-sm text-blue-600 hover:text-blue-700"
                        >
                            Ver todos →
                        </Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <thead>
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                                    >
                                        Destino
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                                    >
                                        Fecha Salida
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                                    >
                                        Precio
                                    </th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                                    >
                                        Cupos
                                    </th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                                    >
                                        Acción
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <tr
                                    v-for="viaje in viajesDisponibles"
                                    :key="viaje.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td
                                        class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ viaje.destino }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300"
                                    >
                                        {{ viaje.fecha_salida }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-sm text-right font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ formatCurrency(viaje.precio) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            :class="[
                                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                viaje.cupos_disponibles <= 5
                                                    ? 'bg-red-100 text-red-800'
                                                    : viaje.cupos_disponibles <=
                                                        10
                                                      ? 'bg-amber-100 text-amber-800'
                                                      : 'bg-emerald-100 text-emerald-800',
                                            ]"
                                        >
                                            {{ viaje.cupos_disponibles }} cupos
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <Link
                                            :href="`/ventas/create?viaje_id=${viaje.id}`"
                                            class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700 transition"
                                        >
                                            <PlusCircleIcon class="h-4 w-4 mr-1" />
                                            Vender
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!viajesDisponibles?.length">
                                    <td
                                        colspan="5"
                                        class="px-4 py-8 text-center text-gray-400"
                                    >
                                        No hay viajes disponibles actualmente
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
