document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    // ===========================
    // Bootstrap Modals
    // ===========================
    // document.querySelectorAll('.modal').forEach((modal) => {

    //     // Prevent duplicate event listeners
    //     if (modal.dataset.gsapInitialized) return;
    //     modal.dataset.gsapInitialized = "true";

    //     const dialog = modal.querySelector('.modal-dialog');

    //     modal.addEventListener('show.bs.modal', function () {
    //         gsap.killTweensOf(dialog);

    //         gsap.set(dialog, {
    //             opacity: 0,
    //             y: -20,
    //             scale: 0.96
    //         });
    //     });

    //     modal.addEventListener('shown.bs.modal', function () {
    //         gsap.killTweensOf(dialog);

    //         gsap.to(dialog, {
    //             opacity: 1,
    //             y: 0,
    //             scale: 1,
    //             duration: 0.25,
    //             ease: "power2.out",
    //             overwrite: "auto"
    //         });
    //     });

    //     modal.addEventListener('hide.bs.modal', function () {
    //         gsap.killTweensOf(dialog);
    //     });

    //     modal.addEventListener('hidden.bs.modal', function () {
    //         gsap.set(dialog, {
    //             clearProps: "transform,opacity"
    //         });
    //     });
    // });

    (() => {
        const DURATION = 0.1;

        document.addEventListener("show.bs.modal", function (e) {
            const modal = e.target;
            const dialog = modal.querySelector(".modal-dialog");

            gsap.set(dialog, {
                opacity: 0,
                y: 20,
                scale: 0.96,
            });

            gsap.set(modal, {
                opacity: 0,
            });
        });

        document.addEventListener("shown.bs.modal", function (e) {
            const modal = e.target;
            const dialog = modal.querySelector(".modal-dialog");

            gsap.timeline()
                .to(modal, {
                    opacity: 1,
                    duration: 0.15,
                })
                .to(
                    dialog,
                    {
                        opacity: 1,
                        y: 0,
                        scale: 1,
                        duration: DURATION,
                        ease: "power3.out",
                    },
                    0,
                );
        });

        document.addEventListener("hide.bs.modal", function (e) {
            const modal = e.target;

            if (modal.dataset.closing === "true") {
                modal.dataset.closing = "";
                return;
            }

            e.preventDefault();

            modal.dataset.closing = "true";

            const dialog = modal.querySelector(".modal-dialog");

            gsap.timeline({
                onComplete() {
                    bootstrap.Modal.getInstance(modal).hide();
                },
            })
                .to(
                    dialog,
                    {
                        opacity: 0,
                        y: 20,
                        scale: 0.96,
                        duration: DURATION,
                        ease: "power2.in",
                    },
                    0,
                )
                .to(
                    modal,
                    {
                        opacity: 0,
                        duration: 0.1,
                    },
                    0,
                );
        });
    })();

    // ===========================
    // Buttons
    // ===========================
    document.querySelectorAll(".btn").forEach((button) => {
        button.addEventListener("mousedown", () => {
            gsap.killTweensOf(button);

            gsap.to(button, {
                scale: 0.96,
                duration: 0.1,
                overwrite: "auto",
            });
        });

        const resetButton = () => {
            gsap.killTweensOf(button);

            gsap.to(button, {
                scale: 1,
                duration: 0.15,
                ease: "power2.out",
                overwrite: "auto",
            });
        };

        button.addEventListener("mouseup", resetButton);
        button.addEventListener("mouseleave", resetButton);
        button.addEventListener("touchend", resetButton);
    });

    // ===========================
    // Dropdowns
    // ===========================
    document.querySelectorAll(".dropdown").forEach((dropdown) => {
        if (dropdown.dataset.gsapInitialized) return;
        dropdown.dataset.gsapInitialized = "true";

        dropdown.addEventListener("show.bs.dropdown", function () {
            const menu = this.querySelector(".dropdown-menu");

            if (!menu) return;

            gsap.killTweensOf(menu);

            gsap.fromTo(
                menu,
                {
                    opacity: 0,
                    y: -10,
                },
                {
                    opacity: 1,
                    y: 0,
                    duration: 0.2,
                    ease: "power2.out",
                    overwrite: "auto",
                    clearProps: "transform", // Safari fix: remove leftover inline transform once open so it doesn't affect dropdown-menu positioning
                },
            );
        });
    });

    // ===========================
    // Page Animation
    // ===========================
    const pageBody = document.querySelector(".page-body");

    if (pageBody) {
        gsap.from(pageBody, {
            opacity: 0,
            y: 15,
            duration: 0.4,
            ease: "power2.out",
            clearProps: "transform", // Safari fix: strip leftover inline transform after animation completes.
            // Without this, GSAP leaves `transform: translate(0px, 0px)` on .page-body,
            // which makes it the containing block for any position:absolute/fixed
            // descendant (e.g. the action-column dropdown menus). Safari and Chrome
            // resolve that containing block differently, causing the dropdown
            // overlap/misposition bug that only shows up in Safari.
        });
    }
});

// New Animations

/* ==========================================================================
   Bootstrap Component Animations (GSAP)
   Smooth entrance + interaction animations for Bootstrap components.

   Excluded on purpose:
     - .modal            -> left completely untouched
     - button press/click -> no scale/ripple effect added anywhere

   Load order: Bootstrap JS -> GSAP -> your other scripts -> THIS FILE LAST.
   Assumes Bootstrap 5 (data-bs-* attributes / the global `bootstrap` object).
   ========================================================================== */

(function () {
    if (typeof gsap === "undefined") {
        console.error(
            "[component-anim] GSAP not found — include gsap.min.js before this file.",
        );
        return;
    }

    const REDUCED_MOTION =
        window.matchMedia &&
        window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    const CONFIG = {
        entranceSelectors: ".card, .alert, .list-group-item, .table tbody tr",
        excludeSelectors: ".modal, .no-anim, .placeholder-glow, .alert", // add class="no-anim" to opt any element out
        entranceDuration: 0.5,
        entranceStagger: 0.08,
        entranceY: 16,
    };

    function isExcluded(el) {
        return !!el.closest(CONFIG.excludeSelectors);
    }

    /* ---------------- Entrance animation with automatic batch stagger ---------------- */
    function initEntrance() {
        const elements = Array.from(
            document.querySelectorAll(CONFIG.entranceSelectors),
        ).filter((el) => el.dataset.animInit !== "true" && !isExcluded(el));

        if (!elements.length) return;

        elements.forEach((el) => {
            el.dataset.animInit = "true";
            if (!REDUCED_MOTION)
                gsap.set(el, { autoAlpha: 0, y: CONFIG.entranceY });
        });

        if (REDUCED_MOTION) return;

        const io = new IntersectionObserver(
            (entries, observer) => {
                // Everything that becomes visible in the SAME batch (e.g. all dashboard
                // cards on load) gets staggered together, in DOM order.
                const visible = entries
                    .filter((e) => e.isIntersecting)
                    .map((e) => e.target)
                    .sort((a, b) => elements.indexOf(a) - elements.indexOf(b));

                if (!visible.length) return;

                gsap.to(visible, {
                    autoAlpha: 1,
                    y: 0,
                    duration: CONFIG.entranceDuration,
                    ease: "power2.out",
                    stagger: CONFIG.entranceStagger,
                    clearProps: "transform", // Safari fix: don't leave leftover inline transforms on cards/rows either,
                    // since these can also become containing blocks for any dropdowns inside them
                    // (e.g. .table tbody tr action-column dropdowns).
                });

                visible.forEach((el) => observer.unobserve(el));
            },
            { threshold: 0.12 },
        );

        elements.forEach((el) => io.observe(el));
    }

    /* ---------------- Collapse / Accordion ---------------- */
    function initCollapse() {
        document.querySelectorAll(".collapse").forEach((el) => {
            if (el.dataset.animInit === "true" || isExcluded(el)) return;
            el.dataset.animInit = "true";

            el.addEventListener("show.bs.collapse", function (e) {
                if (e.target !== el || REDUCED_MOTION) return;
                el.style.transition = "none"; // stop Bootstrap's own CSS transition fighting GSAP
                gsap.killTweensOf(el);
                gsap.fromTo(
                    el,
                    { height: 0 },
                    {
                        height: "auto",
                        duration: 0.4,
                        ease: "power2.out",
                        onComplete: () =>
                            gsap.set(el, { clearProps: "height" }),
                    },
                );
            });

            el.addEventListener("hide.bs.collapse", function (e) {
                if (e.target !== el || REDUCED_MOTION) return;
                el.style.transition = "none";
                gsap.killTweensOf(el);
                gsap.to(el, {
                    height: 0,
                    duration: 0.32,
                    ease: "power2.in",
                    onComplete: () => gsap.set(el, { clearProps: "height" }),
                });
            });
        });
    }

    /* ---------------- Tabs / Pills ---------------- */
    function initTabs() {
        document
            .querySelectorAll('[data-bs-toggle="tab"], [data-bs-toggle="pill"]')
            .forEach((trigger) => {
                if (trigger.dataset.animInit === "true") return;
                trigger.dataset.animInit = "true";
                trigger.addEventListener("shown.bs.tab", function (e) {
                    if (REDUCED_MOTION) return;
                    const targetSel =
                        e.target.getAttribute("data-bs-target") ||
                        e.target.getAttribute("href");
                    const pane = targetSel
                        ? document.querySelector(targetSel)
                        : null;
                    if (pane)
                        gsap.fromTo(
                            pane,
                            { autoAlpha: 0, y: 8 },
                            {
                                autoAlpha: 1,
                                y: 0,
                                duration: 0.3,
                                ease: "power2.out",
                            },
                        );
                });
            });
    }

    /* ---------------- Dropdowns ----------------
       Fade only. Bootstrap/Popper positions the menu via inline `transform`;
       animating x/y/scale here would fight Popper's positioning, so we
       deliberately stick to opacity. */
    function initDropdowns() {
        document
            .querySelectorAll('[data-bs-toggle="dropdown"]')
            .forEach((toggle) => {
                if (toggle.dataset.animInit === "true") return;
                toggle.dataset.animInit = "true";
                toggle.addEventListener("shown.bs.dropdown", function (e) {
                    if (REDUCED_MOTION) return;
                    const menu =
                        e.target.parentElement.querySelector(".dropdown-menu");
                    if (menu)
                        gsap.fromTo(
                            menu,
                            { autoAlpha: 0 },
                            {
                                autoAlpha: 1,
                                duration: 0.18,
                                ease: "power1.out",
                            },
                        );
                });
            });
    }

    /* ---------------- Toasts ---------------- */
    function initToasts() {
        document.querySelectorAll(".toast").forEach((toast) => {
            if (toast.dataset.animInit === "true") return;
            toast.dataset.animInit = "true";
            toast.addEventListener("show.bs.toast", () => {
                if (REDUCED_MOTION) return;
                gsap.fromTo(
                    toast,
                    { autoAlpha: 0, x: 40 },
                    { autoAlpha: 1, x: 0, duration: 0.35, ease: "power2.out" },
                );
            });
        });
    }

    /* ---------------- Alerts (dismiss) ---------------- */
    function initAlerts() {
        document.querySelectorAll(".alert").forEach((alert) => {
            if (alert.dataset.animInit === "true" || isExcluded(alert)) return;
            alert.dataset.animInit = "true";
            alert.addEventListener("close.bs.alert", function (e) {
                if (alert.dataset.closing === "true" || REDUCED_MOTION) return; // let Bootstrap finish the real close
                e.preventDefault();
                alert.dataset.closing = "true";
                gsap.to(alert, {
                    autoAlpha: 0,
                    height: 0,
                    marginTop: 0,
                    marginBottom: 0,
                    paddingTop: 0,
                    paddingBottom: 0,
                    duration: 0.3,
                    ease: "power2.in",
                    onComplete: () =>
                        bootstrap.Alert.getOrCreateInstance(alert).close(),
                });
            });
        });
    }

    /* ---------------- Offcanvas ----------------
       Bootstrap already slides this smoothly via CSS transform; we just
       layer a fade on top rather than touching transform (same reason as
       dropdowns above). */
    function initOffcanvas() {
        document.querySelectorAll(".offcanvas").forEach((oc) => {
            if (oc.dataset.animInit === "true") return;
            oc.dataset.animInit = "true";
            oc.addEventListener("show.bs.offcanvas", () => {
                if (REDUCED_MOTION) return;
                gsap.fromTo(
                    oc,
                    { opacity: 0.4 },
                    { opacity: 1, duration: 0.3, ease: "power2.out" },
                );
            });
        });
    }

    /* ---------------- Progress bars ---------------- */
    function initProgress() {
        document.querySelectorAll(".progress-bar").forEach((bar) => {
            if (bar.dataset.animInit === "true") return;
            bar.dataset.animInit = "true";

            if (REDUCED_MOTION) return;

            const target = bar.style.width;

            gsap.fromTo(
                bar,
                { width: "0%" },
                {
                    width: target,
                    duration: 0.9,
                    ease: "power2.out",
                    delay: 0.1,
                },
            );
        });
    }

    function initAll() {
        initEntrance();
        initCollapse();
        initTabs();
        initDropdowns();
        initToasts();
        initAlerts();
        initOffcanvas();
        initProgress();
    }

    if (document.readyState !== "loading") initAll();
    else document.addEventListener("DOMContentLoaded", initAll);

    // Re-scan when new components are injected later (AJAX-loaded cards,
    // dynamically created toasts, etc.) — debounced so bursts of DOM
    // changes only trigger one pass.
    let refreshTimer = null;
    new MutationObserver(() => {
        clearTimeout(refreshTimer);
        refreshTimer = setTimeout(initAll, 50);
    }).observe(document.body, { childList: true, subtree: true });

    // Manual trigger if you ever need it, e.g. after injecting HTML yourself:
    // window.componentAnimations.refresh();
    window.componentAnimations = { refresh: initAll };
})();
