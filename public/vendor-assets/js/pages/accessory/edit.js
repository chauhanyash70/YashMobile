$(function () {
    'use strict';

    // Initialize Select2
    if ($('#brand_id').length) {
        $('#brand_id').select2({
            placeholder: "Select Brand",
            allowClear: true,
            width: '100%'
        });
    }

    // Initialize Datepicker
    const dateElem = document.querySelector('#purchase_date');
    if (dateElem) {
        const today = new Date();
        new Datepicker(dateElem, {
            autohide: true,
            format: 'yyyy-mm-dd',
            maxDate: today
        });
    }

    // Form Validation
    const accessoryForm = $("#accessoryEditForm");
    if (accessoryForm.length) {
        accessoryForm.validate({
            rules: {
                "sku": {
                    required: true,
                    minlength: 2
                },
                "purchase_date": {
                    required: true,
                    date: true
                },
                "brand_id": {
                    required: true
                },
                "name": {
                    required: true,
                    minlength: 2
                },
                "model": {
                    required: true,
                    minlength: 2
                },
                "color": {
                    required: true,
                    minlength: 2
                },
                "stock": {
                    required: true,
                    integer: true,
                    min: 0
                },
                "purchase_price": {
                    required: true,
                    number: true,
                    min: 0
                },
                "sale_price": {
                    required: true,
                    number: true,
                    min: 0
                },
                "supplier_mobile_number": {
                    required: true,
                    minlength: 10,
                    maxlength: 15
                },
                "supplier_name": {
                    required: true
                },
                "city": {
                    required: true
                }
            },
            messages: {
                "sku": {
                    required: "Please enter the serial number",
                    minlength: "Serial number must be at least 2 characters long"
                },
                "purchase_date": {
                    required: "Please select a purchase date"
                },
                "brand_id": {
                    required: "Please select a brand"
                },
                "name": {
                    required: "Please enter the accessory name"
                },
                "model": {
                    required: "Please enter the model"
                },
                "color": {
                    required: "Please enter the color"
                },
                "stock": {
                    required: "Please enter current stock"
                },
                "purchase_price": {
                    required: "Please enter the buy price"
                },
                "sale_price": {
                    required: "Please enter the selling price"
                },
                "supplier_mobile_number": {
                    required: "Please enter supplier phone number"
                },
                "supplier_name": {
                    required: "Please enter supplier name"
                },
                "city": {
                    required: "Please enter city"
                }
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.mb-3').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });
    }

    // Supplier AJAX Search
    $(document).on("focusout", "#supplier_mobile_number", function () {
        const phone = $(this).val();
        if (!phone) return;

        $.ajax({
            type: "POST",
            url: supplierSearchUrl,
            data: { phone: phone },
            dataType: "json",
            headers: { "X-CSRF-TOKEN": csrfToken },
            success: function (response) {
                if (response) {
                    if (response.name) $("#supplier_name").val(response.name);
                    if (response.city) $("#city").val(response.city);
                    if (response.address) $("#address").val(response.address);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    });
});
