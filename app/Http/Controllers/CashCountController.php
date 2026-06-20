<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CashCountController extends Controller
{
    /**
     * Display the cash counter calculator page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('cash_counts.index')->with([
            'header_title' => "Cash Counter",
            'tagline' => "Calculate cash denominations and print breakdowns in Indian Currency."
        ]);
    }
}
