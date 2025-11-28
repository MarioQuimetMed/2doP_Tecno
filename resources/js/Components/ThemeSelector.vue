<script setup>
/**
 * Componente ThemeSelector
 * Selector visual de temas, modo día/noche y accesibilidad
 */
import { ref, onMounted } from 'vue';
import { useTheme } from '@/Composables/useTheme';
import {
    SunIcon,
    MoonIcon,
    Cog6ToothIcon,
    XMarkIcon,
    EyeIcon,
} from '@heroicons/vue/24/outline';

const {
    currentTheme,
    currentMode,
    currentFontSize,
    highContrast,
    autoMode,
    isNightMode,
    themes,
    fontSizes,
    setTheme,
    setMode,
    toggleMode,
    setAutoMode,
    setFontSize,
    toggleHighContrast,
    initialize,
} = useTheme();

// Estado del panel
const isOpen = ref(false);
const activeTab = ref('temas'); // 'temas', 'modo', 'accesibilidad'

// Inicializar temas al montar
onMounted(() => {
    initialize();
});

// Cerrar panel al hacer clic fuera
const closePanel = () => {
    isOpen.value = false;
};
</script>

<template>
    <div class="relative">
        <!-- Botón de configuración de tema -->
        <button
            @click="isOpen = !isOpen"
            class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200"
            :class="[
                isNightMode 
                    ? 'bg-gray-700 text-gray-200 hover:bg-gray-600' 
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            ]"
            title="Configurar tema"
        >
            <!-- Ícono de modo actual -->
            <SunIcon v-if="!isNightMode" class="w-5 h-5 text-amber-500" />
            <MoonIcon v-else class="w-5 h-5 text-indigo-400" />
            <Cog6ToothIcon class="w-4 h-4" />
        </button>

        <!-- Panel de configuración -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-1"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-1"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 mt-2 w-80 rounded-xl shadow-2xl ring-1 z-50 overflow-hidden"
                :class="[
                    isNightMode 
                        ? 'bg-gray-800 ring-gray-700' 
                        : 'bg-white ring-gray-200'
                ]"
            >
                <!-- Header del panel -->
                <div 
                    class="flex items-center justify-between px-4 py-3 border-b"
                    :class="isNightMode ? 'border-gray-700' : 'border-gray-200'"
                >
                    <h3 
                        class="font-semibold"
                        :class="isNightMode ? 'text-white' : 'text-gray-900'"
                    >
                        Personalizar Apariencia
                    </h3>
                    <button 
                        @click="closePanel" 
                        class="p-1 rounded-lg transition-colors"
                        :class="isNightMode ? 'hover:bg-gray-700' : 'hover:bg-gray-100'"
                    >
                        <XMarkIcon class="w-5 h-5" :class="isNightMode ? 'text-gray-400' : 'text-gray-500'" />
                    </button>
                </div>

                <!-- Tabs de navegación -->
                <div 
                    class="flex border-b"
                    :class="isNightMode ? 'border-gray-700' : 'border-gray-200'"
                >
                    <button
                        v-for="tab in [
                            { id: 'temas', label: 'Temas', icon: '🎨' },
                            { id: 'modo', label: 'Modo', icon: '☀️' },
                            { id: 'accesibilidad', label: 'Acceso', icon: '👁️' },
                        ]"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="flex-1 px-3 py-2 text-sm font-medium transition-colors"
                        :class="[
                            activeTab === tab.id
                                ? isNightMode 
                                    ? 'text-indigo-400 border-b-2 border-indigo-400' 
                                    : 'text-indigo-600 border-b-2 border-indigo-600'
                                : isNightMode 
                                    ? 'text-gray-400 hover:text-gray-200' 
                                    : 'text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        <span class="mr-1">{{ tab.icon }}</span>
                        {{ tab.label }}
                    </button>
                </div>

                <!-- Contenido de tabs -->
                <div class="p-4">
                    <!-- Tab: Temas -->
                    <div v-if="activeTab === 'temas'" class="space-y-3">
                        <p 
                            class="text-xs mb-3"
                            :class="isNightMode ? 'text-gray-400' : 'text-gray-500'"
                        >
                            Selecciona el estilo visual que prefieras
                        </p>
                        
                        <div
                            v-for="theme in themes"
                            :key="theme.id"
                            @click="setTheme(theme.id)"
                            class="flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-all duration-200 border-2"
                            :class="[
                                currentTheme === theme.id
                                    ? 'border-indigo-500 shadow-md'
                                    : isNightMode 
                                        ? 'border-gray-700 hover:border-gray-600' 
                                        : 'border-gray-200 hover:border-gray-300',
                                isNightMode ? 'bg-gray-750' : 'bg-gray-50'
                            ]"
                        >
                            <!-- Preview del tema -->
                            <div 
                                class="w-12 h-12 rounded-lg flex items-center justify-center text-2xl"
                                :class="theme.previewClass"
                            >
                                {{ theme.icon }}
                            </div>
                            
                            <div class="flex-1">
                                <div 
                                    class="font-medium"
                                    :class="isNightMode ? 'text-white' : 'text-gray-900'"
                                >
                                    {{ theme.nombre }}
                                </div>
                                <div 
                                    class="text-xs"
                                    :class="isNightMode ? 'text-gray-400' : 'text-gray-500'"
                                >
                                    {{ theme.descripcion }}
                                </div>
                            </div>
                            
                            <!-- Indicador de selección -->
                            <div 
                                v-if="currentTheme === theme.id"
                                class="w-5 h-5 rounded-full bg-indigo-500 flex items-center justify-center"
                            >
                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Modo Día/Noche -->
                    <div v-if="activeTab === 'modo'" class="space-y-4">
                        <p 
                            class="text-xs mb-3"
                            :class="isNightMode ? 'text-gray-400' : 'text-gray-500'"
                        >
                            Ajusta el brillo de la interfaz
                        </p>

                        <!-- Toggle Día/Noche -->
                        <div class="flex items-center justify-between">
                            <span 
                                class="font-medium"
                                :class="isNightMode ? 'text-white' : 'text-gray-900'"
                            >
                                Modo actual
                            </span>
                            
                            <button
                                @click="toggleMode"
                                class="relative inline-flex h-10 w-20 items-center rounded-full transition-colors duration-300"
                                :class="isNightMode ? 'bg-indigo-600' : 'bg-amber-400'"
                            >
                                <span 
                                    class="absolute left-1 flex h-8 w-8 items-center justify-center rounded-full bg-white shadow-md transition-transform duration-300"
                                    :class="isNightMode ? 'translate-x-10' : 'translate-x-0'"
                                >
                                    <SunIcon v-if="!isNightMode" class="w-5 h-5 text-amber-500" />
                                    <MoonIcon v-else class="w-5 h-5 text-indigo-600" />
                                </span>
                            </button>
                        </div>

                        <!-- Modo automático -->
                        <div 
                            class="flex items-center justify-between p-3 rounded-lg"
                            :class="isNightMode ? 'bg-gray-750' : 'bg-gray-50'"
                        >
                            <div>
                                <div 
                                    class="font-medium"
                                    :class="isNightMode ? 'text-white' : 'text-gray-900'"
                                >
                                    Cambio automático
                                </div>
                                <div 
                                    class="text-xs"
                                    :class="isNightMode ? 'text-gray-400' : 'text-gray-500'"
                                >
                                    Día (6am-6pm) / Noche (6pm-6am)
                                </div>
                            </div>
                            
                            <button
                                @click="setAutoMode(!autoMode)"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                                :class="autoMode ? 'bg-indigo-600' : (isNightMode ? 'bg-gray-600' : 'bg-gray-300')"
                            >
                                <span 
                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                    :class="autoMode ? 'translate-x-6' : 'translate-x-1'"
                                />
                            </button>
                        </div>
                    </div>

                    <!-- Tab: Accesibilidad -->
                    <div v-if="activeTab === 'accesibilidad'" class="space-y-4">
                        <p 
                            class="text-xs mb-3"
                            :class="isNightMode ? 'text-gray-400' : 'text-gray-500'"
                        >
                            Ajustes para mejorar la legibilidad
                        </p>

                        <!-- Tamaño de fuente -->
                        <div>
                            <label 
                                class="block text-sm font-medium mb-2"
                                :class="isNightMode ? 'text-white' : 'text-gray-900'"
                            >
                                Tamaño de texto
                            </label>
                            <div class="grid grid-cols-4 gap-2">
                                <button
                                    v-for="size in fontSizes"
                                    :key="size.id"
                                    @click="setFontSize(size.id)"
                                    class="py-2 px-3 rounded-lg font-medium transition-all border-2"
                                    :class="[
                                        size.class,
                                        currentFontSize === size.id
                                            ? 'border-indigo-500 bg-indigo-50 text-indigo-600'
                                            : isNightMode 
                                                ? 'border-gray-700 hover:border-gray-600 text-gray-300' 
                                                : 'border-gray-200 hover:border-gray-300 text-gray-700'
                                    ]"
                                    :title="size.nombre"
                                >
                                    {{ size.icon }}
                                </button>
                            </div>
                            <div 
                                class="text-xs mt-1 text-center"
                                :class="isNightMode ? 'text-gray-400' : 'text-gray-500'"
                            >
                                {{ fontSizes.find(f => f.id === currentFontSize)?.nombre || 'Normal' }}
                            </div>
                        </div>

                        <!-- Alto contraste -->
                        <div 
                            class="flex items-center justify-between p-3 rounded-lg"
                            :class="isNightMode ? 'bg-gray-750' : 'bg-gray-50'"
                        >
                            <div class="flex items-center gap-3">
                                <EyeIcon class="w-5 h-5" :class="isNightMode ? 'text-gray-400' : 'text-gray-500'" />
                                <div>
                                    <div 
                                        class="font-medium"
                                        :class="isNightMode ? 'text-white' : 'text-gray-900'"
                                    >
                                        Alto contraste
                                    </div>
                                    <div 
                                        class="text-xs"
                                        :class="isNightMode ? 'text-gray-400' : 'text-gray-500'"
                                    >
                                        Mayor legibilidad
                                    </div>
                                </div>
                            </div>
                            
                            <button
                                @click="toggleHighContrast"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                                :class="highContrast ? 'bg-indigo-600' : (isNightMode ? 'bg-gray-600' : 'bg-gray-300')"
                            >
                                <span 
                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                    :class="highContrast ? 'translate-x-6' : 'translate-x-1'"
                                />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer con info -->
                <div 
                    class="px-4 py-2 text-xs text-center border-t"
                    :class="isNightMode ? 'border-gray-700 text-gray-500' : 'border-gray-200 text-gray-400'"
                >
                    Las preferencias se guardan automáticamente
                </div>
            </div>
        </Transition>

        <!-- Overlay para cerrar -->
        <div 
            v-if="isOpen" 
            class="fixed inset-0 z-40" 
            @click="closePanel"
        />
    </div>
</template>

<style scoped>
.bg-gray-750 {
    background-color: rgb(38, 45, 58);
}
</style>
