import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#e8f5ef',
                    100: '#d1ebdf',
                    200: '#a3d7bf',
                    300: '#75c39f',
                    400: '#47af7f',
                    500: '#347958', // Vue.js green
                    600: '#2a6146',
                    700: '#1f4834',
                    800: '#153022',
                    900: '#0a1811',
                },
                secondary: {
                    50: '#ffe8e7',
                    100: '#ffd1cf',
                    200: '#ffa39f',
                    300: '#ff756f',
                    400: '#ff473f',
                    500: '#FF2C1F', // Laravel red
                    600: '#cc2319',
                    700: '#991a13',
                    800: '#66110c',
                    900: '#330906',
                }
            }
        },
    },
    plugins: [],
};
