<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import {
    ArrowLeftIcon,
    BanknotesIcon,
    CreditCardIcon,
    QrCodeIcon,
    BuildingLibraryIcon,
    CurrencyDollarIcon,
    UserIcon,
    MapPinIcon,
    CalendarDaysIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    ventas: Array,
    metodosPago: Array,
    ventaPreseleccionada: Object,
});

const form = useForm({
    venta_id: props.ventaPreseleccionada?.id || "",
    monto: "",
    metodo_pago: "EFECTIVO",
    referencia: "",
    cuota_id: "",
});

const selectedVenta = ref(props.ventaPreseleccionada || null);
const selectedCuota = ref(null);
const pagoTipo = ref("total"); // 'total', 'parcial', 'cuota'

// Cuando cambia la venta seleccionada
watch(
    () => form.venta_id,
    (ventaId) => {
        if (ventaId) {
            selectedVenta.value = props.ventas.find(
                (v) => v.id === parseInt(ventaId)
            );
            selectedCuota.value = null;
            form.cuota_id = "";

            // Si es venta a crédito, preseleccionar la primera cuota pendiente
            if (
                selectedVenta.value?.es_credito &&
                selectedVenta.value?.cuotas_pendientes?.length
            ) {
                pagoTipo.value = "cuota";
            } else {
                pagoTipo.value = "total";
            }

            updateMonto();
        } else {
            selectedVenta.value = null;
            selectedCuota.value = null;
            form.monto = "";
        }
    }
);

// Cuando cambia el tipo de pago
watch(pagoTipo, () => {
    updateMonto();
    if (pagoTipo.value !== "cuota") {
        form.cuota_id = "";
        selectedCuota.value = null;
    }
});

// Cuando cambia la cuota seleccionada
watch(
    () => form.cuota_id,
    (cuotaId) => {
        if (cuotaId && selectedVenta.value?.cuotas_pendientes) {
            selectedCuota.value = selectedVenta.value.cuotas_pendientes.find(
                (c) => c.id === parseInt(cuotaId)
            );
            if (selectedCuota.value) {
                form.monto = selectedCuota.value.saldo_pendiente;
            }
        } else {
            selectedCuota.value = null;
        }
    }
);

const updateMonto = () => {
    if (!selectedVenta.value) return;

    if (pagoTipo.value === "total") {
        form.monto = selectedVenta.value.saldo_pendiente;
    } else if (pagoTipo.value === "parcial") {
        form.monto = "";
    } else if (pagoTipo.value === "cuota" && selectedCuota.value) {
        form.monto = selectedCuota.value.saldo_pendiente;
    }
};

const metodoRequiereReferencia = computed(() => {
    const metodo = props.metodosPago.find((m) => m.value === form.metodo_pago);
    return metodo?.requiereReferencia || false;
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value || 0);
};

const getMetodoIcon = (metodo) => {
    const icons = {
        EFECTIVO: BanknotesIcon,
        TARJETA: CreditCardIcon,
        QR: QrCodeIcon,
        TRANSFERENCIA: BuildingLibraryIcon,
    };
    return icons[metodo] || BanknotesIcon;
};

const submit = () => {
    form.post(route('pagos.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Registrar Pago" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link
                    :href="route('pagos.index')"
                    class="mr-4 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-500" />
                </Link>
                <BanknotesIcon class="h-6 w-6 mr-2 text-green-600" />
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Registrar Nuevo Pago
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Columna principal -->
                        <div class="lg:col-span-2 space-y-6">
                            <!-- Selección de Venta -->
                            <div
                                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                            >
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4"
                                >
                                    1. Seleccionar Venta
                                </h3>

                                <div>
                                    <InputLabel
                                        for="venta_id"
                                        value="Venta Pendiente de Pago *"
                                    />
                                    <select
                                        id="venta_id"
                                        v-model="form.venta_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                        required
                                    >
                                        <option value="">
                                            -- Seleccione una venta --
                                        </option>
                                        <option
                                            v-for="venta in ventas"
                                            :key="venta.id"
                                            :value="venta.id"
                                        >
                                            #{{ venta.id }} -
                                            {{ venta.cliente }} -
                                            {{ venta.viaje }} - Saldo:
                                            {{
                                                formatCurrency(
                                                    venta.saldo_pendiente
                                                )
                                            }}
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.venta_id"
                                        class="mt-2"
                                    />
                                </div>

                                <!-- Detalle de venta seleccionada -->
                                <div
                                    v-if="selectedVenta"
                                    class="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                                >
                                    <div
                                        class="flex items-start justify-between"
                                    >
                                        <div>
                                            <div
                                                class="flex items-center text-gray-900 dark:text-gray-100"
                                            >
                                                <UserIcon
                                                    class="h-5 w-5 mr-2 text-indigo-500"
                                                />
                                                <span class="font-medium">{{
                                                    selectedVenta.cliente
                                                }}</span>
                                            </div>
                                            <div
                                                class="flex items-center text-gray-600 dark:text-gray-400 mt-1"
                                            >
                                                <MapPinIcon
                                                    class="h-4 w-4 mr-2"
                                                />
                                                <span class="text-sm"
                                                    >{{ selectedVenta.viaje }} -
                                                    {{
                                                        selectedVenta.destino
                                                    }}</span
                                                >
                                            </div>
                                            <div
                                                class="flex items-center text-gray-500 dark:text-gray-400 mt-1"
                                            >
                                                <CalendarDaysIcon
                                                    class="h-4 w-4 mr-2"
                                                />
                                                <span class="text-sm"
                                                    >Venta:
                                                    {{
                                                        selectedVenta.fecha_venta
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span
                                                :class="[
                                                    'inline-flex px-2 py-0.5 rounded text-xs font-medium',
                                                    selectedVenta.tipo_pago ===
                                                    'CONTADO'
                                                        ? 'bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200'
                                                        : 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
                                                ]"
                                            >
                                                {{
                                                    selectedVenta.tipo_pago_label
                                                }}
                                            </span>
                                        </div>
                                    </div>

                                    <div
                                        class="mt-4 grid grid-cols-3 gap-4 text-center"
                                    >
                                        <div
                                            class="p-2 bg-white dark:bg-gray-800 rounded"
                                        >
                                            <p
                                                class="text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                Total
                                            </p>
                                            <p
                                                class="font-semibold text-gray-900 dark:text-gray-100"
                                            >
                                                {{
                                                    formatCurrency(
                                                        selectedVenta.monto_total
                                                    )
                                                }}
                                            </p>
                                        </div>
                                        <div
                                            class="p-2 bg-green-50 dark:bg-green-900/20 rounded"
                                        >
                                            <p
                                                class="text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                Pagado
                                            </p>
                                            <p
                                                class="font-semibold text-green-600 dark:text-green-400"
                                            >
                                                {{
                                                    formatCurrency(
                                                        selectedVenta.monto_pagado
                                                    )
                                                }}
                                            </p>
                                        </div>
                                        <div
                                            class="p-2 bg-red-50 dark:bg-red-900/20 rounded"
                                        >
                                            <p
                                                class="text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                Pendiente
                                            </p>
                                            <p
                                                class="font-semibold text-red-600 dark:text-red-400"
                                            >
                                                {{
                                                    formatCurrency(
                                                        selectedVenta.saldo_pendiente
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tipo y monto de pago -->
                            <div
                                v-if="selectedVenta"
                                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                            >
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4"
                                >
                                    2. Monto a Pagar
                                </h3>

                                <!-- Opciones de tipo de pago -->
                                <div class="grid grid-cols-3 gap-3 mb-4">
                                    <button
                                        type="button"
                                        @click="pagoTipo = 'total'"
                                        :class="[
                                            'p-3 rounded-lg border-2 transition text-center',
                                            pagoTipo === 'total'
                                                ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                                : 'border-gray-200 dark:border-gray-600 hover:border-green-300',
                                        ]"
                                    >
                                        <CurrencyDollarIcon
                                            class="h-6 w-6 mx-auto mb-1"
                                            :class="
                                                pagoTipo === 'total'
                                                    ? 'text-green-600'
                                                    : 'text-gray-400'
                                            "
                                        />
                                        <p
                                            class="text-sm font-medium"
                                            :class="
                                                pagoTipo === 'total'
                                                    ? 'text-green-600'
                                                    : 'text-gray-600 dark:text-gray-400'
                                            "
                                        >
                                            Pago Total
                                        </p>
                                    </button>

                                    <button
                                        type="button"
                                        @click="pagoTipo = 'parcial'"
                                        :class="[
                                            'p-3 rounded-lg border-2 transition text-center',
                                            pagoTipo === 'parcial'
                                                ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                                : 'border-gray-200 dark:border-gray-600 hover:border-blue-300',
                                        ]"
                                    >
                                        <BanknotesIcon
                                            class="h-6 w-6 mx-auto mb-1"
                                            :class="
                                                pagoTipo === 'parcial'
                                                    ? 'text-blue-600'
                                                    : 'text-gray-400'
                                            "
                                        />
                                        <p
                                            class="text-sm font-medium"
                                            :class="
                                                pagoTipo === 'parcial'
                                                    ? 'text-blue-600'
                                                    : 'text-gray-600 dark:text-gray-400'
                                            "
                                        >
                                            Pago Parcial
                                        </p>
                                    </button>

                                    <button
                                        v-if="
                                            selectedVenta.es_credito &&
                                            selectedVenta.cuotas_pendientes
                                                ?.length
                                        "
                                        type="button"
                                        @click="pagoTipo = 'cuota'"
                                        :class="[
                                            'p-3 rounded-lg border-2 transition text-center',
                                            pagoTipo === 'cuota'
                                                ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20'
                                                : 'border-gray-200 dark:border-gray-600 hover:border-purple-300',
                                        ]"
                                    >
                                        <CalendarDaysIcon
                                            class="h-6 w-6 mx-auto mb-1"
                                            :class="
                                                pagoTipo === 'cuota'
                                                    ? 'text-purple-600'
                                                    : 'text-gray-400'
                                            "
                                        />
                                        <p
                                            class="text-sm font-medium"
                                            :class="
                                                pagoTipo === 'cuota'
                                                    ? 'text-purple-600'
                                                    : 'text-gray-600 dark:text-gray-400'
                                            "
                                        >
                                            Pago de Cuota
                                        </p>
                                    </button>
                                </div>

                                <!-- Selección de cuota (si aplica) -->
                                <div
                                    v-if="
                                        pagoTipo === 'cuota' &&
                                        selectedVenta.cuotas_pendientes?.length
                                    "
                                    class="mb-4"
                                >
                                    <InputLabel
                                        for="cuota_id"
                                        value="Seleccionar Cuota *"
                                    />
                                    <select
                                        id="cuota_id"
                                        v-model="form.cuota_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                    >
                                        <option value="">
                                            -- Seleccione una cuota --
                                        </option>
                                        <option
                                            v-for="cuota in selectedVenta.cuotas_pendientes"
                                            :key="cuota.id"
                                            :value="cuota.id"
                                        >
                                            Cuota #{{ cuota.numero_cuota }} -
                                            {{
                                                formatCurrency(
                                                    cuota.monto_total_cuota
                                                )
                                            }}
                                            - Vence:
                                            {{ cuota.fecha_vencimiento }}
                                            {{
                                                cuota.estado === "VENCIDO"
                                                    ? "⚠️ VENCIDA"
                                                    : ""
                                            }}
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.cuota_id"
                                        class="mt-2"
                                    />
                                </div>

                                <!-- Monto -->
                                <div>
                                    <InputLabel
                                        for="monto"
                                        value="Monto a Pagar *"
                                    />
                                    <div class="relative mt-1">
                                        <span
                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"
                                            >$</span
                                        >
                                        <TextInput
                                            id="monto"
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            :max="selectedVenta.saldo_pendiente"
                                            class="pl-8 block w-full text-lg"
                                            v-model.number="form.monto"
                                            :readonly="pagoTipo === 'total'"
                                            required
                                        />
                                    </div>
                                    <p
                                        class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Máximo:
                                        {{
                                            formatCurrency(
                                                selectedVenta.saldo_pendiente
                                            )
                                        }}
                                    </p>
                                    <InputError
                                        :message="form.errors.monto"
                                        class="mt-2"
                                    />
                                </div>
                            </div>

                            <!-- Método de pago -->
                            <div
                                v-if="selectedVenta && form.monto"
                                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                            >
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4"
                                >
                                    3. Método de Pago
                                </h3>

                                <div
                                    class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4"
                                >
                                    <button
                                        v-for="metodo in metodosPago"
                                        :key="metodo.value"
                                        type="button"
                                        @click="form.metodo_pago = metodo.value"
                                        :class="[
                                            'p-4 rounded-lg border-2 transition text-center',
                                            form.metodo_pago === metodo.value
                                                ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                                : 'border-gray-200 dark:border-gray-600 hover:border-green-300',
                                        ]"
                                    >
                                        <component
                                            :is="getMetodoIcon(metodo.value)"
                                            class="h-8 w-8 mx-auto mb-2"
                                            :class="
                                                form.metodo_pago ===
                                                metodo.value
                                                    ? 'text-green-600'
                                                    : 'text-gray-400'
                                            "
                                        />
                                        <p
                                            class="text-sm font-medium"
                                            :class="
                                                form.metodo_pago ===
                                                metodo.value
                                                    ? 'text-green-600'
                                                    : 'text-gray-600 dark:text-gray-400'
                                            "
                                        >
                                            {{ metodo.label }}
                                        </p>
                                    </button>
                                </div>

                                <!-- Referencia (si aplica) -->
                                <div v-if="metodoRequiereReferencia">
                                    <InputLabel
                                        for="referencia"
                                        value="Número de Referencia / Transacción"
                                    />
                                    <TextInput
                                        id="referencia"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.referencia"
                                        placeholder="Ej: TRX-123456789"
                                    />
                                    <InputError
                                        :message="form.errors.referencia"
                                        class="mt-2"
                                    />
                                </div>

                                <!-- Enlace a pago electrónico -->
                                <div
                                    v-if="form.metodo_pago !== 'EFECTIVO'"
                                    class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
                                >
                                    <p
                                        class="text-sm text-blue-700 dark:text-blue-300"
                                    >
                                        ¿Desea procesar un pago electrónico?
                                        <Link
                                            :href="route('pagos.electronico', {
                                                venta_id: selectedVenta.id,
                                                cuota_id: form.cuota_id || undefined
                                            })"
                                            class="font-medium underline hover:no-underline ml-1"
                                        >
                                            Ir a pasarela de pago →
                                        </Link>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Columna lateral - Resumen -->
                        <div class="lg:col-span-1">
                            <div
                                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 sticky top-6"
                            >
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4"
                                >
                                    Resumen del Pago
                                </h3>

                                <div
                                    v-if="selectedVenta && form.monto"
                                    class="space-y-4"
                                >
                                    <div
                                        class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg text-center"
                                    >
                                        <p
                                            class="text-sm text-gray-500 dark:text-gray-400 mb-1"
                                        >
                                            Monto a Pagar
                                        </p>
                                        <p
                                            class="text-3xl font-bold text-green-600 dark:text-green-400"
                                        >
                                            {{ formatCurrency(form.monto) }}
                                        </p>
                                    </div>

                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span
                                                class="text-gray-500 dark:text-gray-400"
                                                >Venta #:</span
                                            >
                                            <span
                                                class="font-medium text-gray-900 dark:text-gray-100"
                                                >{{ selectedVenta.id }}</span
                                            >
                                        </div>
                                        <div class="flex justify-between">
                                            <span
                                                class="text-gray-500 dark:text-gray-400"
                                                >Cliente:</span
                                            >
                                            <span
                                                class="font-medium text-gray-900 dark:text-gray-100"
                                                >{{
                                                    selectedVenta.cliente
                                                }}</span
                                            >
                                        </div>
                                        <div class="flex justify-between">
                                            <span
                                                class="text-gray-500 dark:text-gray-400"
                                                >Método:</span
                                            >
                                            <span
                                                class="font-medium text-gray-900 dark:text-gray-100"
                                            >
                                                {{
                                                    metodosPago.find(
                                                        (m) =>
                                                            m.value ===
                                                            form.metodo_pago
                                                    )?.label
                                                }}
                                            </span>
                                        </div>
                                        <div
                                            v-if="selectedCuota"
                                            class="flex justify-between"
                                        >
                                            <span
                                                class="text-gray-500 dark:text-gray-400"
                                                >Cuota:</span
                                            >
                                            <span
                                                class="font-medium text-gray-900 dark:text-gray-100"
                                                >#{{
                                                    selectedCuota.numero_cuota
                                                }}</span
                                            >
                                        </div>
                                    </div>

                                    <div
                                        class="pt-4 border-t border-gray-200 dark:border-gray-700"
                                    >
                                        <div
                                            class="flex justify-between text-sm mb-2"
                                        >
                                            <span
                                                class="text-gray-500 dark:text-gray-400"
                                                >Saldo antes del pago:</span
                                            >
                                            <span
                                                class="text-gray-900 dark:text-gray-100"
                                                >{{
                                                    formatCurrency(
                                                        selectedVenta.saldo_pendiente
                                                    )
                                                }}</span
                                            >
                                        </div>
                                        <div
                                            class="flex justify-between text-sm"
                                        >
                                            <span
                                                class="text-gray-500 dark:text-gray-400"
                                                >Saldo después del pago:</span
                                            >
                                            <span
                                                class="font-medium"
                                                :class="
                                                    selectedVenta.saldo_pendiente -
                                                        form.monto <=
                                                    0
                                                        ? 'text-green-600'
                                                        : 'text-orange-600'
                                                "
                                            >
                                                {{
                                                    formatCurrency(
                                                        Math.max(
                                                            0,
                                                            selectedVenta.saldo_pendiente -
                                                                form.monto
                                                        )
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>

                                    <div
                                        v-if="
                                            selectedVenta.saldo_pendiente -
                                                form.monto <=
                                            0
                                        "
                                        class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center"
                                    >
                                        <CheckCircleIcon
                                            class="h-5 w-5 text-green-600 dark:text-green-400 mr-2"
                                        />
                                        <span
                                            class="text-sm text-green-700 dark:text-green-300"
                                            >La venta quedará completamente
                                            pagada</span
                                        >
                                    </div>

                                    <PrimaryButton
                                        type="submit"
                                        class="w-full justify-center"
                                        :disabled="
                                            form.processing ||
                                            !form.monto ||
                                            form.monto <= 0
                                        "
                                    >
                                        {{
                                            form.processing
                                                ? "Procesando..."
                                                : "Registrar Pago"
                                        }}
                                    </PrimaryButton>
                                </div>

                                <div
                                    v-else
                                    class="text-center py-8 text-gray-500 dark:text-gray-400"
                                >
                                    <BanknotesIcon
                                        class="h-12 w-12 mx-auto mb-2 text-gray-300"
                                    />
                                    <p>
                                        Seleccione una venta y el monto para
                                        continuar
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
