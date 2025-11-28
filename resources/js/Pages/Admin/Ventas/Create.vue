<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import {
    ShoppingCartIcon,
    ArrowLeftIcon,
    ArrowRightIcon,
    CheckIcon,
    UserIcon,
    UserPlusIcon,
    CalendarDaysIcon,
    CurrencyDollarIcon,
    CreditCardIcon,
    BanknotesIcon,
    MapPinIcon,
    ClockIcon,
    TicketIcon,
    UsersIcon,
    InformationCircleIcon,
    XCircleIcon,
} from "@heroicons/vue/24/outline";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    viajes: Array,
    clientes: Array,
    tiposPago: Array,
    metodosPago: Array,
    opcionesCuotas: Array,
    viajePreseleccionado: Object,
});

// Paso actual del wizard
const currentStep = ref(1);
const totalSteps = 4;

// Formulario
const form = useForm({
    // Paso 1: Viaje
    viaje_id: props.viajePreseleccionado?.id || null,
    cantidad_personas: 1,

    // Paso 2: Cliente
    cliente_id: null,
    crear_cliente: false,
    nuevo_cliente: {
        nombre: "",
        email: "",
        telefono: "",
        ci_nit: "",
    },

    // Paso 3: Tipo de pago
    tipo_pago: "CONTADO",
    cantidad_cuotas: 3,
    dia_vencimiento: 15,

    // Paso 4: Pago inicial
    realizar_pago_inicial: true,
    pago_inicial: {
        monto: 0,
        metodo: "EFECTIVO",
        referencia: "",
    },
});

// Viaje seleccionado
const viajeSeleccionado = computed(() => {
    return props.viajes.find((v) => v.id === form.viaje_id);
});

// Cliente seleccionado
const clienteSeleccionado = computed(() => {
    return props.clientes.find((c) => c.id === form.cliente_id);
});

// Monto total calculado
const montoTotal = computed(() => {
    if (!viajeSeleccionado.value) return 0;
    return viajeSeleccionado.value.precio * form.cantidad_personas;
});

// Monto total con interés si es crédito
const montoTotalConInteres = computed(() => {
    if (form.tipo_pago !== "CREDITO") return montoTotal.value;
    const opcion = props.opcionesCuotas.find(
        (o) => o.cantidad === form.cantidad_cuotas
    );
    if (!opcion) return montoTotal.value;
    return montoTotal.value * (1 + opcion.interes / 100);
});

// Monto de cada cuota
const montoCuota = computed(() => {
    if (form.tipo_pago !== "CREDITO") return 0;
    return montoTotalConInteres.value / form.cantidad_cuotas;
});

// Opción de cuota seleccionada
const opcionCuotaSeleccionada = computed(() => {
    return props.opcionesCuotas.find(
        (o) => o.cantidad === form.cantidad_cuotas
    );
});

// Método de pago requiere referencia
const metodoRequiereReferencia = computed(() => {
    const metodo = props.metodosPago.find(
        (m) => m.value === form.pago_inicial.metodo
    );
    return metodo?.requiereReferencia || false;
});

// Actualizar monto del pago inicial cuando cambia el tipo de pago
watch(
    () => form.tipo_pago,
    (newVal) => {
        if (newVal === "CONTADO") {
            form.pago_inicial.monto = montoTotal.value;
            form.realizar_pago_inicial = true;
        } else {
            form.pago_inicial.monto = 0;
            form.realizar_pago_inicial = false;
        }
    }
);

watch(montoTotal, (newVal) => {
    if (form.tipo_pago === "CONTADO") {
        form.pago_inicial.monto = newVal;
    }
});

// Validación por paso
const canProceed = computed(() => {
    switch (currentStep.value) {
        case 1:
            return (
                form.viaje_id &&
                form.cantidad_personas > 0 &&
                form.cantidad_personas <=
                    (viajeSeleccionado.value?.cupos_disponibles || 0)
            );
        case 2:
            if (form.crear_cliente) {
                return (
                    form.nuevo_cliente.nombre &&
                    form.nuevo_cliente.email &&
                    form.nuevo_cliente.ci_nit
                );
            }
            return form.cliente_id !== null;
        case 3:
            if (form.tipo_pago === "CREDITO") {
                return form.cantidad_cuotas > 0;
            }
            return true;
        case 4:
            if (form.realizar_pago_inicial && form.tipo_pago === "CONTADO") {
                return (
                    form.pago_inicial.monto >= montoTotal.value &&
                    form.pago_inicial.metodo
                );
            }
            if (form.realizar_pago_inicial && form.tipo_pago === "CREDITO") {
                return form.pago_inicial.monto >= 0 && form.pago_inicial.metodo;
            }
            return true;
        default:
            return true;
    }
});

const nextStep = () => {
    if (currentStep.value < totalSteps && canProceed.value) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const selectViaje = (viaje) => {
    form.viaje_id = viaje.id;
    // Ajustar cantidad si supera los cupos disponibles
    if (form.cantidad_personas > viaje.cupos_disponibles) {
        form.cantidad_personas = viaje.cupos_disponibles;
    }
};

const selectCliente = (cliente) => {
    form.cliente_id = cliente.id;
    form.crear_cliente = false;
};

const toggleCrearCliente = () => {
    form.crear_cliente = !form.crear_cliente;
    if (form.crear_cliente) {
        form.cliente_id = null;
    }
};

const submit = () => {
    const data = {
        viaje_id: form.viaje_id,
        cantidad_personas: form.cantidad_personas,
        tipo_pago: form.tipo_pago,
    };

    // Cliente
    if (form.crear_cliente) {
        data.nuevo_cliente = form.nuevo_cliente;
    } else {
        data.cliente_id = form.cliente_id;
    }

    // Crédito
    if (form.tipo_pago === "CREDITO") {
        data.cantidad_cuotas = form.cantidad_cuotas;
        data.dia_vencimiento = form.dia_vencimiento;
    }

    // Pago inicial
    if (form.realizar_pago_inicial && form.pago_inicial.monto > 0) {
        data.pago_inicial = form.pago_inicial;
    }

    form.transform(() => data).post("/ventas", {
        preserveScroll: true,
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value);
};
</script>

<template>
    <Head title="Nueva Venta" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link
                    :href="'/ventas'"
                    class="mr-4 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-500" />
                </Link>
                <ShoppingCartIcon class="h-6 w-6 mr-2 text-indigo-600" />
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Nueva Venta
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Progress Steps -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <template v-for="step in totalSteps" :key="step">
                            <div class="flex items-center">
                                <div
                                    :class="[
                                        'flex items-center justify-center w-10 h-10 rounded-full border-2 transition-all duration-300',
                                        currentStep >= step
                                            ? 'bg-indigo-600 border-indigo-600 text-white'
                                            : 'border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500',
                                    ]"
                                >
                                    <CheckIcon
                                        v-if="currentStep > step"
                                        class="h-5 w-5"
                                    />
                                    <span v-else>{{ step }}</span>
                                </div>
                                <span
                                    :class="[
                                        'ml-2 text-sm font-medium hidden sm:inline',
                                        currentStep >= step
                                            ? 'text-indigo-600 dark:text-indigo-400'
                                            : 'text-gray-400 dark:text-gray-500',
                                    ]"
                                >
                                    {{
                                        [
                                            "Viaje",
                                            "Cliente",
                                            "Pago",
                                            "Confirmar",
                                        ][step - 1]
                                    }}
                                </span>
                            </div>
                            <div
                                v-if="step < totalSteps"
                                :class="[
                                    'flex-1 h-0.5 mx-4 transition-all duration-300',
                                    currentStep > step
                                        ? 'bg-indigo-600'
                                        : 'bg-gray-300 dark:bg-gray-600',
                                ]"
                            ></div>
                        </template>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="p-6">
                        <!-- Error General -->
                        <div
                            v-if="form.errors.error"
                            class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded-r-lg"
                        >
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <XCircleIcon
                                        class="h-5 w-5 text-red-400"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div class="ml-3">
                                    <p
                                        class="text-sm text-red-700 dark:text-red-200"
                                    >
                                        {{ form.errors.error }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 1: Selección de Viaje -->
                        <div v-show="currentStep === 1">
                            <h3
                                class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center"
                            >
                                <CalendarDaysIcon
                                    class="h-5 w-5 mr-2 text-indigo-600"
                                />
                                Seleccionar Viaje
                            </h3>

                            <div
                                class="space-y-4 max-h-96 overflow-y-auto pr-2"
                            >
                                <div
                                    v-for="viaje in viajes"
                                    :key="viaje.id"
                                    @click="selectViaje(viaje)"
                                    :class="[
                                        'p-4 border-2 rounded-lg cursor-pointer transition-all duration-200',
                                        form.viaje_id === viaje.id
                                            ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/20'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700',
                                    ]"
                                >
                                    <div
                                        class="flex justify-between items-start"
                                    >
                                        <div class="flex-1">
                                            <h4
                                                class="font-semibold text-gray-900 dark:text-gray-100"
                                            >
                                                {{ viaje.nombre }}
                                            </h4>
                                            <p
                                                class="text-sm text-gray-600 dark:text-gray-400 flex items-center mt-1"
                                            >
                                                <MapPinIcon
                                                    class="h-4 w-4 mr-1"
                                                />
                                                {{ viaje.destino }}
                                            </p>
                                            <div
                                                class="flex items-center space-x-4 mt-2 text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                <span class="flex items-center">
                                                    <CalendarDaysIcon
                                                        class="h-4 w-4 mr-1"
                                                    />
                                                    {{
                                                        viaje.fecha_salida_formato
                                                    }}
                                                </span>
                                                <span class="flex items-center">
                                                    <ClockIcon
                                                        class="h-4 w-4 mr-1"
                                                    />
                                                    {{ viaje.duracion_dias }}
                                                    días
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p
                                                class="text-lg font-bold text-indigo-600 dark:text-indigo-400"
                                            >
                                                {{ viaje.precio_formateado }}
                                            </p>
                                            <p
                                                class="text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                por persona
                                            </p>
                                            <div class="mt-2">
                                                <span
                                                    :class="[
                                                        'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                                                        viaje.cupos_disponibles >
                                                        5
                                                            ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200'
                                                            : viaje.cupos_disponibles >
                                                              0
                                                            ? 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200'
                                                            : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
                                                    ]"
                                                >
                                                    <TicketIcon
                                                        class="h-3 w-3 mr-1"
                                                    />
                                                    {{
                                                        viaje.cupos_disponibles
                                                    }}
                                                    cupos
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-if="viajes.length === 0"
                                    class="text-center py-8 text-gray-500 dark:text-gray-400"
                                >
                                    <CalendarDaysIcon
                                        class="h-12 w-12 mx-auto mb-4 text-gray-300 dark:text-gray-600"
                                    />
                                    <p>
                                        No hay viajes disponibles en este
                                        momento
                                    </p>
                                </div>
                            </div>

                            <!-- Cantidad de personas -->
                            <div
                                v-if="viajeSeleccionado"
                                class="mt-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <InputLabel
                                            value="Cantidad de Personas"
                                        />
                                        <p
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            Máximo
                                            {{
                                                viajeSeleccionado.cupos_disponibles
                                            }}
                                            disponibles
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <button
                                            @click="
                                                form.cantidad_personas =
                                                    Math.max(
                                                        1,
                                                        form.cantidad_personas -
                                                            1
                                                    )
                                            "
                                            class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center hover:bg-gray-300 dark:hover:bg-gray-500"
                                        >
                                            -
                                        </button>
                                        <span
                                            class="text-xl font-semibold text-gray-900 dark:text-gray-100 w-8 text-center"
                                        >
                                            {{ form.cantidad_personas }}
                                        </span>
                                        <button
                                            @click="
                                                form.cantidad_personas =
                                                    Math.min(
                                                        viajeSeleccionado.cupos_disponibles,
                                                        form.cantidad_personas +
                                                            1
                                                    )
                                            "
                                            class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center hover:bg-gray-300 dark:hover:bg-gray-500"
                                            :disabled="
                                                form.cantidad_personas >=
                                                viajeSeleccionado.cupos_disponibles
                                            "
                                        >
                                            +
                                        </button>
                                    </div>
                                </div>
                                <div
                                    class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600 flex justify-between items-center"
                                >
                                    <span
                                        class="text-gray-600 dark:text-gray-400"
                                        >Subtotal:</span
                                    >
                                    <span
                                        class="text-2xl font-bold text-indigo-600 dark:text-indigo-400"
                                    >
                                        {{ formatCurrency(montoTotal) }}
                                    </span>
                                </div>
                            </div>
                            <InputError
                                :message="form.errors.viaje_id"
                                class="mt-2"
                            />
                            <InputError
                                :message="form.errors.cantidad_personas"
                                class="mt-2"
                            />
                        </div>

                        <!-- Paso 2: Selección de Cliente -->
                        <div v-show="currentStep === 2">
                            <h3
                                class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center"
                            >
                                <UserIcon
                                    class="h-5 w-5 mr-2 text-indigo-600"
                                />
                                Seleccionar o Crear Cliente
                            </h3>

                            <!-- Toggle crear nuevo -->
                            <div class="mb-4">
                                <button
                                    @click="toggleCrearCliente"
                                    :class="[
                                        'inline-flex items-center px-4 py-2 rounded-lg border-2 transition-all duration-200',
                                        form.crear_cliente
                                            ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300'
                                            : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-indigo-300',
                                    ]"
                                >
                                    <UserPlusIcon class="h-5 w-5 mr-2" />
                                    {{
                                        form.crear_cliente
                                            ? "Creando nuevo cliente"
                                            : "Crear nuevo cliente"
                                    }}
                                </button>
                            </div>

                            <!-- Formulario nuevo cliente -->
                            <div
                                v-if="form.crear_cliente"
                                class="space-y-4 p-4 border-2 border-indigo-200 dark:border-indigo-700 rounded-lg bg-indigo-50 dark:bg-indigo-900/20"
                            >
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                >
                                    <div>
                                        <InputLabel
                                            for="nombre"
                                            value="Nombre Completo *"
                                        />
                                        <TextInput
                                            id="nombre"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.nuevo_cliente.nombre"
                                            placeholder="Ingrese nombre completo"
                                        />
                                        <InputError
                                            :message="
                                                form.errors[
                                                    'nuevo_cliente.nombre'
                                                ]
                                            "
                                            class="mt-2"
                                        />
                                    </div>
                                    <div>
                                        <InputLabel
                                            for="email"
                                            value="Email *"
                                        />
                                        <TextInput
                                            id="email"
                                            type="email"
                                            class="mt-1 block w-full"
                                            v-model="form.nuevo_cliente.email"
                                            placeholder="correo@ejemplo.com"
                                        />
                                        <InputError
                                            :message="
                                                form.errors[
                                                    'nuevo_cliente.email'
                                                ]
                                            "
                                            class="mt-2"
                                        />
                                    </div>
                                    <div>
                                        <InputLabel
                                            for="telefono"
                                            value="Teléfono"
                                        />
                                        <TextInput
                                            id="telefono"
                                            type="tel"
                                            class="mt-1 block w-full"
                                            v-model="
                                                form.nuevo_cliente.telefono
                                            "
                                            placeholder="Ej: 70000000"
                                        />
                                        <InputError
                                            :message="
                                                form.errors[
                                                    'nuevo_cliente.telefono'
                                                ]
                                            "
                                            class="mt-2"
                                        />
                                    </div>
                                    <div>
                                        <InputLabel
                                            for="ci_nit"
                                            value="CI / NIT *"
                                        />
                                        <TextInput
                                            id="ci_nit"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.nuevo_cliente.ci_nit"
                                            placeholder="Documento de identidad"
                                        />
                                        <InputError
                                            :message="
                                                form.errors[
                                                    'nuevo_cliente.ci_nit'
                                                ]
                                            "
                                            class="mt-2"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Lista de clientes existentes -->
                            <div
                                v-else
                                class="space-y-2 max-h-80 overflow-y-auto pr-2"
                            >
                                <div
                                    v-for="cliente in clientes"
                                    :key="cliente.id"
                                    @click="selectCliente(cliente)"
                                    :class="[
                                        'p-3 border-2 rounded-lg cursor-pointer transition-all duration-200 flex items-center',
                                        form.cliente_id === cliente.id
                                            ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/20'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700',
                                    ]"
                                >
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center"
                                    >
                                        <UserIcon class="h-5 w-5 text-white" />
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{ cliente.name }}
                                        </p>
                                        <p
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            {{ cliente.email }}
                                        </p>
                                    </div>
                                    <div
                                        class="text-right text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        <p v-if="cliente.ci_nit">
                                            CI: {{ cliente.ci_nit }}
                                        </p>
                                        <p v-if="cliente.telefono">
                                            Tel: {{ cliente.telefono }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="clientes.length === 0"
                                    class="text-center py-8 text-gray-500 dark:text-gray-400"
                                >
                                    <UserIcon
                                        class="h-12 w-12 mx-auto mb-4 text-gray-300 dark:text-gray-600"
                                    />
                                    <p>No hay clientes registrados</p>
                                    <button
                                        @click="toggleCrearCliente"
                                        class="mt-2 text-indigo-600 dark:text-indigo-400 hover:underline"
                                    >
                                        Crear nuevo cliente
                                    </button>
                                </div>
                            </div>
                            <InputError
                                :message="form.errors.cliente_id"
                                class="mt-2"
                            />
                        </div>

                        <!-- Paso 3: Configuración de Pago -->
                        <div v-show="currentStep === 3">
                            <h3
                                class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center"
                            >
                                <CurrencyDollarIcon
                                    class="h-5 w-5 mr-2 text-indigo-600"
                                />
                                Tipo de Pago
                            </h3>

                            <!-- Tipo de pago -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div
                                    @click="form.tipo_pago = 'CONTADO'"
                                    :class="[
                                        'p-4 border-2 rounded-lg cursor-pointer transition-all duration-200 text-center',
                                        form.tipo_pago === 'CONTADO'
                                            ? 'border-emerald-600 bg-emerald-50 dark:bg-emerald-900/20'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-emerald-300',
                                    ]"
                                >
                                    <BanknotesIcon
                                        :class="[
                                            'h-12 w-12 mx-auto mb-2',
                                            form.tipo_pago === 'CONTADO'
                                                ? 'text-emerald-600'
                                                : 'text-gray-400',
                                        ]"
                                    />
                                    <h4
                                        class="font-semibold text-gray-900 dark:text-gray-100"
                                    >
                                        Pago al Contado
                                    </h4>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Pago único completo
                                    </p>
                                </div>
                                <div
                                    @click="form.tipo_pago = 'CREDITO'"
                                    :class="[
                                        'p-4 border-2 rounded-lg cursor-pointer transition-all duration-200 text-center',
                                        form.tipo_pago === 'CREDITO'
                                            ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-blue-300',
                                    ]"
                                >
                                    <CreditCardIcon
                                        :class="[
                                            'h-12 w-12 mx-auto mb-2',
                                            form.tipo_pago === 'CREDITO'
                                                ? 'text-blue-600'
                                                : 'text-gray-400',
                                        ]"
                                    />
                                    <h4
                                        class="font-semibold text-gray-900 dark:text-gray-100"
                                    >
                                        Pago a Crédito
                                    </h4>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Pago en cuotas
                                    </p>
                                </div>
                            </div>

                            <!-- Opciones de crédito -->
                            <div
                                v-if="form.tipo_pago === 'CREDITO'"
                                class="space-y-4"
                            >
                                <div>
                                    <InputLabel
                                        value="Seleccionar Plan de Cuotas"
                                    />
                                    <div class="grid grid-cols-3 gap-3 mt-2">
                                        <div
                                            v-for="opcion in opcionesCuotas"
                                            :key="opcion.cantidad"
                                            @click="
                                                form.cantidad_cuotas =
                                                    opcion.cantidad
                                            "
                                            :class="[
                                                'p-3 border-2 rounded-lg cursor-pointer transition-all duration-200 text-center',
                                                form.cantidad_cuotas ===
                                                opcion.cantidad
                                                    ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20'
                                                    : 'border-gray-200 dark:border-gray-700 hover:border-blue-300',
                                            ]"
                                        >
                                            <p
                                                class="text-lg font-bold text-gray-900 dark:text-gray-100"
                                            >
                                                {{ opcion.cantidad }}
                                            </p>
                                            <p
                                                class="text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                cuotas
                                            </p>
                                            <p
                                                :class="[
                                                    'text-xs mt-1',
                                                    opcion.interes === 0
                                                        ? 'text-green-600'
                                                        : 'text-orange-600',
                                                ]"
                                            >
                                                {{ opcion.interes }}% interés
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <InputLabel
                                        for="dia_vencimiento"
                                        value="Día de Vencimiento Mensual"
                                    />
                                    <select
                                        id="dia_vencimiento"
                                        v-model="form.dia_vencimiento"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                        <option
                                            v-for="dia in 28"
                                            :key="dia"
                                            :value="dia"
                                        >
                                            Día {{ dia }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Resumen del crédito -->
                                <div
                                    class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
                                >
                                    <h4
                                        class="font-semibold text-blue-900 dark:text-blue-100 mb-2 flex items-center"
                                    >
                                        <InformationCircleIcon
                                            class="h-5 w-5 mr-2"
                                        />
                                        Resumen del Crédito
                                    </h4>
                                    <div class="space-y-1 text-sm">
                                        <div class="flex justify-between">
                                            <span
                                                class="text-blue-700 dark:text-blue-300"
                                                >Monto base:</span
                                            >
                                            <span
                                                class="font-medium text-blue-900 dark:text-blue-100"
                                                >{{
                                                    formatCurrency(montoTotal)
                                                }}</span
                                            >
                                        </div>
                                        <div class="flex justify-between">
                                            <span
                                                class="text-blue-700 dark:text-blue-300"
                                                >Interés ({{
                                                    opcionCuotaSeleccionada?.interes ||
                                                    0
                                                }}%):</span
                                            >
                                            <span
                                                class="font-medium text-blue-900 dark:text-blue-100"
                                                >{{
                                                    formatCurrency(
                                                        montoTotalConInteres -
                                                            montoTotal
                                                    )
                                                }}</span
                                            >
                                        </div>
                                        <div
                                            class="flex justify-between pt-2 border-t border-blue-200 dark:border-blue-700"
                                        >
                                            <span
                                                class="text-blue-700 dark:text-blue-300"
                                                >Total a pagar:</span
                                            >
                                            <span
                                                class="font-bold text-blue-900 dark:text-blue-100"
                                                >{{
                                                    formatCurrency(
                                                        montoTotalConInteres
                                                    )
                                                }}</span
                                            >
                                        </div>
                                        <div class="flex justify-between">
                                            <span
                                                class="text-blue-700 dark:text-blue-300"
                                                >Cuota mensual:</span
                                            >
                                            <span
                                                class="font-bold text-blue-900 dark:text-blue-100"
                                                >{{
                                                    formatCurrency(montoCuota)
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Paso 4: Confirmación y Pago Inicial -->
                        <div v-show="currentStep === 4">
                            <h3
                                class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center"
                            >
                                <CheckIcon
                                    class="h-5 w-5 mr-2 text-indigo-600"
                                />
                                Confirmar Venta
                            </h3>

                            <!-- Resumen de la venta -->
                            <div class="space-y-4 mb-6">
                                <!-- Viaje -->
                                <div
                                    class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                                >
                                    <h4
                                        class="font-semibold text-gray-700 dark:text-gray-300 text-sm uppercase mb-2"
                                    >
                                        Viaje
                                    </h4>
                                    <p
                                        class="text-gray-900 dark:text-gray-100 font-medium"
                                    >
                                        {{ viajeSeleccionado?.nombre }}
                                    </p>
                                    <p
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        {{ viajeSeleccionado?.destino }}
                                    </p>
                                    <p
                                        class="text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        {{
                                            viajeSeleccionado?.fecha_salida_formato
                                        }}
                                        -
                                        {{ viajeSeleccionado?.duracion_dias }}
                                        días
                                    </p>
                                    <p
                                        class="text-sm text-gray-600 dark:text-gray-400 flex items-center mt-1"
                                    >
                                        <UsersIcon class="h-4 w-4 mr-1" />
                                        {{ form.cantidad_personas }} persona(s)
                                    </p>
                                </div>

                                <!-- Cliente -->
                                <div
                                    class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                                >
                                    <h4
                                        class="font-semibold text-gray-700 dark:text-gray-300 text-sm uppercase mb-2"
                                    >
                                        Cliente
                                    </h4>
                                    <template v-if="form.crear_cliente">
                                        <p
                                            class="text-gray-900 dark:text-gray-100 font-medium"
                                        >
                                            {{ form.nuevo_cliente.nombre }}
                                        </p>
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            {{ form.nuevo_cliente.email }}
                                        </p>
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            CI/NIT:
                                            {{ form.nuevo_cliente.ci_nit }}
                                        </p>
                                    </template>
                                    <template v-else>
                                        <p
                                            class="text-gray-900 dark:text-gray-100 font-medium"
                                        >
                                            {{ clienteSeleccionado?.name }}
                                        </p>
                                        <p
                                            class="text-sm text-gray-600 dark:text-gray-400"
                                        >
                                            {{ clienteSeleccionado?.email }}
                                        </p>
                                    </template>
                                </div>

                                <!-- Totales -->
                                <div
                                    class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg"
                                >
                                    <h4
                                        class="font-semibold text-indigo-700 dark:text-indigo-300 text-sm uppercase mb-2"
                                    >
                                        Detalle de Pago
                                    </h4>
                                    <div class="space-y-2">
                                        <div
                                            class="flex justify-between text-sm"
                                        >
                                            <span
                                                class="text-gray-600 dark:text-gray-400"
                                                >Tipo de pago:</span
                                            >
                                            <span
                                                class="font-medium text-gray-900 dark:text-gray-100"
                                            >
                                                {{
                                                    form.tipo_pago === "CONTADO"
                                                        ? "Contado"
                                                        : `Crédito (${form.cantidad_cuotas} cuotas)`
                                                }}
                                            </span>
                                        </div>
                                        <div
                                            class="flex justify-between text-sm"
                                        >
                                            <span
                                                class="text-gray-600 dark:text-gray-400"
                                                >Precio unitario:</span
                                            >
                                            <span
                                                class="font-medium text-gray-900 dark:text-gray-100"
                                                >{{
                                                    viajeSeleccionado?.precio_formateado
                                                }}</span
                                            >
                                        </div>
                                        <div
                                            class="flex justify-between text-sm"
                                        >
                                            <span
                                                class="text-gray-600 dark:text-gray-400"
                                                >Personas:</span
                                            >
                                            <span
                                                class="font-medium text-gray-900 dark:text-gray-100"
                                                >{{
                                                    form.cantidad_personas
                                                }}</span
                                            >
                                        </div>
                                        <div
                                            v-if="form.tipo_pago === 'CREDITO'"
                                            class="flex justify-between text-sm"
                                        >
                                            <span
                                                class="text-gray-600 dark:text-gray-400"
                                                >Interés:</span
                                            >
                                            <span
                                                class="font-medium text-gray-900 dark:text-gray-100"
                                                >{{
                                                    formatCurrency(
                                                        montoTotalConInteres -
                                                            montoTotal
                                                    )
                                                }}</span
                                            >
                                        </div>
                                        <div
                                            class="flex justify-between pt-2 border-t border-indigo-200 dark:border-indigo-700"
                                        >
                                            <span
                                                class="font-semibold text-gray-900 dark:text-gray-100"
                                                >Total a pagar:</span
                                            >
                                            <span
                                                class="text-xl font-bold text-indigo-600 dark:text-indigo-400"
                                            >
                                                {{
                                                    formatCurrency(
                                                        form.tipo_pago ===
                                                            "CREDITO"
                                                            ? montoTotalConInteres
                                                            : montoTotal
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pago inicial -->
                            <div
                                class="border-t border-gray-200 dark:border-gray-700 pt-4"
                            >
                                <div class="flex items-center mb-4">
                                    <input
                                        type="checkbox"
                                        id="realizar_pago"
                                        v-model="form.realizar_pago_inicial"
                                        :disabled="form.tipo_pago === 'CONTADO'"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    />
                                    <label
                                        for="realizar_pago"
                                        class="ml-2 text-sm text-gray-700 dark:text-gray-300"
                                    >
                                        {{
                                            form.tipo_pago === "CONTADO"
                                                ? "Registrar pago completo ahora"
                                                : "Registrar pago inicial / adelanto"
                                        }}
                                    </label>
                                </div>

                                <div
                                    v-if="form.realizar_pago_inicial"
                                    class="space-y-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg"
                                >
                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                    >
                                        <div>
                                            <InputLabel
                                                for="monto_pago"
                                                value="Monto a Pagar *"
                                            />
                                            <div class="relative mt-1">
                                                <span
                                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"
                                                    >$</span
                                                >
                                                <TextInput
                                                    id="monto_pago"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    :max="
                                                        form.tipo_pago ===
                                                        'CONTADO'
                                                            ? montoTotal
                                                            : montoTotalConInteres
                                                    "
                                                    class="pl-8 block w-full"
                                                    v-model.number="
                                                        form.pago_inicial.monto
                                                    "
                                                    :disabled="
                                                        form.tipo_pago ===
                                                        'CONTADO'
                                                    "
                                                />
                                            </div>
                                            <InputError
                                                :message="
                                                    form.errors[
                                                        'pago_inicial.monto'
                                                    ]
                                                "
                                                class="mt-2"
                                            />
                                        </div>
                                        <div>
                                            <InputLabel
                                                for="metodo_pago"
                                                value="Método de Pago *"
                                            />
                                            <select
                                                id="metodo_pago"
                                                v-model="
                                                    form.pago_inicial.metodo
                                                "
                                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            >
                                                <option
                                                    v-for="metodo in metodosPago"
                                                    :key="metodo.value"
                                                    :value="metodo.value"
                                                >
                                                    {{ metodo.label }}
                                                </option>
                                            </select>
                                            <InputError
                                                :message="
                                                    form.errors[
                                                        'pago_inicial.metodo'
                                                    ]
                                                "
                                                class="mt-2"
                                            />
                                        </div>
                                    </div>
                                    <div v-if="metodoRequiereReferencia">
                                        <InputLabel
                                            for="referencia"
                                            value="Número de Referencia / Comprobante"
                                        />
                                        <TextInput
                                            id="referencia"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="
                                                form.pago_inicial.referencia
                                            "
                                            placeholder="Ej: Número de transacción"
                                        />
                                        <InputError
                                            :message="
                                                form.errors[
                                                    'pago_inicial.referencia'
                                                ]
                                            "
                                            class="mt-2"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navegación -->
                        <div
                            class="flex justify-between mt-8 pt-6 border-t border-gray-200 dark:border-gray-700"
                        >
                            <SecondaryButton
                                v-if="currentStep > 1"
                                @click="prevStep"
                                type="button"
                            >
                                <ArrowLeftIcon class="h-4 w-4 mr-1" />
                                Anterior
                            </SecondaryButton>
                            <div v-else></div>

                            <PrimaryButton
                                v-if="currentStep < totalSteps"
                                @click="nextStep"
                                :disabled="!canProceed"
                                type="button"
                            >
                                Siguiente
                                <ArrowRightIcon class="h-4 w-4 ml-1" />
                            </PrimaryButton>

                            <PrimaryButton
                                v-else
                                @click="submit"
                                :disabled="!canProceed || form.processing"
                            >
                                <CheckIcon class="h-4 w-4 mr-1" />
                                {{
                                    form.processing
                                        ? "Procesando..."
                                        : "Confirmar Venta"
                                }}
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
