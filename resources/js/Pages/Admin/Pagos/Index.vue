<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import {
    BanknotesIcon,
    CurrencyDollarIcon,
    CreditCardIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    EyeIcon,
    DocumentTextIcon,
    CalendarDaysIcon,
    ArrowPathIcon,
    PlusIcon,
    ChartBarIcon,
    QrCodeIcon,
    BuildingLibraryIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    pagos: Object,
    metodosPago: Array,
    stats: Object,
    filters: Object,
});

const searchQuery = ref(props.filters.search || "");
const selectedMetodo = ref(props.filters.metodo_pago || "");
const fechaDesde = ref(props.filters.fecha_desde || "");
const fechaHasta = ref(props.filters.fecha_hasta || "");
const showFilters = ref(false);

const applyFilters = () => {
    router.get(
        "/pagos",
        {
            search: searchQuery.value,
            metodo_pago: selectedMetodo.value,
            fecha_desde: fechaDesde.value,
            fecha_hasta: fechaHasta.value,
        },
        { preserveState: true, preserveScroll: true }
    );
};

const clearFilters = () => {
    searchQuery.value = "";
    selectedMetodo.value = "";
    fechaDesde.value = "";
    fechaHasta.value = "";
    router.get("/pagos");
};

// Debounce para búsqueda
let searchTimeout = null;
watch(searchQuery, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
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
        EFECTIVO:
            "bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300",
        TARJETA:
            "bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300",
        QR: "bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300",
        TRANSFERENCIA:
            "bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300",
    };
    return classes[metodo] || "bg-gray-100 text-gray-800";
};
</script>

<template>
    <Head title="Gestión de Pagos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <BanknotesIcon class="h-6 w-6 mr-2 text-green-600" />
                    <h2
                        class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                    >
                        Gestión de Pagos
                    </h2>
                </div>
                <div class="flex space-x-2">
                    <Link
                        :href="'/pagos/estadisticas'"
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                    >
                        <ChartBarIcon class="h-4 w-4 mr-1" />
                        Estadísticas
                    </Link>
                    <Link
                        :href="'/pagos/create'"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition"
                    >
                        <PlusIcon class="h-4 w-4 mr-1" />
                        Nuevo Pago
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Estadísticas -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6"
                >
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
                                    class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                                >
                                    {{ formatCurrency(stats.monto_total) }}
                                </p>
                            </div>
                            <div
                                class="p-3 bg-green-100 dark:bg-green-900/30 rounded-full"
                            >
                                <CurrencyDollarIcon
                                    class="h-8 w-8 text-green-600 dark:text-green-400"
                                />
                            </div>
                        </div>
                        <p
                            class="text-xs text-gray-500 dark:text-gray-400 mt-2"
                        >
                            {{ stats.total_pagos }} pagos registrados
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Recaudado Hoy
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                                >
                                    {{ formatCurrency(stats.monto_hoy) }}
                                </p>
                            </div>
                            <div
                                class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full"
                            >
                                <CalendarDaysIcon
                                    class="h-8 w-8 text-blue-600 dark:text-blue-400"
                                />
                            </div>
                        </div>
                        <p
                            class="text-xs text-gray-500 dark:text-gray-400 mt-2"
                        >
                            {{ stats.total_hoy }} pagos hoy
                        </p>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    En Efectivo
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                                >
                                    {{
                                        formatCurrency(
                                            stats.por_metodo?.efectivo
                                        )
                                    }}
                                </p>
                            </div>
                            <div
                                class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-full"
                            >
                                <BanknotesIcon
                                    class="h-8 w-8 text-emerald-600 dark:text-emerald-400"
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
                                    Pago Electrónico
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                                >
                                    {{
                                        formatCurrency(
                                            (stats.por_metodo?.tarjeta || 0) +
                                                (stats.por_metodo?.qr || 0) +
                                                (stats.por_metodo
                                                    ?.transferencia || 0)
                                        )
                                    }}
                                </p>
                            </div>
                            <div
                                class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-full"
                            >
                                <CreditCardIcon
                                    class="h-8 w-8 text-purple-600 dark:text-purple-400"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros y búsqueda -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6"
                >
                    <div class="p-4">
                        <div class="flex flex-col md:flex-row gap-4">
                            <!-- Búsqueda -->
                            <div class="flex-1">
                                <div class="relative">
                                    <MagnifyingGlassIcon
                                        class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
                                    />
                                    <input
                                        type="text"
                                        v-model="searchQuery"
                                        placeholder="Buscar por referencia, cliente..."
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-green-500 focus:border-green-500"
                                    />
                                </div>
                            </div>

                            <!-- Botón de filtros -->
                            <button
                                @click="showFilters = !showFilters"
                                class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                            >
                                <FunnelIcon class="h-5 w-5 mr-2" />
                                Filtros
                            </button>
                        </div>

                        <!-- Panel de filtros expandible -->
                        <div
                            v-if="showFilters"
                            class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"
                        >
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                    >
                                        Método de Pago
                                    </label>
                                    <select
                                        v-model="selectedMetodo"
                                        @change="applyFilters"
                                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                    >
                                        <option value="">Todos</option>
                                        <option
                                            v-for="metodo in metodosPago"
                                            :key="metodo.value"
                                            :value="metodo.value"
                                        >
                                            {{ metodo.label }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                    >
                                        Fecha Desde
                                    </label>
                                    <input
                                        type="date"
                                        v-model="fechaDesde"
                                        @change="applyFilters"
                                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                    >
                                        Fecha Hasta
                                    </label>
                                    <input
                                        type="date"
                                        v-model="fechaHasta"
                                        @change="applyFilters"
                                        class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                    />
                                </div>

                                <div class="flex items-end">
                                    <button
                                        @click="clearFilters"
                                        class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-500 transition w-full justify-center"
                                    >
                                        <ArrowPathIcon class="h-4 w-4 mr-1" />
                                        Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de pagos -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        ID / Fecha
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Cliente
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Viaje
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Monto
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Método
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Referencia
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <tr
                                    v-for="pago in pagos.data"
                                    :key="pago.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            #{{ pago.id }}
                                        </div>
                                        <div
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            {{
                                                formatDateTime(pago.fecha_pago)
                                            }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{ pago.venta?.cliente?.name }}
                                        </div>
                                        <div
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            {{ pago.venta?.cliente?.email }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm text-gray-900 dark:text-gray-100"
                                        >
                                            {{
                                                pago.venta?.viaje?.plan_viaje
                                                    ?.nombre || "N/A"
                                            }}
                                        </div>
                                        <div
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            {{
                                                pago.venta?.viaje?.plan_viaje
                                                    ?.destino?.nombre_lugar ||
                                                ""
                                            }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-lg font-bold text-green-600 dark:text-green-400"
                                        >
                                            {{
                                                formatCurrency(
                                                    pago.monto_pagado
                                                )
                                            }}
                                        </div>
                                        <div
                                            v-if="pago.cuota"
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            Cuota #{{ pago.cuota.numero_cuota }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                getMetodoBgClass(
                                                    pago.metodo_pago
                                                ),
                                            ]"
                                        >
                                            <component
                                                :is="
                                                    getMetodoIcon(
                                                        pago.metodo_pago
                                                    )
                                                "
                                                class="h-3 w-3 mr-1"
                                            />
                                            {{
                                                getMetodoLabel(pago.metodo_pago)
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="text-sm text-gray-600 dark:text-gray-400 font-mono"
                                        >
                                            {{
                                                pago.referencia_comprobante ||
                                                "-"
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                    >
                                        <div class="flex justify-end space-x-2">
                                            <Link
                                                :href="'/pagos/' + pago.id"
                                                class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300"
                                                title="Ver detalle"
                                            >
                                                <EyeIcon class="h-5 w-5" />
                                            </Link>
                                            <a
                                                :href="
                                                    '/pagos/' +
                                                    pago.id +
                                                    '/comprobante'
                                                "
                                                target="_blank"
                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300"
                                                title="Descargar comprobante"
                                            >
                                                <DocumentTextIcon
                                                    class="h-5 w-5"
                                                />
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="pagos.data.length === 0">
                                    <td
                                        colspan="7"
                                        class="px-6 py-12 text-center"
                                    >
                                        <BanknotesIcon
                                            class="mx-auto h-12 w-12 text-gray-400"
                                        />
                                        <p
                                            class="mt-2 text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            No se encontraron pagos
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div
                        v-if="pagos.data.length > 0"
                        class="px-6 py-4 border-t border-gray-200 dark:border-gray-700"
                    >
                        <div class="flex items-center justify-between">
                            <div
                                class="text-sm text-gray-700 dark:text-gray-300"
                            >
                                Mostrando {{ pagos.from }} a {{ pagos.to }} de
                                {{ pagos.total }} pagos
                            </div>
                            <div class="flex space-x-2">
                                <Link
                                    v-for="link in pagos.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    :class="[
                                        'px-3 py-1 text-sm rounded transition',
                                        link.active
                                            ? 'bg-green-600 text-white'
                                            : link.url
                                            ? 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                            : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 cursor-not-allowed',
                                    ]"
                                    v-html="link.label"
                                    :disabled="!link.url"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
