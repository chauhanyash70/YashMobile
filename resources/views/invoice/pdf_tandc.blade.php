<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Terms & Conditions</title>
    <link rel="shortcut icon" href="{{ asset('assets/logo/yash-mobile-favicon.svg') }}">
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

        .disclaimer-container {
            width: 100%;
            box-sizing: border-box;
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

        .logo {
            height: 70px;
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
            margin-top: 160px;
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
    </style>
</head>

<body>
    <!-- Watermark -->
    <div class="watermark"></div>

    @php
        $deviceModels = [];
        $deviceIMEIs = [];
        if (isset($invoice) && $invoice->items) {
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
        }
        $deviceModelsStr = !empty($deviceModels) ? implode(', ', array_unique($deviceModels)) : '';
        $deviceIMEIsStr = !empty($deviceIMEIs) ? implode(', ', array_unique($deviceIMEIs)) : '';
        $customerName = (isset($invoice) && $invoice->customer) ? $invoice->customer->name : '';
        $invoiceDateStr = (isset($invoice) && $invoice->invoice_date) ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d / m / Y') : '';
    @endphp

    <div class="disclaimer-container">
        <div class="disclaimer-header">
            <img src="{{ public_path('assets/logo/yash-mobile-logo.png') }}" class="logo" alt="Company Logo">
            <p style="margin: 5px 0 0 0; font-size: 11px;">Shop No. 13, Center Point, Opp. Rudraksh Cinema, Una, Gujarat</p>
        </div>

        <div class="disclaimer-title">
            IMPORTANT NOTICE: TERMS, CONDITIONS & WATERDAMAGE DISCLAIMER
        </div>

        <div style="font-size: 11px; line-height: 1.4; color: #222; margin-bottom: 10px;">
            <strong>Dear {{ $customerName ?: 'Customer' }},</strong> Please read this document carefully. By conducting business with Yash Mobile, you agree to the terms, conditions, warranty policies, and liability disclaimers outlined below.
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
                            <li><strong>No Returns/Refunds:</strong> Once sold, devices purchased under <strong>Bajaj Finance</strong> are <strong style="color: red; font-size: 11px;">NON-REFUNDABLE</strong> and <strong style="color: red; font-size: 11px;">NON-EXCHANGEABLE</strong>.</li>
                            <li><strong>Bill Requirement:</strong> The original store invoice is mandatory for processing any claims.</li>
                            <li><strong>Note:</strong> Devices sold through <strong>BAJAJ FINANCE</strong> are strictly <strong style="color: red; font-size: 11px;">NON-REFUNDABLE</strong> once the sale is processed and the device is handed over to the customer.</li>
                            <li><strong>Note:</strong> Devices purchased under <strong>Bajaj Finance</strong> are absolutely <strong style="color: red; font-size: 11px;">NON-REFUNDABLE</strong>.</li>
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
                        <p style="margin: 4px 0 1px 0;"><strong>5. Please Note:</strong></p>
                        <div style="padding-left: 3px; color: red; font-weight: bold;">In the event of any water or liquid damage, the entire cost of inspection, parts, and repair will be borne solely by the customer.</div>
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
