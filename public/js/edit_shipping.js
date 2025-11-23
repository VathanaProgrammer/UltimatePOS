// =====================
// GLOBAL VARIABLES
// =====================
window._invoiceFiles = [];
window._shippingFiles = [];
var invoiceDropzone = null;
var shippingDropzone = null;

// =====================
// INVOICE DROPZONE
// =====================
$("#include_invoice_checkbox").on("change", function () {
    console.log("Invoice checkbox changed:", $(this).is(":checked"));

    if ($(this).is(":checked")) {
        $("#invoice_dropzone").show();
        $("#invoice_status").text("Select invoice file(s) to upload.");

        if (!invoiceDropzone && $("#invoice_dropzone").length) {
            console.log("Initializing invoice Dropzone...");
            invoiceDropzone = new Dropzone("#invoice_dropzone", {
                url: "/", // dummy, files submitted manually
                autoProcessQueue: false,
                addRemoveLinks: true,
                paramName: "invoice_files[]",
                init: function () {
                    this.on("addedfile", function (file) {
                        console.log("Invoice added:", file.name);
                        window._invoiceFiles.push(file);
                        $("#invoice_status").text(file.name + " ready to upload.");
                    });
                    this.on("removedfile", function (file) {
                        console.log("Invoice removed:", file.name);
                        window._invoiceFiles = window._invoiceFiles.filter(f => f !== file);
                        $("#invoice_status").text(window._invoiceFiles.length ? "Files ready." : "No invoice selected.");
                    });
                }
            });
        }
    } else {
        console.log("Invoice checkbox unchecked");
        $("#invoice_dropzone").hide();
        $("#invoice_status").text("No invoice selected.");
        if (invoiceDropzone) {
            invoiceDropzone.removeAllFiles(true);
            console.log("All invoice files cleared");
        }
        window._invoiceFiles = [];
    }
});

// =====================
// SHIPPING DROPZONE
// =====================
function initShippingDropzone() {
    if (!shippingDropzone && $("#shipping_dropzone").length) {
        console.log("Initializing shipping Dropzone...");
        shippingDropzone = new Dropzone("#shipping_dropzone", {
            url: "/", // dummy, files submitted manually
            autoProcessQueue: false,
            addRemoveLinks: true,
            paramName: "shipping_documents[]",
            init: function () {
                this.on("addedfile", function (file) {
                    console.log("Shipping added:", file.name);
                    window._shippingFiles.push(file);
                });
                this.on("removedfile", function (file) {
                    console.log("Shipping removed:", file.name);
                    window._shippingFiles = window._shippingFiles.filter(f => f !== file);
                });
            }
        });
    } else if (!$("#shipping_dropzone").length) {
        console.warn("Shipping dropzone element not found!");
    }
}

// Initialize shipping Dropzone once
$(document).ready(function () {
    console.log("Document ready, initializing shipping Dropzone");
    initShippingDropzone();

    // Bind form submit only once to prevent double submission
    // Bind submit once globally
if (!$('#edit_shipping_form').data('submit-bound')) {
    $('#edit_shipping_form').on('submit', function(e) {
        e.preventDefault();
        console.log("Submitting form");

        var formData = new FormData(this);

        window._invoiceFiles.forEach(f => formData.append('invoice_files[]', f));
        window._shippingFiles.forEach(f => formData.append('shipping_documents[]', f));

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log("Form submitted successfully:", response);
                toastr.success("Form submitted successfully!");
            },
            error: function(err) {
                console.error("Form submission error:", err);
                toastr.error("Error submitting form!");
            }
        });
    }).data('submit-bound', true);
}

});
