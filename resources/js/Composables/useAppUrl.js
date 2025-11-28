import { ref } from "vue";

export function useAppUrl() {
    const getBaseUrl = () => {
        // 1. Prioridad TOTAL: Leer variable del .env (debe empezar con VITE_)
        let baseUrl = import.meta.env.VITE_APP_URL;

        // 2. Fallback: Si no definiste la variable, intentar deducirla
        if (!baseUrl) {
            // En local (DEV) usamos raíz simple, en Producción usamos el subdirectorio de Vite
            baseUrl = import.meta.env.DEV
                ? ""
                : import.meta.env.BASE_URL.replace(/\/build\/?$/, "");
        }

        // 3. Fallback de Emergencia: Detectar si estamos en un subdirectorio "/public"
        // Esto tiene prioridad si detectamos que estamos corriendo bajo esa estructura
        const path = window.location.pathname;
        const publicIndex = path.indexOf('/public');
        if (publicIndex !== -1) {
            // Si encontramos /public, asumimos que esa es la base correcta
            baseUrl = path.substring(0, publicIndex + 7); // +7 para incluir "/public"
        }

        // Asegurar que baseUrl no tenga slash final para evitar dobles slashes
        if (baseUrl && baseUrl.endsWith("/")) {
            baseUrl = baseUrl.slice(0, -1);
        }

        return baseUrl;
    };

    const resolveUrl = (ruta) => {
        if (!ruta) return "#";
        let cleanRuta = ruta.trim();

        // Si ya es una URL absoluta (http/https), devolverla tal cual
        if (cleanRuta.startsWith("http")) {
            return cleanRuta;
        }

        // Si termina en .index, quitarlo
        if (cleanRuta.endsWith(".index")) {
            cleanRuta = cleanRuta.replace(".index", "");
        }

        // Si empieza con /, quitarlo para normalizar
        if (cleanRuta.startsWith("/")) {
            cleanRuta = cleanRuta.substring(1);
        }

        const baseUrl = getBaseUrl();

        // Mapeo manual de rutas (incluye rutas con / y nombres con .)
        const rutaMap = {
            // Rutas generales
            dashboard: "/dashboard",
            destinos: "/destinos",
            "planes-viaje": "/planes-viaje",
            viajes: "/viajes",
            ventas: "/ventas",
            "planes-pago": "/planes-pago",
            pagos: "/pagos",
            usuarios: "/usuarios",
            roles: "/roles",
            bitacora: "/bitacora",
            reportes: "/reportes",
            
            // Rutas de Vendedor (formato URL con /)
            "vendedor/mis-ventas": "/vendedor/mis-ventas",
            "vendedor/viajes-disponibles": "/vendedor/viajes-disponibles",
            "vendedor/clientes": "/vendedor/clientes",
            "vendedor/ventas/create": "/vendedor/ventas/create",
            
            // Rutas de Vendedor (formato nombre Laravel con .)
            "ventas.mis-ventas": "/vendedor/mis-ventas",
            "viajes.disponibles": "/vendedor/viajes-disponibles",
            "clientes.index": "/vendedor/clientes",
            "clientes": "/vendedor/clientes",
            
            // Rutas de Cliente
            "cliente/inicio": "/cliente/inicio",
            "cliente.inicio": "/cliente/inicio",
            "cliente.cuotas": "/cliente/mis-cuotas",
            "cliente.cuotas.index": "/cliente/mis-cuotas",
        };

        // Obtener la ruta relativa
        const path = rutaMap[cleanRuta] || `/${cleanRuta}`;

        return `${baseUrl}${path}`;
    };

    return {
        resolveUrl,
        getBaseUrl,
    };
}
