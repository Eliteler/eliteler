/* Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Designed by NativeCode - https://nativecode.in
 | All rights reserved
 |--------------------------------------------------------------------------
*/

$(document).on("click", ".open-model", function() {
    "use strict";
    $('#deleteModal').modal('show');
    var cardId = $(this).data('id');
    var link = "/user/card-status/" + cardId;
    var preview = document.getElementById("plan_id"); //getElementById instead of querySelectorAll
    preview.setAttribute("href", link);
    // As pointed out in comments,
    // it is unnecessary to have to manually call the modal.
});

$(document).on("click", ".open-plan-model", function() {
    "use strict";
    $('#planModal').modal('show');
    var planId = $(this).data('id');
    var link = "/user/checkout/" + planId;
    var preview = document.getElementById("plan_id"); //getElementById instead of querySelectorAll
    preview.setAttribute("href", link);
});

$(document).on("click", ".down-plan-model", function() {
    "use strict";
    $('#downPlanModel').modal('show');
});

$(document).on("click", ".open-qr", function() {
    "use strict";
    $('#openQR').modal('show');
    $('#openQR').css('display','block');
});

// QR
function downloadQr(){
    "use strict";

    var imgElement = $(".qr-code").children("img")[0];
    if (!imgElement) return;
    
    var cardName = document.getElementById('qr_card_name') ? document.getElementById('qr_card_name').textContent : '';
    
    // Create a temporary canvas for composition
    var canvas = document.createElement("canvas");
    var ctx = canvas.getContext("2d");
    var qrSize = imgElement.naturalWidth || imgElement.width || 256;
    var padding = cardName ? 50 : 0;
    
    canvas.width = qrSize;
    canvas.height = qrSize + padding;
    
    // Background
    ctx.fillStyle = "#ffffff";
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Draw Text
    if (cardName) {
        ctx.fillStyle = "#000000";
        ctx.font = "bold 20px Arial";
        ctx.textAlign = "center";
        ctx.textBaseline = "middle";
        ctx.fillText(cardName, canvas.width / 2, padding / 2);
    }
    
    // Draw QR Code
    ctx.drawImage(imgElement, 0, padding, qrSize, qrSize);
    
    var combinedDataURL = canvas.toDataURL("image/png");
    
    // Create a hidden form to trigger the server-side download
    var form = document.createElement("form");
    form.method = "POST";
    form.action = "/download-qr-image";
    form.style.display = "none";
    
    // CSRF Token from meta tag
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
}

function updateQr(cardURL, cardName) {
    "use strict";

    // Show card name in the modal if provided
    var nameEl = document.getElementById('qr_card_name');
    if (nameEl) {
        nameEl.textContent = cardName || '';
    }

    var qrtext = cardURL;
    var fill = "#000000";
    var bg = "#ffffff";
    $("#download").show();

    var normalParams ={
        mode: 0,
        ecLevel:'H',
        text: qrtext,
        render:'image',
        fill: fill,
        background: bg,
    };

    $(".qr-code").html("");
    $(".qr-code").qrcode(normalParams);
}