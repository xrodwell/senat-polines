import defaultTheme from 'tailwindcss/defaultTheme';

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
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                polines: {
                    navy: '#003366',
                    navyDark: '#00284D',
                    blue: '#0A66C2',
                    orange: '#F37021',
                    orangeHover: '#d95f14',
                },
            },
        },
    },
    plugins: [],
};
