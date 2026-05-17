<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DeviceImport;
use Exception;

class UploadController extends Controller
{
    public function index()
    {
        return view('upload.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new DeviceImport;
            Excel::import($import, $request->file('file'));

            $msg = "Data imported successfully. " . 
                   "Devices: {$import->deviceCount}, " . 
                   "Accessories: {$import->accessoryCount}";
            
            if ($import->skipCount > 0) {
                $msg .= ", Skipped: {$import->skipCount}";
            }

            return redirect()->back()->with('success', $msg);
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error importing data: ' . $e->getMessage());
        }
    }
}
