<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import {
    CreditCardIcon,
    ArrowLeftIcon,
    CalendarDaysIcon,
    BanknotesIcon,
    CheckCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    UserIcon,
    MapPinIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    XMarkIcon,
    PrinterIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    planPago: Object,
    resumen: Object,
    metodosPago: Array,
});

const showPaymentModal = ref(false);
const selectedCuota = ref(null);

const paymentForm = useForm({
    monto: "",
    metodo_pago: "EFECTIVO",
    referencia: "",
});

const formatMoney = (amount) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-BO", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    });
};

const formatShortDate = (date) => {
    return new Date(date).toLocaleDateString("es-BO", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
};

const getEstadoCuotaClass = (estado) => {
    const classes = {
        PENDIENTE: "bg-yellow-100 text-yellow-800 border-yellow-200",
        PAGADO: "bg-green-100 text-green-800 border-green-200",
        VENCIDO: "bg-red-100 text-red-800 border-red-200",
    };
    return classes[estado] || "bg-gray-100 text-gray-800";
};

const getEstadoCuotaIcon = (estado) => {
    return {
        PENDIENTE: ClockIcon,
        PAGADO: CheckCircleIcon,
        VENCIDO: ExclamationTriangleIcon,
    }[estado];
};

const diasRestantes = (fecha) => {
    const hoy = new Date();
    const vencimiento = new Date(fecha);
    const diff = Math.ceil((vencimiento - hoy) / (1000 * 60 * 60 * 24));
    return diff;
};

const openPaymentModal = (cuota) => {
    selectedCuota.value = cuota;
    const saldoPendiente =
        cuota.monto_total_cuota -
        (cuota.pagos?.reduce((sum, p) => sum + parseFloat(p.monto_pagado), 0) ||
            0);
    paymentForm.monto = saldoPendiente.toFixed(2);
    paymentForm.metodo_pago = "EFECTIVO";
    paymentForm.referencia = "";
    showPaymentModal.value = true;
};

const closePaymentModal = () => {
    showPaymentModal.value = false;
    selectedCuota.value = null;
    paymentForm.reset();
};

const submitPayment = () => {
    paymentForm.post("/planes-pago/pagar-cuota/" + selectedCuota.value.id, {
        onSuccess: () => {
            closePaymentModal();
        },
        preserveScroll: true,
    });
};

const calcularSaldoCuota = (cuota) => {
    const pagado =
        cuota.pagos?.reduce((sum, p) => sum + parseFloat(p.monto_pagado), 0) ||
        0;
    return cuota.monto_total_cuota - pagado;
};

const puedeRecibirPago = (cuota) => {
    return cuota.estado_cuota !== "PAGADO" && calcularSaldoCuota(cuota) > 0;
};

const progressPercentage = computed(() => {
    return props.resumen.porcentaje_pagado || 0;
});
</script>

<template>
    <Head :title="`Plan de Pago #${planPago.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <Link
                        :href="'/planes-pago'"
                        class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                    >
                        <ArrowLeftIcon
                            class="h-5 w-5 text-gray-600 dark:text-gray-400"
                        />
                    </Link>
                    <CreditCardIcon class="h-8 w-8 text-purple-600" />
                    <div>
                        <h2
                            class="text-xl font-semibold text-gray-800 dark:text-gray-200"
                        >
                            Cronograma de Pagos
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Plan #{{ planPago.id }} - Venta #{{
                                planPago.venta.id
                            }}
                        </p>
                    </div>
                </div>
                <Link
                    :href="'/ventas/' + planPago.venta.id"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                >
                    <DocumentTextIcon class="h-4 w-4 mr-2" />
                    Ver Venta
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Columna izquierda: Info del Cliente y Viaje -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Tarjeta del Cliente -->
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden"
                        >
                            <div
                                class="bg-gradient-to-r from-purple-600 to-purple-800 px-4 py-3"
                            >
                                <h3
                                    class="text-white font-semibold flex items-center"
                                >
                                    <UserIcon class="h-5 w-5 mr-2" />
                                    Cliente
                                </h3>
                            </div>
                            <div class="p-4">
                                <p
                                    class="font-medium text-gray-900 dark:text-white"
                                >
                                    {{ planPago.venta.cliente.name }}
                                </p>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ planPago.venta.cliente.email }}
                                </p>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Tel:
                                    {{
                                        planPago.venta.cliente.telefono || "N/A"
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Tarjeta del Viaje -->
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden"
                        >
                            <div
                                class="bg-gradient-to-r from-blue-600 to-blue-800 px-4 py-3"
                            >
                                <h3
                                    class="text-white font-semibold flex items-center"
                                >
                                    <MapPinIcon class="h-5 w-5 mr-2" />
                                    Viaje
                                </h3>
                            </div>
                            <div class="p-4">
                                <p
                                    class="font-medium text-gray-900 dark:text-white"
                                >
                                    {{
                                        planPago.venta.viaje?.plan_viaje
                                            ?.destino?.nombre || "Sin destino"
                                    }}
                                </p>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{
                                        planPago.venta.viaje?.plan_viaje
                                            ?.nombre || "Sin plan"
                                    }}
                                </p>
                                <div
                                    class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400"
                                >
                                    <CalendarDaysIcon class="h-4 w-4 mr-1" />
                                    {{
                                        formatShortDate(
                                            planPago.venta.viaje?.fecha_salida
                                        )
                                    }}
                                </div>
                            </div>
                        </div>

                        <!-- Resumen del Plan -->
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden"
                        >
                            <div
                                class="bg-gradient-to-r from-green-600 to-green-800 px-4 py-3"
                            >
                                <h3
                                    class="text-white font-semibold flex items-center"
                                >
                                    <BanknotesIcon class="h-5 w-5 mr-2" />
                                    Resumen Financiero
                                </h3>
                            </div>
                            <div class="p-4 space-y-3">
                                <div class="flex justify-between">
                                    <span
                                        class="text-gray-600 dark:text-gray-400"
                                        >Monto Original:</span
                                    >
                                    <span
                                        class="font-medium text-gray-900 dark:text-white"
                                    >
                                        {{
                                            formatMoney(resumen.monto_original)
                                        }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span
                                        class="text-gray-600 dark:text-gray-400"
                                        >Total Intereses:</span
                                    >
                                    <span class="font-medium text-orange-600">
                                        +
                                        {{
                                            formatMoney(resumen.total_intereses)
                                        }}
                                    </span>
                                </div>
                                <div
                                    class="flex justify-between border-t border-gray-200 dark:border-gray-700 pt-2"
                                >
                                    <span
                                        class="text-gray-600 dark:text-gray-400"
                                        >Total con Intereses:</span
                                    >
                                    <span
                                        class="font-bold text-gray-900 dark:text-white"
                                    >
                                        {{
                                            formatMoney(
                                                resumen.monto_total_con_intereses
                                            )
                                        }}
                                    </span>
                                </div>
                                <div
                                    class="flex justify-between text-green-600"
                                >
                                    <span>Total Pagado:</span>
                                    <span class="font-medium">{{
                                        formatMoney(resumen.total_pagado)
                                    }}</span>
                                </div>
                                <div
                                    class="flex justify-between text-red-600 font-semibold"
                                >
                                    <span>Saldo Pendiente:</span>
                                    <span>{{
                                        formatMoney(resumen.saldo_pendiente)
                                    }}</span>
                                </div>

                                <!-- Barra de progreso -->
                                <div class="mt-4">
                                    <div
                                        class="flex justify-between text-sm mb-1"
                                    >
                                        <span
                                            class="text-gray-600 dark:text-gray-400"
                                            >Progreso de Pago</span
                                        >
                                        <span
                                            class="font-medium text-gray-900 dark:text-white"
                                        >
                                            {{ progressPercentage }}%
                                        </span>
                                    </div>
                                    <div
                                        class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-3"
                                    >
                                        <div
                                            class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-500"
                                            :style="{
                                                width: progressPercentage + '%',
                                            }"
                                        ></div>
                                    </div>
                                </div>

                                <!-- Cuotas Info -->
                                <div
                                    class="mt-4 grid grid-cols-3 gap-2 text-center"
                                >
                                    <div
                                        class="bg-green-50 dark:bg-green-900/20 rounded-lg p-2"
                                    >
                                        <p
                                            class="text-lg font-bold text-green-600"
                                        >
                                            {{ resumen.cuotas_pagadas }}
                                        </p>
                                        <p
                                            class="text-xs text-gray-600 dark:text-gray-400"
                                        >
                                            Pagadas
                                        </p>
                                    </div>
                                    <div
                                        class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-2"
                                    >
                                        <p
                                            class="text-lg font-bold text-yellow-600"
                                        >
                                            {{ resumen.cuotas_pendientes }}
                                        </p>
                                        <p
                                            class="text-xs text-gray-600 dark:text-gray-400"
                                        >
                                            Pendientes
                                        </p>
                                    </div>
                                    <div
                                        class="bg-red-50 dark:bg-red-900/20 rounded-lg p-2"
                                    >
                                        <p
                                            class="text-lg font-bold text-red-600"
                                        >
                                            {{ resumen.cuotas_vencidas }}
                                        </p>
                                        <p
                                            class="text-xs text-gray-600 dark:text-gray-400"
                                        >
                                            Vencidas
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info del Plan -->
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                        >
                            <h4
                                class="font-medium text-gray-900 dark:text-white mb-3"
                            >
                                Detalles del Plan
                            </h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span
                                        class="text-gray-600 dark:text-gray-400"
                                        >Cantidad de Cuotas:</span
                                    >
                                    <span
                                        class="font-medium text-gray-900 dark:text-white"
                                    >
                                        {{ planPago.cantidad_cuotas }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span
                                        class="text-gray-600 dark:text-gray-400"
                                        >Tasa de Interés:</span
                                    >
                                    <span
                                        class="font-medium text-gray-900 dark:text-white"
                                    >
                                        {{ planPago.tasa_interes }}%
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span
                                        class="text-gray-600 dark:text-gray-400"
                                        >Día de Vencimiento:</span
                                    >
                                    <span
                                        class="font-medium text-gray-900 dark:text-white"
                                    >
                                        Día
                                        {{
                                            planPago.dia_vencimiento_mensual
                                        }}
                                        de cada mes
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span
                                        class="text-gray-600 dark:text-gray-400"
                                        >Fecha de Venta:</span
                                    >
                                    <span
                                        class="font-medium text-gray-900 dark:text-white"
                                    >
                                        {{
                                            formatShortDate(
                                                planPago.venta.fecha_venta
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha: Cronograma de Cuotas -->
                    <div class="lg:col-span-2">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-lg shadow"
                        >
                            <div
                                class="px-6 py-4 border-b border-gray-200 dark:border-gray-700"
                            >
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-white flex items-center"
                                >
                                    <CalendarDaysIcon
                                        class="h-5 w-5 mr-2 text-purple-600"
                                    />
                                    Cronograma de Cuotas
                                </h3>
                            </div>

                            <div
                                class="divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <div
                                    v-for="cuota in planPago.cuotas"
                                    :key="cuota.id"
                                    class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                                    :class="{
                                        'bg-red-50 dark:bg-red-900/10':
                                            cuota.estado_cuota === 'VENCIDO',
                                        'bg-green-50 dark:bg-green-900/10':
                                            cuota.estado_cuota === 'PAGADO',
                                    }"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div
                                            class="flex items-center space-x-4"
                                        >
                                            <!-- Número de cuota -->
                                            <div
                                                class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center text-white font-bold"
                                                :class="{
                                                    'bg-green-500':
                                                        cuota.estado_cuota ===
                                                        'PAGADO',
                                                    'bg-yellow-500':
                                                        cuota.estado_cuota ===
                                                        'PENDIENTE',
                                                    'bg-red-500':
                                                        cuota.estado_cuota ===
                                                        'VENCIDO',
                                                }"
                                            >
                                                {{ cuota.numero_cuota }}
                                            </div>

                                            <div>
                                                <div
                                                    class="flex items-center space-x-2"
                                                >
                                                    <span
                                                        class="font-medium text-gray-900 dark:text-white"
                                                    >
                                                        Cuota
                                                        {{
                                                            cuota.numero_cuota
                                                        }}
                                                        de
                                                        {{
                                                            planPago.cantidad_cuotas
                                                        }}
                                                    </span>
                                                    <span
                                                        :class="
                                                            getEstadoCuotaClass(
                                                                cuota.estado_cuota
                                                            )
                                                        "
                                                        class="px-2 py-0.5 text-xs font-medium rounded-full border"
                                                    >
                                                        {{ cuota.estado_cuota }}
                                                    </span>
                                                </div>
                                                <div
                                                    class="flex items-center text-sm text-gray-500 dark:text-gray-400 mt-1"
                                                >
                                                    <CalendarDaysIcon
                                                        class="h-4 w-4 mr-1"
                                                    />
                                                    Vence:
                                                    {{
                                                        formatDate(
                                                            cuota.fecha_vencimiento
                                                        )
                                                    }}
                                                    <template
                                                        v-if="
                                                            cuota.estado_cuota ===
                                                            'PENDIENTE'
                                                        "
                                                    >
                                                        <span
                                                            class="ml-2 px-2 py-0.5 rounded text-xs"
                                                            :class="
                                                                diasRestantes(
                                                                    cuota.fecha_vencimiento
                                                                ) < 0
                                                                    ? 'bg-red-100 text-red-700'
                                                                    : diasRestantes(
                                                                          cuota.fecha_vencimiento
                                                                      ) <= 7
                                                                    ? 'bg-yellow-100 text-yellow-700'
                                                                    : 'bg-gray-100 text-gray-700'
                                                            "
                                                        >
                                                            {{
                                                                diasRestantes(
                                                                    cuota.fecha_vencimiento
                                                                ) < 0
                                                                    ? `Vencida hace ${Math.abs(
                                                                          diasRestantes(
                                                                              cuota.fecha_vencimiento
                                                                          )
                                                                      )} días`
                                                                    : `${diasRestantes(
                                                                          cuota.fecha_vencimiento
                                                                      )} días restantes`
                                                            }}
                                                        </span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <div
                                                class="text-lg font-bold text-gray-900 dark:text-white"
                                            >
                                                {{
                                                    formatMoney(
                                                        cuota.monto_total_cuota
                                                    )
                                                }}
                                            </div>
                                            <div
                                                class="text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                Capital:
                                                {{
                                                    formatMoney(
                                                        cuota.monto_capital
                                                    )
                                                }}
                                                | Interés:
                                                {{
                                                    formatMoney(
                                                        cuota.monto_interes
                                                    )
                                                }}
                                            </div>
                                            <div
                                                v-if="
                                                    calcularSaldoCuota(cuota) <
                                                        cuota.monto_total_cuota &&
                                                    cuota.estado_cuota !==
                                                        'PAGADO'
                                                "
                                                class="text-xs text-blue-600 mt-1"
                                            >
                                                Pagado:
                                                {{
                                                    formatMoney(
                                                        cuota.monto_total_cuota -
                                                            calcularSaldoCuota(
                                                                cuota
                                                            )
                                                    )
                                                }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pagos de esta cuota -->
                                    <div
                                        v-if="
                                            cuota.pagos &&
                                            cuota.pagos.length > 0
                                        "
                                        class="mt-3 ml-16"
                                    >
                                        <div
                                            class="text-xs text-gray-500 dark:text-gray-400 mb-2"
                                        >
                                            Pagos realizados:
                                        </div>
                                        <div class="space-y-1">
                                            <div
                                                v-for="pago in cuota.pagos"
                                                :key="pago.id"
                                                class="flex items-center justify-between text-sm bg-green-50 dark:bg-green-900/20 rounded px-3 py-1"
                                            >
                                                <span
                                                    class="text-gray-600 dark:text-gray-400"
                                                >
                                                    {{
                                                        formatShortDate(
                                                            pago.fecha_pago
                                                        )
                                                    }}
                                                    - {{ pago.metodo_pago }}
                                                </span>
                                                <span
                                                    class="font-medium text-green-600"
                                                >
                                                    {{
                                                        formatMoney(
                                                            pago.monto_pagado
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botón de pagar -->
                                    <div
                                        v-if="puedeRecibirPago(cuota)"
                                        class="mt-3 ml-16"
                                    >
                                        <button
                                            @click="openPaymentModal(cuota)"
                                            class="inline-flex items-center px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md transition"
                                        >
                                            <CurrencyDollarIcon
                                                class="h-4 w-4 mr-1"
                                            />
                                            Registrar Pago
                                            <span
                                                v-if="
                                                    calcularSaldoCuota(cuota) <
                                                    cuota.monto_total_cuota
                                                "
                                                class="ml-1"
                                            >
                                                (Saldo:
                                                {{
                                                    formatMoney(
                                                        calcularSaldoCuota(
                                                            cuota
                                                        )
                                                    )
                                                }})
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Historial de todos los pagos -->
                        <div
                            v-if="
                                planPago.venta.pagos &&
                                planPago.venta.pagos.length > 0
                            "
                            class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow"
                        >
                            <div
                                class="px-6 py-4 border-b border-gray-200 dark:border-gray-700"
                            >
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-white flex items-center"
                                >
                                    <BanknotesIcon
                                        class="h-5 w-5 mr-2 text-green-600"
                                    />
                                    Historial de Pagos
                                </h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table
                                    class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                                >
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                            >
                                                Fecha
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                            >
                                                Cuota
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                            >
                                                Método
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                            >
                                                Referencia
                                            </th>
                                            <th
                                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"
                                            >
                                                Monto
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-200 dark:divide-gray-700"
                                    >
                                        <tr
                                            v-for="pago in planPago.venta.pagos"
                                            :key="pago.id"
                                        >
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"
                                            >
                                                {{
                                                    formatShortDate(
                                                        pago.fecha_pago
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                {{
                                                    pago.cuota_id
                                                        ? `Cuota #${
                                                              planPago.cuotas.find(
                                                                  (c) =>
                                                                      c.id ===
                                                                      pago.cuota_id
                                                              )?.numero_cuota ||
                                                              pago.cuota_id
                                                          }`
                                                        : "Pago inicial"
                                                }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                {{ pago.metodo_pago }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                {{
                                                    pago.referencia_transaccion ||
                                                    "-"
                                                }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-green-600"
                                            >
                                                {{
                                                    formatMoney(
                                                        pago.monto_pagado
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <td
                                                colspan="4"
                                                class="px-6 py-3 text-right text-sm font-medium text-gray-900 dark:text-white"
                                            >
                                                Total Pagado:
                                            </td>
                                            <td
                                                class="px-6 py-3 text-right text-sm font-bold text-green-600"
                                            >
                                                {{
                                                    formatMoney(
                                                        resumen.total_pagado
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Pago -->
        <Teleport to="body">
            <div
                v-if="showPaymentModal"
                class="fixed inset-0 z-50 overflow-y-auto"
            >
                <div
                    class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0"
                >
                    <div
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                        @click="closePaymentModal"
                    ></div>

                    <div
                        class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all sm:max-w-lg sm:w-full"
                    >
                        <div
                            class="px-6 py-4 border-b border-gray-200 dark:border-gray-700"
                        >
                            <div class="flex items-center justify-between">
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-white"
                                >
                                    Registrar Pago - Cuota
                                    {{ selectedCuota?.numero_cuota }}
                                </h3>
                                <button
                                    @click="closePaymentModal"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                >
                                    <XMarkIcon class="h-6 w-6" />
                                </button>
                            </div>
                        </div>

                        <form @submit.prevent="submitPayment" class="p-6">
                            <div class="space-y-4">
                                <!-- Info de la cuota -->
                                <div
                                    class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4"
                                >
                                    <div class="flex justify-between text-sm">
                                        <span
                                            class="text-gray-600 dark:text-gray-400"
                                            >Monto de la cuota:</span
                                        >
                                        <span
                                            class="font-medium text-gray-900 dark:text-white"
                                        >
                                            {{
                                                formatMoney(
                                                    selectedCuota?.monto_total_cuota
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <div
                                        class="flex justify-between text-sm mt-1"
                                    >
                                        <span
                                            class="text-gray-600 dark:text-gray-400"
                                            >Saldo pendiente:</span
                                        >
                                        <span class="font-medium text-red-600">
                                            {{
                                                formatMoney(
                                                    calcularSaldoCuota(
                                                        selectedCuota
                                                    )
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Monto -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                    >
                                        Monto a Pagar *
                                    </label>
                                    <div class="relative">
                                        <span
                                            class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"
                                        >
                                            Bs.
                                        </span>
                                        <input
                                            v-model="paymentForm.monto"
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            :max="
                                                calcularSaldoCuota(
                                                    selectedCuota
                                                )
                                            "
                                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white"
                                            required
                                        />
                                    </div>
                                    <p
                                        v-if="paymentForm.errors.monto"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ paymentForm.errors.monto }}
                                    </p>
                                </div>

                                <!-- Método de pago -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                    >
                                        Método de Pago *
                                    </label>
                                    <select
                                        v-model="paymentForm.metodo_pago"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white"
                                        required
                                    >
                                        <option
                                            v-for="metodo in metodosPago"
                                            :key="metodo.value"
                                            :value="metodo.value"
                                        >
                                            {{ metodo.label }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="paymentForm.errors.metodo_pago"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ paymentForm.errors.metodo_pago }}
                                    </p>
                                </div>

                                <!-- Referencia -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                    >
                                        Referencia / Nro. Transacción
                                    </label>
                                    <input
                                        v-model="paymentForm.referencia"
                                        type="text"
                                        placeholder="Ej: TRX-123456"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end space-x-3">
                                <button
                                    type="button"
                                    @click="closePaymentModal"
                                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    :disabled="paymentForm.processing"
                                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="paymentForm.processing"
                                        >Procesando...</span
                                    >
                                    <span v-else>Registrar Pago</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
