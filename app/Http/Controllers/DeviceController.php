<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Brand;
use App\Models\PhoneModel;
use App\Models\DeviceImei;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('device.index', [
            'title' => "Mobiles",
            'breadcrumb' => array()
        ]);
    }

    public function searchByImei(Request $request)
    {
        $imei = $request->input('imei');
        if (!$imei) {
            return response()->json(null);
        }

        $deviceImei = DeviceImei::with(['device.brand', 'device.model'])
            ->where('imei', $imei)
            ->first();

        if ($deviceImei && $deviceImei->device) {
            $device = $deviceImei->device;

            // Get last customer who bought this device
            $invoiceItem = InvoiceItem::where('item_type', 'device')
                ->where('item_id', $device->id)
                ->with('invoice.customer')
                ->latest()
                ->first();

            $customer = $invoiceItem->invoice->customer ?? null;

            return response()->json([
                'brand_id' => $device->brand_id,
                'model_name' => $device->model->name ?? '',
                'storage' => $device->storage,
                'ram' => $device->ram,
                'color' => $device->color,
                'condition' => $device->condition,
                'buy_price' => $device->buy_price,
                // Customer Info (to be filled in Purchase From section)
                'supplier_name' => $customer->name ?? '',
                'supplier_phone' => $customer->phone ?? '',
                'supplier_city' => $customer->city ?? '',
                'supplier_address' => $customer->address ?? '',
            ]);
        }

        return response()->json(null);
    }

    public function getMobileData(Request $request)
    {
        // DataTable column mapping
        $columns = [
            0 => 'devices.id',
            1 => 'brands.name',
            2 => 'phone_models.name',
            3 => 'devices.storage',
            4 => 'devices.stock',
            5 => 'devices.status',
        ];

        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */
        $query = Device::query()
            ->with(['brand', 'model', 'imeis'])
            ->leftJoin('brands', 'devices.brand_id', '=', 'brands.id')
            ->leftJoin('phone_models', 'devices.model_id', '=', 'phone_models.id')
            ->select('devices.*')
            ->distinct('devices.id');

        /*
        |--------------------------------------------------------------------------
        | Total Records
        |--------------------------------------------------------------------------
        */
        $totalData = Device::count();

        /*
        |--------------------------------------------------------------------------
        | Search (Datatable)
        |--------------------------------------------------------------------------
        */
        $search = $request->input('search.value');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('brands.name', 'like', "%{$search}%")
                    ->orWhere('phone_models.name', 'like', "%{$search}%")
                    ->orWhere('devices.storage', 'like', "%{$search}%")
                    ->orWhere('devices.color', 'like', "%{$search}%")
                    ->orWhere('devices.condition', 'like', "%{$search}%")
                    ->orWhereHas('imeis', function ($sub) use ($search) {
                        $sub->where('imei', $search);
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filtered Records Count
        |--------------------------------------------------------------------------
        */
        $totalFiltered = $query->distinct('devices.id')->count('devices.id');

        /*
        |--------------------------------------------------------------------------
        | Ordering
        |--------------------------------------------------------------------------
        */
        $orderColumn = 'devices.id';
        $orderDir = 'DESC';

        if ($request->has('order.0.column')) {
            $index = $request->order[0]['column'];
            if (isset($columns[$index])) {
                $orderColumn = $columns[$index];
                $orderDir = $request->order[0]['dir'] ?? 'ASC';
            }
        }

        $query->orderBy($orderColumn, $orderDir);

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $devices = $query
            ->offset($start)
            ->limit($length)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Data Formatting
        |--------------------------------------------------------------------------
        */
        $data = [];

        foreach ($devices as $index => $d) {
            $data[] = [
                'id' => $start + $index + 1,
                'brand' => $d->brand->name ?? 'N/A',
                'model' => $d->model->name ?? 'N/A',
                'invoice_items_count' => $d->invoiceItems()->count(),
                'specs' => view('device.specs', compact('d'))->render(),
                // IMEI DISPLAY (ALL IMEIS)
                'imei' => $d->imeis->pluck('imei')->implode(', ') ?: 'N/A',

                'imei_count' => '<span class="badge bg-primary">' . $d->imeis->count() . '</span>',
                'stock' => '<span class="badge ' . ($d->stock > 0 ? 'bg-success' : 'bg-danger') . '">' . $d->stock . '</span>',
                'status' => view('device.status', compact('d'))->render(),
                'show_url' => route('mobiles.show', $d->id),
                'edit_url' => route('mobiles.edit', $d->id),
                'delete_url' => route('mobiles.destroy', $d->id),
                'actions' => $d->id,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | JSON Response
        |--------------------------------------------------------------------------
        */
        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data,
        ]);
    }




    public function show($id)
    {
        $device = Device::with([
            'brand',
            'model',
            'imeis.purchaseItem.purchase.supplier',
            'imeis.invoiceItem.invoice.customer',
        ])->findOrFail($id);

        return view('device.show', compact('device'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = Brand::orderBy('name')->get();
        // We might want to load models via AJAX based on brand, but for now we can pass all or handle in view
        $models = PhoneModel::orderBy('name')->get();

        return view('device.create', compact('brands', 'models'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'brand_id' => 'required',
            'model_name' => 'required|string',
            'storage' => 'required|string',
            'ram' => 'required|string',
            'color' => 'required|string',
            'condition' => 'required|string',
            'units' => 'required|array|min:1',
            'units.*.imei' => 'required|string|unique:device_imeis,imei',
            'units.*.buy_price' => 'required|numeric|min:0',
            'units.*.repair_cost' => 'nullable|numeric|min:0',
            'units.*.supplier_name' => 'required|string',
            'units.*.supplier_phone' => 'required|string',
            'units.*.purchase_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            // 1. Find or Create Model
            $model = PhoneModel::firstOrCreate(
                [
                    'brand_id' => $request->brand_id,
                    'name' => $request->model_name
                ]
            );

            // 2. Find or Create Device SKU (Variant)
            $device = Device::where('brand_id', $request->brand_id)
                ->where('model_id', $model->id)
                ->where('storage', $request->storage)
                ->where('ram', $request->ram)
                ->where('color', $request->color)
                ->where('condition', Str::lower($request->condition))
                ->first();

            if (!$device) {
                $device = Device::create([
                    'brand_id' => $request->brand_id,
                    'model_id' => $model->id,
                    'storage' => $request->storage,
                    'ram' => $request->ram,
                    'color' => $request->color,
                    'condition' => Str::lower($request->condition),
                    'status' => 'in_stock',
                    'buy_price' => $request->units[0]['buy_price'] ?? 0,
                    'stock' => 0,
                ]);
            }

            // 3. Process Each Unit
            foreach ($request->units as $unitData) {
                // Handle Supplier
                $supplier = Supplier::updateOrCreate(
                    ['phone' => $unitData['supplier_phone']],
                    [
                        'name' => $unitData['supplier_name'],
                        'city' => $unitData['supplier_city'] ?? '',
                        'address' => $unitData['supplier_address'] ?? '',
                    ]
                );

                // Create IMEI
                $imeiRecord = DeviceImei::create([
                    'device_id' => $device->id,
                    'imei' => $unitData['imei'],
                    'status' => 'available'
                ]);

                // Create Purchase Record
                $purchase = Purchase::create([
                    'supplier_id' => $supplier->id,
                    'purchase_date' => $unitData['purchase_date'] ? Carbon::parse($unitData['purchase_date']) : now(),
                    'total_amount' => $unitData['buy_price'] ?? 0,
                    'paid_amount' => $unitData['buy_price'] ?? 0,
                    'due_amount' => 0,
                    'payment_method' => 'cash',
                    'notes' => 'Batch entry'
                ]);

                // Create Purchase Item
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_type' => 'device',
                    'item_id' => $device->id,
                    'imei_id' => $imeiRecord->id,
                    'quantity' => 1,
                    'price' => $unitData['buy_price'] ?? 0,
                    'repair_cost' => $unitData['repair_cost'] ?? 0,
                    'total' => $unitData['buy_price'] ?? 0,
                ]);
            }

            // Update Stock
            $device->update(['stock' => $device->imeis()->where('status', 'available')->count()]);

            DB::commit();
            return redirect()->route('mobiles.index')->with('success', count($request->units) . ' unit(s) added successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error adding device: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $device = Device::with([
            'brand',
            'model',
            'imeis.purchaseItem.purchase.supplier'
        ])->findOrFail($id);

        $brands = Brand::orderBy('name')->get();

        return view('device.edit', compact('device', 'brands'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'brand_id' => 'required',
            'model_name' => 'required|string',
            'storage' => 'required|string',
            'ram' => 'required|string',
            'color' => 'required|string',
            'condition' => 'required|string',
            'units' => 'required|array|min:1',
            'units.*.imei' => 'required|string',
            'units.*.buy_price' => 'required|numeric|min:0',
            'units.*.repair_cost' => 'nullable|numeric|min:0',
            'units.*.supplier_name' => 'required|string',
            'units.*.supplier_phone' => 'required|string',
            'units.*.purchase_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $device = Device::findOrFail($id);

            // 1. Update Global Model/SKU Info
            $model = PhoneModel::firstOrCreate(
                [
                    'brand_id' => $request->brand_id,
                    'name' => $request->model_name
                ]
            );

            $device->update([
                'brand_id' => $request->brand_id,
                'model_id' => $model->id,
                'storage' => $request->storage,
                'ram' => $request->ram,
                'color' => $request->color,
                'condition' => Str::lower($request->condition),
            ]);

            // 2. Update Each Unit (IMEI wise)
            $processedImeiIds = [];

            foreach ($request->units as $unitData) {
                $imeiId = $unitData['id'] ?? null;

                // Update or Create IMEI
                if ($imeiId) {
                    $imeiRecord = DeviceImei::findOrFail($imeiId);
                    $imeiRecord->update(['imei' => $unitData['imei']]);
                } else {
                    $imeiRecord = $device->imeis()
                    ->where('imei', $unitData['imei'])
                    ->first();

                    if (!$imeiRecord) {
                        // IMEI does not exist → create
                        $imeiRecord = $device->imeis()->create([
                            'imei' => $unitData['imei'],
                            'status' => 'available',
                        ]);
                    }else{
                        throw new \Exception(
                            "IMEI {$unitData['imei']} already exists and is already available."
                        );
                    }
                }
                $processedImeiIds[] = $imeiRecord->id;

                // Handle Supplier
                $supplier = Supplier::updateOrCreate(
                    ['phone' => $unitData['supplier_phone']],
                    [
                        'name' => $unitData['supplier_name'],
                        'city' => $unitData['supplier_city'] ?? '',
                        'address' => $unitData['supplier_address'] ?? '',
                    ]
                );

                // Handle Purchase and PurchaseItem
                $purchaseItem = PurchaseItem::where('imei_id', $imeiRecord->id)->first();

                if ($purchaseItem) {
                    $purchase = Purchase::find($purchaseItem->purchase_id);
                    if ($purchase) {
                        $purchase->update([
                            'supplier_id' => $supplier->id,
                            'purchase_date' => $unitData['purchase_date'] ? Carbon::parse($unitData['purchase_date']) : now(),
                            'total_amount' => $unitData['buy_price'],
                            'paid_amount' => $unitData['buy_price'],
                        ]);
                    }
                    $purchaseItem->update([
                        'price' => $unitData['buy_price'],
                        'repair_cost' => $unitData['repair_cost'] ?? 0,
                        'total' => $unitData['buy_price'],
                    ]);
                } else {
                    // Create new purchase record if it didn't exist for this unit
                    $purchase = Purchase::create([
                        'supplier_id' => $supplier->id,
                        'purchase_date' => $unitData['purchase_date'] ? Carbon::parse($unitData['purchase_date']) : now(),
                        'total_amount' => $unitData['buy_price'] ?? 0,
                        'paid_amount' => $unitData['buy_price'] ?? 0,
                        'due_amount' => 0,
                        'payment_method' => 'cash',
                        'notes' => 'Added during device update'
                    ]);

                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'item_type' => 'device',
                        'item_id' => $device->id,
                        'imei_id' => $imeiRecord->id,
                        'quantity' => 1,
                        'price' => $unitData['buy_price'] ?? 0,
                        'repair_cost' => $unitData['repair_cost'] ?? 0,
                        'total' => $unitData['buy_price'] ?? 0,
                    ]);
                }
            }

            // Optional: Handle missing IMEIs (deletion)? 
            // For now let's just update stock based on current available IMEIs for this device
            $device->update(['stock' => $device->imeis()->where('status', 'available')->count()]);

            DB::commit();
            return redirect()->route('mobiles.index')->with('success', 'Device units updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating device: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $device = Device::findOrFail($id);
            // Check if it has sold items?
            // If stock > 0, we are deleting inventory.
            if ($device->invoiceItems()->count() > 0) {
                return back()->with('error', 'Cannot delete device with sales history.');
            }
            $device->delete(); // This might cascade delete IMEIs depending on DB setup

            return redirect()->route('mobiles.index')->with('success', 'Device deleted successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Error deleting device: ' . $e->getMessage());
        }
    }
}
