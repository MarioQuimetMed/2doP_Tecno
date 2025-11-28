/**
 * Composable para manejar el sistema de temas
 * Gestiona tema, modo día/noche, tamaño de fuente y preferencias
 */
import { ref, computed, watch, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useAppUrl } from "@/Composables/useAppUrl";

// Estado reactivo global (singleton)
const currentTheme = ref("adultos");
const currentMode = ref("dia");
const currentFontSize = ref("normal");
const highContrast = ref(false);
const autoMode = ref(true);
const isInitialized = ref(false);

// Claves de localStorage
const STORAGE_KEYS = {
    THEME: "tendencias_tours_theme",
    MODE: "tendencias_tours_mode",
    FONT_SIZE: "tendencias_tours_font_size",
    HIGH_CONTRAST: "tendencias_tours_high_contrast",
    AUTO_MODE: "tendencias_tours_auto_mode",
};

/**
 * Composable principal de temas
 */
export function useTheme() {
    const page = usePage();

    // Temas disponibles
    const themes = [
        {
            id: "ninos",
            nombre: "Niños",
            descripcion: "Colorido y divertido",
            icon: "🎨",
            previewClass: "theme-preview-ninos",
        },
        {
            id: "jovenes",
            nombre: "Jóvenes",
            descripcion: "Moderno y vibrante",
            icon: "✨",
            previewClass: "theme-preview-jovenes",
        },
        {
            id: "adultos",
            nombre: "Adultos",
            descripcion: "Profesional y elegante",
            icon: "💼",
            previewClass: "theme-preview-adultos",
        },
    ];

    // Tamaños de fuente disponibles
    const fontSizes = [
        { id: "pequeno", nombre: "Pequeño", icon: "A", class: "text-sm" },
        { id: "normal", nombre: "Normal", icon: "A", class: "text-base" },
        { id: "grande", nombre: "Grande", icon: "A", class: "text-lg" },
        {
            id: "extra_grande",
            nombre: "Extra Grande",
            icon: "A",
            class: "text-xl",
        },
    ];

    /**
     * Determinar si debería ser modo noche según la hora
     */
    const shouldBeNightMode = () => {
        const hour = new Date().getHours();
        // Modo noche: 18:00 - 06:00
        return hour >= 18 || hour < 6;
    };

    /**
     * Aplicar el tema actual al DOM
     */
    const applyTheme = () => {
        const html = document.documentElement;

        // Aplicar tema
        html.setAttribute("data-theme", currentTheme.value);

        // Aplicar modo
        html.setAttribute("data-mode", currentMode.value);

        // Aplicar tamaño de fuente
        html.setAttribute("data-font-size", currentFontSize.value);

        // Aplicar alto contraste
        html.setAttribute("data-high-contrast", highContrast.value.toString());

        // Añadir clase de transición temporalmente
        html.classList.add("theme-transition");
        setTimeout(() => {
            html.classList.remove("theme-transition");
        }, 300);
    };

    /**
     * Guardar preferencias en localStorage
     */
    const saveToLocalStorage = () => {
        try {
            localStorage.setItem(STORAGE_KEYS.THEME, currentTheme.value);
            localStorage.setItem(STORAGE_KEYS.MODE, currentMode.value);
            localStorage.setItem(STORAGE_KEYS.FONT_SIZE, currentFontSize.value);
            localStorage.setItem(
                STORAGE_KEYS.HIGH_CONTRAST,
                highContrast.value.toString()
            );
            localStorage.setItem(
                STORAGE_KEYS.AUTO_MODE,
                autoMode.value.toString()
            );
        } catch (e) {
            console.warn("No se pudo guardar en localStorage:", e);
        }
    };

    /**
     * Cargar preferencias desde localStorage
     */
    const loadFromLocalStorage = () => {
        try {
            const savedTheme = localStorage.getItem(STORAGE_KEYS.THEME);
            const savedMode = localStorage.getItem(STORAGE_KEYS.MODE);
            const savedFontSize = localStorage.getItem(STORAGE_KEYS.FONT_SIZE);
            const savedHighContrast = localStorage.getItem(
                STORAGE_KEYS.HIGH_CONTRAST
            );
            const savedAutoMode = localStorage.getItem(STORAGE_KEYS.AUTO_MODE);

            if (savedTheme) currentTheme.value = savedTheme;
            if (savedFontSize) currentFontSize.value = savedFontSize;
            if (savedHighContrast)
                highContrast.value = savedHighContrast === "true";
            if (savedAutoMode !== null)
                autoMode.value = savedAutoMode === "true";

            // Si hay modo automático, determinar según hora
            if (autoMode.value) {
                currentMode.value = shouldBeNightMode() ? "noche" : "dia";
            } else if (savedMode) {
                currentMode.value = savedMode;
            }
        } catch (e) {
            console.warn("No se pudo cargar desde localStorage:", e);
        }
    };

    /**
     * Cargar preferencias desde el servidor (usuario autenticado)
     */
    const loadFromServer = () => {
        const preferencias = page.props.auth?.preferencias;

        if (preferencias) {
            if (preferencias.tema?.tipo) {
                currentTheme.value = preferencias.tema.tipo;
            }
            if (preferencias.tamaño_fuente) {
                currentFontSize.value = preferencias.tamaño_fuente;
            }
            if (preferencias.alto_contraste !== undefined) {
                highContrast.value = preferencias.alto_contraste;
            }
            if (preferencias.modo_oscuro_auto !== undefined) {
                autoMode.value = preferencias.modo_oscuro_auto;
            }

            // Determinar modo según preferencias
            if (autoMode.value) {
                currentMode.value = shouldBeNightMode() ? "noche" : "dia";
            }
        }
    };

    /**
     * Cambiar tema
     */
    const setTheme = (theme) => {
        if (themes.some((t) => t.id === theme)) {
            currentTheme.value = theme;
            applyTheme();
            saveToLocalStorage();
            saveToServer();
        }
    };

    /**
     * Cambiar modo (día/noche)
     */
    const setMode = (mode) => {
        if (["dia", "noche"].includes(mode)) {
            currentMode.value = mode;
            autoMode.value = false; // Desactivar automático al cambiar manualmente
            applyTheme();
            saveToLocalStorage();
            saveToServer();
        }
    };

    /**
     * Alternar modo día/noche
     */
    const toggleMode = () => {
        setMode(currentMode.value === "dia" ? "noche" : "dia");
    };

    /**
     * Activar/desactivar modo automático
     */
    const setAutoMode = (enabled) => {
        autoMode.value = enabled;
        if (enabled) {
            currentMode.value = shouldBeNightMode() ? "noche" : "dia";
        }
        applyTheme();
        saveToLocalStorage();
        saveToServer();
    };

    /**
     * Cambiar tamaño de fuente
     */
    const setFontSize = (size) => {
        if (fontSizes.some((f) => f.id === size)) {
            currentFontSize.value = size;
            applyTheme();
            saveToLocalStorage();
            saveToServer();
        }
    };

    /**
     * Alternar alto contraste
     */
    const toggleHighContrast = () => {
        highContrast.value = !highContrast.value;
        applyTheme();
        saveToLocalStorage();
        saveToServer();
    };

    /**
     * Guardar preferencias en el servidor
     */
    const saveToServer = () => {
        // Solo guardar si hay usuario autenticado
        if (!page.props.auth?.user) return;

        // Encontrar el tema_id correspondiente
        const temaMap = {
            ninos: 1,
            jovenes: 2,
            adultos: 3,
        };

        // Usar fetch en lugar de router.post para evitar navegación
        const { resolveUrl } = useAppUrl();
        fetch(resolveUrl("preferencias"), {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({
                tema_id: temaMap[currentTheme.value] || null,
                tamaño_fuente: currentFontSize.value,
                alto_contraste: highContrast.value,
                modo_oscuro_auto: autoMode.value,
            }),
        }).catch((err) => {
            console.warn("No se pudo guardar preferencias en servidor:", err);
        });
    };

    /**
     * Inicializar el sistema de temas
     */
    const initialize = () => {
        if (isInitialized.value) return;

        // Primero cargar desde localStorage (para visitantes)
        loadFromLocalStorage();

        // Si hay usuario autenticado, sobrescribir con preferencias del servidor
        if (page.props.auth?.user) {
            loadFromServer();
        }

        // Aplicar tema
        applyTheme();

        // Configurar verificación automática de hora cada minuto
        if (autoMode.value) {
            setInterval(() => {
                if (autoMode.value) {
                    const newMode = shouldBeNightMode() ? "noche" : "dia";
                    if (newMode !== currentMode.value) {
                        currentMode.value = newMode;
                        applyTheme();
                    }
                }
            }, 60000); // Cada minuto
        }

        isInitialized.value = true;
    };

    // Computed para saber si es modo noche
    const isNightMode = computed(() => currentMode.value === "noche");

    // Computed para obtener el tema actual completo
    const currentThemeData = computed(
        () => themes.find((t) => t.id === currentTheme.value) || themes[2]
    );

    return {
        // Estado
        currentTheme,
        currentMode,
        currentFontSize,
        highContrast,
        autoMode,
        isNightMode,
        currentThemeData,

        // Datos
        themes,
        fontSizes,

        // Métodos
        setTheme,
        setMode,
        toggleMode,
        setAutoMode,
        setFontSize,
        toggleHighContrast,
        initialize,
        applyTheme,
    };
}

export default useTheme;
