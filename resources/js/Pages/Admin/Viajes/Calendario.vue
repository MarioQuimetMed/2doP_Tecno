<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, onMounted, watch } from "vue";
import {
    CalendarDaysIcon,
    ArrowLeftIcon,
    PlusIcon,
    ListBulletIcon,
    MapPinIcon,
    UsersIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    viajes: Array,
    estados: Array,
});

const calendarEl = ref(null);
const calendar = ref(null);
const selectedViaje = ref(null);
const showModal = ref(false);

// Colores por estado
const colorMap = {
    ABIERTO: { bg: "#10B981", text: "Abierto" },
    LLENO: { bg: "#3B82F6", text: "Lleno" },
    EN_CURSO: { bg: "#8B5CF6", text: "En Curso" },
    FINALIZADO: { bg: "#6B7280", text: "Finalizado" },
    CANCELADO: { bg: "#EF4444", text: "Cancelado" },
};

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

onMounted(async () => {
    // Cargar FullCalendar dinámicamente
    const { Calendar } = await import("@fullcalendar/core");
    const dayGridPlugin = (await import("@fullcalendar/daygrid")).default;
    const interactionPlugin = (await import("@fullcalendar/interaction"))
        .default;

    calendar.value = new Calendar(calendarEl.value, {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: "dayGridMonth",
        locale: "es",
        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,dayGridWeek",
        },
        buttonText: {
            today: "Hoy",
            month: "Mes",
            week: "Semana",
        },
        events: props.viajes,
        eventClick: (info) => {
            const viaje = props.viajes.find((v) => v.id == info.event.id);
            if (viaje) {
                selectedViaje.value = viaje;
                showModal.value = true;
            }
        },
        eventContent: (arg) => {
            return {
                html: `
                    <div class="p-1 text-xs truncate">
                        <span class="font-medium">${arg.event.title}</span>
                    </div>
                `,
            };
        },
        eventDidMount: (info) => {
            // Añadir tooltip o clases adicionales si es necesario
        },
        datesSet: (info) => {
            // Recargar eventos cuando cambie el rango de fechas
            router.get(
                "/viajes/calendario",
                {
                    start: info.startStr,
                    end: info.endStr,
                },
                {
                    preserveState: true,
                    replace: true,
                    only: ["viajes"],
                }
            );
        },
    });

    calendar.value.render();
});

// Actualizar eventos cuando cambien los viajes
watch(
    () => props.viajes,
    (newViajes) => {
        if (calendar.value) {
            calendar.value.removeAllEvents();
            calendar.value.addEventSource(newViajes);
        }
    },
    { deep: true }
);

const closeModal = () => {
    showModal.value = false;
    selectedViaje.value = null;
};
</script>

<template>
    <Head title="Calendario de Viajes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <CalendarDaysIcon class="h-6 w-6 mr-2 text-indigo-600" />
                    <h2
                        class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                    >
                        Calendario de Viajes
                    </h2>
                </div>
                <div class="flex space-x-2">
                    <Link
                        :href="'/viajes'"
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-600 transition ease-in-out duration-150"
                    >
                        <ListBulletIcon class="h-4 w-4 mr-1" />
                        Vista Lista
                    </Link>
                    <Link
                        :href="'/viajes/create'"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <PlusIcon class="h-4 w-4 mr-1" />
                        Nuevo Viaje
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

                <!-- Leyenda de colores -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6 p-4"
                >
                    <div class="flex flex-wrap items-center gap-4">
                        <span
                            class="text-sm font-medium text-gray-700 dark:text-gray-300"
                            >Estados:</span
                        >
                        <div
                            v-for="(color, estado) in colorMap"
                            :key="estado"
                            class="flex items-center"
                        >
                            <span
                                class="w-4 h-4 rounded mr-2"
                                :style="{ backgroundColor: color.bg }"
                            ></span>
                            <span
                                class="text-sm text-gray-600 dark:text-gray-400"
                                >{{ color.text }}</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Calendario -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="p-6">
                        <div ref="calendarEl" class="fc-calendar"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de detalle del viaje -->
        <div
            v-if="showModal && selectedViaje"
            class="fixed inset-0 z-50 overflow-y-auto"
            @click.self="closeModal"
        >
            <div class="flex items-center justify-center min-h-screen px-4">
                <div
                    class="fixed inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"
                ></div>

                <div
                    class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full overflow-hidden"
                >
                    <!-- Header con gradiente -->
                    <div
                        class="px-6 py-4 text-white"
                        :style="{
                            backgroundColor: selectedViaje.backgroundColor,
                        }"
                    >
                        <h3 class="text-lg font-semibold">
                            {{ selectedViaje.title.split(" (")[0] }}
                        </h3>
                        <div
                            class="flex items-center mt-1 text-white/80 text-sm"
                        >
                            <MapPinIcon class="h-4 w-4 mr-1" />
                            {{ selectedViaje.extendedProps?.destino }},
                            {{ selectedViaje.extendedProps?.ciudad }}
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400"
                                    >Estado:</span
                                >
                                <span
                                    class="px-3 py-1 rounded-full text-sm font-medium"
                                    :style="{
                                        backgroundColor:
                                            selectedViaje.backgroundColor +
                                            '20',
                                        color: selectedViaje.backgroundColor,
                                    }"
                                >
                                    {{ selectedViaje.extendedProps?.estado }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400"
                                    >Fecha de inicio:</span
                                >
                                <span
                                    class="font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {{ formatDate(selectedViaje.start) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 dark:text-gray-400"
                                    >Fecha de fin:</span
                                >
                                <span
                                    class="font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {{
                                        formatDate(
                                            new Date(
                                                new Date(
                                                    selectedViaje.end
                                                ).getTime() - 86400000
                                            )
                                        )
                                    }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span
                                    class="text-gray-500 dark:text-gray-400 flex items-center"
                                >
                                    <UsersIcon class="h-4 w-4 mr-1" />
                                    Cupos:
                                </span>
                                <span
                                    class="font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {{
                                        selectedViaje.extendedProps
                                            ?.cupos_totales -
                                        selectedViaje.extendedProps
                                            ?.cupos_disponibles
                                    }}
                                    /
                                    {{
                                        selectedViaje.extendedProps
                                            ?.cupos_totales
                                    }}
                                    ({{
                                        selectedViaje.extendedProps
                                            ?.porcentaje_ocupacion
                                    }}% ocupado)
                                </span>
                            </div>

                            <!-- Barra de ocupación -->
                            <div>
                                <div
                                    class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2"
                                >
                                    <div
                                        class="h-2 rounded-full transition-all duration-300"
                                        :style="{
                                            width:
                                                selectedViaje.extendedProps
                                                    ?.porcentaje_ocupacion +
                                                '%',
                                            backgroundColor:
                                                selectedViaje.backgroundColor,
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex space-x-3">
                            <Link
                                :href="'/viajes/' + selectedViaje.id"
                                class="flex-1 text-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                            >
                                Ver Detalles
                            </Link>
                            <button
                                @click="closeModal"
                                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                            >
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Estilos personalizados para FullCalendar */
.fc-calendar {
    --fc-border-color: #e5e7eb;
    --fc-today-bg-color: #eef2ff;
}

.dark .fc-calendar {
    --fc-border-color: #374151;
    --fc-today-bg-color: #1e1b4b;
    --fc-page-bg-color: #1f2937;
    --fc-neutral-bg-color: #374151;
}

.fc .fc-button-primary {
    background-color: #4f46e5;
    border-color: #4f46e5;
}

.fc .fc-button-primary:hover {
    background-color: #4338ca;
    border-color: #4338ca;
}

.fc .fc-button-primary:disabled {
    background-color: #6366f1;
    border-color: #6366f1;
}

.fc-event {
    cursor: pointer;
    border-radius: 4px;
    font-size: 0.75rem;
}

.fc-event:hover {
    opacity: 0.9;
}

.fc-daygrid-day-number {
    color: #374151;
}

.dark .fc-daygrid-day-number {
    color: #d1d5db;
}

.fc-col-header-cell-cushion {
    color: #374151;
}

.dark .fc-col-header-cell-cushion {
    color: #d1d5db;
}
</style>
