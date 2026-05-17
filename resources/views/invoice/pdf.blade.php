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

        .container {
            width: 100%;
            min-height: 100vh;
            position: relative;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
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
            bottom: 455px;
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

        <table class="bottom-section">
            <tr>
                <td class="terms">
                    <p>
                    <h3>Terms & Conditions:</h3>
                    </p>

                    <p><strong>1. Warranty on New Mobiles</strong></p>
                    <ul>
                        <li>Manufacturer warranty applies as per brand policy.</li>
                        <li>Warranty claims will be handled by authorized service centers only.</li>
                    </ul>

                    <p><strong>2. Warranty on Old/Used Mobiles</strong></p>
                    <ul>
                        <li>24 Hours store warranty is provided (only for internal hardware issues).</li>
                        <li>The original purchase bill is mandatory for any claim.</li>
                        <li>Mobile displays, batteries, charging ports, and physical/liquid damages are not covered.
                        </li>
                    </ul>

                    <p><strong>3. Accessories</strong></p>
                    <ul>
                        <li>Only branded accessories carry company warranty.</li>
                        <li>Local/unbranded accessories are sold without warranty unless specified.</li>
                    </ul>
                </td>

                <td class="signature">
                    <p>Signature</p>
                </td>
            </tr>
        </table>

        <div class="footer">
            <p>Thank You For Shopping With Us.</p>
            <p>Shop No. 13, Center Point, Opp. Rudrax Cinema, Una - 362560</p>
        </div>
    </div>
</body>

</html>
