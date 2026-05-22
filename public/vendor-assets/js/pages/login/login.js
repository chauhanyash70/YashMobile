$(function () {
	// Active validation instance
	var formValidate = $('#login-form').validate({
		focusInvalid: true,
		rules: {
			email: {
				required: true,
				email: true
			},
			password: {
				required: function() {
					return $('.credentials-section').is(':visible');
				}
			},
			otp: {
				required: function() {
					return $('.otp-section').is(':visible');
				},
				digits: true,
				minlength: 4,
				maxlength: 4
			}
		},
		messages: {
			email: {
				required: "Email address is required.",
				email: "Please enter a valid email address."
			},
			password: {
				required: "Password is required to proceed."
			},
			otp: {
				required: "Please enter the 4-digit verification code.",
				digits: "Verification code must contain numbers only.",
				minlength: "Verification code must be exactly 4 digits.",
				maxlength: "Verification code must be exactly 4 digits."
			}
		},
		errorPlacement: function(error, element) {
			if (element.closest(".input-group").length) {
				element.closest(".input-group").after(error);
			} else if (element.hasClass("otp-digit")) {
				// Put OTP errors below the digit boxes container
				if (!$('.otp-error-container').length) {
					$('.otp-digit').parent().after('<div class="otp-error-container text-center mt-2"></div>');
				}
				$('.otp-error-container').html(error);
			} else {
				error.insertAfter(element);
			}
		},
		submitHandler: function (form) {
			// Step 1: Intercept credentials submit and run AJAX precheck
			var $btn = $('#btn-login');
			var $spinner = $('#login-spinner');
			
			$btn.prop('disabled', true);
			$spinner.show();
			$('#email').prop('disabled', true);

			$.ajax({
				url: '/login/pre-check',
				method: 'POST',
				headers: {
					'X-CSRF-TOKEN': csrfToken
				},
				data: {
					email: $('#email').val(),
					password: $('#password').val(),
					remember: $('#remember').is(':checked') ? 1 : 0
				},
				success: function (response) {
					$spinner.hide();
					toastr.success(response.message);

					// Slide transition: Hide credentials, reveal OTP digit inputs
					$('.credentials-section').slideUp(300, function() {
						$('.otp-section').slideDown(300, function() {
							$('#otp_1').focus();
						});
					});

					// Start 60s cooldown for resending
					startCooldown(60);
				},
				error: function (xhr) {
					$spinner.hide();
					$btn.prop('disabled', false);
					$('#email').prop('disabled', false);

					var errorMsg = 'Invalid email or password.';
					if (xhr.responseJSON && xhr.responseJSON.message) {
						errorMsg = xhr.responseJSON.message;
					}
					toastr.error(errorMsg);
				}
			});
		}
	});

	// --- 4-Digit Input Boxes Interactivity ---

	// Function to combine values and set hidden input
	function combineOtpDigits() {
		var combined = '';
		$('.otp-digit').each(function () {
			combined += $(this).val();
		});
		$('#otp').val(combined);
		
		// If code is complete, run validate
		if (combined.length === 4) {
			$('#otp').valid();
		}
	}

	$('.otp-digit').on('input', function (e) {
		var val = $(this).val();
		
		// Filter out non-numeric characters
		if (val && !/^[0-9]$/.test(val)) {
			$(this).val('');
			return;
		}

		combineOtpDigits();

		// Auto focus next field
		if (val && $(this).next('.otp-digit').length) {
			$(this).next('.otp-digit').focus();
		}
	});

	$('.otp-digit').on('keydown', function (e) {
		// Backspace key
		if (e.key === 'Backspace' || e.keyCode === 8) {
			var val = $(this).val();
			if (!val && $(this).prev('.otp-digit').length) {
				var $prev = $(this).prev('.otp-digit');
				$prev.val('').focus();
				combineOtpDigits();
				e.preventDefault();
			} else if (val) {
				$(this).val('');
				combineOtpDigits();
				e.preventDefault();
			}
		}
	});

	// Intercept clipboard paste across all digit boxes
	$('.otp-digit').on('paste', function (e) {
		var pasteData = (e.originalEvent || e).clipboardData.getData('text');
		pasteData = pasteData.replace(/[^0-9]/g, ''); // Extract digits only

		if (pasteData.length === 4) {
			$('.otp-digit').each(function (index) {
				$(this).val(pasteData[index]);
			});
			combineOtpDigits();
			$('#otp_4').focus();
			e.preventDefault();
		}
	});

	// --- Cooldown Timer & Operations ---

	var cooldownInterval = null;

	function startCooldown(seconds) {
		var $timer = $('#cooldown-timer');
		var $timerContainer = $('.timer-container');
		var $resendBtn = $('#btn-resend-otp');

		$resendBtn.hide();
		$timerContainer.show();
		$timer.text(seconds + 's');
		
		clearInterval(cooldownInterval);
		cooldownInterval = setInterval(function() {
			seconds--;
			$timer.text(seconds + 's');

			if (seconds <= 0) {
				clearInterval(cooldownInterval);
				$timerContainer.hide();
				$resendBtn.fadeIn(150);
			}
		}, 1000);
	}

	// AJAX: Resend 2FA OTP Code
	$('#btn-resend-otp').on('click', function () {
		var $btn = $(this);
		var $spinner = $('#resend-spinner');
		var $icon = $('#resend-icon');

		$btn.addClass('loading').prop('disabled', true);
		$spinner.show();
		$icon.hide();

		$.ajax({
			url: '/login/resend-otp',
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': csrfToken
			},
			success: function (response) {
				$spinner.hide();
				$icon.show();
				$btn.removeClass('loading').prop('disabled', false);
				toastr.success(response.message);

				// Clear input digit boxes
				$('.otp-digit').val('');
				$('#otp').val('');

				// Start new 60s cooldown
				startCooldown(60);
				$('#otp_1').focus();
			},
			error: function (xhr) {
				$spinner.hide();
				$icon.show();
				$btn.removeClass('loading').prop('disabled', false);

				var errorMsg = 'Failed to resend code. Please try again.';
				if (xhr.responseJSON && xhr.responseJSON.message) {
					errorMsg = xhr.responseJSON.message;
				}
				toastr.error(errorMsg);
			}
		});
	});

	// AJAX: Verify 2FA OTP & Authenticate
	$('#btn-verify-otp').on('click', function () {
		// Run validations
		var isEmailValid = $('#email').valid();
		var isOtpValid = $('#otp').valid();

		if (!isEmailValid || !isOtpValid) {
			return;
		}

		var $btn = $(this);
		var $spinner = $('#verify-spinner');
		var otp = $('#otp').val();

		$btn.prop('disabled', true);
		$spinner.show();

		$.ajax({
			url: '/login/verify-otp',
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': csrfToken
			},
			data: {
				otp: otp
			},
			success: function (response) {
				$spinner.hide();
				toastr.success(response.message);

				// Redirect to home dashboard
				setTimeout(function() {
					window.location.href = response.redirect;
				}, 1000);
			},
			error: function (xhr) {
				$spinner.hide();
				$btn.prop('disabled', false);

				var errorMsg = 'The verification code entered is incorrect.';
				if (xhr.responseJSON && xhr.responseJSON.message) {
					errorMsg = xhr.responseJSON.message;
				}
				toastr.error(errorMsg);
			}
		});
	});
});



