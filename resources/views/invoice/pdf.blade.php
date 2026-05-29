<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Invoice</title>
    <style>
        @page {
            size: A4;
            margin: 10;
        }

        @media print {
            .hidden-print {
                display: none !important;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin: 10px;
            padding: 20px;
            position: relative;
            height: 100%;
            border: 1px solid #ddd;
        }

        .container {
            width: 100%;
            min-height: 100vh;
            position: relative;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        .disclaimer-container {
            width: 100%;
            box-sizing: border-box;
        }

        .page-break {
            page-break-before: always;
        }

        .watermark {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('{{ public_path('assets/logo/yash-mobile-logo.png') }}') no-repeat center;
            background-size: contain;
            opacity: 0.1;
            /* Light transparency */
            z-index: -1;
            /* Keep behind content */
        }

        /* Disclaimer Styling */
        .disclaimer-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px double #333;
            padding-bottom: 10px;
        }

        .disclaimer-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 15px 0;
            color: #111;
            text-decoration: underline;
        }

        .disclaimer-body {
            font-size: 12px;
            line-height: 1.5;
            color: #222;
        }

        .disclaimer-body p {
            margin-bottom: 10px;
        }

        .disclaimer-body ol {
            padding-left: 20px;
            margin-bottom: 15px;
        }

        .disclaimer-body li {
            margin-bottom: 8px;
            text-align: justify;
        }

        .disclaimer-section-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0 10px 0;
            border-top: 1px dashed #333;
            border-bottom: 1px dashed #333;
            padding: 5px 0;
        }

        .acknowledgement-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .acknowledgement-table td {
            padding: 6px 5px;
            font-size: 12px;
            vertical-align: middle;
        }

        .field-label {
            font-weight: bold;
            color: #333;
        }

        .field-line {
            border-bottom: 1px solid #333;
            display: inline-block;
            padding-left: 5px;
            font-style: italic;
            color: #000;
        }

        .signature-table {
            width: 100%;
            margin-top: 35px;
        }

        .signature-table td {
            width: 50%;
            vertical-align: bottom;
        }

        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 12px;
            font-weight: bold;
        }

        .disclaimer-footer {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin-top: 25px;
            border-top: 2px double #333;
            padding-top: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo {
            height: 70px;
        }

        .invoice-details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .invoice-details td {
            padding: 5px;
            border: 1px solid #ddd;
        }

        .invoice-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .invoice-items th,
        .invoice-items td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        .invoice-items th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .total {
            font-weight: bold;
            text-align: right;
        }

        .bottom-section {
            width: 100%;
            border-top: 1px solid #ddd;
            position: absolute;
            bottom: 365px;
            margin-top: auto;
        }

        .terms,
        .signature {
            padding: 10px;
        }

        .terms {
            width: 70%;
            text-align: left;
            font-size: 11px;
            line-height: 1.4;
        }

        .terms p {
            margin: 2px 0;
        }

        .terms ul {
            margin: 0 0 5px 15px;
            padding: 0;
        }

        .terms li {
            margin: 0;
            font-size: 11px;
        }

        .bottom-section td {
            vertical-align: bottom;
        }

        .signature {
            width: 30%;
            text-align: right;
            font-size: 12px;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 2px;
            position: absolute;
            bottom: 40px;
            width: 100%;
        }

        .total-section {
            width: 100%;
            position: absolute;
            bottom: 155px;
            padding-top: 10px;
        }

        .total-section td {
            text-align: right;
            padding: 4px;
        }
    </style>
</head>

<body>
    <!-- Watermark -->
    <div class="watermark"></div>

    <div class="container">
        <div class="header">
            <img src="{{ public_path('assets/logo/yash-mobile-logo.png') }}" class="logo" alt="Company Logo">
        </div>

        <table class="invoice-details">
            <tr>
                <td><strong>Invoice No:</strong> <br>#{{ $invoice->invoice_no }}</td>
                <td><strong>Date:</strong> <br>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') }} <br>
                    <strong>Payment Method:</strong>
                    <br>{{ $invoice->payment_method == 'bajaj_finance' ? 'Bajaj Finance' : ucfirst($invoice->payment_method ?? 'N/A') }}
                    @if ($invoice->bajaj_approval_number)
                        <br><strong>Bajaj Appr No:</strong> {{ $invoice->bajaj_approval_number }}
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>Invoice To:</strong> <br>{{ $invoice->customer ? $invoice->customer->name : 'N/A' }} <br>
                    <strong>Mobile:</strong>
                    <br>{{ $invoice->customer ? $invoice->customer->phone : 'N/A' }}
                </td>
                <td><strong>Address:</strong> <br>{{ $invoice->customer ? $invoice->customer->address : 'N/A' }}</td>
            </tr>
        </table>

        <table class="invoice-items">
            <thead>
                <tr>
                    <th>Sr. No.</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>
                            @if ($item->mobile)
                                {{ $item->mobile->brand->name ?? '' }} {{ $item->mobile->model->name ?? '' }}<br>
                                @if ($item->mobile->color)
                                    <small>Color: {{ $item->mobile->color }}</small><br>
                                  @endif
                                @if ($item->mobile->storage)
                                    <small>Storage: {{ $item->mobile->storage }}</small><br>
                                  @endif
                                @if ($item->mobile->ram)
                                    <small>RAM: {{ $item->mobile->ram }}</small><br>
                                  @endif
                                @if ($item->mobile->hsn_number)
                                    <small>HSN Number: {{ $item->mobile->hsn_number }}</small>
                                @endif
                            @elseif ($item->accessory)
                                {{ $item->accessory->brand->name ?? '' }} {{ $item->accessory->name ?? '' }}
                                {{ $item->accessory->model ? '(' . $item->accessory->model . ')' : '' }}<br>
                                @if ($item->accessory->color)
                                    <small>Color: {{ $item->accessory->color }}</small><br>
                                  @endif
                                @if ($item->accessory->hsn)
                                    <small>HSN: {{ $item->accessory->hsn }}</small>
                                @endif
                            @else
                                Item Deleted
                            @endif
                        </td>
                        <td>{{ $item->qty ?? ($item->quantity ?? 1) }}</td>
                        <td>{{ number_format($item->price, 2) }}</td>
                        <td>{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="total-section">
            @if ($invoice->tax_amount > 0)
                <tr>
                    <td colspan="4" class="total-label">Tax</td>
                    <td class="total">{{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td colspan="4" class="total-label"><strong>Total Amount</strong></td>
                <td class="total"><strong>{{ number_format($invoice->paid_amount, 2) }}</strong></td>
            </tr>
        </table>


        <div class="footer">
            <p>Thank You For Shopping With Us.</p>
            <p>Shop No. 13, Center Point, Opp. Rudrax Cinema, Una - 362560</p>
        </div>
    </div>

    @php
        $deviceModels = [];
        $deviceIMEIs = [];
        foreach ($invoice->items as $item) {
            if ($item->mobile) {
                $brand = $item->mobile->brand->name ?? '';
                $model = $item->mobile->model->name ?? '';
                $deviceModels[] = trim($brand . ' ' . $model);
                if ($item->mobile->hsn_number) {
                    $deviceIMEIs[] = $item->mobile->hsn_number;
                }
            }
        }
        $deviceModelsStr = !empty($deviceModels) ? implode(', ', array_unique($deviceModels)) : 'N/A';
        $deviceIMEIsStr = !empty($deviceIMEIs) ? implode(', ', array_unique($deviceIMEIs)) : 'N/A';
        $customerName = $invoice->customer ? $invoice->customer->name : 'N/A';
        $invoiceDateStr = \Carbon\Carbon::parse($invoice->invoice_date)->format('d / m / Y');
    @endphp

    <!-- PAGE BREAK -->
    <div class="page-break"></div>

    <!-- PAGE 2: DISCLAIMER -->
    <div class="disclaimer-container">
        <div class="disclaimer-header">
            <img src="{{ public_path('assets/logo/yash-mobile-logo.png') }}" class="logo" alt="Company Logo">
            <p style="margin: 5px 0 0 0; font-size: 11px;">Shop No. 13, Center Point, Opp. Rudraksh Cinema, Una, Gujarat</p>
        </div>

        <div class="disclaimer-title">
            IMPORTANT NOTICE: TERMS, CONDITIONS & WATER DAMAGE DISCLAIMER
        </div>

        <div style="font-size: 11px; line-height: 1.4; color: #222; margin-bottom: 10px;">
            <strong>Dear Valued Customer,</strong> Please read this document carefully. By conducting business with Yash Mobile, you agree to the terms, conditions, warranty policies, and liability disclaimers outlined below.
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 10px;">
            <tr>
                <td style="width: 48%; vertical-align: top; padding-right: 15px; border-right: 1px solid #ddd;">
                    <h3 style="margin: 0 0 5px 0; font-size: 12px; text-transform: uppercase; color: #111; border-bottom: 1px solid #333; padding-bottom: 2px;">
                        Terms & Conditions (Warranty)
                    </h3>
                    <div style="font-size: 10px; line-height: 1.35; color: #222;">
                        <p style="margin: 2px 0 1px 0;"><strong>1. Warranty on New Mobiles:</strong></p>
                        <ul style="margin: 0 0 4px 0; padding-left: 12px; list-style-type: square;">
                            <li>Manufacturer warranty applies as per brand policy.</li>
                            <li>Warranty claims are handled by authorized service centers.</li>
                        </ul>

                        <p style="margin: 4px 0 1px 0;"><strong>2. Warranty & Sales on Used/Old Mobiles:</strong></p>
                        <ul style="margin: 0 0 4px 0; padding-left: 12px; list-style-type: square;">
                            <li><strong>24-Hour Warranty:</strong> Applies strictly to internal motherboard/hardware issues only.</li>
                            <li><strong>No Warranty Coverage:</strong> Screen/Displays (including green lines/touch issues), batteries, charging ports, cameras, and physical/liquid damages are explicitly excluded.</li>
                            <li><strong>"AS-IS" Condition:</strong> Devices are sold "As-Is". Customers must inspect, test all features, and log in to accounts (Apple ID/Google Account) before leaving.</li>
                            <li><strong>No Returns/Refunds:</strong> Once sold, devices are non-refundable and non-exchangeable after 24 hours.</li>
                            <li><strong>Bill Requirement:</strong> The original store invoice is mandatory for processing any claims.</li>
                        </ul>

                        <p style="margin: 4px 0 1px 0;"><strong>3. Accessories:</strong></p>
                        <ul style="margin: 0; padding-left: 12px; list-style-type: square;">
                            <li>Only branded accessories carry company warranty.</li>
                            <li>Local accessories are sold without warranty unless specified.</li>
                        </ul>
                    </div>
                </td>
                <td style="width: 48%; vertical-align: top; padding-left: 15px;">
                    <h3 style="margin: 0 0 5px 0; font-size: 12px; text-transform: uppercase; color: #111; border-bottom: 1px solid #333; padding-bottom: 2px;">
                        Water Damage Disclaimer
                    </h3>
                    <div style="font-size: 10px; line-height: 1.35; color: #222; text-align: justify;">
                        <p style="margin: 2px 0 1px 0;"><strong>1. No Liability for Liquid Damage:</strong></p>
                        <div style="margin-bottom: 4px; padding-left: 3px;">Yash Mobile and staff hold zero liability under any circumstances for water damage to your device.</div>

                        <p style="margin: 4px 0 1px 0;"><strong>2. Manufacturer Warranty Limits:</strong></p>
                        <div style="margin-bottom: 4px; padding-left: 3px;">Major brands explicitly state that liquid damage is NOT covered, regardless of IP-ratings.</div>

                        <p style="margin: 4px 0 1px 0;"><strong>3. Used Devices & Water Risk:</strong></p>
                        <div style="margin-bottom: 4px; padding-left: 3px;">Water-resistance degrades over time. For used/old devices, original factory seals are likely degraded or opened for inspection, making water resistance <strong>completely non-existent</strong>. Any water exposure is entirely at your own risk.</div>

                        <p style="margin: 4px 0 1px 0;"><strong>4. Post-Repair Warning:</strong></p>
                        <div style="padding-left: 3px;">Opening a device breaks its waterproof seal. Original water-resistance CANNOT be guaranteed after repair. Keep repaired units strictly dry.</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="disclaimer-section-title" style="margin-top: 5px; margin-bottom: 5px;">
            CUSTOMER ACKNOWLEDGEMENT
        </div>

        <div style="font-size: 10.5px; line-height: 1.35; text-align: justify; margin-bottom: 10px;">
            I hereby declare that I have read, understood, and accepted the terms, conditions, warranty policies, and disclaimers stated above. I agree that Yash Mobile and the device manufacturer hold zero liability for any water or liquid-related damages, data loss, or component failure in my mobile device.
        </div>

        <table class="acknowledgement-table">
            <tr>
                <td style="width: 55%;">
                    <span class="field-label">Customer Name:</span>
                    <span class="field-line" style="width: 70%;">{{ $customerName }}</span>
                </td>
                <td style="width: 45%;">
                    <span class="field-label">Device Model:</span>
                    <span class="field-line" style="width: 68%;">{{ $deviceModelsStr }}</span>
                </td>
            </tr>
            <tr>
                <td style="width: 55%;">
                    <span class="field-label">Mobile / IMEI No:</span>
                    <span class="field-line" style="width: 68%;">{{ $deviceIMEIsStr }}</span>
                </td>
                <td style="width: 45%;">
                    <span class="field-label">Date:</span>
                    <span class="field-line" style="width: 82%;">{{ $invoiceDateStr }}</span>
                </td>
            </tr>
        </table>

        <table class="signature-table">
            <tr>
                <td style="text-align: left; width: 50%;">
                    <div class="signature-line" style="text-align: left; padding-left: 20px; width: 75%;">
                        Customer Signature
                    </div>
                </td>
                <td style="text-align: right; width: 50%;">
                    <div class="signature-line" style="margin-left: auto; text-align: right; padding-right: 20px; width: 75%;">
                        Store Stamp / Sign
                    </div>
                </td>
            </tr>
        </table>

        <div class="disclaimer-footer">
            Thank you for your cooperation and business!
        </div>
    </div>
</body>

</html>
