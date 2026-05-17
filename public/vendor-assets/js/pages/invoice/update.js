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
		if (!productData) return;

		row.find('[name$="[name]"]').val(productData.name);
		row.find('[name$="[color]"]').val(productData.color);
		row.find('[name$="[imei_or_serial_number]"]').val(
			productData.imei_or_serial_number
		);
		row.find('[name$="[storage]"]').val(productData.storage);
		row.find('[name$="[ram]"]').val(productData.ram);

		// Populate and trigger item_type and brand
		row.find('.item-type-select').val(productData.type).trigger("change");
		row.find('.brand-select').val(productData.brand_id).trigger("change");

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
		let $manualInputs = row.find('#manual_div input, #manual_div select');

		if ($checkbox.is(':checked')) {
			// Manual Mode
			$productSelect.val('').trigger('change'); // Clear selection
			$productSelect.prop('disabled', true);
			$manualInputs.prop('readonly', false).prop('disabled', false);
			row.find('.quantity-input').prop('readonly', false);

			// Remove required from product select, add to manual inputs
			$productSelect.rules('remove', 'required');

			// Trigger filtering based on default/current item_type
			row.find('.item-type-select').trigger('change');
		} else {
			// Product Mode
			$productSelect.prop('disabled', false);
			$manualInputs.prop('readonly', true);
			row.find('.quantity-input').prop('readonly', true);
			// For select inputs in manual div, we might need to disable them
			row.find('#manual_div select').prop('disabled', true);

			// Restore required to product select
			$productSelect.rules('add', { required: true });
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
							row.find('[name$="[imei_or_serial_number]"]').val(data.product.imei_or_serial_number);
							row.find('[name$="[storage]"]').val(data.product.storage);
							row.find('[name$="[ram]"]').val(data.product.ram);

							row.find('.item-type-select').val(data.product.type).trigger("change");
							row.find('.brand-select').val(data.product.brand_id).trigger("change");

							row.find('[name$="[quantity]"]').val(1);
							row.find('[name$="[price]"]').val(data.product.price).trigger("change").blur();
							$("#barcode").val("");
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
					let quantity = $(this)
						.parent()
						.parent()
						.find(".quantity-input")
						.val();
					let discount = $(this)
						.parent()
						.parent()
						.find(".discount")
						.val();
					let subTotal = price * quantity - discount;
					$(this)
						.parent()
						.parent()
						.find(".item_sub_total")
						.text(subTotal);
				}
			});
		input.trigger("change");
		total();
	})
	.on("blur change keyup", ".price, .discount", function () {
		createInvoiceForm
			.find('input[name^="invoice_items"]')
			.each(function () {
				let name = $(this).attr("name");
				if (name.includes("[price]")) {
					let price = $(this).val();
					let quantity = $(this)
						.parent()
						.parent()
						.find(".quantity-input")
						.val();
					let discount = $(this)
						.parent()
						.parent()
						.find(".discount")
						.val();
					let subTotal = price * quantity - discount;
					$(this)
						.parent()
						.parent()
						.find(".item_sub_total")
						.text(subTotal);
				}
			});
		total();
	})
	.on("click", "[data-repeater-delete]", function () {
		let row = $(this).closest("tr");
		row.remove();
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

try {
	$(".repeater").repeater({
		initEmpty: false,
		show: function () {
			$(this).slideDown();

			// Re-init select2 for the new row
			var $select = $(this).find('.product_id');

			// Clean up cloned select2 artifacts
			$select.next('.select2-container').remove();
			$select.removeClass('select2-hidden-accessible');
			$select.removeAttr('data-select2-id');
			$select.find('option').removeAttr('data-select2-id');

			$select.select2({
				placeholder: "Select a product",
				allowClear: true,
				width: "100%",
			});
		},
		hide: function (deleteElement) {
			$(this).slideUp(deleteElement);
		},
		ready: function (setIndexes) { },
	});

	const elem = document.querySelector('input[name="invoice_date"]');
	const today = new Date(elem.value);
	new Datepicker(elem, {
		defaultDate: today,
		maxDate: new Date(),
		autoHide: true,
		format: "yyyy-mm-dd",
	});
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
		var createInvoiceFormValidator = createInvoiceForm.validate({
			focusInvalid: true,
			rules: {
				name: {
					required: true,
				},
				mobile: {
					required: true,
				},
				address: {
					required: true,
				},
				"invoice_items[0][product_id]": {
					required: true,
				},
				/* "invoice_items[0][name]": {
					required: true,
				},
				"invoice_items[0][color]": {
					required: true,
				},
				"invoice_items[0][imei_or_serial_number]": {
					required: true,
				},
				"invoice_items[0][storage]": {
					required: true,
				}, */
				"invoice_items[0][quantity]": {
					required: true,
					min: true,
				},
				"invoice_items[0][price]": {
					required: true,
				},
			},
			messages: {
				name: {
					required: "The name field is required",
				},
				mobile: {
					required: "The mobile field is required",
				},
				address: {
					required: "The address field is required",
				},
				"invoice_items[0][product_id]": {
					required: "The product filed is required",
				},
				/* "invoice_items[0][name]": {
					required: 'The name filed is required',
				},
				"invoice_items[0][color]": {
					required: 'The color filed is required',
				},
				"invoice_items[0][imei_or_serial_number]": {
					required: 'The IMEI or serial number filed is required',
				},
				"invoice_items[0][storage]": {
					required: 'The storage filed is required',
				}, */
				"invoice_items[0][quantity]": {
					required: "The quantity field is required",
					min: "The quantity field must be at least 1",
				},
				"invoice_items[0][price]": {
					required: "The price field is required",
				},
			},
			errorPlacement: function (error, element) {
				if (element.closest(".input-group").length) {
					element.closest(".input-group").after(error);
				} else if (element.hasClass("select2-hidden-accessible")) {
					error.insertAfter(
						$(element)
							.next(".select2-container")
							.find(".dropdown-wrapper")
					);
				} else {
					error.insertAfter(element);
				}
			},
			submitHandler: function (form) {
				form.submit();
			},
		});
	}

	$("[data-repeater-create]").on("click", function () {
		setTimeout(function () {
			createInvoiceForm
				.find('input[name^="invoice_items"]')
				.each(function () {
					let name = $(this).attr("name");
					if (name.includes("[product_id]")) {
						$(this).rules("add", {
							required: true,
							messages: {
								required: "The product field is required",
							},
						});
					}

					/* if (name.includes("[name]")) {
						$(this).rules("add", {
							required: true,
							messages: {
								required: "The name field is required",
							},
						});
					}

					if (name.includes("[color]")) {
						$(this).rules("add", {
							required: true,
							messages: {
								required: "The color field is required",
							},
						});
					}
					if (name.includes("[imei_or_serial_number]")) {
						$(this).rules("add", {
							required: true,
							messages: {
								required: "The IMEI or serial number field is required",
							},
						});
					}
					if (name.includes("[storage]")) {
						$(this).rules("add", {
							required: true,
							messages: {
								required: "The storage field is required",
							},
						});
					} */

					if (name.includes("[quantity]")) {
						$(this).rules("add", {
							required: true,
							min: 1,
							messages: {
								required: "The quantity field is required",
								min: "The quantity field must be at least 1",
							},
						});
					}

					if (name.includes("[price]")) {
						$(this).rules("add", {
							required: true,
							messages: {
								required: "The price field is required",
							},
						});
					}
				});
		}, 100);
		updateDisabledProductOptions();
	});

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

		var discounts = $("input[name^='invoice_items'][name$='[discount]']")
			.map(function () {
				return $(this).val() == "" ? 0 : $(this).val();
			})
			.get();
		var totalDiscount = 0;
		for (var i = 0; i < discounts.length; i++) {
			totalDiscount += parseFloat(discounts[i]);
		}
		$("#totalDiscount").text(totalDiscount.toFixed(2));
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
				if (
					selectedProductIds.includes(optionVal) &&
					optionVal !== currentVal
				) {
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
} catch (e) {
	console.error(e);
}
