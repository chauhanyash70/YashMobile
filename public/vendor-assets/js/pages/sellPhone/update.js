
$("#category_id").select2({
	placeholder: "Select a category",
	allowClear: true,
	width: "100%",
});
var updateProductForm = $("#updateProductForm");
if (updateProductForm.length) {
	var updateProductFormValidator = updateProductForm.validate({
		focusInvalid: true,
		rules: {
			"category_id": {
				required: true,
			},
			"name": {
				required: true,
			},
			"color": {
				required: true,
			},
			"imei_or_serial_number": {
				required: true,
			},
			"price": {
				required: true,
			},
		},
		messages: {
			"category_id": {
				required: "The category field is required",
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
			"price": {
				required: 'The price filed is required',
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