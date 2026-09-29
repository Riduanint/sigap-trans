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
                sans: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // MP072 Architectural Palette: Palladian, Oatmeal, Blue Fantastic, Burning Flame, Truffle Trouble, Abyssal Anchorfish Blue
                brand: {
                    canvas: '#EEE9DF',       // Palladian: Soft limestone / warm paper canvas
                    surface: '#FFFFFF',      // Crisp elevated surface
                    oatmeal: '#C9C1B1',      // Oatmeal: Subtle neutral border & secondary surface
                    slate: '#2C3B4D',        // Blue Fantastic: Slate navy / active items / card headers
                    flame: '#FFB162',        // Burning Flame: Vivid warm amber / active ping / primary CTA
                    truffle: '#A35139',      // Truffle Trouble: Terracotta rust / heritage accent
                    abyssal: '#1B2632',      // Abyssal Anchorfish Blue: Deep master navy / sidebar / primary typography
                    // Aliases for compatibility
                    sand: '#C9C1B1',
                    tan: '#FFB162',
                    ink: '#1B2632',
                    'ink-dark': '#121A23',
                    'ink-light': '#2C3B4D',
                },
                // Semantic status colors harmonious with MP072
                status: {
                    clean: '#1B4D3E',        // Heritage Forest Green
                    'clean-bg': '#EBF5F0',
                    'clean-border': '#A7D7C5',
                    warning: '#A35139',      // Truffle Trouble
                    'warning-bg': '#FDF2EE',
                    'warning-border': '#E8C5BC',
                    critical: '#991B1B',     // Deep Crimson Rosewood
                    'critical-bg': '#FEE2E2',
                    'critical-border': '#FECACA',
                },
                // Backward compatibility mapping
                'sigap-navy': '#1B2632',
                'sigap-green': '#1B4D3E',
                'sigap-gold': '#FFB162',
                'sigap-alabaster': '#EEE9DF',
            },
            boxShadow: {
                'ambient-xs': '0 1px 3px 0 rgba(27, 38, 50, 0.04), 0 1px 2px -1px rgba(27, 38, 50, 0.03)',
                'ambient': '0 4px 20px -2px rgba(27, 38, 50, 0.06), 0 2px 6px -1px rgba(27, 38, 50, 0.04)',
                'ambient-md': '0 12px 32px -4px rgba(27, 38, 50, 0.09), 0 4px 12px -2px rgba(27, 38, 50, 0.04)',
                'ambient-lg': '0 20px 48px -6px rgba(27, 38, 50, 0.12), 0 8px 24px -4px rgba(27, 38, 50, 0.06)',
            },
        },
    },

    plugins: [forms],
};
