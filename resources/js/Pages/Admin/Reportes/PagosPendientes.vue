<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import { useAppUrl } from "@/Composables/useAppUrl";
import {
    CurrencyDollarIcon,
    ExclamationTriangleIcon,
    ArrowDownTrayIcon,
    FunnelIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    cuotas: Array,
    estadisticas: Object,
    filtros: Object,
});

const tipo = ref(props.filtros.tipo || "todos");

const aplicarFiltros = () => {
    router.get(
        resolveUrl("reportes/pagos-pendientes"),
        {
            tipo: tipo.value,
        },
        { preserveState: true }
    );
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value || 0);
};

const getEstadoColor = (diasVencimiento) => {
    if (diasVencimiento < 0) return "bg-red-100 text-red-800";
    if (diasVencimiento <= 7) return "bg-amber-100 text-amber-800";
    if (diasVencimiento <= 7) return "bg-amber-100 text-amber-800";
    return "bg-emerald-100 text-emerald-800";
};

const { resolveUrl } = useAppUrl();
</script>

<template>
    <Head title="Pagos Pendientes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Reporte de Pagos Pendientes
                </h2>
                <a
                    :href="
                        resolveUrl('reportes/exportar-pagos-excel?tipo=') +
                        (filtros.tipo || 'todos')
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
                            Filtros
                        </h3>
                    </div>
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center">
                            <input
                                v-model="tipo"
                                type="radio"
                                value="todos"
                                class="form-radio text-red-600"
                                @change="aplicarFiltros"
                            />
                            <span class="ml-2 text-gray-700 dark:text-gray-300"
                                >Todas las pendientes</span
                            >
                        </label>
                        <label class="flex items-center">
                            <input
                                v-model="tipo"
                                type="radio"
                                value="vencidas"
                                class="form-radio text-red-600"
                                @change="aplicarFiltros"
                            />
                            <span class="ml-2 text-gray-700 dark:text-gray-300"
                                >Solo vencidas</span
                            >
                        </label>
                        <label class="flex items-center">
                            <input
                                v-model="tipo"
                                type="radio"
                                value="proximas"
                                class="form-radio text-red-600"
                                @change="aplicarFiltros"
                            />
                            <span class="ml-2 text-gray-700 dark:text-gray-300"
                                >Próximas a vencer (15 días)</span
                            >
                        </label>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Total Pendiente
                        </p>
                        <p class="text-2xl font-bold text-red-600">
                            {{ formatCurrency(estadisticas.total_pendiente) }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Cuotas Vencidas
                        </p>
                        <p class="text-2xl font-bold text-red-600">
                            {{ estadisticas.cuotas_vencidas }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Próximas a Vencer
                        </p>
                        <p class="text-2xl font-bold text-amber-600">
                            {{ estadisticas.cuotas_proximas }}
                        </p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Total Cuotas
                        </p>
                        <p
                            class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                        >
                            {{ estadisticas.total_cuotas }}
                        </p>
                    </div>
                </div>

                <!-- Alerta si hay vencidas -->
                <div
                    v-if="estadisticas.cuotas_vencidas > 0"
                    class="mb-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4"
                >
                    <div class="flex items-center">
                        <ExclamationTriangleIcon
                            class="h-5 w-5 text-red-600 mr-3"
                        />
                        <p class="text-sm text-red-800 dark:text-red-200">
                            <strong>¡Atención!</strong> Hay
                            {{ estadisticas.cuotas_vencidas }} cuotas vencidas
                            que requieren seguimiento inmediato.
                        </p>
                    </div>
                </div>

                <!-- Tabla de cuotas -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden"
                >
                    <div
                        class="px-6 py-4 border-b border-gray-200 dark:border-gray-700"
                    >
                        <h3
                            class="font-medium text-gray-900 dark:text-gray-100 flex items-center"
                        >
                            <CurrencyDollarIcon class="h-5 w-5 mr-2" />
                            Detalle de Cuotas Pendientes
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
                                        Cliente
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Viaje
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Cuota
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Vencimiento
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Monto
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Saldo
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Estado
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                    >
                                        Acción
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <tr
                                    v-for="cuota in cuotas"
                                    :key="cuota.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td
                                        class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ cuota.cliente }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300"
                                    >
                                        {{ cuota.viaje }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-center text-gray-900 dark:text-gray-100"
                                    >
                                        #{{ cuota.numero_cuota }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300"
                                    >
                                        {{ cuota.fecha_vencimiento }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-right text-gray-900 dark:text-gray-100"
                                    >
                                        {{ formatCurrency(cuota.monto) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-sm text-right font-medium text-red-600"
                                    >
                                        {{
                                            formatCurrency(
                                                cuota.saldo_pendiente
                                            )
                                        }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            :class="[
                                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                getEstadoColor(
                                                    cuota.dias_vencimiento
                                                ),
                                            ]"
                                        >
                                            {{
                                                cuota.dias_vencimiento < 0
                                                    ? `Vencida (${Math.abs(
                                                          cuota.dias_vencimiento
                                                      )} días)`
                                                    : cuota.dias_vencimiento ===
                                                      0
                                                    ? "Vence hoy"
                                                    : `Vence en ${cuota.dias_vencimiento} días`
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <Link
                                            :href="
                                                resolveUrl(
                                                    'ventas/' + cuota.venta_id
                                                )
                                            "
                                            class="text-sm text-blue-600 hover:text-blue-700"
                                        >
                                            Ver venta
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="!cuotas.length">
                                    <td
                                        colspan="8"
                                        class="px-6 py-8 text-center text-gray-400"
                                    >
                                        No hay cuotas pendientes 🎉
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Navegación -->
                <div class="mt-6">
                    <Link
                        :href="resolveUrl('reportes')"
                        class="text-red-600 hover:text-red-700"
                    >
                        ← Volver a Reportes
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
