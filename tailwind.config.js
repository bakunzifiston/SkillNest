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
            colors: {
                primary: {
                    DEFAULT: '#185FA5',
                    dark: '#144f89',
                    darker: '#0f3f6e',
                    light: '#e7f1fa',
                    muted: '#cce3f4',
                },
                success: {
                    DEFAULT: '#1D9E75',
                    dark: '#188562',
                    darker: '#136b4f',
                    light: '#e6f7f1',
                    muted: '#cceee3',
                },
                accent: {
                    DEFAULT: '#EF9F27',
                    dark: '#d68f22',
                    darker: '#bd7f1e',
                    light: '#fdf5e6',
                    muted: '#fbe8c4',
                },
            },
        },
    },

    plugins: [forms],
};
