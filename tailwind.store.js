/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: "class",
    content: [
        // Root blade files in digital-products
        "./resources/views/templates/stores/digital-products/*.blade.php",

        // Includes directory blade files
        "./resources/views/templates/stores/digital-products/includes/**/*.blade.php",

        // Partials directory blade files
        "./resources/views/templates/stores/digital-products/partials/**/*.blade.php",

        // Catch-all recursive pattern for any nested subfolders
        "./resources/views/templates/stores/digital-products/**/*.blade.php",

        // Include JavaScript files if any Tailwind classes are used dynamically
        "./resources/views/templates/stores/digital-products/**/*.js",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ["Plus Jakarta Sans", "sans-serif"],
            },
        },
    },
    plugins: [],
};
