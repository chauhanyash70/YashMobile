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
	<!-- Sweet Alert -->
	<link href="{{ asset('vendor-assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('vendor-assets/libs/animate.css/animate.min.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('vendor-assets/libs/tobii/css/tobii.min.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ asset('vendor-assets/libs/cropperjs/cropper.min.css') }}" rel="stylesheet" type="text/css">
	@yield('pageCss')
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

		/* function initImageUploader(dropAreaId, fileInputId, galleryId, formInputId) {
			const dropArea = document.getElementById(dropAreaId);
			const fileElem = document.getElementById(fileInputId);
			const gallery = document.getElementById(galleryId);
			const formImagesInput = document.getElementById(formInputId);

			let filesArray = [];

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

			fileElem.addEventListener('change', (e) => {
				handleFiles(e.target.files);
			});

			dropArea.addEventListener('click', () => {
				fileElem.click();
			});

			function handleFiles(files) {
				for (let file of files) {
					if (!file.type.startsWith('image/')) continue;
					if (filesArray.some(f => f.name === file.name && f.size === file.size)) continue;

					filesArray.push(file);

					const reader = new FileReader();
					reader.onload = (e) => {
						const wrapper = document.createElement('div');
						wrapper.className = 'img-wrapper';

						const img = document.createElement('img');
						img.src = e.target.result;

						const removeBtn = document.createElement('button');
						removeBtn.innerText = '×';
						removeBtn.className = 'img-remove';
						removeBtn.type = 'button';
						removeBtn.onclick = () => {
							wrapper.remove();
							filesArray = filesArray.filter(f => f !== file);
							syncFormInput();
						};

						wrapper.appendChild(img);
						wrapper.appendChild(removeBtn);
						gallery.appendChild(wrapper);
					};
					reader.readAsDataURL(file);
				}
				syncFormInput();
			}

			function syncFormInput() {
				const dataTransfer = new DataTransfer();
				filesArray.forEach(file => dataTransfer.items.add(file));
				formImagesInput.files = dataTransfer.files;
			}
		} */
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
				wrapper.className = 'img-wrapper';

				const img = document.createElement('img');
				if (file.type.startsWith('image/')) {
					img.src = src;
				} else {
					img.src = "{{ asset('assets/images/no-photo.jpg') }}"; // Or a generic file icon
				}

				const removeBtn = document.createElement('button');
				removeBtn.innerText = '×';
				removeBtn.className = 'img-remove';
				removeBtn.type = 'button';
				removeBtn.addEventListener('click', (e) => {
					e.stopPropagation();
					e.preventDefault();
					wrapper.remove();
					filesArray = filesArray.filter(f => f !== file);
					syncFormInput();
				});

				wrapper.appendChild(img);
				wrapper.appendChild(removeBtn);
				gallery.appendChild(wrapper);
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
				wrapper.className = 'img-wrapper existing-image-' + data.id;

				const img = document.createElement('img');
				img.src = data.image ? storageUrl + data.image :
					data.document ? storageUrl + data.document : '#';

				const removeBtn = document.createElement('button');
				removeBtn.innerText = '×';
				removeBtn.className = 'img-remove';
				removeBtn.type = 'button';
				removeBtn.setAttribute('data-id', data.id);
				wrapper.appendChild(img);
				wrapper.appendChild(removeBtn);
				gallery.appendChild(wrapper);
			});
		}
	</script>
	@yield('pageScripts')
</body>

</html>