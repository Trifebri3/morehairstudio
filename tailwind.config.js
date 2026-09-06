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
                sans: ['"Plus Jakarta Sans"', '"Suisse Int\'l"', 'Inter', ...defaultTheme.fontFamily.sans],
                primary: ['"Plus Jakarta Sans"', '"Stack Sans Notch"', 'Inter', 'sans-serif'],
                display: ['"Plus Jakarta Sans"', '"Stack Sans Notch"', 'Inter', 'sans-serif'],
                headline: ['"Plus Jakarta Sans"', '"Stack Sans Notch"', 'Inter', 'sans-serif'],
                secondary: ['"Plus Jakarta Sans"', '"Suisse Int\'l"', 'Inter', 'sans-serif'],
                serif: ['"EB Garamond"', '"Adobe Caslon Pro"', 'Georgia', 'serif'],
                caslon: ['"EB Garamond"', '"Adobe Caslon Pro"', 'Georgia', 'serif'],
                alverata: ['"Plus Jakarta Sans"', 'sans-serif'],
                josefa: ['"Plus Jakarta Sans"', 'sans-serif'],
                mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'monospace'],
            },
            colors: {
                brand: {
                    50: '#fdf7f4',
                    100: '#faede7',
                    200: '#f6d9ce',
                    300: '#efbba9',
                    400: '#e59278',
                    500: '#c9512d', // Burnt Orange primary
                    600: '#b74423',
                    700: '#98361b',
                    800: '#7d2e1a',
                    900: '#672a1a',
                    950: '#38120a',
                },
                burnt: {
                    DEFAULT: '#c9512d',
                    hover: '#b74423',
                    light: '#faede7',
                    50: '#fdf7f4',
                    100: '#faede7',
                    200: '#f6d9ce',
                    500: '#c9512d',
                    600: '#b74423',
                    700: '#98361b',
                }
            }
        },
    },

    plugins: [forms],
};

