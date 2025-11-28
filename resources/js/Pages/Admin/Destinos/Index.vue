<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import {
    MapPinIcon,
    PencilSquareIcon,
    TrashIcon,
    PlusIcon,
    MagnifyingGlassIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    EyeIcon,
    GlobeAltIcon,
    FunnelIcon,
    XMarkIcon,
} from "@heroicons/vue/24/outline";
import { ref, watch } from "vue";

const props = defineProps({
    destinos: Object,
    paises: Array,
    filters: Object,
});

const search = ref(props.filters?.search || "");
const selectedPais = ref(props.filters?.pais || "");

// Debounce para búsqueda
let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

watch(selectedPais, () => {
    applyFilters();
});

const applyFilters = () => {
    router.get(
        route('destinos.index'),
        {
            search: search.value || undefined,
            pais: selectedPais.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const clearFilters = () => {
    search.value = "";
    selectedPais.value = "";
    router.get(route('destinos.index'));
};

const deleteDestino = (id, nombre) => {
    if (confirm(`¿Estás seguro de eliminar el destino "${nombre}"?`)) {
        router.delete(route('destinos.destroy', id));
    }
};

const goToPage = (url) => {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Gestión de Destinos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <GlobeAltIcon class="h-6 w-6 mr-2 text-indigo-600" />
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Gestión de Destinos
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Header Actions -->
                        <div
                            class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4"
                        >
                            <!-- Filtros -->
                            <div
                                class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto"
                            >
                                <!-- Búsqueda -->
                                <div class="relative w-full sm:w-64">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                    >
                                        <MagnifyingGlassIcon
                                            class="h-5 w-5 text-gray-400"
                                        />
                                    </div>
                                    <input
                                        v-model="search"
                                        type="text"
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        placeholder="Buscar destinos..."
                                    />
                                </div>

                                <!-- Filtro por país -->
                                <div class="relative w-full sm:w-48">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                    >
                                        <FunnelIcon
                                            class="h-5 w-5 text-gray-400"
                                        />
                                    </div>
                                    <select
                                        v-model="selectedPais"
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    >
                                        <option value="">
                                            Todos los países
                                        </option>
                                        <option
                                            v-for="pais in paises"
                                            :key="pais"
                                            :value="pais"
                                        >
                                            {{ pais }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Limpiar filtros -->
                                <button
                                    v-if="search || selectedPais"
                                    @click="clearFilters"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                                >
                                    <XMarkIcon class="h-4 w-4 mr-1" />
                                    Limpiar
                                </button>
                            </div>

                            <!-- Botón Nuevo -->
                            <Link
                                :href="route('destinos.create')"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 w-full sm:w-auto justify-center"
                            >
                                <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
                                Nuevo Destino
                            </Link>
                        </div>

                        <!-- Stats Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                            <div
                                class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-4"
                            >
                                <div class="flex items-center">
                                    <GlobeAltIcon
                                        class="h-8 w-8 text-indigo-600 dark:text-indigo-400"
                                    />
                                    <div class="ml-3">
                                        <p
                                            class="text-sm font-medium text-indigo-600 dark:text-indigo-400"
                                        >
                                            Total Destinos
                                        </p>
                                        <p
                                            class="text-2xl font-bold text-indigo-900 dark:text-indigo-100"
                                        >
                                            {{ destinos.total }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4"
                            >
                                <div class="flex items-center">
                                    <MapPinIcon
                                        class="h-8 w-8 text-green-600 dark:text-green-400"
                                    />
                                    <div class="ml-3">
                                        <p
                                            class="text-sm font-medium text-green-600 dark:text-green-400"
                                        >
                                            Países
                                        </p>
                                        <p
                                            class="text-2xl font-bold text-green-900 dark:text-green-100"
                                        >
                                            {{ paises.length }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4"
                            >
                                <div class="flex items-center">
                                    <EyeIcon
                                        class="h-8 w-8 text-purple-600 dark:text-purple-400"
                                    />
                                    <div class="ml-3">
                                        <p
                                            class="text-sm font-medium text-purple-600 dark:text-purple-400"
                                        >
                                            En esta página
                                        </p>
                                        <p
                                            class="text-2xl font-bold text-purple-900 dark:text-purple-100"
                                        >
                                            {{ destinos.data.length }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
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
                                            Destino
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                        >
                                            Ubicación
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                        >
                                            Planes de Viaje
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
                                        v-for="destino in destinos.data"
                                        :key="destino.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    >
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div
                                                    class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center"
                                                >
                                                    <MapPinIcon
                                                        class="h-5 w-5 text-white"
                                                    />
                                                </div>
                                                <div class="ml-4">
                                                    <div
                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                                    >
                                                        {{
                                                            destino.nombre_lugar
                                                        }}
                                                    </div>
                                                    <div
                                                        class="text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate"
                                                    >
                                                        {{
                                                            destino.descripcion ||
                                                            "Sin descripción"
                                                        }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div
                                                class="text-sm text-gray-900 dark:text-gray-100"
                                            >
                                                {{ destino.ciudad }}
                                            </div>
                                            <div
                                                class="text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                {{ destino.pais }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="
                                                    destino.planes_viaje_count >
                                                    0
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                                                        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400'
                                                "
                                            >
                                                {{
                                                    destino.planes_viaje_count
                                                }}
                                                {{
                                                    destino.planes_viaje_count ===
                                                    1
                                                        ? "plan"
                                                        : "planes"
                                                }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                        >
                                            <div
                                                class="flex justify-end space-x-2"
                                            >
                                                <Link
                                                    :href="
                                                        '/destinos/' +
                                                        destino.id
                                                    "
                                                    class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                                                    title="Ver detalles"
                                                >
                                                    <EyeIcon class="h-5 w-5" />
                                                </Link>
                                                <Link
                                                    :href="
                                                        '/destinos/' +
                                                        destino.id +
                                                        '/edit'
                                                    "
                                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                    title="Editar"
                                                >
                                                    <PencilSquareIcon
                                                        class="h-5 w-5"
                                                    />
                                                </Link>
                                                <button
                                                    @click="
                                                        deleteDestino(
                                                            destino.id,
                                                            destino.nombre_lugar
                                                        )
                                                    "
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                    :class="{
                                                        'opacity-50 cursor-not-allowed':
                                                            destino.planes_viaje_count >
                                                            0,
                                                    }"
                                                    :disabled="
                                                        destino.planes_viaje_count >
                                                        0
                                                    "
                                                    :title="
                                                        destino.planes_viaje_count >
                                                        0
                                                            ? 'No se puede eliminar: tiene planes asociados'
                                                            : 'Eliminar'
                                                    "
                                                >
                                                    <TrashIcon
                                                        class="h-5 w-5"
                                                    />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="destinos.data.length === 0">
                                        <td
                                            colspan="4"
                                            class="px-6 py-12 text-center"
                                        >
                                            <GlobeAltIcon
                                                class="mx-auto h-12 w-12 text-gray-400"
                                            />
                                            <h3
                                                class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100"
                                            >
                                                No hay destinos
                                            </h3>
                                            <p
                                                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                {{
                                                    search || selectedPais
                                                        ? "No se encontraron destinos con los filtros aplicados."
                                                        : "Comienza creando un nuevo destino."
                                                }}
                                            </p>
                                            <div
                                                class="mt-6"
                                                v-if="!search && !selectedPais"
                                            >
                                                <Link
                                                    :href="'/destinos/create'"
                                                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                                                >
                                                    <PlusIcon
                                                        class="-ml-1 mr-2 h-5 w-5"
                                                    />
                                                    Nuevo Destino
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div
                            v-if="destinos.last_page > 1"
                            class="mt-6 flex flex-col sm:flex-row items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4 gap-4"
                        >
                            <div
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Mostrando
                                <span class="font-medium">{{
                                    destinos.from
                                }}</span>
                                a
                                <span class="font-medium">{{
                                    destinos.to
                                }}</span>
                                de
                                <span class="font-medium">{{
                                    destinos.total
                                }}</span>
                                destinos
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    @click="goToPage(destinos.prev_page_url)"
                                    :disabled="!destinos.prev_page_url"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <ChevronLeftIcon class="h-4 w-4 mr-1" />
                                    Anterior
                                </button>
                                <span
                                    class="inline-flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300"
                                >
                                    Página {{ destinos.current_page }} de
                                    {{ destinos.last_page }}
                                </span>
                                <button
                                    @click="goToPage(destinos.next_page_url)"
                                    :disabled="!destinos.next_page_url"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Siguiente
                                    <ChevronRightIcon class="h-4 w-4 ml-1" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
