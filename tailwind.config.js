import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Roboto', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: '#C28C42',
                'primary-dark': '#8B5B29',
                'primary-light': '#DDC3AA',
                secondary: '#F0E0FF',
                'primary-box': '#232027',
                'secondary-box': '#F2F2F2',
            },
        },
    },

    darkMode: 'false',

    plugins: [forms, typography],
};
