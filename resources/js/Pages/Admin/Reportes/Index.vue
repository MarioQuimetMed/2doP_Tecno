<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { useAppUrl } from "@/Composables/useAppUrl";
import {
    ChartBarIcon,
    GlobeAmericasIcon,
    TruckIcon,
    CurrencyDollarIcon,
    UsersIcon,
    DocumentArrowDownIcon,
    ClipboardDocumentListIcon,
} from "@heroicons/vue/24/outline";

defineProps({
    resumen: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value || 0);
};

const reportes = [
    {
        titulo: "Ventas por Período",
        descripcion:
            "Analiza las ventas por día, semana o mes con gráficos interactivos",
        icono: ChartBarIcon,
        color: "emerald",
        ruta: "reportes/ventas-periodo",
    },
    {
        titulo: "Destinos Populares",
        descripcion: "Descubre los destinos más vendidos y su evolución",
        icono: GlobeAmericasIcon,
        color: "blue",
        ruta: "reportes/destinos-populares",
    },
    {
        titulo: "Ocupación de Viajes",
        descripcion:
            "Revisa el porcentaje de ocupación de cada viaje programado",
        icono: TruckIcon,
        color: "purple",
        ruta: "reportes/ocupacion-viajes",
    },
    {
        titulo: "Pagos Pendientes",
        descripcion: "Lista de cuotas vencidas y próximas a vencer",
        icono: CurrencyDollarIcon,
        color: "red",
        ruta: "reportes/pagos-pendientes",
    },
    {
        titulo: "Ventas por Vendedor",
        descripcion: "Comparativo de rendimiento de cada vendedor",
        icono: UsersIcon,
        color: "amber",
        ruta: "reportes/ventas-vendedor",
    },
    {
        titulo: "Bitácora de Accesos",
        descripcion: "Historial de acciones y auditoría del sistema",
        icono: ClipboardDocumentListIcon,
        color: "gray",
        ruta: "bitacora",
    },
];

const colorClasses = {
    emerald: {
        bg: "bg-emerald-100 dark:bg-emerald-900/30",
        text: "text-emerald-600 dark:text-emerald-400",
        hover: "hover:bg-emerald-200 dark:hover:bg-emerald-900/50",
    },
    blue: {
        bg: "bg-blue-100 dark:bg-blue-900/30",
        text: "text-blue-600 dark:text-blue-400",
        hover: "hover:bg-blue-200 dark:hover:bg-blue-900/50",
    },
    purple: {
        bg: "bg-purple-100 dark:bg-purple-900/30",
        text: "text-purple-600 dark:text-purple-400",
        hover: "hover:bg-purple-200 dark:hover:bg-purple-900/50",
    },
    red: {
        bg: "bg-red-100 dark:bg-red-900/30",
        text: "text-red-600 dark:text-red-400",
        hover: "hover:bg-red-200 dark:hover:bg-red-900/50",
    },
    amber: {
        bg: "bg-amber-100 dark:bg-amber-900/30",
        text: "text-amber-600 dark:text-amber-400",
        hover: "hover:bg-amber-200 dark:hover:bg-amber-900/50",
    },
    gray: {
        bg: "bg-gray-100 dark:bg-gray-700",
        text: "text-gray-600 dark:text-gray-400",
        hover: "hover:bg-gray-200 dark:hover:bg-gray-600",
    },
};

const { resolveUrl } = useAppUrl();
</script>

<template>
    <Head title="Reportes y Estadísticas" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Reportes y Estadísticas
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Resumen Rápido -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
                >
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-6"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Ventas Totales
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                                >
                                    {{ resumen.ventas_totales }}
                                </p>
                                <p class="text-xs text-emerald-600">
                                    {{
                                        formatCurrency(resumen.ingresos_totales)
                                    }}
                                </p>
                            </div>
                            <div
                                class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-full"
                            >
                                <ChartBarIcon
                                    class="h-8 w-8 text-emerald-600 dark:text-emerald-400"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-6"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Ventas del Mes
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                                >
                                    {{ resumen.ventas_mes }}
                                </p>
                                <p class="text-xs text-blue-600">
                                    {{ formatCurrency(resumen.ingresos_mes) }}
                                </p>
                            </div>
                            <div
                                class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full"
                            >
                                <CurrencyDollarIcon
                                    class="h-8 w-8 text-blue-600 dark:text-blue-400"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-6"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Viajes Activos
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                                >
                                    {{ resumen.viajes_activos }}
                                </p>
                                <p class="text-xs text-purple-600">
                                    {{ resumen.ocupacion_promedio }}% ocupación
                                    promedio
                                </p>
                            </div>
                            <div
                                class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-full"
                            >
                                <TruckIcon
                                    class="h-8 w-8 text-purple-600 dark:text-purple-400"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-6"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Cuotas Vencidas
                                </p>
                                <p class="text-2xl font-bold text-red-600">
                                    {{ resumen.cuotas_vencidas }}
                                </p>
                                <p class="text-xs text-red-600">
                                    {{
                                        formatCurrency(resumen.pagos_pendientes)
                                    }}
                                    pendiente
                                </p>
                            </div>
                            <div
                                class="p-3 bg-red-100 dark:bg-red-900/30 rounded-full"
                            >
                                <CurrencyDollarIcon
                                    class="h-8 w-8 text-red-600 dark:text-red-400"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid de Reportes -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <Link
                        v-for="reporte in reportes"
                        :key="reporte.titulo"
                        :href="resolveUrl(reporte.ruta)"
                        :class="[
                            'bg-white dark:bg-gray-800 rounded-lg shadow p-6 transition-all duration-200',
                            colorClasses[reporte.color].hover,
                            'border-2 border-transparent hover:border-gray-200 dark:hover:border-gray-600',
                        ]"
                    >
                        <div class="flex items-start">
                            <div
                                :class="[
                                    'p-3 rounded-full',
                                    colorClasses[reporte.color].bg,
                                ]"
                            >
                                <component
                                    :is="reporte.icono"
                                    :class="[
                                        'h-8 w-8',
                                        colorClasses[reporte.color].text,
                                    ]"
                                />
                            </div>
                            <div class="ml-4">
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ reporte.titulo }}
                                </h3>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                                >
                                    {{ reporte.descripcion }}
                                </p>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Exportaciones Rápidas -->
                <div
                    class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow p-6"
                >
                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center"
                    >
                        <DocumentArrowDownIcon class="h-5 w-5 mr-2" />
                        Exportaciones Rápidas
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a
                            :href="
                                resolveUrl(
                                    'reportes/exportar-ventas?fecha_inicio=' +
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
                                )
                            "
                            class="flex items-center justify-center p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-emerald-400 dark:hover:border-emerald-500 transition"
                        >
                            <DocumentArrowDownIcon
                                class="h-6 w-6 text-gray-400 mr-2"
                            />
                            <span class="text-gray-600 dark:text-gray-300"
                                >Ventas (Excel)</span
                            >
                        </a>
                        <a
                            :href="
                                resolveUrl(
                                    'reportes/exportar-ventas?fecha_inicio=' +
                                        new Date(
                                            new Date().setMonth(
                                                new Date().getMonth() - 1
                                            )
                                        )
                                            .toISOString()
                                            .split('T')[0] +
                                        '&fecha_fin=' +
                                        new Date().toISOString().split('T')[0] +
                                        '&formato=pdf'
                                )
                            "
                            class="flex items-center justify-center p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-red-400 dark:hover:border-red-500 transition"
                        >
                            <DocumentArrowDownIcon
                                class="h-6 w-6 text-gray-400 mr-2"
                            />
                            <span class="text-gray-600 dark:text-gray-300"
                                >Ventas (PDF)</span
                            >
                        </a>
                        <a
                            :href="resolveUrl('reportes/exportar-ocupacion')"
                            class="flex items-center justify-center p-4 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg hover:border-purple-400 dark:hover:border-purple-500 transition"
                        >
                            <DocumentArrowDownIcon
                                class="h-6 w-6 text-gray-400 mr-2"
                            />
                            <span class="text-gray-600 dark:text-gray-300"
                                >Ocupación (Excel)</span
                            >
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
