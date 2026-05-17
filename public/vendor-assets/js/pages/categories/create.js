$("#parent_id").select2({
    placeholder: "Select a category",
    allowClear: true,
    width: "100%",
});

var createCategoryForm = $("#createCategoryForm");
if (createCategoryForm.length) {
	var createCategoryFormValidator = createCategoryForm.validate({
		focusInvalid: true,
		rules: {
			"name": {
				required: true,
			},
			"image": {
				required: true,
			},
		},
		messages: {
			"name": {
				required: "The category Name field is required",
			},
			"image": {
				required: "The Image field is required",
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