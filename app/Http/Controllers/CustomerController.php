<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Device;
use App\Models\Accessory;
use App\Http\Traits\Traits;

class CustomerController extends Controller
{
	public function index()
	{
		return view('customer.index', [
			'title' => "Customers",
			'header_title' => "Customers",
			'tagline' => "View and manage your customer list and their purchase history.",
			'breadcrumb' => array()
		]);
	}

	/**
	 * Get all customer data using AJAX (Datatables Server Side)
	 */
	public function getCustomerData(Request $request)
	{
		$columns = [
			0 => 'id',
			1 => 'name',
			2 => 'phone',
			3 => 'address',
			4 => 'created_at'
		];

		$limit = $request->input('length');
		$start = $request->input('start');
		$order = $columns[$request->input('order.0.column')] ?? 'id';
		$dir = $request->input('order.0.dir') ?? 'DESC';

		// Base query
		$query = Customer::where('user_id', auth()->id());

		// Total count
		$totalData = $query->count();

		// Search filter
		if (!empty($request->input('search.value'))) {

			$search = $request->input('search.value');

			$query->where(function ($q) use ($search) {
				$q->where('name', 'LIKE', "%{$search}%")
					->orWhere('phone', 'LIKE', "%{$search}%")
					->orWhere('email', 'LIKE', "%{$search}%")
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
				'edit_url' => route('customers.edit', $data->id),
				'delete_url' => route('customers.destroy', $data->id),
				'details_url' => route('customers.show', $data->id),
				'profile_url' => $data->profile_url,
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
		return view('customer.create');
	}

	public function store(Request $request)
	{
		$request->validate([
			'name' => 'required',
			'phone' => 'nullable|unique:customers,phone',
			'email' => 'nullable|email|unique:customers,email',
			'profile_image' => 'nullable|image|max:2048',
			'customer_document.*' => 'nullable|file|max:5120',
		]);

		$data = $request->all();
		$data['user_id'] = auth()->id();

		// Handle Profile Image
		if ($request->hasFile('profile_image')) {
			$data['profile_image'] = Traits::uploadFile($request->file('profile_image'), 'customers/profiles');
		}

		// Handle Documents
		if ($request->hasFile('customer_document')) {
			$documents = [];
			foreach ($request->file('customer_document') as $file) {
				$documents[] = Traits::uploadFile($file, 'customers/documents');
			}
			$data['documents'] = $documents;
		}

		Customer::create($data);

		return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
	}

	public function show(Customer $customer)
	{
		$customer->load([
			'invoices.items.mobile.brand',
			'invoices.items.mobile.model',
		]);
		return view('customer.show', compact('customer'))->with([
			'header_title' => $customer->name,
			'tagline' => "Profile, contact information, and transaction history for this customer."
		]);
	}

	public function edit(Customer $customer)
	{
		return view('customer.create', compact('customer'));
	}

	public function update(Request $request, Customer $customer)
	{
		$request->validate([
			'name' => 'required',
			'phone' => 'nullable|unique:customers,phone,' . $customer->id,
			'email' => 'nullable|email|unique:customers,email,' . $customer->id,
			'profile_image' => 'nullable|image|max:2048',
			'customer_document.*' => 'nullable|file|max:5120',
		]);

		$data = $request->all();

		// Handle Profile Image
		if ($request->hasFile('profile_image')) {
			// Optional: delete old profile if needed
			$data['profile_image'] = Traits::uploadFile($request->file('profile_image'), 'customers/profiles');
		}

		// Handle Documents
		if ($request->hasFile('customer_document')) {
			$documents = $customer->documents ?? [];
			foreach ($request->file('customer_document') as $file) {
				$documents[] = Traits::uploadFile($file, 'customers/documents');
			}
			$data['documents'] = $documents;
		}

		$customer->update($data);

		return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
	}

	public function destroy(Customer $customer)
	{
		$customer->invoices()->delete();
		$customer->delete();
		return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
	}
}
