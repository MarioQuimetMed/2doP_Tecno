<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import {
    UserIcon,
    PencilSquareIcon,
    TrashIcon,
    PlusIcon,
    MagnifyingGlassIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
} from "@heroicons/vue/24/outline";
import { ref, computed } from "vue";

const props = defineProps({
    usuarios: Object, // Ahora es un objeto de paginación
});

const search = ref("");

// Acceder a los datos paginados
const usuariosList = computed(() => props.usuarios.data || []);

const filteredUsuarios = computed(() => {
    if (!search.value) return usuariosList.value;
    const term = search.value.toLowerCase();
    return usuariosList.value.filter(
        (user) =>
            user.name.toLowerCase().includes(term) ||
            user.email.toLowerCase().includes(term) ||
            (user.rol && user.rol.nombre.toLowerCase().includes(term))
    );
});

const deleteUser = (id) => {
    if (confirm("¿Estás seguro de eliminar este usuario?")) {
        router.delete("/usuarios/" + id);
    }
};

// Navegación de paginación
const goToPage = (url) => {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Gestión de Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Gestión de Usuarios
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Header Actions -->
                        <div
                            class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4"
                        >
                            <div class="relative w-full sm:w-64">
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
                                    placeholder="Buscar usuarios..."
                                />
                            </div>

                            <Link
                                :href="'/usuarios/create'"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 w-full sm:w-auto justify-center"
                            >
                                <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
                                Nuevo Usuario
                            </Link>
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
                                            Usuario
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                        >
                                            Rol
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                        >
                                            Contacto
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
                                        v-for="user in filteredUsuarios"
                                        :key="user.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div
                                                    class="flex-shrink-0 h-10 w-10"
                                                >
                                                    <div
                                                        class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-gray-500 dark:text-gray-300"
                                                    >
                                                        <span
                                                            class="text-lg font-medium"
                                                            >{{
                                                                user.name
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
                                                        {{ user.name }}
                                                    </div>
                                                    <div
                                                        class="text-sm text-gray-500 dark:text-gray-400"
                                                    >
                                                        {{ user.email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="{
                                                    'bg-purple-100 text-purple-800':
                                                        user.rol?.nombre ===
                                                        'Propietario',
                                                    'bg-blue-100 text-blue-800':
                                                        user.rol?.nombre ===
                                                        'Vendedor',
                                                    'bg-green-100 text-green-800':
                                                        user.rol?.nombre ===
                                                        'Cliente',
                                                    'bg-gray-100 text-gray-800':
                                                        !user.rol,
                                                }"
                                            >
                                                {{
                                                    user.rol?.nombre ||
                                                    "Sin Rol"
                                                }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            <div v-if="user.telefono">
                                                Tel: {{ user.telefono }}
                                            </div>
                                            <div v-if="user.ci_nit">
                                                CI/NIT: {{ user.ci_nit }}
                                            </div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                        >
                                            <div
                                                class="flex justify-end space-x-2"
                                            >
                                                <Link
                                                    :href="
                                                        '/usuarios/' +
                                                        user.id +
                                                        '/edit'
                                                    "
                                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                    title="Editar"
                                                >
                                                    <PencilSquareIcon
                                                        class="h-5 w-5"
                                                    />
                                                </Link>
                                                <button
                                                    @click="deleteUser(user.id)"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                    title="Eliminar"
                                                    :disabled="
                                                        user.id ===
                                                        $page.props.auth.user.id
                                                    "
                                                    :class="{
                                                        'opacity-50 cursor-not-allowed':
                                                            user.id ===
                                                            $page.props.auth
                                                                .user.id,
                                                    }"
                                                >
                                                    <TrashIcon
                                                        class="h-5 w-5"
                                                    />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredUsuarios.length === 0">
                                        <td
                                            colspan="4"
                                            class="px-6 py-4 text-center text-gray-500 dark:text-gray-400"
                                        >
                                            No se encontraron usuarios.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div
                            v-if="usuarios.last_page > 1"
                            class="mt-6 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4"
                        >
                            <div
                                class="text-sm text-gray-500 dark:text-gray-400"
                            >
                                Mostrando {{ usuarios.from }} a
                                {{ usuarios.to }} de
                                {{ usuarios.total }} usuarios
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    @click="goToPage(usuarios.prev_page_url)"
                                    :disabled="!usuarios.prev_page_url"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <ChevronLeftIcon class="h-4 w-4 mr-1" />
                                    Anterior
                                </button>
                                <button
                                    @click="goToPage(usuarios.next_page_url)"
                                    :disabled="!usuarios.next_page_url"
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
