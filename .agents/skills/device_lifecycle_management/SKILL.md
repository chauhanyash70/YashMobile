---
name: device_lifecycle_management
description: Guidelines and business logic for device lifecycles, buybacks, invoices, and data integrity in YashMobile.
---

# Device Lifecycle & Invoice Management Skill

This skill explains how device lifecycles, buybacks, transactions, and invoice cancellations/deletions work across YashMobile.

## 1. Lifecycle Architecture & Multi-unit Tracking
- Every unique device is identified by its `hsn_number` (IMEI/Serial).
- Devices can go through multiple lifecycles:
  1. **Lifecycle 1 (Purchase)**: `Mobile` record created (`status = 'in_stock'`) with buy invoice & transaction.
  2. **Lifecycle 1 (Sale)**: `status` becomes `'sold'`, `InvoiceItem` created with `is_bought_back = false`.
  3. **Lifecycle 2 (Buyback)**: `buybackStore` marks the previous `InvoiceItem.is_bought_back = true` and creates a **NEW `Mobile` record** for the same `hsn_number` (`status = 'in_stock'`).
  4. **Listing queries**: All inventory listings (e.g. `getMobileData`) select `MAX(id)` grouped by `hsn_number` so that only the latest active cycle unit is shown.
  5. **Unit History queries**: `MobileController::show` and `hsnHistory` query all `Mobile` records matching `hsn_number` to render the full chronological audit table.

## 2. Rules for Invoice Deletions (`InvoiceController::destroy`)
When an invoice is deleted, data integrity across lifecycles must be strictly preserved:

### A. Sell Invoice Deletion
- **Mobiles**: Revert `status` to `'in_stock'`. Do not delete the mobile record.
- **Accessories**: Increment stock count by `qty`.
- **Transactions**: Delete all `Transaction` records where `invoice_no = $invoice->invoice_no`.

### B. Buy Invoice Deletion (Initial Purchase / Single Lifecycle)
- If the mobile was added via this buy invoice and has no other sales or subsequent invoices:
  - Delete repairs (`$mobile->repairs()->delete()`).
  - Delete expenses (`$mobile->expenses()->delete()`).
  - Delete buy transactions (`$mobile->transactions()->delete()`).
  - Delete the `Mobile` record itself (`$mobile->delete()`).
- Delete all `Transaction` records matching `invoice_no = $invoice->invoice_no`.

### C. Buyback Invoice Deletion (Multi-Lifecycle)
- Find the previous cycle `Mobile` unit with the same `hsn_number` (`id < $mobile->id`).
- Revert the previous unit's sold `InvoiceItem` flag: `is_bought_back = false`.
- Delete the temporary buyback `Mobile` unit, along with any repairs, expenses, and transactions attached to it.
- As a result, the device automatically falls back to its previous cycle unit (`status = 'sold'`), and the "Initiate Buyback" button becomes available again.
- Delete all `Transaction` records matching `invoice_no = $invoice->invoice_no`.

## 3. Safe Cleanup Pattern in Eloquent
```php
if ($invoice->invoice_type === 'sell') {
    foreach ($invoice->items as $item) {
        if ($item->mobile) {
            $item->mobile->update(['status' => 'in_stock']);
        }
        if ($item->accessory) {
            $item->accessory->increment('stock', $item->qty ?? 1);
        }
    }
    Transaction::where('invoice_no', $invoice->invoice_no)->delete();
} else {
    foreach ($invoice->items as $item) {
        if ($item->accessory) {
            $item->accessory->decrement('stock', $item->qty ?? 1);
        }
        if ($item->mobile) {
            $mobile = $item->mobile;

            // Revert previous buyback flag if applicable
            $previousMobile = Mobile::where('hsn_number', $mobile->hsn_number)
                ->where('id', '<', $mobile->id)
                ->orderBy('id', 'desc')
                ->first();

            if ($previousMobile) {
                $previousSaleItem = InvoiceItem::where('mobile_id', $previousMobile->id)
                    ->where('is_bought_back', true)
                    ->latest()
                    ->first();

                if ($previousSaleItem) {
                    $previousSaleItem->update(['is_bought_back' => false]);
                }
            }

            $otherInvoiceItemsCount = InvoiceItem::where('mobile_id', $mobile->id)
                ->where('invoice_id', '!=', $invoice->id)
                ->count();

            if ($otherInvoiceItemsCount === 0) {
                $mobile->repairs()->delete();
                $mobile->expenses()->delete();
                $mobile->transactions()->delete();
                $mobile->delete();
            } else {
                $mobile->update(['status' => 'sold']);
            }
        }
    }
    Transaction::where('invoice_no', $invoice->invoice_no)->delete();
}
```
