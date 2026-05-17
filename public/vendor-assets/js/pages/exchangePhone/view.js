var addStockForm = $("#addStockForm");
if (addStockForm.length) {
	var addStockFormValidator = addStockForm.validate({
		focusInvalid: true,
		rules: {
			"quantity": {
				required: true,
			},
		},
		messages: {
			"quantity": {
				required: 'The quantity filed is required',
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

$(document).ready(function () {

}).on('click', '.stockDeleteBtn', function () {
	var formId = $(this).data('form-id');
	Swal.fire({
		title: 'Are you sure?',
		text: "You won't be able to revert this!",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Yes, delete it!'
	}).then((result) => {
		if (result.isConfirmed) {
			$(formId).submit();
		}
	})
});