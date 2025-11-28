<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    TicketIcon,
    CurrencyDollarIcon,
    CalendarIcon,
} from "@heroicons/vue/24/outline";
import { useAppUrl } from "@/Composables/useAppUrl";

defineProps({
    stats: Object,
});

const { resolveUrl } = useAppUrl();
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Ventas Activas -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 mr-4"
                            >
                                <TicketIcon class="h-8 w-8" />
                            </div>
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Viajes Activos
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.ventas_activas }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Cuotas Pendientes -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-amber-100 dark:bg-amber-900 text-amber-600 dark:text-amber-300 mr-4"
                            >
                                <CurrencyDollarIcon class="h-8 w-8" />
                            </div>
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Cuotas Pendientes
                                </p>
                                <p
                                    class="text-2xl font-bold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.cuotas_pendientes }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Próximo Vencimiento -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300 mr-4"
                            >
                                <CalendarIcon class="h-8 w-8" />
                            </div>
                            <div>
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Próximo Vencimiento
                                </p>
                                <p
                                    class="text-xl font-bold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.proximo_vencimiento }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center">
                    <Link
                        :href="resolveUrl('cliente.cuotas.index')"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Ver Mis Cuotas
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
