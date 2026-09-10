<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Bajaj Details</title>
    <link rel="shortcut icon" href="{{ asset('assets/logo/yash-mobile-favicon.svg') }}">
    <link href="{{ asset('vendor-assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />

    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #333;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .receipt-container {
            max-width: 700px;
            margin: 25px auto;
            background: #fff;
            padding: 30px 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border-radius: 12px;
            border: 1px solid #eef2f6;
            position: relative;
        }
        /* Top Accent line representing Yash Mobile identity */
        .receipt-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #fd7e14 0%, #ffc107 100%);
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        .receipt-header {
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .brand-name {
            font-size: 26px;
            font-weight: 800;
            color: #fd7e14;
            letter-spacing: 0.5px;
        }
        .brand-address {
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
            max-width: 400px;
            margin: 5px auto 0 auto;
        }
        .meta-info-row {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 10px 18px;
            margin-bottom: 20px;
        }
        .meta-label {
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .meta-value {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
        }
        .section-header {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            border-left: 4px solid #fd7e14;
            padding-left: 8px;
            margin-top: 20px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 8px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #64748b;
            font-weight: 500;
        }
        .detail-value {
            color: #1e293b;
            font-weight: 600;
        }
        .confidential-box {
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 8px;
            padding: 12px 18px;
            margin-top: 12px;
        }
        .confidential-title {
            color: #b45309;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .confidential-footer {
            font-size: 10px;
            color: #b45309;
            margin-top: 6px;
            font-style: italic;
        }
        .receipt-footer {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 2px dashed #e2e8f0;
            text-align: center;
            color: #64748b;
            font-size: 12px;
            font-weight: 500;
        }
        .action-bar {
            max-width: 700px;
            margin: 15px auto 0 auto;
            display: flex;
            justify-content: space-between;
        }


        
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            .receipt-container {
                margin: 0;
                padding: 20px 0;
                box-shadow: none;
                border: none;
                max-width: 100%;
                width: 100%;
            }
            .receipt-container::before {
                display: none;
            }
            .action-bar {
                display: none !important;
            }
            @page {
                size: A4;
                margin: 15mm;
            }
        }
    </style>
</head>
<body>

    <div class="action-bar d-print-none">
        <button onclick="window.close();" class="btn btn-outline-secondary">
            &larr; Close Window
        </button>
        <button onclick="window.print();" class="btn btn-primary px-4" style="background-color: #fd7e14; border-color: #fd7e14;">
            Print Receipt
        </button>
    </div>

    <div class="receipt-container">
        <!-- Logo and Brand Info -->
        <div class="receipt-header text-center">
            <div class="brand-name">YASH MOBILE</div>
            <div class="brand-address">Shop No 13, Center Point, Opp. Rudrax Cinema, Una-362560<br>Contact: 8000946725</div>
        </div>

        <!-- Meta Information Panel -->
        <div class="meta-info-row d-flex justify-content-between align-items-center">
            <div>
                <span class="meta-label">Date</span>
            </div>
            <div class="text-end">
                <span class="meta-value" style="font-size: 14px;">{{ \Carbon\Carbon::parse($data['date'])->format('d/m/Y') }}</span>
            </div>
        </div>


        <!-- Customer Details -->
        <div class="section-header">Customer Details</div>
        <div class="card border-0 shadow-none">
            <div class="detail-row">
                <span class="detail-label">Customer Name</span>
                <span class="detail-value">{{ $data['customer_name'] }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Contact Number</span>
                <span class="detail-value">+91 {{ $data['contact_number'] }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">City</span>
                <span class="detail-value">{{ $data['city'] ?? '—' }}</span>
            </div>
        </div>

        <!-- Product & Device Details -->
        <div class="section-header">Product & Device Details</div>
        <div class="card border-0 shadow-none">
            <div class="detail-row">
                <span class="detail-label">Model</span>
                <span class="detail-value">{{ $data['model'] }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">IMEI No</span>
                <span class="detail-value">{{ $data['imei_no'] }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Price</span>
                <span class="detail-value" style="color: #fd7e14; font-size: 16px;">₹{{ \App\Http\Traits\Traits::formatINR($data['total_price'], 2) }}</span>
            </div>
        </div>

        <!-- Bajaj Finance EMI Details -->
        <div class="section-header">Bajaj Finance EMI Details</div>
        <div class="card border-0 shadow-none">
            <div class="detail-row">
                <span class="detail-label">Down Payment</span>
                <span class="detail-value">₹{{ \App\Http\Traits\Traits::formatINR($data['down_payment'], 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">EMI Tenure</span>
                <span class="detail-value">{{ $data['emi_tenure'] }} Months</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Monthly EMI Amt</span>
                <span class="detail-value" style="color: #16a34a; font-weight: 700;">₹{{ \App\Http\Traits\Traits::formatINR($data['monthly_emi'], 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">First EMI Date</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($data['first_emi_date'])->format('d/m/Y') }}</span>
            </div>
        </div>

        <!-- Apple ID Details -->
        @if(!empty($data['apple_id']) || !empty($data['apple_password']) || !empty($data['security_code']))
        <div class="section-header">Apple ID & Setup Details</div>
        <div class="confidential-box">
            <div class="confidential-title">🔑 Confidential Credentials</div>
            <div class="detail-row" style="background-color: transparent;">
                <span class="detail-label" style="color: #78350f;">Apple ID / Email</span>
                <span class="detail-value" style="color: #451a03;">{{ $data['apple_id'] ?? '—' }}</span>
            </div>
            <div class="detail-row" style="background-color: transparent;">
                <span class="detail-label" style="color: #78350f;">Apple ID Password</span>
                <span class="detail-value" style="color: #451a03;">{{ $data['apple_password'] ?? '—' }}</span>
            </div>
            <div class="detail-row" style="background-color: transparent; border-bottom: none;">
                <span class="detail-label" style="color: #78350f;">Security Code/PIN</span>
                <span class="detail-value" style="color: #451a03;">{{ $data['security_code'] ?? '—' }}</span>
            </div>
            <div class="confidential-footer">
                * Note: Please advise the customer to change their password after setup for security purposes.
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="receipt-footer">
            Thank You for Shopping with Yash Mobile!
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
