<script setup>
import { ref, computed } from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import {
    Bars3Icon,
    XMarkIcon,
    UserCircleIcon,
    ChartBarIcon,
    GlobeAltIcon,
    MapPinIcon,
    CalendarIcon,
    TruckIcon,
    ShoppingCartIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    CogIcon,
    UsersIcon,
    ShieldCheckIcon,
    ClipboardDocumentListIcon,
    HomeIcon,
} from "@heroicons/vue/24/outline";

const showingNavigationDropdown = ref(false);
const page = usePage();
const user = computed(() => page.props.auth.user);
const menu = computed(() => page.props.auth.menu);

// Mapa de iconos
const iconMap = {
    ChartBarIcon,
    GlobeAltIcon,
    MapPinIcon,
    CalendarIcon,
    TruckIcon,
    ShoppingCartIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    CogIcon,
    UsersIcon,
    ShieldCheckIcon,
    ClipboardDocumentListIcon,
    HomeIcon,
};

const getIcon = (iconName) => {
    return iconMap[iconName] || GlobeAltIcon;
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <nav
            class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700"
        >
            <!-- Primary Navigation Menu -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <Link
                                :href="route('dashboard')"
                                class="font-bold text-2xl text-indigo-600 dark:text-indigo-400"
                            >
                                AgenciaViajes
                            </Link>
                        </div>

                        <!-- Navigation Links (Desktop) -->
                        <div
                            class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex"
                        >
                            <template v-for="item in menu" :key="item.id">
                                <!-- Item simple -->
                                <Link
                                    v-if="
                                        !item.hijos || item.hijos.length === 0
                                    "
                                    :href="item.ruta ? route(item.ruta) : '#'"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out"
                                    :class="[
                                        route().current(item.ruta)
                                            ? 'border-indigo-400 text-gray-900 dark:text-gray-100 focus:border-indigo-700'
                                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300',
                                    ]"
                                >
                                    <component
                                        :is="getIcon(item.icono)"
                                        class="w-5 h-5 mr-2"
                                    />
                                    {{ item.titulo }}
                                </Link>

                                <!-- Dropdown (si tiene hijos) -->
                                <div
                                    v-else
                                    class="hidden sm:flex sm:items-center sm:ml-6 relative group"
                                >
                                    <button
                                        class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 focus:outline-none transition duration-150 ease-in-out"
                                    >
                                        <component
                                            :is="getIcon(item.icono)"
                                            class="w-5 h-5 mr-2"
                                        />
                                        {{ item.titulo }}
                                        <svg
                                            class="ml-2 -mr-0.5 h-4 w-4"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>

                                    <!-- Dropdown Content -->
                                    <div
                                        class="absolute left-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 hidden group-hover:block z-50 top-10"
                                    >
                                        <Link
                                            v-for="subitem in item.hijos"
                                            :key="subitem.id"
                                            :href="
                                                subitem.ruta
                                                    ? route(subitem.ruta)
                                                    : '#'
                                            "
                                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600"
                                        >
                                            <div class="flex items-center">
                                                <component
                                                    :is="getIcon(subitem.icono)"
                                                    class="w-4 h-4 mr-2"
                                                />
                                                {{ subitem.titulo }}
                                            </div>
                                        </Link>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Settings Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="ml-3 relative">
                            <div class="flex items-center">
                                <span class="inline-flex rounded-md">
                                    <button
                                        type="button"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150"
                                    >
                                        {{ user.name }}
                                        <UserCircleIcon class="ml-2 h-5 w-5" />
                                    </button>
                                </span>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="ml-4 text-sm text-red-600 hover:text-red-800"
                                >
                                    Cerrar Sesión
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Hamburger -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button
                            @click="
                                showingNavigationDropdown =
                                    !showingNavigationDropdown
                            "
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                        >
                            <Bars3Icon
                                v-if="!showingNavigationDropdown"
                                class="h-6 w-6"
                            />
                            <XMarkIcon v-else class="h-6 w-6" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div
                :class="{
                    block: showingNavigationDropdown,
                    hidden: !showingNavigationDropdown,
                }"
                class="sm:hidden"
            >
                <div class="pt-2 pb-3 space-y-1">
                    <template v-for="item in menu" :key="item.id">
                        <Link
                            v-if="!item.hijos || item.hijos.length === 0"
                            :href="item.ruta ? route(item.ruta) : '#'"
                            class="block pl-3 pr-4 py-2 border-l-4 text-base font-medium transition duration-150 ease-in-out"
                            :class="[
                                route().current(item.ruta)
                                    ? 'border-indigo-400 text-indigo-700 bg-indigo-50 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700'
                                    : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300',
                            ]"
                        >
                            <div class="flex items-center">
                                <component
                                    :is="getIcon(item.icono)"
                                    class="w-5 h-5 mr-2"
                                />
                                {{ item.titulo }}
                            </div>
                        </Link>

                        <div v-else class="space-y-1">
                            <div
                                class="pl-3 pr-4 py-2 text-base font-medium text-gray-500 border-l-4 border-transparent flex items-center"
                            >
                                <component
                                    :is="getIcon(item.icono)"
                                    class="w-5 h-5 mr-2"
                                />
                                {{ item.titulo }}
                            </div>
                            <Link
                                v-for="subitem in item.hijos"
                                :key="subitem.id"
                                :href="subitem.ruta ? route(subitem.ruta) : '#'"
                                class="block pl-8 pr-4 py-2 border-l-4 border-transparent text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out"
                            >
                                <div class="flex items-center">
                                    <component
                                        :is="getIcon(subitem.icono)"
                                        class="w-4 h-4 mr-2"
                                    />
                                    {{ subitem.titulo }}
                                </div>
                            </Link>
                        </div>
                    </template>
                </div>

                <!-- Responsive Settings Options -->
                <div
                    class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600"
                >
                    <div class="px-4">
                        <div
                            class="font-medium text-base text-gray-800 dark:text-gray-200"
                        >
                            {{ user.name }}
                        </div>
                        <div class="font-medium text-sm text-gray-500">
                            {{ user.email }}
                        </div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <Link
                            :href="route('profile.edit')"
                            class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 transition duration-150 ease-in-out"
                        >
                            Perfil
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="block w-full text-left pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-red-600 hover:text-red-800 hover:bg-red-50 hover:border-red-300 transition duration-150 ease-in-out"
                        >
                            Cerrar Sesión
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header class="bg-white dark:bg-gray-800 shadow" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <div
                v-if="$page.props.flash.success"
                class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8"
            >
                <div
                    class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                    role="alert"
                >
                    <strong class="font-bold">¡Éxito!</strong>
                    <span class="block sm:inline ml-2">{{
                        $page.props.flash.success
                    }}</span>
                </div>
            </div>

            <div
                v-if="$page.props.flash.error"
                class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8"
            >
                <div
                    class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative"
                    role="alert"
                >
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline ml-2">{{
                        $page.props.flash.error
                    }}</span>
                </div>
            </div>

            <slot />
        </main>
    </div>
</template>
