<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Accessory;
use App\Models\Device;
use App\Models\Invoice;
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

		// Low stock
		$lowStockAccessories = Accessory::where('stock', '<', 5)->get();

		// Sales & profit
		$periodInvoices = Invoice::with('items')->whereBetween('invoice_date', [$startDate, $endDate])->get();

		$todayMobileSalesCount = $todayMobileSalesRevenue = 0;
		$todayAccessorySalesCount = $todayAccessorySalesRevenue = 0;
		$periodProfit = 0;

		foreach ($periodInvoices as $invoice) {
			foreach ($invoice->items as $item) {
				if ($item->item_type == 'device') {
					$todayMobileSalesCount += $item->quantity;
					$todayMobileSalesRevenue += $item->total;

					$purchaseItem = $this->getDevicePurchaseItem($item, $invoice->invoice_date);
					if ($purchaseItem) {
						$periodProfit += ($item->total - (($purchaseItem->price + $purchaseItem->repair_cost) * $item->quantity));
					} else {
						// Fallback if no purchase item found (should be rare with new logic)
						$device = Device::find($item->item_id);
						if ($device)
							$periodProfit += ($item->total - (($device->buy_price) * $item->quantity));
					}
				} elseif ($item->item_type == 'accessory') {
					$todayAccessorySalesCount += $item->quantity;
					$todayAccessorySalesRevenue += $item->total;
					$accessory = Accessory::find($item->item_id);
					if ($accessory)
						$periodProfit += ($item->total - ($accessory->purchase_price * $item->quantity));
				}
			}
		}

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
                        if ($item->item_type == 'device') {
                            $purchaseItem = $this->getDevicePurchaseItem($item, $invoice->invoice_date);
                            if ($purchaseItem) {
                                $hourProfit += ($item->total - (($purchaseItem->price + $purchaseItem->repair_cost) * $item->quantity));
                            } else {
                                $device = Device::find($item->item_id);
                                if ($device)
                                    $hourProfit += ($item->total - (($device->buy_price) * $item->quantity));
                            }
                        } elseif ($item->item_type == 'accessory') {
                            $accessory = Accessory::find($item->item_id);
                            if ($accessory)
                                $hourProfit += ($item->total - ($accessory->purchase_price * $item->quantity));
                        }
                    }
                }
                $chartData['revenue'][] = $hourRevenue;
                $chartData['profit'][] = $hourProfit;
            }

        } else {
            // Daily breakdown
            $period = CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                $dates[] = $date->format('d M');
                $dayInvoices = $periodInvoices->filter(fn($invoice) => $invoice->invoice_date === $date->format('Y-m-d'));

                $dayRevenue = $dayProfit = 0;
                foreach ($dayInvoices as $invoice) {
                    foreach ($invoice->items as $item) {
                        $dayRevenue += $item->total;
                        if ($item->item_type == 'device') {
                            $purchaseItem = $this->getDevicePurchaseItem($item, $invoice->invoice_date);
                            if ($purchaseItem) {
                                $dayProfit += ($item->total - (($purchaseItem->price + $purchaseItem->repair_cost) * $item->quantity));
                            } else {
                                $device = Device::find($item->item_id);
                                if ($device)
                                    $dayProfit += ($item->total - (($device->buy_price) * $item->quantity));
                            }
                        } elseif ($item->item_type == 'accessory') {
                            $accessory = Accessory::find($item->item_id);
                            if ($accessory)
                                $dayProfit += ($item->total - ($accessory->purchase_price * $item->quantity));
                        }
                    }
                }
                $chartData['revenue'][] = $dayRevenue;
                $chartData['profit'][] = $dayProfit;
            }
        }

		return response()->json([
			'mobileSalesCount' => $todayMobileSalesCount,
			'mobileSalesRevenue' => $todayMobileSalesRevenue,
			'accessorySalesCount' => $todayAccessorySalesCount,
			'accessorySalesRevenue' => $todayAccessorySalesRevenue,
			'profit' => $periodProfit,
			'dates' => $dates,
			'chartData' => $chartData,
			'lowStockHtml' => view('partials.low-stock', compact('lowStockAccessories'))->render()
		]);
	}

    public function export(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::today()->endOfDay();
        $type = $request->type; // 'device' or 'accessory'

        if ($type == 'device') {
            return Excel::download(new \App\Exports\SoldDeviceExport($startDate, $endDate), 'sold_devices_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.xlsx');
        } elseif ($type == 'accessory') {
            return Excel::download(new \App\Exports\SoldAccessoryExport($startDate, $endDate), 'sold_accessories_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.xlsx');
        }

        return redirect()->back();
    }
    private function getDevicePurchaseItem($item, $invoiceDate)
    {
        if (!$item->imei_id) return null;

        $purchaseItem = \App\Models\PurchaseItem::where('imei_id', $item->imei_id)
            ->whereHas('purchase', function ($query) use ($invoiceDate) {
                $query->where('purchase_date', '<=', $invoiceDate);
            })
            ->with('purchase')
            ->get()
            ->sortByDesc(function ($pi) {
                return $pi->purchase->purchase_date;
            })
            ->first();

        // Fallback to latest
        if (!$purchaseItem) {
            $purchaseItem = \App\Models\PurchaseItem::where('imei_id', $item->imei_id)->latest()->first();
        }

        return $purchaseItem;
    }
}
