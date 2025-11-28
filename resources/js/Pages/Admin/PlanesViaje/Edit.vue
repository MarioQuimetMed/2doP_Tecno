<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed, watch, onMounted } from "vue";
import {
    MapIcon,
    ArrowLeftIcon,
    PlusIcon,
    TrashIcon,
    CalendarDaysIcon,
    ClockIcon,
    CurrencyDollarIcon,
    DocumentTextIcon,
    InformationCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    planViaje: Object,
    destinos: Array,
});

// Preparar actividades existentes
const actividadesIniciales =
    props.planViaje.actividades_diarias?.map((a) => ({
        dia_numero: a.dia_numero,
        descripcion_actividad: a.descripcion_actividad,
        hora_inicio: a.hora_inicio ? a.hora_inicio.substring(0, 5) : "09:00",
    })) || [];

const form = useForm({
    destino_id: props.planViaje.destino_id,
    nombre: props.planViaje.nombre,
    descripcion: props.planViaje.descripcion || "",
    duracion_dias: props.planViaje.duracion_dias,
    precio_base: props.planViaje.precio_base,
    actividades: actividadesIniciales,
});

// Manejar cambio de duración
watch(
    () => form.duracion_dias,
    (newDuracion, oldDuracion) => {
        if (newDuracion > oldDuracion) {
            // Agregar días nuevos sin actividades
            for (let i = oldDuracion + 1; i <= newDuracion; i++) {
                // Solo agregar si no hay actividades para ese día
                const actividadesDelDia = form.actividades.filter(
                    (a) => a.dia_numero === i
                );
                if (actividadesDelDia.length === 0) {
                    form.actividades.push({
                        dia_numero: i,
                        descripcion_actividad: "",
                        hora_inicio: "09:00",
                    });
                }
            }
        } else if (newDuracion < oldDuracion) {
            // Remover actividades de días excedentes
            form.actividades = form.actividades.filter(
                (a) => a.dia_numero <= newDuracion
            );
        }
    }
);

// Agregar actividad a un día específico
const agregarActividad = (diaNumero) => {
    form.actividades.push({
        dia_numero: diaNumero,
        descripcion_actividad: "",
        hora_inicio: "09:00",
    });
};

// Eliminar actividad específica
const eliminarActividad = (index) => {
    form.actividades.splice(index, 1);
};

// Actividades agrupadas por día
const actividadesPorDia = computed(() => {
    const grouped = {};
    for (let i = 1; i <= form.duracion_dias; i++) {
        grouped[i] = form.actividades
            .map((a, index) => ({ ...a, originalIndex: index }))
            .filter((a) => a.dia_numero === i)
            .sort((a, b) =>
                (a.hora_inicio || "").localeCompare(b.hora_inicio || "")
            );
    }
    return grouped;
});

// Total de actividades
const totalActividades = computed(() => {
    return form.actividades.filter((a) => a.descripcion_actividad.trim())
        .length;
});

// Destino seleccionado
const destinoSeleccionado = computed(() => {
    return props.destinos.find((d) => d.id == form.destino_id);
});

const submit = () => {
    form.put(route('planes-viaje.update', props.planViaje.id));
};

const formatCurrency = (value) => {
    if (!value) return "";
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
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

<template>
    <Head :title="`Editar Plan: ${planViaje.nombre}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <MapIcon class="h-6 w-6 mr-2 text-indigo-600" />
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Editar Plan de Viaje
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <div class="mb-4">
                    <Link
                        :href="'/planes-viaje'"
                        class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400"
                    >
                        <ArrowLeftIcon class="h-4 w-4 mr-1" />
                        Volver a la lista de planes
                    </Link>
                </div>

                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Columna Principal - Información del Plan -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Información Básica -->
                            <div
                                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                            >
                                <div class="p-6">
                                    <div
                                        class="flex items-center mb-6 pb-4 border-b border-gray-200 dark:border-gray-700"
                                    >
                                        <div
                                            class="flex-shrink-0 h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center"
                                        >
                                            <DocumentTextIcon
                                                class="h-6 w-6 text-white"
                                            />
                                        </div>
                                        <div class="ml-4">
                                            <h3
                                                class="text-lg font-medium text-gray-900 dark:text-gray-100"
                                            >
                                                Información del Plan
                                            </h3>
                                            <p
                                                class="text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                Modifique los datos del plan de
                                                viaje
                                            </p>
                                        </div>
                                    </div>

                                    <div class="space-y-6">
                                        <!-- Destino -->
                                        <div>
                                            <InputLabel
                                                for="destino_id"
                                                value="Destino *"
                                            />
                                            <select
                                                id="destino_id"
                                                v-model="form.destino_id"
                                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                required
                                            >
                                                <option value="">
                                                    Seleccione un destino
                                                </option>
                                                <option
                                                    v-for="destino in destinos"
                                                    :key="destino.id"
                                                    :value="destino.id"
                                                >
                                                    {{ destino.nombre_lugar }} -
                                                    {{ destino.ciudad }},
                                                    {{ destino.pais }}
                                                </option>
                                            </select>
                                            <InputError
                                                class="mt-2"
                                                :message="
                                                    form.errors.destino_id
                                                "
                                            />
                                        </div>

                                        <!-- Nombre del Plan -->
                                        <div>
                                            <InputLabel
                                                for="nombre"
                                                value="Nombre del Plan *"
                                            />
                                            <TextInput
                                                id="nombre"
                                                type="text"
                                                class="mt-1 block w-full"
                                                v-model="form.nombre"
                                                required
                                                placeholder="Ej: Aventura en el Salar de Uyuni"
                                            />
                                            <InputError
                                                class="mt-2"
                                                :message="form.errors.nombre"
                                            />
                                        </div>

                                        <!-- Duración y Precio -->
                                        <div
                                            class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                        >
                                            <div>
                                                <InputLabel
                                                    for="duracion_dias"
                                                    value="Duración (días) *"
                                                />
                                                <div class="relative mt-1">
                                                    <CalendarDaysIcon
                                                        class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
                                                    />
                                                    <input
                                                        id="duracion_dias"
                                                        type="number"
                                                        class="pl-10 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                        v-model.number="
                                                            form.duracion_dias
                                                        "
                                                        required
                                                        min="1"
                                                        max="30"
                                                    />
                                                </div>
                                                <p
                                                    class="mt-1 text-xs text-amber-600 dark:text-amber-400"
                                                >
                                                    <InformationCircleIcon
                                                        class="inline h-4 w-4 mr-1"
                                                    />
                                                    Cambiar la duración puede
                                                    afectar las actividades
                                                </p>
                                                <InputError
                                                    class="mt-2"
                                                    :message="
                                                        form.errors
                                                            .duracion_dias
                                                    "
                                                />
                                            </div>

                                            <div>
                                                <InputLabel
                                                    for="precio_base"
                                                    value="Precio Base (USD) *"
                                                />
                                                <div class="relative mt-1">
                                                    <CurrencyDollarIcon
                                                        class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400"
                                                    />
                                                    <input
                                                        id="precio_base"
                                                        type="number"
                                                        step="0.01"
                                                        class="pl-10 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                        v-model="
                                                            form.precio_base
                                                        "
                                                        required
                                                        min="0"
                                                        placeholder="0.00"
                                                    />
                                                </div>
                                                <InputError
                                                    class="mt-2"
                                                    :message="
                                                        form.errors.precio_base
                                                    "
                                                />
                                            </div>
                                        </div>

                                        <!-- Descripción -->
                                        <div>
                                            <InputLabel
                                                for="descripcion"
                                                value="Descripción"
                                            />
                                            <textarea
                                                id="descripcion"
                                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                                v-model="form.descripcion"
                                                rows="3"
                                                placeholder="Describe el plan de viaje, qué incluye, puntos destacados..."
                                            ></textarea>
                                            <InputError
                                                class="mt-2"
                                                :message="
                                                    form.errors.descripcion
                                                "
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Itinerario por Días -->
                            <div
                                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                            >
                                <div class="p-6">
                                    <div
                                        class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-700"
                                    >
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-12 w-12 rounded-full bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center"
                                            >
                                                <CalendarDaysIcon
                                                    class="h-6 w-6 text-white"
                                                />
                                            </div>
                                            <div class="ml-4">
                                                <h3
                                                    class="text-lg font-medium text-gray-900 dark:text-gray-100"
                                                >
                                                    Itinerario de Actividades
                                                </h3>
                                                <p
                                                    class="text-sm text-gray-500 dark:text-gray-400"
                                                >
                                                    {{ totalActividades }}
                                                    actividades en
                                                    {{ form.duracion_dias }}
                                                    {{
                                                        form.duracion_dias === 1
                                                            ? "día"
                                                            : "días"
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Días -->
                                    <div class="space-y-6">
                                        <div
                                            v-for="dia in form.duracion_dias"
                                            :key="dia"
                                            class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden"
                                        >
                                            <!-- Header del Día -->
                                            <div
                                                class="bg-gray-50 dark:bg-gray-700 px-4 py-3 flex items-center justify-between"
                                            >
                                                <div class="flex items-center">
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
                                                    <span
                                                        class="ml-2 text-sm text-gray-500 dark:text-gray-400"
                                                    >
                                                        ({{
                                                            actividadesPorDia[
                                                                dia
                                                            ]?.length || 0
                                                        }}
                                                        actividades)
                                                    </span>
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="
                                                        agregarActividad(dia)
                                                    "
                                                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-indigo-700 dark:text-indigo-300 bg-indigo-100 dark:bg-indigo-900 hover:bg-indigo-200 dark:hover:bg-indigo-800"
                                                >
                                                    <PlusIcon
                                                        class="h-4 w-4 mr-1"
                                                    />
                                                    Agregar
                                                </button>
                                            </div>

                                            <!-- Actividades del Día -->
                                            <div class="p-4 space-y-3">
                                                <div
                                                    v-for="actividad in actividadesPorDia[
                                                        dia
                                                    ]"
                                                    :key="
                                                        actividad.originalIndex
                                                    "
                                                    class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg"
                                                >
                                                    <!-- Hora -->
                                                    <div
                                                        class="flex-shrink-0 w-24"
                                                    >
                                                        <div class="relative">
                                                            <ClockIcon
                                                                class="absolute left-2 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
                                                            />
                                                            <input
                                                                type="time"
                                                                v-model="
                                                                    form
                                                                        .actividades[
                                                                        actividad
                                                                            .originalIndex
                                                                    ]
                                                                        .hora_inicio
                                                                "
                                                                class="pl-8 block w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                            />
                                                        </div>
                                                    </div>

                                                    <!-- Descripción -->
                                                    <div class="flex-1">
                                                        <input
                                                            type="text"
                                                            v-model="
                                                                form
                                                                    .actividades[
                                                                    actividad
                                                                        .originalIndex
                                                                ]
                                                                    .descripcion_actividad
                                                            "
                                                            placeholder="Describe la actividad..."
                                                            class="block w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        />
                                                    </div>

                                                    <!-- Botón Eliminar -->
                                                    <button
                                                        type="button"
                                                        @click="
                                                            eliminarActividad(
                                                                actividad.originalIndex
                                                            )
                                                        "
                                                        class="flex-shrink-0 p-2 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/50 rounded-md"
                                                    >
                                                        <TrashIcon
                                                            class="h-4 w-4"
                                                        />
                                                    </button>
                                                </div>

                                                <div
                                                    v-if="
                                                        !actividadesPorDia[dia]
                                                            ?.length
                                                    "
                                                    class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm"
                                                >
                                                    No hay actividades para este
                                                    día
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Errores de actividades -->
                                    <InputError
                                        class="mt-4"
                                        :message="form.errors.actividades"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Columna Lateral - Preview e Info -->
                        <div class="lg:col-span-1 space-y-6">
                            <!-- Vista Previa -->
                            <div
                                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg sticky top-6"
                            >
                                <div class="p-6">
                                    <h3
                                        class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4"
                                    >
                                        Vista Previa
                                    </h3>

                                    <!-- Card Preview -->
                                    <div
                                        class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden"
                                    >
                                        <div
                                            class="h-32 bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center"
                                        >
                                            <MapIcon
                                                class="h-16 w-16 text-white/50"
                                            />
                                        </div>
                                        <div class="p-4">
                                            <h4
                                                class="font-semibold text-gray-900 dark:text-gray-100"
                                            >
                                                {{
                                                    form.nombre ||
                                                    "Nombre del plan"
                                                }}
                                            </h4>
                                            <p
                                                v-if="destinoSeleccionado"
                                                class="text-sm text-indigo-600 dark:text-indigo-400 mt-1"
                                            >
                                                {{
                                                    destinoSeleccionado.nombre_lugar
                                                }},
                                                {{ destinoSeleccionado.ciudad }}
                                            </p>

                                            <div
                                                class="mt-4 flex items-center justify-between"
                                            >
                                                <div
                                                    class="flex items-center text-sm text-gray-500 dark:text-gray-400"
                                                >
                                                    <CalendarDaysIcon
                                                        class="h-4 w-4 mr-1"
                                                    />
                                                    {{ form.duracion_dias }}
                                                    {{
                                                        form.duracion_dias === 1
                                                            ? "día"
                                                            : "días"
                                                    }}
                                                </div>
                                                <div
                                                    class="text-lg font-bold text-indigo-600 dark:text-indigo-400"
                                                >
                                                    {{
                                                        form.precio_base
                                                            ? formatCurrency(
                                                                  form.precio_base
                                                              )
                                                            : "$0.00"
                                                    }}
                                                </div>
                                            </div>

                                            <div
                                                class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700"
                                            >
                                                <div
                                                    class="flex items-center justify-between text-sm"
                                                >
                                                    <span
                                                        class="text-gray-500 dark:text-gray-400"
                                                        >Actividades:</span
                                                    >
                                                    <span
                                                        class="font-medium text-gray-900 dark:text-gray-100"
                                                    >
                                                        {{ totalActividades }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Info de Auditoría -->
                                    <div
                                        class="mt-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg"
                                    >
                                        <h4
                                            class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2"
                                        >
                                            Información de Registro
                                        </h4>
                                        <dl
                                            class="space-y-1 text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            <div class="flex justify-between">
                                                <dt>Creado:</dt>
                                                <dd>
                                                    {{
                                                        formatDate(
                                                            planViaje.created_at
                                                        )
                                                    }}
                                                </dd>
                                            </div>
                                            <div class="flex justify-between">
                                                <dt>Actualizado:</dt>
                                                <dd>
                                                    {{
                                                        formatDate(
                                                            planViaje.updated_at
                                                        )
                                                    }}
                                                </dd>
                                            </div>
                                            <div class="flex justify-between">
                                                <dt>ID:</dt>
                                                <dd>#{{ planViaje.id }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Botones de Acción -->
                                    <div class="mt-6 space-y-3">
                                        <PrimaryButton
                                            class="w-full justify-center"
                                            :class="{
                                                'opacity-25': form.processing,
                                            }"
                                            :disabled="form.processing"
                                        >
                                            <span v-if="form.processing"
                                                >Guardando...</span
                                            >
                                            <span v-else>Guardar Cambios</span>
                                        </PrimaryButton>

                                        <Link
                                            :href="
                                                '/planes-viaje/' + planViaje.id
                                            "
                                            class="block w-full text-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                                        >
                                            Ver Detalles
                                        </Link>

                                        <Link
                                            :href="'/planes-viaje'"
                                            class="block w-full text-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
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
