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
            Excel::import(new DeviceImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data imported successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error importing data: ' . $e->getMessage());
        }
    }
}
