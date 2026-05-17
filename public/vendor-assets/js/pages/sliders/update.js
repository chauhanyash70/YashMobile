var updateSliderForm = $("#updateSliderForm");
if (updateSliderForm.length) {
	var updateSliderFormValidator = updateSliderForm.validate({
		focusInvalid: true,
		rules: {
			"title": {
				required: false,
			},
			"short_description": {
				required: false,
			},
		},
		messages: {
			"title": {
				required: "The title field is required",
			},
			"short_description": {
				required: "The short description field is required",
			},
		},
		errorPlacement: function (error, element) {
			if (element.closest(".input-group").length) {
				element.closest(".input-group").after(error);
			} else {
				error.insertAfter(element);
			}
		},
		submitHandler: function (form) {
			form.submit();
		},
	});
}

const imageInput = document.getElementById("imageInput");
const imagePreview = document.getElementById("imagePreview");
const uploadTrigger = document.getElementById("uploadTrigger");

uploadTrigger.addEventListener("click", function () {
	imageInput.click();
});

imageInput.addEventListener("change", function (event) {
	const file = event.target.files[0];
	if (file) {
		const reader = new FileReader();
		reader.onload = function (e) {
			imagePreview.src = e.target.result;
		};
		reader.readAsDataURL(file);
	}
});