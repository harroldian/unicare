// assets/js/landing.js
document.addEventListener('DOMContentLoaded', () => {
    console.log("Unicare Landing Page Initialized.");
    // Smooth scroll for anchor links if any
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href'))?.scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
});