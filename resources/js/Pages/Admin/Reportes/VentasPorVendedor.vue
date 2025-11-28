<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed, onMounted } from "vue";
import {
    UsersIcon,
    TrophyIcon,
    ArrowDownTrayIcon,
    FunnelIcon,
} from "@heroicons/vue/24/outline";
import { Bar } from "vue-chartjs";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from "chart.js";

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend
);

const props = defineProps({
    vendedores: Array,
    totales: Object,
    filtros: Object,
});

const fechaInicio = ref(props.filtros?.fecha_inicio || "");
const fechaFin = ref(props.filtros?.fecha_fin || "");

const aplicarFiltros = () => {
    router.get(
        "/reportes/ventas-vendedor",
        {
            fecha_inicio: fechaInicio.value,
            fecha_fin: fechaFin.value,
        },
        { preserveState: true }
    );
};

const chartData = computed(() => ({
    labels: props.vendedores?.map((v) => v.nombre) || [],
    datasets: [
        {
            label: "Ventas Realizadas",
            backgroundColor: "#DC2626",
            data: props.vendedores?.map((v) => v.total_ventas) || [],
        },
    ],
}));

const chartIngresos = computed(() => ({
    labels: props.vendedores?.map((v) => v.nombre) || [],
    datasets: [
        {
            label: "Ingresos Generados (USD)",
            backgroundColor: "#059669",
            data: props.vendedores?.map((v) => v.total_ingresos) || [],
        },
    ],
}));

const chartOptions = {
    responsive: true,
    plugins: {
        legend: { display: false },
    },
    scales: {
        y: { beginAtZero: true },
    },
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value || 0);
};

// Ranking de vendedores
const vendedoresRanking = computed(() => {
    if (!props.vendedores) return [];
    return [...props.vendedores].sort(
        (a, b) => b.total_ingresos - a.total_ingresos
    );
});

const getMedalClass = (index) => {
    if (index === 0) return "bg-yellow-400";
    if (index === 1) return "bg-gray-300";
    if (index === 2) return "bg-amber-600";
    return "bg-gray-200";
};
</script>

<template>
    <Head title="Ventas por Vendedor" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Reporte de Ventas por Vendedor
                </h2>
                <a
                    :href="
                        '/reportes/exportar-ventas?fecha_inicio=' +
                        (filtros.fecha_inicio || '') +
                        '&fecha_fin=' +
                        (filtros.fecha_fin || '') +
                        '&formato=excel'
                    "
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700 transition"
                >
                    <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
                    Exportar Excel
                </a>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtros -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6"
                >
                    <div class="flex items-center gap-2 mb-4">
                        <FunnelIcon class="h-5 w-5 text-gray-500" />
                        <h3
                            class="font-medium text-gray-900 dark:text-gray-100"
                        >
                            Período de Análisis
                        </h3>
                    </div>
                    <div class="flex flex-wrap gap-4 items-end">
                        <div>
                            <label
                                class="block text-sm text-gray-600 dark:text-gray-400 mb-1"
                                >Desde</label
                            >
                            <input
                                v-model="fechaInicio"
                                type="date"
                                class="form-input rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm text-gray-600 dark:text-gray-400 mb-1"
                                >Hasta</label
                            >
                            <input
                                v-model="fechaFin"
                                type="date"
                                class="form-input rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                            />
                        </div>
                        <button
                            @click="aplicarFiltros"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
                        >
                            Aplicar Filtros
                        </button>
                    </div>
                </div>

                <!-- Totales generales -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Vendedores Activos
                        </p>
                        <p class="text-2xl font-bold text-red-600">
                            {{ vendedores?.length || 0 }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Total Ventas
                        </p>
                        <p
                            class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                        >
                            {{ totales?.total_ventas || 0 }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Total Ingresos
                        </p>
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ formatCurrency(totales?.total_ingresos) }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Promedio por Vendedor
                        </p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ formatCurrency(totales?.promedio_vendedor) }}
                        </p>
                    </div>
                </div>

                <!-- Ranking y gráficos -->
                <div class="grid lg:grid-cols-3 gap-6 mb-6">
                    <!-- Ranking Top Vendedores -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-6"
                    >
                        <div class="flex items-center gap-2 mb-4">
                            <TrophyIcon class="h-5 w-5 text-yellow-500" />
                            <h3
                                class="font-medium text-gray-900 dark:text-gray-100"
                            >
                                Ranking de Vendedores
                            </h3>
                        </div>
                        <div class="space-y-3">
                            <div
                                v-for="(
                                    vendedor, index
                                ) in vendedoresRanking.slice(0, 5)"
                                :key="vendedor.id"
                                class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <span
                                    :class="[
                                        'flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold',
                                        getMedalClass(index),
                                    ]"
                                >
                                    {{ index + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p
                                        class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate"
                                    >
                                        {{ vendedor.nombre }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ vendedor.total_ventas }} ventas
                                    </p>
                                </div>
                                <span
                                    class="text-sm font-semibold text-emerald-600"
                                >
                                    {{
                                        formatCurrency(vendedor.total_ingresos)
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico de ventas -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-6"
                    >
                        <h3
                            class="font-medium text-gray-900 dark:text-gray-100 mb-4"
                        >
                            Cantidad de Ventas
                        </h3>
                        <Bar :data="chartData" :options="chartOptions" />
                    </div>

                    <!-- Gráfico de ingresos -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-6"
                    >
                        <h3
                            class="font-medium text-gray-900 dark:text-gray-100 mb-4"
                        >
                            Ingresos Generados
                        </h3>
                        <Bar :data="chartIngresos" :options="chartOptions" />
                    </div>
                </div>

                <!-- Tabla de vendedores -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden"
                >
                    <div
                        class="px-6 py-4 border-b border-gray-200 dark:border-gray-700"
                    >
                        <h3
                            class="font-medium text-gray-900 dark:text-gray-100 flex items-center"
                        >
                            <UsersIcon class="h-5 w-5 mr-2" />
                            Detalle por Vendedor
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        #
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Vendedor
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Email
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Total Ventas
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Total Ingresos
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Ticket Promedio
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        % del Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <tr
                                    v-for="(
                                        vendedor, index
                                    ) in vendedoresRanking"
                                    :key="vendedor.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ index + 1 }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ vendedor.nombre }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300"
                                    >
                                        {{ vendedor.email }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-center text-gray-900 dark:text-gray-100"
                                    >
                                        {{ vendedor.total_ventas }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-right font-medium text-emerald-600"
                                    >
                                        {{
                                            formatCurrency(
                                                vendedor.total_ingresos
                                            )
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-right text-gray-900 dark:text-gray-100"
                                    >
                                        {{
                                            formatCurrency(
                                                vendedor.ticket_promedio
                                            )
                                        }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center">
                                            <span
                                                class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                            >
                                                {{ vendedor.porcentaje_total }}%
                                            </span>
                                            <div
                                                class="ml-2 w-16 bg-gray-200 rounded-full h-2"
                                            >
                                                <div
                                                    class="bg-red-600 h-2 rounded-full"
                                                    :style="{
                                                        width: `${vendedor.porcentaje_total}%`,
                                                    }"
                                                ></div>
                                            </div>
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!vendedores?.length">
                                    <td
                                        colspan="7"
                                        class="px-6 py-8 text-center text-gray-400"
                                    >
                                        No hay datos para el período
                                        seleccionado.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <td
                                        colspan="3"
                                        class="px-6 py-3 text-sm font-bold text-gray-900 dark:text-gray-100"
                                    >
                                        TOTALES
                                    </td>
                                    <td
                                        class="px-6 py-3 text-sm text-center font-bold text-gray-900 dark:text-gray-100"
                                    >
                                        {{ totales?.total_ventas || 0 }}
                                    </td>
                                    <td
                                        class="px-6 py-3 text-sm text-right font-bold text-emerald-600"
                                    >
                                        {{
                                            formatCurrency(
                                                totales?.total_ingresos
                                            )
                                        }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Navegación -->
                <div class="mt-6">
                    <Link
                        :href="'/reportes'"
                        class="text-red-600 hover:text-red-700"
                    >
                        ← Volver a Reportes
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
