$("#category_id").select2({
	placeholder: "Select a category",
	allowClear: true,
	width: "100%",
});
$("#brand_id").select2({
	placeholder: "Select a brand",
	allowClear: true,
	width: "100%",
});

elem = document.querySelector('#purchase_date');
const today = new Date();
elem.value = today.toISOString().split('T')[0];
new Datepicker(elem, {
	defaultDate: today,
	maxDate: today,
	autoHide: true,
	format: 'yyyy-mm-dd',
});

let imageArray = [];
const handleChange = function () {
    var files = document.querySelector("#input-file").files;
    if (files.length > 0) {
        Array.from(files).forEach((file) => {
            readFile(file);
        });
    }
};

const readFile = function (file) {
    if (file) {
        const reader = new FileReader();
        reader.onload = function (event) {
            let imgSrc = event.target.result;
            if (!imageArray.includes(imgSrc)) {
                imageArray.push(imgSrc);
                updatePreview();
            }
        };
        reader.readAsDataURL(file);
    }
};

const updatePreview = function () {
    let previewBox = document.querySelector(".preview-box");
    previewBox.innerHTML = "";
    imageArray.forEach((imgSrc, index) => {
        let div = document.createElement("div");
        div.className = "image-container position-relative m-2";
        div.style.display = "inline-block";

        let img = document.createElement("img");
        img.className = "preview-content";
        img.src = imgSrc;
        img.style.width = "100px";
        img.style.height = "100px";
        img.style.objectFit = "cover";
        img.style.borderRadius = "8px";
        img.style.border = "1px solid #ddd";

        let removeBtn = document.createElement("span");
        removeBtn.innerHTML = "&#10006;";
        removeBtn.className = "remove-icon";
        removeBtn.style.position = "absolute";
        removeBtn.style.top = "5px";
        removeBtn.style.right = "5px";
        removeBtn.style.background = "red";
        removeBtn.style.color = "white";
        removeBtn.style.borderRadius = "50%";
        removeBtn.style.width = "20px";
        removeBtn.style.height = "20px";
        removeBtn.style.display = "flex";
        removeBtn.style.alignItems = "center";
        removeBtn.style.justifyContent = "center";
        removeBtn.style.cursor = "pointer";
        removeBtn.style.fontSize = "14px";
        removeBtn.onclick = function () {
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
					imageArray.splice(index, 1);
            		updatePreview();
				}
			})
        };
        div.appendChild(img);
        div.appendChild(removeBtn);
        previewBox.appendChild(div);
    });
};

$(document).on("focusout","#customer_phone",function(){
    $.ajax({
		type: "POST",
		url: customerSearchUrl,
		data: { phone: $(this).val() },
		dataType: "json",
		headers: { "X-CSRF-TOKEN": csrfToken },
		success: function (response) {
			if (response && response.name) {
				$("#customer_name").val(response.name);
			}
			if (response && response.email) {
				$("#customer_email").val(response.email);
			}
			if (response && response.phone) {
				$("#customer_phone").val(response.phone);
			}
			if (response && response.address) {
				$("#customer_address").val(response.address);
			}
		},
		error: function (xhr, status, error) {
			console.error("AJAX Error:", error);
			console.error("Response:", xhr.responseText);
		},
	});
}).on('click','.img-remove', (e) => {
	e.stopPropagation();
	e.preventDefault();
	const id = $(e.target).data('id');
	$.ajax({
		url: '{{ route("admin.customer.deleteDocument") }}',
		type: 'POST',
		data: JSON.stringify({ _token: csrfToken, id: id }),
		contentType: 'application/json',
		success: function(response) {
			if (response.status === true) {
				$(".existing-image-" + id).remove();
				toastr.success(response.message);
			} else {
				toastr.error(response.message);
			}
		},
		error: function(xhr, status, error) {
			toastr.success(xhr.responseJSON.message);
		}
	});
});;

var newBuyProductForm = $("#newBuyProductForm");
if (newBuyProductForm.length) {
	var newBuyProductFormValidator = newBuyProductForm.validate({
		focusInvalid: true,
		rules: {
			"category_id": {
				required: true,
			},
			"name": {
				required: true,
			},
			"color": {
				required: true,
			},
			"imei_or_serial_number": {
				required: true,
			},
			"buy_price": {
				required: true,
			},
			"sell_price": {
				required: true,
			},
			"quantity": {
				required: true,
			},
			"customer_name": {
			    required: true,
			},
			"customer_phone": {
			    required: true,
			},
			"customer_email":{
			    required: false,
			},
			"customer_address":{
			    required: true,
			},
			"customer_document":{
			    required: true,
			
			}
		},
		messages: {
			"category_id": {
				required: "The category field is required",
			},
			"name": {
				required: "The name field is required",
			},
			"color": {
				required: "The color field is required",
			},
			"imei_or_serial_number": {
				required: 'The IMEI or serial number filed is required',
			},
			"buy_price": {
				required: 'The purchase price filed is required',
			},
			"sell_price": {
				required: 'The selling price filed is required',
			},
			"quantity": {
				required: 'The quantity filed is required',
			},
			"customer_name": {
			    required: 'The customer name filed is required',
			},
			"customer_phone": {
			    required: 'The customer phone filed is required',
			},
			"customer_email":{
			    required: 'The customer email filed is required',
			},
			"customer_address":{
			    required: 'The customer address filed is required',
			},
			"customer_document":{
			    required: 'The customer document filed is required',
			}
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