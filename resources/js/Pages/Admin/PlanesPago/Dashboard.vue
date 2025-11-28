<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    ChartBarIcon,
    ExclamationTriangleIcon,
    ClockIcon,
    CreditCardIcon,
    BanknotesIcon,
    CalendarDaysIcon,
    ArrowLeftIcon,
    UserIcon,
    MapPinIcon,
    EyeIcon,
    CheckCircleIcon,
    CurrencyDollarIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    cuotasVencidas: Array,
    cuotasProximas: Array,
    stats: Object,
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
        month: "short",
        year: "numeric",
    });
};

const diasVencida = (fecha) => {
    const hoy = new Date();
    const vencimiento = new Date(fecha);
    return Math.ceil((hoy - vencimiento) / (1000 * 60 * 60 * 24));
};

const diasRestantes = (fecha) => {
    const hoy = new Date();
    const vencimiento = new Date(fecha);
    return Math.ceil((vencimiento - hoy) / (1000 * 60 * 60 * 24));
};
</script>

<template>
    <Head title="Dashboard de Cobranzas" />

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
                    <ChartBarIcon class="h-8 w-8 text-purple-600" />
                    <h2
                        class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
                    >
                        Dashboard de Cobranzas
                    </h2>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Estadísticas Principales -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6"
                >
                    <!-- Total Planes -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border-l-4 border-purple-500"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Total Planes de Pago
                                </p>
                                <p
                                    class="text-3xl font-bold text-gray-900 dark:text-white mt-1"
                                >
                                    {{ stats.total_planes }}
                                </p>
                            </div>
                            <div
                                class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full"
                            >
                                <CreditCardIcon
                                    class="h-8 w-8 text-purple-600 dark:text-purple-400"
                                />
                            </div>
                        </div>
                        <div class="mt-3 flex items-center text-sm">
                            <span class="text-green-600 font-medium">{{
                                stats.planes_completados
                            }}</span>
                            <span class="text-gray-500 dark:text-gray-400 ml-1"
                                >completados</span
                            >
                            <span class="mx-2 text-gray-300">|</span>
                            <span class="text-blue-600 font-medium">{{
                                stats.planes_activos
                            }}</span>
                            <span class="text-gray-500 dark:text-gray-400 ml-1"
                                >activos</span
                            >
                        </div>
                    </div>

                    <!-- Monto Pendiente -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border-l-4 border-yellow-500"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Total Por Cobrar
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 dark:text-white mt-1"
                                >
                                    {{
                                        formatMoney(stats.monto_total_pendiente)
                                    }}
                                </p>
                            </div>
                            <div
                                class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full"
                            >
                                <BanknotesIcon
                                    class="h-8 w-8 text-yellow-600 dark:text-yellow-400"
                                />
                            </div>
                        </div>
                        <div
                            class="mt-3 text-sm text-gray-500 dark:text-gray-400"
                        >
                            En cuotas pendientes y vencidas
                        </div>
                    </div>

                    <!-- Monto Vencido -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border-l-4 border-red-500"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Monto Vencido
                                </p>
                                <p
                                    class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1"
                                >
                                    {{ formatMoney(stats.monto_vencido) }}
                                </p>
                            </div>
                            <div
                                class="p-3 bg-red-100 dark:bg-red-900 rounded-full"
                            >
                                <ExclamationTriangleIcon
                                    class="h-8 w-8 text-red-600 dark:text-red-400"
                                />
                            </div>
                        </div>
                        <div class="mt-3 text-sm">
                            <span class="text-red-600 font-medium">{{
                                stats.cuotas_vencidas
                            }}</span>
                            <span class="text-gray-500 dark:text-gray-400 ml-1"
                                >cuotas vencidas</span
                            >
                        </div>
                    </div>

                    <!-- Recaudación del Mes -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border-l-4 border-green-500"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Recaudación del Mes
                                </p>
                                <p
                                    class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1"
                                >
                                    {{ formatMoney(stats.recaudacion_mes) }}
                                </p>
                            </div>
                            <div
                                class="p-3 bg-green-100 dark:bg-green-900 rounded-full"
                            >
                                <CurrencyDollarIcon
                                    class="h-8 w-8 text-green-600 dark:text-green-400"
                                />
                            </div>
                        </div>
                        <div
                            class="mt-3 text-sm text-gray-500 dark:text-gray-400"
                        >
                            En pagos de cuotas
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Cuotas Vencidas -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                        <div
                            class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-red-50 dark:bg-red-900/20 rounded-t-lg"
                        >
                            <div class="flex items-center justify-between">
                                <h3
                                    class="text-lg font-semibold text-red-800 dark:text-red-200 flex items-center"
                                >
                                    <ExclamationTriangleIcon
                                        class="h-5 w-5 mr-2"
                                    />
                                    Cuotas Vencidas
                                    <span
                                        class="ml-2 px-2 py-0.5 text-sm bg-red-200 dark:bg-red-800 text-red-800 dark:text-red-200 rounded-full"
                                    >
                                        {{ cuotasVencidas.length }}
                                    </span>
                                </h3>
                            </div>
                        </div>

                        <div
                            class="divide-y divide-gray-200 dark:divide-gray-700 max-h-96 overflow-y-auto"
                        >
                            <div
                                v-for="cuota in cuotasVencidas"
                                :key="cuota.id"
                                class="p-4 hover:bg-red-50 dark:hover:bg-red-900/10 transition"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-3">
                                        <div
                                            class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center"
                                        >
                                            <span
                                                class="text-sm font-bold text-red-600 dark:text-red-400"
                                            >
                                                {{ cuota.numero_cuota }}
                                            </span>
                                        </div>
                                        <div>
                                            <p
                                                class="font-medium text-gray-900 dark:text-white"
                                            >
                                                {{
                                                    cuota.plan_pago?.venta
                                                        ?.cliente?.name ||
                                                    "Cliente"
                                                }}
                                            </p>
                                            <p
                                                class="text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                {{
                                                    cuota.plan_pago?.venta
                                                        ?.viaje?.plan_viaje
                                                        ?.destino?.nombre ||
                                                    "Viaje"
                                                }}
                                            </p>
                                            <p
                                                class="text-xs text-red-600 dark:text-red-400 mt-1"
                                            >
                                                Vencida hace
                                                {{
                                                    diasVencida(
                                                        cuota.fecha_vencimiento
                                                    )
                                                }}
                                                días ({{
                                                    formatDate(
                                                        cuota.fecha_vencimiento
                                                    )
                                                }})
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p
                                            class="font-bold text-red-600 dark:text-red-400"
                                        >
                                            {{
                                                formatMoney(
                                                    cuota.monto_total_cuota
                                                )
                                            }}
                                        </p>
                                        <Link
                                            v-if="cuota.plan_pago"
                                            :href="
                                                '/planes-pago/' +
                                                cuota.plan_pago.id
                                            "
                                            class="text-xs text-purple-600 hover:text-purple-800 flex items-center justify-end mt-1"
                                        >
                                            <EyeIcon class="h-3 w-3 mr-1" />
                                            Ver plan
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty state -->
                            <div
                                v-if="cuotasVencidas.length === 0"
                                class="p-8 text-center"
                            >
                                <CheckCircleIcon
                                    class="mx-auto h-12 w-12 text-green-400"
                                />
                                <p
                                    class="mt-2 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    ¡Excelente! No hay cuotas vencidas
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Cuotas Próximas a Vencer -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                        <div
                            class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-yellow-50 dark:bg-yellow-900/20 rounded-t-lg"
                        >
                            <div class="flex items-center justify-between">
                                <h3
                                    class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 flex items-center"
                                >
                                    <ClockIcon class="h-5 w-5 mr-2" />
                                    Próximas a Vencer (7 días)
                                    <span
                                        class="ml-2 px-2 py-0.5 text-sm bg-yellow-200 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200 rounded-full"
                                    >
                                        {{ cuotasProximas.length }}
                                    </span>
                                </h3>
                            </div>
                        </div>

                        <div
                            class="divide-y divide-gray-200 dark:divide-gray-700 max-h-96 overflow-y-auto"
                        >
                            <div
                                v-for="cuota in cuotasProximas"
                                :key="cuota.id"
                                class="p-4 hover:bg-yellow-50 dark:hover:bg-yellow-900/10 transition"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-3">
                                        <div
                                            class="flex-shrink-0 w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900 flex items-center justify-center"
                                        >
                                            <span
                                                class="text-sm font-bold text-yellow-600 dark:text-yellow-400"
                                            >
                                                {{ cuota.numero_cuota }}
                                            </span>
                                        </div>
                                        <div>
                                            <p
                                                class="font-medium text-gray-900 dark:text-white"
                                            >
                                                {{
                                                    cuota.plan_pago?.venta
                                                        ?.cliente?.name ||
                                                    "Cliente"
                                                }}
                                            </p>
                                            <p
                                                class="text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                {{
                                                    cuota.plan_pago?.venta
                                                        ?.viaje?.plan_viaje
                                                        ?.destino?.nombre ||
                                                    "Viaje"
                                                }}
                                            </p>
                                            <p
                                                class="text-xs text-yellow-600 dark:text-yellow-400 mt-1 flex items-center"
                                            >
                                                <CalendarDaysIcon
                                                    class="h-3 w-3 mr-1"
                                                />
                                                Vence en
                                                {{
                                                    diasRestantes(
                                                        cuota.fecha_vencimiento
                                                    )
                                                }}
                                                días ({{
                                                    formatDate(
                                                        cuota.fecha_vencimiento
                                                    )
                                                }})
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p
                                            class="font-bold text-yellow-600 dark:text-yellow-400"
                                        >
                                            {{
                                                formatMoney(
                                                    cuota.monto_total_cuota
                                                )
                                            }}
                                        </p>
                                        <Link
                                            v-if="cuota.plan_pago"
                                            :href="
                                                '/planes-pago/' +
                                                cuota.plan_pago.id
                                            "
                                            class="text-xs text-purple-600 hover:text-purple-800 flex items-center justify-end mt-1"
                                        >
                                            <EyeIcon class="h-3 w-3 mr-1" />
                                            Ver plan
                                        </Link>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty state -->
                            <div
                                v-if="cuotasProximas.length === 0"
                                class="p-8 text-center"
                            >
                                <CheckCircleIcon
                                    class="mx-auto h-12 w-12 text-green-400"
                                />
                                <p
                                    class="mt-2 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    No hay cuotas próximas a vencer esta semana
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div
                    class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow p-6"
                >
                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Acciones Rápidas
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <Link
                            :href="'/planes-pago'"
                            class="flex items-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/40 transition"
                        >
                            <CreditCardIcon
                                class="h-8 w-8 text-purple-600 dark:text-purple-400 mr-3"
                            />
                            <div>
                                <p
                                    class="font-medium text-gray-900 dark:text-white"
                                >
                                    Ver Todos los Planes
                                </p>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Gestionar planes de pago
                                </p>
                            </div>
                        </Link>

                        <Link
                            :href="'/planes-pago?con_vencidas=true'"
                            class="flex items-center p-4 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition"
                        >
                            <ExclamationTriangleIcon
                                class="h-8 w-8 text-red-600 dark:text-red-400 mr-3"
                            />
                            <div>
                                <p
                                    class="font-medium text-gray-900 dark:text-white"
                                >
                                    Planes con Vencidas
                                </p>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ stats.cuotas_vencidas }} cuotas por
                                    cobrar
                                </p>
                            </div>
                        </Link>

                        <Link
                            :href="'/ventas?tipo_pago=CREDITO'"
                            class="flex items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition"
                        >
                            <BanknotesIcon
                                class="h-8 w-8 text-blue-600 dark:text-blue-400 mr-3"
                            />
                            <div>
                                <p
                                    class="font-medium text-gray-900 dark:text-white"
                                >
                                    Ventas a Crédito
                                </p>
                                <p
                                    class="text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Ver todas las ventas a crédito
                                </p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
