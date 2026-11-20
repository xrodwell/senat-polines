import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/Livewire/**/*.php',
        './app/Http/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                portal: ['"Inter"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                senat: {
                    hero: '#03428b',
                    primary: '#181a58',
                    primaryHover: '#0f103b',
                    link: '#03428b',
                    orange: '#f8c300',
                    orangeHover: '#dfae00',
                    yellow: '#f8c300',
                    yellowHover: '#dfae00',
                    blue: '#03428b',
                    navy: '#181a58',
                    ink: '#172133',
                    body: '#52637d',
                    muted: '#637082',
                    line: '#e2e8ee',
                    surface: '#f7f9fc',
                    sidebarLine: '#d9e2ef',
                    sky: '#8fc7ff',
                    skySoft: '#cfe0ff',
                    badgeBg: '#f2f9ff',
                    badgeLine: '#8cb8e3',
                    border: '#cbd5e1',
                },
                polines: {
                    navy: '#181a58',
                    navyDark: '#10123b',
                    blue: '#03428b',
                    orange: '#f8c300',
                    orangeHover: '#dfae00',
                },
            },
        },
    },
    plugins: [],
};

