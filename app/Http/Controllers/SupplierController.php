<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Device;
use App\Models\Accessory;

class SupplierController extends Controller
{
	public function index()
	{
		return view('supplier.index', [
			'title' => "Suppliers",
			'breadcrumb' => array()
		]);
	}

	/**
	 * Get all supplier data using AJAX (Datatables Server Side)
	 */
	public function getSupplierData(Request $request)
	{
		$columns = [
			0 => 'id',
			1 => 'name',
			2 => 'phone',
			3 => 'address',
			4 => 'created_at',
			5 => 'id'
		];

		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')] ?? 'id';
		$dir = $request->input('order.0.dir') ?? 'DESC';

		// Base query
		$query = Supplier::query();

		// Total count
		$totalData = $query->count();

		// Search filter
		if (!empty($request->input('search.value'))) {

			$search = $request->input('search.value');

			$query->where(function ($q) use ($search) {
				$q->where('name', 'LIKE', "%{$search}%")
					->orWhere('phone', 'LIKE', "%{$search}%")
					->orWhere('address', 'LIKE', "%{$search}%");
			});
		}

		// Filtered count (before limit)
		$totalFiltered = $query->count();

		// Apply order + pagination
		$customers = $query->orderBy($order, $dir)
			->offset($start)
			->limit($limit)
			->get();

		$dataArray = [];

		foreach ($customers as $data) {

			$dataArray[] = [
				'id' => $data->id,
				'name' => Str::title($data->name),
				'phone' => $data->phone,
				'email' => $data->email,
				'address' => $data->address,
				'created_at' => Carbon::parse($data->created_at)->format(config('app.date_format', 'd-m-Y')),
				'edit_url' => route('suppliers.edit', $data->id),
				'delete_url' => route('suppliers.destroy', $data->id),
				'details_url' => route('suppliers.show', $data->id),
				'actions' => $data->id
			];
		}

		return response()->json([
			'draw' => intval($request->input('draw')),
			'recordsTotal' => $totalData,
			'recordsFiltered' => $totalFiltered,
			'data' => $dataArray
		]);
	}


	public function create()
	{
		return view('supplier.create');
	}

	public function store(Request $request)
	{
		$request->validate([
			'name' => 'required',
			'phone' => 'nullable|unique:suppliers,phone',
		]);

		Supplier::create($request->all());

		return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
	}

	public function show(Supplier $supplier)
	{
		$supplier->load([
			'purchases.items.item' => function ($morphTo) {
				$morphTo->morphWith([
					Device::class => ['model', 'brand'],
					Accessory::class => ['brand'],
				]);
			},
			'purchases.items.deviceImei',
			'purchases.itemData.item' => function ($morphTo) {
				$morphTo->morphWith([
					Device::class => ['model', 'brand'],
					Accessory::class => ['brand'],
				]);
			},
			'purchases.itemData.deviceImei',
		]);

		return view('supplier.show', compact('supplier'));
	}

	public function edit(Supplier $supplier)
	{
		return view('supplier.create', compact('supplier'));
	}

	public function update(Request $request, Supplier $supplier)
	{
		$request->validate([
			'name' => 'required',
			'phone' => 'nullable|unique:suppliers,phone,' . $supplier->id,
		]);

		$supplier->update($request->all());

		return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
	}

	public function destroy(Supplier $supplier)
	{
		$supplier->delete();
		return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
	}

	public function searchSupplier(Request $request)
	{
		$supplier = Supplier::where('phone', $request->phone)->first();
		if ($supplier) {
			return response()->json($supplier);
		}
		return response()->json(null);
	}

}
