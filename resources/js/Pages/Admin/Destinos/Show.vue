<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    GlobeAltIcon,
    ArrowLeftIcon,
    MapPinIcon,
    PencilSquareIcon,
    CalendarIcon,
    ClockIcon,
    CurrencyDollarIcon,
    MapIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    destino: Object,
});

// Formatear fecha
const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};
</script>

<template>
    <Head :title="destino.nombre_lugar" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <MapPinIcon class="h-6 w-6 mr-2 text-indigo-600" />
                    <h2
                        class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                    >
                        {{ destino.nombre_lugar }}
                    </h2>
                </div>
                <Link
                    :href="'/destinos/' + destino.id + '/edit'"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    <PencilSquareIcon class="-ml-1 mr-2 h-5 w-5" />
                    Editar
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <div class="mb-4">
                    <Link
                        :href="'/destinos'"
                        class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400"
                    >
                        <ArrowLeftIcon class="h-4 w-4 mr-1" />
                        Volver a la lista de destinos
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Información principal -->
                    <div class="lg:col-span-2">
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <!-- Header con gradiente -->
                            <div
                                class="h-48 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 relative"
                            >
                                <div class="absolute inset-0 bg-black/20"></div>
                                <div
                                    class="absolute bottom-0 left-0 right-0 p-6"
                                >
                                    <div class="flex items-end">
                                        <div
                                            class="flex-shrink-0 h-20 w-20 rounded-xl bg-white dark:bg-gray-800 shadow-lg flex items-center justify-center"
                                        >
                                            <MapPinIcon
                                                class="h-10 w-10 text-indigo-600"
                                            />
                                        </div>
                                        <div class="ml-4 mb-1">
                                            <h1
                                                class="text-2xl font-bold text-white drop-shadow-lg"
                                            >
                                                {{ destino.nombre_lugar }}
                                            </h1>
                                            <p
                                                class="text-white/90 flex items-center"
                                            >
                                                <GlobeAltIcon
                                                    class="h-4 w-4 mr-1"
                                                />
                                                {{ destino.ciudad }},
                                                {{ destino.pais }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contenido -->
                            <div class="p-6">
                                <div class="mb-6">
                                    <h3
                                        class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2"
                                    >
                                        Descripción
                                    </h3>
                                    <p
                                        class="text-gray-600 dark:text-gray-400 whitespace-pre-line"
                                    >
                                        {{
                                            destino.descripcion ||
                                            "Este destino aún no tiene una descripción."
                                        }}
                                    </p>
                                </div>

                                <!-- Información de ubicación -->
                                <div
                                    class="grid grid-cols-2 gap-4 py-4 border-t border-gray-200 dark:border-gray-700"
                                >
                                    <div>
                                        <span
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                            >País</span
                                        >
                                        <p
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{ destino.pais }}
                                        </p>
                                    </div>
                                    <div>
                                        <span
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                            >Ciudad</span
                                        >
                                        <p
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{ destino.ciudad }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Estadísticas -->
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                        >
                            <h3
                                class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                            >
                                Estadísticas
                            </h3>

                            <div class="space-y-4">
                                <div
                                    class="flex items-center justify-between p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg"
                                >
                                    <div class="flex items-center">
                                        <CalendarIcon
                                            class="h-5 w-5 text-indigo-600 dark:text-indigo-400"
                                        />
                                        <span
                                            class="ml-2 text-sm text-gray-600 dark:text-gray-400"
                                            >Planes de Viaje</span
                                        >
                                    </div>
                                    <span
                                        class="text-lg font-bold text-indigo-600 dark:text-indigo-400"
                                    >
                                        {{ destino.planes_viaje_count || 0 }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Fechas -->
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                        >
                            <h3
                                class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                            >
                                Información del Registro
                            </h3>

                            <div class="space-y-3 text-sm">
                                <div
                                    class="flex items-center text-gray-600 dark:text-gray-400"
                                >
                                    <ClockIcon class="h-4 w-4 mr-2" />
                                    <span
                                        >Creado:
                                        {{
                                            formatDate(destino.created_at)
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex items-center text-gray-600 dark:text-gray-400"
                                >
                                    <ClockIcon class="h-4 w-4 mr-2" />
                                    <span
                                        >Actualizado:
                                        {{
                                            formatDate(destino.updated_at)
                                        }}</span
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Acciones rápidas -->
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                        >
                            <h3
                                class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                            >
                                Acciones
                            </h3>

                            <div class="space-y-3">
                                <Link
                                    :href="'/destinos/' + destino.id + '/edit'"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                                >
                                    <PencilSquareIcon
                                        class="-ml-1 mr-2 h-5 w-5"
                                    />
                                    Editar Destino
                                </Link>

                                <Link
                                    :href="'/planes-viaje'"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                                >
                                    <MapIcon class="-ml-1 mr-2 h-5 w-5" />
                                    Ver Planes de Viaje
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Planes de viaje asociados -->
                <div
                    class="mt-6"
                    v-if="
                        destino.planes_viaje && destino.planes_viaje.length > 0
                    "
                >
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <h3
                                class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                            >
                                Planes de Viaje Recientes
                            </h3>

                            <div
                                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                            >
                                <div
                                    v-for="plan in destino.planes_viaje"
                                    :key="plan.id"
                                    class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow"
                                >
                                    <h4
                                        class="font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        {{ plan.nombre }}
                                    </h4>
                                    <div
                                        class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        <CalendarIcon class="h-4 w-4 mr-1" />
                                        {{ plan.duracion_dias }} días
                                    </div>
                                    <div
                                        class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        <CurrencyDollarIcon
                                            class="h-4 w-4 mr-1"
                                        />
                                        ${{
                                            parseFloat(
                                                plan.precio_base
                                            ).toFixed(2)
                                        }}
                                    </div>
                                    <div
                                        class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{
                                            plan.actividades_diarias_count || 0
                                        }}
                                        actividades
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
