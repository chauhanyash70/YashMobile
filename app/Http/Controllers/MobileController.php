<?php

namespace App\Http\Controllers;

use App\Models\Mobile;
use App\Models\Brand;
use App\Models\MobileModel;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use App\Exports\MobileExport;
use App\Exports\AvailableMobileExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;
use App\Http\Traits\Traits;

class MobileController extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
		$brands = Brand::orderBy('name')->get();

		return view('device.index', [
			'title' => "Mobiles",
			'header_title' => "Mobiles",
			'tagline' => "Manage your inventory and mobile device stock.",
			'breadcrumb' => array(),
			'brands' => $brands,
		]);
	}

	public function searchByHsn(Request $request)
	{
		$hsn = $request->input('hsn');
		if (!$hsn) {
			return response()->json(null);
		}

		// Find the latest record for this serial number
		$mobile = Mobile::with(['brand', 'model'])
			->where('hsn_number', $hsn)
			->latest()
			->first();

		if ($mobile) {
			// Get last customer who bought this device (if sold before)
			$lastSale = InvoiceItem::where('mobile_id', $mobile->id)
				->with('invoice.customer')
				->latest()
				->first();

			$customer = $lastSale->invoice->customer ?? null;

			return response()->json([
				'brand_id' => $mobile->brand_id,
				'model_name' => $mobile->model->name ?? '',
				'storage' => $mobile->storage,
				'ram' => $mobile->ram,
				'color' => $mobile->color,
				'condition' => $mobile->condition_type,
				'buy_price' => $mobile->buy_price,
				// Customer Info (to be filled in Purchase From section)
				'customer_name' => $customer->name ?? '',
				'customer_phone' => $customer->phone ?? '',
				'customer_address' => $customer->address ?? '',
			]);
		}

		return response()->json(null);
	}

	public function search(Request $request)
	{
		$hsn = $request->input('hsn');
		if (!$hsn) {
			return back()->with('error', 'Please enter an HSN number.');
		}

		// Find the latest mobile record with this HSN number
		$mobile = Mobile::where('hsn_number', $hsn)
			->latest()
			->first();

		if ($mobile) {
			return redirect()->route('mobiles.show', $mobile->id);
		}

		return redirect()->route('mobiles.create', ['hsn' => $hsn])
			->with('info', 'Device with HSN ' . $hsn . ' not found. You can create it here.');
	}

	public function getMobileData(Request $request)
	{
		// DataTable column mapping
		$columns = [
			0 => 'mobiles.id',
			1 => 'brands.name',
			2 => 'models.name',
			3 => 'mobiles.storage',
			4 => 'mobiles.status',
		];

		$query = Mobile::query()
			->with(['brand', 'model'])
			->leftJoin('brands', 'mobiles.brand_id', '=', 'brands.id')
			->leftJoin('models', 'mobiles.model_id', '=', 'models.id')
			->whereIn('mobiles.id', function ($q) {
				$q->select(DB::raw('MAX(id)'))
					->from('mobiles')
					->where('user_id', auth()->id())
					->groupBy('hsn_number');
			})
			->select('mobiles.*');

		$totalData = Mobile::distinct()->count('hsn_number');

		if (!empty($request->input('brand_id'))) {
			$query->where('mobiles.brand_id', $request->input('brand_id'));
		}

		if (!empty($request->input('status'))) {
			$query->where('mobiles.status', $request->input('status'));
		}

		if (!empty($request->input('condition'))) {
			$query->where('mobiles.condition_type', $request->input('condition'));
		}

		if (!empty($request->input('from_date'))) {
			$query->whereDate('mobiles.created_at', '>=', $request->input('from_date'));
		}

		if (!empty($request->input('to_date'))) {
			$query->whereDate('mobiles.created_at', '<=', $request->input('to_date'));
		}

		$search = $request->input('search.value');
		if (!empty($search)) {
			$query->where(function ($q) use ($search) {
				$q->where('brands.name', 'like', "%{$search}%")
					->orWhere('models.name', 'like', "%{$search}%")
					->orWhere('mobiles.hsn_number', 'like', "%{$search}%")
					->orWhere('mobiles.color', 'like', "%{$search}%");
			});
		}

		$totalFiltered = $query->count();

		$orderColumn = 'mobiles.id';
		$orderDir = 'DESC';
		if ($request->has('order.0.column')) {
			$index = $request->order[0]['column'];
			if (isset($columns[$index])) {
				$orderColumn = $columns[$index];
				$orderDir = $request->order[0]['dir'] ?? 'ASC';
			}
		}
		$query->orderBy($orderColumn, $orderDir);

		$start = (int) $request->input('start', 0);
		$length = (int) $request->input('length', 10);
		$mobiles = $query->offset($start)->limit($length)->get();

		$data = [];
		foreach ($mobiles as $index => $m) {
			$data[] = [
				'id' => $start + $index + 1,
				'brand' => $m->brand->name ?? 'N/A',
				'model' => $m->model->name ?? 'N/A',
				'invoice_items_count' => $m->invoiceItems()->count(),
				'specs' => view('device.specs', ['d' => $m])->render(),
				'hsn_number' => $m->hsn_number ?: 'N/A',
				'hsn_number_val' => $m->hsn_number ?: 'N/A',
				'stock' => '<span class="badge ' . ($m->status == 'in_stock' ? 'bg-success' : 'bg-danger') . '">' . ($m->status == 'in_stock' ? '1' : '0') . '</span>',
				'show_url' => route('mobiles.show', $m->id),
				'edit_url' => route('mobiles.edit', $m->id),
				'delete_url' => route('mobiles.destroy', $m->id),
				'actions' => $m->id,
			];
		}

		return response()->json([
			"draw" => intval($request->input('draw')),
			"recordsTotal" => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data" => $data,
		]);
	}

	public function available()
	{
		$brands = Brand::orderBy('name')->get();

		return view('device.available', [
			'title' => "Available Mobiles",
			'header_title' => "Available Stock",
			'tagline' => "View and manage all mobile devices currently available.",
			'breadcrumb' => array(),
			'brands' => $brands,
		]);
	}

	public function getAvailableMobileData(Request $request)
	{
		// DataTable column mapping
		$columns = [
			0 => 'mobiles.id',
			1 => 'brands.name',
			2 => 'models.name',
			3 => 'mobiles.storage',
			4 => 'mobiles.status',
		];

		$query = Mobile::query()
			->with(['brand', 'model'])
			->leftJoin('brands', 'mobiles.brand_id', '=', 'brands.id')
			->leftJoin('models', 'mobiles.model_id', '=', 'models.id')
			->where('mobiles.status', 'in_stock')
			->where('mobiles.user_id', auth()->id())
			->select('mobiles.*');

		$totalData = Mobile::where('status', 'in_stock')->where('user_id', auth()->id())->count();

		if (!empty($request->input('brand_id'))) {
			$query->where('mobiles.brand_id', $request->input('brand_id'));
		}

		if (!empty($request->input('condition'))) {
			$query->where('mobiles.condition_type', $request->input('condition'));
		}

		if (!empty($request->input('from_date'))) {
			$query->whereDate('mobiles.created_at', '>=', $request->input('from_date'));
		}

		if (!empty($request->input('to_date'))) {
			$query->whereDate('mobiles.created_at', '<=', $request->input('to_date'));
		}

		$search = $request->input('search.value');
		if (!empty($search)) {
			$query->where(function ($q) use ($search) {
				$q->where('brands.name', 'like', "%{$search}%")
					->orWhere('models.name', 'like', "%{$search}%")
					->orWhere('mobiles.hsn_number', 'like', "%{$search}%")
					->orWhere('mobiles.color', 'like', "%{$search}%");
			});
		}

		$totalFiltered = $query->count();

		$orderColumn = 'mobiles.id';
		$orderDir = 'DESC';
		if ($request->has('order.0.column')) {
			$index = $request->order[0]['column'];
			if (isset($columns[$index])) {
				$orderColumn = $columns[$index];
				$orderDir = $request->order[0]['dir'] ?? 'ASC';
			}
		}
		$query->orderBy($orderColumn, $orderDir);

		$start = (int) $request->input('start', 0);
		$length = (int) $request->input('length', 10);
		$mobiles = $query->offset($start)->limit($length)->get();

		$data = [];
		foreach ($mobiles as $index => $m) {
			$data[] = [
				'id' => $start + $index + 1,
				'brand' => $m->brand->name ?? 'N/A',
				'model' => $m->model->name ?? 'N/A',
				'invoice_items_count' => $m->invoiceItems()->count(),
				'specs' => view('device.specs', ['d' => $m])->render(),
				'hsn_number' => $m->hsn_number ?: 'N/A',
				'hsn_number_val' => $m->hsn_number ?: 'N/A',
				'stock' => '<span class="badge bg-success">In Stock</span>',
				'show_url' => route('mobiles.show', $m->id),
				'edit_url' => route('mobiles.edit', $m->id),
				'delete_url' => route('mobiles.destroy', $m->id),
				'actions' => $m->id,
			];
		}

		return response()->json([
			"draw" => intval($request->input('draw')),
			"recordsTotal" => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data" => $data,
		]);
	}

	public function export(Request $request)
	{
		return Excel::download(
			new MobileExport($request->all()),
			'mobiles_export_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
		);
	}

	public function exportAvailable(Request $request)
	{
		return Excel::download(
			new AvailableMobileExport($request->all()),
			'available_mobiles_export_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
		);
	}

	public function show($id)
	{
		$mobile = Mobile::with([
			'brand',
			'model',
			'transactions.customer',
			'repairs',
			'expenses'
		])->findOrFail($id);

		// For history, we search all records with same serial number
		$history = Mobile::where('hsn_number', $mobile->hsn_number)
			->with(['transactions.customer', 'invoiceItems.invoice.customer'])
			->orderBy('mobiles.id', 'desc')
			->get();

		return view('device.show', compact('mobile', 'history'));
	}

	public function create(Request $request)
	{
		$brands = Brand::orderBy('name')->get();
		$models = MobileModel::orderBy('name')->get();
		$prefilledHsn = $request->query('hsn');

		return view('device.create', compact('brands', 'models', 'prefilledHsn'));
	}

	public function store(Request $request)
	{
		$request->validate([
			'brand_id' => 'required',
			'model_name' => 'required|string',
			'storage' => 'required|string',
			'ram' => 'required|string',
			'color' => 'required|string',
			'condition' => 'required|string',
			'hsn_number' => 'required|string|unique:mobiles,hsn_number,NULL,id,user_id,' . auth()->id(),
			'buy_price' => 'required|numeric|min:0',
			'supplier_name' => 'required|string',
			'supplier_phone' => 'required|string',
			'supplier_address' => 'required|string',
			'purchase_date' => 'required|date',
		]);

		try {
			DB::beginTransaction();

			$model = MobileModel::firstOrCreate(
				['brand_id' => $request->brand_id, 'name' => $request->model_name]
			);

			foreach ([$request] as $unitData) {
				// Check if HSN already in stock
				$existing = Mobile::where('hsn_number', $unitData->hsn_number)->where('status', 'in_stock')->first();
				if ($existing) {
					throw new Exception("HSN " . $unitData->hsn_number . " is already in stock.");
				}

				$mobile = Mobile::create([
					'brand_id' => $request->brand_id,
					'model_id' => $model->id,
					'hsn_number' => $unitData->hsn_number,
					'storage' => $request->storage,
					'ram' => $request->ram,
					'color' => $request->color,
					'battery_health' => $request->battery_health,
					'condition_type' => Str::lower($request->condition),
					'buy_price' => $unitData->buy_price,
					'status' => 'in_stock',
				]);

				$customer = Customer::updateOrCreate(
					['phone' => $unitData->supplier_phone],
					[
						'name' => $unitData->supplier_name,
						'address' => $unitData->supplier_address
					]
				);

				// Create Invoice (type buy)
				$invoice = Invoice::create([
					'customer_id' => $customer->id,
					'invoice_no' => Traits::getInvoiceNumber(),
					'invoice_date' => $unitData->purchase_date,
					'invoice_type' => 'buy',
					'subtotal' => $unitData->buy_price,
					'grand_total' => $unitData->buy_price,
					'paid_amount' => $unitData->buy_price,
					'payment_status' => 'paid',
					'status' => 'paid',
				]);

				// Create Transaction
				$transaction = Transaction::create([
					'mobile_id' => $mobile->id,
					'customer_id' => $customer->id,
					'transaction_type' => 'buy',
					'price' => $unitData->buy_price,
					'transaction_date' => $unitData->purchase_date,
					'invoice_no' => $invoice->invoice_no,
				]);

				// Create Invoice Item
				InvoiceItem::create([
					'invoice_id' => $invoice->id,
					'mobile_id' => $mobile->id,
					'transaction_id' => $transaction->id,
					'qty' => 1,
					'price' => $unitData->buy_price,
					'total' => $unitData->buy_price,
				]);
			}

			DB::commit();
			return redirect()->route('mobiles.index')->with('success', 'Mobile unit added successfully.');

		} catch (Exception $e) {
			DB::rollBack();
			return back()->with('error', 'Error adding mobile: ' . $e->getMessage())->withInput();
		}
	}

	public function edit($id)
	{
		$device = Mobile::with(['brand', 'model', 'purchaseTransaction.customer'])->findOrFail($id);
		$brands = Brand::whereIn('type', ['device', 'both'])->orderBy('name', 'asc')->get();

		// Load all records with the same HSN number
		$devices = Mobile::where('hsn_number', $device->hsn_number)
			->with(['brand', 'model', 'purchaseTransaction.customer'])
			->orderBy('id', 'desc')
			->get();

		// For each device, if it is sold, get its sale transaction
		foreach ($devices as $d) {
			$d->saleTransaction = null;
			if ($d->status == 'sold') {
				$d->saleTransaction = Transaction::where('mobile_id', $d->id)
					->where('transaction_type', 'sell')
					->with('customer')
					->latest()
					->first();
			}
		}

		return view('device.edit', compact('device', 'brands', 'devices'));
	}


	public function update(Request $request, $id)
	{
		$rules = [
			'brand_id' => 'required',
			'model_name' => 'required|string',
			'storage' => 'required|string',
			'ram' => 'required|string',
			'color' => 'required|string',
			'condition' => 'required|string',
			'units' => 'required|array',
			'units.*.id' => 'required|exists:mobiles,id',
			'units.*.hsn_number' => 'required|string',
			'units.*.buy_price' => 'required|numeric|min:0',
			'units.*.purchase_date' => 'required|date',
			'units.*.supplier_phone' => 'required|string',
			'units.*.supplier_name' => 'required|string',
		];

		$request->validate($rules);

		try {
			DB::beginTransaction();

			$model = MobileModel::firstOrCreate(
				['brand_id' => $request->brand_id, 'name' => $request->model_name]
			);

			foreach ($request->units as $unitData) {
				$mobile = Mobile::findOrFail($unitData['id']);

				// Dynamically validate sell details if this unit status is 'sold'
				if ($mobile->status == 'sold') {
					$validator = \Validator::make($unitData, [
						'sell_price' => 'required|numeric|min:0',
						'sale_date' => 'required|date',
						'customer_phone' => 'required|string',
						'customer_name' => 'required|string',
					]);

					if ($validator->fails()) {
						throw new Exception("Validation failed for unit " . $unitData['hsn_number'] . ": " . implode(', ', $validator->errors()->all()));
					}
				}

				$mobile->update([
					'brand_id' => $request->brand_id,
					'model_id' => $model->id,
					'hsn_number' => $unitData['hsn_number'],
					'storage' => $request->storage,
					'ram' => $request->ram,
					'color' => $request->color,
					'battery_health' => $request->battery_health,
					'condition_type' => Str::lower($request->condition),
				]);

				// 1. Update/Create Purchase Transaction & Invoice
				$purchaseTransaction = $mobile->purchaseTransaction;
				if ($purchaseTransaction) {
					// Update existing supplier details
					$supplier = Customer::updateOrCreate(
						['phone' => $unitData['supplier_phone']],
						['name' => $unitData['supplier_name'], 'address' => $unitData['supplier_address'] ?? '']
					);

					$purchaseTransaction->update([
						'customer_id' => $supplier->id,
						'price' => $unitData['buy_price'],
						'transaction_date' => $unitData['purchase_date'],
					]);

					// Update related invoice if exists
					if ($purchaseTransaction->invoice_no) {
						$invoice = Invoice::where('invoice_no', $purchaseTransaction->invoice_no)->first();
						if ($invoice) {
							$invoice->update([
								'customer_id' => $supplier->id,
								'invoice_date' => $unitData['purchase_date'],
								'subtotal' => $unitData['buy_price'],
								'grand_total' => $unitData['buy_price'],
								'paid_amount' => $unitData['buy_price'],
							]);

							// Update invoice item
							$invoice->items()->where('mobile_id', $mobile->id)->update([
								'price' => $unitData['buy_price'],
								'total' => $unitData['buy_price'],
							]);
						}
					}
				} else {
					// Create new Purchase Invoice & Transaction if supplier details are filled
					if ($unitData['supplier_phone'] && $unitData['supplier_name']) {
						$supplier = Customer::updateOrCreate(
							['phone' => $unitData['supplier_phone']],
							['name' => $unitData['supplier_name'], 'address' => $unitData['supplier_address'] ?? '']
						);

						// Create Invoice (type buy)
						$invoice = Invoice::create([
							'customer_id' => $supplier->id,
							'invoice_no' => Traits::getInvoiceNumber(),
							'invoice_date' => $unitData['purchase_date'],
							'invoice_type' => 'buy',
							'subtotal' => $unitData['buy_price'],
							'grand_total' => $unitData['buy_price'],
							'paid_amount' => $unitData['buy_price'],
							'status' => 'paid',
						]);

						// Create Transaction
						$purchaseTransaction = Transaction::create([
							'mobile_id' => $mobile->id,
							'customer_id' => $supplier->id,
							'transaction_type' => 'buy',
							'price' => $unitData['buy_price'],
							'transaction_date' => $unitData['purchase_date'],
							'invoice_no' => $invoice->invoice_no,
						]);

						// Create Invoice Item
						InvoiceItem::create([
							'invoice_id' => $invoice->id,
							'mobile_id' => $mobile->id,
							'transaction_id' => $purchaseTransaction->id,
							'qty' => 1,
							'price' => $unitData['buy_price'],
							'total' => $unitData['buy_price'],
						]);
					}
				}

				// 2. Update Sale Transaction & Invoice (only if mobile is sold)
				if ($mobile->status == 'sold') {
					$saleTransaction = Transaction::where('mobile_id', $mobile->id)
						->where('transaction_type', 'sell')
						->latest()
						->first();

					if ($saleTransaction) {
						$customer = Customer::updateOrCreate(
							['phone' => $unitData['customer_phone']],
							['name' => $unitData['customer_name'], 'address' => $unitData['customer_address'] ?? '']
						);

						$saleTransaction->update([
							'customer_id' => $customer->id,
							'price' => $unitData['sell_price'],
							'transaction_date' => $unitData['sale_date'],
						]);

						// Update related invoice if exists
						if ($saleTransaction->invoice_no) {
							$invoice = Invoice::where('invoice_no', $saleTransaction->invoice_no)->first();
							if ($invoice) {
								$invoice->update([
									'customer_id' => $customer->id,
									'invoice_date' => $unitData['sale_date'],
								]);

								// Update invoice item
								$invoice->items()->where('mobile_id', $mobile->id)->update([
									'price' => $unitData['sell_price'],
									'total' => $unitData['sell_price'],
								]);

								$invoice->recalculateTotals();
							}
						}
					}
				}
			}

			DB::commit();
			return redirect()->route('mobiles.index')->with('success', 'Mobile units updated successfully.');
		} catch (Exception $e) {
			DB::rollBack();
			return back()->with('error', 'Error updating mobile units: ' . $e->getMessage())->withInput();
		}
	}

	public function destroy($id)
	{
		try {
			$mobile = Mobile::findOrFail($id);
			if ($mobile->invoiceItems()->count() > 0) {
				return back()->with('error', 'Cannot delete mobile with sales history.');
			}
			$mobile->delete();
			return redirect()->route('mobiles.index')->with('success', 'Mobile deleted successfully.');
		} catch (Exception $e) {
			return back()->with('error', 'Error deleting mobile: ' . $e->getMessage());
		}
	}

	public function hsnHistory($id)
	{
		$mobile = Mobile::with(['brand', 'model'])->findOrFail($id);
		$history = Mobile::where('hsn_number', $mobile->hsn_number)
			->with(['transactions.customer', 'invoiceItems.invoice.customer'])
			->orderBy('mobiles.id', 'desc')
			->get();

		return view('device.hsn_history', [
			'imei' => $mobile,
			'history' => $history,
			'header_title' => "HSN History: " . $mobile->hsn_number,
			'tagline' => "Detailed lifecycle history for unit " . $mobile->hsn_number
		]);
	}

	public function searchCustomers(Request $request)
	{
		$q = $request->input('q', '');
		$customers = Customer::where('phone', 'like', "%{$q}%")
			->orWhere('name', 'like', "%{$q}%")
			->orderBy('phone')
			->limit(20)
			->get(['id', 'name', 'phone']);

		$results = $customers->map(fn($c) => [
			'id'   => $c->phone,
			'text' => $c->phone . ($c->name ? ' — ' . $c->name : ''),
			'name' => $c->name,
		]);

		return response()->json(['results' => $results]);
	}

	public function buyback($invoice_item_id)
	{
		$invoiceItem = InvoiceItem::with(['invoice.customer', 'mobile.brand', 'mobile.model'])->findOrFail($invoice_item_id);
		return view('device.buyback', compact('invoiceItem'));
	}

	public function buybackStore(Request $request)
	{
		$request->validate([
			'invoice_item_id' => 'required',
			'buyback_price'   => 'required|numeric|min:0',
			'buyback_date'    => 'required|date',
			'ram'             => 'nullable|string',
			'storage'         => 'nullable|string',
			'customer_phone'  => 'required_if:new_customer,1|nullable|string',
			'customer_name'   => 'nullable|string',
			'customer_address' => 'nullable|string',
		]);

		try {
			DB::beginTransaction();

			$oldItem = InvoiceItem::with('mobile', 'invoice')->findOrFail($request->invoice_item_id);

			// Mark as bought back
			$oldItem->is_bought_back = true;
			$oldItem->save();
			$oldMobile = $oldItem->mobile;

			// Create a NEW Mobile record for the same IMEI to track the new lifecycle
			$newMobile = Mobile::create([
				'brand_id' => $oldMobile->brand_id,
				'model_id' => $oldMobile->model_id,
				'hsn_number' => $oldMobile->hsn_number,
				'storage' => $request->storage ?: $oldMobile->storage,
				'ram' => $request->ram ?: $oldMobile->ram,
				'color' => $oldMobile->color,
				'battery_health' => $request->battery_health,
				'condition_type' => 'used',
				'status' => 'in_stock',
			]);

			// Use new/different customer if checkbox was checked
			if ($request->boolean('new_customer') && $request->filled('customer_phone')) {
				$customer = Customer::updateOrCreate(
					['phone'   => $request->customer_phone],
					['name'    => $request->customer_name ?? '',
					 'address' => $request->customer_address ?? '']
				);
			} else {
				$customer = $oldItem->invoice->customer;
			}

			$invoiceDate = $request->buyback_date ?: date('Y-m-d');

			// Create Buyback Invoice
			$invoice = Invoice::create([
				'customer_id' => $customer->id,
				'invoice_no' => Traits::getInvoiceNumber(),
				'invoice_date' => $invoiceDate,
				'invoice_type' => 'buy',
				'subtotal' => $request->buyback_price,
				'grand_total' => $request->buyback_price,
				'paid_amount' => $request->buyback_price,
				'status' => 'paid',
				'notes' => 'Buyback from Invoice #' . $oldItem->invoice->invoice_no,
			]);

			// Create Transaction
			$transaction = Transaction::create([
				'mobile_id' => $newMobile->id,
				'customer_id' => $customer->id,
				'transaction_type' => 'buy',
				'price' => $request->buyback_price,
				'transaction_date' => $invoiceDate,
				'invoice_no' => $invoice->invoice_no,
			]);

			// Create Invoice Item
			InvoiceItem::create([
				'invoice_id' => $invoice->id,
				'mobile_id' => $newMobile->id,
				'transaction_id' => $transaction->id,
				'qty' => 1,
				'price' => $request->buyback_price,
				'total' => $request->buyback_price,
			]);

			DB::commit();
			return redirect()->route('mobiles.show', $newMobile->id)->with('success', 'Buyback completed successfully. Unit added back to stock.');

		} catch (Exception $e) {
			DB::rollBack();
			return back()->with('error', 'Error in buyback: ' . $e->getMessage());
		}
	}

	public function storeUnit(Request $request, $id)
	{
		$request->validate([
			'imei' => 'required|string',
			'buy_price' => 'required|numeric|min:0',
			'supplier_name' => 'required|string',
			'supplier_phone' => 'required|string',
			'purchase_date' => 'required|date',
		]);

		try {
			DB::beginTransaction();

			$baseMobile = Mobile::findOrFail($id);

			// Check if IMEI already in stock
			$existing = Mobile::where('hsn_number', $request->imei)->where('status', 'in_stock')->first();
			if ($existing) {
				throw new Exception("IMEI " . $request->imei . " is already in stock.");
			}

			$mobile = Mobile::create([
				'brand_id' => $baseMobile->brand_id,
				'model_id' => $baseMobile->model_id,
				'hsn_number' => $request->imei,
				'storage' => $baseMobile->storage,
				'ram' => $baseMobile->ram,
				'color' => $baseMobile->color,
				'battery_health' => $baseMobile->battery_health,
				'condition_type' => $baseMobile->condition_type,
				'status' => 'in_stock',
			]);

			$customer = Customer::updateOrCreate(
				['phone' => $request->supplier_phone],
				['name' => $request->supplier_name]
			);

			$invoice = Invoice::create([
				'customer_id' => $customer->id,
				'invoice_no' => Traits::getInvoiceNumber(),
				'invoice_date' => $request->purchase_date,
				'invoice_type' => 'buy',
				'subtotal' => $request->buy_price,
				'grand_total' => $request->buy_price,
				'paid_amount' => $request->buy_price,
				'status' => 'paid',
			]);

			$transaction = Transaction::create([
				'mobile_id' => $mobile->id,
				'customer_id' => $customer->id,
				'transaction_type' => 'buy',
				'price' => $request->buy_price,
				'transaction_date' => $request->purchase_date,
				'invoice_no' => $invoice->invoice_no,
			]);

			InvoiceItem::create([
				'invoice_id' => $invoice->id,
				'mobile_id' => $mobile->id,
				'transaction_id' => $transaction->id,
				'qty' => 1,
				'price' => $request->buy_price,
				'total' => $request->buy_price,
			]);

			DB::commit();
			return redirect()->back()->with('success', 'Unit added successfully.');

		} catch (Exception $e) {
			DB::rollBack();
			return back()->with('error', 'Error adding unit: ' . $e->getMessage());
		}
	}
}
