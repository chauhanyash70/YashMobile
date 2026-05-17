var formValidate = $('#login-form').validate({
	focusInvalid: true,
	rules: {
		email: {
			required: true,
			email: true
		},
		password: {
			required: true
		}
	},
	messages: {
		email: {
			required: "Email field is required",
			email: "Please enter a valid email address."
		},
		password: {
			required: "Password field is required"
		}
	},
	errorPlacement: function(error, element) {
		if (element.closest(".input-group").length) {
			element.closest(".input-group").after(error);
		} else {
			error.insertAfter(element);
		}
	},
	submitHandler: function (form) {
		form.submit();
	}
});
