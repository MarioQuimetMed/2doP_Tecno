<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { WrenchScrewdriverIcon } from "@heroicons/vue/24/outline";

defineProps({
    title: String,
    module: String,
});
</script>

<template>
    <Head :title="title" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight"
            >
                {{ title }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-center py-20">
                        <div class="flex justify-center mb-6">
                            <div
                                class="p-4 bg-indigo-100 dark:bg-indigo-900 rounded-full text-indigo-600 dark:text-indigo-300"
                            >
                                <WrenchScrewdriverIcon class="h-12 w-12" />
                            </div>
                        </div>
                        <h3
                            class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2"
                        >
                            Módulo {{ module }} en Construcción
                        </h3>
                        <p
                            class="text-gray-600 dark:text-gray-400 max-w-md mx-auto"
                        >
                            Estamos trabajando arduamente para implementar esta
                            funcionalidad. Pronto podrás gestionar
                            {{ module.toLowerCase() }} desde aquí.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
