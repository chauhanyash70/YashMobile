var createProductForm = $("#createProductForm");
if (createProductForm.length) {
	var createProductFormValidator = createProductForm.validate({
		focusInvalid: true,
		rules: {
			"imei_or_serial_number": {
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
			"customer_document[]":{
			    required: true,
			}
			
		},
		messages: {
			"imei_or_serial_number": {
				required: 'The IMEI or serial number filed is required',
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
			"customer_document[]":{
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

$(document).ready(function () {
	
}).on('keypress', '#imei_or_serial_number', function (e) {
	if (e.which === 13) {
		e.preventDefault();
		let barcode = $(this).val().trim();
		if (!barcode) return;
		$.ajax({
			url:  `/admin/used-phones/sell-phones/get-product-by-barcode/${barcode}`,
			type: 'GET',
			success: function (data) {
				if (data.status === true) {
					let productId = data.product.id;
					let alreadyExists = false;
					$('[data-repeater-item]').each(function () {
						let existingId = $(this).find('[name$="[product_id]"]').val();
						if (existingId == productId) {
							alreadyExists = true;
							return false;
						}
					});

					if (alreadyExists) {
						Swal.fire({
							icon: 'info',
							title: 'Already Added',
							text: 'This product is already in the list.',
						});
						$('#barcode').val('');
						return;
					}
					
					setTimeout(function () {
						$('#product_id').val(data.product.id);
						$('#imei_or_serial_number_text').text(data.product.imei_or_serial_number);
						$('#product_name').text(data.product.name);
						$('#product_price').text(data.product.price);
						$('#product_buy_price').text(data.product.buy_price);
						$('#product_sell_price').text(data.product.sell_price);
						$('#product_color').text(data.product.color);
						$('#product_category').text(data.product.category.name);
						$('#product_brand').text(data.product.brand.name);
						$('#product_status').text(data.product.status);
						$('#purchased_date').text(data.product.purchase_date);
						$('#battery_health').text(data.product.battery_health);
						$('#note').text(data.product.note);
						if (data.product.images.length > 0) {
							$('#grid').empty();
							data.product.images.forEach(function (data) {
								$('#grid').append(`
									<div class="col-md-4 col-lg-4 picture-item picture-item--overlay">
										<a href="${storageUrl+data.image}" class="lightbox h-100" id="${storageUrl+data.id}" >
											<img src="${storageUrl+data.image}" alt="" class="img-fluid h-100" />
										</a>
									</div>`);
							});
							new Tobii()
						}
					}, 100);

				} else {
					$('#product_id').val('');
					$('#imei_or_serial_number_text').text('');
					$('#product_name').text('');
					$('#product_price').text('');
					$('#product_buy_price').text('');
					$('#product_sell_price').text('');
					$('#product_color').text('');
					$('#product_category').text('');
					$('#product_brand').text('');
					$('#product_status').text('');
					$('#purchased_date').text('');
					$('#battery_health').text('');
					$('#note').text('');
					$('#grid').empty();
					Swal.fire({
						icon: 'error',
						title: 'Not Found',
						text: 'Product not found with this barcode.',
					});
				}
			},
			error: function () {
				Swal.fire({
					icon: 'error',
					title: 'Error',
					text: 'Could not fetch product data.',
				});
			}
		});
	}
}).on("focusout","#customer_phone",function(){
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
			initImageUploader('drop-area-document', 'fileElem-document', 'gallery-document', 'formImages-document', response.documents);
		},
		error: function (xhr, status, error) {
			console.error("AJAX Error:", error);
			console.error("Response:", xhr.responseText);
		},
	});
});