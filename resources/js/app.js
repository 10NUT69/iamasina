import './bootstrap';
import './components/combobox';
import './components/character-counter';
import './components/toast';
import './components/favorites';
import './components/saved-searches';

import Alpine from 'alpinejs';
window.Alpine = Alpine;

Alpine.start();

// Header shrink on scroll
window.addEventListener("scroll", () => {
    const header = document.getElementById("mainHeader");
    if (!header) return;

    if (window.scrollY > 30) {
        header.classList.add("header-scrolled");
    } else {
        header.classList.remove("header-scrolled");
    }
});
