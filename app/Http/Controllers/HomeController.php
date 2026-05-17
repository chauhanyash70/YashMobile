<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Mobile;
use App\Models\Invoice;
use App\Models\Transaction;
use Maatwebsite\Excel\Facades\Excel;

class HomeController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Contracts\Support\Renderable
	 */
	public function index()
	{
		// Default: todays
		$startDate = Carbon::today()->startOfDay();
		$endDate = Carbon::today()->endOfDay();

		// Render view with empty data, AJAX will populate
		return view('home', compact('startDate', 'endDate'));
	}

	public function ajaxDashboardData(Request $request)
	{
		$startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::today()->startOfDay();
		$endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::today()->endOfDay();

		// Low stock accessories
		$lowStockAccessories = \App\Models\Accessory::with('brand')->where('stock', '<=', 5)->orderBy('stock', 'asc')->take(10)->get();

		// Sales & profit
		$periodInvoices = Invoice::with([
            'items.mobile.purchaseTransaction',
            'items.mobile.repairs',
            'items.mobile.expenses',
            'items.accessory'
        ])
        ->where('invoice_type', 'sell')
        ->whereBetween('invoice_date', [$startDate, $endDate])
        ->get();

		$mobileSalesCount = $mobileSalesRevenue = 0;
		$accessorySalesCount = $accessorySalesRevenue = 0;
		$periodProfit = 0;

		foreach ($periodInvoices as $invoice) {
			foreach ($invoice->items as $item) {
                if ($item->mobile_id && $item->mobile) {
                    $mobileSalesCount += $item->qty;
                    $mobileSalesRevenue += $item->total;

                    $mobile = $item->mobile;
                    $buyPrice = $mobile->purchaseTransaction ? $mobile->purchaseTransaction->price : 0;
                    $repairCost = $mobile->repair_cost; // Using accessor
                    $expenseAmount = $mobile->expense_amount; // Using accessor
                    
                    // Profit = (Total Sell Price) - (Buy Price * Qty) - Total Repair Cost - Total Expense Amount
                    // Note: Repair/Expense are usually per physical unit, but we assume they apply to the sale.
                    $periodProfit += ($item->total - ($buyPrice * $item->qty) - $repairCost - $expenseAmount);
                } elseif ($item->accessory_id && $item->accessory) {
                    $accessorySalesCount += $item->qty;
                    $accessorySalesRevenue += $item->total;

                    $accessory = $item->accessory;
                    $periodProfit += ($item->total - ($accessory->purchase_price * $item->qty));
                }
			}
		}
		$periodProfit = round($periodProfit, 2);

		// Chart
		$chartData = ['revenue' => [], 'profit' => []];
		$dates = [];

        // Check if single day
        $isSingleDay = $startDate->diffInDays($endDate) == 0;

        if ($isSingleDay) {
            // Hourly breakdown (00 to 23)
            for ($i = 0; $i < 24; $i++) {
                $dates[] = sprintf('%02d:00', $i);
                
                // Filter invoices for this hour
                $hourInvoices = $periodInvoices->filter(function($invoice) use ($i) {
                     return $invoice->created_at && $invoice->created_at->hour == $i;
                });

                $hourRevenue = $hourProfit = 0;
                foreach ($hourInvoices as $invoice) {
                    foreach ($invoice->items as $item) {
                        $hourRevenue += $item->total;
                        if ($item->mobile_id && $item->mobile) {
                            $mobile = $item->mobile;
                            $buyPrice = $mobile->purchaseTransaction ? $mobile->purchaseTransaction->price : 0;
                            $repairCost = $mobile->repair_cost;
                            $expenseAmount = $mobile->expense_amount;

                            $hourProfit += ($item->total - ($buyPrice * $item->qty) - $repairCost - $expenseAmount);
                        } elseif ($item->accessory_id && $item->accessory) {
                            $hourProfit += ($item->total - ($item->accessory->purchase_price * $item->qty));
                        }
                    }
                }
                $chartData['revenue'][] = round($hourRevenue, 2);
                $chartData['profit'][] = round($hourProfit, 2);
            }

        } else {
            // Daily breakdown
            $period = CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                $dates[] = $date->format('d M');
                $dayInvoices = $periodInvoices->filter(fn($invoice) => Carbon::parse($invoice->invoice_date)->format('Y-m-d') === $date->format('Y-m-d'));

                $dayRevenue = $dayProfit = 0;
                foreach ($dayInvoices as $invoice) {
                    foreach ($invoice->items as $item) {
                        $dayRevenue += $item->total;
                        if ($item->mobile_id && $item->mobile) {
                            $mobile = $item->mobile;
                            $buyPrice = $mobile->purchaseTransaction ? $mobile->purchaseTransaction->price : 0;
                            $repairCost = $mobile->repair_cost;
                            $expenseAmount = $mobile->expense_amount;

                            $dayProfit += ($item->total - ($buyPrice * $item->qty) - $repairCost - $expenseAmount);
                        } elseif ($item->accessory_id && $item->accessory) {
                            $dayProfit += ($item->total - ($item->accessory->purchase_price * $item->qty));
                        }
                    }
                }
                $chartData['revenue'][] = round($dayRevenue, 2);
                $chartData['profit'][] = round($dayProfit, 2);
            }
        }

		return response()->json([
			'mobileSalesCount' => $mobileSalesCount,
			'mobileSalesRevenue' => $mobileSalesRevenue,
			'accessorySalesCount' => $accessorySalesCount,
			'accessorySalesRevenue' => $accessorySalesRevenue,
			'profit' => $periodProfit,
			'dates' => $dates,
			'chartData' => $chartData,
			'lowStockHtml' => view('partials.low-stock', ['lowStockAccessories' => $lowStockAccessories])->render()
		]);
	}

    public function export(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::today()->endOfDay();
        
        $type = $request->get('type', 'device');

        if ($type === 'accessory') {
            return Excel::download(new \App\Exports\SoldAccessoryExport($startDate, $endDate), 'sold_accessories_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new \App\Exports\SoldDeviceExport($startDate, $endDate), 'sold_mobiles_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.xlsx');
    }
}

