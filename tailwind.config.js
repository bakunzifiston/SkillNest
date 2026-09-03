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
                sans: ['DM Sans', ...defaultTheme.fontFamily.sans],
                display: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Sampled from the brand logo: blue figure + orange figure.
                primary: {
                    DEFAULT: '#19499B',
                    dark: '#153F86',
                    darker: '#102C5E',
                    light: '#E8EEF7',
                    muted: '#C9D6EE',
                },
                navy: {
                    DEFAULT: '#0B1B33',
                    light: '#132848',
                },
                success: {
                    DEFAULT: '#1D9E75',
                    dark: '#188562',
                    darker: '#136b4f',
                    light: '#e6f7f1',
                    muted: '#cceee3',
                },
                accent: {
                    DEFAULT: '#F16029',
                    dark: '#D95420',
                    darker: '#B8461A',
                    light: '#FEF0EA',
                    muted: '#FBD4C4',
                },
            },
            boxShadow: {
                brand: '0 10px 30px -12px rgb(25 73 155 / 0.35)',
                'brand-accent': '0 10px 24px -12px rgb(241 96 41 / 0.45)',
            },
        },
    },

    plugins: [forms],
};
