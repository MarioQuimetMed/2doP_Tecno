<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { useAppUrl } from "@/Composables/useAppUrl";
import {
    UserIcon,
    MagnifyingGlassIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    ShoppingCartIcon,
} from "@heroicons/vue/24/outline";
import { ref, watch } from "vue";

const props = defineProps({
    clientes: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");

// Debounce para búsqueda
let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            resolveUrl("vendedor/clientes"),
            {
                search: search.value || undefined,
            },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300);
});

// Navegación de paginación
const goToPage = (url) => {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
};

const { resolveUrl } = useAppUrl();
</script>

<template>
    <Head title="Mis Clientes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <UserIcon class="h-6 w-6 mr-2 text-indigo-600" />
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Mis Clientes
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Header Actions -->
                        <div class="mb-6">
                            <div class="relative max-w-md">
                                <div
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                >
                                    <MagnifyingGlassIcon
                                        class="h-5 w-5 text-gray-400"
                                    />
                                </div>
                                <input
                                    v-model="search"
                                    type="text"
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white dark:bg-gray-700 dark:border-gray-600 dark:text-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="Buscar clientes por nombre o email..."
                                />
                            </div>
                        </div>

                        <!-- Table -->
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
                                            Cliente
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
                                            Documento
                                        </th>
                                        <th
                                            scope="col"
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
                                        v-for="cliente in clientes.data"
                                        :key="cliente.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div
                                                    class="flex-shrink-0 h-10 w-10"
                                                >
                                                    <div
                                                        class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white"
                                                    >
                                                        <span
                                                            class="text-lg font-medium"
                                                            >{{
                                                                cliente.name
                                                                    .charAt(0)
                                                                    .toUpperCase()
                                                            }}</span
                                                        >
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div
                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                                    >
                                                        {{ cliente.name }}
                                                    </div>
                                                    <div
                                                        class="text-sm text-gray-500 dark:text-gray-400"
                                                    >
                                                        {{ cliente.email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            <div v-if="cliente.telefono">
                                                {{ cliente.telefono }}
                                            </div>
                                            <div v-else class="text-gray-400 italic">
                                                No registrado
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            <div v-if="cliente.ci_nit">
                                                {{ cliente.ci_nit }}
                                            </div>
                                            <div v-else class="text-gray-400 italic">
                                                No registrado
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                        >
                                            <Link
                                                :href="
                                                    resolveUrl(
                                                        'vendedor/ventas/create'
                                                    ) +
                                                    '?cliente_id=' +
                                                    cliente.id
                                                "
                                                class="inline-flex items-center px-3 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-full hover:bg-indigo-200 dark:hover:bg-indigo-800 transition"
                                            >
                                                <ShoppingCartIcon
                                                    class="h-4 w-4 mr-1"
                                                />
                                                Nueva Venta
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="clientes.data.length === 0">
                                        <td
                                            colspan="4"
                                            class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"
                                        >
                                            <UserIcon
                                                class="h-12 w-12 mx-auto mb-4 text-gray-300 dark:text-gray-600"
                                            />
                                            <p class="text-lg font-medium">
                                                No se encontraron clientes
                                            </p>
                                            <p class="text-sm mt-1">
                                                Los clientes se registran
                                                automáticamente al realizar una
                                                venta.
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div
                            v-if="clientes.last_page > 1"
                            class="mt-6 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4"
                        >
                            <div
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Mostrando {{ clientes.from }} a
                                {{ clientes.to }} de
                                {{ clientes.total }} clientes
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    @click="goToPage(clientes.prev_page_url)"
                                    :disabled="!clientes.prev_page_url"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <ChevronLeftIcon class="h-4 w-4 mr-1" />
                                    Anterior
                                </button>
                                <button
                                    @click="goToPage(clientes.next_page_url)"
                                    :disabled="!clientes.next_page_url"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Siguiente
                                    <ChevronRightIcon class="h-4 w-4 ml-1" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
