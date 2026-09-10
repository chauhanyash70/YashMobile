<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="light" data-bs-theme="light">

<head>
	<script>
		/**
		 * Theme Manager - Immediate Execution to prevent flash
		 */
		(function() {
			function getCookie(name) {
				let value = "; " + document.cookie;
				let parts = value.split("; " + name + "=");
				if (parts.length === 2) return parts.pop().split(";").shift();
			}

			let theme = getCookie("theme") || "auto";

			if (theme === "auto") {
				const hour = new Date().getHours();
				theme = (hour >= 18 || hour < 6) ? "dark" : "light";
			}

			document.documentElement.setAttribute("data-bs-theme", theme);
		})();
	</script>

	<meta charset="utf-8" />
	<title>@yield('title', $title ?? 'Dashboard') | {{ config('app.name') }}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="shortcut icon" href="{{ asset('assets/logo/yash-mobile-favicon.svg') }}">
	<link href="{{ asset('vendor-assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
	<link href="{{ asset('vendor-assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
	<link href="{{ asset('vendor-assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
	<link href="{{ asset('assets/js/toastr/toastr.min.css') }}" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="{{ asset('vendor-assets/libs/jsvectormap/css/jsvectormap.min.css') }}">
	<link href="{{ asset('vendor-assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('vendor-assets/libs/animate.css/animate.min.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('vendor-assets/libs/tobii/css/tobii.min.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('vendor-assets/libs/cropperjs/cropper.min.css') }}" rel="stylesheet" type="text/css">
	@yield('pageCss')
	@stack('pageCss')
	<link href="{{ asset('assets/css/datepicker-dark.css') }}?v={{ time() }}" rel="stylesheet" type="text/css" />
	<link href="{{ asset('assets/css/custom-dark.css') }}?v={{ time() }}" rel="stylesheet" type="text/css" />
</head>

<body>
	<x-header />

	<x-sidebar />

	<div class="page-wrapper">
		<div class="page-content">
			@yield('content')

			<x-footer />
		</div>
	</div>
	<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
	<script src="{{ asset('assets/js/moment.min.js') }}"></script>
	<script src="{{ asset('assets/js/jquery-validate/jquery.validate.min.js') }}"></script>
	<script src="{{ asset('assets/js/jquery-validate/additional-methods.min.js') }}"></script>
	<script src="{{ asset('assets/js/toastr/toastr.min.js') }}"></script>
	<script src="{{ asset('vendor-assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
	<script src="{{ asset('vendor-assets/libs/simplebar/simplebar.min.js') }}"></script>
	<script src="{{ asset('vendor-assets/libs/tobii/js/tobii.min.js') }}"></script>
	<script src="{{ asset('vendor-assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
	<script src="{{ asset('vendor-assets/libs/data-tables/datatables.min.js') }}"></script>
	<script src="{{ asset('vendor-assets/js/app.js') }}"></script>

	<script>
		var csrfToken = "{{ csrf_token() }}";
		var storageUrl = "{{ Storage::url('/') }}";
		toastr.options = {
			"closeButton": true,
			"newestOnTop": false,
			"progressBar": true,
			"positionClass": "toast-bottom-center",
			"preventDuplicates": false,
			"onclick": null,
			"showDuration": "2000",
			"hideDuration": "1000",
			"timeOut": "5000",
			"extendedTimeOut": "1000",
			"showEasing": "swing",
			"hideEasing": "linear",
			"showMethod": "fadeIn",
			"hideMethod": "fadeOut"
		}
		@foreach (['error', 'warning', 'success', 'info'] as $msg)
			@if (Session::has($msg))
				toastr.{{ $msg }}("{{ Session::get($msg) }}");
			@endif
		@endforeach

		// Global Indian Currency (INR) Formatter
		window.formatInr = function (amount, decimals = 2, symbol = false) {
			if (amount === null || amount === undefined || isNaN(amount)) {
				let prefix = (symbol === true) ? '₹' : (typeof symbol === 'string' ? symbol : '');
				return prefix + (0).toFixed(decimals);
			}

			let num = Number(amount);
			let isNegative = num < 0;
			num = Math.abs(num);

			let parts = num.toFixed(decimals).split('.');
			let integerPart = parts[0];
			let decimalPart = parts.length > 1 ? '.' + parts[1] : '';

			if (integerPart.length > 3) {
				let lastThree = integerPart.substring(integerPart.length - 3);
				let remaining = integerPart.substring(0, integerPart.length - 3);
				let remainingFormatted = remaining.replace(/\B(?=(\d{2})+(?!\d))/g, ",");
				integerPart = remainingFormatted + ',' + lastThree;
			}

			let prefix = (symbol === true) ? '₹' : (typeof symbol === 'string' ? symbol : '');
			let sign = isNegative ? '-' : '';

			return sign + prefix + integerPart + decimalPart;
		};

		window.registerDataTableInr = function () {
			if (typeof $ !== 'undefined' && $.fn && $.fn.dataTable && $.fn.dataTable.render) {
				$.fn.dataTable.render.inr = function (decimals = 2, prefix = '₹') {
					return function (data, type, row) {
						if (type === 'display') {
							return window.formatInr(data, decimals, prefix);
						}
						return data;
					};
				};
			}
		};
		window.registerDataTableInr();
		$(document).ready(window.registerDataTableInr);

		const tobii = new Tobii();

		document.addEventListener("DOMContentLoaded", function () {
			document.querySelectorAll(".toggle-password").forEach(button => {
				button.addEventListener("click", function () {
					const passwordField = this.previousElementSibling;
					if (passwordField.type === "password") {
						passwordField.type = "text";
						this.innerHTML = '<i class="fas fa-eye-slash"></i>';
					} else {
						passwordField.type = "password";
						this.innerHTML = '<i class="fas fa-eye"></i>';
					}
				});
			});
		});

		destroyFunctionAjax = null;

		function destroyFunction(e) {
			var id = $(e).attr("data-id");
			var url = $(e).attr("data-url");
			Swal.fire({
				title: "Are you sure?",
				text: "You won't be able to revert this!",
				icon: "warning",
				showCancelButton: true,
				confirmButtonText: "Yes, delete it!",
				customClass: {
					confirmButton: "btn btn-sm btn-success",
					cancelButton: "btn btn-sm btn-danger",
				},
			}).then(function (result) {
				if (result.value) {
					destroyFunctionAjax = $.ajax({
						method: "POST",
						url: url,
						data: {
							id: id,
							_method: "delete",
							_token: csrfToken,
						},
						beforeSend: function () {
							if (destroyFunctionAjax != null) {
								destroyFunctionAjax.abort();
							}
						},
						success: function (resultData) {
							tableVar.ajax.reload();
							toastr.success(resultData.message);
						},
						error: function (jqXHR, ajaxOptions, thrownError) {
							if (jqXHR.status == 401 || jqXHR.status == 419) {
								console.log(jqXHR.status);
								Swal.fire({
									title: "Session Expired",
									text: "You'll be take to the login page",
									icon: "warning",
									confirmButtonText: "Ok",
									allowOutsideClick: false,
									customClass: {
										confirmButton: "btn btn-sm btn-success",
									},
								}).then(function (result) {
									location.reload();
									return false;
								});
							} else {
								toastr.error(jqXHR.responseJSON.message);
							}
						},
					});
				}
			});
		}
		function initImageUploader(dropAreaId, fileInputId, galleryId, formInputId, existingImages = []) {
			const dropArea = document.getElementById(dropAreaId);
			const fileElem = document.getElementById(fileInputId);
			const gallery = document.getElementById(galleryId);
			const formImagesInput = document.getElementById(formInputId);

			let filesArray = [];

			function handleFiles(files) {
				for (let file of files) {
					if (filesArray.some(f => f.name === file.name && f.size === file.size)) continue;

					filesArray.push(file);

					const reader = new FileReader();
					reader.onload = (e) => {
						appendToGallery(e.target.result, file);
					};
					reader.readAsDataURL(file);
				}
				syncFormInput();
			}

			function appendToGallery(src, file) {
				const wrapper = document.createElement('div');
				wrapper.className = 'img-wrapper border rounded overflow-hidden position-relative shadow-sm hover-scale';
				wrapper.style.width = '100px';
				wrapper.style.height = '100px';
				wrapper.style.display = 'inline-block';
				wrapper.style.margin = '5px';

				// Create anchor element for Fancybox
				const link = document.createElement('a');
				link.href = src;
				link.setAttribute('data-fancybox', 'customer-create-docs');
				if (file && file.name) {
					link.setAttribute('data-caption', file.name);
				}

				const img = document.createElement('img');
				img.className = 'w-100 h-100 object-fit-cover';
				if (file.type.startsWith('image/')) {
					img.src = src;
				} else if (file.type === 'application/pdf') {
					link.setAttribute('data-type', 'pdf');
					const pdfContainer = document.createElement('div');
					pdfContainer.className = 'd-flex flex-column align-items-center justify-content-center bg-danger-subtle text-danger w-100 h-100';
					pdfContainer.innerHTML = '<i class="far fa-file-pdf fa-2x mb-1"></i><span class="fs-10 text-uppercase fw-semibold">PDF</span>';
					link.appendChild(pdfContainer);
				} else {
					const fileContainer = document.createElement('div');
					fileContainer.className = 'd-flex flex-column align-items-center justify-content-center bg-secondary-subtle text-secondary w-100 h-100';
					fileContainer.innerHTML = '<i class="fas fa-file fa-2x mb-1"></i><span class="fs-10 text-uppercase fw-semibold">FILE</span>';
					link.appendChild(fileContainer);
				}

				if (!link.hasChildNodes()) {
					link.appendChild(img);
				}

				const removeBtn = document.createElement('button');
				removeBtn.type = 'button';
				removeBtn.className = 'btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 m-1 d-flex align-items-center justify-content-center';
				removeBtn.style.zIndex = '10';
				removeBtn.style.width = '22px';
				removeBtn.style.height = '22px';
				removeBtn.style.padding = '0';
				removeBtn.style.border = 'none';
				removeBtn.style.backgroundColor = 'rgba(220, 53, 69, 0.9)';
				removeBtn.innerHTML = '<i class="fas fa-times" style="font-size: 10px; color: white;"></i>';

				removeBtn.addEventListener('click', (e) => {
					e.stopPropagation();
					e.preventDefault();
					wrapper.remove();
					filesArray = filesArray.filter(f => f !== file);
					syncFormInput();
				});

				wrapper.appendChild(removeBtn);
				wrapper.appendChild(link);
				gallery.appendChild(wrapper);

				// Rebind Fancybox
				if (typeof Fancybox !== 'undefined') {
					Fancybox.unbind('[data-fancybox="customer-create-docs"]');
					Fancybox.bind('[data-fancybox="customer-create-docs"]', {
						Compact: false,
						Idle: false,
						Animated: true,
						dragToClose: true,
						Toolbar: {
							display: {
								left: ["infobar"],
								middle: [],
								right: ["slideshow", "download", "thumbs", "close"],
							},
						},
						Html: {
							pdf: {
								type: "pdf"
							}
						}
					});
				}
			}

			function syncFormInput() {
				const dataTransfer = new DataTransfer();
				filesArray.forEach(file => dataTransfer.items.add(file));
				formImagesInput.files = dataTransfer.files;
			}
			dropArea.addEventListener('dragover', (e) => {
				e.preventDefault();
				dropArea.classList.add('hover');
			});
			dropArea.addEventListener('dragleave', () => {
				dropArea.classList.remove('hover');
			});
			dropArea.addEventListener('drop', (e) => {
				e.preventDefault();
				dropArea.classList.remove('hover');
				handleFiles(e.dataTransfer.files);
			});
			dropArea.addEventListener('click', () => {
				fileElem.click();
			});
			fileElem.addEventListener('change', (e) => {
				handleFiles(e.target.files);
			});
			existingImages.forEach((data) => {
				const wrapper = document.createElement('div');
				wrapper.className = 'img-wrapper border rounded overflow-hidden position-relative shadow-sm hover-scale existing-image-' + data.id;
				wrapper.style.width = '100px';
				wrapper.style.height = '100px';
				wrapper.style.display = 'inline-block';
				wrapper.style.margin = '5px';

				const imageUrl = data.image ? storageUrl + data.image :
					data.document ? storageUrl + data.document : '#';

				const link = document.createElement('a');
				link.href = imageUrl;
				link.setAttribute('data-fancybox', 'customer-create-docs');

				const img = document.createElement('img');
				img.className = 'w-100 h-100 object-fit-cover';
				img.src = imageUrl;
				link.appendChild(img);

				const removeBtn = document.createElement('button');
				removeBtn.type = 'button';
				removeBtn.className = 'btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 m-1 d-flex align-items-center justify-content-center';
				removeBtn.style.zIndex = '10';
				removeBtn.style.width = '22px';
				removeBtn.style.height = '22px';
				removeBtn.style.padding = '0';
				removeBtn.style.border = 'none';
				removeBtn.style.backgroundColor = 'rgba(220, 53, 69, 0.9)';
				removeBtn.innerHTML = '<i class="fas fa-times" style="font-size: 10px; color: white;"></i>';
				removeBtn.setAttribute('data-id', data.id);

				wrapper.appendChild(removeBtn);
				wrapper.appendChild(link);
				gallery.appendChild(wrapper);
			});
		}
	</script>
	@yield('pageScripts')
	@stack('pageScripts')
</body>

</html>