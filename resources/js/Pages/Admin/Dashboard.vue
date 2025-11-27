<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted, computed } from "vue";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from "chart.js";
import { Line, Bar, Doughnut } from "vue-chartjs";
import {
    CurrencyDollarIcon,
    UserGroupIcon,
    GlobeAmericasIcon,
    TicketIcon,
    ChartBarIcon,
    ExclamationTriangleIcon,
    ArrowTrendingUpIcon,
    CalendarDaysIcon,
    DocumentArrowDownIcon,
    ClipboardDocumentListIcon,
} from "@heroicons/vue/24/outline";

// Registrar componentes de Chart.js
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const props = defineProps({
    stats: Object,
    ventasPorMes: Array,
    ventasPorTipo: Object,
    destinosPopulares: Array,
    viajesProximos: Array,
    cuotasVencidas: Array,
    alertas: Object,
});

// Configuración del gráfico de líneas (Ventas por mes)
const ventasChartData = computed(() => ({
    labels: props.ventasPorMes?.map((v) => v.label) || [],
    datasets: [
        {
            label: "Ventas ($)",
            data: props.ventasPorMes?.map((v) => v.monto) || [],
            borderColor: "rgb(16, 185, 129)",
            backgroundColor: "rgba(16, 185, 129, 0.1)",
            fill: true,
            tension: 0.4,
        },
        {
            label: "Cantidad",
            data: props.ventasPorMes?.map((v) => v.cantidad * 100) || [],
            borderColor: "rgb(59, 130, 246)",
            backgroundColor: "rgba(59, 130, 246, 0.1)",
            fill: false,
            tension: 0.4,
            yAxisID: "y1",
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
            text: "Ventas por Mes (Últimos 6 meses)",
        },
    },
    scales: {
        y: {
            type: "linear",
            display: true,
            position: "left",
            title: {
                display: true,
                text: "Monto ($)",
            },
        },
        y1: {
            type: "linear",
            display: true,
            position: "right",
            title: {
                display: true,
                text: "Cantidad (x100)",
            },
            grid: {
                drawOnChartArea: false,
            },
        },
    },
};

// Configuración del gráfico de dona (Tipo de pago)
const tipoPagoChartData = computed(() => ({
    labels: ["Contado", "Crédito"],
    datasets: [
        {
            data: [
                props.ventasPorTipo?.contado || 0,
                props.ventasPorTipo?.credito || 0,
            ],
            backgroundColor: ["rgb(16, 185, 129)", "rgb(59, 130, 246)"],
            borderWidth: 2,
        },
    ],
}));

const tipoPagoChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: "bottom",
        },
        title: {
            display: true,
            text: "Tipo de Pago",
        },
    },
};

// Configuración del gráfico de barras (Destinos populares)
const destinosChartData = computed(() => ({
    labels: props.destinosPopulares?.map((d) => d.nombre) || [],
    datasets: [
        {
            label: "Ventas",
            data: props.destinosPopulares?.map((d) => d.total_ventas) || [],
            backgroundColor: [
                "rgba(16, 185, 129, 0.8)",
                "rgba(59, 130, 246, 0.8)",
                "rgba(245, 158, 11, 0.8)",
                "rgba(139, 92, 246, 0.8)",
                "rgba(239, 68, 68, 0.8)",
            ],
            borderRadius: 8,
        },
    ],
}));

const destinosChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: "y",
    plugins: {
        legend: {
            display: false,
        },
        title: {
            display: true,
            text: "Top 5 Destinos Populares",
        },
    },
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value || 0);
};
</script>

<template>
    <Head title="Dashboard Propietario" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Panel de Control - Propietario
                </h2>
                <div class="flex gap-2">
                    <Link
                        :href="'/reportes'"
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 transition"
                    >
                        <ChartBarIcon class="h-4 w-4 mr-2" />
                        Ver Reportes
                    </Link>
                    <Link
                        :href="'/bitacora'"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition"
                    >
                        <ClipboardDocumentListIcon class="h-4 w-4 mr-2" />
                        Bitácora
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Alertas -->
                <div
                    v-if="alertas?.cuotas_vencidas > 0"
                    class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4"
                >
                    <div class="flex items-center">
                        <ExclamationTriangleIcon
                            class="h-5 w-5 text-red-600 dark:text-red-400 mr-3"
                        />
                        <div>
                            <p
                                class="text-sm font-medium text-red-800 dark:text-red-200"
                            >
                                ¡Atención! Hay
                                {{ alertas.cuotas_vencidas }} cuotas vencidas
                                por un monto de
                                {{ formatCurrency(alertas.monto_vencido) }}
                            </p>
                            <Link
                                :href="'/reportes/pagos-pendientes?tipo=vencidas'"
                                class="text-sm text-red-600 dark:text-red-400 hover:underline"
                            >
                                Ver detalle →
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid Principal -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6"
                >
                    <!-- Total Ventas -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-400"
                            >
                                <CurrencyDollarIcon class="h-8 w-8" />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Total Ventas
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.total_ventas }}
                                </p>
                                <p class="text-xs text-emerald-600">
                                    {{ formatCurrency(stats.ingresos_totales) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Ventas del Mes -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400"
                            >
                                <ArrowTrendingUpIcon class="h-8 w-8" />
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
                                <p class="text-xs text-blue-600">
                                    {{ formatCurrency(stats.ingresos_mes) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Viajes -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400"
                            >
                                <GlobeAmericasIcon class="h-8 w-8" />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Total Viajes
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.total_viajes }}
                                </p>
                                <p class="text-xs text-purple-600">
                                    {{ stats.ocupacion_promedio }}% ocupación
                                    prom.
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
                                class="p-3 rounded-full bg-amber-100 dark:bg-amber-900 text-amber-600 dark:text-amber-400"
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
                                <p class="text-xs text-amber-600">
                                    Con cupos abiertos
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos principales -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Gráfico de Ventas por Mes (2/3) -->
                    <div
                        class="lg:col-span-2 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
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

                    <!-- Gráfico de Tipo de Pago (1/3) -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="h-80">
                            <Doughnut
                                v-if="
                                    ventasPorTipo?.contado ||
                                    ventasPorTipo?.credito
                                "
                                :data="tipoPagoChartData"
                                :options="tipoPagoChartOptions"
                            />
                            <div
                                v-else
                                class="flex items-center justify-center h-full text-gray-400"
                            >
                                <p>Sin datos de tipo de pago</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fila de gráficos y tablas -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Destinos Populares -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="h-80">
                            <Bar
                                v-if="destinosPopulares?.length"
                                :data="destinosChartData"
                                :options="destinosChartOptions"
                            />
                            <div
                                v-else
                                class="flex items-center justify-center h-full text-gray-400"
                            >
                                <p>No hay datos de destinos disponibles</p>
                            </div>
                        </div>
                    </div>

                    <!-- Viajes Próximos -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center"
                        >
                            <CalendarDaysIcon class="h-5 w-5 mr-2" />
                            Próximos Viajes
                        </h3>
                        <div class="space-y-3 max-h-64 overflow-y-auto">
                            <div
                                v-for="viaje in viajesProximos"
                                :key="viaje.id"
                                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                            >
                                <div>
                                    <p
                                        class="font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ viaje.destino }}
                                    </p>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ viaje.fecha_salida }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                            viaje.porcentaje_ocupacion >= 80
                                                ? 'bg-red-100 text-red-800'
                                                : viaje.porcentaje_ocupacion >=
                                                  50
                                                ? 'bg-amber-100 text-amber-800'
                                                : 'bg-emerald-100 text-emerald-800',
                                        ]"
                                    >
                                        {{ viaje.porcentaje_ocupacion }}%
                                        ocupado
                                    </span>
                                    <p
                                        class="text-xs text-gray-500 mt-1 dark:text-gray-400"
                                    >
                                        {{ viaje.cupos_disponibles }} cupos
                                        disponibles
                                    </p>
                                </div>
                            </div>
                            <p
                                v-if="!viajesProximos?.length"
                                class="text-center text-gray-400 py-4"
                            >
                                No hay viajes próximos programados
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Cuotas Vencidas / Próximas -->
                <div
                    v-if="cuotasVencidas?.length"
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                >
                    <div class="flex justify-between items-center mb-4">
                        <h3
                            class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center"
                        >
                            <ExclamationTriangleIcon
                                class="h-5 w-5 mr-2 text-red-500"
                            />
                            Cuotas que Requieren Atención
                        </h3>
                        <Link
                            :href="'/reportes/pagos-pendientes'"
                            class="text-sm text-emerald-600 hover:text-emerald-700"
                        >
                            Ver todas →
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
                                        Cliente
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                                    >
                                        Viaje
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                                    >
                                        Cuota
                                    </th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                                    >
                                        Vencimiento
                                    </th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                                    >
                                        Monto
                                    </th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase"
                                    >
                                        Estado
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <tr
                                    v-for="cuota in cuotasVencidas.slice(0, 5)"
                                    :key="cuota.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td
                                        class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100"
                                    >
                                        {{ cuota.cliente }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300"
                                    >
                                        {{ cuota.viaje }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300"
                                    >
                                        #{{ cuota.numero_cuota }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300"
                                    >
                                        {{ cuota.fecha_vencimiento }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-sm text-right font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ formatCurrency(cuota.monto) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            :class="[
                                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                cuota.dias_vencimiento < 0
                                                    ? 'bg-red-100 text-red-800'
                                                    : 'bg-amber-100 text-amber-800',
                                            ]"
                                        >
                                            {{
                                                cuota.dias_vencimiento < 0
                                                    ? `Vencida (${Math.abs(
                                                          cuota.dias_vencimiento
                                                      )} días)`
                                                    : `Vence en ${cuota.dias_vencimiento} días`
                                            }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div
                    class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                >
                    <h3
                        class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                    >
                        Acciones Rápidas
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <Link
                            :href="'/ventas/create'"
                            class="flex flex-col items-center p-4 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition"
                        >
                            <TicketIcon
                                class="h-8 w-8 text-emerald-600 dark:text-emerald-400 mb-2"
                            />
                            <span
                                class="text-sm font-medium text-emerald-700 dark:text-emerald-300"
                                >Nueva Venta</span
                            >
                        </Link>
                        <Link
                            :href="'/viajes/create'"
                            class="flex flex-col items-center p-4 bg-blue-50 dark:bg-blue-900/30 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 transition"
                        >
                            <GlobeAmericasIcon
                                class="h-8 w-8 text-blue-600 dark:text-blue-400 mb-2"
                            />
                            <span
                                class="text-sm font-medium text-blue-700 dark:text-blue-300"
                                >Nuevo Viaje</span
                            >
                        </Link>
                        <Link
                            :href="'/reportes/ventas-periodo'"
                            class="flex flex-col items-center p-4 bg-purple-50 dark:bg-purple-900/30 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/50 transition"
                        >
                            <ChartBarIcon
                                class="h-8 w-8 text-purple-600 dark:text-purple-400 mb-2"
                            />
                            <span
                                class="text-sm font-medium text-purple-700 dark:text-purple-300"
                                >Ver Reportes</span
                            >
                        </Link>
                        <a
                            :href="
                                '/reportes/exportar-ventas?fecha_inicio=' +
                                new Date(
                                    new Date().setMonth(
                                        new Date().getMonth() - 1
                                    )
                                )
                                    .toISOString()
                                    .split('T')[0] +
                                '&fecha_fin=' +
                                new Date().toISOString().split('T')[0] +
                                '&formato=excel'
                            "
                            class="flex flex-col items-center p-4 bg-amber-50 dark:bg-amber-900/30 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/50 transition"
                        >
                            <DocumentArrowDownIcon
                                class="h-8 w-8 text-amber-600 dark:text-amber-400 mb-2"
                            />
                            <span
                                class="text-sm font-medium text-amber-700 dark:text-amber-300"
                                >Exportar Excel</span
                            >
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
