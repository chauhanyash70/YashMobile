<?php

namespace App\Http\Controllers;

use App\Http\Traits\Traits;
use App\Models\Accessory;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Mobile;
use App\Models\MobileModel;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    use Traits;

    public function index()
    {
        return view('invoice.index', [
            'header_title' => 'Invoices',
            'tagline' => 'View and manage sales invoices and customer payments.',
        ]);
    }

    public function getData(Request $request)
    {
        $columns = [
            0 => 'invoice_no',
            1 => 'invoice_date',
            2 => 'customer_id',
            3 => 'subtotal',
            4 => 'grand_total',
            5 => 'paid_amount',
            6 => 'payment_method',
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        $orderColumnIndex = $request->input('order.0.column');
        $order = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'invoice_no';
        $dir = $request->input('order.0.dir') ?? 'desc';

        $query = Invoice::with('customer');

        $totalData = $query->count();

        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->input('to_date'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        if (! empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                    });
            });
        }

        $totalFiltered = $query->count();

        $invoices = $query->orderBy($order, $dir)->offset($start)->limit($limit)->get();

        $data = [];
        foreach ($invoices as $invoice) {
            $nested = [];
            $nested['id'] = $invoice->id;
            $nested['invoice_no'] = $invoice->invoice_no.' <span class="badge bg-soft-'.($invoice->invoice_type == 'sell' ? 'info' : 'warning').' text-'.($invoice->invoice_type == 'sell' ? 'info' : 'warning').' fs-10">'.ucfirst($invoice->invoice_type).'</span>';
            $nested['invoice_date'] = Carbon::parse($invoice->invoice_date)->format('d M, Y');
            $nested['customer_name'] = $invoice->customer ? $invoice->customer->name : 'Walking Customer';
            $nested['total_amount'] = $invoice->grand_total;
            $nested['subtotal'] = $invoice->subtotal;
            $nested['tax_amount'] = $invoice->tax_amount;
            $nested['paid_amount'] = $invoice->paid_amount;
            $payment_method = $invoice->payment_method == 'bajaj_finance' ? 'Bajaj Finance' : ucfirst($invoice->payment_method);
            if ($invoice->bajaj_approval_number) {
                $payment_method .= ' <br><span class="badge bg-soft-primary border border-primary text-primary fs-10">'.$invoice->bajaj_approval_number.'</span>';
            }
            $nested['payment_method'] = $payment_method;
            $nested['edit_url'] = route('invoice.edit', $invoice->id);
            $nested['pdf_url'] = route('invoice.generatePdf', $invoice->id);
            $nested['delete_url'] = route('invoice.destroy', $invoice->id);
            $nested['show_url'] = route('invoice.show', $invoice->id);
            $nested['actions'] = $invoice->id;

            $data[] = $nested;
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => intval($totalData),
            'recordsFiltered' => intval($totalFiltered),
            'data' => $data,
        ]);
    }

    public function create()
    {
        $products = Mobile::where('status', 'in_stock')
            ->with(['brand', 'model'])
            ->get()
            ->toBase()
            ->map(function ($m) {
                $name = ($m->brand->name ?? '').' '.($m->model->name ?? '');
                $name .= ' ('.$m->storage.'/'.$m->ram.')';

                return (object) [
                    'id' => $m->id,
                    'name' => $name.' [HSN: '.$m->hsn_number.']',
                    'model_name' => $m->model->name ?? '',
                    'brand_id' => $m->brand_id,
                    'storage' => $m->storage,
                    'ram' => $m->ram,
                    'color' => $m->color,
                    'battery_health' => $m->battery_health,
                    'quantity' => 1,
                    'price' => 0, // Will be filled by user
                    'hsn_number' => $m->hsn_number,
                    'type' => 'device',
                    'unique_id' => 'mobile_'.$m->id,
                ];
            });

        $accessories = Accessory::where('stock', '>', 0)
            ->with(['brand'])
            ->get()
            ->toBase()
            ->map(function ($a) {
                $name = ($a->brand->name ?? '').' '.($a->name ?? '');
                $name .= $a->model ? ' ('.$a->model.')' : '';

                return (object) [
                    'id' => $a->id,
                    'name' => $name.' [HSN: '.$a->hsn.']',
                    'model_name' => $a->model ?? '',
                    'brand_id' => $a->brand_id,
                    'storage' => '',
                    'ram' => '',
                    'color' => $a->color,
                    'battery_health' => '',
                    'quantity' => 1,
                    'price' => 0, // Will be filled by user
                    'hsn_number' => $a->hsn,
                    'type' => 'accessory',
                    'unique_id' => 'accessory_'.$a->id,
                ];
            });

        $products = $products->merge($accessories);

        $brands = Brand::orderBy('name')->get();

        return view('invoice.create', compact('products', 'brands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mobile' => 'required',
            'name' => 'required',
            'invoice_items' => 'required|array|min:1',
            'invoice_items.*.price' => 'required|numeric|gt:0',
            'invoice_items.*.item_type' => 'nullable|required_without:invoice_items.*.product_id',
            'invoice_items.*.brand_id' => 'nullable|required_without:invoice_items.*.product_id',
            'invoice_items.*.name' => 'nullable|required_without:invoice_items.*.product_id',
        ]);

        try {
            DB::beginTransaction();

            $customer = Customer::updateOrCreate(
                ['phone' => $request->mobile],
                ['name' => $request->name, 'address' => $request->address]
            );

            $totalAmount = 0;
            $totalDiscount = 0;
            $totalTax = 0;
            $itemsToCreate = [];
            $buyInvoices = []; // To group manual items by supplier for buy invoices

            foreach ($request->invoice_items as $item) {
                $qty = (int) ($item['qty'] ?? $item['quantity'] ?? 1);
                $price = (float) ($item['price'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);
                $lineTotal = ($price * $qty) - $discount;
                $buyPrice = (float) ($item['buy_price'] ?? 0);

                if (Str::startsWith($item['product_id'] ?? '', 'mobile_')) {
                    $mobileId = str_replace('mobile_', '', $item['product_id']);
                    $mobile = Mobile::find($mobileId);

                    if (! $mobile || $mobile->status != 'in_stock') {
                        throw new Exception("Mobile unit with ID $mobileId is not available.");
                    }

                    $totalAmount += $lineTotal;
                    $totalDiscount += $discount;

                    $itemsToCreate[] = [
                        'type' => 'mobile',
                        'item' => $mobile,
                        'price' => $price,
                        'discount' => $discount,
                        'qty' => 1,
                        'total' => $lineTotal,
                    ];
                } elseif (Str::startsWith($item['product_id'] ?? '', 'accessory_')) {
                    $accessoryId = str_replace('accessory_', '', $item['product_id']);
                    $accessory = Accessory::find($accessoryId);

                    if (! $accessory || $accessory->stock <= 0) {
                        throw new Exception("Accessory unit with ID $accessoryId is out of stock.");
                    }

                    $totalAmount += $lineTotal;
                    $totalDiscount += $discount;

                    $itemsToCreate[] = [
                        'type' => 'accessory',
                        'item' => $accessory,
                        'qty' => $qty,
                        'price' => $price,
                        'discount' => $discount,
                        'total' => $lineTotal,
                    ];
                } elseif (empty($item['product_id'])) {
                    // Manual entry
                    $itemType = $item['item_type'] ?? 'device';
                    $brandId = $item['brand_id'];
                    $name = $item['name'];

                    if ($itemType == 'device') {
                        $model = MobileModel::firstOrCreate([
                            'brand_id' => $brandId,
                            'name' => $name,
                            'user_id' => auth()->id(),
                        ]);

                        $mobile = Mobile::create([
                            'user_id' => auth()->id(),
                            'brand_id' => $brandId,
                            'model_id' => $model->id,
                            'hsn_number' => $item['hsn_number'] ?? null,
                            'color' => $item['color'] ?? null,
                            'storage' => $item['storage'] ?? null,
                            'ram' => $item['ram'] ?? null,
                            'battery_health' => $item['battery_health'] ?? null,
                            'status' => 'sold',
                            'buy_price' => $buyPrice,
                        ]);

                        if (! empty($item['supplier_phone'])) {
                            $supplier = Customer::updateOrCreate(
                                ['phone' => $item['supplier_phone']],
                                ['name' => $item['supplier_name'] ?? 'Supplier', 'address' => $item['supplier_address'] ?? null]
                            );

                            // Group for Buy Invoice
                            $supplierId = $supplier->id;
                            if (! isset($buyInvoices[$supplierId])) {
                                $buyInvoices[$supplierId] = [
                                    'customer' => $supplier,
                                    'items' => [],
                                ];
                            }
                            $buyInvoices[$supplierId]['items'][] = [
                                'type' => 'mobile',
                                'id' => $mobile->id,
                                'buy_price' => $buyPrice,
                                'qty' => 1,
                                'hsn' => $mobile->hsn_number,
                            ];
                        }

                        $totalAmount += $lineTotal;
                        $totalDiscount += $discount;

                        $itemsToCreate[] = [
                            'type' => 'mobile',
                            'item' => $mobile,
                            'price' => $price,
                            'discount' => $discount,
                            'qty' => 1,
                            'total' => $lineTotal,
                        ];
                    } else {
                        $accessory = Accessory::create([
                            'user_id' => auth()->id(),
                            'brand_id' => $brandId,
                            'name' => $name,
                            'hsn' => $item['hsn_number'] ?? null,
                            'color' => $item['color'] ?? null,
                            'stock' => 0,
                            'purchase_price' => $buyPrice,
                        ]);

                        if (! empty($item['supplier_phone'])) {
                            $supplier = Customer::updateOrCreate(
                                ['phone' => $item['supplier_phone']],
                                ['name' => $item['supplier_name'] ?? 'Supplier', 'address' => $item['supplier_address'] ?? null]
                            );

                            // Group for Buy Invoice
                            $supplierId = $supplier->id;
                            if (! isset($buyInvoices[$supplierId])) {
                                $buyInvoices[$supplierId] = [
                                    'customer' => $supplier,
                                    'items' => [],
                                ];
                            }
                            $buyInvoices[$supplierId]['items'][] = [
                                'type' => 'accessory',
                                'id' => $accessory->id,
                                'buy_price' => $buyPrice,
                                'qty' => $qty,
                                'hsn' => $accessory->hsn,
                            ];
                        }

                        $totalAmount += $lineTotal;
                        $totalDiscount += $discount;

                        $itemsToCreate[] = [
                            'type' => 'accessory',
                            'item' => $accessory,
                            'qty' => $qty,
                            'price' => $price,
                            'discount' => $discount,
                            'total' => $lineTotal,
                        ];
                    }
                }
            }

            if (empty($itemsToCreate)) {
                throw new Exception('At least one valid item is required.');
            }

            // Create Buy Invoices for Manual Entries (Supplier Wise)
            foreach ($buyInvoices as $supplierId => $data) {
                $supplier = $data['customer'];
                $buyItems = $data['items'];
                $buyTotal = 0;
                foreach ($buyItems as $bi) {
                    $buyTotal += ($bi['buy_price'] * $bi['qty']);
                }

                $buyInvoice = Invoice::create([
                    'user_id' => auth()->id(),
                    'customer_id' => $supplier->id,
                    'invoice_no' => $this->getInvoiceNumber(),
                    'invoice_date' => $request->invoice_date ?: date('Y-m-d'),
                    'invoice_type' => 'buy',
                    'subtotal' => $buyTotal,
                    'grand_total' => $buyTotal,
                    'paid_amount' => $buyTotal,
                    'payment_status' => 'paid',
                    'payment_method' => 'cash',
                ]);

                foreach ($buyItems as $bi) {
                    $transaction = Transaction::create([
                        'user_id' => auth()->id(),
                        'mobile_id' => $bi['type'] == 'mobile' ? $bi['id'] : null,
                        'accessory_id' => $bi['type'] == 'accessory' ? $bi['id'] : null,
                        'customer_id' => $supplier->id,
                        'transaction_type' => 'buy',
                        'price' => $bi['buy_price'],
                        'transaction_date' => $buyInvoice->invoice_date,
                        'invoice_no' => $buyInvoice->invoice_no,
                    ]);

                    // Create Invoice Item for Buy Invoice
                    $buyInvoice->items()->create([
                        'user_id' => auth()->id(),
                        'mobile_id' => $bi['type'] == 'mobile' ? $bi['id'] : null,
                        'accessory_id' => $bi['type'] == 'accessory' ? $bi['id'] : null,
                        'transaction_id' => $transaction->id,
                        'qty' => $bi['qty'],
                        'price' => $bi['buy_price'],
                        'total' => $bi['buy_price'] * $bi['qty'],
                    ]);
                }
            }

            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'invoice_no' => $this->getInvoiceNumber(),
                'invoice_date' => $request->invoice_date ?: date('Y-m-d'),
                'invoice_type' => 'sell',
                'subtotal' => $totalAmount + $totalDiscount,
                'discount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'grand_total' => $totalAmount,
                'paid_amount' => $totalAmount,
                'due_amount' => 0,
                'payment_status' => 'paid',
                'payment_method' => $payment_method = strtolower($request->payment_method ?: 'cash'),
                'bajaj_approval_number' => $payment_method == 'bajaj_finance' ? $request->bajaj_approval_number : null,
            ]);

            foreach ($itemsToCreate as $it) {
                if ($it['type'] == 'mobile') {
                    $mobile = $it['item'];

                    // Create Transaction
                    $transaction = Transaction::create([
                        'mobile_id' => $mobile->id,
                        'customer_id' => $customer->id,
                        'transaction_type' => 'sell',
                        'price' => $it['total'],
                        'transaction_date' => $invoice->invoice_date,
                        'invoice_no' => $invoice->invoice_no,
                        'payment_method' => $invoice->payment_method,
                        'bajaj_approval_number' => $invoice->bajaj_approval_number,
                    ]);

                    // Create Invoice Item
                    $invoice->items()->create([
                        'mobile_id' => $mobile->id,
                        'transaction_id' => $transaction->id,
                        'qty' => 1,
                        'price' => $it['price'],
                        'discount' => $it['discount'] ?? 0,
                        'total' => $it['total'],
                    ]);

                    // Update Mobile Status
                    $mobile->update(['status' => 'sold']);
                } elseif ($it['type'] == 'accessory') {
                    $accessory = $it['item'];

                    $qty = $it['qty'] ?? 1;
                    // Create Transaction
                    $transaction = Transaction::create([
                        'accessory_id' => $accessory->id,
                        'customer_id' => $customer->id,
                        'transaction_type' => 'sell',
                        'price' => $it['total'],
                        'transaction_date' => $invoice->invoice_date,
                        'invoice_no' => $invoice->invoice_no,
                        'payment_method' => $invoice->payment_method,
                        'bajaj_approval_number' => $invoice->bajaj_approval_number,
                    ]);

                    // Create Invoice Item
                    $invoice->items()->create([
                        'accessory_id' => $accessory->id,
                        'transaction_id' => $transaction->id,
                        'qty' => $qty,
                        'price' => $it['price'],
                        'discount' => $it['discount'] ?? 0,
                        'total' => $it['total'],
                    ]);

                    // Update Accessory Stock
                    $accessory->decrement('stock', $qty);
                }
            }

            $invoice->recalculateTotals();

            DB::commit();

            return redirect()->route('invoice.index')->with('success', 'Invoice created successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error creating invoice: '.$e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $invoice = Invoice::with(['customer', 'items.mobile.brand', 'items.mobile.model', 'items.accessory.brand'])->findOrFail($id);

        $currentMobileIds = $invoice->items->pluck('mobile_id')->filter();
        $currentAccessoryIds = $invoice->items->pluck('accessory_id')->filter();

        $products = Mobile::where(function ($q) use ($currentMobileIds) {
            $q->where('status', 'in_stock')
                ->orWhereIn('id', $currentMobileIds);
        })
            ->with(['brand', 'model'])
            ->get()
            ->toBase()
            ->map(function ($m) {
                $name = ($m->brand->name ?? '').' '.($m->model->name ?? '');
                $name .= ' ('.$m->storage.'/'.$m->ram.')';

                return (object) [
                    'id' => $m->id,
                    'name' => $name.' [HSN: '.$m->hsn_number.']',
                    'model_name' => $m->model->name ?? '',
                    'brand_id' => $m->brand_id,
                    'storage' => $m->storage,
                    'ram' => $m->ram,
                    'color' => $m->color,
                    'battery_health' => $m->battery_health,
                    'quantity' => 1,
                    'price' => 0,
                    'hsn_number' => $m->hsn_number,
                    'type' => 'device',
                    'unique_id' => 'mobile_'.$m->id,
                ];
            });

        $accessories = Accessory::where(function ($q) use ($currentAccessoryIds) {
            $q->where('stock', '>', 0)
                ->orWhereIn('id', $currentAccessoryIds);
        })
            ->with(['brand'])
            ->get()
            ->toBase()
            ->map(function ($a) {
                $name = ($a->brand->name ?? '').' '.($a->name ?? '');
                $name .= $a->model ? ' ('.$a->model.')' : '';

                return (object) [
                    'id' => $a->id,
                    'name' => $name.' [HSN: '.$a->hsn.']',
                    'model_name' => $a->model ?? '',
                    'brand_id' => $a->brand_id,
                    'storage' => '',
                    'ram' => '',
                    'color' => $a->color,
                    'battery_health' => '',
                    'quantity' => 1,
                    'price' => 0,
                    'hsn_number' => $a->hsn,
                    'type' => 'accessory',
                    'unique_id' => 'accessory_'.$a->id,
                ];
            });

        $products = $products->merge($accessories);

        $brands = Brand::orderBy('name')->get();

        return view('invoice.edit', compact('invoice', 'products', 'brands'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'mobile' => 'required',
            'name' => 'required',
            'invoice_items' => 'required|array|min:1',
            'invoice_items.*.price' => 'required|numeric|gt:0',
            'invoice_items.*.item_type' => 'nullable|required_without:invoice_items.*.product_id',
            'invoice_items.*.brand_id' => 'nullable|required_without:invoice_items.*.product_id',
            'invoice_items.*.name' => 'nullable|required_without:invoice_items.*.product_id',
        ]);

        try {
            DB::beginTransaction();

            $invoice = Invoice::with(['items.mobile', 'items.accessory'])->findOrFail($id);

            // 1. Revert old items status/stock
            foreach ($invoice->items as $item) {
                if ($item->mobile) {
                    $item->mobile->update(['status' => $invoice->invoice_type === 'buy' ? 'sold' : 'in_stock']);
                }
                if ($item->accessory) {
                    if ($invoice->invoice_type === 'buy') {
                        $item->accessory->decrement('stock', $item->qty ?? 1);
                    } else {
                        $item->accessory->increment('stock', $item->qty ?? 1);
                    }
                }
            }

            // 2. Delete old items and associated transactions
            $invoice->items()->delete();
            Transaction::where('invoice_no', $invoice->invoice_no)->delete();

            // 3. Handle Customer
            $customer = Customer::updateOrCreate(
                ['phone' => $request->mobile],
                ['name' => $request->name, 'address' => $request->address]
            );

            $totalAmount = 0;
            $totalDiscount = 0;
            $totalTax = 0;
            $itemsToCreate = [];

            // 4. Process new items (re-applying logic from store)
            foreach ($request->invoice_items as $item) {
                $qty = (int) ($item['qty'] ?? $item['quantity'] ?? 1);
                $price = (float) ($item['price'] ?? 0);
                $discount = (float) ($item['discount'] ?? 0);
                $lineTotal = ($price * $qty) - $discount;
                $buyPrice = (float) ($item['buy_price'] ?? 0);

                if (Str::startsWith($item['product_id'] ?? '', 'mobile_')) {
                    $mobileId = str_replace('mobile_', '', $item['product_id']);
                    $mobile = Mobile::find($mobileId);

                    if (! $mobile || ($mobile->status != 'in_stock' && ! $invoice->items->contains('mobile_id', $mobile->id))) {
                        throw new Exception("Mobile unit with ID $mobileId is not available.");
                    }

                    $totalAmount += $lineTotal;
                    $totalDiscount += $discount;

                    $itemsToCreate[] = [
                        'type' => 'mobile',
                        'item' => $mobile,
                        'price' => $price,
                        'discount' => $discount,
                        'qty' => 1,
                        'total' => $lineTotal,
                    ];
                } elseif (Str::startsWith($item['product_id'] ?? '', 'accessory_')) {
                    $accessoryId = str_replace('accessory_', '', $item['product_id']);
                    $accessory = Accessory::find($accessoryId);

                    if (! $accessory || ($accessory->stock <= 0 && ! $invoice->items->contains('accessory_id', $accessory->id))) {
                        throw new Exception("Accessory unit with ID $accessoryId is out of stock.");
                    }

                    $totalAmount += $lineTotal;
                    $totalDiscount += $discount;

                    $itemsToCreate[] = [
                        'type' => 'accessory',
                        'item' => $accessory,
                        'qty' => $qty,
                        'price' => $price,
                        'discount' => $discount,
                        'total' => $lineTotal,
                    ];
                } elseif (empty($item['product_id'])) {
                    // Manual entry in Update
                    $itemType = $item['item_type'] ?? 'device';
                    $brandId = $item['brand_id'];
                    $name = $item['name'];

                    if ($itemType == 'device') {
                        $model = MobileModel::firstOrCreate([
                            'brand_id' => $brandId,
                            'name' => $name,
                            'user_id' => auth()->id(),
                        ]);

                        $mobile = Mobile::create([
                            'user_id' => auth()->id(),
                            'brand_id' => $brandId,
                            'model_id' => $model->id,
                            'hsn_number' => $item['hsn_number'] ?? null,
                            'color' => $item['color'] ?? null,
                            'storage' => $item['storage'] ?? null,
                            'ram' => $item['ram'] ?? null,
                            'battery_health' => $item['battery_health'] ?? null,
                            'status' => 'sold',
                        ]);

                        $totalAmount += $lineTotal;
                        $totalDiscount += $discount;

                        $itemsToCreate[] = [
                            'type' => 'mobile',
                            'item' => $mobile,
                            'price' => $price,
                            'discount' => $discount,
                            'qty' => 1,
                            'total' => $lineTotal,
                        ];
                    } else {
                        $accessory = Accessory::create([
                            'user_id' => auth()->id(),
                            'brand_id' => $brandId,
                            'name' => $name,
                            'hsn' => $item['hsn_number'] ?? null,
                            'color' => $item['color'] ?? null,
                            'stock' => 0,
                            'purchase_price' => $buyPrice,
                        ]);

                        $totalAmount += $lineTotal;
                        $totalDiscount += $discount;

                        $itemsToCreate[] = [
                            'type' => 'accessory',
                            'item' => $accessory,
                            'qty' => $qty,
                            'price' => $price,
                            'discount' => $discount,
                            'total' => $lineTotal,
                        ];
                    }
                }
            }

            if (empty($itemsToCreate)) {
                throw new Exception('At least one valid item is required.');
            }

            // 5. Update Invoice Main Record
            $invoice->update([
                'customer_id' => $customer->id,
                'invoice_date' => $request->invoice_date ?: $invoice->invoice_date,
                'subtotal' => $totalAmount + $totalDiscount,
                'discount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'grand_total' => $totalAmount,
                'paid_amount' => $totalAmount,
                'due_amount' => 0,
                'payment_status' => 'paid',
                'payment_method' => $payment_method = strtolower($request->payment_method ?: $invoice->payment_method),
                'bajaj_approval_number' => $payment_method == 'bajaj_finance' ? $request->bajaj_approval_number : null,
            ]);

            // 6. Re-create new items and transactions
            foreach ($itemsToCreate as $it) {
                if ($it['type'] == 'mobile') {
                    $mobile = $it['item'];

                    $transaction = Transaction::create([
                        'mobile_id' => $mobile->id,
                        'customer_id' => $customer->id,
                        'transaction_type' => $invoice->invoice_type === 'buy' ? 'buy' : 'sell',
                        'price' => $it['total'],
                        'transaction_date' => $invoice->invoice_date,
                        'invoice_no' => $invoice->invoice_no,
                        'payment_method' => $invoice->payment_method,
                        'bajaj_approval_number' => $invoice->bajaj_approval_number,
                    ]);

                    $invoice->items()->create([
                        'mobile_id' => $mobile->id,
                        'transaction_id' => $transaction->id,
                        'qty' => 1,
                        'price' => $it['price'],
                        'discount' => $it['discount'] ?? 0,
                        'total' => $it['total'],
                    ]);

                    $mobile->update(['status' => $invoice->invoice_type === 'buy' ? 'in_stock' : 'sold']);
                } elseif ($it['type'] == 'accessory') {
                    $accessory = $it['item'];
                    $qty = $it['qty'] ?? 1;

                    $transaction = Transaction::create([
                        'accessory_id' => $accessory->id,
                        'customer_id' => $customer->id,
                        'transaction_type' => $invoice->invoice_type === 'buy' ? 'buy' : 'sell',
                        'price' => $it['total'],
                        'transaction_date' => $invoice->invoice_date,
                        'invoice_no' => $invoice->invoice_no,
                        'payment_method' => $invoice->payment_method,
                        'bajaj_approval_number' => $invoice->bajaj_approval_number,
                    ]);

                    $invoice->items()->create([
                        'accessory_id' => $accessory->id,
                        'transaction_id' => $transaction->id,
                        'qty' => $qty,
                        'price' => $it['price'],
                        'discount' => $it['discount'] ?? 0,
                        'total' => $it['total'],
                    ]);

                    if ($invoice->invoice_type === 'buy') {
                        $accessory->increment('stock', $qty);
                    } else {
                        $accessory->decrement('stock', $qty);
                    }
                }
            }

            $invoice->recalculateTotals();
            DB::commit();

            return redirect()->route('invoice.index')->with('success', 'Invoice updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error updating invoice: '.$e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $invoice = Invoice::with(['customer', 'items.mobile.brand', 'items.mobile.model', 'items.accessory.brand'])->findOrFail($id);

        return view('invoice.show', compact('invoice'))->with([
            'header_title' => 'Invoice #'.$invoice->invoice_no,
            'tagline' => 'Detailed breakdown of the items and payment information.',
        ]);
    }
    /**
     * Get the path to the qpdf executable if available.
     *
     * @return string|null
     */
    protected function getQpdfPath(): ?string
    {
        if (!function_exists('exec')) {
            return null;
        }

        // 1. Check if qpdf is in the system PATH
        $command = PHP_OS_FAMILY === 'Windows' ? 'where qpdf' : 'which qpdf';
        exec($command, $output, $returnVar);

        if ($returnVar === 0 && !empty($output)) {
            $path = trim(is_array($output) ? $output[0] : $output);
            if (!empty($path)) {
                return $path;
            }
        }

        // 2. Fallback check for common Windows installation paths
        if (PHP_OS_FAMILY === 'Windows') {
            $commonPatterns = [
                'C:\\Program Files\\qpdf*\\bin\\qpdf.exe',
                'C:\\Program Files (x86)\\qpdf*\\bin\\qpdf.exe',
            ];
            foreach ($commonPatterns as $pattern) {
                $matches = glob($pattern);
                if (!empty($matches)) {
                    return $matches[0];
                }
            }
        }

        return null;
    }

    public function generateInvoicePdf(Request $request, $id)
    {
        $invoice = Invoice::with(['customer', 'items.mobile.brand', 'items.mobile.model', 'items.accessory.brand'])->findOrFail($id);
        $pdf = Pdf::loadView('invoice.pdf_invoice', compact('invoice'));

        // Render PDF first to allow canvas access/output
        $pdf->render();

        $pdfContent = $pdf->output();
        $qpdfPath = $this->getQpdfPath();

        if ($qpdfPath) {
            $tempInput = tempnam(sys_get_temp_dir(), 'pdf_in_');
            $tempOutput = tempnam(sys_get_temp_dir(), 'pdf_out_');

            if ($tempInput && $tempOutput) {
                file_put_contents($tempInput, $pdfContent);
                $ownerPassword = config('app.key', 'Yash_Mobile');

                // Run QPDF to encrypt the PDF file with AES-256 and restrict modify/copy permissions.
                // --encrypt --owner-password=[password] --bits=256 --print=full --modify=none --extract=n -- [input] [output]
                $cmd = sprintf(
                    '%s --encrypt --owner-password=%s --bits=256 --print=full --modify=none --extract=n -- %s %s',
                    escapeshellarg($qpdfPath),
                    escapeshellarg($ownerPassword),
                    escapeshellarg($tempInput),
                    escapeshellarg($tempOutput)
                );

                exec($cmd, $cmdOutput, $cmdResult);

                if ($cmdResult === 0 && file_exists($tempOutput) && filesize($tempOutput) > 0) {
                    $pdfContent = file_get_contents($tempOutput);
                }

                @unlink($tempInput);
                @unlink($tempOutput);
            }
        } else {
            // Fallback to legacy 40-bit RC4 encryption if QPDF is not available
            $canvas = $pdf->getDomPDF()->getCanvas();
            if ($canvas) {
                $cpdf = $canvas->get_cpdf();
                if ($cpdf) {
                    $cpdf->setEncryption('', config('app.key', 'Yash_Mobile'), ['print', 'copy']);
                }
            }
            $pdfContent = $pdf->output();
        }

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="invoice_' . $invoice->invoice_no . '.pdf"',
        ]);
    }

    public function generateTandcPdf()
    {
        $invoice = new Invoice;
        $invoice->invoice_date = null;
        $invoice->customer = null;
        $invoice->items = collect([]);
        $pdf = Pdf::loadView('invoice.pdf_tandc', compact('invoice'));

        // Render PDF first to allow canvas access/output
        $pdf->render();

        $pdfContent = $pdf->output();
        $qpdfPath = $this->getQpdfPath();

        if ($qpdfPath) {
            $tempInput = tempnam(sys_get_temp_dir(), 'pdf_in_');
            $tempOutput = tempnam(sys_get_temp_dir(), 'pdf_out_');

            if ($tempInput && $tempOutput) {
                file_put_contents($tempInput, $pdfContent);
                $ownerPassword = config('app.key', 'secure_owner_pass');

                // Run QPDF to encrypt the PDF file with AES-256 and restrict modify/copy permissions.
                // --encrypt --owner-password=[password] --bits=256 --print=full --modify=none --extract=n -- [input] [output]
                $cmd = sprintf(
                    '%s --encrypt --owner-password=%s --bits=256 --print=full --modify=none --extract=n -- %s %s',
                    escapeshellarg($qpdfPath),
                    escapeshellarg($ownerPassword),
                    escapeshellarg($tempInput),
                    escapeshellarg($tempOutput)
                );

                exec($cmd, $cmdOutput, $cmdResult);

                if ($cmdResult === 0 && file_exists($tempOutput) && filesize($tempOutput) > 0) {
                    $pdfContent = file_get_contents($tempOutput);
                }

                @unlink($tempInput);
                @unlink($tempOutput);
            }
        } else {
            // Fallback to legacy 40-bit RC4 encryption if QPDF is not available
            $canvas = $pdf->getDomPDF()->getCanvas();
            if ($canvas) {
                $cpdf = $canvas->get_cpdf();
                if ($cpdf) {
                    $cpdf->setEncryption('', config('app.key', 'secure_owner_pass'), ['print', 'copy']);
                }
            }
            $pdfContent = $pdf->output();
        }

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="terms_and_conditions.pdf"',
        ]);
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $invoice = Invoice::with(['items.mobile', 'items.accessory'])->findOrFail($id);

            // Revert status/stock
            foreach ($invoice->items as $item) {
                if ($item->mobile) {
                    $item->mobile->update(['status' => 'in_stock']);
                }
                if ($item->accessory) {
                    $item->accessory->increment('stock', $item->qty ?? 1);
                }
                // Delete associated transactions
                Transaction::where('invoice_no', $invoice->invoice_no)->delete();
            }

            $invoice->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully.',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error deleting invoice: '.$e->getMessage(),
            ]);
        }
    }

    public function getProductByBarcode($barcode)
    {
        $mobile = Mobile::with(['brand', 'model'])
            ->where('hsn_number', $barcode)
            ->where('status', 'in_stock')
            ->first();

        if ($mobile) {
            $name = ($mobile->brand->name ?? '').' '.($mobile->model->name ?? '');
            $name .= ' ('.$mobile->storage.'/'.$mobile->ram.')';

            $product = (object) [
                'id' => $mobile->id,
                'name' => $name.' [HSN: '.$barcode.']',
                'quantity' => 1,
                'price' => $mobile->buy_price,
                'hsn_number' => $barcode,
                'unique_id' => 'mobile_'.$mobile->id,
                'type' => 'device',
                'brand_id' => $mobile->brand_id,
                'color' => $mobile->color,
                'battery_health' => $mobile->battery_health,
                'storage' => $mobile->storage,
                'ram' => $mobile->ram,
            ];

            return response()->json(['status' => true, 'product' => $product]);
        }

        $accessory = Accessory::with(['brand'])
            ->where('hsn', $barcode)
            ->where('stock', '>', 0)
            ->first();

        if ($accessory) {
            $name = ($accessory->brand->name ?? '').' '.($accessory->name ?? '');
            $name .= $accessory->model ? ' ('.$accessory->model.')' : '';

            $product = (object) [
                'id' => $accessory->id,
                'name' => $name.' [HSN: '.$barcode.']',
                'quantity' => 1,
                'price' => 0,
                'hsn_number' => $barcode,
                'unique_id' => 'accessory_'.$accessory->id,
                'type' => 'accessory',
                'brand_id' => $accessory->brand_id,
                'color' => $accessory->color,
                'battery_health' => '',
                'storage' => '',
                'ram' => '',
            ];

            return response()->json(['status' => true, 'product' => $product]);
        }

        return response()->json(['status' => false, 'message' => 'Product not found or not in stock']);
    }

    public function getCustomer(Request $request)
    {
        $phone = $request->phone;
        $cleanPhone = preg_replace('/^\+91|^0/', '', $phone);

        $customer = Customer::where('phone', $phone)
            ->orWhere('phone', $cleanPhone)
            ->orWhere('phone', '+91' . $cleanPhone)
            ->orWhere('phone', '0' . $cleanPhone)
            ->first();

        if ($customer) {
            return response()->json(['status' => true, 'customer' => $customer]);
        }

        return response()->json(['status' => false, 'message' => 'Customer not found']);
    }

    public function getSupplier(Request $request)
    {
        $phone = $request->phone;
        $cleanPhone = preg_replace('/^\+91|^0/', '', $phone);

        $supplier = Customer::where('phone', $phone)
            ->orWhere('phone', $cleanPhone)
            ->orWhere('phone', '+91' . $cleanPhone)
            ->orWhere('phone', '0' . $cleanPhone)
            ->first();

        if ($supplier) {
            return response()->json(['status' => true, 'supplier' => $supplier]);
        }

        return response()->json(['status' => false, 'message' => 'Supplier not found']);
    }
}
