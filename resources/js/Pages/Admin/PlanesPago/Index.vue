<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import {
    CreditCardIcon,
    MagnifyingGlassIcon,
    EyeIcon,
    FunnelIcon,
    BanknotesIcon,
    CalendarDaysIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    ClockIcon,
    CurrencyDollarIcon,
    UserIcon,
    ChartBarIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    planesPago: Object,
    stats: Object,
    estadosPago: Array,
    opcionesCuotas: Array,
    filters: Object,
});

const search = ref(props.filters.search || "");
const selectedEstado = ref(props.filters.estado || "");
const selectedCuotas = ref(props.filters.cuotas || "");
const conVencidas = ref(props.filters.con_vencidas || false);
const showFilters = ref(false);

// Debounce para búsqueda
let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

const applyFilters = () => {
    router.get(
        "/planes-pago",
        {
            search: search.value || undefined,
            estado: selectedEstado.value || undefined,
            cuotas: selectedCuotas.value || undefined,
            con_vencidas: conVencidas.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const clearFilters = () => {
    search.value = "";
    selectedEstado.value = "";
    selectedCuotas.value = "";
    conVencidas.value = false;
    router.get("/planes-pago");
};

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

const getEstadoColor = (estado) => {
    const colors = {
        PENDIENTE: "bg-yellow-100 text-yellow-800",
        PARCIAL: "bg-blue-100 text-blue-800",
        COMPLETADO: "bg-green-100 text-green-800",
        ANULADO: "bg-red-100 text-red-800",
    };
    return colors[estado] || "bg-gray-100 text-gray-800";
};

const calcularProgreso = (plan) => {
    const pagadas = plan.cuotas.filter(
        (c) => c.estado_cuota === "PAGADO"
    ).length;
    return Math.round((pagadas / plan.cantidad_cuotas) * 100);
};

const tieneCuotasVencidas = (plan) => {
    return plan.cuotas.some(
        (c) =>
            c.estado_cuota === "VENCIDO" ||
            (c.estado_cuota === "PENDIENTE" &&
                new Date(c.fecha_vencimiento) < new Date())
    );
};
</script>

<template>
    <Head title="Planes de Pago" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <CreditCardIcon class="h-8 w-8 text-purple-600" />
                    <h2
                        class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
                    >
                        Planes de Pago (Crédito)
                    </h2>
                </div>
                <Link
                    :href="'/planes-pago/dashboard'"
                    class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 transition"
                >
                    <ChartBarIcon class="h-4 w-4 mr-2" />
                    Dashboard Cobranzas
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Estadísticas -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6"
                >
                    <!-- Total Planes -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-purple-500"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full"
                                >
                                    <CreditCardIcon
                                        class="h-6 w-6 text-purple-600 dark:text-purple-400"
                                    />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Total Planes
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 dark:text-white"
                                >
                                    {{ stats.total_planes }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Planes Activos -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-blue-500"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full"
                                >
                                    <ClockIcon
                                        class="h-6 w-6 text-blue-600 dark:text-blue-400"
                                    />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Planes Activos
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 dark:text-white"
                                >
                                    {{ stats.planes_activos }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Cuotas Vencidas -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-red-500"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="p-3 bg-red-100 dark:bg-red-900 rounded-full"
                                >
                                    <ExclamationTriangleIcon
                                        class="h-6 w-6 text-red-600 dark:text-red-400"
                                    />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Cuotas Vencidas
                                </p>
                                <p
                                    class="text-2xl font-bold text-red-600 dark:text-red-400"
                                >
                                    {{ stats.cuotas_vencidas }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Monto Pendiente -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-yellow-500"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div
                                    class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full"
                                >
                                    <BanknotesIcon
                                        class="h-6 w-6 text-yellow-600 dark:text-yellow-400"
                                    />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Por Cobrar
                                </p>
                                <p
                                    class="text-xl font-bold text-gray-900 dark:text-white"
                                >
                                    {{
                                        formatMoney(stats.monto_total_pendiente)
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alertas si hay cuotas vencidas -->
                <div
                    v-if="stats.cuotas_vencidas > 0"
                    class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4"
                >
                    <div class="flex items-center">
                        <ExclamationTriangleIcon
                            class="h-5 w-5 text-red-500 mr-3"
                        />
                        <div>
                            <p
                                class="text-sm font-medium text-red-800 dark:text-red-200"
                            >
                                ¡Atención! Tienes
                                {{ stats.cuotas_vencidas }} cuota(s) vencida(s)
                                por un total de
                                {{ formatMoney(stats.monto_vencido) }}
                            </p>
                            <button
                                @click="
                                    conVencidas = true;
                                    applyFilters();
                                "
                                class="text-sm text-red-600 hover:text-red-800 underline mt-1"
                            >
                                Ver planes con cuotas vencidas
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6">
                    <div class="p-4">
                        <div
                            class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
                        >
                            <!-- Búsqueda -->
                            <div class="relative flex-1 max-w-md">
                                <MagnifyingGlassIcon
                                    class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400"
                                />
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Buscar por cliente..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white"
                                />
                            </div>

                            <div class="flex items-center gap-2">
                                <button
                                    @click="showFilters = !showFilters"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <FunnelIcon class="h-4 w-4 mr-2" />
                                    Filtros
                                </button>
                                <button
                                    v-if="
                                        search ||
                                        selectedEstado ||
                                        selectedCuotas ||
                                        conVencidas
                                    "
                                    @click="clearFilters"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 hover:text-red-800"
                                >
                                    Limpiar
                                </button>
                            </div>
                        </div>

                        <!-- Panel de filtros expandible -->
                        <div
                            v-show="showFilters"
                            class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700"
                        >
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                    >
                                        Estado de Pago
                                    </label>
                                    <select
                                        v-model="selectedEstado"
                                        @change="applyFilters"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">Todos</option>
                                        <option
                                            v-for="estado in estadosPago"
                                            :key="estado.value"
                                            :value="estado.value"
                                        >
                                            {{ estado.label }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
                                    >
                                        Cantidad de Cuotas
                                    </label>
                                    <select
                                        v-model="selectedCuotas"
                                        @change="applyFilters"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="">Todas</option>
                                        <option
                                            v-for="opcion in opcionesCuotas"
                                            :key="opcion.value"
                                            :value="opcion.value"
                                        >
                                            {{ opcion.label }}
                                        </option>
                                    </select>
                                </div>

                                <div class="flex items-end">
                                    <label
                                        class="flex items-center space-x-2 cursor-pointer"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="conVencidas"
                                            @change="applyFilters"
                                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                        />
                                        <span
                                            class="text-sm text-gray-700 dark:text-gray-300"
                                        >
                                            Solo con cuotas vencidas
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Planes de Pago -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden"
                >
                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Cliente / Viaje
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Plan
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Progreso
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Monto
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Estado
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                    >
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                            >
                                <tr
                                    v-for="plan in planesPago.data"
                                    :key="plan.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                    :class="{
                                        'bg-red-50 dark:bg-red-900/10':
                                            tieneCuotasVencidas(plan),
                                    }"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10"
                                            >
                                                <div
                                                    class="h-10 w-10 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center"
                                                >
                                                    <UserIcon
                                                        class="h-5 w-5 text-purple-600 dark:text-purple-400"
                                                    />
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div
                                                    class="text-sm font-medium text-gray-900 dark:text-white"
                                                >
                                                    {{
                                                        plan.venta.cliente.name
                                                    }}
                                                </div>
                                                <div
                                                    class="text-sm text-gray-500 dark:text-gray-400"
                                                >
                                                    {{
                                                        plan.venta.viaje
                                                            ?.plan_viaje
                                                            ?.destino?.nombre ||
                                                        "Sin destino"
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm text-gray-900 dark:text-white"
                                        >
                                            {{ plan.cantidad_cuotas }} cuotas
                                        </div>
                                        <div
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            {{ plan.tasa_interes }}% interés
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-1 mr-3">
                                                <div
                                                    class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2"
                                                >
                                                    <div
                                                        class="bg-purple-600 h-2 rounded-full transition-all duration-300"
                                                        :style="{
                                                            width:
                                                                calcularProgreso(
                                                                    plan
                                                                ) + '%',
                                                        }"
                                                    ></div>
                                                </div>
                                            </div>
                                            <span
                                                class="text-sm text-gray-600 dark:text-gray-400"
                                            >
                                                {{ calcularProgreso(plan) }}%
                                            </span>
                                        </div>
                                        <div
                                            class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                                        >
                                            {{
                                                plan.cuotas.filter(
                                                    (c) =>
                                                        c.estado_cuota ===
                                                        "PAGADO"
                                                ).length
                                            }}/{{
                                                plan.cantidad_cuotas
                                            }}
                                            pagadas
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-medium text-gray-900 dark:text-white"
                                        >
                                            {{
                                                formatMoney(
                                                    plan.venta.monto_total
                                                )
                                            }}
                                        </div>
                                        <div
                                            class="text-xs text-gray-500 dark:text-gray-400"
                                        >
                                            Venta #{{ plan.venta.id }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="
                                                getEstadoColor(
                                                    plan.venta.estado_pago
                                                )
                                            "
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        >
                                            {{ plan.venta.estado_pago }}
                                        </span>
                                        <div
                                            v-if="tieneCuotasVencidas(plan)"
                                            class="mt-1"
                                        >
                                            <span
                                                class="text-xs text-red-600 dark:text-red-400 flex items-center"
                                            >
                                                <ExclamationTriangleIcon
                                                    class="h-3 w-3 mr-1"
                                                />
                                                Con vencidas
                                            </span>
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                    >
                                        <Link
                                            :href="'/planes-pago/' + plan.id"
                                            class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 inline-flex items-center"
                                        >
                                            <EyeIcon class="h-4 w-4 mr-1" />
                                            Ver Cronograma
                                        </Link>
                                    </td>
                                </tr>

                                <!-- Empty State -->
                                <tr v-if="planesPago.data.length === 0">
                                    <td
                                        colspan="6"
                                        class="px-6 py-12 text-center"
                                    >
                                        <CreditCardIcon
                                            class="mx-auto h-12 w-12 text-gray-400"
                                        />
                                        <h3
                                            class="mt-2 text-sm font-medium text-gray-900 dark:text-white"
                                        >
                                            No hay planes de pago
                                        </h3>
                                        <p
                                            class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            Los planes de pago se crean
                                            automáticamente al realizar una
                                            venta a crédito.
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div
                        v-if="planesPago.links && planesPago.links.length > 3"
                        class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6"
                    >
                        <div class="flex items-center justify-between">
                            <div
                                class="text-sm text-gray-700 dark:text-gray-300"
                            >
                                Mostrando {{ planesPago.from }} a
                                {{ planesPago.to }} de
                                {{ planesPago.total }} resultados
                            </div>
                            <nav class="flex items-center space-x-1">
                                <template
                                    v-for="(link, index) in planesPago.links"
                                    :key="index"
                                >
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        class="px-3 py-1 text-sm rounded-md"
                                        :class="
                                            link.active
                                                ? 'bg-purple-600 text-white'
                                                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                                        "
                                        v-html="link.label"
                                    />
                                    <span
                                        v-else
                                        class="px-3 py-1 text-sm text-gray-400"
                                        v-html="link.label"
                                    />
                                </template>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
