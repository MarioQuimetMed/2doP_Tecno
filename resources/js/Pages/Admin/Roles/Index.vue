<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ShieldCheckIcon, UserGroupIcon } from "@heroicons/vue/24/outline";

defineProps({
    roles: Array,
});
</script>

<template>
    <Head title="Gestión de Roles" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Roles del Sistema
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">
                                Listado de Roles
                            </h3>
                            <span
                                class="px-3 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full"
                            >
                                Solo Lectura
                            </span>
                        </div>

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
                                            Nombre del Rol
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                        >
                                            Usuarios Asignados
                                        </th>
                                        <th
                                            scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                        >
                                            Estado
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                                >
                                    <tr
                                        v-for="rol in roles"
                                        :key="rol.id"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div
                                                    class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-indigo-100 dark:bg-indigo-900 rounded-full text-indigo-600 dark:text-indigo-300"
                                                >
                                                    <ShieldCheckIcon
                                                        class="h-6 w-6"
                                                    />
                                                </div>
                                                <div class="ml-4">
                                                    <div
                                                        class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                                    >
                                                        {{ rol.nombre }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div
                                                class="flex items-center text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                <UserGroupIcon
                                                    class="h-5 w-5 mr-2 text-gray-400"
                                                />
                                                {{
                                                    rol.usuarios_count
                                                }}
                                                usuarios
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                                            >
                                                Activo
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
