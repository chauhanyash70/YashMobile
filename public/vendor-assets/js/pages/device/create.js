$(document).ready(function () {
    // 1. Initialize Datepicker
    function initDatepicker(elem) {
        new Datepicker(elem, {
            autoHide: true,
            format: 'yyyy-mm-dd',
        });
    }

    // Initialize existing datepickers
    $('.date-picker').each(function() {
        initDatepicker(this);
    });

    // 2. Add Validator Methods if needed
    // (Optional: add custom method for specific patterns if required)

    // 3. Initialize Form Validation
    var validator = $("#deviceCreateForm").validate({
        focusInvalid: true,
        rules: {
            "brand_id": { required: true },
            "model_name": { required: true, minlength: 2 },
            "storage": { required: true },
            "color": { required: true },
            "condition": { required: true }
        },
        messages: {
            "brand_id": { required: "Please select a brand" },
            "model_name": { required: "Please enter the model name" }
        },
        errorElement: 'div',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            if (element.closest(".input-group").length) {
                element.closest(".input-group").after(error);
            } else if (element.hasClass("select2-hidden-accessible")) {
                error.insertAfter(element.next(".select2-container"));
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: function (form) {
            form.submit();
        }
    });

    // 4. Add Class Rules for Dynamic Fields
    // These classes are added to the inputs in the blade file (or JS template)
    $.validator.addClassRules("imei-field", {
        required: true,
        minlength: 8
    });

    $.validator.addClassRules("buy-price-field", {
        required: true,
        number: true,
        min: 0
    });
    
    $.validator.addClassRules("supplier-phone", {
        required: true,
        minlength: 10
    });

    $.validator.addClassRules("supplier-name", {
        required: true
    });

    // 5. Dynamic "Add Unit" Logic
    $('#add-unit-btn').on('click', function() {
        let index = nextIndex++;
        
        // Get last values to pre-fill
        let lastDateVal = $('.date-picker, .date-picker-new').last().val();
        // Fallback to today if empty, but usually it has a value. 
        // We can use a simpler approach: new Date().toISOString().split('T')[0] if needed.
        if(!lastDateVal) {
             const d = new Date();
             lastDateVal = d.getFullYear() + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2);
        }

        let lastPhone = $('.supplier-phone').last().val() || '';
        let lastName = $('.supplier-name').last().val() || '';
        let lastCity = $('.supplier-city').last().val() || '';

        let html = `
        <div class="card mb-3 unit-card" data-index="${index}">
            <div class="card-header unit-header d-flex justify-content-between align-items-center">
                <span class="fw-bold text-success">Unit #${index + 1}</span>
                <button type="button" class="btn btn-link text-danger p-0 remove-unit">
                    <i class="iconoir-trash"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">IMEI / Serial Number</label>
                        <input type="text" name="units[${index}][imei]" class="form-control fw-bold border-success imei-field" placeholder="Scan or Type" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Buy Price (₹)</label>
                        <input type="number" step="0.01" name="units[${index}][buy_price]" class="form-control buy-price-field" placeholder="0.00" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Repair (₹)</label>
                        <input type="number" step="0.01" name="units[${index}][repair_cost]" class="form-control" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Purchase Date</label>
                        <input type="text" name="units[${index}][purchase_date]" class="form-control date-picker-new" value="${lastDateVal}" required>
                    </div>

                    <div class="col-12"><hr class="my-1"></div>
                    
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Supplier Phone</label>
                        <input type="text" name="units[${index}][supplier_phone]" class="form-control supplier-phone" value="${lastPhone}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Supplier Name</label>
                        <input type="text" name="units[${index}][supplier_name]" class="form-control supplier-name" value="${lastName}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">City</label>
                        <input type="text" name="units[${index}][supplier_city]" class="form-control supplier-city" value="${lastCity}">
                    </div>
                </div>
            </div>
        </div>`;
        
        $('#units-container').append(html);
        
        // Initialize datepicker for the new element
        initDatepicker($('.date-picker-new').last()[0]);
    });

    $(document).on('click', '.remove-unit', function() {
        $(this).closest('.unit-card').remove();
    });

    // 6. Supplier Lookup (AJAX)
    $(document).on('focusout', '.supplier-phone', function() {
        let $card = $(this).closest('.unit-card');
        let phone = $(this).val();
        if (phone.length >= 10) {
            $.post(supplierSearchUrl, { phone: phone, _token: csrfToken }, function(res) {
                if (res && res.name) {
                    $card.find('.supplier-name').val(res.name);
                    $card.find('.supplier-city').val(res.city);
                }
            });
        }
    });

    // 7. IMEI Details Lookup (Optional: logic from original file was for single unit, adapted here if needed)
    // The previous file had logic to auto-fill brand/model based on IMEI.
    // If that is desired for multi-unit, it's complex because global fields are for ALL units.
    // For now, I'll assume IMEI lookup for pre-filling GLOBAL fields is good.
    // But which IMEI? Probably the first one?
    // The original logic was #imei events. Now we have .imei-field.
    
    $(document).on("focusout", ".imei-field", function () {
        // Only run if it's the first unit? Or maybe we don't want to change global specs based on unit IMEI anymore?
        // The blade file separates "Global Specs" from "Individual Units".
        // Changing global specs based on one unit might be unexpected. 
        // I will OMIT the auto-fill of global specs from unit IMEI for now to avoid confusion, 
        // as the user's prompt is about validation.
    });
});
