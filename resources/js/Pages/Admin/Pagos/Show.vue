<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { useAppUrl } from "@/Composables/useAppUrl";
import {
    ArrowLeftIcon,
    BanknotesIcon,
    CreditCardIcon,
    QrCodeIcon,
    BuildingLibraryIcon,
    CheckCircleIcon,
    DocumentTextIcon,
    UserIcon,
    MapPinIcon,
    CalendarDaysIcon,
    TicketIcon,
    PrinterIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    pago: Object,
    metodosPago: Array,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-BO", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleDateString("es-BO", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value || 0);
};

const getMetodoLabel = (metodo) => {
    const obj = props.metodosPago.find((m) => m.value === metodo);
    return obj?.label || metodo;
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

const getMetodoBgClass = (metodo) => {
    const classes = {
        EFECTIVO: "from-green-500 to-emerald-600",
        TARJETA: "from-blue-500 to-indigo-600",
        QR: "from-purple-500 to-pink-600",
        TRANSFERENCIA: "from-orange-500 to-amber-600",
    };
    return classes[metodo] || "from-gray-500 to-gray-600";
};

const { resolveUrl } = useAppUrl();
</script>

<template>
    <Head :title="`Pago #${pago.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <Link
                        :href="resolveUrl('pagos')"
                        class="mr-4 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                    >
                        <ArrowLeftIcon class="h-5 w-5 text-gray-500" />
                    </Link>
                    <BanknotesIcon class="h-6 w-6 mr-2 text-green-600" />
                    <h2
                        class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                    >
                        Pago #{{ pago.id }}
                    </h2>
                </div>
                <div class="flex space-x-2">
                    <a
                        :href="resolveUrl('pagos/' + pago.id + '/comprobante')"
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition"
                    >
                        <DocumentTextIcon class="h-4 w-4 mr-1" />
                        Descargar Comprobante
                    </a>
                    <button
                        @click="window.print()"
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                    >
                        <PrinterIcon class="h-4 w-4 mr-1" />
                        Imprimir
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Tarjeta principal del pago -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6"
                >
                    <div
                        :class="[
                            'bg-gradient-to-r p-8 text-white text-center',
                            getMetodoBgClass(pago.metodo_pago),
                        ]"
                    >
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4"
                        >
                            <CheckCircleIcon class="h-10 w-10" />
                        </div>
                        <p
                            class="text-white/80 text-sm uppercase tracking-wider mb-2"
                        >
                            Pago Exitoso
                        </p>
                        <p class="text-4xl font-bold mb-2">
                            {{ formatCurrency(pago.monto_pagado) }}
                        </p>
                        <div
                            class="inline-flex items-center bg-white/20 px-4 py-2 rounded-full"
                        >
                            <component
                                :is="getMetodoIcon(pago.metodo_pago)"
                                class="h-5 w-5 mr-2"
                            />
                            {{ getMetodoLabel(pago.metodo_pago) }}
                        </div>
                        <p class="mt-4 text-white/70 text-sm">
                            {{ formatDateTime(pago.fecha_pago) }}
                        </p>
                    </div>

                    <div class="p-6">
                        <!-- Referencia si existe -->
                        <div
                            v-if="pago.referencia_comprobante"
                            class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center"
                        >
                            <p
                                class="text-sm text-gray-500 dark:text-gray-400 mb-1"
                            >
                                Número de Referencia
                            </p>
                            <p
                                class="text-lg font-mono font-bold text-gray-900 dark:text-gray-100"
                            >
                                {{ pago.referencia_comprobante }}
                            </p>
                        </div>

                        <!-- Información de la cuota (si aplica) -->
                        <div
                            v-if="pago.cuota"
                            class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
                        >
                            <h4
                                class="font-semibold text-blue-800 dark:text-blue-200 mb-2 flex items-center"
                            >
                                <CalendarDaysIcon class="h-5 w-5 mr-2" />
                                Pago de Cuota
                            </h4>
                            <div class="grid grid-cols-3 gap-4 text-sm">
                                <div>
                                    <p class="text-blue-600 dark:text-blue-400">
                                        Número de Cuota
                                    </p>
                                    <p
                                        class="font-medium text-blue-900 dark:text-blue-100"
                                    >
                                        #{{ pago.cuota.numero_cuota }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-blue-600 dark:text-blue-400">
                                        Monto de la Cuota
                                    </p>
                                    <p
                                        class="font-medium text-blue-900 dark:text-blue-100"
                                    >
                                        {{
                                            formatCurrency(
                                                pago.cuota.monto_total_cuota
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-blue-600 dark:text-blue-400">
                                        Vencimiento
                                    </p>
                                    <p
                                        class="font-medium text-blue-900 dark:text-blue-100"
                                    >
                                        {{
                                            formatDate(
                                                pago.cuota.fecha_vencimiento
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Datos del Cliente -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <h4
                                    class="font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center"
                                >
                                    <UserIcon
                                        class="h-5 w-5 mr-2 text-indigo-500"
                                    />
                                    Datos del Cliente
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span
                                            class="text-gray-500 dark:text-gray-400"
                                            >Nombre:</span
                                        >
                                        <span
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                            >{{
                                                pago.venta?.cliente?.name
                                            }}</span
                                        >
                                    </div>
                                    <div class="flex justify-between">
                                        <span
                                            class="text-gray-500 dark:text-gray-400"
                                            >Email:</span
                                        >
                                        <span
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                            >{{
                                                pago.venta?.cliente?.email
                                            }}</span
                                        >
                                    </div>
                                    <div
                                        v-if="pago.venta?.cliente?.telefono"
                                        class="flex justify-between"
                                    >
                                        <span
                                            class="text-gray-500 dark:text-gray-400"
                                            >Teléfono:</span
                                        >
                                        <span
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                            >{{
                                                pago.venta?.cliente?.telefono
                                            }}</span
                                        >
                                    </div>
                                    <div
                                        v-if="pago.venta?.cliente?.ci_nit"
                                        class="flex justify-between"
                                    >
                                        <span
                                            class="text-gray-500 dark:text-gray-400"
                                            >CI/NIT:</span
                                        >
                                        <span
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                            >{{
                                                pago.venta?.cliente?.ci_nit
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4
                                    class="font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center"
                                >
                                    <MapPinIcon
                                        class="h-5 w-5 mr-2 text-green-500"
                                    />
                                    Información del Viaje
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span
                                            class="text-gray-500 dark:text-gray-400"
                                            >Viaje:</span
                                        >
                                        <span
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                            >{{
                                                pago.venta?.viaje?.plan_viaje
                                                    ?.nombre
                                            }}</span
                                        >
                                    </div>
                                    <div class="flex justify-between">
                                        <span
                                            class="text-gray-500 dark:text-gray-400"
                                            >Destino:</span
                                        >
                                        <span
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                            >{{
                                                pago.venta?.viaje?.plan_viaje
                                                    ?.destino?.nombre_lugar
                                            }}</span
                                        >
                                    </div>
                                    <div class="flex justify-between">
                                        <span
                                            class="text-gray-500 dark:text-gray-400"
                                            >Fecha salida:</span
                                        >
                                        <span
                                            class="font-medium text-gray-900 dark:text-gray-100"
                                            >{{
                                                formatDate(
                                                    pago.venta?.viaje
                                                        ?.fecha_salida
                                                )
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estado de la venta -->
                        <div
                            class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg"
                        >
                            <h4
                                class="font-semibold text-gray-900 dark:text-gray-100 mb-3"
                            >
                                Estado de la Venta
                            </h4>
                            <div class="grid grid-cols-4 gap-4 text-center">
                                <div>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Nº Venta
                                    </p>
                                    <p
                                        class="text-lg font-bold text-gray-900 dark:text-gray-100"
                                    >
                                        #{{ pago.venta?.id }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Total Venta
                                    </p>
                                    <p
                                        class="text-lg font-bold text-gray-900 dark:text-gray-100"
                                    >
                                        {{
                                            formatCurrency(
                                                pago.venta?.monto_total
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Pagado
                                    </p>
                                    <p
                                        class="text-lg font-bold text-green-600 dark:text-green-400"
                                    >
                                        {{
                                            formatCurrency(
                                                pago.venta?.monto_pagado ||
                                                    pago.monto_pagado
                                            )
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Pendiente
                                    </p>
                                    <p
                                        class="text-lg font-bold"
                                        :class="
                                            (pago.venta?.saldo_pendiente || 0) >
                                            0
                                                ? 'text-red-600 dark:text-red-400'
                                                : 'text-green-600 dark:text-green-400'
                                        "
                                    >
                                        {{
                                            formatCurrency(
                                                pago.venta?.saldo_pendiente || 0
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones adicionales -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link
                        :href="resolveUrl('ventas/' + pago.venta?.id)"
                        class="inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        <TicketIcon class="h-5 w-5 mr-2" />
                        Ver Detalle de Venta
                    </Link>
                    <Link
                        :href="resolveUrl('pagos/historial/' + pago.venta?.id)"
                        class="inline-flex items-center justify-center px-6 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        <BanknotesIcon class="h-5 w-5 mr-2" />
                        Ver Historial de Pagos
                    </Link>
                    <Link
                        :href="resolveUrl('pagos/create')"
                        class="inline-flex items-center justify-center px-6 py-3 bg-green-600 rounded-lg font-semibold text-white hover:bg-green-700 transition"
                    >
                        <BanknotesIcon class="h-5 w-5 mr-2" />
                        Registrar Otro Pago
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
