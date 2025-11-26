<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import {
    CurrencyDollarIcon,
    UserGroupIcon,
    GlobeAmericasIcon,
    TicketIcon,
} from "@heroicons/vue/24/outline";

defineProps({
    stats: Object,
});
</script>

<template>
    <Head title="Dashboard Propietario" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                Panel de Control - Propietario
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6"
                >
                    <!-- Total Ventas -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-green-100 text-green-600"
                            >
                                <CurrencyDollarIcon class="h-8 w-8" />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Total Ventas
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.total_ventas }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Ventas del Mes -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-blue-100 text-blue-600"
                            >
                                <TicketIcon class="h-8 w-8" />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Ventas este Mes
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.ventas_mes }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Viajes -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-purple-100 text-purple-600"
                            >
                                <GlobeAmericasIcon class="h-8 w-8" />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Total Viajes
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.total_viajes }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Viajes Disponibles -->
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6"
                    >
                        <div class="flex items-center">
                            <div
                                class="p-3 rounded-full bg-yellow-100 text-yellow-600"
                            >
                                <UserGroupIcon class="h-8 w-8" />
                            </div>
                            <div class="ml-4">
                                <p
                                    class="text-sm font-medium text-gray-500 dark:text-gray-400"
                                >
                                    Viajes Disponibles
                                </p>
                                <p
                                    class="text-2xl font-semibold text-gray-900 dark:text-gray-100"
                                >
                                    {{ stats.viajes_disponibles }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Welcome Section -->
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium mb-4">
                            Bienvenido al Sistema de Gestión
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Desde aquí puedes administrar todos los aspectos de
                            tu agencia de viajes. Utiliza el menú lateral para
                            navegar por las diferentes secciones.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
