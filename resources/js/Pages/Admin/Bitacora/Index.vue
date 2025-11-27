<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import {
    ClockIcon,
    UserIcon,
    FunnelIcon,
    ArrowDownTrayIcon,
    ArrowPathIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    ArrowLeftOnRectangleIcon,
    ArrowRightOnRectangleIcon,
    PlusIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    registros: Object, // paginado
    filtros: Object,
    usuarios: Array,
    acciones: Array,
    tablas: Array,
});

const filtroUsuario = ref(props.filtros?.usuario_id || "");
const filtroAccion = ref(props.filtros?.accion || "");
const filtroTabla = ref(props.filtros?.tabla || "");
const filtroFechaInicio = ref(props.filtros?.fecha_inicio || "");
const filtroFechaFin = ref(props.filtros?.fecha_fin || "");

const aplicarFiltros = () => {
    router.get(route("bitacora.index"), {
        usuario_id: filtroUsuario.value || undefined,
        accion: filtroAccion.value || undefined,
        tabla: filtroTabla.value || undefined,
        fecha_inicio: filtroFechaInicio.value || undefined,
        fecha_fin: filtroFechaFin.value || undefined,
    }, { preserveState: true });
};

const limpiarFiltros = () => {
    filtroUsuario.value = "";
    filtroAccion.value = "";
    filtroTabla.value = "";
    filtroFechaInicio.value = "";
    filtroFechaFin.value = "";
    router.get(route("bitacora.index"));
};

const getAccionIcon = (accion) => {
    const icons = {
        crear: PlusIcon,
        actualizar: PencilIcon,
        eliminar: TrashIcon,
        login: ArrowRightOnRectangleIcon,
        logout: ArrowLeftOnRectangleIcon,
        ver: EyeIcon,
    };
    return icons[accion] || ClockIcon;
};

const getAccionColor = (accion) => {
    const colors = {
        crear: "bg-emerald-100 text-emerald-800",
        actualizar: "bg-blue-100 text-blue-800",
        eliminar: "bg-red-100 text-red-800",
        login: "bg-purple-100 text-purple-800",
        logout: "bg-gray-100 text-gray-800",
        ver: "bg-amber-100 text-amber-800",
    };
    return colors[accion] || "bg-gray-100 text-gray-800";
};

const formatDateTime = (datetime) => {
    if (!datetime) return "-";
    const date = new Date(datetime);
    return date.toLocaleString("es-BO", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
};

const exportarFiltros = () => {
    const params = new URLSearchParams();
    if (filtroUsuario.value) params.append("usuario_id", filtroUsuario.value);
    if (filtroAccion.value) params.append("accion", filtroAccion.value);
    if (filtroTabla.value) params.append("tabla", filtroTabla.value);
    if (filtroFechaInicio.value) params.append("fecha_inicio", filtroFechaInicio.value);
    if (filtroFechaFin.value) params.append("fecha_fin", filtroFechaFin.value);
    return params.toString();
};
</script>

<template>
    <Head title="Bitácora de Accesos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Bitácora de Accesos
                </h2>
                <a
                    :href="`${route('bitacora.exportar')}?${exportarFiltros()}`"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700 transition"
                >
                    <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
                    Exportar Excel
                </a>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filtros -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <FunnelIcon class="h-5 w-5 text-gray-500" />
                            <h3 class="font-medium text-gray-900 dark:text-gray-100">Filtros</h3>
                        </div>
                        <button
                            @click="limpiarFiltros"
                            class="text-sm text-gray-500 hover:text-gray-700 flex items-center"
                        >
                            <ArrowPathIcon class="h-4 w-4 mr-1" />
                            Limpiar
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Usuario
                            </label>
                            <select
                                v-model="filtroUsuario"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="">Todos</option>
                                <option
                                    v-for="usuario in usuarios"
                                    :key="usuario.id"
                                    :value="usuario.id"
                                >
                                    {{ usuario.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Acción
                            </label>
                            <select
                                v-model="filtroAccion"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="">Todas</option>
                                <option
                                    v-for="accion in acciones"
                                    :key="accion"
                                    :value="accion"
                                >
                                    {{ accion }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Tabla
                            </label>
                            <select
                                v-model="filtroTabla"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="">Todas</option>
                                <option
                                    v-for="tabla in tablas"
                                    :key="tabla"
                                    :value="tabla"
                                >
                                    {{ tabla }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Fecha Inicio
                            </label>
                            <input
                                v-model="filtroFechaInicio"
                                type="date"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Fecha Fin
                            </label>
                            <input
                                v-model="filtroFechaFin"
                                type="date"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            />
                        </div>
                    </div>
                    <div class="mt-4">
                        <button
                            @click="aplicarFiltros"
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition"
                        >
                            Aplicar Filtros
                        </button>
                    </div>
                </div>

                <!-- Estadísticas rápidas -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Registros</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ registros?.total || 0 }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Página Actual</p>
                        <p class="text-2xl font-bold text-red-600">
                            {{ registros?.current_page || 1 }} / {{ registros?.last_page || 1 }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Por Página</p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ registros?.per_page || 50 }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Mostrando</p>
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ registros?.data?.length || 0 }}
                        </p>
                    </div>
                </div>

                <!-- Tabla de registros -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="font-medium text-gray-900 dark:text-gray-100 flex items-center">
                            <ClockIcon class="h-5 w-5 mr-2" />
                            Registro de Actividades
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Fecha/Hora
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Usuario
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Acción
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Tabla
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Registro ID
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        Descripción
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                        IP
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr
                                    v-for="registro in registros?.data"
                                    :key="registro.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                        {{ formatDateTime(registro.created_at) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <UserIcon class="h-5 w-5 text-gray-400 mr-2" />
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ registro.usuario?.name || "Sistema" }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ registro.usuario?.email || "" }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize',
                                                getAccionColor(registro.accion)
                                            ]"
                                        >
                                            <component
                                                :is="getAccionIcon(registro.accion)"
                                                class="h-3 w-3 mr-1"
                                            />
                                            {{ registro.accion }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ registro.tabla || "-" }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ registro.registro_id || "-" }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 max-w-xs truncate">
                                        {{ registro.descripcion || "-" }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 font-mono">
                                        {{ registro.ip || "-" }}
                                    </td>
                                </tr>
                                <tr v-if="!registros?.data?.length">
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                        No hay registros de actividad que coincidan con los filtros.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div
                        v-if="registros?.last_page > 1"
                        class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between"
                    >
                        <p class="text-sm text-gray-500">
                            Mostrando {{ registros.from }} a {{ registros.to }} de {{ registros.total }} registros
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-if="registros.prev_page_url"
                                :href="registros.prev_page_url"
                                class="px-3 py-1 border rounded-md text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                                preserve-state
                            >
                                Anterior
                            </Link>
                            <span v-else class="px-3 py-1 border rounded-md text-sm text-gray-400 cursor-not-allowed">
                                Anterior
                            </span>
                            <Link
                                v-if="registros.next_page_url"
                                :href="registros.next_page_url"
                                class="px-3 py-1 border rounded-md text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                                preserve-state
                            >
                                Siguiente
                            </Link>
                            <span v-else class="px-3 py-1 border rounded-md text-sm text-gray-400 cursor-not-allowed">
                                Siguiente
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="mt-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                    <p class="text-sm text-amber-800 dark:text-amber-200">
                        <strong>Nota:</strong> La bitácora registra todas las acciones importantes del sistema: inicios de sesión, creación, modificación y eliminación de registros. Esta información es útil para auditorías y seguimiento de actividad.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
