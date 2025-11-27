<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import {
    CalendarDaysIcon,
    ArrowLeftIcon,
    MapPinIcon,
    UsersIcon,
    CurrencyDollarIcon,
    ClockIcon,
    InformationCircleIcon,
    ExclamationTriangleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    viaje: Object,
    planesViaje: Array,
    estados: Array,
});

const form = useForm({
    plan_viaje_id: props.viaje.plan_viaje_id,
    fecha_salida: props.viaje.fecha_salida?.split('T')[0] || props.viaje.fecha_salida,
    cupos_totales: props.viaje.cupos_totales,
    estado_viaje: props.viaje.estado_viaje,
});

// Plan seleccionado para mostrar detalles
const planSeleccionado = computed(() => {
    return props.planesViaje.find(p => p.id == form.plan_viaje_id);
});

// Fecha de retorno calculada
const fechaRetornoCalculada = computed(() => {
    if (!form.fecha_salida || !planSeleccionado.value) return null;
    const fechaSalida = new Date(form.fecha_salida);
    fechaSalida.setDate(fechaSalida.getDate() + planSeleccionado.value.duracion_dias - 1);
    return fechaSalida.toISOString().split('T')[0];
});

// Cupos vendidos
const cuposVendidos = computed(() => {
    return props.viaje.cupos_totales - props.viaje.cupos_disponibles;
});

// Verificar si se puede reducir cupos
const puedeReducirCupos = computed(() => {
    return form.cupos_totales >= cuposVendidos.value;
});

const submit = () => {
    form.put(route("viajes.update", props.viaje.id));
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value);
};

const formatDate = (date) => {
    if (!date) return "";
    return new Date(date).toLocaleDateString("es-BO", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const getEstadoClasses = (estado) => {
    const colorMap = {
        ABIERTO: 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
        LLENO: 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
        EN_CURSO: 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200',
        FINALIZADO: 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300',
        CANCELADO: 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
    };
    return colorMap[estado] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Editar Viaje" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <CalendarDaysIcon class="h-6 w-6 mr-2 text-indigo-600" />
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Editar Viaje
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <div class="mb-4">
                    <Link
                        :href="route('viajes.index')"
                        class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400"
                    >
                        <ArrowLeftIcon class="h-4 w-4 mr-1" />
                        Volver a la lista de viajes
                    </Link>
                </div>

                <!-- Alerta si tiene ventas -->
                <div
                    v-if="cuposVendidos > 0"
                    class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg"
                >
                    <div class="flex">
                        <ExclamationTriangleIcon class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Viaje con ventas activas
                            </h3>
                            <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                                Este viaje tiene {{ cuposVendidos }} cupo(s) vendido(s). No podrá reducir los cupos por debajo de esta cantidad.
                            </p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Columna Principal - Formulario -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Selección del Plan de Viaje -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <div class="flex items-center mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                                        <div class="flex-shrink-0 h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                            <MapPinIcon class="h-6 w-6 text-white" />
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                Plan de Viaje
                                            </h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Modificar el plan asociado al viaje
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        <InputLabel for="plan_viaje_id" value="Plan de Viaje *" />
                                        <select
                                            id="plan_viaje_id"
                                            v-model="form.plan_viaje_id"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            required
                                            :disabled="cuposVendidos > 0"
                                        >
                                            <option value="">Seleccione un plan de viaje</option>
                                            <option
                                                v-for="plan in planesViaje"
                                                :key="plan.id"
                                                :value="plan.id"
                                            >
                                                {{ plan.nombre }} - {{ plan.destino?.nombre_lugar }} ({{ plan.duracion_dias }} días)
                                            </option>
                                        </select>
                                        <p v-if="cuposVendidos > 0" class="mt-1 text-xs text-yellow-600 dark:text-yellow-400">
                                            No se puede cambiar el plan porque hay ventas asociadas
                                        </p>
                                        <InputError class="mt-2" :message="form.errors.plan_viaje_id" />
                                    </div>

                                    <!-- Información del Plan Seleccionado -->
                                    <div
                                        v-if="planSeleccionado"
                                        class="mt-4 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800"
                                    >
                                        <div class="flex items-start">
                                            <InformationCircleIcon class="h-5 w-5 text-indigo-600 dark:text-indigo-400 mt-0.5" />
                                            <div class="ml-3">
                                                <h4 class="text-sm font-medium text-indigo-900 dark:text-indigo-100">
                                                    {{ planSeleccionado.nombre }}
                                                </h4>
                                                <div class="mt-2 grid grid-cols-2 gap-4 text-sm text-indigo-700 dark:text-indigo-300">
                                                    <div class="flex items-center">
                                                        <MapPinIcon class="h-4 w-4 mr-1" />
                                                        {{ planSeleccionado.destino?.nombre_lugar }}, {{ planSeleccionado.destino?.ciudad }}
                                                    </div>
                                                    <div class="flex items-center">
                                                        <ClockIcon class="h-4 w-4 mr-1" />
                                                        {{ planSeleccionado.duracion_dias }} {{ planSeleccionado.duracion_dias === 1 ? 'día' : 'días' }}
                                                    </div>
                                                    <div class="flex items-center">
                                                        <CurrencyDollarIcon class="h-4 w-4 mr-1" />
                                                        {{ formatCurrency(planSeleccionado.precio_base) }} por persona
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Configuración del Viaje -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                                <div class="p-6">
                                    <div class="flex items-center mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                                        <div class="flex-shrink-0 h-12 w-12 rounded-full bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center">
                                            <CalendarDaysIcon class="h-6 w-6 text-white" />
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                Configuración del Viaje
                                            </h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Modifique las fechas, cupos y estado
                                            </p>
                                        </div>
                                    </div>

                                    <div class="space-y-6">
                                        <!-- Fecha de Salida -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <InputLabel for="fecha_salida" value="Fecha de Salida *" />
                                                <div class="relative mt-1">
                                                    <CalendarDaysIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                                                    <input
                                                        id="fecha_salida"
                                                        type="date"
                                                        class="pl-10 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                        v-model="form.fecha_salida"
                                                        required
                                                    />
                                                </div>
                                                <InputError class="mt-2" :message="form.errors.fecha_salida" />
                                            </div>

                                            <div>
                                                <InputLabel for="fecha_retorno" value="Fecha de Retorno (calculada)" />
                                                <div class="relative mt-1">
                                                    <CalendarDaysIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                                                    <input
                                                        id="fecha_retorno"
                                                        type="date"
                                                        class="pl-10 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-500 rounded-md shadow-sm bg-gray-50 dark:bg-gray-800 cursor-not-allowed"
                                                        :value="fechaRetornoCalculada"
                                                        disabled
                                                    />
                                                </div>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    Se calcula automáticamente según la duración del plan
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Cupos y Estado -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <!-- Cupos Totales -->
                                            <div>
                                                <InputLabel for="cupos_totales" value="Cupos Totales *" />
                                                <div class="relative mt-1">
                                                    <UsersIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                                                    <input
                                                        id="cupos_totales"
                                                        type="number"
                                                        class="pl-10 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                        v-model.number="form.cupos_totales"
                                                        required
                                                        :min="cuposVendidos"
                                                        max="100"
                                                    />
                                                </div>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                    Mínimo: {{ cuposVendidos }} (cupos ya vendidos)
                                                </p>
                                                <InputError class="mt-2" :message="form.errors.cupos_totales" />
                                            </div>

                                            <!-- Estado -->
                                            <div>
                                                <InputLabel for="estado_viaje" value="Estado del Viaje *" />
                                                <select
                                                    id="estado_viaje"
                                                    v-model="form.estado_viaje"
                                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                    required
                                                >
                                                    <option
                                                        v-for="estado in estados"
                                                        :key="estado.value"
                                                        :value="estado.value"
                                                    >
                                                        {{ estado.label }}
                                                    </option>
                                                </select>
                                                <InputError class="mt-2" :message="form.errors.estado_viaje" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Lateral - Preview -->
                        <div class="lg:col-span-1">
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                                <div class="p-6">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                        Resumen del Viaje
                                    </h3>

                                    <!-- Card Preview -->
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                        <div class="h-32 bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center relative">
                                            <CalendarDaysIcon class="h-16 w-16 text-white/50" />
                                            <span
                                                :class="[
                                                    'absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-medium',
                                                    getEstadoClasses(form.estado_viaje)
                                                ]"
                                            >
                                                {{ estados.find(e => e.value === form.estado_viaje)?.label }}
                                            </span>
                                        </div>
                                        <div class="p-4">
                                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">
                                                {{ planSeleccionado?.nombre || "Seleccione un plan" }}
                                            </h4>
                                            <p
                                                v-if="planSeleccionado"
                                                class="text-sm text-indigo-600 dark:text-indigo-400 mt-1"
                                            >
                                                {{ planSeleccionado.destino?.nombre_lugar }}, {{ planSeleccionado.destino?.ciudad }}
                                            </p>

                                            <div class="mt-4 space-y-2">
                                                <div class="flex items-center justify-between text-sm">
                                                    <span class="text-gray-500 dark:text-gray-400 flex items-center">
                                                        <CalendarDaysIcon class="h-4 w-4 mr-1" />
                                                        Salida:
                                                    </span>
                                                    <span class="font-medium text-gray-900 dark:text-gray-100">
                                                        {{ form.fecha_salida ? formatDate(form.fecha_salida) : "-" }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center justify-between text-sm">
                                                    <span class="text-gray-500 dark:text-gray-400 flex items-center">
                                                        <CalendarDaysIcon class="h-4 w-4 mr-1" />
                                                        Retorno:
                                                    </span>
                                                    <span class="font-medium text-gray-900 dark:text-gray-100">
                                                        {{ fechaRetornoCalculada ? formatDate(fechaRetornoCalculada) : "-" }}
                                                    </span>
                                                </div>
                                                <div class="flex items-center justify-between text-sm">
                                                    <span class="text-gray-500 dark:text-gray-400 flex items-center">
                                                        <UsersIcon class="h-4 w-4 mr-1" />
                                                        Cupos:
                                                    </span>
                                                    <span class="font-medium text-gray-900 dark:text-gray-100">
                                                        {{ cuposVendidos }} / {{ form.cupos_totales }} vendidos
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Barra de progreso de ocupación -->
                                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                                <div class="flex items-center justify-between text-sm mb-2">
                                                    <span class="text-gray-500 dark:text-gray-400">Ocupación</span>
                                                    <span class="font-medium text-gray-900 dark:text-gray-100">
                                                        {{ Math.round((cuposVendidos / form.cupos_totales) * 100) }}%
                                                    </span>
                                                </div>
                                                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                                    <div
                                                        class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                                                        :style="{ width: (cuposVendidos / form.cupos_totales) * 100 + '%' }"
                                                    ></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botones de Acción -->
                                    <div class="mt-6 space-y-3">
                                        <PrimaryButton
                                            class="w-full justify-center"
                                            :class="{ 'opacity-25': form.processing }"
                                            :disabled="form.processing || !puedeReducirCupos"
                                        >
                                            <span v-if="form.processing">Guardando...</span>
                                            <span v-else>Guardar Cambios</span>
                                        </PrimaryButton>

                                        <Link
                                            :href="route('viajes.show', viaje.id)"
                                            class="block w-full text-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                                        >
                                            Ver Detalles
                                        </Link>

                                        <Link
                                            :href="route('viajes.index')"
                                            class="block w-full text-center px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                                        >
                                            Cancelar
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
