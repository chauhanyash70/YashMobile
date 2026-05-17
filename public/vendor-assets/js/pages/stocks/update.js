$("#product_id").select2({
	placeholder: "Select a product",
	allowClear: true,
	width: "100%",
});

var createStockForm = $("#updateStockForm");
if (updateStockForm.length) {
	var updateStockFormValidator = updateStockForm.validate({
		focusInvalid: true,
		rules: {
			"product_id": {
				required: true,
			},
			"quantity": {
				required: true,
			},
		},
		messages: {
			"product_id": {
				required: "The product field is required",
			},
			"quantity": {
				required: "The quantity field is required",
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
