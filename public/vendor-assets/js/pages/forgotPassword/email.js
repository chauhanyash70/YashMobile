var formValidate = $('#send-email-form').validate({
	focusInvalid: true,
	rules: {
		email: {
			required: true,
			email: true
		},
	},
	messages: {
		email: {
			required: "Email field is required",
			email: "Please enter a valid email address."
		},
	},
	submitHandler: function (form) {
		form.submit();
	}
});
