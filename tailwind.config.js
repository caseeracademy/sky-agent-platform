import preset from './vendor/filament/support/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            // Custom icon sizes to fix large icons issue
            width: {
                'icon-xs': '0.75rem',
                'icon-sm': '1rem',
                'icon-md': '1.25rem',
                'icon-lg': '1.5rem',
                'icon-xl': '2rem',
            },
            height: {
                'icon-xs': '0.75rem',
                'icon-sm': '1rem',
                'icon-md': '1.25rem',
                'icon-lg': '1.5rem',
                'icon-xl': '2rem',
            },
        },
    },
    // Ensure proper icon handling
    plugins: [],
}













