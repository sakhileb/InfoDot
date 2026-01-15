import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, typography, daisyui],
    
    // DaisyUI configuration
    daisyui: {
        themes: ['light', 'dark'], // Only include themes you use
        darkTheme: "dark",
        base: true,
        styled: true,
        utils: true,
        logs: false, // Disable logs in production
    },

    // Optimization settings
    future: {
        hoverOnlyWhenSupported: true, // Only apply hover styles on devices that support it
    },
    
    // Experimental features for better performance
    experimental: {
        optimizeUniversalDefaults: true,
    },
};
