var createSliderForm = $("#createSliderForm");
if (createSliderForm.length) {
	var createSliderFormValidator = createSliderForm.validate({
		focusInvalid: true,
		rules: {
			"image": {
				required: true,
			},
		},
		messages: {
			"image": {
				required: "The Image field is required",
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