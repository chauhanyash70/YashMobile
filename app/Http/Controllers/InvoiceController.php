<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Device;
use App\Models\Accessory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('customer')->latest()->get();
        return view('invoice.index', compact('invoices'));
    }

    public function getData(Request $request)
    {
        $columns = [
            0 => 'invoice_no',
            1 => 'invoice_date',
            2 => 'customer_id',
            3 => 'total_amount',
            4 => 'paid_amount',
            5 => 'due_amount',
            6 => 'payment_method',
            7 => 'id'
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column') ?? 0];
        $dir = $request->input('order.0.dir') ?? 'desc';

        $query = Invoice::with('customer')->orderBy($order, $dir);

        $totalData = $query->count();
        $totalFiltered = $totalData;

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query = $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'LIKE', "%{$search}%")
                    ->orWhere('payment_method', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%");
                    });
            });
            $totalFiltered = $query->count();
        }

        $invoices = $query->offset($start)->limit($limit)->get();

        $data = [];
        foreach ($invoices as $invoice) {
            $nested = [];
            $nested['id'] = $invoice->id;
            $nested['invoice_no'] = $invoice->invoice_no;
            $nested['invoice_date'] = Carbon::parse($invoice->invoice_date)->format(config('app.date_format'));
            $nested['customer_name'] = $invoice->customer ? $invoice->customer->name : 'N/A';
            $nested['total_amount'] = $invoice->total_amount;
            $nested['paid_amount'] = $invoice->paid_amount;
            $nested['due_amount'] = $invoice->due_amount;
            $nested['payment_method'] = Str::title($invoice->payment_method);
            $nested['edit_url'] = route('invoice.edit', $invoice->id);
            $nested['pdf_url'] = route('invoice.generatePdf', $invoice->id);
            $nested['delete_url'] = route('invoice.destroy', $invoice->id);
            $nested['show_url'] = route('invoice.show', $invoice->id);
            $nested['actions'] = $invoice->id;

            $data[] = $nested;
        }

        $json_data = [
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
        ];

        return response()->json($json_data);
    }

    public function create()
    {
        // For devices, each IMEI should be a selectable "product"
        $deviceProducts = Device::with([
            'brand',
            'model',
            'imeis' => function ($q) {
                $q->where('status', 'available');
            }
        ])
            ->where('stock', '>', 0)
            ->get()
            ->flatMap(function ($device) {
                return $device->imeis->where('status', 'available')->map(function ($imei) use ($device) {
                    $name = ($device->brand ? $device->brand->name : '') . ' ' . ($device->model ? $device->model->name : '');
                    if ($device->storage)
                        $name .= ' - ' . $device->storage;
                    if ($device->ram)
                        $name .= ' (' . $device->ram . ')';
                    if ($device->color)
                        $name .= ' - ' . $device->color;

                    // Add IMEI to name for clarity in dropdown
                    $displayName = $name . " (IMEI: " . $imei->imei . ")";

                    return (object) [
                        'id' => $device->id,
                        'name' => $displayName,
                        'model_name' => $device->model->name ?? '',
                        'brand_id' => $device->brand_id,
                        'storage' => $device->storage,
                        'ram' => $device->ram,
                        'color' => $device->color,
                        'quantity' => 1, // Individual IMEI has quantity 1
                        'price' => $device->sell_price,
                        'imei_or_serial_number' => $imei->imei,
                        'type' => 'device',
                        'unique_id' => 'imei_' . $imei->id, // Use imei_id as unique identifier
                    ];
                });
            });

        $accessories = Accessory::where('stock', '>', 0)->get()->map(function ($accessory) {
            return (object) [
                'id' => $accessory->id,
                'name' => $accessory->name,
                'quantity' => $accessory->stock,
                'price' => $accessory->sale_price,
                'imei_or_serial_number' => $accessory->sku,
                'type' => 'accessory',
                'unique_id' => 'accessory_' . $accessory->id,
            ];
        });

        $products = $deviceProducts->concat($accessories);
        $brands = \App\Models\Brand::all();

        return view('invoice.create', compact('products', 'brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mobile' => 'required',
            'name' => 'required',
            'invoice_items' => 'required|array|min:1',
            'invoice_items.*.quantity' => 'required|integer|min:1',
            'invoice_items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $customer = Customer::updateOrCreate(
                ['phone' => $request->mobile],
                ['name' => $request->name, 'address' => $request->address]
            );

            $invoiceItems = [];
            $totalAmount = 0;

            foreach ($request->invoice_items as $item) {
                $lineTotal = ($item['price'] * $item['quantity']) - ($item['discount'] ?? 0);
                $totalAmount += $lineTotal;

                $uniqueId = $item['product_id'] ?? null;
                $itemType = 'device';
                $itemId = null;
                $imeiId = null;

                if ($uniqueId) {
                    if (str_starts_with($uniqueId, 'imei_')) {
                        $imeiId = str_replace('imei_', '', $uniqueId);
                        $imeiRecord = \App\Models\DeviceImei::find($imeiId);
                        if ($imeiRecord) {
                            $itemId = $imeiRecord->device_id;
                            $itemType = 'device';
                            $imeiRecord->update(['status' => 'sold']);
                        }
                    } elseif (str_starts_with($uniqueId, 'device_')) {
                        $itemId = str_replace('device_', '', $uniqueId);
                        $itemType = 'device';
                    } elseif (str_starts_with($uniqueId, 'accessory_')) {
                        $itemId = str_replace('accessory_', '', $uniqueId);
                        $itemType = 'accessory';
                    }
                } else {
                    // Manual Entry
                    $itemType = $item['item_type'] ?? 'device';
                    if ($itemType == 'device') {
                        $brandId = $item['brand_id'] ?? \App\Models\Brand::firstOrCreate(['name' => 'Manual Entry'])->id;
                        $model = \App\Models\PhoneModel::firstOrCreate(['name' => $item['name'], 'brand_id' => $brandId]);
                        $device = Device::create([
                            'brand_id' => $brandId,
                            'model_id' => $model->id,
                            'storage' => $item['storage'] ?? null,
                            'ram' => $item['ram'] ?? null,
                            'color' => $item['color'] ?? null,
                            'status' => 'sold',
                            'stock' => 0,
                            'sell_price' => $item['price']
                        ]);
                        $itemId = $device->id;

                        if (!empty($item['imei_or_serial_number'])) {
                            $newImei = \App\Models\DeviceImei::create([
                                'device_id' => $device->id,
                                'imei' => $item['imei_or_serial_number'],
                                'status' => 'sold'
                            ]);
                            $imeiId = $newImei->id;
                        }
                    } else {
                        $accessory = Accessory::create([
                            'name' => $item['name'],
                            'sku' => $item['imei_or_serial_number'] ?? 'MANUAL-' . time(),
                            'sale_price' => $item['price'],
                            'stock' => 0
                        ]);
                        $itemId = $accessory->id;
                    }
                }

                // Associate IMEI if found by text (fallback/barcode scan)
                if (!$imeiId && $itemType == 'device' && !empty($item['imei_or_serial_number'])) {
                    $imeiRecord = \App\Models\DeviceImei::where('imei', $item['imei_or_serial_number'])->first();
                    if ($imeiRecord) {
                        $imeiId = $imeiRecord->id;
                        $imeiRecord->update(['status' => 'sold']);
                        $itemId = $imeiRecord->device_id;
                    }
                }

                $invoiceItems[] = new InvoiceItem([
                    'item_type' => $itemType,
                    'item_id' => $itemId,
                    'imei_id' => $imeiId,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $lineTotal,
                ]);

                // Update stock count
                if ($itemType == 'device' && $itemId) {
                    $dev = Device::find($itemId);
                    if ($dev) {
                        $dev->decrement('stock', $item['quantity']);
                        if ($dev->stock <= 0)
                            $dev->update(['status' => 'sold']);
                    }
                } elseif ($itemType == 'accessory' && $itemId) {
                    Accessory::find($itemId)?->decrement('stock', $item['quantity']);
                }
            }

            $invoice = Invoice::create([
                'invoice_no' => \App\Http\Traits\Traits::getInvoiceNumber(),
                'customer_id' => $customer->id,
                'invoice_date' => Carbon::parse($request->invoice_date)->format('Y-m-d'),
                'total_amount' => $totalAmount,
                'paid_amount' => $totalAmount,
                'due_amount' => 0,
                'payment_method' => $request->payment_method ?? 'Cash',
                'notes' => '',
            ]);

            $invoice->items()->saveMany($invoiceItems);

            DB::commit();
            return redirect()->route('invoice.index')->with('success', 'Invoice created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating invoice: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $invoice = Invoice::with(['customer', 'items.item', 'items.deviceImei'])->findOrFail($id);
        return view('invoice.show', compact('invoice'));
    }

    public function edit($id)
    {
        $invoice = Invoice::with([
            'items.item' => function ($morphTo) {
                $morphTo->morphWith([
                    Device::class => ['model', 'brand'],
                    Accessory::class => ['brand'],
                ]);
            },
            'items.deviceImei',
            'customer'
        ])->findOrFail($id);

        // 1. Get Available Products (Individual IMEIs)
        $availableDeviceIMEIs = Device::with([
            'brand',
            'model',
            'imeis' => function ($q) {
                $q->where('status', 'available');
            }
        ])
            ->where('stock', '>', 0)
            ->get()
            ->flatMap(function ($device) {
                return $device->imeis->where('status', 'available')->map(function ($imei) use ($device) {
                    $name = ($device->brand ? $device->brand->name : '') . ' ' . ($device->model ? $device->model->name : '');
                    if ($device->storage)
                        $name .= ' - ' . $device->storage;
                    if ($device->ram)
                        $name .= ' (' . $device->ram . ')';
                    if ($device->color)
                        $name .= ' - ' . $device->color;

                    return (object) [
                        'id' => $device->id,
                        'name' => $name . " (IMEI: " . $imei->imei . ")",
                        'quantity' => 1,
                        'price' => $device->sell_price,
                        'imei_or_serial_number' => $imei->imei,
                        'type' => 'device',
                        'unique_id' => 'imei_' . $imei->id,
                    ];
                });
            });

        $availableAccessories = Accessory::where('stock', '>', 0)->get()->map(function ($accessory) {
            return (object) [
                'id' => $accessory->id,
                'name' => $accessory->name,
                'quantity' => $accessory->stock,
                'price' => $accessory->sale_price,
                'imei_or_serial_number' => $accessory->sku,
                'type' => 'accessory',
                'unique_id' => 'accessory_' . $accessory->id,
            ];
        });

        // 2. Add Currently Sold IMEIs/Accessories of this invoice to products so they show as selected
        $currentInvoiceProducts = $invoice->items->map(function ($item) {
            $product = $item->item;
            if (!$product)
                return null;

            if ($item->item_type == 'device') {
                $name = ($product->brand ? $product->brand->name : '') . ' ' . ($product->model ? $product->model->name : '');
                if ($product->storage)
                    $name .= ' - ' . $product->storage;
                if ($product->ram)
                    $name .= ' (' . $product->ram . ')';
                if ($product->color)
                    $name .= ' - ' . $product->color;

                $imei = $item->deviceImei ? $item->deviceImei->imei : ($item->imei_or_serial_number ?? '');

                return (object) [
                    'id' => $product->id,
                    'name' => $name . " (IMEI: " . $imei . ")",
                    'quantity' => $item->quantity, // Usually 1 for IMEI
                    'price' => $item->price,
                    'imei_or_serial_number' => $imei,
                    'type' => 'device',
                    'unique_id' => $item->imei_id ? 'imei_' . $item->imei_id : 'device_' . $product->id,
                ];
            } else {
                return (object) [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $product->stock + $item->quantity,
                    'price' => $item->price,
                    'imei_or_serial_number' => $product->sku,
                    'type' => 'accessory',
                    'unique_id' => 'accessory_' . $product->id,
                ];
            }
        })->filter();

        $products = $availableDeviceIMEIs->concat($availableAccessories)->concat($currentInvoiceProducts)->unique('unique_id');
        $brands = \App\Models\Brand::all();

        return view('invoice.edit', compact('invoice', 'products', 'brands'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'mobile' => 'required',
            'name' => 'required',
            'invoice_items' => 'required|array|min:1',
            'invoice_items.*.quantity' => 'required|integer|min:1',
            'invoice_items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::findOrFail($id);

            // 1. REVERT Stock for Existing Items
            foreach ($invoice->items as $item) {
                if ($item->item_type == 'device') {
                    $device = Device::find($item->item_id);
                    if ($device) {
                        $device->increment('stock', $item->quantity);
                        $device->update(['status' => 'in_stock']);
                    }
                    if ($item->imei_id) {
                        \App\Models\DeviceImei::where('id', $item->imei_id)->update(['status' => 'available']);
                    }
                } elseif ($item->item_type == 'accessory') {
                    $accessory = Accessory::find($item->item_id);
                    if ($accessory) {
                        $accessory->increment('stock', $item->quantity);
                    }
                }
                $item->delete();
            }

            // 2. Process NEW Items
            $customer = Customer::updateOrCreate(
                ['phone' => $request->mobile],
                ['name' => $request->name, 'address' => $request->address]
            );

            $invoiceItems = [];
            $totalAmount = 0;

            foreach ($request->invoice_items as $item) {
                $lineTotal = ($item['price'] * $item['quantity']) - ($item['discount'] ?? 0);
                $totalAmount += $lineTotal;

                $uniqueId = $item['product_id'] ?? null;
                $itemType = 'device';
                $itemId = null;
                $imeiId = null;

                if ($uniqueId) {
                    if (str_starts_with($uniqueId, 'imei_')) {
                        $imeiId = str_replace('imei_', '', $uniqueId);
                        $imeiRecord = \App\Models\DeviceImei::find($imeiId);
                        if ($imeiRecord) {
                            $itemId = $imeiRecord->device_id;
                            $itemType = 'device';
                            $imeiRecord->update(['status' => 'sold']);
                        }
                    } elseif (str_starts_with($uniqueId, 'device_')) {
                        $itemId = str_replace('device_', '', $uniqueId);
                        $itemType = 'device';
                    } elseif (str_starts_with($uniqueId, 'accessory_')) {
                        $itemId = str_replace('accessory_', '', $uniqueId);
                        $itemType = 'accessory';
                    }
                } else {
                    // Manual Entry Logic (unchanged derived logic)
                    $itemType = $item['item_type'] ?? 'device';
                    if ($itemType == 'device') {
                        $brandId = $item['brand_id'] ?? \App\Models\Brand::firstOrCreate(['name' => 'Manual Entry'])->id;
                        $model = \App\Models\PhoneModel::firstOrCreate(['name' => $item['name'], 'brand_id' => $brandId]);
                        $device = Device::create([
                            'brand_id' => $brandId,
                            'model_id' => $model->id,
                            'storage' => $item['storage'] ?? null,
                            'ram' => $item['ram'] ?? null,
                            'color' => $item['color'] ?? null,
                            'status' => 'sold',
                            'stock' => 0,
                            'sell_price' => $item['price']
                        ]);
                        $itemId = $device->id;

                        if (!empty($item['imei_or_serial_number'])) {
                            $newImei = \App\Models\DeviceImei::create([
                                'device_id' => $device->id,
                                'imei' => $item['imei_or_serial_number'],
                                'status' => 'sold'
                            ]);
                            $imeiId = $newImei->id;
                        }
                    } else {
                        $accessory = Accessory::create([
                            'name' => $item['name'],
                            'sku' => $item['imei_or_serial_number'] ?? 'MANUAL-' . time(),
                            'sale_price' => $item['price'],
                            'stock' => 0
                        ]);
                        $itemId = $accessory->id;
                    }
                }

                // If not set yet (e.g. from device_ selection or barcode scan)
                if (!$imeiId && $itemType == 'device' && !empty($item['imei_or_serial_number'])) {
                    $imeiRecord = \App\Models\DeviceImei::where('imei', $item['imei_or_serial_number'])->first();
                    if ($imeiRecord) {
                        $imeiId = $imeiRecord->id;
                        $imeiRecord->update(['status' => 'sold']);
                        $itemId = $imeiRecord->device_id;
                    }
                }

                $invoiceItems[] = new InvoiceItem([
                    'item_type' => $itemType,
                    'item_id' => $itemId,
                    'imei_id' => $imeiId,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $lineTotal,
                ]);

                // Update stock count
                if ($itemType == 'device' && $itemId) {
                    $dev = Device::find($itemId);
                    if ($dev) {
                        $dev->decrement('stock', $item['quantity']);
                        if ($dev->stock <= 0)
                            $dev->update(['status' => 'sold']);
                    }
                } elseif ($itemType == 'accessory' && $itemId) {
                    Accessory::find($itemId)?->decrement('stock', $item['quantity']);
                }
            }

            $invoice->update([
                'customer_id' => $customer->id,
                'total_amount' => $totalAmount,
                'paid_amount' => $totalAmount, // Assuming paid
                'due_amount' => 0,
                'payment_method' => $request->payment_method ?? 'Cash',
                'invoice_date' => $request->invoice_date,
            ]);

            $invoice->items()->saveMany($invoiceItems);

            DB::commit();
            return redirect()->route('invoice.index')->with('success', 'Invoice updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating invoice: ' . $e->getMessage())->withInput();
        }
    }

    public function generateInvoicePdf($id)
    {
        $invoice = Invoice::with([
            'items.item' => function ($morphTo) {
                $morphTo->morphWith([
                    Device::class => ['model', 'brand'],
                    Accessory::class => ['brand'],
                ]);
            },
            'items.deviceImei',
            'customer'
        ])->findOrFail($id);
        $pdf = Pdf::loadView('invoice.pdf', compact('invoice'));
        return $pdf->stream('invoice_' . $invoice->invoice_no . '.pdf');
    }

    public function destroy($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            // Ideally revert stock changes here
            $invoice->delete();
            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting invoice: ' . $e->getMessage(),
            ]);
        }
    }
    public function getProductByBarcode($barcode)
    {
        $deviceImei = \App\Models\DeviceImei::with(['device.brand', 'device.model'])->where('imei', $barcode);

        if ($deviceImei->count() > 0) {
            $deviceImei = $deviceImei->where('status', 'available')->first();
            if (!$deviceImei) {
                return response()->json(['status' => false, 'message' => 'This IMEI is already marked as sold']);
            }
            $device = $deviceImei->device;
            if ($device) {
                $name = ($device->brand ? $device->brand->name : '') . ' ' . ($device->model ? $device->model->name : '');
                if ($device->storage)
                    $name .= ' - ' . $device->storage;
                if ($device->ram)
                    $name .= ' (' . $device->ram . ')';
                if ($device->color)
                    $name .= ' - ' . $device->color;

                $device->name = $name . " (IMEI: " . $barcode . ")";
                $device->quantity = 1;
                $device->price = $device->sell_price;
                $device->imei_or_serial_number = $barcode;
                $device->unique_id = 'imei_' . $deviceImei->id;
                $device->type = 'device';

                return response()->json(['status' => true, 'product' => $device]);
            }
        }

        $accessory = Accessory::where('sku', $barcode)->where('stock', '>', 0)->first();
        if ($accessory) {
            $accessory->quantity = $accessory->stock;
            $accessory->price = $accessory->sale_price;
            $accessory->imei_or_serial_number = $accessory->sku;
            $accessory->unique_id = 'accessory_' . $accessory->id;
            $accessory->type = 'accessory';
            return response()->json(['status' => true, 'product' => $accessory]);
        }

        return response()->json(['status' => false, 'message' => 'Product not found']);
    }

    public function getCustomer(Request $request)
    {
        $customer = Customer::where('phone', $request->phone)->first();
        if ($customer) {
            return response()->json(['status' => true, 'customer' => $customer]);
        }
        return response()->json(['status' => false, 'message' => 'Customer not found']);
    }
}
