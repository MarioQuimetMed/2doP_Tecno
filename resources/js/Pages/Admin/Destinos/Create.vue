<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { useAppUrl } from "@/Composables/useAppUrl";
import {
    GlobeAltIcon,
    ArrowLeftIcon,
    MapPinIcon,
} from "@heroicons/vue/24/outline";

const form = useForm({
    pais: "",
    ciudad: "",
    nombre_lugar: "",
    descripcion: "",
});

const submit = () => {
    form.post(resolveUrl("destinos"));
};

const { resolveUrl } = useAppUrl();
</script>

<template>
    <Head title="Crear Destino" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <GlobeAltIcon class="h-6 w-6 mr-2 text-indigo-600" />
                <h2
                    class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
                >
                    Crear Nuevo Destino
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <div class="mb-4">
                    <Link
                        :href="resolveUrl('destinos')"
                        class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400"
                    >
                        <ArrowLeftIcon class="h-4 w-4 mr-1" />
                        Volver a la lista de destinos
                    </Link>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="p-6">
                        <!-- Header del formulario -->
                        <div
                            class="mb-6 pb-4 border-b border-gray-200 dark:border-gray-700"
                        >
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center"
                                >
                                    <MapPinIcon class="h-6 w-6 text-white" />
                                </div>
                                <div class="ml-4">
                                    <h3
                                        class="text-lg font-medium text-gray-900 dark:text-gray-100"
                                    >
                                        Información del Destino
                                    </h3>
                                    <p
                                        class="text-sm text-gray-500 dark:text-gray-400"
                                    >
                                        Complete los datos del nuevo destino
                                        turístico
                                    </p>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- País y Ciudad en fila -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="pais" value="País *" />
                                    <TextInput
                                        id="pais"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.pais"
                                        required
                                        autofocus
                                        placeholder="Ej: Bolivia"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.pais"
                                    />
                                </div>

                                <div>
                                    <InputLabel for="ciudad" value="Ciudad *" />
                                    <TextInput
                                        id="ciudad"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.ciudad"
                                        required
                                        placeholder="Ej: Potosí"
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.ciudad"
                                    />
                                </div>
                            </div>

                            <!-- Nombre del lugar -->
                            <div>
                                <InputLabel
                                    for="nombre_lugar"
                                    value="Nombre del Lugar *"
                                />
                                <TextInput
                                    id="nombre_lugar"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.nombre_lugar"
                                    required
                                    placeholder="Ej: Salar de Uyuni"
                                />
                                <p
                                    class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    El nombre específico del atractivo turístico
                                </p>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.nombre_lugar"
                                />
                            </div>

                            <!-- Descripción -->
                            <div>
                                <InputLabel
                                    for="descripcion"
                                    value="Descripción"
                                />
                                <textarea
                                    id="descripcion"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    v-model="form.descripcion"
                                    rows="4"
                                    placeholder="Describe las características principales del destino, qué lo hace especial, qué pueden esperar los visitantes..."
                                ></textarea>
                                <p
                                    class="mt-1 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    Una descripción atractiva del destino
                                    (opcional)
                                </p>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.descripcion"
                                />
                            </div>

                            <!-- Preview Card -->
                            <div
                                v-if="
                                    form.nombre_lugar ||
                                    form.ciudad ||
                                    form.pais
                                "
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-900"
                            >
                                <p
                                    class="text-xs text-gray-500 dark:text-gray-400 mb-2"
                                >
                                    Vista previa:
                                </p>
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center"
                                    >
                                        <MapPinIcon
                                            class="h-5 w-5 text-white"
                                        />
                                    </div>
                                    <div class="ml-3">
                                        <p
                                            class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >
                                            {{
                                                form.nombre_lugar ||
                                                "Nombre del lugar"
                                            }}
                                        </p>
                                        <p
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            {{
                                                [form.ciudad, form.pais]
                                                    .filter(Boolean)
                                                    .join(", ") ||
                                                "Ciudad, País"
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div
                                class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200 dark:border-gray-700"
                            >
                                <Link
                                    :href="resolveUrl('destinos')"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Cancelar
                                </Link>

                                <PrimaryButton
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing"
                                        >Guardando...</span
                                    >
                                    <span v-else>Crear Destino</span>
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
