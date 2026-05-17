<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Auth::routes();

Route::group(['middleware' => ['auth']], function () {

	Route::get('/home', [HomeController::class, 'index'])->name('home');
	Route::post('/dashboard/ajax', [HomeController::class, 'ajaxDashboardData'])
		->name('dashboard.ajax');
    Route::get('/dashboard/export', [HomeController::class, 'export'])->name('dashboard.export');

	Route::get('profile', [UserController::class, 'profile'])->name('profile');
	Route::post('profile/update', [UserController::class, 'updateProfile'])->name('profile.update');
	Route::post('profile/change-password', [UserController::class, 'changePassword'])->name('profile.change-password');
	Route::post('profile/check-password', [UserController::class, 'checkCurrentPassword'])->name('profile.check-password');

	/* Invoice Route */
	Route::controller(InvoiceController::class)->group(function () {
		Route::group(["prefix" => "invoices", "as" => "invoice."], function () {
			Route::get('/', 'index')->name('index');
			Route::post('/get-data', 'getData')->name('getData');
			Route::get('/create', 'create')->name('create');
			Route::post('/store', 'store')->name('store');
			Route::get('/show/{id}', 'show')->name('show');
			Route::get('/edit/{id}', 'edit')->name('edit');
			Route::put('/update/{id}', 'update')->name('update');
			Route::post('/delete/{id}', 'destroy')->name('destroy');
			Route::get('/generate-pdf/{id}', 'generateInvoicePdf')->name('generatePdf');
			Route::get('/get-product-by-barcode/{barcode}', 'getProductByBarcode')->name('getProductByBarcode');
			Route::post('/get/customer', 'getCustomer')->name('getCustomer');
		});
	});

	/* Upload Data */
	Route::controller(UploadController::class)->group(function () {
		Route::group(["prefix" => "upload", "as" => "upload."], function () {
			Route::get('/', 'index')->name('index');
			Route::post('/store', 'store')->name('store');
		});
	});

	/* Customer Route */
	Route::resource('customers', \App\Http\Controllers\CustomerController::class);
	Route::post('/customers/get-data', [\App\Http\Controllers\CustomerController::class, 'getCustomerData'])->name('getCustomerData');

	/* Brand Route */
	Route::resource('brands', \App\Http\Controllers\BrandController::class);
	Route::post('/brands/get-data', [\App\Http\Controllers\BrandController::class, 'getBrandData'])->name('getBrandData');

	/* Product (Device) Route */
	Route::resource('mobiles', \App\Http\Controllers\DeviceController::class);
	Route::post('/mobiles/get-data', [\App\Http\Controllers\DeviceController::class, 'getMobileData'])->name('getMobileData');
	Route::post('/mobiles/search-imei', [\App\Http\Controllers\DeviceController::class, 'searchByImei'])->name('mobiles.searchImei');

	/* Accessory Route */
	Route::resource('accessories', \App\Http\Controllers\AccessoryController::class);
	Route::post('/accessories/get-data', [\App\Http\Controllers\AccessoryController::class, 'getAccessoryData'])->name('getAccessoryData');

	/* Supplier Route */
	Route::resource('suppliers', \App\Http\Controllers\SupplierController::class);
	Route::post('/suppliers/get-data', [\App\Http\Controllers\SupplierController::class, 'getSupplierData'])->name('getSupplierData');
	Route::post('/suppliers/search', [\App\Http\Controllers\SupplierController::class, 'searchSupplier'])->name('supplier.search');

	/* Purchase Route */
	Route::resource('purchases', \App\Http\Controllers\PurchaseController::class);
	Route::post('/purchases/get-data', [\App\Http\Controllers\PurchaseController::class, 'getPurchaseData'])->name('getPurchaseData');

});
