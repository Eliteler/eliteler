/* Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Designed by NativeCode - https://nativecode.in
 | All rights reserved
 |--------------------------------------------------------------------------
*/

var scroll = new SmoothScroll('a[href*="#"]');
function closeCookie() {
    $(".cookie-consent").fadeOut(300);
}
function downloadQr(url, size, name) {
    "use strict";
    
    var qrious = new QRious({ value: url, size: size });
    var qrDataURL = qrious.toDataURL();
    
    // Create a temporary canvas for composition
    var canvas = document.createElement("canvas");
    var ctx = canvas.getContext("2d");
    var padding = name ? 50 : 0; // Space for text at the top
    
    canvas.width = size;
    canvas.height = size + padding;
    
    // Background
    ctx.fillStyle = "#ffffff";
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Draw Text
    if (name) {
        ctx.fillStyle = "#000000";
        ctx.font = "bold 20px Arial";
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillText(name, canvas.width / 2, padding / 2);
    }
    
    // Draw QR Code
    var img = new Image();
    img.onload = function() {
        ctx.drawImage(img, 0, padding);
        var combinedDataURL = canvas.toDataURL("image/png");
        
        // Trigger the server-side download
        var form = document.createElement("form");
        form.method = "POST";
        form.action = "/download-qr-image";
        form.style.display = "none";
        
        var csrf = document.querySelector('meta[name="csrf-token"]');
        if (csrf) {
            var csrfInput = document.createElement("input");
            csrfInput.type = "hidden";
            csrfInput.name = "_token";
            csrfInput.value = csrf.content;
            form.appendChild(csrfInput);
        }
        
        var dataInput = document.createElement("input");
        dataInput.type = "hidden";
        dataInput.name = "image_data";
        dataInput.value = combinedDataURL;
        form.appendChild(dataInput);
        
        var fileInput = document.createElement("input");
        fileInput.type = "hidden";
        fileInput.name = "filename";
        fileInput.value = "ecard-qr.png";
        form.appendChild(fileInput);
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    };
    img.src = qrDataURL;
}
function updateQr(e) {
    "use strict";
    var t = new QRious({ value: e, size: 200 });
    ($(".qr-code").html(t.image), $("#download").show());
}

(!(function (e) {
    "use strict";
    var t = document.querySelectorAll(".send-modal-open");
    if (t)
        for (var o = 0; o < t.length; o++)
            t[o].addEventListener("click", function (e) {
                (e.preventDefault(), c());
            });
    let n = document.querySelector(".send-modal-overlay");
    n && n.addEventListener("click", c);
    var l = document.querySelectorAll(".modal-close");
    if (l) for (var o = 0; o < l.length; o++) l[o].addEventListener("click", c);
    function c() {
        let e = document.querySelector("body"),
            t = document.querySelector(".send-modal");
        t &&
            (t.classList.toggle("opacity-0"),
            t.classList.toggle("pointer-events-none"),
            e.classList.toggle("modal-active"));
    }
    document.onkeydown = function (e) {
        e = e || window.event;
        var t = !1;
        (t =
            "key" in e
                ? "Escape" === e.key || "Esc" === e.key
                : 27 === e.keyCode) &&
            document.body.classList.contains("modal-active") &&
            c();
    };
})(jQuery),
    (function (e) {
        "use strict";
        e(document).ready(function () {
            for (
                var e = document.querySelectorAll(".qr-modal-open"), t = 0;
                t < e.length;
                t++
            )
                e[t].addEventListener("click", function (e) {
                    (e.preventDefault(), l());
                });
            let o = document.querySelector(".qr-modal-overlay");
            o && o.addEventListener("click", l);
            for (
                var n = document.querySelectorAll(".modal-close"), t = 0;
                t < n.length;
                t++
            )
                n[t].addEventListener("click", l);
            function l() {
                let e = document.querySelector("body"),
                    t = document.querySelector(".qr-modal");
                t &&
                    (t.classList.toggle("opacity-0"),
                    t.classList.toggle("pointer-events-none"),
                    e.classList.toggle("modal-active"));
            }
            document.onkeydown = function (e) {
                e = e || window.event;
                var t = !1;
                (t =
                    "key" in e
                        ? "Escape" === e.key || "Esc" === e.key
                        : 27 === e.keyCode) &&
                    document.body.classList.contains("modal-active") &&
                    l();
            };
        });
    })(jQuery));
// Info pop modal
$("#closeInfoPopModal").click(function () {
    "use strict";
    $("#infoPopModal").hide();
});
// Newsletter Modal
setTimeout(function () {
    $("#newsletterModal").removeClass("hidden");
}, 5e3);
const csrfToken = $("#newsletterModal").data("csrf"),
    emailError = $("#newsletterModal").data("email-error"),
    vaildEmailError = $("#newsletterModal").data("vaild-email-error");
$("#newsletterModal").on("click", "#subscribeButton", function (e) {
    e.preventDefault();
    let r = $("#newsletter_email").val(),
        s = $("#card_id").val();
    if (!r) {
        ($("#errorMessage").text(emailError).removeClass("hidden").fadeIn(),
            setTimeout(() => {
                $("#errorMessage").fadeOut();
            }, 1500));
        return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(r)) {
        ($("#errorMessage")
            .text(vaildEmailError)
            .removeClass("hidden")
            .fadeIn(),
            setTimeout(() => {
                $("#errorMessage").fadeOut();
            }, 1500));
        return;
    }
    ($("#subscribeButton").prop("disabled", !0),
        $("#subscribeButton").text("Loading..."),
        $.ajax({
            url: "/subscribe/newsletter",
            type: "POST",
            data: { email: r, card_id: s, _token: csrfToken },
            success: function (e) {
                if ("failed" == e.status) {
                    ($("#errorNewsMessage")
                        .text(e.message)
                        .removeClass("hidden")
                        .fadeIn(),
                        setTimeout(() => {
                            $("#errorNewsMessage").fadeOut();
                        }, 3e3),
                        $("#subscribeButton").prop("disabled", !1),
                        $("#subscribeButton").text("Subscribe"));
                    return;
                }
                ($("#successNewsMessage")
                    .text(e.message)
                    .removeClass("hidden")
                    .fadeIn(),
                    setTimeout(() => {
                        $("#successNewsMessage").fadeOut();
                    }, 3e3),
                    $("#newsletter_email").val(""),
                    $("#card_id").val(""),
                    $("#subscribeButton").prop("disabled", !1),
                    $("#subscribeButton").text("Subscribe"));
            },
            error: function (e) {
                (console.error(e.responseText),
                    $("#errorNewsMessage")
                        .text(emailError)
                        .removeClass("hidden")
                        .fadeIn(),
                    setTimeout(() => {
                        $("#errorNewsMessage").fadeOut();
                    }, 3e3),
                    $("#subscribeButton").prop("disabled", !1),
                    $("#subscribeButton").text("Subscribe"));
            },
        }));
});
