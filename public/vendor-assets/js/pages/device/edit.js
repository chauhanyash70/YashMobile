$(document).ready(function () {
	// 1. Initialize Datepicker
	function initDatepicker(elem) {
		new Datepicker(elem, {
			autoHide: true,
			format: "yyyy-mm-dd",
		});
	}

	// Initialize existing datepickers
	$(".date-picker").each(function () {
		initDatepicker(this);
	});

	// 2. Initialize Form Validation
	$("#deviceEditForm").validate({
		rules: {
			brand_id: "required",
			model_name: "required",
			storage: "required",
			ram: "required",
			color: "required",
		},
		messages: {
			brand_id: "Please select a brand",
			model_name: "Please enter model name",
			storage: "Please enter storage",
			ram: "Please enter RAM",
			color: "Please enter color",
		},
		errorClass: "text-danger small",
		errorElement: "span",
		highlight: function (element) {
			$(element).addClass("is-invalid");
		},
		unhighlight: function (element) {
			$(element).removeClass("is-invalid");
		},
	});

	// 3. Dynamic rules for units
	$.validator.addClassRules({
		"imei-field": {
			required: true,
			minlength: 5,
		},
		"buy-price-field": {
			required: true,
			number: true,
			min: 0,
		},
		"supplier-phone": {
			required: true,
			minlength: 10,
		},
		"supplier-name": {
			required: true,
		},
		"supplier-address": {
			required: true,
		},
	});

	// 4. Supplier Lookup
	$(document).on("blur", ".supplier-phone", function () {
		var phone = $(this).val();
		if (phone.length >= 10 && typeof supplierSearchUrl !== "undefined") {
			$.post(
				supplierSearchUrl,
				{
					phone: phone,
					_token: typeof csrfToken !== "undefined" ? csrfToken : "",
				},
				function (data) {
					if (data && data.status) {
						$(".supplier-name").val(data.customer.name);
						$(".supplier-address").val(data.customer.address);
					}
				},
			);
		}
	});
});
