import defaultTheme from 'tailwindcss/defaultTheme';
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
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // Cores tiradas do cardápio do restaurante: o vinho do fundo
            // e o dourado das molduras e dos destaques.
            colors: {
                brand: {
                    50: '#fbf4f3',
                    100: '#f6e4e2',
                    200: '#eecbc8',
                    300: '#e0a29e',
                    400: '#cc6f6b',
                    500: '#b34845',
                    600: '#9c2f2e',
                    700: '#8a1f20',
                    800: '#741a1b',
                    900: '#571213',
                    950: '#3a0b0c',
                },
                gold: {
                    400: '#fff200',
                    500: '#fcb239',
                    600: '#e0951a',
                },
            },
        },
    },

    plugins: [forms],
};
