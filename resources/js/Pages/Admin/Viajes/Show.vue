<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import {
    CalendarDaysIcon,
    ArrowLeftIcon,
    MapPinIcon,
    UsersIcon,
    CurrencyDollarIcon,
    ClockIcon,
    PencilSquareIcon,
    TicketIcon,
    CheckCircleIcon,
    XCircleIcon,
    PlayCircleIcon,
    ChartBarIcon,
    ListBulletIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    viaje: Object,
    pasajeros: Array,
    actividadesPorDia: Object,
    stats: Object,
    estados: Array,
});

const showEstadoModal = ref(false);
const selectedEstado = ref(props.viaje.estado_viaje);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-BO", {
        weekday: "long",
        year: "numeric",
        month: "long",
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

const cambiarEstado = () => {
    router.patch(
        "/viajes/" + props.viaje.id + "/cambiar-estado",
        {
            estado_viaje: selectedEstado.value,
        },
        {
            onSuccess: () => {
                showEstadoModal.value = false;
            },
        }
    );
};

const formatTime = (time) => {
    if (!time) return "";
    return time.substring(0, 5);
};
</script>

<template>
    <Head :title="`Viaje: ${viaje.plan_viaje?.nombre}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <CalendarDaysIcon class="h-6 w-6 mr-2 text-indigo-600" />
                    <h2
                        class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                    >
                        Detalle del Viaje
                    </h2>
                </div>
                <div class="flex space-x-2">
                    <Link
                        :href="'/viajes/' + viaje.id + '/pasajeros'"
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-600 transition ease-in-out duration-150"
                    >
                        <UsersIcon class="h-4 w-4 mr-1" />
                        Pasajeros
                    </Link>
                    <Link
                        :href="'/viajes/' + viaje.id + '/edit'"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <PencilSquareIcon class="h-4 w-4 mr-1" />
                        Editar
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <div class="mb-4">
                    <Link
                        :href="'/viajes'"
                        class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400"
                    >
                        <ArrowLeftIcon class="h-4 w-4 mr-1" />
                        Volver a la lista de viajes
                    </Link>
                </div>

                <!-- Header del Viaje -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6"
                >
                    <div
                        class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-8 text-white"
                    >
                        <div
                            class="flex flex-col md:flex-row md:items-center md:justify-between"
                        >
                            <div>
                                <h1 class="text-2xl font-bold">
                                    {{ viaje.plan_viaje?.nombre }}
                                </h1>
                                <div
                                    class="flex items-center mt-2 text-indigo-100"
                                >
                                    <MapPinIcon class="h-5 w-5 mr-1" />
                                    {{
                                        viaje.plan_viaje?.destino?.nombre_lugar
                                    }}, {{ viaje.plan_viaje?.destino?.ciudad }},
                                    {{ viaje.plan_viaje?.destino?.pais }}
                                </div>
                            </div>
                            <div
                                class="mt-4 md:mt-0 flex items-center space-x-3"
                            >
                                <span
                                    :class="[
                                        'inline-flex items-center px-4 py-2 rounded-full text-sm font-medium',
                                        getEstadoClasses(viaje.estado_viaje),
                                    ]"
                                >
                                    <component
                                        :is="getEstadoIcon(viaje.estado_viaje)"
                                        class="h-5 w-5 mr-2"
                                    />
                                    {{ getEstadoLabel(viaje.estado_viaje) }}
                                </span>
                                <button
                                    @click="showEstadoModal = true"
                                    class="px-3 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition"
                                >
                                    Cambiar Estado
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Columna Principal -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Estadísticas -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div
                                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                            >
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 p-3 bg-green-100 dark:bg-green-900 rounded-full"
                                    >
                                        <UsersIcon
                                            class="h-6 w-6 text-green-600 dark:text-green-400"
                                        />
                                    </div>
                                    <div class="ml-4">
                                        <p
                                            class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            Ventas
                                        </p>
                                        <p
                                            class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                        >
                                            {{ stats.total_ventas }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                            >
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 p-3 bg-indigo-100 dark:bg-indigo-900 rounded-full"
                                    >
                                        <CurrencyDollarIcon
                                            class="h-6 w-6 text-indigo-600 dark:text-indigo-400"
                                        />
                                    </div>
                                    <div class="ml-4">
                                        <p
                                            class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            Ingresos
                                        </p>
                                        <p
                                            class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                        >
                                            {{
                                                formatCurrency(
                                                    stats.ingresos_generados
                                                )
                                            }}
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
                                        <ChartBarIcon
                                            class="h-6 w-6 text-purple-600 dark:text-purple-400"
                                        />
                                    </div>
                                    <div class="ml-4">
                                        <p
                                            class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                        >
                                            Ocupación
                                        </p>
                                        <p
                                            class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                        >
                                            {{ stats.porcentaje_ocupacion }}%
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
                                <div
                                    class="flex items-center mb-6 pb-4 border-b border-gray-200 dark:border-gray-700"
                                >
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center"
                                    >
                                        <ListBulletIcon
                                            class="h-5 w-5 text-white"
                                        />
                                    </div>
                                    <div class="ml-4">
                                        <h3
                                            class="text-lg font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            Itinerario
                                        </h3>
                                        <p
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            Actividades planificadas para este
                                            viaje
                                        </p>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    <div
                                        v-for="(
                                            actividades, dia
                                        ) in actividadesPorDia"
                                        :key="dia"
                                        class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden"
                                    >
                                        <div
                                            class="bg-gray-50 dark:bg-gray-700 px-4 py-3 flex items-center"
                                        >
                                            <span
                                                class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-indigo-600 text-white text-sm font-medium"
                                            >
                                                {{ dia }}
                                            </span>
                                            <span
                                                class="ml-3 font-medium text-gray-900 dark:text-gray-100"
                                            >
                                                Día {{ dia }}
                                            </span>
                                        </div>
                                        <div class="p-4 space-y-3">
                                            <div
                                                v-for="actividad in actividades"
                                                :key="actividad.id"
                                                class="flex items-start p-3 bg-gray-50 dark:bg-gray-900 rounded-lg"
                                            >
                                                <div
                                                    v-if="actividad.hora_inicio"
                                                    class="flex-shrink-0 w-16 text-sm text-indigo-600 dark:text-indigo-400 font-medium"
                                                >
                                                    {{
                                                        formatTime(
                                                            actividad.hora_inicio
                                                        )
                                                    }}
                                                </div>
                                                <div
                                                    class="flex-1 text-sm text-gray-700 dark:text-gray-300"
                                                >
                                                    {{
                                                        actividad.descripcion_actividad
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-if="
                                            Object.keys(actividadesPorDia)
                                                .length === 0
                                        "
                                        class="text-center py-8 text-gray-500 dark:text-gray-400"
                                    >
                                        <ListBulletIcon
                                            class="h-12 w-12 mx-auto mb-4 text-gray-300 dark:text-gray-600"
                                        />
                                        <p>
                                            No hay actividades definidas para
                                            este plan de viaje
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Últimas Ventas -->
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="p-6">
                                <div
                                    class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-700"
                                >
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-yellow-500 to-orange-600 flex items-center justify-center"
                                        >
                                            <TicketIcon
                                                class="h-5 w-5 text-white"
                                            />
                                        </div>
                                        <div class="ml-4">
                                            <h3
                                                class="text-lg font-medium text-gray-900 dark:text-gray-100"
                                            >
                                                Últimas Ventas
                                            </h3>
                                            <p
                                                class="text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                Ventas recientes para este viaje
                                            </p>
                                        </div>
                                    </div>
                                    <Link
                                        :href="
                                            '/viajes/' + viaje.id + '/pasajeros'
                                        "
                                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                                    >
                                        Ver todas →
                                    </Link>
                                </div>

                                <div
                                    v-if="viaje.ventas?.length > 0"
                                    class="space-y-3"
                                >
                                    <div
                                        v-for="venta in viaje.ventas"
                                        :key="venta.id"
                                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-lg"
                                    >
                                        <div class="flex items-center">
                                            <div
                                                class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center"
                                            >
                                                <UsersIcon
                                                    class="h-5 w-5 text-gray-500 dark:text-gray-400"
                                                />
                                            </div>
                                            <div class="ml-3">
                                                <p
                                                    class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                                >
                                                    {{ venta.cliente?.name }}
                                                </p>
                                                <p
                                                    class="text-xs text-gray-500 dark:text-gray-400"
                                                >
                                                    Vendedor:
                                                    {{ venta.vendedor?.name }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p
                                                class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                            >
                                                {{
                                                    formatCurrency(
                                                        venta.monto_total
                                                    )
                                                }}
                                            </p>
                                            <p
                                                class="text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                {{ venta.tipo_pago }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-else
                                    class="text-center py-8 text-gray-500 dark:text-gray-400"
                                >
                                    <TicketIcon
                                        class="h-12 w-12 mx-auto mb-4 text-gray-300 dark:text-gray-600"
                                    />
                                    <p>
                                        No hay ventas registradas para este
                                        viaje
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Lateral -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Información del Viaje -->
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="p-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                                >
                                    Información del Viaje
                                </h3>

                                <div class="space-y-4">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-gray-500 dark:text-gray-400 flex items-center"
                                        >
                                            <CalendarDaysIcon
                                                class="h-4 w-4 mr-2"
                                            />
                                            Salida
                                        </span>
                                        <span
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{ formatDate(viaje.fecha_salida) }}
                                        </span>
                                    </div>

                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-gray-500 dark:text-gray-400 flex items-center"
                                        >
                                            <CalendarDaysIcon
                                                class="h-4 w-4 mr-2"
                                            />
                                            Retorno
                                        </span>
                                        <span
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{
                                                formatDate(viaje.fecha_retorno)
                                            }}
                                        </span>
                                    </div>

                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-gray-500 dark:text-gray-400 flex items-center"
                                        >
                                            <ClockIcon class="h-4 w-4 mr-2" />
                                            Duración
                                        </span>
                                        <span
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{
                                                viaje.plan_viaje?.duracion_dias
                                            }}
                                            días
                                        </span>
                                    </div>

                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="text-gray-500 dark:text-gray-400 flex items-center"
                                        >
                                            <CurrencyDollarIcon
                                                class="h-4 w-4 mr-2"
                                            />
                                            Precio
                                        </span>
                                        <span
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{
                                                formatCurrency(
                                                    viaje.plan_viaje
                                                        ?.precio_base
                                                )
                                            }}/persona
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cupos -->
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="p-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                                >
                                    Disponibilidad de Cupos
                                </h3>

                                <div class="text-center mb-4">
                                    <span
                                        class="text-4xl font-bold text-indigo-600 dark:text-indigo-400"
                                    >
                                        {{ viaje.cupos_disponibles }}
                                    </span>
                                    <span
                                        class="text-gray-500 dark:text-gray-400"
                                    >
                                        / {{ viaje.cupos_totales }}
                                    </span>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400 mt-1"
                                    >
                                        cupos disponibles
                                    </p>
                                </div>

                                <div
                                    class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-4 mb-4"
                                >
                                    <div
                                        class="bg-indigo-600 h-4 rounded-full transition-all duration-300"
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

                                <div class="grid grid-cols-2 gap-4 text-center">
                                    <div
                                        class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg"
                                    >
                                        <p
                                            class="text-2xl font-bold text-green-600 dark:text-green-400"
                                        >
                                            {{ viaje.cupos_disponibles }}
                                        </p>
                                        <p
                                            class="text-xs text-green-700 dark:text-green-300"
                                        >
                                            Disponibles
                                        </p>
                                    </div>
                                    <div
                                        class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
                                    >
                                        <p
                                            class="text-2xl font-bold text-blue-600 dark:text-blue-400"
                                        >
                                            {{
                                                viaje.cupos_totales -
                                                viaje.cupos_disponibles
                                            }}
                                        </p>
                                        <p
                                            class="text-xs text-blue-700 dark:text-blue-300"
                                        >
                                            Vendidos
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones Rápidas -->
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                        >
                            <div class="p-6">
                                <h3
                                    class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                                >
                                    Acciones Rápidas
                                </h3>

                                <div class="space-y-3">
                                    <Link
                                        :href="'/viajes/' + viaje.id + '/edit'"
                                        class="block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                                    >
                                        Editar Viaje
                                    </Link>
                                    <Link
                                        :href="
                                            '/viajes/' + viaje.id + '/pasajeros'
                                        "
                                        class="block w-full text-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                                    >
                                        Ver Pasajeros
                                    </Link>
                                    <button
                                        @click="showEstadoModal = true"
                                        class="block w-full text-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                                    >
                                        Cambiar Estado
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Cambiar Estado -->
        <div
            v-if="showEstadoModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            @click.self="showEstadoModal = false"
        >
            <div class="flex items-center justify-center min-h-screen px-4">
                <div
                    class="fixed inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"
                ></div>

                <div
                    class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6"
                >
                    <h3
                        class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                    >
                        Cambiar Estado del Viaje
                    </h3>

                    <div class="space-y-3">
                        <label
                            v-for="estado in estados"
                            :key="estado.value"
                            class="flex items-center p-3 border rounded-lg cursor-pointer transition"
                            :class="[
                                selectedEstado === estado.value
                                    ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20'
                                    : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700',
                            ]"
                        >
                            <input
                                type="radio"
                                v-model="selectedEstado"
                                :value="estado.value"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500"
                            />
                            <span
                                :class="[
                                    'ml-3 px-2 py-1 rounded-full text-xs font-medium',
                                    getEstadoClasses(estado.value),
                                ]"
                            >
                                {{ estado.label }}
                            </span>
                        </label>
                    </div>

                    <div class="mt-6 flex space-x-3">
                        <button
                            @click="showEstadoModal = false"
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Cancelar
                        </button>
                        <button
                            @click="cambiarEstado"
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                        >
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
