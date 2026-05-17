<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Expense;
use App\Models\Mobile;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('mobile.model')->latest()->paginate(10);
        return view('expenses.index', compact('expenses'))->with([
            'header_title' => "Expense Ledger",
            'tagline' => "Track shop operational costs and device-specific expenses."
        ]);
    }

    public function create(Request $request)
    {
        $mobiles = Mobile::with('model')->get();
        $selected_mobile = $request->mobile_id;
        return view('expenses.create', compact('mobiles', 'selected_mobile'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mobile_id' => 'nullable|exists:mobiles,id',
            'title' => 'required|string|max:200',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        Expense::create($validated);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded!');
    }
}
