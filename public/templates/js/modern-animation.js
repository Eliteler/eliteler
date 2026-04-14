document.addEventListener("DOMContentLoaded", () => {    
    gsap.registerPlugin(ScrollTrigger);

    // audio
    const audioToggle = document.getElementById("audio-toggle");
    const audio = document.getElementById("bg-audio");
    const iconOn = document.getElementById("icon-volume-on");
    const iconOff = document.getElementById("icon-volume-off");
    let isPlaying = false;
    let fadeInterval;

    // Scroll to top on page load to ensure consistent state
    window.scrollTo(0, 0);

    // Force all elements to initial position on page load
    if (window.matchMedia("(max-width: 768px)").matches) {
        gsap.set(
            "#leaf-tl, #leaf-tr, #leaf-tr2, #leaf-lc, #leaf-lc2, #leaf-rc, #leaf-bl, #leaf-br, #leaf-br2, #audio-toggle",
            {
                x: 0,
                y: 0,
                rotation: 0,
                clearProps: "all",
            }
        );

        gsap.set("#index", {
            y: 0,
            zIndex: 9999,
            clearProps: "transform",
        });

        gsap.set("#leaf-bg", {
            opacity: 1,
            zIndex: 99,
        });

        // Store original sway animations to control them
        const swayAnimations = [];

        // bg animation + auto snap
        gsap.to("#leaf-bg", {
            opacity: 0,
            ease: "none",
            scrollTrigger: {
                trigger: "body",
                start: "top top",
                end: "2% top",
                scrub: true,
                onLeave: () => {
                    gsap.set("#leaf-bg", { zIndex: -1 });
                    // jump instantly instead of animating
                    window.scrollTo({
                        top: document.querySelector("#content-screen")
                            .offsetTop,
                        behavior: "smooth",
                    });
                },
                onEnterBack: () => {
                    gsap.set("#leaf-bg", { zIndex: 99 });
                },
            },
        });

        // blur effect
        gsap.to("#content-screen", {            
            scrollTrigger: {
                trigger: "body",
                start: "top top",
                end: "6% top",
                scrub: true,
                onLeave: () => {
                    gsap.set("#content-screen", {
                        filter: "blur(0px)",
                        duration: 0.7,
                        ease: "power2.out",
                    });                    
                },
                onEnterBack: () => {
                    gsap.set("#content-screen", {
                        filter: "blur(100px)",
                        duration: 0.7,
                        ease: "power2.out",
                    });
                    window.scrollTo({ top: 0, behavior: "smooth" });
                },
            },
        });

        // Sway Leaf
        function swayLeaf(id, rotationRange) {
            function createSwayAnimation() {
                const animation = gsap.to(id, {
                    xPercent: gsap.utils.random(-2, 2),
                    rotation: gsap.utils.random(-rotationRange, rotationRange),
                    duration: gsap.utils.random(3, 5),
                    ease: "sine.inOut",
                    yoyo: true,
                    onComplete: () => {
                        // Create new random animation when current one completes
                        createSwayAnimation();
                    },
                });
                swayAnimations.push(animation);
                return animation;
            }
            return createSwayAnimation();
        }

        // Sway Text
        function swayText(id, value, duration) {
            function createTextSwayAnimation() {
                const animation = gsap.to(id, {
                    yPercent: gsap.utils.random(-value, value),
                    duration: gsap.utils.random(duration - 0.5, duration + 0.5),
                    ease: "sine.inOut",
                    yoyo: true,
                    onComplete: () => {
                        // Create new random animation when current one completes
                        createTextSwayAnimation();
                    },
                });
                swayAnimations.push(animation);
                return animation;
            }
            return createTextSwayAnimation();
        }

        // Create sway animations
        swayLeaf("#leaf-tl", 3);
        swayLeaf("#leaf-tr", 3);
        swayLeaf("#leaf-tr2", 2);
        swayLeaf("#leaf-lc", 6);
        swayLeaf("#leaf-lc2", 2);
        swayLeaf("#leaf-rc", 6);
        swayLeaf("#leaf-bl", 3);
        swayLeaf("#leaf-br", 2);
        swayLeaf("#leaf-br2", 3);
        swayText("#index-text", 25, 3);
        swayText("#index-icon", 40, 2.8);

        // Main scroll trigger that controls all leaf animations
        ScrollTrigger.create({
            trigger: "body",
            start: "top top",
            end: "6% top",
            scrub: 1,
            refreshPriority: -1, // Lower priority to ensure it runs after refresh
            onRefresh: () => {
                // Reset positions when ScrollTrigger refreshes
                if (window.pageYOffset === 0) {
                    gsap.set(
                        "#leaf-tl, #leaf-tr, #leaf-tr2, #leaf-lc, #leaf-lc2, #leaf-rc, #leaf-bl, #leaf-br, #leaf-br2, #audio-toggle",
                        {
                            x: 0,
                            y: 0,
                            rotation: 0,
                        }
                    );
                    gsap.set("#index", { y: 0, zIndex: 9999 });
                    gsap.set("#leaf-bg", { opacity: 1, zIndex: 99 });
                }
            },
            onUpdate: (self) => {
                const progress = self.progress;

                // Reduce sway intensity as user scrolls
                const swayIntensity = 1 - progress * 0.8;
                swayAnimations.forEach((anim) => {
                    gsap.to(anim, { timeScale: swayIntensity, duration: 0.3 });
                });

                // Apply scroll-based transformations
                gsap.to("#leaf-tl", {
                    y: -600 * progress,
                    rotation: -45 * progress,
                    duration: 0.1,
                    overwrite: "auto",
                });

                gsap.to("#leaf-tr", {
                    y: -600 * progress,
                    rotation: 45 * progress,
                    duration: 0.1,
                    overwrite: "auto",
                });

                gsap.to("#leaf-tr2", {
                    y: -600 * progress,
                    rotation: 120 * progress,
                    duration: 0.1,
                    overwrite: "auto",
                });

                gsap.to("#leaf-lc", {
                    x: -600 * progress,
                    rotation: 120 * progress,
                    duration: 0.1,
                    overwrite: "auto",
                });

                gsap.to("#leaf-lc2", {
                    x: -600 * progress,
                    rotation: -120 * progress,
                    duration: 0.1,
                    overwrite: "auto",
                });

                gsap.to("#leaf-rc", {
                    x: 600 * progress,
                    rotation: 120 * progress,
                    duration: 0.1,
                    overwrite: "auto",
                });

                gsap.to("#leaf-bl", {
                    y: 600 * progress,
                    rotation: 45 * progress,
                    duration: 0.1,
                    overwrite: "auto",
                });

                gsap.to("#leaf-br", {
                    x: 600 * progress,
                    rotation: 120 * progress,
                    duration: 0.1,
                    overwrite: "auto",
                });

                gsap.to("#leaf-br2", {
                    y: 600 * progress,
                    rotation: -120 * progress,
                    duration: 0.1,
                    overwrite: "auto",
                });

                gsap.to("#index", {
                    y: 600 * progress,
                    duration: 0.1,
                    overwrite: "auto",
                });

                gsap.to("#audio-toggle", {
                    y: -600 * progress,
                    duration: 0.1,
                    overwrite: "auto",
                });
            },
            onLeave: () => {
                gsap.set("#index", { zIndex: -1 });
                // Stop sway animations when scrolled away
                swayAnimations.forEach((anim) => anim.pause());
            },
            onEnterBack: () => {
                gsap.set("#index", { zIndex: 9999 });
                // Resume sway animations when back to top
                swayAnimations.forEach((anim) => anim.resume());
            },
        });
    }

    // Audio Fade function
    function fadeAudio(targetVolume, callback) {
        clearInterval(fadeInterval);
        const step = targetVolume > audio.volume ? 0.05 : -0.05; // fade direction
        fadeInterval = setInterval(() => {
            audio.volume = +(audio.volume + step).toFixed(2);

            if (
                (step > 0 && audio.volume >= targetVolume) ||
                (step < 0 && audio.volume <= targetVolume)
            ) {
                audio.volume = targetVolume; // snap exact
                clearInterval(fadeInterval);
                if (callback) callback();
            }
        }, 50);
    }

    // Toggle button
    audioToggle.addEventListener("click", () => {
        if (isPlaying) {
            iconOn.classList.remove("hidden");
            iconOff.classList.add("hidden");
            // Fade out, then pause
            fadeAudio(0, () => {
                audio.pause();
            });
        } else {
            audio.volume = 0; // start silent
            audio.play().then(() => {
                iconOn.classList.add("hidden");
                iconOff.classList.remove("hidden");
                fadeAudio(1); // fade in
            });
        }
        isPlaying = !isPlaying;
    });

    // Audio Toggle
    document.getElementById("index-text").addEventListener("click", () => {
        if (!isPlaying) {
            audio.volume = 0; // start silent
            audio.play().then(() => {
                iconOn.classList.add("hidden");
                iconOff.classList.remove("hidden");
                fadeAudio(1); // fade in
            });
            isPlaying = !isPlaying;
        }
    });   
});