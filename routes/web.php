<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\HomeController;


/* Route::get('/', function () {
	return view('welcome');
}); */
Route::get('/', [HomeController::class, 'index'])->name('home');

Auth::routes();

Route::group(['middleware' => ['auth']], function () {
	Route::get('mobiles/search-global', [\App\Http\Controllers\MobileController::class, 'search'])->name('mobiles.searchGlobal');

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
			Route::post('/get/supplier', 'getSupplier')->name('getSupplier');
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
	Route::get('/mobiles/available', [\App\Http\Controllers\MobileController::class, 'available'])->name('mobiles.available');
	Route::post('/mobiles/get-available-data', [\App\Http\Controllers\MobileController::class, 'getAvailableMobileData'])->name('mobiles.getAvailableData');
	Route::resource('mobiles', \App\Http\Controllers\MobileController::class);
	Route::post('/mobiles/get-data', [\App\Http\Controllers\MobileController::class, 'getMobileData'])->name('getMobileData');
	Route::post('/mobiles/search-hsn', [\App\Http\Controllers\MobileController::class, 'searchByHsn'])->name('mobiles.searchHsn');
	Route::get('/mobiles/buyback/{invoice_item_id}', [\App\Http\Controllers\MobileController::class, 'buyback'])->name('mobiles.buyback');
	Route::post('/mobiles/buyback-store', [\App\Http\Controllers\MobileController::class, 'buybackStore'])->name('mobiles.buybackStore');
	Route::get('/mobiles/hsn-history/{id}', [\App\Http\Controllers\MobileController::class, 'hsnHistory'])->name('mobiles.hsnHistory');
	Route::post('/mobiles/{id}/add-unit', [\App\Http\Controllers\MobileController::class, 'storeUnit'])->name('mobiles.storeUnit');

	/* Accessory Route */
	Route::resource('accessories', \App\Http\Controllers\AccessoryController::class);
	Route::post('/accessories/get-data', [\App\Http\Controllers\AccessoryController::class, 'getAccessoryData'])->name('getAccessoryData');

	/* Repair Route */
	Route::resource('repairs', \App\Http\Controllers\RepairController::class);
	Route::post('/repairs/get-data', [\App\Http\Controllers\RepairController::class, 'getRepairData'])->name('getRepairData');

	/* Expense Route */
	Route::resource('expenses', \App\Http\Controllers\ExpenseController::class);

	/* Transaction Route */
	Route::resource('transactions', \App\Http\Controllers\TransactionController::class);
	Route::post('/transactions/get-data', [\App\Http\Controllers\TransactionController::class, 'getData'])->name('transactions.getData');

});
