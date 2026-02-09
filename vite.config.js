import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    server: {
        // port: 9788,
    },
    plugins: [
        
        laravel({
            input: ["resources/js/app.js"],
            refresh: true,
        }),
    ],
});
