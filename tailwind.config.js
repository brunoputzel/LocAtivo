import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                surface: '#F5F6F4',
                'surface-card': '#FFFFFF',
                ink: '#1C211D',
                'ink-muted': '#5B655F',
                border: '#DADFDB',
                brand: { DEFAULT: '#2B6E4F', dark: '#1E4E38' },
                signal: '#E2711D',
                status: {
                    disponivel: '#2F855A',
                    locacao: '#2B6CB0',
                    manutencao: '#C05621',
                    inativo: '#6B7280',
                    cancelado: '#B42318',
                },
            },
            fontFamily: {
                display: ['Space Grotesk', 'sans-serif'],
                sans: ['Inter', 'sans-serif'],
                mono: ['JetBrains Mono', 'monospace'],
            },
        },
    },

    plugins: [forms],
};
