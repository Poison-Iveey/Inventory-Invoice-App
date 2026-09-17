import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

function withOpacity(variable) {
    return `rgb(var(${variable}) / <alpha-value>)`;
}

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

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
                    DEFAULT: withOpacity('--color-primary'),
                    deep: withOpacity('--color-primary-deep'),
                    light: withOpacity('--color-primary-light'),
                    tint: withOpacity('--color-primary-tint'),
                },
                accent: {
                    DEFAULT: withOpacity('--color-accent'),
                    deep: withOpacity('--color-accent-deep'),
                    mid: withOpacity('--color-accent-mid'),
                    tint: withOpacity('--color-accent-tint'),
                },
                ink: {
                    DEFAULT: withOpacity('--color-ink'),
                    light: withOpacity('--color-ink-light'),
                },
                background: {
                    DEFAULT: withOpacity('--color-background'),
                    alt: withOpacity('--color-background-alt'),
                },
                surface: {
                    DEFAULT: withOpacity('--color-surface'),
                    white: withOpacity('--color-surface-white'),
                },
                text: {
                    primary: withOpacity('--color-text-primary'),
                    body: withOpacity('--color-text-body'),
                    muted: withOpacity('--color-text-muted'),
                },
                border: {
                    DEFAULT: withOpacity('--color-border'),
                    strong: withOpacity('--color-border-strong'),
                },
                stat: {
                    teal: { DEFAULT: withOpacity('--color-stat-teal'), tint: withOpacity('--color-stat-teal-tint'), deep: withOpacity('--color-stat-teal-deep') },
                    blue: { DEFAULT: withOpacity('--color-stat-blue'), tint: withOpacity('--color-stat-blue-tint'), deep: withOpacity('--color-stat-blue-deep') },
                    orange: { DEFAULT: withOpacity('--color-stat-orange'), tint: withOpacity('--color-stat-orange-tint'), deep: withOpacity('--color-stat-orange-deep') },
                    violet: { DEFAULT: withOpacity('--color-stat-violet'), tint: withOpacity('--color-stat-violet-tint'), deep: withOpacity('--color-stat-violet-deep') },
                },
            },
            keyframes: {
                'bell-shake': {
                    '0%, 92%, 100%': { transform: 'rotate(0deg)' },
                    '93%': { transform: 'rotate(12deg)' },
                    '94%': { transform: 'rotate(-10deg)' },
                    '95%': { transform: 'rotate(8deg)' },
                    '96%': { transform: 'rotate(-6deg)' },
                    '97%': { transform: 'rotate(3deg)' },
                    '98%': { transform: 'rotate(0deg)' },
                },
            },
            animation: {
                'bell-shake': 'bell-shake 3.5s ease-in-out infinite',
            },
        },
    },

    plugins: [forms],
};
