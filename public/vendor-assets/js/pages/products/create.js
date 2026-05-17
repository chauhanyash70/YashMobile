$("#parent_id").select2({
	placeholder: "Select a category",
	allowClear: true,
	width: "100%",
});

var createProductForm = $("#createProductForm");
if (createProductForm.length) {
	var createProductFormValidator = createProductForm.validate({
		focusInvalid: true,
		rules: {
			"category_id": {
				required: true,
			},
			"brand_id": {
				required: true,
			},
			"name": {
				required: true,
			},
			"color": {
				required: true,
			},
			"imei_or_serial_number": {
				required: function () {
					return $("#generate_code").is(":checked") === false ? true : false;
				},
			},
			"buy_price": {
				required: true,
			},
			"sell_price": {
				required: true,
			},
			"quantity": {
				required: true,
			},
		},
		messages: {
			"category_id": {
				required: "The category field is required",
			},
			"brand_id": {
				required: "The brand field is required",
			},
			"name": {
				required: "The name field is required",
			},
			"color": {
				required: "The color field is required",
			},
			"imei_or_serial_number": {
				required: 'The IMEI or serial number filed is required',
			},
			"buy_price": {
				required: 'The purchase price filed is required',
			},
			"sell_price": {
				required: 'The selling price filed is required',
			},
			"quantity": {
				required: 'The quantity filed is required',
			},
		},
		errorPlacement: function (error, element) {
			if (element.closest(".input-group").length) {
				element.closest(".input-group").after(error);
			} else if (element.hasClass("select2-hidden-accessible")) {
				error.insertAfter($(element).next(".select2-container"));
			} else {
				error.insertAfter(element);
			}
		},
		submitHandler: function (form) {
			form.submit();
		},
	});
}

$("#generate_code").on("change", function () {
	if ($(this).is(":checked")) {
		$("#imei_or_serial_number").val('').prop("readonly", true);
		$("#imei_or_serial_number_required").html('');
	} else {
		$("#imei_or_serial_number").prop("readonly", false);
		$("#imei_or_serial_number_required").html('<span class="text-danger">&nbsp;<strong>*</strong></span>');
	}
});