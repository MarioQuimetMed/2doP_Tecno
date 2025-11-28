<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import { useAppUrl } from "@/Composables/useAppUrl";
import {
    ShoppingCartIcon,
    PlusIcon,
    MagnifyingGlassIcon,
    EyeIcon,
    FunnelIcon,
    ChevronUpIcon,
    ChevronDownIcon,
    CurrencyDollarIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
    DocumentTextIcon,
    UserIcon,
    CalendarDaysIcon,
    BanknotesIcon,
    CreditCardIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    ventas: Object,
    estadosPago: Array,
    tiposPago: Array,
    stats: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const selectedEstadoPago = ref(props.filters.estado_pago || "");
const selectedTipoPago = ref(props.filters.tipo_pago || "");
const fechaDesde = ref(props.filters.fecha_desde || "");
const fechaHasta = ref(props.filters.fecha_hasta || "");
const showFilters = ref(false);

// Debounce para búsqueda
let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

watch(
    [
        selectedEstadoPago,
        selectedTipoPago,
        fechaDesde,
        fechaHasta,
    ],
    () => {
        applyFilters();
    }
);

const applyFilters = () => {
    router.get(
        resolveUrl("vendedor/mis-ventas"),
        {
            search: search.value || undefined,
            estado_pago: selectedEstadoPago.value || undefined,
            tipo_pago: selectedTipoPago.value || undefined,
            fecha_desde: fechaDesde.value || undefined,
            fecha_hasta: fechaHasta.value || undefined,
            sort: props.filters.sort,
            direction: props.filters.direction,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const clearFilters = () => {
    search.value = "";
    selectedEstadoPago.value = "";
    selectedTipoPago.value = "";
    fechaDesde.value = "";
    fechaHasta.value = "";
};

const sortBy = (field) => {
    const direction =
        props.filters.sort === field && props.filters.direction === "asc"
            ? "desc"
            : "asc";

    router.get(
        resolveUrl("vendedor/mis-ventas"),
        {
            ...props.filters,
            sort: field,
            direction: direction,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const getSortIcon = (field) => {
    if (props.filters.sort !== field) return null;
    return props.filters.direction === "asc" ? ChevronUpIcon : ChevronDownIcon;
};

const formatDate = (date) => {
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
        PENDIENTE:
            "bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200",
        PARCIAL:
            "bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200",
        COMPLETADO:
            "bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200",
        ANULADO: "bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200",
    };
    return colorMap[estado] || "bg-gray-100 text-gray-800";
};

const getEstadoIcon = (estado) => {
    const iconMap = {
        PENDIENTE: ClockIcon,
        PARCIAL: CurrencyDollarIcon,
        COMPLETADO: CheckCircleIcon,
        ANULADO: XCircleIcon,
    };
    return iconMap[estado] || ClockIcon;
};

const getEstadoLabel = (estado) => {
    const estadoObj = props.estadosPago.find((e) => e.value === estado);
    return estadoObj?.label || estado;
};

const getTipoPagoIcon = (tipo) => {
    return tipo === "CONTADO" ? BanknotesIcon : CreditCardIcon;
};

const getTipoPagoLabel = (tipo) => {
    const tipoObj = props.tiposPago.find((t) => t.value === tipo);
    return tipoObj?.label || tipo;
};

// Filtros activos
const activeFiltersCount = computed(() => {
    let count = 0;
    if (selectedEstadoPago.value) count++;
    if (selectedTipoPago.value) count++;
    if (fechaDesde.value) count++;
    if (fechaHasta.value) count++;
    return count;
});

const { resolveUrl } = useAppUrl();
</script>

<template>
    <Head title="Mis Ventas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <ShoppingCartIcon class="h-6 w-6 mr-2 text-indigo-600" />
                    <h2
                        class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                    >
                        Mis Ventas
                    </h2>
                </div>
                <Link
                    :href="resolveUrl('vendedor/ventas/create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <PlusIcon class="h-4 w-4 mr-1" />
                    Nueva Venta
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 p-3 bg-indigo-100 dark:bg-indigo-900 rounded-full"
                            >
                                <ShoppingCartIcon
                                    class="h-6 w-6 text-indigo-600 dark:text-indigo-400"
                                />
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
                                    {{ stats.total }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full"
                            >
                                <ClockIcon
                                    class="h-6 w-6 text-yellow-600 dark:text-yellow-400"
                                />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Pendientes
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.pendientes }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 p-3 bg-green-100 dark:bg-green-900 rounded-full"
                            >
                                <CheckCircleIcon
                                    class="h-6 w-6 text-green-600 dark:text-green-400"
                                />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Completadas
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.completadas }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 p-3 bg-purple-100 dark:bg-purple-900 rounded-full"
                            >
                                <CurrencyDollarIcon
                                    class="h-6 w-6 text-purple-600 dark:text-purple-400"
                                />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Monto Total
                                </p>
                                <p
                                    class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ formatCurrency(stats.monto_total) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Búsqueda y Filtros -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6"
                >
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row gap-4">
                            <!-- Búsqueda -->
                            <div class="flex-1">
                                <div class="relative">
                                    <MagnifyingGlassIcon
                                        class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
                                    />
                                    <input
                                        type="text"
                                        v-model="search"
                                        placeholder="Buscar por cliente, viaje o destino..."
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    />
                                </div>
                            </div>

                            <!-- Toggle filtros -->
                            <button
                                @click="showFilters = !showFilters"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <FunnelIcon class="h-5 w-5 mr-2" />
                                Filtros
                                <span
                                    v-if="activeFiltersCount > 0"
                                    class="ml-2 px-2 py-0.5 text-xs bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded-full"
                                >
                                    {{ activeFiltersCount }}
                                </span>
                            </button>
                        </div>

                        <!-- Filtros expandibles -->
                        <div
                            v-show="showFilters"
                            class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 grid grid-cols-1 md:grid-cols-4 gap-4"
                        >
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                >
                                    Estado Pago
                                </label>
                                <select
                                    v-model="selectedEstadoPago"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Todos</option>
                                    <option
                                        v-for="estado in estadosPago"
                                        :key="estado.value"
                                        :value="estado.value"
                                    >
                                        {{ estado.label }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                >
                                    Tipo Pago
                                </label>
                                <select
                                    v-model="selectedTipoPago"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Todos</option>
                                    <option
                                        v-for="tipo in tiposPago"
                                        :key="tipo.value"
                                        :value="tipo.value"
                                    >
                                        {{ tipo.label }}
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
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
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
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>

                            <div class="flex items-end md:col-span-4">
                                <button
                                    @click="clearFilters"
                                    class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    Limpiar filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Ventas -->
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
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        #
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                        @click="sortBy('fecha_venta')"
                                    >
                                        <div class="flex items-center">
                                            Fecha
                                            <component
                                                :is="getSortIcon('fecha_venta')"
                                                v-if="
                                                    getSortIcon('fecha_venta')
                                                "
                                                class="h-4 w-4 ml-1"
                                            />
                                        </div>
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Cliente
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Viaje
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Tipo
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                        @click="sortBy('monto_total')"
                                    >
                                        <div class="flex items-center">
                                            Monto
                                            <component
                                                :is="getSortIcon('monto_total')"
                                                v-if="
                                                    getSortIcon('monto_total')
                                                "
                                                class="h-4 w-4 ml-1"
                                            />
                                        </div>
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Progreso
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Estado
                                    </th>
                                    <th
                                        scope="col"
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
                                    v-for="venta in ventas.data"
                                    :key="venta.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ venta.id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm text-gray-900 dark:text-gray-100"
                                        >
                                            {{ formatDate(venta.fecha_venta) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center"
                                            >
                                                <UserIcon
                                                    class="h-4 w-4 text-white"
                                                />
                                            </div>
                                            <div class="ml-3">
                                                <div
                                                    class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                                >
                                                    {{ venta.cliente?.name }}
                                                </div>
                                                <div
                                                    class="text-xs text-gray-500 dark:text-gray-400"
                                                >
                                                    {{ venta.cliente?.email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div
                                            class="text-sm text-gray-900 dark:text-gray-100"
                                        >
                                            {{
                                                venta.viaje?.plan_viaje?.nombre
                                            }}
                                        </div>
                                        <div
                                            class="text-xs text-gray-500 dark:text-gray-400 flex items-center"
                                        >
                                            <CalendarDaysIcon
                                                class="h-3 w-3 mr-1"
                                            />
                                            {{
                                                new Date(
                                                    venta.viaje?.fecha_salida
                                                ).toLocaleDateString("es-BO")
                                            }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                                                venta.tipo_pago === 'CONTADO'
                                                    ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200'
                                                    : 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
                                            ]"
                                        >
                                            <component
                                                :is="
                                                    getTipoPagoIcon(
                                                        venta.tipo_pago
                                                    )
                                                "
                                                class="h-3 w-3 mr-1"
                                            />
                                            {{
                                                getTipoPagoLabel(
                                                    venta.tipo_pago
                                                )
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                                        >
                                            {{
                                                formatCurrency(
                                                    venta.monto_total
                                                )
                                            }}
                                        </div>
                                        <div
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            Pagado:
                                            {{
                                                formatCurrency(
                                                    venta.monto_pagado
                                                )
                                            }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-1 mr-2">
                                                <div
                                                    class="w-20 bg-gray-200 dark:bg-gray-600 rounded-full h-2"
                                                >
                                                    <div
                                                        class="h-2 rounded-full transition-all duration-300"
                                                        :class="[
                                                            venta.porcentaje_pagado >=
                                                            100
                                                                ? 'bg-green-500'
                                                                : venta.porcentaje_pagado >
                                                                  0
                                                                ? 'bg-blue-500'
                                                                : 'bg-gray-400',
                                                        ]"
                                                        :style="{
                                                            width:
                                                                Math.min(
                                                                    venta.porcentaje_pagado,
                                                                    100
                                                                ) + '%',
                                                        }"
                                                    ></div>
                                                </div>
                                            </div>
                                            <span
                                                class="text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                {{ venta.porcentaje_pagado }}%
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                getEstadoClasses(
                                                    venta.estado_pago
                                                ),
                                            ]"
                                        >
                                            <component
                                                :is="
                                                    getEstadoIcon(
                                                        venta.estado_pago
                                                    )
                                                "
                                                class="h-3 w-3 mr-1"
                                            />
                                            {{
                                                getEstadoLabel(
                                                    venta.estado_pago
                                                )
                                            }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                    >
                                        <div class="flex justify-end space-x-2">
                                            <Link
                                                :href="
                                                    resolveUrl(
                                                        'vendedor/ventas/' + venta.id
                                                    )
                                                "
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 p-1"
                                                title="Ver detalles"
                                            >
                                                <EyeIcon class="h-5 w-5" />
                                            </Link>
                                            <a
                                                :href="
                                                    resolveUrl(
                                                        'vendedor/ventas/' +
                                                            venta.id +
                                                            '/comprobante'
                                                    )
                                                "
                                                target="_blank"
                                                class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 p-1"
                                                title="Comprobante PDF"
                                            >
                                                <DocumentTextIcon
                                                    class="h-5 w-5"
                                                />
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="ventas.data.length === 0">
                                    <td
                                        colspan="9"
                                        class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"
                                    >
                                        <ShoppingCartIcon
                                            class="h-12 w-12 mx-auto mb-4 text-gray-300 dark:text-gray-600"
                                        />
                                        <p class="text-lg font-medium">
                                            No se encontraron ventas
                                        </p>
                                        <p class="text-sm mt-1">
                                            Prueba ajustando los filtros o
                                            registra una nueva venta
                                        </p>
                                        <Link
                                            :href="resolveUrl('vendedor/ventas/create')"
                                            class="inline-flex items-center mt-4 px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                                        >
                                            <PlusIcon class="h-4 w-4 mr-1" />
                                            Nueva Venta
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div
                        v-if="ventas.links && ventas.links.length > 3"
                        class="px-6 py-4 border-t border-gray-200 dark:border-gray-700"
                    >
                        <div class="flex items-center justify-between">
                            <div
                                class="text-sm text-gray-700 dark:text-gray-300"
                            >
                                Mostrando
                                <span class="font-medium">{{
                                    ventas.from
                                }}</span>
                                a
                                <span class="font-medium">{{ ventas.to }}</span>
                                de
                                <span class="font-medium">{{
                                    ventas.total
                                }}</span>
                                resultados
                            </div>
                            <div class="flex space-x-1">
                                <template
                                    v-for="link in ventas.links"
                                    :key="link.label"
                                >
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        class="px-3 py-2 text-sm rounded-md"
                                        :class="[
                                            link.active
                                                ? 'bg-indigo-600 text-white'
                                                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                                        ]"
                                        v-html="link.label"
                                    />
                                    <span
                                        v-else
                                        class="px-3 py-2 text-sm text-gray-400 dark:text-gray-600"
                                        v-html="link.label"
                                    />
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
