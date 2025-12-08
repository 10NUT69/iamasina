module.exports = {
  // 👇 Aceasta este linia care activează Dark Mode automat (după sistem)
  darkMode: 'media', 

  content: [
    "./resources/**/*.{blade.php,js,vue}",
    // Uneori e bine să incluzi și folderul de paginare din Laravel dacă folosești stilurile default
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php", 
  ],
  
  theme: {
    extend: {},
  },
  
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/line-clamp'),
  ],
};