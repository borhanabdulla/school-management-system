import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: "class", // Enable dark mode with class strategy
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", "Segoe UI", ...defaultTheme.fontFamily.sans],
                display: ["Poppins", "Inter", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: "rgb(var(--color-primary) / <alpha-value>)",
                secondary: "rgb(var(--color-secondary) / <alpha-value>)",
                accent: "rgb(var(--color-accent) / <alpha-value>)",
                success: "rgb(var(--color-success) / <alpha-value>)",
                warning: "rgb(var(--color-warning) / <alpha-value>)",
                danger: "rgb(var(--color-danger) / <alpha-value>)",
                info: "rgb(var(--color-info) / <alpha-value>)",
                surface: "rgb(var(--color-surface) / <alpha-value>)",
                background: "rgb(var(--color-background) / <alpha-value>)",
                border: "rgb(var(--color-border) / <alpha-value>)",
                foreground: "rgb(var(--color-foreground) / <alpha-value>)",
                "muted-foreground":
                    "rgb(var(--color-muted-foreground) / <alpha-value>)",
            },
            backgroundImage: {
                "gradient-purple-blue":
                    "linear-gradient(135deg, rgb(var(--gradient-purple-blue-start)) 0%, rgb(var(--gradient-purple-blue-end)) 100%)",
                "gradient-green-teal":
                    "linear-gradient(135deg, rgb(var(--gradient-green-teal-start)) 0%, rgb(var(--gradient-green-teal-end)) 100%)",
                "gradient-orange-pink":
                    "linear-gradient(135deg, rgb(var(--gradient-orange-pink-start)) 0%, rgb(var(--gradient-orange-pink-end)) 100%)",
                "gradient-red-purple":
                    "linear-gradient(135deg, rgb(var(--gradient-red-purple-start)) 0%, rgb(var(--gradient-red-purple-end)) 100%)",
                "gradient-dark-gold":
                    "linear-gradient(135deg, rgb(var(--gradient-dark-gold-start)) 0%, rgb(var(--gradient-dark-gold-end)) 100%)", // Dark mode header
                "gradient-gold-amber":
                    "linear-gradient(135deg, rgb(var(--gradient-gold-amber-start)) 0%, rgb(var(--gradient-gold-amber-end)) 100%)", // Premium accent
                "gradient-midnight":
                    "linear-gradient(135deg, rgb(var(--gradient-midnight-start)) 0%, rgb(var(--gradient-midnight-end)) 100%)", // Deep blue dark mode
            },
            animation: {
                "fade-in": "fadeIn 0.5s ease-in",
                "slide-up": "slideUp 0.5s ease-out",
                "scale-in": "scaleIn 0.3s ease-out",
                "bounce-slow": "bounce 3s infinite",
                "pulse-slow": "pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite",
            },
            keyframes: {
                fadeIn: {
                    "0%": { opacity: "0" },
                    "100%": { opacity: "1" },
                },
                slideUp: {
                    "0%": { opacity: "0", transform: "translateY(20px)" },
                    "100%": { opacity: "1", transform: "translateY(0)" },
                },
                scaleIn: {
                    "0%": { opacity: "0", transform: "scale(0.95)" },
                    "100%": { opacity: "1", transform: "scale(1)" },
                },
            },
            boxShadow: {
                glow: "0 0 20px rgba(99, 102, 241, 0.4)",
                "glow-lg": "0 0 30px rgba(99, 102, 241, 0.6)",
            },
        },
    },

    plugins: [forms],
};
