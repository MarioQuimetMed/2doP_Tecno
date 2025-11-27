<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import {
    ArrowLeftIcon,
    CreditCardIcon,
    QrCodeIcon,
    BuildingLibraryIcon,
    ShieldCheckIcon,
    LockClosedIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    BanknotesIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    venta: Object,
    cuota: Object,
    metodosPago: Array,
});

const currentStep = ref(1);
const processingPayment = ref(false);
const paymentSuccess = ref(false);
const paymentError = ref(null);

const form = useForm({
    venta_id: props.venta?.id || '',
    cuota_id: props.cuota?.id || '',
    monto: props.cuota?.saldo_pendiente || props.venta?.saldo_pendiente || 0,
    metodo_pago: 'TARJETA',
    // Datos de tarjeta
    numero_tarjeta: '',
    nombre_titular: '',
    fecha_expiracion: '',
    cvv: '',
});

const selectedMetodo = ref('TARJETA');

watch(selectedMetodo, (value) => {
    form.metodo_pago = value;
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value || 0);
};

// Formatear número de tarjeta con espacios
const formatCardNumber = (e) => {
    let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
    let formatted = '';
    for (let i = 0; i < value.length && i < 16; i++) {
        if (i > 0 && i % 4 === 0) formatted += ' ';
        formatted += value[i];
    }
    form.numero_tarjeta = formatted;
};

// Formatear fecha de expiración
const formatExpiration = (e) => {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    form.fecha_expiracion = value;
};

const getCardType = computed(() => {
    const number = form.numero_tarjeta.replace(/\s/g, '');
    if (number.startsWith('4')) return 'Visa';
    if (number.startsWith('5') || number.startsWith('2')) return 'Mastercard';
    if (number.startsWith('3')) return 'American Express';
    return null;
});

const isFormValid = computed(() => {
    if (selectedMetodo.value === 'TARJETA') {
        return form.numero_tarjeta.replace(/\s/g, '').length >= 15 &&
               form.nombre_titular.length >= 3 &&
               form.fecha_expiracion.length === 5 &&
               form.cvv.length >= 3;
    }
    return true;
});

const nextStep = () => {
    if (currentStep.value < 3) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const processPayment = () => {
    processingPayment.value = true;
    paymentError.value = null;
    
    form.post(route('pagos.procesar-electronico'), {
        preserveScroll: true,
        onSuccess: () => {
            paymentSuccess.value = true;
            processingPayment.value = false;
            currentStep.value = 4;
        },
        onError: (errors) => {
            processingPayment.value = false;
            paymentError.value = errors.error || 'Error al procesar el pago. Por favor intente nuevamente.';
        },
    });
};

const getMetodoIcon = (metodo) => {
    const icons = {
        TARJETA: CreditCardIcon,
        QR: QrCodeIcon,
        TRANSFERENCIA: BuildingLibraryIcon,
    };
    return icons[metodo] || CreditCardIcon;
};
</script>

<template>
    <Head title="Pago Electrónico" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link
                    :href="route('pagos.index')"
                    class="mr-4 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-500" />
                </Link>
                <CreditCardIcon class="h-6 w-6 mr-2 text-blue-600" />
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Pago Electrónico
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <!-- Indicador de pasos -->
                <div class="mb-8">
                    <div class="flex items-center justify-center">
                        <template v-for="(step, index) in ['Método', 'Datos', 'Confirmar', 'Resultado']" :key="step">
                            <div class="flex items-center">
                                <div
                                    :class="[
                                        'w-10 h-10 rounded-full flex items-center justify-center font-bold transition',
                                        currentStep > index + 1 ? 'bg-green-500 text-white' :
                                        currentStep === index + 1 ? 'bg-blue-600 text-white' :
                                        'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'
                                    ]"
                                >
                                    <CheckCircleIcon v-if="currentStep > index + 1" class="h-6 w-6" />
                                    <span v-else>{{ index + 1 }}</span>
                                </div>
                                <span class="ml-2 text-sm font-medium" :class="currentStep >= index + 1 ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400'">
                                    {{ step }}
                                </span>
                            </div>
                            <div v-if="index < 3" class="w-12 h-1 mx-2" :class="currentStep > index + 1 ? 'bg-green-500' : 'bg-gray-200 dark:bg-gray-700'"></div>
                        </template>
                    </div>
                </div>

                <!-- Información del pago -->
                <div v-if="venta" class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg p-6 text-white mb-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-blue-100 text-sm">Pago para</p>
                            <p class="font-bold text-lg">{{ venta.viaje }}</p>
                            <p class="text-blue-200 text-sm">{{ venta.destino }}</p>
                            <p class="text-blue-200 text-sm mt-1">Cliente: {{ venta.cliente }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-blue-100 text-sm">Monto a pagar</p>
                            <p class="text-3xl font-bold">{{ formatCurrency(form.monto) }}</p>
                            <p v-if="cuota" class="text-blue-200 text-sm">Cuota #{{ cuota.numero_cuota }}</p>
                        </div>
                    </div>
                </div>

                <!-- Contenido según el paso -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Paso 1: Selección de método -->
                    <div v-if="currentStep === 1" class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Seleccione el método de pago
                        </h3>

                        <div class="space-y-3">
                            <button
                                v-for="metodo in metodosPago"
                                :key="metodo.value"
                                type="button"
                                @click="selectedMetodo = metodo.value"
                                :class="[
                                    'w-full p-4 rounded-lg border-2 transition flex items-center',
                                    selectedMetodo === metodo.value
                                        ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                        : 'border-gray-200 dark:border-gray-600 hover:border-blue-300'
                                ]"
                            >
                                <component 
                                    :is="getMetodoIcon(metodo.value)" 
                                    class="h-8 w-8 mr-4" 
                                    :class="selectedMetodo === metodo.value ? 'text-blue-600' : 'text-gray-400'" 
                                />
                                <div class="text-left">
                                    <p class="font-medium" :class="selectedMetodo === metodo.value ? 'text-blue-600' : 'text-gray-900 dark:text-gray-100'">
                                        {{ metodo.label }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ metodo.value === 'TARJETA' ? 'Visa, Mastercard, American Express' : 
                                           metodo.value === 'QR' ? 'Escanee el código QR con su app bancaria' : 
                                           'Transferencia directa a nuestra cuenta' }}
                                    </p>
                                </div>
                                <div v-if="selectedMetodo === metodo.value" class="ml-auto">
                                    <CheckCircleIcon class="h-6 w-6 text-blue-600" />
                                </div>
                            </button>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <PrimaryButton @click="nextStep">
                                Continuar
                            </PrimaryButton>
                        </div>
                    </div>

                    <!-- Paso 2: Datos del pago -->
                    <div v-if="currentStep === 2" class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            {{ selectedMetodo === 'TARJETA' ? 'Datos de la tarjeta' : 
                               selectedMetodo === 'QR' ? 'Pago con código QR' : 
                               'Datos de transferencia' }}
                        </h3>

                        <!-- Formulario de Tarjeta -->
                        <div v-if="selectedMetodo === 'TARJETA'" class="space-y-4">
                            <div>
                                <InputLabel for="numero_tarjeta" value="Número de Tarjeta *" />
                                <div class="relative">
                                    <TextInput
                                        id="numero_tarjeta"
                                        type="text"
                                        class="mt-1 block w-full pr-12"
                                        v-model="form.numero_tarjeta"
                                        @input="formatCardNumber"
                                        placeholder="0000 0000 0000 0000"
                                        maxlength="19"
                                    />
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <span v-if="getCardType" class="text-sm font-medium text-gray-500">{{ getCardType }}</span>
                                    </div>
                                </div>
                                <InputError :message="form.errors.numero_tarjeta" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="nombre_titular" value="Nombre del Titular *" />
                                <TextInput
                                    id="nombre_titular"
                                    type="text"
                                    class="mt-1 block w-full uppercase"
                                    v-model="form.nombre_titular"
                                    placeholder="COMO APARECE EN LA TARJETA"
                                />
                                <InputError :message="form.errors.nombre_titular" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <InputLabel for="fecha_expiracion" value="Fecha de Expiración *" />
                                    <TextInput
                                        id="fecha_expiracion"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.fecha_expiracion"
                                        @input="formatExpiration"
                                        placeholder="MM/YY"
                                        maxlength="5"
                                    />
                                    <InputError :message="form.errors.fecha_expiracion" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="cvv" value="CVV *" />
                                    <TextInput
                                        id="cvv"
                                        type="password"
                                        class="mt-1 block w-full"
                                        v-model="form.cvv"
                                        placeholder="•••"
                                        maxlength="4"
                                    />
                                    <InputError :message="form.errors.cvv" class="mt-2" />
                                </div>
                            </div>

                            <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mt-4">
                                <LockClosedIcon class="h-4 w-4 mr-2" />
                                <span>Tus datos están protegidos con encriptación SSL</span>
                            </div>
                        </div>

                        <!-- Código QR -->
                        <div v-if="selectedMetodo === 'QR'" class="text-center py-8">
                            <div class="inline-block p-4 bg-white rounded-lg shadow-lg mb-4">
                                <!-- QR Code simulado -->
                                <div class="w-48 h-48 bg-gray-100 flex items-center justify-center border-2 border-gray-300">
                                    <div class="text-center">
                                        <QrCodeIcon class="h-16 w-16 text-gray-400 mx-auto mb-2" />
                                        <p class="text-xs text-gray-500">QR Simulado</p>
                                        <p class="text-xs text-gray-400">ID: {{ Date.now() }}</p>
                                    </div>
                                </div>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 mb-2">
                                Escanee este código con la app de su banco
                            </p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ formatCurrency(form.monto) }}
                            </p>
                        </div>

                        <!-- Datos de Transferencia -->
                        <div v-if="selectedMetodo === 'TRANSFERENCIA'" class="space-y-4">
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Datos de la cuenta</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Banco:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">Banco Nacional de Bolivia</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Cuenta:</span>
                                        <span class="font-mono font-medium text-gray-900 dark:text-gray-100">1234-5678-9012-3456</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Titular:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">Tendencias Tours SRL</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">NIT:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">12345678901</span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    <strong>Importante:</strong> Incluya el número de venta <span class="font-mono">#{{ venta?.id }}</span> en la descripción de la transferencia.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-between">
                            <button
                                @click="prevStep"
                                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                            >
                                ← Atrás
                            </button>
                            <PrimaryButton @click="nextStep" :disabled="selectedMetodo === 'TARJETA' && !isFormValid">
                                Continuar
                            </PrimaryButton>
                        </div>
                    </div>

                    <!-- Paso 3: Confirmación -->
                    <div v-if="currentStep === 3" class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            Confirmar Pago
                        </h3>

                        <div class="space-y-4">
                            <!-- Resumen del pago -->
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Método de pago:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100 flex items-center">
                                            <component :is="getMetodoIcon(selectedMetodo)" class="h-4 w-4 mr-1" />
                                            {{ metodosPago.find(m => m.value === selectedMetodo)?.label }}
                                        </span>
                                    </div>
                                    <div v-if="selectedMetodo === 'TARJETA'" class="flex justify-between">
                                        <span class="text-gray-500">Tarjeta:</span>
                                        <span class="font-mono text-gray-900 dark:text-gray-100">
                                            •••• •••• •••• {{ form.numero_tarjeta.slice(-4) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between border-t border-gray-200 dark:border-gray-600 pt-3">
                                        <span class="text-gray-700 dark:text-gray-300 font-medium">Total a pagar:</span>
                                        <span class="text-xl font-bold text-green-600 dark:text-green-400">
                                            {{ formatCurrency(form.monto) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Aviso de seguridad -->
                            <div class="flex items-start p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <ShieldCheckIcon class="h-6 w-6 text-green-600 dark:text-green-400 mr-3 flex-shrink-0" />
                                <div>
                                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                        Pago Seguro
                                    </p>
                                    <p class="text-sm text-green-700 dark:text-green-300">
                                        Esta es una simulación de pago electrónico. En producción, se integraría con pasarelas de pago reales.
                                    </p>
                                </div>
                            </div>

                            <!-- Error si existe -->
                            <div v-if="paymentError" class="flex items-start p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                <ExclamationTriangleIcon class="h-6 w-6 text-red-600 dark:text-red-400 mr-3 flex-shrink-0" />
                                <div>
                                    <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                        Error en el pago
                                    </p>
                                    <p class="text-sm text-red-700 dark:text-red-300">
                                        {{ paymentError }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-between">
                            <button
                                @click="prevStep"
                                class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                                :disabled="processingPayment"
                            >
                                ← Atrás
                            </button>
                            <PrimaryButton 
                                @click="processPayment" 
                                :disabled="processingPayment"
                                class="min-w-[150px]"
                            >
                                <span v-if="processingPayment" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Procesando...
                                </span>
                                <span v-else>
                                    Confirmar Pago
                                </span>
                            </PrimaryButton>
                        </div>
                    </div>

                    <!-- Paso 4: Resultado -->
                    <div v-if="currentStep === 4" class="p-6 text-center">
                        <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
                            <CheckCircleIcon class="h-12 w-12 text-green-600 dark:text-green-400" />
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                            ¡Pago Exitoso!
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-6">
                            Su pago ha sido procesado correctamente.
                        </p>
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg inline-block mb-6">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Monto pagado</p>
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                                {{ formatCurrency(form.monto) }}
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <Link
                                :href="route('pagos.index')"
                                class="inline-flex items-center justify-center px-6 py-3 bg-green-600 rounded-lg font-semibold text-white hover:bg-green-700 transition"
                            >
                                <BanknotesIcon class="h-5 w-5 mr-2" />
                                Ver Mis Pagos
                            </Link>
                            <Link
                                :href="route('ventas.show', venta?.id)"
                                class="inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                            >
                                Ver Detalle de Venta
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
