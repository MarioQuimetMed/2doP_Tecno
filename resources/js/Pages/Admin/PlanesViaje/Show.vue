<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import {
    MapIcon,
    ArrowLeftIcon,
    PencilSquareIcon,
    TrashIcon,
    CalendarDaysIcon,
    ClockIcon,
    CurrencyDollarIcon,
    MapPinIcon,
    GlobeAltIcon,
    TicketIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    planViaje: Object,
    actividadesPorDia: Object,
    stats: Object,
});

const deletePlan = () => {
    if (
        confirm(`¿Está seguro de eliminar el plan "${props.planViaje.nombre}"?`)
    ) {
        router.delete(route('planes-viaje.destroy', props.planViaje.id));
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return "";
    return new Date(dateString).toLocaleDateString("es-BO", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const formatTime = (timeString) => {
    if (!timeString) return "";
    // Manejar formato HH:mm:ss o HH:mm
    const parts = timeString.split(":");
    const hours = parseInt(parts[0]);
    const minutes = parts[1];
    const ampm = hours >= 12 ? "PM" : "AM";
    const hour12 = hours % 12 || 12;
    return `${hour12}:${minutes} ${ampm}`;
};

// Colores para los días
const dayColors = [
    "from-indigo-500 to-purple-600",
    "from-green-500 to-teal-600",
    "from-orange-500 to-red-600",
    "from-blue-500 to-cyan-600",
    "from-pink-500 to-rose-600",
    "from-yellow-500 to-amber-600",
    "from-violet-500 to-fuchsia-600",
];

const getDayColor = (day) => {
    return dayColors[(day - 1) % dayColors.length];
};
</script>

<template>
    <Head :title="`Plan: ${planViaje.nombre}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <MapIcon class="h-6 w-6 mr-2 text-indigo-600" />
                    <h2
                        class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                    >
                        Detalle del Plan de Viaje
                    </h2>
                </div>
                <div class="flex items-center space-x-3">
                    <Link
                        :href="route('planes-viaje.edit', planViaje.id)"
                        class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 focus:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <PencilSquareIcon class="h-4 w-4 mr-1" />
                        Editar
                    </Link>
                    <button
                        @click="deletePlan"
                        :disabled="stats.total_viajes > 0"
                        :class="[
                            'inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150',
                            stats.total_viajes > 0
                                ? 'bg-gray-400 cursor-not-allowed'
                                : 'bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:ring-red-500',
                        ]"
                        :title="
                            stats.total_viajes > 0
                                ? 'No se puede eliminar: tiene viajes asociados'
                                : 'Eliminar plan'
                        "
                    >
                        <TrashIcon class="h-4 w-4 mr-1" />
                        Eliminar
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <div class="mb-4">
                    <Link
                        :href="route('planes-viaje.index')"
                        class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400"
                    >
                        <ArrowLeftIcon class="h-4 w-4 mr-1" />
                        Volver a la lista de planes
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Columna Principal -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Header del Plan -->
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="relative">
                                <!-- Banner -->
                                <div
                                    class="h-48 bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center"
                                >
                                    <MapIcon class="h-24 w-24 text-white/30" />
                                </div>

                                <!-- Contenido -->
                                <div class="p-6">
                                    <div
                                        class="flex items-start justify-between"
                                    >
                                        <div>
                                            <h1
                                                class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                                            >
                                                {{ planViaje.nombre }}
                                            </h1>
                                            <div
                                                class="mt-2 flex items-center text-indigo-600 dark:text-indigo-400"
                                            >
                                                <MapPinIcon
                                                    class="h-5 w-5 mr-1"
                                                />
                                                <span class="font-medium">
                                                    {{
                                                        planViaje.destino
                                                            .nombre_lugar
                                                    }}
                                                </span>
                                            </div>
                                            <div
                                                class="mt-1 flex items-center text-gray-500 dark:text-gray-400 text-sm"
                                            >
                                                <GlobeAltIcon
                                                    class="h-4 w-4 mr-1"
                                                />
                                                {{ planViaje.destino.ciudad }},
                                                {{ planViaje.destino.pais }}
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div
                                                class="text-3xl font-bold text-indigo-600 dark:text-indigo-400"
                                            >
                                                {{
                                                    formatCurrency(
                                                        planViaje.precio_base
                                                    )
                                                }}
                                            </div>
                                            <div
                                                class="text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                por persona
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Badges -->
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200"
                                        >
                                            <CalendarDaysIcon
                                                class="h-4 w-4 mr-1"
                                            />
                                            {{ planViaje.duracion_dias }}
                                            {{
                                                planViaje.duracion_dias === 1
                                                    ? "día"
                                                    : "días"
                                            }}
                                        </span>
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200"
                                        >
                                            <CheckCircleIcon
                                                class="h-4 w-4 mr-1"
                                            />
                                            {{
                                                stats.total_actividades
                                            }}
                                            actividades
                                        </span>
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200"
                                        >
                                            <TicketIcon class="h-4 w-4 mr-1" />
                                            {{ stats.total_viajes }} viajes
                                            programados
                                        </span>
                                    </div>

                                    <!-- Descripción -->
                                    <div
                                        v-if="planViaje.descripcion"
                                        class="mt-6"
                                    >
                                        <h3
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                        >
                                            Descripción
                                        </h3>
                                        <p
                                            class="text-gray-600 dark:text-gray-400 whitespace-pre-line"
                                        >
                                            {{ planViaje.descripcion }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Itinerario -->
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="p-6">
                                <h2
                                    class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6 flex items-center"
                                >
                                    <CalendarDaysIcon
                                        class="h-6 w-6 mr-2 text-indigo-600"
                                    />
                                    Itinerario Detallado
                                </h2>

                                <!-- Timeline -->
                                <div class="space-y-6">
                                    <div
                                        v-for="dia in planViaje.duracion_dias"
                                        :key="dia"
                                        class="relative"
                                    >
                                        <!-- Línea de conexión -->
                                        <div
                                            v-if="dia < planViaje.duracion_dias"
                                            class="absolute left-6 top-14 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"
                                        ></div>

                                        <!-- Día Header -->
                                        <div class="flex items-center mb-4">
                                            <div
                                                :class="[
                                                    'flex-shrink-0 h-12 w-12 rounded-full flex items-center justify-center text-white font-bold shadow-lg bg-gradient-to-br',
                                                    getDayColor(dia),
                                                ]"
                                            >
                                                {{ dia }}
                                            </div>
                                            <div class="ml-4">
                                                <h3
                                                    class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                                                >
                                                    Día {{ dia }}
                                                </h3>
                                                <p
                                                    class="text-sm text-gray-500 dark:text-gray-400"
                                                >
                                                    {{
                                                        actividadesPorDia[dia]
                                                            ?.length || 0
                                                    }}
                                                    actividades programadas
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Actividades del día -->
                                        <div class="ml-6 pl-10 space-y-3">
                                            <div
                                                v-for="actividad in actividadesPorDia[
                                                    dia
                                                ]"
                                                :key="actividad.id"
                                                class="relative flex items-start p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700"
                                            >
                                                <!-- Punto de la timeline -->
                                                <div
                                                    class="absolute -left-10 top-1/2 -translate-y-1/2 h-3 w-3 rounded-full bg-indigo-500 border-2 border-white dark:border-gray-800"
                                                ></div>

                                                <!-- Hora -->
                                                <div
                                                    v-if="actividad.hora_inicio"
                                                    class="flex-shrink-0 mr-4"
                                                >
                                                    <div
                                                        class="flex items-center text-sm font-medium text-indigo-600 dark:text-indigo-400"
                                                    >
                                                        <ClockIcon
                                                            class="h-4 w-4 mr-1"
                                                        />
                                                        {{
                                                            formatTime(
                                                                actividad.hora_inicio
                                                            )
                                                        }}
                                                    </div>
                                                </div>

                                                <!-- Descripción -->
                                                <div class="flex-1">
                                                    <p
                                                        class="text-gray-900 dark:text-gray-100"
                                                    >
                                                        {{
                                                            actividad.descripcion_actividad
                                                        }}
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- Sin actividades -->
                                            <div
                                                v-if="
                                                    !actividadesPorDia[dia]
                                                        ?.length
                                                "
                                                class="flex items-center p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-700"
                                            >
                                                <ExclamationCircleIcon
                                                    class="h-5 w-5 text-amber-500 mr-3"
                                                />
                                                <span
                                                    class="text-amber-700 dark:text-amber-400 text-sm"
                                                >
                                                    No hay actividades
                                                    programadas para este día
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sin itinerario -->
                                <div
                                    v-if="stats.total_actividades === 0"
                                    class="text-center py-12"
                                >
                                    <CalendarDaysIcon
                                        class="h-16 w-16 mx-auto text-gray-300 dark:text-gray-600 mb-4"
                                    />
                                    <h3
                                        class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2"
                                    >
                                        Sin itinerario definido
                                    </h3>
                                    <p
                                        class="text-gray-500 dark:text-gray-400 mb-4"
                                    >
                                        Este plan aún no tiene actividades
                                        programadas
                                    </p>
                                    <Link
                                        :href="
                                            route('planes-viaje.edit', planViaje.id)
                                        "
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                                    >
                                        <PencilSquareIcon
                                            class="h-4 w-4 mr-1"
                                        />
                                        Agregar Actividades
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Lateral -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Estadísticas Rápidas -->
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="p-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                                >
                                    Resumen
                                </h3>

                                <dl class="space-y-4">
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <dt
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            Precio base
                                        </dt>
                                        <dd
                                            class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                                        >
                                            {{
                                                formatCurrency(
                                                    planViaje.precio_base
                                                )
                                            }}
                                        </dd>
                                    </div>
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <dt
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            Duración
                                        </dt>
                                        <dd
                                            class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                                        >
                                            {{ planViaje.duracion_dias }}
                                            {{
                                                planViaje.duracion_dias === 1
                                                    ? "día"
                                                    : "días"
                                            }}
                                        </dd>
                                    </div>
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <dt
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            Actividades
                                        </dt>
                                        <dd
                                            class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                                        >
                                            {{ stats.total_actividades }}
                                        </dd>
                                    </div>
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <dt
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            Viajes programados
                                        </dt>
                                        <dd
                                            class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                                        >
                                            {{ stats.total_viajes }}
                                        </dd>
                                    </div>
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <dt
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            Viajes activos
                                        </dt>
                                        <dd>
                                            <span
                                                :class="[
                                                    'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                                                    stats.viajes_activos > 0
                                                        ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
                                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
                                                ]"
                                            >
                                                {{ stats.viajes_activos }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Destino -->
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="p-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                                >
                                    Destino
                                </h3>

                                <div class="flex items-start">
                                    <div
                                        class="flex-shrink-0 h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center"
                                    >
                                        <MapPinIcon
                                            class="h-6 w-6 text-white"
                                        />
                                    </div>
                                    <div class="ml-4">
                                        <h4
                                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{ planViaje.destino.nombre_lugar }}
                                        </h4>
                                        <p
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            {{ planViaje.destino.ciudad }},
                                            {{ planViaje.destino.pais }}
                                        </p>
                                        <Link
                                            :href="
                                                route('destinos.show', planViaje.destino.id)
                                            "
                                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline mt-1 inline-block"
                                        >
                                            Ver destino →
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Viajes Recientes -->
                        <div
                            v-if="planViaje.viajes?.length"
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="p-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                                >
                                    Viajes Programados
                                </h3>

                                <div class="space-y-3">
                                    <div
                                        v-for="viaje in planViaje.viajes"
                                        :key="viaje.id"
                                        class="p-3 bg-gray-50 dark:bg-gray-900 rounded-lg"
                                    >
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <div>
                                                <p
                                                    class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                                >
                                                    {{
                                                        formatDate(
                                                            viaje.fecha_salida
                                                        )
                                                    }}
                                                </p>
                                                <p
                                                    class="text-xs text-gray-500 dark:text-gray-400"
                                                >
                                                    {{
                                                        viaje.ventas_count || 0
                                                    }}
                                                    ventas
                                                </p>
                                            </div>
                                            <span
                                                :class="[
                                                    'px-2 py-1 text-xs rounded-full font-medium',
                                                    viaje.estado === 'ABIERTO'
                                                        ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
                                                        : viaje.estado ===
                                                          'LLENO'
                                                        ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200'
                                                        : viaje.estado ===
                                                          'EN_CURSO'
                                                        ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200'
                                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
                                                ]"
                                            >
                                                {{ viaje.estado }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <Link
                                    :href="route('viajes.index')"
                                    class="block mt-4 text-center text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
                                >
                                    Ver todos los viajes →
                                </Link>
                            </div>
                        </div>

                        <!-- Información de Registro -->
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="p-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                                >
                                    Información de Registro
                                </h3>

                                <dl class="space-y-2 text-sm">
                                    <div>
                                        <dt
                                            class="text-gray-500 dark:text-gray-400"
                                        >
                                            ID
                                        </dt>
                                        <dd
                                            class="font-mono text-gray-900 dark:text-gray-100"
                                        >
                                            #{{ planViaje.id }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt
                                            class="text-gray-500 dark:text-gray-400"
                                        >
                                            Creado
                                        </dt>
                                        <dd
                                            class="text-gray-900 dark:text-gray-100"
                                        >
                                            {{
                                                formatDate(planViaje.created_at)
                                            }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt
                                            class="text-gray-500 dark:text-gray-400"
                                        >
                                            Última actualización
                                        </dt>
                                        <dd
                                            class="text-gray-900 dark:text-gray-100"
                                        >
                                            {{
                                                formatDate(planViaje.updated_at)
                                            }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
