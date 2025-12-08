import './bootstrap';

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


// =============================
// FAVORITE BUTTON TOGGLE AJAX
// =============================
document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll('.favorite-btn').forEach(btn => {

        btn.addEventListener('click', function (e) {
            e.preventDefault();

            const id = this.dataset.id;

            fetch('/favorite/toggle', {
                method: 'POST',
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ service_id: id })
            })
            .then(res => res.json())
            .then(data => {

                if (data.error === 'not_logged_in') {
                    window.location.href = "/login";
                    return;
                }

                const svg = this.querySelector('svg');

                if (data.status === 'added') {
                    svg.classList.remove('text-gray-400');
                    svg.classList.add('text-red-500');
                    svg.setAttribute("fill", "currentColor");
                } else {
                    svg.classList.remove('text-red-500');
                    svg.classList.add('text-gray-400');
                    svg.setAttribute("fill", "none");
                }
            });
        });
    });

});
