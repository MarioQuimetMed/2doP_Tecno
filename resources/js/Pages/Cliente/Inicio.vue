<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    mensaje: String,
});
</script>

<template>
    <Head title="Inicio Cliente" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Bienvenido a tu Agencia de Viajes
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-center py-16">
                        <div class="flex justify-center mb-4">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="w-20 h-20 text-indigo-500"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.014a2.559 2.559 0 011.169 3.935l-1.715 1.715m8.498-15.102l-6.918 6.918c-.642.641-1.085 1.464-1.228 2.37l-.196 1.246 1.632 1.632 1.256-.206c.899-.148 1.705-.593 2.323-1.22l6.89-6.89a2.25 2.25 0 00-3.182-3.182z"
                                />
                            </svg>
                        </div>
                        <h3
                            class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2"
                        >
                            Página en Construcción
                        </h3>
                        <p
                            class="text-gray-600 dark:text-gray-400 max-w-md mx-auto"
                        >
                            {{ mensaje }}
                        </p>
                        <p class="mt-4 text-sm text-gray-500">
                            Pronto podrás ver tus viajes, itinerarios y realizar
                            reservas desde aquí.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
