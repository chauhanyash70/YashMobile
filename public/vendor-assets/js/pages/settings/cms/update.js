var createCmsForm = $("#createCmsForm");
if (createCmsForm.length) {
	var createCmsFormValidator = createCmsForm.validate({
		focusInvalid: true,
		rules: {
			"title": {
				required: true,
			},
			"content": {
				required: true,
			},
		},
		messages: {
			"title": {
				required: "The title field is required",
			},
			"content": {
				required: "The content field is required",
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

$(document).ready(function() {
    $('#content').summernote();
});

