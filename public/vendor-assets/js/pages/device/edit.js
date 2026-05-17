$(document).ready(function () {
    // 1. Initialize Datepicker
    function initDatepicker(elem) {
        new Datepicker(elem, {
            autoHide: true,
            format: 'yyyy-mm-dd',
        });
    }

    // Initialize existing datepickers
    $('.date-picker').each(function () {
        initDatepicker(this);
    });

    // 2. Initialize Form Validation
    // The ID in edit.blade.php is actually #deviceEditForm
    var validator = $("#deviceEditForm").validate({
        focusInvalid: true,
        rules: {
            "brand_id": { required: true },
            "model_name": { required: true, minlength: 2 },
            "storage": { required: true },
            "ram": { required: true },
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

    // 3. Add Class Rules
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

    // 4. Add Unit Dynamic Logic
    $('#add-unit-btn').on('click', function () {
        let index = nextIndex++;
        let html = `
        <div class="card mb-3 unit-card border-warning" data-index="${index}">
            <div class="card-header unit-header d-flex justify-content-between align-items-center bg-warning-subtle">
                <span class="fw-bold text-dark">New Unit #${index + 1}</span>
                <button type="button" class="btn btn-link text-danger p-0 remove-unit">
                    <i class="iconoir-trash"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">IMEI / Serial Number<span class="text-danger">*</span></label>
                        <input type="text" name="units[${index}][imei]" class="form-control fw-bold border-warning imei-field" placeholder="Scan/Type IMEI" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Buy Price (₹)<span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="units[${index}][buy_price]" class="form-control buy-price-field" value="0" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Repair (₹)</label>
                        <input type="number" step="0.01" name="units[${index}][repair_cost]" class="form-control" value="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Purchase Date<span class="text-danger">*</span></label>
                        <input type="text" name="units[${index}][purchase_date]" class="form-control date-picker-new" value="${new Date().toISOString().split('T')[0]}" required>
                    </div>

                    <div class="col-12"><hr class="my-1"></div>
                    <div class="col-12"><h6 class="small fw-bold">Supplier Details</h6></div>
                    
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Supplier Phone<span class="text-danger">*</span></label>
                        <input type="text" name="units[${index}][supplier_phone]" class="form-control supplier-phone" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Supplier Name<span class="text-danger">*</span></label>
                        <input type="text" name="units[${index}][supplier_name]" class="form-control supplier-name" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">City</label>
                        <input type="text" name="units[${index}][supplier_city]" class="form-control supplier-city">
                    </div>
                </div>
            </div>
        </div>`;

        $('#units-container').append(html);
        initDatepicker($('.date-picker-new').last()[0]);
        updateTotalCount();
    });

    // Remove Unit
    $(document).on('click', '.remove-unit', function () {
        if ($('#units-container .unit-card').length > 1) {
            $(this).closest('.unit-card').remove();
            updateTotalCount();
        } else {
            // alert('At least one unit is required.'); // Or use toastr if available
            if (window.toastr) toastr.warning('At least one unit is required.');
            else alert('At least one unit is required.');
        }
    });

    function updateTotalCount() {
        $('#total-units').text($('#units-container .unit-card').length);
    }

    // Supplier Lookup
    $(document).on('focusout', '.supplier-phone', function () {
        let $phoneInput = $(this);
        let $card = $phoneInput.closest('.unit-card');
        let phone = $phoneInput.val();

        if (phone.length >= 10) {
            $.post(supplierSearchUrl, {
                phone: phone,
                _token: csrfToken
            }, function (res) {
                if (res && res.name) {
                    $card.find('.supplier-name').val(res.name);
                    $card.find('.supplier-city').val(res.city);
                }
            });
        }
    });
});
