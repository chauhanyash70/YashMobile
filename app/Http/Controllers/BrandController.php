<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::latest()->get();
        return view('brand.index', compact('brands'));
    }

public function getBrandData(Request $request)
{
    $columns = ['id','name','slug','type'];

    $query = Brand::query();

    // Search
    $search = $request->input('search.value') ?? null;
    if($search) {
        $query->where('name','like',"%{$search}%")
              ->orWhere('slug','like',"%{$search}%")
              ->orWhere('type','like',"%{$search}%");
    }

    $totalData = Brand::count();
    $totalFiltered = $query->count();

    // Ordering
    $orderColumn = $columns[$request->input('order.0.column') ?? 0];
    $orderDir = $request->input('order.0.dir') ?? 'asc';
    $query->orderBy($orderColumn, $orderDir);

    // Pagination
    $start = $request->input('start',0);
    $length = $request->input('length',10);
    $brands = $query->offset($start)->limit($length)->get();

    // Data array
    $data = [];
    foreach($brands as $b){
        $data[] = [
            'id' => $b->id,
            'name' => $b->name,
            'slug' => $b->slug,
            'type' => $b->type,
            'actions' => $b->id,
        ];
    }

    return response()->json([
        "draw" => intval($request->input('draw',1)),
        "recordsTotal" => intval($totalData),
        "recordsFiltered" => intval($totalFiltered),
        "data" => $data
    ]);
}


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'type' => 'required|in:device,accessory,both',
        ]);

        $brand = Brand::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully!',
            'data' => $brand
        ]);
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'type' => 'required|in:device,accessory,both',
        ]);

        $brand->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully!',
            'data' => $brand
        ]);
    }

    public function destroy(Brand $brand)
    {
        // Check if brand has models or devices? 
        if ($brand->devices()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete brand with associated models.'
            ], 422);
        }

        $brand->delete();
        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully!'
        ]);
    }
}
