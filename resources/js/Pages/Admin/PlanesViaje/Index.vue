<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import {
    MapIcon,
    PlusIcon,
    MagnifyingGlassIcon,
    PencilSquareIcon,
    TrashIcon,
    EyeIcon,
    CalendarDaysIcon,
    CurrencyDollarIcon,
    ClockIcon,
    FunnelIcon,
    ChevronUpIcon,
    ChevronDownIcon,
    MapPinIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    planesViaje: Object,
    destinos: Array,
    stats: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const selectedDestino = ref(props.filters.destino_id || "");
const selectedDuracion = ref(props.filters.duracion || "");
const showFilters = ref(false);

// Debounce para búsqueda
let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

watch([selectedDestino, selectedDuracion], () => {
    applyFilters();
});

const applyFilters = () => {
    router.get(
        "/planes-viaje",
        {
            search: search.value || undefined,
            destino_id: selectedDestino.value || undefined,
            duracion: selectedDuracion.value || undefined,
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
    selectedDestino.value = "";
    selectedDuracion.value = "";
};

const sortBy = (field) => {
    const direction =
        props.filters.sort === field && props.filters.direction === "asc"
            ? "desc"
            : "asc";

    router.get(
        "/planes-viaje",
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

const deletePlan = (plan) => {
    if (confirm(`¿Está seguro de eliminar el plan "${plan.nombre}"?`)) {
        router.delete("/planes-viaje/" + plan.id);
    }
};

// Duraciones únicas para el filtro
const duracionesUnicas = computed(() => {
    const duraciones = [
        ...new Set(props.planesViaje.data.map((p) => p.duracion_dias)),
    ];
    return duraciones.sort((a, b) => a - b);
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value);
};
</script>

<template>
    <Head title="Planes de Viaje" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <MapIcon class="h-6 w-6 mr-2 text-indigo-600" />
                    <h2
                        class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                    >
                        Planes de Viaje
                    </h2>
                </div>
                <Link
                    :href="'/planes-viaje/create'"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <PlusIcon class="h-4 w-4 mr-1" />
                    Nuevo Plan
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
                                <MapIcon
                                    class="h-6 w-6 text-indigo-600 dark:text-indigo-400"
                                />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Total Planes
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
                                <ClockIcon
                                    class="h-6 w-6 text-green-600 dark:text-green-400"
                                />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Duración Promedio
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.duracion_promedio }} días
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
                                <CurrencyDollarIcon
                                    class="h-6 w-6 text-yellow-600 dark:text-yellow-400"
                                />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Precio Promedio
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ formatCurrency(stats.precio_promedio) }}
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
                                <CalendarDaysIcon
                                    class="h-6 w-6 text-purple-600 dark:text-purple-400"
                                />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Con Itinerario
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.con_actividades }}
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
                                        placeholder="Buscar por nombre o descripción..."
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
                                    v-if="selectedDestino || selectedDuracion"
                                    class="ml-2 px-2 py-0.5 text-xs bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded-full"
                                >
                                    {{
                                        (selectedDestino ? 1 : 0) +
                                        (selectedDuracion ? 1 : 0)
                                    }}
                                </span>
                            </button>
                        </div>

                        <!-- Filtros expandibles -->
                        <div
                            v-show="showFilters"
                            class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 grid grid-cols-1 md:grid-cols-3 gap-4"
                        >
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                >
                                    Destino
                                </label>
                                <select
                                    v-model="selectedDestino"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">Todos los destinos</option>
                                    <option
                                        v-for="destino in destinos"
                                        :key="destino.id"
                                        :value="destino.id"
                                    >
                                        {{ destino.nombre_lugar }} -
                                        {{ destino.ciudad }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                >
                                    Duración
                                </label>
                                <select
                                    v-model="selectedDuracion"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">
                                        Todas las duraciones
                                    </option>
                                    <option value="1">1 día</option>
                                    <option value="2">2 días</option>
                                    <option value="3">3 días</option>
                                    <option value="5">5 días</option>
                                    <option value="7">7 días (1 semana)</option>
                                    <option value="14">
                                        14 días (2 semanas)
                                    </option>
                                </select>
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

                <!-- Tabla de Planes -->
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
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                        @click="sortBy('nombre')"
                                    >
                                        <div class="flex items-center">
                                            Nombre
                                            <component
                                                :is="getSortIcon('nombre')"
                                                v-if="getSortIcon('nombre')"
                                                class="h-4 w-4 ml-1"
                                            />
                                        </div>
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Destino
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                        @click="sortBy('duracion_dias')"
                                    >
                                        <div class="flex items-center">
                                            Duración
                                            <component
                                                :is="
                                                    getSortIcon('duracion_dias')
                                                "
                                                v-if="
                                                    getSortIcon('duracion_dias')
                                                "
                                                class="h-4 w-4 ml-1"
                                            />
                                        </div>
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600"
                                        @click="sortBy('precio_base')"
                                    >
                                        <div class="flex items-center">
                                            Precio
                                            <component
                                                :is="getSortIcon('precio_base')"
                                                v-if="
                                                    getSortIcon('precio_base')
                                                "
                                                class="h-4 w-4 ml-1"
                                            />
                                        </div>
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Actividades
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Viajes
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
                                    v-for="plan in planesViaje.data"
                                    :key="plan.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center"
                                            >
                                                <MapIcon
                                                    class="h-5 w-5 text-white"
                                                />
                                            </div>
                                            <div class="ml-4">
                                                <div
                                                    class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                                >
                                                    {{ plan.nombre }}
                                                </div>
                                                <div
                                                    class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-xs"
                                                >
                                                    {{
                                                        plan.descripcion?.substring(
                                                            0,
                                                            50
                                                        )
                                                    }}{{
                                                        plan.descripcion
                                                            ?.length > 50
                                                            ? "..."
                                                            : ""
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="flex items-center text-sm text-gray-900 dark:text-gray-100"
                                        >
                                            <MapPinIcon
                                                class="h-4 w-4 mr-1 text-gray-400"
                                            />
                                            {{ plan.destino?.nombre_lugar }}
                                        </div>
                                        <div
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            {{ plan.destino?.ciudad }},
                                            {{ plan.destino?.pais }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200"
                                        >
                                            <ClockIcon class="h-3 w-3 mr-1" />
                                            {{ plan.duracion_dias }}
                                            {{
                                                plan.duracion_dias === 1
                                                    ? "día"
                                                    : "días"
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{
                                                formatCurrency(plan.precio_base)
                                            }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                plan.actividades_diarias_count >
                                                0
                                                    ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
                                                    : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
                                            ]"
                                        >
                                            {{
                                                plan.actividades_diarias_count
                                            }}
                                            actividades
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200"
                                        >
                                            {{ plan.viajes_count }} viajes
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                    >
                                        <div class="flex justify-end space-x-2">
                                            <Link
                                                :href="
                                                    '/planes-viaje/' + plan.id
                                                "
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 p-1"
                                                title="Ver detalles"
                                            >
                                                <EyeIcon class="h-5 w-5" />
                                            </Link>
                                            <Link
                                                :href="
                                                    '/planes-viaje/' +
                                                    plan.id +
                                                    '/edit'
                                                "
                                                class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-900 dark:hover:text-yellow-300 p-1"
                                                title="Editar"
                                            >
                                                <PencilSquareIcon
                                                    class="h-5 w-5"
                                                />
                                            </Link>
                                            <button
                                                @click="deletePlan(plan)"
                                                class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 p-1"
                                                title="Eliminar"
                                                :disabled="
                                                    plan.viajes_count > 0
                                                "
                                                :class="{
                                                    'opacity-50 cursor-not-allowed':
                                                        plan.viajes_count > 0,
                                                }"
                                            >
                                                <TrashIcon class="h-5 w-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="planesViaje.data.length === 0">
                                    <td
                                        colspan="7"
                                        class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"
                                    >
                                        <MapIcon
                                            class="h-12 w-12 mx-auto mb-4 text-gray-300 dark:text-gray-600"
                                        />
                                        <p class="text-lg font-medium">
                                            No se encontraron planes de viaje
                                        </p>
                                        <p class="text-sm mt-1">
                                            Prueba ajustando los filtros o crea
                                            un nuevo plan
                                        </p>
                                        <Link
                                            :href="'/planes-viaje/create'"
                                            class="inline-flex items-center mt-4 px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                                        >
                                            <PlusIcon class="h-4 w-4 mr-1" />
                                            Crear Plan de Viaje
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div
                        v-if="planesViaje.links && planesViaje.links.length > 3"
                        class="px-6 py-4 border-t border-gray-200 dark:border-gray-700"
                    >
                        <div class="flex items-center justify-between">
                            <div
                                class="text-sm text-gray-700 dark:text-gray-300"
                            >
                                Mostrando
                                <span class="font-medium">{{
                                    planesViaje.from
                                }}</span>
                                a
                                <span class="font-medium">{{
                                    planesViaje.to
                                }}</span>
                                de
                                <span class="font-medium">{{
                                    planesViaje.total
                                }}</span>
                                resultados
                            </div>
                            <div class="flex space-x-1">
                                <template
                                    v-for="link in planesViaje.links"
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
