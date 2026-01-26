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
        "./node_modules/flowbite/**/*.js",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Roboto', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Primary
                primary: '#D24985',
                'primary-hover': '#B7376E',
                'primary-dark': '#8C2A5A',
                'primary-light': '#FDA4BE',

                secondary: '#F0E0FF',

                'primary-box': '#D24985',
                'secondary-box': '#F9F9F9',

                // Brand - Pink
                'brand-pink': '#D24985',
                'brand-pink-hover': '#B7376E',
                'brand-pink-dark': '#8C2A5A',
                'brand-pink-light': '#FDA4BE',

                // Brand - Yellow
                'brand-yellow': '#EF9F48',
                'brand-yellow-hover': '#D68036',
                'brand-yellow-dark': '#A66329',
                'brand-yellow-light': '#FFEDD6',

                // Brand - Purple
                'brand-purple': '#703C6F',
                'brand-purple-hover': '#5A2A5A',
                'brand-purple-dark': '#3C1A3C',
                'brand-purple-light': '#EEDFFF',

                // Cream
                cream: '#FFF4E5',
                'cream-hover': '#FFE6C7',
                'cream-dark': '#CCB299',
                'cream-light': '#FFF9F0',

                'cream-50': '#FFFAF5',
                'cream-100': '#FFF4E5',
                'cream-200': '#FFE6C7',
                'cream-300': '#FFD8A8',
                'cream-400': '#FFC078',
                'cream-500': '#FFA94D',
                'cream-600': '#FF922B',
                'cream-700': '#FD7E14',
                'cream-800': '#F76707',
                'cream-900': '#E8590C',

                // Gold
                gold: '#C28C42',
                'gold-hover': '#B07A36',
                'gold-dark': '#8B5B29',
                'gold-light': '#DDC3AA',
            },
            backgroundImage: {
                'gradient-brand': 'linear-gradient(to right, #EF9F48, #D24985, #703C6F)',
            },
        },
    },

    darkMode: 'false',

    plugins: [
        forms,
        typography,
        require('flowbite/plugin'),
    ],
};
