<!DOCTYPE html>
<html lang="en" dir="ltr" data-startbar="light" data-bs-theme="light">

<head>
	<meta charset="utf-8" />
	<title>{{ config('app.name') }} - {{ $title ?? '' }}</title>
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
	<link href="{{ asset('vendor-assets/libs/cropperjs/cropper.min.css') }}" rel="stylesheet" type="text/css">
	<style>
		#drop-area-document-crop-area {
			border: 1px dashed #ccc;
			padding: 10px;
			margin-bottom: 15px;
			text-align: center;
		}
	</style>
	@yield('pageCss')
</head>

<body>
	<x-header />

	<x-sidebar />

	<div class="page-wrapper">
		<div class="page-content">
			@yield('content')
			<div class="offcanvas offcanvas-end" tabindex="-1" id="Appearance" aria-labelledby="AppearanceLabel">
				<div class="offcanvas-header border-bottom justify-content-between">
					<h5 class="m-0 font-14" id="AppearanceLabel">Appearance</h5>
					<button type="button" class="btn-close text-reset p-0 m-0 align-self-center"
						data-bs-dismiss="offcanvas" aria-label="Close"></button>
				</div>
				<div class="offcanvas-body">
					<h6>Account Settings</h6>
					<div class="p-2 text-start mt-3">
						<div class="form-check form-switch mb-2">
							<input class="form-check-input" type="checkbox" id="settings-switch1">
							<label class="form-check-label" for="settings-switch1">Auto updates</label>
						</div><!--end form-switch-->
						<div class="form-check form-switch mb-2">
							<input class="form-check-input" type="checkbox" id="settings-switch2" checked>
							<label class="form-check-label" for="settings-switch2">Location Permission</label>
						</div><!--end form-switch-->
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="settings-switch3">
							<label class="form-check-label" for="settings-switch3">Show offline Contacts</label>
						</div><!--end form-switch-->
					</div><!--end /div-->
					<h6>General Settings</h6>
					<div class="p-2 text-start mt-3">
						<div class="form-check form-switch mb-2">
							<input class="form-check-input" type="checkbox" id="settings-switch4">
							<label class="form-check-label" for="settings-switch4">Show me Online</label>
						</div><!--end form-switch-->
						<div class="form-check form-switch mb-2">
							<input class="form-check-input" type="checkbox" id="settings-switch5" checked>
							<label class="form-check-label" for="settings-switch5">Status visible to all</label>
						</div><!--end form-switch-->
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="settings-switch6">
							<label class="form-check-label" for="settings-switch6">Notifications Popup</label>
						</div><!--end form-switch-->
					</div><!--end /div-->
				</div><!--end offcanvas-body-->
			</div>
			<x-footer />
		</div>
	</div>
	<div class="modal fade" id="cropperModal" tabindex="-1" aria-labelledby="cropperModalLabel" aria-hidden="true"
		style="display: none;">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h6 class="modal-title m-0" id="cropperModalLabel">Crop Image</h6>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div><!--end modal-header-->
				<div class="modal-body">
					<div class="row mb-2">
						<img id="cropperImage" style="max-width: 100%;" />
					</div>
					<!-- Aspect Ratio Buttons -->
					<div class="btn-group d-flex flex-nowrap mb-2" data-toggle="buttons">
						<label class="btn btn-light mb-1">
							<input type="radio" class="sr-only" id="aspectRatio0" name="aspectRatio"
								value="1.7777777777777777" checked>
							<span class="docs-tooltip" data-bs-toggle="tooltip" data-animation="false"
								title="aspectRatio: 16 / 9">16:9</span>
						</label>
						<label class="btn btn-light mb-1">
							<input type="radio" class="sr-only" id="aspectRatio1" name="aspectRatio"
								value="1.3333333333333333">
							<span class="docs-tooltip" data-bs-toggle="tooltip" data-animation="false"
								title="aspectRatio: 4 / 3">4:3</span>
						</label>
						<label class="btn btn-light mb-1">
							<input type="radio" class="sr-only" id="aspectRatio2" name="aspectRatio"
								value="1">
							<span class="docs-tooltip" data-bs-toggle="tooltip" data-animation="false"
								title="aspectRatio: 1 / 1">1:1</span>
						</label>
						<label class="btn btn-light mb-1">
							<input type="radio" class="sr-only" id="aspectRatio3" name="aspectRatio"
								value="0.6666666666666666">
							<span class="docs-tooltip" data-bs-toggle="tooltip" data-animation="false"
								title="aspectRatio: 2 / 3">2:3</span>
						</label>
						<label class="btn btn-light mb-1">
							<input type="radio" class="sr-only" id="aspectRatio4" name="aspectRatio"
								value="NaN">
							<span class="docs-tooltip" data-bs-toggle="tooltip" data-animation="false"
								title="aspectRatio: Free">Free</span>
						</label>
					</div>

				</div><!--end modal-body-->

				<div class="modal-footer">
					<button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
					<button type="button" id="cropImageBtn" class="btn btn-primary">Crop & Save</button>
				</div><!--end modal-footer-->
			</div><!--end modal-content-->
		</div><!--end modal-dialog-->
	</div>

	<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/moment.min.js') }}"></script>
	<script src="{{ asset('assets/js/jquery-validate/jquery.validate.min.js') }}"></script>
	<script src="{{ asset('assets/js/jquery-validate/additional-methods.min.js') }}"></script>
	<script src="{{ asset('assets/js/toastr/toastr.min.js') }}"></script>
	<script src="{{ asset('vendor-assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
	<script src="{{ asset('vendor-assets/libs/simplebar/simplebar.min.js') }}"></script>
	<script src="{{ asset('vendor-assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
	<script src="{{ asset('vendor-assets/js/app.js') }}"></script>
	<script src="{{ asset('vendor-assets/libs/cropperjs/cropper.min.js') }}"></script>

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
		document.addEventListener("DOMContentLoaded", function() {
			document.querySelectorAll(".toggle-password").forEach(button => {
				button.addEventListener("click", function() {
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
			}).then(function(result) {
				if (result.value) {
					destroyFunctionAjax = $.ajax({
						method: "POST",
						url: url,
						data: {
							id: id,
							_method: "delete",
							_token: csrfToken,
						},
						beforeSend: function() {
							if (destroyFunctionAjax != null) {
								destroyFunctionAjax.abort();
							}
						},
						success: function(resultData) {
							tableVar.ajax.reload();
							toastr.success(resultData.message);
						},
						error: function(jqXHR, ajaxOptions, thrownError) {
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
								}).then(function(result) {
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

			const cropperImage = document.getElementById('cropperImage');
			const cropImageBtn = document.getElementById('cropImageBtn');
			const cropperModal = new bootstrap.Modal(document.getElementById('cropperModal'), {
				backdrop: 'static',
				keyboard: false
			});

			let cropper = null;
			let filesArray = [];
			let currentFile = null;
			let selectedAspectRatio = NaN;
			function handleFiles(files) {
				for (let file of files) {
					if (!file.type.startsWith('image/')) continue;
					if (filesArray.some(f => f.name === file.name && f.size === file.size)) continue;

					const reader = new FileReader();
					reader.onload = (e) => {
						currentFile = file;
						cropperImage.src = e.target.result;
						document.getElementById('cropperModal').addEventListener('shown.bs.modal', () => {
							cropper?.destroy();
							cropper = new Cropper(cropperImage, {
								viewMode: 3,
								aspectRatio: selectedAspectRatio
							});
						}, {
							once: true
						});
						cropperModal.show();
					};
					reader.readAsDataURL(file);
					break;
				}
			}
			cropImageBtn.addEventListener('click', () => {
				if (cropper) {
					cropper.getCroppedCanvas().toBlob(blob => {
						const croppedFile = new File([blob], currentFile.name, {
							type: 'image/jpeg'
						});

						filesArray.push(croppedFile);
						appendToGallery(URL.createObjectURL(croppedFile), croppedFile);
						syncFormInput();

						cropper.destroy();
						cropper = null;
						cropperModal.hide();
					}, 'image/jpeg');
				}
			});
			document.querySelectorAll('input[name="aspectRatio"]').forEach((radio) => {
				radio.addEventListener('change', function() {
					const value = parseFloat(this.value);
					selectedAspectRatio = isNaN(value) ? NaN : value;

					if (cropper) {
						cropper.setAspectRatio(selectedAspectRatio);
					}
				});
			});
			function appendToGallery(src, file) {
				const wrapper = document.createElement('div');
				wrapper.className = 'img-wrapper';

				const img = document.createElement('img');
				img.src = src;

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
