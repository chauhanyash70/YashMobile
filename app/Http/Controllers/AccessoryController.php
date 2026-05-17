<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class AccessoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('accessory.index', [
            'title' => "Accessories",
            'breadcrumb' => array()
        ]);
    }

    /**
     * Get all Accessory data using AJAX (Datatables Server Side)
     */
    public function getAccessoryData(Request $request)
    {
        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'sku',
            3 => 'stock',
            4 => 'purchase_price',
            5 => 'sale_price',
            6 => 'id'
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')] ?? 'id';
        $dir = $request->input('order.0.dir') ?? 'DESC';

        // Base query
        $query = Accessory::query();

        // Total count before filter
        $totalData = $query->count();

        // Searching
        if (!empty($request->input('search.value'))) {

            $search = $request->input('search.value');

            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        // Filtered count
        $totalFiltered = $query->count();

        // Ordering + Pagination
        $items = $query->orderBy($order, $dir)
            ->offset($start)
            ->limit($limit)
            ->get();

        $dataArray = [];

        foreach ($items as $data) {

            $stockBadge = $data->stock > 0
                ? '<span class="badge bg-success">' . $data->stock . '</span>'
                : '<span class="badge bg-danger">' . $data->stock . '</span>';

            $dataArray[] = [
                'id' => $data->id,
                'name' => $data->name,
                'sku' => $data->sku,
                'stock' => $stockBadge,
                'purchase_price' => '₹' . number_format($data->purchase_price, 2),
                'sale_price' => '₹' . number_format($data->sale_price, 2),

                // URLs
                'edit_url' => route('accessories.edit', $data->id),
                'delete_url' => route('accessories.destroy', $data->id),

                // Needed for actions rendering
                'actions' => $data->id,
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $dataArray
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = \App\Models\Brand::whereIn('type', ['accessory', 'both'])->orderBy('name')->get();
        return view('accessory.create', compact('brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'brand_id' => 'required',
            'name' => 'required|string',
            'sku' => 'nullable|string|unique:accessories,sku',
            'purchase_price' => 'nullable|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'purchase_date' => 'required|date',
            'supplier_name' => 'required|string',
            'supplier_mobile_number' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // 1. Handle Supplier
            $supplier = Supplier::firstOrCreate(
                ['phone' => $request->supplier_mobile_number],
                [
                    'name' => $request->supplier_name,
                    'city' => $request->city,
                    'address' => $request->address,
                ]
            );

            // 2. Find or Create Accessory
            $accessory = Accessory::where('brand_id', $request->brand_id)
                ->where('name', $request->name)
                ->where('model', $request->model)
                ->where('color', $request->color)
                ->first();

            if ($accessory) {
                $accessory->update([
                    'purchase_price' => $request->purchase_price ?? $accessory->purchase_price,
                    'sale_price' => $request->sale_price ?? $accessory->sale_price,
                    'stock' => $accessory->stock + $request->stock,
                    'purchase_date' => $request->purchase_date ? Carbon::parse($request->purchase_date) : $accessory->purchase_date,
                ]);
            } else {
                $accessory = Accessory::create([
                    'brand_id' => $request->brand_id,
                    'name' => $request->name,
                    'model' => $request->model,
                    'color' => $request->color,
                    'sku' => $request->sku ?? 'ACC-' . time(),
                    'purchase_price' => $request->purchase_price ?? 0,
                    'sale_price' => $request->sale_price,
                    'stock' => $request->stock,
                    'description' => $request->description,
                    'purchase_date' => $request->purchase_date ? Carbon::parse($request->purchase_date) : null
                ]);
            }

            // 3. Create Purchase Record
            $purchase = Purchase::create([
                'supplier_id' => $supplier->id,
                'purchase_date' => $request->purchase_date ? Carbon::parse($request->purchase_date) : now(),
                'total_amount' => ($request->purchase_price ?? 0) * $request->stock,
                'paid_amount' => ($request->purchase_price ?? 0) * $request->stock,
                'due_amount' => 0,
                'payment_method' => 'cash',
                'notes' => 'Bulk accessory entry'
            ]);

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'item_type' => 'accessory',
                'item_id' => $accessory->id,
                'quantity' => $request->stock,
                'price' => $request->purchase_price ?? 0,
                'total' => ($request->purchase_price ?? 0) * $request->stock,
            ]);

            DB::commit();

            return redirect()->route('accessories.index')->with('success', 'Accessory added successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error adding accessory: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $accessory = Accessory::with(['brand'])->findOrFail($id);
        $brands = \App\Models\Brand::whereIn('type', ['accessory', 'both'])->orderBy('name')->get();

        $purchaseItem = PurchaseItem::where('item_type', 'accessory')
            ->where('item_id', $accessory->id)
            ->first();

        $purchase = $purchaseItem ? Purchase::with('supplier')->find($purchaseItem->purchase_id) : null;

        return view('accessory.edit', compact('accessory', 'brands', 'purchase'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'brand_id' => 'required',
            'name' => 'required|string',
            'model' => 'nullable|string',
            'color' => 'nullable|string',
            'sku' => 'nullable|string|unique:accessories,sku,' . $id,
            'purchase_price' => 'nullable|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'purchase_date' => 'required|date',
            'supplier_name' => 'required|string',
            'supplier_mobile_number' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $accessory = Accessory::findOrFail($id);

            // 1. Update Accessory
            $accessory->update([
                'brand_id' => $request->brand_id,
                'name' => $request->name,
                'model' => $request->model,
                'color' => $request->color,
                'sku' => $request->sku,
                'description' => $request->description,
                'purchase_price' => $request->purchase_price,
                'sale_price' => $request->sale_price,
                'stock' => $request->stock,
                'purchase_date' => $request->purchase_date ? Carbon::parse($request->purchase_date) : null,
            ]);

            // 2. Handle Supplier
            $supplier = Supplier::firstOrCreate(
                ['phone' => $request->supplier_mobile_number],
                [
                    'name' => $request->supplier_name,
                    'city' => $request->city,
                    'address' => $request->address,
                ]
            );

            // 3. Update or Create Purchase Record associated with this item
            $purchaseItem = PurchaseItem::where('item_type', 'accessory')
                ->where('item_id', $accessory->id)
                ->first();

            if ($purchaseItem) {
                $purchase = Purchase::find($purchaseItem->purchase_id);
                if ($purchase) {
                    $purchase->update([
                        'supplier_id' => $supplier->id,
                        'purchase_date' => $request->purchase_date ? Carbon::parse($request->purchase_date) : null,
                        'total_amount' => ($request->purchase_price ?? 0) * $request->stock,
                        'paid_amount' => ($request->purchase_price ?? 0) * $request->stock,
                    ]);

                    $purchaseItem->update([
                        'quantity' => $request->stock,
                        'price' => $request->purchase_price ?? 0,
                        'total' => ($request->purchase_price ?? 0) * $request->stock,
                    ]);
                }
            } else {
                // Create if not exists (migrating old data)
                $purchase = Purchase::create([
                    'supplier_id' => $supplier->id,
                    'purchase_date' => $request->purchase_date ? Carbon::parse($request->purchase_date) : now(),
                    'total_amount' => ($request->purchase_price ?? 0) * $request->stock,
                    'paid_amount' => ($request->purchase_price ?? 0) * $request->stock,
                    'due_amount' => 0,
                    'payment_method' => 'cash',
                    'notes' => 'Updated accessory entry'
                ]);

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_type' => 'accessory',
                    'item_id' => $accessory->id,
                    'quantity' => $request->stock,
                    'price' => $request->purchase_price ?? 0,
                    'total' => ($request->purchase_price ?? 0) * $request->stock,
                ]);
            }

            DB::commit();
            return redirect()->route('accessories.index')->with('success', 'Accessory updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating accessory: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $accessory = Accessory::findOrFail($id);
            $accessory->delete();
            return redirect()->route('accessories.index')->with('success', 'Accessory deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting accessory: ' . $e->getMessage());
        }
    }
}
