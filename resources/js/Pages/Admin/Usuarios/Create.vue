<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { UserPlusIcon, ArrowLeftIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    roles: Array,
});

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    rol_id: "",
    telefono: "",
    ci_nit: "",
});

const submit = () => {
    form.post(route('usuarios.store'));
};
</script>

<template>
    <Head title="Crear Usuario" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Crear Nuevo Usuario
                </h2>
                <Link
                    :href="route('usuarios.index')"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 flex items-center"
                >
                    <ArrowLeftIcon class="h-4 w-4 mr-1" />
                    Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Nombre -->
                            <div>
                                <label
                                    for="name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >Nombre Completo</label
                                >
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                    required
                                />
                                <div
                                    v-if="form.errors.name"
                                    class="text-red-500 text-xs mt-1"
                                >
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label
                                    for="email"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >Correo Electrónico</label
                                >
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                    required
                                />
                                <div
                                    v-if="form.errors.email"
                                    class="text-red-500 text-xs mt-1"
                                >
                                    {{ form.errors.email }}
                                </div>
                            </div>

                            <!-- Rol -->
                            <div>
                                <label
                                    for="rol_id"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >Rol</label
                                >
                                <select
                                    id="rol_id"
                                    v-model="form.rol_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                    required
                                >
                                    <option value="" disabled>
                                        Seleccione un rol
                                    </option>
                                    <option
                                        v-for="rol in roles"
                                        :key="rol.id"
                                        :value="rol.id"
                                    >
                                        {{ rol.nombre }}
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.rol_id"
                                    class="text-red-500 text-xs mt-1"
                                >
                                    {{ form.errors.rol_id }}
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label
                                        for="password"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >Contraseña</label
                                    >
                                    <input
                                        id="password"
                                        v-model="form.password"
                                        type="password"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                        required
                                    />
                                    <div
                                        v-if="form.errors.password"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ form.errors.password }}
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="password_confirmation"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >Confirmar Contraseña</label
                                    >
                                    <input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        type="password"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                        required
                                    />
                                </div>
                            </div>

                            <!-- Datos Adicionales -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label
                                        for="telefono"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >Teléfono</label
                                    >
                                    <input
                                        id="telefono"
                                        v-model="form.telefono"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                    />
                                    <div
                                        v-if="form.errors.telefono"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ form.errors.telefono }}
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="ci_nit"
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                        >CI / NIT</label
                                    >
                                    <input
                                        id="ci_nit"
                                        v-model="form.ci_nit"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                                    />
                                    <div
                                        v-if="form.errors.ci_nit"
                                        class="text-red-500 text-xs mt-1"
                                    >
                                        {{ form.errors.ci_nit }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    :disabled="form.processing"
                                >
                                    <UserPlusIcon class="-ml-1 mr-2 h-5 w-5" />
                                    Crear Usuario
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
