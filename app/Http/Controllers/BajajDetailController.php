<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mobile;
use Carbon\Carbon;

class BajajDetailController extends Controller
{
    public function create()
    {
        $currentDate = Carbon::now()->format('Y-m-d');

        return view('bajaj_details.create', [
            'currentDate' => $currentDate,
            'header_title' => 'Bajaj Details',
            'tagline' => 'Fill details to print a clean and professional Bajaj Details receipt.'
        ]);
    }

    public function print(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',

            
            // Customer Details
            'customer_name' => 'required|string',
            'contact_number' => 'required|string',
            'city' => 'nullable|string',
            
            // Product Details
            'model' => 'required|string',
            'imei_no' => 'required|string',
            'total_price' => 'required|numeric',
            
            // Bajaj Finance Details
            'down_payment' => 'required|numeric',
            'monthly_emi' => 'required|numeric',
            'emi_tenure' => 'required|integer',
            'first_emi_date' => 'required|date',

            
            // Apple ID Details
            'apple_id' => 'nullable|string',
            'apple_password' => 'nullable|string',
            'security_code' => 'nullable|string',
        ]);

        return view('bajaj_details.print', compact('data'));
    }

    public function getDevice(Request $request)
    {
        $imei = $request->imei;
        
        $mobile = Mobile::with(['brand', 'model'])
            ->where('hsn_number', $imei)
            ->first();

        if ($mobile) {
            $name = ($mobile->brand->name ?? '') . ' ' . ($mobile->model->name ?? '');
            if ($mobile->storage || $mobile->ram) {
                $name .= ' (' . implode('/', array_filter([$mobile->storage, $mobile->ram])) . ')';
            }
            if ($mobile->color) {
                $name .= ' - ' . $mobile->color;
            }

            // Let's get the price from its latest sell transaction, or purchase cost, or default to 0
            $price = $mobile->sell_price ?: ($mobile->buy_price ?: 0);

            return response()->json([
                'status' => true,
                'device' => [
                    'model' => $name,
                    'price' => $price
                ]
            ]);
        }

        return response()->json(['status' => false, 'message' => 'Device not found']);
    }
}
