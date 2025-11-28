<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { useAppUrl } from "@/Composables/useAppUrl";
import {
    CalendarDaysIcon,
    ArrowLeftIcon,
    MapPinIcon,
    UsersIcon,
    CurrencyDollarIcon,
    EnvelopeIcon,
    PhoneIcon,
    TicketIcon,
    CheckCircleIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    viaje: Object,
    ventas: Array,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-BO", {
        weekday: "short",
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "USD",
    }).format(value);
};

const getEstadoPagoClasses = (estado) => {
    const colorMap = {
        PENDIENTE:
            "bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200",
        PARCIAL:
            "bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200",
        COMPLETADO:
            "bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200",
    };
    return colorMap[estado] || "bg-gray-100 text-gray-800";
};

const getTipoPagoClasses = (tipo) => {
    return tipo === "CONTADO"
        ? "bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200"
        : "bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200";
};

// Estadísticas de pasajeros
const totalPasajeros = props.ventas.length;
const totalIngresos = props.ventas.reduce(
    (sum, v) => sum + parseFloat(v.monto_total),
    0
);
const pagosCompletados = props.ventas.filter(
    (v) => v.estado_pago === "COMPLETADO"
).length;

const { resolveUrl } = useAppUrl();
</script>

<template>
    <Head :title="`Pasajeros: ${viaje.plan_viaje?.nombre}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <UsersIcon class="h-6 w-6 mr-2 text-indigo-600" />
                    <h2
                        class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                    >
                        Lista de Pasajeros
                    </h2>
                </div>
                <Link
                    :href="resolveUrl('viajes/' + viaje.id)"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-600 transition ease-in-out duration-150"
                >
                    Ver Viaje
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <div class="mb-4">
                    <Link
                        :href="resolveUrl('viajes')"
                        class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400"
                    >
                        <ArrowLeftIcon class="h-4 w-4 mr-1" />
                        Volver a la lista de viajes
                    </Link>
                </div>

                <!-- Info del Viaje -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6"
                >
                    <div class="p-6">
                        <div
                            class="flex flex-col md:flex-row md:items-center md:justify-between"
                        >
                            <div>
                                <h3
                                    class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ viaje.plan_viaje?.nombre }}
                                </h3>
                                <div
                                    class="flex items-center mt-1 text-gray-500 dark:text-gray-400"
                                >
                                    <MapPinIcon class="h-4 w-4 mr-1" />
                                    {{
                                        viaje.plan_viaje?.destino?.nombre_lugar
                                    }}, {{ viaje.plan_viaje?.destino?.ciudad }}
                                </div>
                                <div
                                    class="flex items-center mt-1 text-gray-500 dark:text-gray-400"
                                >
                                    <CalendarDaysIcon class="h-4 w-4 mr-1" />
                                    {{ formatDate(viaje.fecha_salida) }} -
                                    {{ formatDate(viaje.fecha_retorno) }}
                                </div>
                            </div>
                            <div
                                class="mt-4 md:mt-0 flex items-center space-x-4"
                            >
                                <div class="text-center">
                                    <p
                                        class="text-2xl font-bold text-indigo-600 dark:text-indigo-400"
                                    >
                                        {{
                                            viaje.cupos_totales -
                                            viaje.cupos_disponibles
                                        }}
                                    </p>
                                    <p
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        Pasajeros
                                    </p>
                                </div>
                                <div class="text-center">
                                    <p
                                        class="text-2xl font-bold text-green-600 dark:text-green-400"
                                    >
                                        {{ viaje.cupos_disponibles }}
                                    </p>
                                    <p
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        Disponibles
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 p-3 bg-indigo-100 dark:bg-indigo-900 rounded-full"
                            >
                                <UsersIcon
                                    class="h-6 w-6 text-indigo-600 dark:text-indigo-400"
                                />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Total Pasajeros
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ totalPasajeros }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 p-3 bg-green-100 dark:bg-green-900 rounded-full"
                            >
                                <CurrencyDollarIcon
                                    class="h-6 w-6 text-green-600 dark:text-green-400"
                                />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Ingresos Totales
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ formatCurrency(totalIngresos) }}
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
                                <CheckCircleIcon
                                    class="h-6 w-6 text-purple-600 dark:text-purple-400"
                                />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Pagos Completados
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ pagosCompletados }} /
                                    {{ totalPasajeros }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Pasajeros -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Pasajero
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Contacto
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Vendedor
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Tipo Pago
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Monto
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Estado Pago
                                    </th>
                                    <th
                                        scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Fecha Venta
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <tr
                                    v-for="venta in ventas"
                                    :key="venta.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center"
                                            >
                                                <span
                                                    class="text-white font-medium text-sm"
                                                >
                                                    {{
                                                        venta.cliente?.name
                                                            ?.charAt(0)
                                                            ?.toUpperCase()
                                                    }}
                                                </span>
                                            </div>
                                            <div class="ml-4">
                                                <div
                                                    class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                                >
                                                    {{ venta.cliente?.name }}
                                                </div>
                                                <div
                                                    class="text-sm text-gray-500 dark:text-gray-400"
                                                >
                                                    ID: {{ venta.cliente?.id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="flex items-center text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            <EnvelopeIcon
                                                class="h-4 w-4 mr-1"
                                            />
                                            {{ venta.cliente?.email }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm text-gray-900 dark:text-gray-100"
                                        >
                                            {{ venta.vendedor?.name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                getTipoPagoClasses(
                                                    venta.tipo_pago
                                                ),
                                            ]"
                                        >
                                            {{ venta.tipo_pago }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{
                                                formatCurrency(
                                                    venta.monto_total
                                                )
                                            }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                getEstadoPagoClasses(
                                                    venta.estado_pago
                                                ),
                                            ]"
                                        >
                                            {{ venta.estado_pago }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        {{ formatDate(venta.fecha_venta) }}
                                    </td>
                                </tr>
                                <tr v-if="ventas.length === 0">
                                    <td
                                        colspan="7"
                                        class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"
                                    >
                                        <UsersIcon
                                            class="h-12 w-12 mx-auto mb-4 text-gray-300 dark:text-gray-600"
                                        />
                                        <p class="text-lg font-medium">
                                            No hay pasajeros registrados
                                        </p>
                                        <p class="text-sm mt-1">
                                            Aún no se han registrado ventas para
                                            este viaje
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
