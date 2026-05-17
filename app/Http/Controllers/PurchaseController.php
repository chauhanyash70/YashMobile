<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('purchase.index', [
			'title' => "Purchases",
			'breadcrumb' => array()
		]);
    }

    /**
     * Get all Purchase data using AJAX (Datatables Server Side)
     */
    public function getPurchaseData(Request $request)
    {
        $columns = [
            0 => 'id',
            1 => 'purchase_date',
            2 => 'supplier_id',
            3 => 'total_amount',
            4 => 'paid_amount',
            5 => 'due_amount'
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')] ?? 'id';
        $dir = $request->input('order.0.dir') ?? 'DESC';

        // Base query with supplier relationship
        $query = Purchase::with('supplier');

        // Total count before filter
        $totalData = $query->count();

        // Searching
        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('name', 'LIKE', "%{$search}%");
                  });
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
            $dataArray[] = [
                'id'             => '#PUR-'.$data->id,
                'purchase_date'  => $data->purchase_date,
                'supplier_name'  => $data->supplier->name ?? 'N/A',
                'total_amount'   => '₹'.number_format($data->total_amount, 2),
                'paid_amount'    => '₹'.number_format($data->paid_amount, 2),
                'due_amount'     => '₹'.number_format($data->due_amount, 2),

                // URLs
                'show_url'       => route('purchases.show', $data->id),
                'actions'        => $data->id,
            ];
        }

        return response()->json([
            'draw'            => intval($request->input('draw')),
            'recordsTotal'    => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data'            => $dataArray
        ]);
    }

    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'items.item'])->findOrFail($id);
        return view('purchase.show', compact('purchase'));
    }
}
