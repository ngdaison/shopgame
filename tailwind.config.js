import typography from '@tailwindcss/typography';
import aspectRatio from '@tailwindcss/aspect-ratio';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './resources/js/**/*.vue',
    ],
    darkMode: 'class',
    theme: {
        container: {
            center: true,
        },
        extend: {
            fontFamily: {
                Inter: ['Inter', 'sans-serif'],
            },
            colors: {
                primary: {
                    50: "#eef2ff",
                    100: "#e0e7ff",
                    200: "#c7d2fe",
                    300: "#a5b4fc",
                    400: "#818cf8",
                    500: "#4669fa",
                    600: "#4f46e5",
                    700: "#4338ca",
                    800: "#3730a3",
                    900: "#312e81",
                },
                secondary: {
                    50: "#f8fafc",
                    100: "#f1f5f9",
                    200: "#e2e8f0",
                    300: "#cbd5e1",
                    400: "#94a3b8",
                    500: "#a0aec0",
                    600: "#475569",
                    700: "#334155",
                    800: "#1e293b",
                    900: "#0f172a",
                },
                black: {
                    50: "#f3f4f6",
                    100: "#e5e7eb",
                    200: "#d1d5db",
                    300: "#9ca3af",
                    400: "#6b7280",
                    500: "#1e293b",
                    600: "#4b5563",
                    700: "#374151",
                    800: "#1f2937",
                    900: "#111827",
                },
                success: {
                    500: "#50c793",
                },
                info: {
                    500: "#0ce7fa",
                },
                warning: {
                    500: "#fa916b",
                },
                danger: {
                    500: "#f1595c",
                },
            },
            boxShadow: {
                base: "0px 2px 4px rgba(0, 0, 0, 0.05)",
                base2: "0px 10px 15px -3px rgba(0, 0, 0, 0.1), 0px 4px 6px -2px rgba(0, 0, 0, 0.05)",
                base3: "0px 20px 25px -5px rgba(0, 0, 0, 0.1), 0px 10px 10px -5px rgba(0, 0, 0, 0.04)",
            },
            backgroundImage: {
                gradientbg: "linear-gradient(45deg, rgba(0, 0, 0, 0.1) 25%, transparent 25%, transparent 50%, rgba(0, 0, 0, 0.1) 50%, rgba(0, 0, 0, 0.1) 75%, transparent 75%, transparent)",
            },
            backgroundSize: {
                customSize: "1rem 1rem",
            },
        },
    },
    plugins: [typography, aspectRatio],
};
