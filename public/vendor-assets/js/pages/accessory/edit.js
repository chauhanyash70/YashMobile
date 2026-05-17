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
                "hsn": {
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
                }
            },
            messages: {
                "hsn": {
                    required: "Please enter the HSN number",
                    minlength: "HSN number must be at least 2 characters long"
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
                }
            },
            errorElement: 'div',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                if (element.parent('.input-group').length) {
                    error.insertAfter(element.parent());
                } else if (element.hasClass('select2-hidden-accessible')) {
                    error.insertAfter(element.next('.select2-container'));
                } else {
                    error.insertAfter(element);
                }
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
                if (response && response.status) {
                    if (response.customer.name) $("#supplier_name").val(response.customer.name);
                    if (response.customer.city) $("#city").val(response.customer.city);
                    if (response.customer.address) $("#address").val(response.customer.address);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    });
});
