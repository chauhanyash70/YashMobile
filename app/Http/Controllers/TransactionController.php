<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('transaction.index', [
            'header_title' => "Transactions",
            'tagline' => "Track all buy, sell, and repair transactions."
        ]);
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request)
    {
        $columns = [
            0 => 'id',
            1 => 'transaction_date',
            2 => 'transaction_type',
            3 => 'customer_id',
            4 => 'mobile_id',
            5 => 'price',
            6 => 'payment_method',
            7 => 'invoice_no',
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column') ?? 0];
        $dir = $request->input('order.0.dir') ?? 'desc';

        $query = Transaction::with(['customer', 'mobile.brand', 'mobile.model', 'accessory.brand', 'invoiceItem']);

        $totalData = $query->count();
        $totalFiltered = $totalData;

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query = $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('mobile.brand', function ($b) use ($search) {
                        $b->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('mobile.model', function ($m) use ($search) {
                        $m->where('name', 'LIKE', "%{$search}%");
                    });
            });
            $totalFiltered = $query->count();
        }

        $transactions = $query->orderBy($order, $dir)->offset($start)->limit($limit)->get();

        $data = [];
        foreach ($transactions as $transaction) {
            $nested = [];
            $nested['id'] = $transaction->id;
            $nested['transaction_date'] = Carbon::parse($transaction->transaction_date)->format('d M, Y');
            
            $typeBadge = '';
            switch ($transaction->transaction_type) {
                case 'buy': $typeBadge = '<span class="badge bg-soft-success text-success">Buy</span>'; break;
                case 'sell': $typeBadge = '<span class="badge bg-soft-info text-info">Sell</span>'; break;
                case 'return': $typeBadge = '<span class="badge bg-soft-danger text-danger">Return</span>'; break;
                case 'exchange': $typeBadge = '<span class="badge bg-soft-warning text-warning">Exchange</span>'; break;
                case 'repair': $typeBadge = '<span class="badge bg-soft-primary text-primary">Repair</span>'; break;
            }
            $nested['transaction_type'] = $typeBadge;
            
            $nested['customer_name'] = $transaction->customer ? $transaction->customer->name : 'N/A';
            
            $item = '';
            if ($transaction->mobile) {
                $item = ($transaction->mobile->brand->name ?? '') . ' ' . ($transaction->mobile->model->name ?? '');
            } elseif ($transaction->accessory) {
                $item = ($transaction->accessory->brand->name ?? '') . ' ' . ($transaction->accessory->name ?? '');
            }
            $nested['item'] = $item ?: 'N/A';
            
            $nested['price'] = $transaction->price;
            $nested['discount'] = $transaction->invoiceItem->discount ?? 0;
            $nested['payment_method'] = $transaction->payment_method == 'bajaj_finance' ? 'Bajaj Finance' : ucfirst($transaction->payment_method);
            $nested['invoice_no'] = $transaction->invoice_no ?: 'N/A';
            $nested['show_url'] = route('transactions.show', $transaction->id);
            $nested['actions'] = $transaction->id;

            $data[] = $nested;
        }

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = Transaction::with(['customer', 'mobile.brand', 'mobile.model', 'accessory.brand', 'invoice'])->findOrFail($id);
        
        return view('transaction.show', compact('transaction'))->with([
            'header_title' => "Transaction Details",
            'tagline' => "Detailed overview of the transaction #" . $transaction->id
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $transaction = Transaction::findOrFail($id);
            $transaction->delete();
            return response()->json(['success' => true, 'message' => 'Transaction deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
