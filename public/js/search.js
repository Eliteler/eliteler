"use strict";

// Open modal (desktop input or Ctrl+K)
$("#adminPageSearchTrigger, #openSearchModalMobile").on(
    "click focus",
    function () {
        $("#pageSearchModal").modal("show");
    },
);

// Ctrl + K shortcut
document.addEventListener("keydown", function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "k") {
        e.preventDefault();
        $("#pageSearchModal").modal("show");
    }
});

// Focus input when modal opens
$("#pageSearchModal").on("shown.bs.modal", function () {
    $("#adminPageSearch").val("").focus();
    $("#adminPageResults").empty();
});

// Close on ESC
document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
        $("#pageSearchModal").modal("hide");
    }
});

// AJAX page search
$("#adminPageSearch").on("keyup", function () {
    const q = this.value.trim();
    const $results = $("#adminPageResults");

    // Reset
    $results.addClass("d-none").html("");

    if (q.length < 2) {
        return;
    }

    $.get(
        window.pageSearchUrl,
        {
            q,
        },
        function (res) {
            // Always show container when response arrives
            $results.removeClass("d-none");

            if (!res.length) {
                $results.html(
                    `<div class="empty text-muted text-center py-3 text-capitalize fw-bold">
                    ${window.resultNoResults}
                 </div>`,
                );
                return;
            }

            let html = "";
            res.forEach((item) => {
                html += `
                <a href="${item.url}" class="dropdown-item">
                    ${item.label}
                </a>`;
            });

            $results.html(html);
        },
    );
});
