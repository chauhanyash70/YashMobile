document.addEventListener("DOMContentLoaded", function() {
    const profileImageInput = document.getElementById("profileImageInput");
    const profilePreview = document.getElementById("profilePreview");
    const uploadTrigger = document.getElementById("uploadTrigger");

    uploadTrigger.addEventListener("click", function() {
        profileImageInput.click();
    });

    profileImageInput.addEventListener("change", function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                profilePreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    var profileFormValidate = $('#profile-form').validate({
        focusInvalid: true,
        rules: {
            name: {
                required: true
            },
            email: {
                required: true,
                email: true
            },
            mobile: {
                required: true
            }
        },
        messages: {
            name: {
                required: "Name field is required"
            },
            email: {
                required: "Email field is required",
                email: "Please enter a valid email address."
            },
            mobile: {
                required: "Mobile field is required"
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

    var changePasswordFormValidate = $('#change-password-form').validate({
        focusInvalid: true,
        rules: {
            current_password: {
					required: true,
					remote: {
						url: currentPasswordCheckUrl,
						type: "post",
						data: {
							_token: csrfToken,
							current_password: function() {
								return $( "#current_password" ).val();
							}
						},
					}
				},
				new_password: {
					required: true,
					minlength: 8,
					maxlength: 20
				},
				new_password_confirmation: {
					required: true,
					minlength: 8,
					maxlength: 20,
					equalTo: '#new_password'
				},
        },
        messages: {
            current_password: {
                required: "Current password field is required"
            },
            new_password: {
                required: "New password field is required",
                minlength: "New password must be at least 8 characters long",
                maxlength: "New password must be at most 20 characters long"
            },
            new_password_confirmation: {
                required: "Confirm new password field is required",
                minlength: "Confirm new password must be at least 8 characters long",
                maxlength: "Confirm new password must be at most 20 characters long",
                equalTo: "Confirm new password must be same as new password"
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
});
