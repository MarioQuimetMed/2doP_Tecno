import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    base: "/inf513/grupo15sa/2doP_Tecno/public/build/",
    plugins: [
        laravel({
            input: "resources/js/app.js",
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    build: {
        // Optimizar chunks para mejor carga
        rollupOptions: {
            output: {
                manualChunks: {
                    // Separar vendor para mejor cacheo
                    vendor: ["vue", "@inertiajs/vue3"],
                    heroicons: ["@heroicons/vue/24/outline"],
                },
            },
        },
        // Reducir tamaño de chunks
        chunkSizeWarningLimit: 1000,
    },
    // Optimizar dependencias
    optimizeDeps: {
        include: ["vue", "@inertiajs/vue3", "@heroicons/vue/24/outline"],
    },
});
