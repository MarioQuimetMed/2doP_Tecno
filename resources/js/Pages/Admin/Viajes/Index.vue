<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import {
    CalendarDaysIcon,
    PlusIcon,
    MagnifyingGlassIcon,
    PencilSquareIcon,
    TrashIcon,
    EyeIcon,
    FunnelIcon,
    ChevronUpIcon,
    ChevronDownIcon,
    MapPinIcon,
    UsersIcon,
    TicketIcon,
    ClockIcon,
    CheckCircleIcon,
    XCircleIcon,
    PlayCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    viajes: Object,
    planesViaje: Array,
    estados: Array,
    stats: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const selectedEstado = ref(props.filters.estado || "");
const selectedPlanViaje = ref(props.filters.plan_viaje_id || "");
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

watch([selectedEstado, selectedPlanViaje, fechaDesde, fechaHasta], () => {
    applyFilters();
});

const applyFilters = () => {
    router.get(
        route('viajes.index'),
        {
            search: search.value || undefined,
            estado: selectedEstado.value || undefined,
            plan_viaje_id: selectedPlanViaje.value || undefined,
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
    selectedEstado.value = "";
    selectedPlanViaje.value = "";
    fechaDesde.value = "";
    fechaHasta.value = "";
};

const sortBy = (field) => {
    const direction =
        props.filters.sort === field && props.filters.direction === "asc"
            ? "desc"
            : "asc";

    router.get(
        route('viajes.index'),
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

const deleteViaje = (viaje) => {
    if (
        confirm(
            `¿Está seguro de eliminar el viaje del ${formatDate(
                viaje.fecha_salida
            )}?`
        )
    ) {
        router.delete(route('viajes.destroy', viaje.id));
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-BO", {
        weekday: "short",
        year: "numeric",
        month: "short",
        day: "numeric",
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
        ABIERTO:
            "bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200",
        LLENO: "bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200",
        EN_CURSO:
            "bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200",
        FINALIZADO:
            "bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300",
        CANCELADO: "bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200",
    };
    return colorMap[estado] || "bg-gray-100 text-gray-800";
};

const getEstadoIcon = (estado) => {
    const iconMap = {
        ABIERTO: TicketIcon,
        LLENO: CheckCircleIcon,
        EN_CURSO: PlayCircleIcon,
        FINALIZADO: CheckCircleIcon,
        CANCELADO: XCircleIcon,
    };
    return iconMap[estado] || TicketIcon;
};

const getEstadoLabel = (estado) => {
    const estadoObj = props.estados.find((e) => e.value === estado);
    return estadoObj?.label || estado;
};

// Filtros activos
const activeFiltersCount = computed(() => {
    let count = 0;
    if (selectedEstado.value) count++;
    if (selectedPlanViaje.value) count++;
    if (fechaDesde.value) count++;
    if (fechaHasta.value) count++;
    return count;
});
</script>

<template>
    <Head title="Viajes Programados" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <CalendarDaysIcon class="h-6 w-6 mr-2 text-indigo-600" />
                    <h2
                        class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                    >
                        Viajes Programados
                    </h2>
                </div>
                <div class="flex space-x-2">
                    <Link
                        :href="route('viajes.calendario')"
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-600 transition ease-in-out duration-150"
                    >
                        <CalendarDaysIcon class="h-4 w-4 mr-1" />
                        Calendario
                    </Link>
                    <Link
                        :href="route('viajes.create')"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <PlusIcon class="h-4 w-4 mr-1" />
                        Programar Viaje
                    </Link>
                </div>
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
                                <CalendarDaysIcon
                                    class="h-6 w-6 text-indigo-600 dark:text-indigo-400"
                                />
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
                                class="flex-shrink-0 p-3 bg-green-100 dark:bg-green-900 rounded-full"
                            >
                                <TicketIcon
                                    class="h-6 w-6 text-green-600 dark:text-green-400"
                                />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Abiertos
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.abiertos }}
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
                                <PlayCircleIcon
                                    class="h-6 w-6 text-purple-600 dark:text-purple-400"
                                />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    En Curso
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.en_curso }}
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
                                    Próximos 30 días
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.proximos }}
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
                                        placeholder="Buscar por plan de viaje o destino..."
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
                            class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 grid grid-cols-1 md:grid-cols-5 gap-4"
                        >
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                >
                                    Estado
                                </label>
                                <select
                                    v-model="selectedEstado"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Todos los estados</option>
                                    <option
                                        v-for="estado in estados"
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
                                    Plan de Viaje
                                </label>
                                <select
                                    v-model="selectedPlanViaje"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Todos los planes</option>
                                    <option
                                        v-for="plan in planesViaje"
                                        :key="plan.id"
                                        :value="plan.id"
                                    >
                                        {{ plan.nombre }}
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

                            <div class="flex items-end">
                                <button
                                    @click="clearFilters"
                                    class="w-full px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    Limpiar filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Viajes -->
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
                                        Plan de Viaje
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                        @click="sortBy('fecha_salida')"
                                    >
                                        <div class="flex items-center">
                                            Fechas
                                            <component
                                                :is="
                                                    getSortIcon('fecha_salida')
                                                "
                                                v-if="
                                                    getSortIcon('fecha_salida')
                                                "
                                                class="h-4 w-4 ml-1"
                                            />
                                        </div>
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                        @click="sortBy('cupos_disponibles')"
                                    >
                                        <div class="flex items-center">
                                            Cupos
                                            <component
                                                :is="
                                                    getSortIcon(
                                                        'cupos_disponibles'
                                                    )
                                                "
                                                v-if="
                                                    getSortIcon(
                                                        'cupos_disponibles'
                                                    )
                                                "
                                                class="h-4 w-4 ml-1"
                                            />
                                        </div>
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Estado
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Ventas
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
                                    v-for="viaje in viajes.data"
                                    :key="viaje.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center"
                                            >
                                                <CalendarDaysIcon
                                                    class="h-5 w-5 text-white"
                                                />
                                            </div>
                                            <div class="ml-4">
                                                <div
                                                    class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                                >
                                                    {{
                                                        viaje.plan_viaje?.nombre
                                                    }}
                                                </div>
                                                <div
                                                    class="flex items-center text-sm text-gray-500 dark:text-gray-400"
                                                >
                                                    <MapPinIcon
                                                        class="h-3 w-3 mr-1"
                                                    />
                                                    {{
                                                        viaje.plan_viaje
                                                            ?.destino
                                                            ?.nombre_lugar
                                                    }},
                                                    {{
                                                        viaje.plan_viaje
                                                            ?.destino?.ciudad
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm text-gray-900 dark:text-gray-100"
                                        >
                                            {{ formatDate(viaje.fecha_salida) }}
                                        </div>
                                        <div
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            hasta
                                            {{
                                                formatDate(viaje.fecha_retorno)
                                            }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-1">
                                                <div
                                                    class="flex items-center justify-between text-sm mb-1"
                                                >
                                                    <span
                                                        class="text-gray-900 dark:text-gray-100"
                                                    >
                                                        {{
                                                            viaje.cupos_totales -
                                                            viaje.cupos_disponibles
                                                        }}
                                                        /
                                                        {{
                                                            viaje.cupos_totales
                                                        }}
                                                    </span>
                                                    <span
                                                        class="text-gray-500 dark:text-gray-400 text-xs"
                                                    >
                                                        {{
                                                            Math.round(
                                                                ((viaje.cupos_totales -
                                                                    viaje.cupos_disponibles) /
                                                                    viaje.cupos_totales) *
                                                                    100
                                                            )
                                                        }}%
                                                    </span>
                                                </div>
                                                <div
                                                    class="w-24 bg-gray-200 dark:bg-gray-600 rounded-full h-2"
                                                >
                                                    <div
                                                        class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                                                        :style="{
                                                            width:
                                                                ((viaje.cupos_totales -
                                                                    viaje.cupos_disponibles) /
                                                                    viaje.cupos_totales) *
                                                                    100 +
                                                                '%',
                                                        }"
                                                    ></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                getEstadoClasses(
                                                    viaje.estado_viaje
                                                ),
                                            ]"
                                        >
                                            <component
                                                :is="
                                                    getEstadoIcon(
                                                        viaje.estado_viaje
                                                    )
                                                "
                                                class="h-3 w-3 mr-1"
                                            />
                                            {{
                                                getEstadoLabel(
                                                    viaje.estado_viaje
                                                )
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300"
                                        >
                                            <UsersIcon class="h-3 w-3 mr-1" />
                                            {{ viaje.ventas_count }} ventas
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                    >
                                        <div class="flex justify-end space-x-2">
                                            <Link
                                                :href="route('viajes.show', viaje.id)"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 p-1"
                                                title="Ver detalles"
                                            >
                                                <EyeIcon class="h-5 w-5" />
                                            </Link>
                                            <Link
                                                :href="route('viajes.pasajeros', viaje.id)"
                                                class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 p-1"
                                                title="Ver pasajeros"
                                            >
                                                <UsersIcon class="h-5 w-5" />
                                            </Link>
                                            <Link
                                                :href="route('viajes.edit', viaje.id)"
                                                class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-900 dark:hover:text-yellow-300 p-1"
                                                title="Editar"
                                            >
                                                <PencilSquareIcon
                                                    class="h-5 w-5"
                                                />
                                            </Link>
                                            <button
                                                @click="deleteViaje(viaje)"
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 p-1"
                                                title="Eliminar"
                                                :disabled="
                                                    viaje.ventas_count > 0
                                                "
                                                :class="{
                                                    'opacity-50 cursor-not-allowed':
                                                        viaje.ventas_count > 0,
                                                }"
                                            >
                                                <TrashIcon class="h-5 w-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="viajes.data.length === 0">
                                    <td
                                        colspan="6"
                                        class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"
                                    >
                                        <CalendarDaysIcon
                                            class="h-12 w-12 mx-auto mb-4 text-gray-300 dark:text-gray-600"
                                        />
                                        <p class="text-lg font-medium">
                                            No se encontraron viajes programados
                                        </p>
                                        <p class="text-sm mt-1">
                                            Prueba ajustando los filtros o
                                            programa un nuevo viaje
                                        </p>
                                        <Link
                                            :href="route('viajes.create')"
                                            class="inline-flex items-center mt-4 px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                                        >
                                            <PlusIcon class="h-4 w-4 mr-1" />
                                            Programar Viaje
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div
                        v-if="viajes.links && viajes.links.length > 3"
                        class="px-6 py-4 border-t border-gray-200 dark:border-gray-700"
                    >
                        <div class="flex items-center justify-between">
                            <div
                                class="text-sm text-gray-700 dark:text-gray-300"
                            >
                                Mostrando
                                <span class="font-medium">{{
                                    viajes.from
                                }}</span>
                                a
                                <span class="font-medium">{{ viajes.to }}</span>
                                de
                                <span class="font-medium">{{
                                    viajes.total
                                }}</span>
                                resultados
                            </div>
                            <div class="flex space-x-1">
                                <template
                                    v-for="link in viajes.links"
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
