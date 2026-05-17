<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Repair;
use App\Models\Mobile;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RepairController extends Controller
{
    public function index(Request $request)
    {
        $query = Repair::with('mobile.model');
        
        if ($request->has('mobile_id')) {
            $query->where('mobile_id', $request->mobile_id);
        }

        $repairs = $query->latest()->paginate(10);
        return view('repairs.index', compact('repairs'))->with([
            'header_title' => "Repairs",
            'tagline' => "Manage device repairs and maintenance costs."
        ]);
    }

    public function create(Request $request)
    {
        $mobiles = Mobile::where('status', '!=', 'sold')->with('model')->get();
        $selected_mobile = $request->mobile_id;
        return view('repairs.create', compact('mobiles', 'selected_mobile'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mobile_id' => 'required|exists:mobiles,id',
            'issue' => 'required|string',
            'repair_cost' => 'required|numeric|min:0',
            'technician_name' => 'nullable|string',
            'repair_status' => 'required|in:pending,completed,cancelled',
            'repair_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        Repair::create($validated);

        // Update mobile status based on ALL repairs for this mobile
        $mobile = Mobile::find($request->mobile_id);
        $pendingCount = Repair::where('mobile_id', $mobile->id)->where('repair_status', 'pending')->count();
        
        if ($pendingCount > 0) {
            $mobile->update(['status' => 'repair']);
        } else {
            if ($mobile->status != 'sold') {
                $mobile->update(['status' => 'in_stock']);
            }
        }

        return redirect()->route('repairs.index')->with('success', 'Repair record added!');
    }

    public function edit(Repair $repair)
    {
        $mobiles = Mobile::with('model')->get();
        return view('repairs.edit', compact('repair', 'mobiles'));
    }

    public function update(Request $request, Repair $repair)
    {
        $validated = $request->validate([
            'mobile_id' => 'required|exists:mobiles,id',
            'issue' => 'required|string',
            'repair_cost' => 'required|numeric|min:0',
            'technician_name' => 'nullable|string',
            'repair_status' => 'required|in:pending,completed,cancelled',
            'repair_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $repair->update($validated);

        // Update mobile status based on ALL repairs for this mobile
        $mobile = Mobile::find($request->mobile_id);
        $pendingCount = Repair::where('mobile_id', $mobile->id)->where('repair_status', 'pending')->count();
        
        if ($pendingCount > 0) {
            $mobile->update(['status' => 'repair']);
        } else {
            // Only set to in_stock if it's not sold
            if ($mobile->status != 'sold') {
                $mobile->update(['status' => 'in_stock']);
            }
        }

        return redirect()->route('repairs.index')->with('success', 'Repair record updated!');
    }

    public function destroy(Repair $repair)
    {
        $mobileId = $repair->mobile_id;
        $repair->delete();

        // Check if there are other pending repairs for this mobile
        $mobile = Mobile::find($mobileId);
        $pendingRepairs = Repair::where('mobile_id', $mobileId)->where('repair_status', 'pending')->count();
        
        if ($pendingRepairs == 0 && $mobile->status != 'sold') {
            $mobile->update(['status' => 'in_stock']);
        }

        return redirect()->route('repairs.index')->with('success', 'Repair record deleted!');
    }

    /**
     * AJAX handler for DataTables server‑side processing of repairs.
     */
    public function getRepairData(Request $request)
    {
        $columns = [
            0 => 'mobile_id',
            1 => 'issue',
            2 => 'technician_name',
            3 => 'repair_cost',
            4 => 'repair_status',
            5 => 'repair_date',
        ];

        $limit = $request->input('length');
        $start = $request->input('start');
        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir') ?? 'desc';
        $orderColumn = $columns[$orderColumnIndex] ?? 'repair_date';

        $query = Repair::with('mobile.model');

        // Global search
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('issue', 'LIKE', "%{$search}%")
                  ->orWhere('technician_name', 'LIKE', "%{$search}%")
                  ->orWhereHas('mobile', function ($q2) use ($search) {
                      $q2->where('hsn_number', 'LIKE', "%{$search}%")
                         ->orWhereHas('model', function ($q3) use ($search) {
                             $q3->where('name', 'LIKE', "%{$search}%");
                         });
                  });
            });
        }

        $totalData = Repair::count();
        $totalFiltered = $query->count();

        $repairs = $query->orderBy($orderColumn, $orderDir)
                        ->offset($start)
                        ->limit($limit)
                        ->get();

        $data = [];
        foreach ($repairs as $repair) {
            $deviceHtml = '<strong>' . e($repair->mobile->model->name) . '</strong><br>' .
                         '<small class="text-muted">' . e($repair->mobile->hsn_number) . '</small>'; 
            $statusBadge = '<span class="badge bg-' . ($repair->repair_status == 'completed' ? 'success' : ($repair->repair_status == 'pending' ? 'warning' : 'danger')) . '">' .
                           strtoupper($repair->repair_status) . '</span>';
            $actions = '<div class="d-flex gap-1">' .
                        '<a href="' . route('repairs.edit', $repair) . '" class="btn btn-sm btn-outline-info" title="Edit Repair"><i class="iconoir-edit-pencil text-info fs-18"></i></a>'.
                       /* '<a href="' . route('mobiles.hsnHistory', $repair->mobile_id) . '" class="btn btn-sm btn-outline-primary" title="View Device History"><i class="iconoir-eye text-primary fs-18"></i></a>' .
                       '<form id="delete-form-' . $repair->id . '" action="' . route('repairs.destroy', $repair) . '" method="POST" class="d-inline">' .
                       csrf_field() .
                       method_field('DELETE') .
                       '<button type="button" class="btn btn-sm btn-outline-danger" title="Delete Repair" onclick="confirmDelete(' . $repair->id . ')"><i class="iconoir-trash text-danger fs-18"></i></button>' .
                       '</form>' . */
                       '</div>';

            $data[] = [
                'device' => $deviceHtml,
                'issue' => Str::limit($repair->issue, 30),
                'technician' => $repair->technician_name ?? 'N/A',
                'cost' => '₹' . number_format($repair->repair_cost, 2),
                'status' => $statusBadge,
                'date' => Carbon::parse($repair->repair_date)->format('d M, Y'),
                'actions' => $actions,
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }
}
