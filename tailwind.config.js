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
                // Body / UI — Suisse Intl (default font-sans)
                sans:      ['"Suisse Intl"', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'sans-serif'],
                body:      ['"Suisse Intl"', 'sans-serif'],
                secondary: ['"Suisse Intl"', 'sans-serif'],
                serif:     ['"Suisse Intl"', 'Georgia', 'serif'],
                caslon:    ['"Suisse Intl"', 'sans-serif'],
                // Headings / Display — Stack Sans Notch
                display:   ['"Stack Sans Notch"', 'sans-serif'],
                headline:  ['"Stack Sans Notch"', 'sans-serif'],
                primary:   ['"Stack Sans Notch"', 'sans-serif'],
                alverata:  ['"Stack Sans Notch"', 'sans-serif'],
                josefa:    ['"Stack Sans Notch"', 'sans-serif'],
                // Mono
                mono:      ['"SuisseIntlMono"', 'ui-monospace', 'SFMono-Regular', 'monospace'],
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


