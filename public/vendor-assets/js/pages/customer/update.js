var updateCustomerForm = $("#updateCustomerForm");
if (updateCustomerForm.length) {
	var updateCustomerFormValidator = updateCustomerForm.validate({
		rules: {
			"name": {
				required: true,
			},
			"phone": {
				required: true,
			},
			"address": {
				required: true,
			},
		},
		messages: {
			"name": {
			    required: 'The name filed is required',
			},
			"phone": {
			    required: 'The phone filed is required',
			},
			"address":{
			    required: 'The address filed is required',
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