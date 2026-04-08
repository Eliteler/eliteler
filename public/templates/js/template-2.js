/* GoBiz vCard SaaS
 |--------------------------------------------------------------------------
 | Designed by NativeCode - https://nativecode.in
 | All rights reserved
 |--------------------------------------------------------------------------
*/

// Burger menus
document.addEventListener('DOMContentLoaded', function () {
    // open
    const burger = document.querySelectorAll('.navbar-burger');
    const menu = document.querySelectorAll('.navbar-menu');

    if (burger.length && menu.length) {
        for (var i = 0; i < burger.length; i++) {
            burger[i].addEventListener('click', function () {
                for (var j = 0; j < menu.length; j++) {
                    menu[j].classList.toggle('hidden');
                }
            });
        }
    }

    // close
    const close = document.querySelectorAll('.navbar-close');
    const backdrop = document.querySelectorAll('.navbar-backdrop');

    if (close.length) {
        for (var i = 0; i < close.length; i++) {
            close[i].addEventListener('click', function () {
                for (var j = 0; j < menu.length; j++) {
                    menu[j].classList.toggle('hidden');
                }
            });
        }
    }

    if (backdrop.length) {
        for (var i = 0; i < backdrop.length; i++) {
            backdrop[i].addEventListener('click', function () {
                for (var j = 0; j < menu.length; j++) {
                    menu[j].classList.toggle('hidden');
                }
            });
        }
    }
});

// Services Slider
var swiper = new Swiper(".services", {
    slidesPerView: 1, // default for mobile
    spaceBetween: 10,
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    breakpoints: {
        640: {
            slidesPerView: 2, // tablets and up
        },
        1024: {
            slidesPerView: 2, // desktops and up (optional)
        }
    }
});

// Testimonials Slider
var testimonialSwiper = new Swiper(".testimonials", {
    slidesPerView: 1,
    spaceBetween: 10,
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
});

// Products Slider
var productSwiper = new Swiper(".products", {
    slidesPerView: 1, // Default for mobile
    spaceBetween: 10,
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
    breakpoints: {
        640: {
            slidesPerView: 2,
        }
    }
});

// Gallery Slider
var gallerySwiper = new Swiper(".gallery", {
    slidesPerView: 2, // Default for mobile
    spaceBetween: 2,
    autoplay: {
        delay: 3000,
        disableOnInteraction: false,
    },
    breakpoints: {
        768: {
            slidesPerView: 3,
        }
    }
});

// Videos Slider
var videoSwiper = new Swiper(".videos", {
    slidesPerView: 1,
    spaceBetween: 10,
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
    },
});
 