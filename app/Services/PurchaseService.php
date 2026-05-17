<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    /**
     * Record a purchase for a specific item (usually a unit).
     */
    public function recordUnitPurchase(array $purchaseData, array $itemData): Purchase
    {
        return DB::transaction(function () use ($purchaseData, $itemData) {
            // 1. Handle Supplier
            $supplier = Supplier::updateOrCreate(
                ['phone' => $purchaseData['supplier_phone']],
                [
                    'name' => $purchaseData['supplier_name'],
                    'city' => $purchaseData['supplier_city'] ?? '',
                    'address' => $purchaseData['supplier_address'] ?? '',
                ]
            );

            // 2. Create Purchase Header
            $purchase = Purchase::create([
                'supplier_id' => $supplier->id,
                'purchase_date' => Carbon::parse($purchaseData['purchase_date'] ?? now()),
                'total_amount' => $purchaseData['amount'],
                'paid_amount' => $purchaseData['amount'],
                'due_amount' => 0,
                'payment_method' => $purchaseData['payment_method'] ?? 'cash',
                'notes' => $purchaseData['notes'] ?? 'System Entry'
            ]);

            // 3. Create Purchase Item
            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'item_type' => $itemData['item_type'],
                'item_id' => $itemData['item_id'],
                'imei_id' => $itemData['imei_id'],
                'quantity' => 1,
                'price' => $itemData['price'],
                'repair_cost' => $itemData['repair_cost'] ?? 0,
                'total' => $itemData['price'],
            ]);

            return $purchase;
        });
    }
}
