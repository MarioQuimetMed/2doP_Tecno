<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    MagnifyingGlassIcon, 
    XMarkIcon,
    MapPinIcon,
    ClipboardDocumentListIcon,
    CalendarIcon,
} from '@heroicons/vue/24/outline';

// Estado del componente
const searchQuery = ref('');
const isOpen = ref(false);
const isLoading = ref(false);
const results = ref({
    destinos: [],
    planes: [],
    viajes: [],
    total: 0,
});
const selectedIndex = ref(-1);
const searchInput = ref(null);

// Mapa de iconos
const iconMap = {
    MapPinIcon,
    ClipboardDocumentListIcon,
    CalendarIcon,
};

const getIcon = (iconName) => {
    return iconMap[iconName] || MagnifyingGlassIcon;
};

// Lista plana de todos los resultados para navegación con teclado
const flatResults = computed(() => {
    const flat = [];
    
    if (results.value.destinos.length > 0) {
        flat.push({ type: 'header', label: 'Destinos' });
        results.value.destinos.forEach(item => flat.push({ type: 'item', ...item }));
    }
    
    if (results.value.planes.length > 0) {
        flat.push({ type: 'header', label: 'Planes de Viaje' });
        results.value.planes.forEach(item => flat.push({ type: 'item', ...item }));
    }
    
    if (results.value.viajes.length > 0) {
        flat.push({ type: 'header', label: 'Viajes Programados' });
        results.value.viajes.forEach(item => flat.push({ type: 'item', ...item }));
    }
    
    return flat;
});

// Items navegables (sin headers)
const navigableItems = computed(() => {
    return flatResults.value.filter(item => item.type === 'item');
});

// Debounce para la búsqueda
let debounceTimer = null;

const debounceSearch = (value) => {
    clearTimeout(debounceTimer);
    
    if (value.length < 2) {
        results.value = { destinos: [], planes: [], viajes: [], total: 0 };
        isLoading.value = false;
        return;
    }
    
    isLoading.value = true;
    
    debounceTimer = setTimeout(async () => {
        try {
            const response = await fetch(`/search?q=${encodeURIComponent(value)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            
            if (response.ok) {
                results.value = await response.json();
            }
        } catch (error) {
            console.error('Error en búsqueda:', error);
        } finally {
            isLoading.value = false;
        }
    }, 300); // 300ms debounce
};

// Observar cambios en la búsqueda
watch(searchQuery, (newValue) => {
    selectedIndex.value = -1;
    debounceSearch(newValue);
});

// Navegar al resultado seleccionado
const goToResult = (item) => {
    if (item && item.ruta) {
        router.visit(route(item.ruta, { [item.tipo === 'plan' ? 'planesViaje' : item.tipo]: item.id }));
        closeSearch();
    }
};

// Abrir búsqueda
const openSearch = () => {
    isOpen.value = true;
    setTimeout(() => {
        searchInput.value?.focus();
    }, 100);
};

// Cerrar búsqueda
const closeSearch = () => {
    isOpen.value = false;
    searchQuery.value = '';
    results.value = { destinos: [], planes: [], viajes: [], total: 0 };
    selectedIndex.value = -1;
};

// Navegación con teclado
const handleKeydown = (e) => {
    if (!isOpen.value) return;
    
    const items = navigableItems.value;
    
    switch (e.key) {
        case 'ArrowDown':
            e.preventDefault();
            selectedIndex.value = Math.min(selectedIndex.value + 1, items.length - 1);
            break;
        case 'ArrowUp':
            e.preventDefault();
            selectedIndex.value = Math.max(selectedIndex.value - 1, -1);
            break;
        case 'Enter':
            e.preventDefault();
            if (selectedIndex.value >= 0 && items[selectedIndex.value]) {
                goToResult(items[selectedIndex.value]);
            }
            break;
        case 'Escape':
            closeSearch();
            break;
    }
};

// Atajo de teclado global (Ctrl+K o Cmd+K)
const handleGlobalKeydown = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        if (isOpen.value) {
            closeSearch();
        } else {
            openSearch();
        }
    }
};

// Registrar listeners globales
onMounted(() => {
    document.addEventListener('keydown', handleGlobalKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleGlobalKeydown);
    clearTimeout(debounceTimer);
});

// Obtener índice real en flatResults para resaltar
const getItemIndex = (item) => {
    return navigableItems.value.findIndex(i => i.id === item.id && i.tipo === item.tipo);
};
</script>

<template>
    <div class="relative">
        <!-- Botón de búsqueda -->
        <button
            @click="openSearch"
            class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all duration-200 opacity-80 hover:opacity-100"
            style="background: rgba(255,255,255,0.1); color: var(--text-nav);"
            title="Buscar (Ctrl+K)"
        >
            <MagnifyingGlassIcon class="w-5 h-5" />
            <span class="hidden md:inline text-sm">Buscar...</span>
            <kbd class="hidden lg:inline-flex items-center gap-1 px-1.5 py-0.5 text-xs rounded bg-white/20">
                <span>Ctrl</span>
                <span>K</span>
            </kbd>
        </button>

        <!-- Modal de búsqueda -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="isOpen"
                    class="fixed inset-0 z-50 flex items-start justify-center pt-[10vh] px-4"
                    @click.self="closeSearch"
                >
                    <!-- Overlay -->
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

                    <!-- Contenedor de búsqueda -->
                    <div
                        class="relative w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden"
                        style="background: var(--bg-card);"
                        @keydown="handleKeydown"
                    >
                        <!-- Input de búsqueda -->
                        <div class="flex items-center gap-3 p-4 border-b" style="border-color: var(--border-color);">
                            <MagnifyingGlassIcon 
                                class="w-6 h-6 flex-shrink-0"
                                :class="isLoading ? 'animate-pulse' : ''"
                                style="color: var(--text-muted);"
                            />
                            <input
                                ref="searchInput"
                                v-model="searchQuery"
                                type="text"
                                placeholder="Buscar destinos, planes de viaje, viajes..."
                                class="flex-1 bg-transparent border-none outline-none text-lg"
                                style="color: var(--text-primary);"
                                autocomplete="off"
                            />
                            <button
                                @click="closeSearch"
                                class="p-1 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                            >
                                <XMarkIcon class="w-5 h-5" style="color: var(--text-muted);" />
                            </button>
                        </div>

                        <!-- Resultados -->
                        <div class="max-h-[60vh] overflow-y-auto">
                            <!-- Estado vacío inicial -->
                            <div
                                v-if="searchQuery.length < 2 && !isLoading"
                                class="p-8 text-center"
                                style="color: var(--text-muted);"
                            >
                                <MagnifyingGlassIcon class="w-12 h-12 mx-auto mb-3 opacity-50" />
                                <p>Escribe al menos 2 caracteres para buscar</p>
                                <p class="text-sm mt-2">Busca en destinos, planes de viaje y viajes programados</p>
                            </div>

                            <!-- Loading -->
                            <div
                                v-else-if="isLoading"
                                class="p-8 text-center"
                                style="color: var(--text-muted);"
                            >
                                <div class="animate-spin w-8 h-8 border-4 border-current border-t-transparent rounded-full mx-auto mb-3"></div>
                                <p>Buscando...</p>
                            </div>

                            <!-- Sin resultados -->
                            <div
                                v-else-if="results.total === 0 && searchQuery.length >= 2"
                                class="p-8 text-center"
                                style="color: var(--text-muted);"
                            >
                                <XMarkIcon class="w-12 h-12 mx-auto mb-3 opacity-50" />
                                <p>No se encontraron resultados para "{{ searchQuery }}"</p>
                                <p class="text-sm mt-2">Intenta con otros términos de búsqueda</p>
                            </div>

                            <!-- Resultados agrupados -->
                            <div v-else class="py-2">
                                <!-- Destinos -->
                                <div v-if="results.destinos.length > 0">
                                    <div class="px-4 py-2 text-xs font-semibold uppercase tracking-wider" style="color: var(--text-muted);">
                                        <MapPinIcon class="w-4 h-4 inline mr-1" />
                                        Destinos ({{ results.destinos.length }})
                                    </div>
                                    <button
                                        v-for="item in results.destinos"
                                        :key="`destino-${item.id}`"
                                        @click="goToResult(item)"
                                        class="w-full flex items-start gap-3 px-4 py-3 text-left transition-colors"
                                        :class="getItemIndex(item) === selectedIndex ? 'bg-blue-50 dark:bg-blue-900/30' : 'hover:bg-gray-100 dark:hover:bg-gray-800'"
                                    >
                                        <MapPinIcon class="w-5 h-5 mt-0.5 flex-shrink-0 text-green-500" />
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium truncate" style="color: var(--text-primary);">
                                                {{ item.titulo }}
                                            </p>
                                            <p class="text-sm truncate" style="color: var(--text-muted);">
                                                {{ item.subtitulo }}
                                            </p>
                                        </div>
                                    </button>
                                </div>

                                <!-- Planes de Viaje -->
                                <div v-if="results.planes.length > 0">
                                    <div class="px-4 py-2 text-xs font-semibold uppercase tracking-wider" style="color: var(--text-muted);">
                                        <ClipboardDocumentListIcon class="w-4 h-4 inline mr-1" />
                                        Planes de Viaje ({{ results.planes.length }})
                                    </div>
                                    <button
                                        v-for="item in results.planes"
                                        :key="`plan-${item.id}`"
                                        @click="goToResult(item)"
                                        class="w-full flex items-start gap-3 px-4 py-3 text-left transition-colors"
                                        :class="getItemIndex(item) === selectedIndex ? 'bg-blue-50 dark:bg-blue-900/30' : 'hover:bg-gray-100 dark:hover:bg-gray-800'"
                                    >
                                        <ClipboardDocumentListIcon class="w-5 h-5 mt-0.5 flex-shrink-0 text-blue-500" />
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium truncate" style="color: var(--text-primary);">
                                                {{ item.titulo }}
                                            </p>
                                            <p class="text-sm truncate" style="color: var(--text-muted);">
                                                {{ item.subtitulo }}
                                            </p>
                                            <p v-if="item.destino" class="text-xs mt-1" style="color: var(--text-muted);">
                                                📍 {{ item.destino }}
                                            </p>
                                        </div>
                                    </button>
                                </div>

                                <!-- Viajes Programados -->
                                <div v-if="results.viajes.length > 0">
                                    <div class="px-4 py-2 text-xs font-semibold uppercase tracking-wider" style="color: var(--text-muted);">
                                        <CalendarIcon class="w-4 h-4 inline mr-1" />
                                        Viajes Programados ({{ results.viajes.length }})
                                    </div>
                                    <button
                                        v-for="item in results.viajes"
                                        :key="`viaje-${item.id}`"
                                        @click="goToResult(item)"
                                        class="w-full flex items-start gap-3 px-4 py-3 text-left transition-colors"
                                        :class="getItemIndex(item) === selectedIndex ? 'bg-blue-50 dark:bg-blue-900/30' : 'hover:bg-gray-100 dark:hover:bg-gray-800'"
                                    >
                                        <CalendarIcon class="w-5 h-5 mt-0.5 flex-shrink-0 text-purple-500" />
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium truncate" style="color: var(--text-primary);">
                                                {{ item.titulo }}
                                            </p>
                                            <p class="text-sm truncate" style="color: var(--text-muted);">
                                                {{ item.subtitulo }}
                                            </p>
                                            <span
                                                class="inline-block mt-1 px-2 py-0.5 text-xs rounded-full"
                                                :class="{
                                                    'bg-green-100 text-green-800': item.estado === 'ABIERTO',
                                                    'bg-yellow-100 text-yellow-800': item.estado === 'LLENO',
                                                    'bg-blue-100 text-blue-800': item.estado === 'EN_CURSO',
                                                    'bg-gray-100 text-gray-800': item.estado === 'FINALIZADO',
                                                }"
                                            >
                                                {{ item.estado }}
                                            </span>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Footer con atajos -->
                        <div
                            class="flex items-center justify-between px-4 py-2 border-t text-xs"
                            style="border-color: var(--border-color); color: var(--text-muted);"
                        >
                            <div class="flex items-center gap-4">
                                <span class="flex items-center gap-1">
                                    <kbd class="px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700">↑</kbd>
                                    <kbd class="px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700">↓</kbd>
                                    para navegar
                                </span>
                                <span class="flex items-center gap-1">
                                    <kbd class="px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700">Enter</kbd>
                                    para seleccionar
                                </span>
                            </div>
                            <span class="flex items-center gap-1">
                                <kbd class="px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700">Esc</kbd>
                                para cerrar
                            </span>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
