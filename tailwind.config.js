import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Source Sans 3"', 'system-ui', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Warna status semantik (dipakai template PDF & halaman lama)
                status: {
                    clean: '#1B4D3E',        // Heritage Forest Green
                    'clean-bg': '#EBF5F0',
                    'clean-border': '#A7D7C5',
                    warning: '#A35139',
                    'warning-bg': '#FDF2EE',
                    'warning-border': '#E8C5BC',
                    critical: '#991B1B',     // Deep Crimson Rosewood
                    'critical-bg': '#FEE2E2',
                    'critical-border': '#FECACA',
                },
            },
            boxShadow: {
                // Dipakai drawer registri warga (input/impor Super Admin)
                'ambient-xs': '0 1px 3px 0 rgba(36, 55, 70, 0.05), 0 1px 2px -1px rgba(36, 55, 70, 0.03)',
                'ambient': '0 4px 20px -2px rgba(36, 55, 70, 0.07), 0 2px 6px -1px rgba(36, 55, 70, 0.04)',
                'ambient-md': '0 12px 32px -4px rgba(36, 55, 70, 0.1), 0 4px 12px -2px rgba(36, 55, 70, 0.05)',
                'ambient-lg': '0 20px 48px -6px rgba(36, 55, 70, 0.13), 0 8px 24px -4px rgba(36, 55, 70, 0.06)',
            },
        },
    },

    plugins: [forms],
};
