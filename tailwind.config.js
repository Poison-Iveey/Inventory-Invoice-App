import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#1C3B2A',
                    deep: '#122619',
                    light: '#2C3A31',
                    tint: '#E7EFE1',
                },
                accent: {
                    DEFAULT: '#F9E0A7',
                    deep: '#7A5C1C',
                    mid: '#8A6A28',
                    tint: '#FCEFCF',
                },
                background: {
                    DEFAULT: '#FDF7EA',
                    alt: '#FAF4E6',
                },
                surface: {
                    DEFAULT: '#FDF9EF',
                    white: '#FFFFFF',
                },
                text: {
                    primary: '#1C2A21',
                    body: '#5E6E63',
                    muted: '#8A9A8E',
                },
                border: {
                    DEFAULT: '#E8DFC9',
                    strong: '#DED8CE',
                },
            },
        },
    },

    plugins: [forms],
};
