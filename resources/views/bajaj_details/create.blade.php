@extends('layouts.app')
@section('title', 'Bajaj Details')
@section('header_title', $header_title ?? 'Bajaj Details')
@section('tagline', $tagline ?? 'Fill details to print a clean and professional Bajaj Details receipt.')

@section('pageCss')
    <link href="{{ asset('vendor-assets/libs/vanillajs-datepicker/css/datepicker.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('bajaj-details.print') }}" method="POST" target="_blank" id="setupReceiptForm">
        @csrf
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Main Form Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 py-3 px-3">
                        <div class="d-flex align-items-center">
                            <i class="iconoir-page-star fs-24 me-2"></i>
                            <h4 class="card-title text-white mb-0 fs-18">Details Form</h4>
                        </div>
                        <div class="d-flex align-items-center w-100 w-sm-auto" style="max-width: 220px; align-self: flex-end;">
                            <span class="me-2 fw-semibold fs-13 text-nowrap">Date:</span>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-primary border-0"><i class="iconoir-calendar"></i></span>
                                <input type="text" name="date" id="date" class="form-control date-picker border-0" value="{{ $currentDate }}" required style="font-weight: 600; color: #212529;">
                            </div>
                        </div>
                    </div>


                    <div class="card-body p-4">



                        <!-- Section 1: Customer Details -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body p-3">
                                <h5 class="card-title text-primary mb-3 d-flex align-items-center">
                                    <i class="iconoir-group me-2"></i> CUSTOMER DETAILS
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="contact_number" class="form-label fw-semibold text-secondary">Contact Number <span class="text-danger">*</span></label>
                                        <div>
                                            <x-intl-tel-input name="contact_number" id="contact_number" placeholder="Enter Contact Number" required="true" />
                                        </div>
                                    </div>


                                    <div class="col-md-4">
                                        <label for="customer_name" class="form-label fw-semibold text-secondary">Customer Name <span class="text-danger">*</span></label>
                                        <input type="text" name="customer_name" id="customer_name" class="form-control" placeholder="Enter Full Name" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="city" class="form-label fw-semibold text-secondary">City</label>
                                        <input type="text" name="city" id="city" class="form-control" placeholder="City / Town">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Product & Device Details -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body p-3">
                                <h5 class="card-title text-primary mb-3 d-flex align-items-center">
                                    <i class="iconoir-smartphone-device me-2"></i> PRODUCT & DEVICE DETAILS
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="imei_no" class="form-label fw-semibold text-secondary">IMEI No <span class="text-danger">*</span></label>
                                        <input type="text" name="imei_no" id="imei_no" class="form-control" placeholder="15 Digit IMEI" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="model" class="form-label fw-semibold text-secondary">Model <span class="text-danger">*</span></label>
                                        <input type="text" name="model" id="model" class="form-control" placeholder="e.g. iPhone 15 Pro Max" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="total_price" class="form-label fw-semibold text-secondary">Total Price (₹) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" step="0.01" name="total_price" id="total_price" class="form-control" placeholder="0.00" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Bajaj Finance EMI Details -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body p-3">
                                <h5 class="card-title text-primary mb-3 d-flex align-items-center">
                                    <i class="iconoir-coins me-2"></i> BAJAJ FINANCE EMI DETAILS
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="down_payment" class="form-label fw-semibold text-secondary">Down Payment <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" step="0.01" name="down_payment" id="down_payment" class="form-control" placeholder="0.00" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="emi_tenure" class="form-label fw-semibold text-secondary">EMI Tenure (Months) <span class="text-danger">*</span></label>
                                        <input type="number" name="emi_tenure" id="emi_tenure" class="form-control" placeholder="e.g. 6, 8, 12" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="monthly_emi" class="form-label fw-semibold text-secondary">Monthly EMI Amt <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" step="0.01" name="monthly_emi" id="monthly_emi" class="form-control" placeholder="0.00" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="first_emi_date" class="form-label fw-semibold text-secondary">First EMI Date <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="iconoir-calendar"></i></span>
                                            <input type="text" name="first_emi_date" id="first_emi_date" class="form-control date-picker" placeholder="YYYY-MM-DD" required>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>


                        <!-- Section 4: Apple ID & Setup Details -->
                        <div class="card border-warning mb-4" style="background-color: rgba(255, 193, 7, 0.05);">
                            <div class="card-body p-3">
                                <h5 class="card-title text-warning-emphasis mb-3 d-flex align-items-center">
                                    <i class="iconoir-security-pass me-2"></i> APPLE ID & SETUP DETAILS (CONFIDENTIAL)
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="apple_id" class="form-label fw-semibold text-secondary">Apple ID / Email</label>
                                        <input type="text" name="apple_id" id="apple_id" class="form-control" placeholder="example@icloud.com">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="apple_password" class="form-label fw-semibold text-secondary">Apple ID Password</label>
                                        <div class="input-group">
                                            <input type="password" name="apple_password" id="apple_password" class="form-control" placeholder="Password">
                                            <button class="btn btn-outline-secondary toggle-password-local" type="button">
                                                <i class="iconoir-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="security_code" class="form-label fw-semibold text-secondary">Security Code/PIN</label>
                                        <input type="text" name="security_code" id="security_code" class="form-control" placeholder="Passcode / Security PIN">
                                    </div>
                                </div>
                                <div class="form-text text-muted mt-2">
                                    <i class="iconoir-info-circle me-1"></i> Note: Please advise the customer to change their password after setup for security purposes.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="card-footer bg-light p-3 d-flex flex-column flex-sm-row justify-content-end gap-2">
                        <button type="reset" class="btn btn-outline-danger w-100 w-sm-auto">
                            <i class="iconoir-undo me-1"></i> Reset Form
                        </button>
                        <button type="submit" class="btn btn-primary px-4 w-100 w-sm-auto">
                            <i class="iconoir-printer me-1"></i> Generate & Print Receipt
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('pageScripts')
<script src="{{ asset('vendor-assets/libs/vanillajs-datepicker/js/datepicker-full.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // Initialize Datepickers
        document.querySelectorAll('.date-picker').forEach(el => {
            new Datepicker(el, {
                autoHide: true,
                format: 'yyyy-mm-dd',
            });
        });

        // Form validation using jQuery validation
        $('#setupReceiptForm').validate({
            rules: {
                date: "required",

                contact_number: {
                    required: true,
                    minlength: 10
                },
                customer_name: "required",
                imei_no: "required",
                model: "required",
                total_price: {
                    required: true,
                    number: true
                },
                down_payment: {
                    required: true,
                    number: true
                },
                monthly_emi: {
                    required: true,
                    number: true
                },
                emi_tenure: {
                    required: true,
                    number: true
                },
                first_emi_date: "required"
            },

            errorElement: 'div',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                if (element.closest('.input-group').length) {
                    element.closest('.input-group').after(error);
                } else {
                    element.parent().append(error);
                }
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });

        // Toggle Password visibility
        $('.toggle-password-local').on('click', function() {
            let input = $(this).closest('.input-group').find('input');
            let icon = $(this).find('i');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('iconoir-eye').addClass('iconoir-eye-closed');
            } else {
                input.attr('type', 'password');
                icon.removeClass('iconoir-eye-closed').addClass('iconoir-eye');
            }
        });


        // Clean up input value (strip +91, 91 or 0) to avoid double country code display
        $('#contact_number').on('input change keyup', function() {
            let val = $(this).val();
            val = val.replace(/[\s\-]/g, ''); // Remove spaces/hyphens
            if (val.startsWith('+91')) {
                $(this).val(val.substring(3));
            } else if (val.startsWith('91') && val.length > 10) {
                $(this).val(val.substring(2));
            } else if (val.startsWith('0')) {
                $(this).val(val.substring(1));
            }
        });

        // Auto-fill Customer details on phone number focusout
        $('#contact_number').on('focusout', function() {

            let phoneVal = $(this).val();
            if (!phoneVal) return;

            $.ajax({
                type: "POST",
                url: "{{ route('invoice.getCustomer') }}",
                data: { phone: phoneVal },
                dataType: "json",
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                success: function (response) {
                    if (response.status && response.customer) {
                        if (response.customer.name) {
                            $("#customer_name").val(response.customer.name);
                        }
                        if (response.customer.city) {
                            $("#city").val(response.customer.city);
                        } else if (response.customer.address) {
                            // Try to extract city from address if city is empty
                            let addressParts = response.customer.address.split(',');
                            let possibleCity = addressParts[addressParts.length - 1].trim();
                            $("#city").val(possibleCity);
                        }
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        });

        // Auto-calculate Device details on IMEI lookup
        $('#imei_no').on('focusout', function() {
            let imeiVal = $(this).val();
            if (!imeiVal) return;

            $.ajax({
                type: "POST",
                url: "{{ route('bajaj-details.getDevice') }}",
                data: { imei: imeiVal },
                dataType: "json",
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                success: function (response) {
                    if (response.status && response.device) {
                        if (response.device.model) {
                            $("#model").val(response.device.model);
                        }
                        if (response.device.price) {
                            $("#total_price").val(response.device.price);
                            calculateEMI(); // Recalculate EMI if total price changes
                        }
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        });

        // Auto-calculate Monthly EMI
        function calculateEMI() {
            let totalPrice = parseFloat($('#total_price').val()) || 0;
            let downPayment = parseFloat($('#down_payment').val()) || 0;
            let tenure = parseInt($('#emi_tenure').val()) || 0;

            if (totalPrice > 0 && tenure > 0) {
                let remaining = totalPrice - downPayment;
                if (remaining > 0) {
                    let emi = remaining / tenure;
                    $('#monthly_emi').val(emi.toFixed(2));
                } else {
                    $('#monthly_emi').val('0.00');
                }
            } else {
                $('#monthly_emi').val('');
            }
        }

        // Trigger calculation on input change
        $('#total_price, #down_payment, #emi_tenure').on('input change', function() {
            calculateEMI();
        });
    });
</script>

@endsection
