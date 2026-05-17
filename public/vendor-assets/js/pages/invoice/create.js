$(document)
    .ready(function () {
        select2Init();
    })
    .on("change", ".product_id", function () {
        let $select = $(this);
        let row = $select.closest("[data-repeater-item]");
        let selectedOption = $select.find("option:selected");
        let productData = selectedOption.data("product");
        if ($select.hasClass("is-invalid")) {
            $select.removeClass("is-invalid");
            $select.closest(".form-group").find("label.error").remove();
        }
        if (!productData) {
            row.find('[name$="[name]"]').val('');
            row.find('[name$="[color]"]').val('');
            row.find('[name$="[hsn_number]"]').val('');
            row.find('[name$="[storage]"]').val('');
            row.find('[name$="[ram]"]').val('');
            row.find('.item-type-select').val('device').trigger("change");
            row.find('.brand-select').val('').trigger("change");
            row.find('[name$="[quantity]"]').val(1).trigger("change");
            row.find('[name$="[price]"]').val(0).trigger("change");
            row.find('.item_sub_total').text('0.00');
            total();
            updateDisabledProductOptions();
            return;
        }

        row.find('[name$="[name]"]').val(productData.name);
        row.find('[name$="[color]"]').val(productData.color);
        row.find('[name$="[hsn_number]"]').val(
            productData.hsn_number
        );
        row.find('[name$="[storage]"]').val(productData.storage);
        row.find('[name$="[ram]"]').val(productData.ram);
        row.find('[name$="[battery_health]"]').val(productData.battery_health);

        // Show/hide battery health div based on availability
        if (productData.battery_health) {
            row.find('.battery-health-div').show();
        } else {
            row.find('.battery-health-div').hide();
        }

        // Populate and trigger item_type and brand
        row.find('.item-type-select').val(productData.type).trigger("change");
        row.find('.brand-select').val(productData.brand_id).trigger("change");

        // Ensure manual entry mode is off when a product is selected
        let $manualCheckbox = row.find('.manual-entry-checkbox');
        if ($manualCheckbox.is(':checked')) {
            $manualCheckbox.prop('checked', false);
        }
        row.find('.manual_div input').prop('readonly', true);
        row.find('.manual_div select').prop('disabled', true);
        row.find('.quantity-input').prop('readonly', true);

        row.find('[name$="[quantity]"]').val(1).trigger("change").blur();
        row.find('[name$="[price]"]')
            .val(productData.price)
            .trigger("change")
            .blur();
        $select.valid();
        updateDisabledProductOptions();
    })
    .on("change", ".manual-entry-checkbox", function () {
        let $checkbox = $(this);
        let row = $checkbox.closest("[data-repeater-item]");
        let $productSelect = row.find('.product_id');
        let $manualInputs = row.find('.manual_div input, .manual_div select');

        if ($checkbox.is(':checked')) {
            // Manual Mode
            $productSelect.val('').trigger('change'); // Clear selection
            $productSelect.prop('disabled', true);
            $manualInputs.prop('readonly', false).prop('disabled', false);
            row.find('.quantity-input').prop('readonly', false);
            row.find('.supplier-details-div').show();

            // Remove required from product select, add to manual inputs
            $productSelect.rules('remove', 'required');

            row.find('[name$="[item_type]"]').rules('add', { required: true });
            row.find('[name$="[brand_id]"]').rules('add', { required: true });
            row.find('[name$="[name]"]').rules('add', { required: true });
            row.find('[name$="[hsn_number]"]').rules('add', { required: true });
            row.find('[name$="[color]"]').rules('add', { required: true });
            row.find('[name$="[storage]"]').rules('add', { required: true });
            row.find('[name$="[ram]"]').rules('add', { required: true });
            row.find('[name$="[supplier_phone]"]').rules('add', { required: true });
            row.find('[name$="[supplier_name]"]').rules('add', { required: true });
            row.find('[name$="[supplier_address]"]').rules('add', { required: true });
            row.find('[name$="[buy_price]"]').rules('add', { required: true });

            // Trigger filtering based on default/current item_type
            row.find('.item-type-select').trigger('change');
            row.find('.brand-select').trigger('change');
        } else {
            // Product Mode
            $productSelect.prop('disabled', false);
            $manualInputs.prop('readonly', true);
            row.find('.quantity-input').prop('readonly', true);
            row.find('.supplier-details-div').hide();
            // For select inputs in manual div, we might need to disable them
            row.find('.manual_div select').prop('disabled', true);

            // Restore required to product select
            $productSelect.rules('add', { required: true });

            // Remove rules from manual inputs
            row.find('[name$="[item_type]"]').rules('remove', 'required');
            row.find('[name$="[brand_id]"]').rules('remove', 'required');
            row.find('[name$="[name]"]').rules('remove', 'required');
            row.find('[name$="[hsn_number]"]').rules('remove', 'required');
            row.find('[name$="[color]"]').rules('remove', 'required');
            row.find('[name$="[storage]"]').rules('remove', 'required');
            row.find('[name$="[ram]"]').rules('remove', 'required');
            row.find('[name$="[supplier_phone]"]').rules('remove', 'required');
            row.find('[name$="[supplier_name]"]').rules('remove', 'required');
            row.find('[name$="[supplier_address]"]').rules('remove', 'required');
            row.find('[name$="[buy_price]"]').rules('remove', 'required');
        }
    })
    .on("change", '.item-type-select', function () {
        let row = $(this).closest("[data-repeater-item]");
        let type = $(this).val();
        let $brandSelect = row.find('.brand-select');

        $brandSelect.find('option').each(function () {
            let brandType = $(this).data('type');
            if (brandType === 'both' || brandType === type || !brandType) {
                $(this).prop('hidden', false);
                $(this).prop('disabled', false); // Ensure it's enabled
            } else {
                $(this).prop('hidden', true);
                $(this).prop('disabled', true); // Also disable so it can't be selected
            }
        });

        // Reset selection if current selection is invalid
        let currentVal = $brandSelect.val();
        let selectedOption = $brandSelect.find('option:selected');
        if (selectedOption.prop('hidden') || selectedOption.prop('disabled')) {
            $brandSelect.val('').trigger('change');
        }

        // Hide battery health if accessory is selected
        if (type === 'accessory') {
            row.find('.battery-health-div').hide().find('input').val('');
        }
    })
    .on("change", ".brand-select", function () {
        let row = $(this).closest("[data-repeater-item]");
        let slug = $(this).find('option:selected').data('slug');
        let isManual = row.find('.manual-entry-checkbox').is(':checked');
        let itemType = row.find('.item-type-select').val();

        if (isManual && itemType === 'device' && slug === 'apple') {
            row.find('.battery-health-div').show();
        } else if (isManual) {
            row.find('.battery-health-div').hide().find('input').val('');
        }
    })
    .on('input', '.supplier_phone', function() {
        let $input = $(this);
        let phone = $input.val();
        let row = $input.closest('[data-repeater-item]');

        if (phone.length >= 10) {
            $.ajax({
                url: supplierSearchUrl,
                method: 'POST',
                data: {
                    phone: phone,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        row.find('[name$="[supplier_name]"]').val(response.supplier.name);
                        row.find('[name$="[supplier_address]"]').val(response.supplier.address);
                        toastr.success('Supplier details found and populated.');
                    }
                }
            });
        }
    })
    .on("keypress", "#barcode", function (e) {
        if (e.which === 13) {
            e.preventDefault();

            let barcode = $(this).val().trim();

            if (!barcode) return;

            $.ajax({
                url: `/invoices/get-product-by-barcode/${barcode}`,
                type: "GET",
                success: function (data) {
                    if (data.status === true) {
                        let productId = data.product.unique_id;
                        let alreadyExists = false;
                        $("[data-repeater-item]").each(function () {
                            let existingId = $(this)
                                .find('[name$="[product_id]"]')
                                .val();
                            if (existingId == productId) {
                                alreadyExists = true;
                                return false;
                            }
                        });

                        if (alreadyExists) {
                            Swal.fire({
                                icon: "info",
                                title: "Already Added",
                                text: "This product is already in the list.",
                            });
                            $("#barcode").val("");
                            return;
                        }

                        let $rows = $("[data-repeater-item]");
                        let $targetRow = null;

                        // Check if we have exactly one row and it is empty
                        if ($rows.length === 1) {
                            let firstRowVal = $rows.first().find('[name$="[product_id]"]').val();
                            if (!firstRowVal) {
                                $targetRow = $rows.first();
                            }
                        }

                        let populateRow = function (row) {
                            row.find('[name$="[product_id]"]').val(data.product.unique_id).trigger("change");

                            row.find('[name$="[name]"]').val(data.product.name);
                            row.find('[name$="[color]"]').val(data.product.color);
                            row.find('[name$="[hsn_number]"]').val(data.product.hsn_number);
                            row.find('[name$="[storage]"]').val(data.product.storage);
                            row.find('[name$="[ram]"]').val(data.product.ram);
                            row.find('[name$="[battery_health]"]').val(data.product.battery_health);

                            if (data.product.battery_health) {
                                row.find('.battery-health-div').show();
                            } else {
                                row.find('.battery-health-div').hide();
                            }

                            row.find('.item-type-select').val(data.product.type).trigger("change");
                            row.find('.brand-select').val(data.product.brand_id).trigger("change");

                            row.find('[name$="[quantity]"]').val(1);
                            row.find('[name$="[price]"]').val(data.product.price).trigger("change").trigger("input").blur();
                            $("#barcode").val("");
                            total();
                        };

                        if ($targetRow) {
                            populateRow($targetRow);
                        } else {
                            $("[data-repeater-create]").trigger("click");
                            setTimeout(function () {
                                let newRow = $("[data-repeater-item]").last();
                                populateRow(newRow);
                            }, 100);
                        }

                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Not Found",
                            text: "Product not found with this barcode.",
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Could not fetch product data.",
                    });
                },
            });
        }
    })
    .on(
        "blur change stepDown stepUp",
        'input[name^="invoice_items"]',
        function () {
            $(this).valid();
        }
    )
    .on("click", ".increment, .decrement", function () {
        let row = $(this).closest("tr");
        let input = row.find(".quantity-input");
        let isManual = row.find(".manual-entry-checkbox").is(":checked");
        let productQty =
            Number(
                row
                    .find(".product_id")
                    .find("option:selected")
                    .attr("data-quantity")
            ) || 0;

        if ($(this).hasClass("increment")) {
            if (isManual || input.val() < productQty) {
                input[0].stepUp();
            }
        } else {
            input[0].stepDown();
        }
        createInvoiceForm
            .find('input[name^="invoice_items"]')
            .each(function () {
                let name = $(this).attr("name");
                if (name.includes("[price]")) {
                    let price = $(this).val();
                    let row = $(this).closest("[data-repeater-item]");
                    let quantity = row.find(".quantity-input").val();
                    let subTotal = (parseFloat(price) || 0) * (parseInt(quantity) || 0);
                    row.find(".item_sub_total").text(subTotal.toFixed(2));
                }
            });
        input.trigger("change");
        total();
    })
    .on("blur change keyup", ".price, .quantity-input", function () {
        createInvoiceForm
            .find('input[name^="invoice_items"]')
            .each(function () {
                let name = $(this).attr("name");
                if (name.includes("[price]")) {
                    let price = $(this).val();
                    let row = $(this).closest("[data-repeater-item]");
                    let quantity = row.find(".quantity-input").val();
                    let subTotal = (parseFloat(price) || 0) * (parseInt(quantity) || 0);
                    row.find(".item_sub_total").text(subTotal.toFixed(2));
                }
            });
        total();
    })
    .on("focusout", "#mobile", function () {
        $.ajax({
            type: "POST",
            url: customerSearchUrl,
            data: { phone: $(this).val() },
            dataType: "json",
            headers: { "X-CSRF-TOKEN": csrfToken },
            success: function (response) {
                if (response.status && response.customer) {
                    if (response.customer.name) {
                        $("#name").val(response.customer.name);
                    }
                    if (response.customer.phone) {
                        $("#mobile").val(response.customer.phone);
                    }
                    if (response.customer.address) {
                        $("#address").val(response.customer.address);
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", error);
                console.error("Response:", xhr.responseText);
            },
        });
    });

    $(".repeater").repeater({
        initEmpty: false,
        show: function () {
            $(this).slideDown();

            // Initialize row state
            let $newRow = $(this);
            $newRow.find(".manual-entry-checkbox").prop("checked", false);
            $newRow.find(".manual_div input").prop("readonly", true);
            $newRow.find(".manual_div select").prop("disabled", true);
            $newRow.find(".supplier-details-div").hide();
            $newRow.find(".product_id").prop("disabled", false);

            // Re-init select2 for the new row
            var $select = $newRow.find('.product_id');
            $select.next('.select2-container').remove();
            $select.removeClass('select2-hidden-accessible');
            $select.removeAttr('data-select2-id');
            $select.find('option').removeAttr('data-select2-id');

            $select.select2({
                placeholder: "Select a product",
                allowClear: true,
                width: "100%",
            });

            // Add validation rules for new fields
            $newRow.find('input, select').each(function () {
                let name = $(this).attr("name");
                if (!name) return;

                if (name.includes("[product_id]")) {
                    $(this).rules("add", {
                        required: true,
                        messages: { required: "The product field is required" },
                    });
                }
                if (name.includes("[quantity]")) {
                    $(this).rules("add", {
                        required: true,
                        min: 1,
                        messages: {
                            required: "The quantity field is required",
                            min: "Must be at least 1",
                        },
                    });
                }
                if (name.includes("[price]")) {
                    $(this).rules("add", {
                        required: true,
                        messages: { required: "The price field is required" },
                    });
                }
            });

            updateDisabledProductOptions();
            total();
        },
        hide: function (deleteElement) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $(this).slideUp(function() {
                        $(this).remove();
                        total();
                        updateDisabledProductOptions();
                    });
                }
            });
        },
        ready: function (setIndexes) {
            updateDisabledProductOptions();
            total();
        },
    });

    const elem = document.querySelector('input[name="invoice_date"]');
    const today = new Date();
    if (elem) {
        elem.value = today.toISOString().split("T")[0];
        new Datepicker(elem, {
            defaultDate: today,
            maxDate: today,
            autoHide: true,
            format: "yyyy-mm-dd",
        });
    }

    function select2Init() {
        $(".product_id").each(function () {
            if ($.data(this, "select2")) {
                $(this).select2("destroy");
            }
            $(this).select2({
                placeholder: "Select a product",
                allowClear: true,
                width: "100%",
            });
        });
    }

    var createInvoiceForm = $("#createInvoiceForm");
    if (createInvoiceForm.length) {
        // Class-based rules for dynamic elements
        jQuery.validator.addClassRules("price", {
            required: true,
            min: 1
        });

        var createInvoiceFormValidator = createInvoiceForm.validate({
            focusInvalid: true,
            rules: {
                name: { required: true },
                mobile: { required: true },
                address: { required: true },
                "invoice_items[0][product_id]": { required: true },
                "invoice_items[0][quantity]": { required: true, min: 1 },
                "invoice_items[0][price]": { required: true },
            },
            messages: {
                name: { required: "The name field is required" },
                mobile: { required: "The mobile field is required" },
                address: { required: "The address field is required" },
                "invoice_items[0][product_id]": { required: "The product filed is required" },
                "invoice_items[0][quantity]": {
                    required: "The quantity field is required",
                    min: "Must be at least 1",
                },
                "invoice_items[0][price]": { required: "The price field is required" },
            },
            errorPlacement: function (error, element) {
                if (element.closest(".input-group").length) {
                    element.closest(".input-group").after(error);
                } else if (element.hasClass("select2-hidden-accessible")) {
                    error.insertAfter($(element).next(".select2-container").find(".dropdown-wrapper"));
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function (form) {
                form.submit();
            },
        });
    }

    function total() {
        var subTotal = document.getElementsByClassName("item_sub_total");
        var sum = 0;
        for (var i = 0; i < subTotal.length; i++) {
            var value = subTotal[i].innerText;
            if (value == "") {
                value = 0;
            }
            sum += parseFloat(value);
        }

        $("#total").text(sum.toFixed(2));
    }

    function updateDisabledProductOptions() {
        let selectedProductIds = [];
        $(".product_id").each(function () {
            let val = $(this).val();
            if (val) selectedProductIds.push(val);
        });
        $(".product_id").each(function () {
            let $select = $(this);
            let currentVal = $select.val();

            $select.find("option").each(function () {
                let optionVal = $(this).val();
                if (!optionVal) return;
                if (selectedProductIds.includes(optionVal) && optionVal !== currentVal) {
                    $(this).prop("disabled", true);
                } else {
                    $(this).prop("disabled", false);
                }
            });
            if ($select.hasClass("select2-hidden-accessible")) {
                $select.trigger("change.select2");
            }
        });
    }
