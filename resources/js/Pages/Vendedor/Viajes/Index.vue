<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { useAppUrl } from "@/Composables/useAppUrl";
import {
    CalendarDaysIcon,
    MagnifyingGlassIcon,
    MapPinIcon,
    TicketIcon,
    ClockIcon,
    CurrencyDollarIcon,
    ShoppingCartIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    viajes: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");

// Debounce para búsqueda
let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            resolveUrl("vendedor/viajes-disponibles"),
            {
                search: search.value || undefined,
                sort: props.filters.sort,
                direction: props.filters.direction,
            },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300);
});

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

const { resolveUrl } = useAppUrl();
</script>

<template>
    <Head title="Viajes Disponibles" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <CalendarDaysIcon class="h-6 w-6 mr-2 text-indigo-600" />
                    <h2
                        class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                    >
                        Viajes Disponibles
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Búsqueda -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6"
                >
                    <div class="p-6">
                        <div class="relative">
                            <MagnifyingGlassIcon
                                class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
                            />
                            <input
                                type="text"
                                v-model="search"
                                placeholder="Buscar por nombre de viaje o destino..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Grid de Viajes -->
                <div
                    v-if="viajes.data.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <div
                        v-for="viaje in viajes.data"
                        :key="viaje.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-300 border border-gray-200 dark:border-gray-700 flex flex-col"
                    >
                        <!-- Encabezado con Destino -->
                        <div
                            class="h-32 bg-gradient-to-r from-indigo-500 to-purple-600 p-6 text-white relative"
                        >
                            <h3 class="text-xl font-bold mb-1 truncate">
                                {{ viaje.plan_viaje?.nombre }}
                            </h3>
                            <div class="flex items-center text-indigo-100 text-sm">
                                <MapPinIcon class="h-4 w-4 mr-1" />
                                <span class="truncate">
                                    {{
                                        viaje.plan_viaje?.destino?.nombre_lugar
                                    }},
                                    {{ viaje.plan_viaje?.destino?.ciudad }}
                                </span>
                            </div>
                            <div
                                class="absolute top-4 right-4 bg-white/20 backdrop-blur-sm px-2 py-1 rounded text-xs font-bold"
                            >
                                {{ viaje.plan_viaje?.duracion_dias }} Días
                            </div>
                        </div>

                        <!-- Detalles -->
                        <div class="p-6 flex-1 flex flex-col">
                            <div class="space-y-4 flex-1">
                                <div class="flex justify-between items-center">
                                    <div class="text-sm">
                                        <p
                                            class="text-gray-500 dark:text-gray-400"
                                        >
                                            Salida
                                        </p>
                                        <p
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{ formatDate(viaje.fecha_salida) }}
                                        </p>
                                    </div>
                                    <div class="text-right text-sm">
                                        <p
                                            class="text-gray-500 dark:text-gray-400"
                                        >
                                            Retorno
                                        </p>
                                        <p
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{
                                                formatDate(viaje.fecha_retorno)
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Cupos Disponibles</span>
                                        <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                            {{ viaje.cupos_disponibles }} / {{ viaje.cupos_totales }}
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
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

                                <div class="flex items-center justify-between pt-2">
                                    <span class="text-gray-500 dark:text-gray-400 text-sm">Precio por persona</span>
                                    <span class="text-xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ formatCurrency(viaje.plan_viaje?.precio_base) }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-6">
                                <Link
                                    :href="resolveUrl('vendedor/ventas/create') + '?viaje_id=' + viaje.id"
                                    class="w-full flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <ShoppingCartIcon class="h-4 w-4 mr-2" />
                                    Vender Boleto
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-12 text-center"
                >
                    <TicketIcon
                        class="h-12 w-12 mx-auto mb-4 text-gray-300 dark:text-gray-600"
                    />
                    <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        No se encontraron viajes disponibles
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Intenta ajustar tu búsqueda o contacta al administrador.
                    </p>
                </div>

                <!-- Paginación -->
                <div
                    v-if="viajes.links && viajes.links.length > 3"
                    class="mt-6"
                >
                    <div class="flex justify-center space-x-1">
                        <template
                            v-for="link in viajes.links"
                            :key="link.label"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-2 text-sm rounded-md bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600"
                                :class="[
                                    link.active
                                        ? 'bg-indigo-600 text-white border-indigo-600'
                                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700',
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="px-3 py-2 text-sm text-gray-400 dark:text-gray-600 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
