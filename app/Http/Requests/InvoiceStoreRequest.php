<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
			'invoice_number' => 'required|unique:invoices,invoice_number',
            'invoice_date' => 'required',
            'name' => 'required',
            'mobile' => 'required',
            'address' => 'required',
            'invoice_items' => 'required|array',
            'invoice_items.*.product_id' => 'required|exists:products,id',
            /* 'invoice_items.*.name' => 'required',
            'invoice_items.*.color' => 'required',
            'invoice_items.*.imei_or_serial_number' => 'required',
            'invoice_items.*.storage' => 'required', */
            'invoice_items.*.quantity' => 'required',
            'invoice_items.*.price' => 'required',
            'invoice_items.*.discount' => 'nullable',
        ];
    }
}
