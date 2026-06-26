@extends('layouts.app')
@section('title', 'Cash Counter')
@section('header_title', $header_title ?? 'Cash Counter')
@section('tagline', $tagline ?? 'Calculate cash denominations and print breakdowns in Indian Currency.')

@section('pageCss')
<style>
    /* Styling for currency badge identifiers */
    .denom-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 80px;
        font-weight: 700;
        font-size: 14px;
        padding: 6px 12px;
        border-radius: 6px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* Distinct colors for Indian Currency notes (curated harmoniously) */
    .badge-2000 { background-color: #f1d2e7; color: #721c24; border: 1px solid #e7aed1; }
    .badge-500 { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
    .badge-200 { background-color: #fff3cd; color: #664d03; border: 1px solid #ffecb5; }
    .badge-100 { background-color: #e2e3e5; color: #41464b; border: 1px solid #d3d6d8; }
    .badge-50 { background-color: #cff4fc; color: #055160; border: 1px solid #b6effb; }
    .badge-20 { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
    .badge-10 { background-color: #efebe9; color: #4e342e; border: 1px solid #d7ccc8; }
    .badge-5 { background-color: #f3e5f5; color: #4a148c; border: 1px solid #e1bee7; }
    .badge-coin { background-color: #eceff1; color: #37474f; border: 1px solid #cfd8dc; font-size: 13px; }

    /* Custom Input adjustments */
    .count-input {
        font-size: 16px;
        font-weight: 600;
        text-align: center;
        border-radius: 8px;
        transition: all 0.2s ease-in-out;
    }
    .count-input:focus {
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
    }
    .subtotal-display {
        font-size: 16px;
        font-weight: 700;
        text-align: right;
    }

    /* Hover effect for row items */
    .denom-row {
        transition: background-color 0.15s ease-in-out;
    }
    .denom-row:hover {
        background-color: rgba(0,0,0,0.015);
    }
    [data-bs-theme="dark"] .denom-row:hover {
        background-color: rgba(255,255,255,0.02);
    }

    /* Large Display Panel */
    .total-box {
        background: linear-gradient(135deg, #0d6efd, #0b5ed7);
        color: #fff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.25);
    }
    .total-amount-label {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.85;
    }
    .total-amount-val {
        font-size: 32px;
        font-weight: 800;
        margin-top: 5px;
        letter-spacing: 0.5px;
    }
    .words-box {
        background-color: rgba(0,0,0,0.03);
        border-radius: 8px;
        padding: 12px;
        font-size: 14px;
        font-weight: 600;
        font-style: italic;
    }
    [data-bs-theme="dark"] .words-box {
        background-color: rgba(255,255,255,0.05);
    }

    /* Print styling rules */
    @media print {
        @page {
            size: auto;
            margin: 10mm 15mm 10mm 15mm;
        }
        :root, [data-bs-theme="dark"], [data-bs-theme="dark"] body {
            --bs-body-bg: #fff !important;
            --bs-body-color: #000 !important;
            --bs-card-bg: #fff !important;
            --bs-card-color: #000 !important;
            --bs-tertiary-bg: #fff !important;
            --bs-border-color: #dee2e6 !important;
            --bs-light-bg: #f8f9fa !important;
            --bs-light-color: #000 !important;
            --bs-primary: #000 !important;
        }
        *, *::before, *::after,
        html[data-bs-theme="dark"] *,
        html[data-bs-theme="dark"] *::before,
        html[data-bs-theme="dark"] *::after {
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
            text-shadow: none !important;
        }
        body, html, .page-wrapper, .page-content, .container-xxl, .card, .card-body, table, thead, tbody, tr, td, th,
        html[data-bs-theme="dark"] body,
        html[data-bs-theme="dark"] html,
        html[data-bs-theme="dark"] .page-wrapper,
        html[data-bs-theme="dark"] .page-content,
        html[data-bs-theme="dark"] .container-xxl,
        html[data-bs-theme="dark"] .card,
        html[data-bs-theme="dark"] .card-body,
        html[data-bs-theme="dark"] table,
        html[data-bs-theme="dark"] thead,
        html[data-bs-theme="dark"] tbody,
        html[data-bs-theme="dark"] tr,
        html[data-bs-theme="dark"] td,
        html[data-bs-theme="dark"] th {
            background-color: #fff !important;
            background: #fff !important;
            color: #000 !important;
        }
        .startbar, .startbar-overlay, .topbar, .d-print-none, header, footer, .page-content-header {
            display: none !important;
        }
        .page-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            min-height: auto !important;
        }
        .page-content {
            margin: 0 !important;
            padding: 0 0 110px 0 !important;
            min-height: auto !important;
        }
        .card, 
        html[data-bs-theme="dark"] body .card,
        html[data-bs-theme="dark"] body .shadow-sm,
        html[data-bs-theme="dark"] body .shadow {
            border: none !important;
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
            background: transparent !important;
            background-color: transparent !important;
        }
        .card-header {
            display: none !important;
        }
        .card-body, 
        html[data-bs-theme="dark"] body .card-body {
            padding: 0 !important;
            background-color: transparent !important;
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
        }
        .container-xxl {
            max-width: 100% !important;
            width: 100% !important;
            padding: 0 !important;
        }
        .sticky-top {
            position: static !important;
        }

        /* Force table and summary side-by-side on print */
        .main-calculator-row {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            align-items: flex-start !important;
            gap: 30px !important;
        }
        .main-calculator-row .col-lg-8 {
            flex: 0 0 55% !important;
            max-width: 55% !important;
            width: 55% !important;
        }
        .main-calculator-row .col-lg-4 {
            flex: 0 0 40% !important;
            max-width: 40% !important;
            width: 40% !important;
        }

        /* Table print design styling */
        #denom-table {
            border-collapse: collapse !important;
            width: 100% !important;
            background-color: #fff !important;
        }
        #denom-table th, #denom-table td {
            padding: 8px 12px !important;
            border: 1px solid #dee2e6 !important;
            background-color: #fff !important;
            color: #000 !important;
        }
        #denom-table th {
            background-color: #f8f9fa !important;
            color: #000 !important;
            border-bottom: 2px solid #dee2e6 !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        .denom-row {
            border-bottom: 1px solid #dee2e6 !important;
        }
        .denom-row.zero-count {
            display: none !important;
        }

        /* For print, display a clean slip headers */
        .print-header {
            display: block !important;
            margin-bottom: 20px;
            text-align: center;
            background-color: #fff !important;
        }
        .print-header p {
            margin: 0;
            color: #333 !important;
            font-size: 13px;
        }
        .print-footer {
            display: block !important;
            position: fixed !important;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            margin: 0 !important;
            padding-bottom: 10px !important;
            background-color: #fff !important;
        }
        .print-footer p, .print-footer div {
            color: #000 !important;
        }
        .total-box, html[data-bs-theme="dark"] body .total-box {
            background: transparent !important;
            background-color: transparent !important;
            color: #000 !important;
            border: 2px solid #000 !important;
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
            padding: 12px !important;
            border-radius: 8px !important;
        }
        .total-amount-val {
            font-size: 24px !important;
            font-weight: 800 !important;
            color: #000 !important;
        }
        .total-amount-label {
            color: #000 !important;
            font-weight: 700 !important;
            font-size: 12px !important;
        }
        .words-box, html[data-bs-theme="dark"] body .words-box {
            border: 1px dashed #000 !important;
            background-color: transparent !important;
            color: #000 !important;
            font-size: 13px !important;
            padding: 10px !important;
            border-radius: 6px !important;
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
        }
        .print-notes-val, html[data-bs-theme="dark"] body .print-notes-val {
            background-color: #f8f9fa !important;
            color: #000 !important;
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            -webkit-box-shadow: none !important;
        }
        .subtotal-display {
            text-align: right !important;
            font-weight: 700 !important;
            color: #000 !important;
        }
        tr {
            page-break-inside: avoid;
        }
        .print-watermark {
            display: flex !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            justify-content: center !important;
            align-items: center !important;
            z-index: -1000 !important;
            pointer-events: none !important;
            opacity: 0.05 !important;
            background-color: transparent !important;
            background: transparent !important;
        }
        .print-watermark img {
            width: 100% !important;
            height: auto !important;
            max-width: 100% !important;
            background-color: transparent !important;
            background: transparent !important;
        }
    }

    /* Print helper hidden elements by default */
    .print-header, .print-footer, .print-watermark {
        display: none;
    }
</style>
@endsection

@section('content')
<div class="container-xxl">
    <!-- Print Watermark -->
    <div class="print-watermark">
        <img src="{{ asset('assets/logo/yash-mobile-logo.png') }}" alt="Yash Mobile Watermark">
    </div>

    <!-- Browser Print Header (only visible on print) -->
    <div class="print-header">
        <img src="{{ asset('assets/logo/yash-mobile-logo.png') }}" alt="Yash Mobile Logo" width="150" class="mb-2">
        <p><strong>CASH DENOMINATION REPORT</strong></p>
        <p>Date: <span id="print-date"></span></p>
    </div>

    <div class="row main-calculator-row">
        <!-- Input Board Section -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
                    <div>
                        <h4 class="card-title mb-0">Currency Calculator</h4>
                        <p class="text-muted mb-0 fs-12 d-print-none">Input the count for notes and coins to compute the grand total.</p>
                    </div>
                    <div class="d-print-none">
                        <button type="button" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" id="btn-reset">
                            <i class="iconoir-refresh fs-14"></i>
                            Reset
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" id="denom-table">
                            <thead class="table-light d-print-table-header">
                                <tr>
                                    <th class="ps-4" style="width: 25%;">Denomination</th>
                                    <th class="d-print-none" style="width: 10%;"></th>
                                    <th class="text-center" style="width: 30%;">Count</th>
                                    <th class="d-print-none" style="width: 10%;"></th>
                                    <th class="text-end pe-4" style="width: 25%;">Total (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Notes -->
                                {{-- <tr class="denom-row zero-count" data-value="2000">
                                    <td class="ps-4">
                                        <span class="denom-badge badge-2000">₹ 2000</span>
                                    </td>
                                    <td class="text-muted text-center fs-14 d-print-none">×</td>
                                    <td class="text-center">
                                        <input type="number" min="0" step="1" class="form-control count-input border-dashed d-print-none" placeholder="0">
                                        <span class="d-none d-print-inline fw-semibold print-count-val">0</span>
                                    </td>
                                    <td class="text-muted text-center fs-14 d-print-none">=</td>
                                    <td class="pe-4 subtotal-display text-primary">₹ 0</td>
                                </tr> --}}
                                <tr class="denom-row zero-count" data-value="500">
                                    <td class="ps-4">
                                        <span class="denom-badge badge-500">₹ 500</span>
                                    </td>
                                    <td class="text-muted text-center fs-14 d-print-none">×</td>
                                    <td class="text-center">
                                        <input type="number" min="0" step="1" class="form-control count-input border-dashed d-print-none" placeholder="0">
                                        <span class="d-none d-print-inline fw-semibold print-count-val">0</span>
                                    </td>
                                    <td class="text-muted text-center fs-14 d-print-none">=</td>
                                    <td class="pe-4 subtotal-display text-primary">₹ 0</td>
                                </tr>
                                <tr class="denom-row zero-count" data-value="200">
                                    <td class="ps-4">
                                        <span class="denom-badge badge-200">₹ 200</span>
                                    </td>
                                    <td class="text-muted text-center fs-14 d-print-none">×</td>
                                    <td class="text-center">
                                        <input type="number" min="0" step="1" class="form-control count-input border-dashed d-print-none" placeholder="0">
                                        <span class="d-none d-print-inline fw-semibold print-count-val">0</span>
                                    </td>
                                    <td class="text-muted text-center fs-14 d-print-none">=</td>
                                    <td class="pe-4 subtotal-display text-primary">₹ 0</td>
                                </tr>
                                <tr class="denom-row zero-count" data-value="100">
                                    <td class="ps-4">
                                        <span class="denom-badge badge-100">₹ 100</span>
                                    </td>
                                    <td class="text-muted text-center fs-14 d-print-none">×</td>
                                    <td class="text-center">
                                        <input type="number" min="0" step="1" class="form-control count-input border-dashed d-print-none" placeholder="0">
                                        <span class="d-none d-print-inline fw-semibold print-count-val">0</span>
                                    </td>
                                    <td class="text-muted text-center fs-14 d-print-none">=</td>
                                    <td class="pe-4 subtotal-display text-primary">₹ 0</td>
                                </tr>
                                <tr class="denom-row zero-count" data-value="50">
                                    <td class="ps-4">
                                        <span class="denom-badge badge-50">₹ 50</span>
                                    </td>
                                    <td class="text-muted text-center fs-14 d-print-none">×</td>
                                    <td class="text-center">
                                        <input type="number" min="0" step="1" class="form-control count-input border-dashed d-print-none" placeholder="0">
                                        <span class="d-none d-print-inline fw-semibold print-count-val">0</span>
                                    </td>
                                    <td class="text-muted text-center fs-14 d-print-none">=</td>
                                    <td class="pe-4 subtotal-display text-primary">₹ 0</td>
                                </tr>
                                <tr class="denom-row zero-count" data-value="20">
                                    <td class="ps-4">
                                        <span class="denom-badge badge-20">₹ 20</span>
                                    </td>
                                    <td class="text-muted text-center fs-14 d-print-none">×</td>
                                    <td class="text-center">
                                        <input type="number" min="0" step="1" class="form-control count-input border-dashed d-print-none" placeholder="0">
                                        <span class="d-none d-print-inline fw-semibold print-count-val">0</span>
                                    </td>
                                    <td class="text-muted text-center fs-14 d-print-none">=</td>
                                    <td class="pe-4 subtotal-display text-primary">₹ 0</td>
                                </tr>
                                <tr class="denom-row zero-count" data-value="10">
                                    <td class="ps-4">
                                        <span class="denom-badge badge-10">₹ 10</span>
                                    </td>
                                    <td class="text-muted text-center fs-14 d-print-none">×</td>
                                    <td class="text-center">
                                        <input type="number" min="0" step="1" class="form-control count-input border-dashed d-print-none" placeholder="0">
                                        <span class="d-none d-print-inline fw-semibold print-count-val">0</span>
                                    </td>
                                    <td class="text-muted text-center fs-14 d-print-none">=</td>
                                    <td class="pe-4 subtotal-display text-primary">₹ 0</td>
                                </tr>
                                {{-- <tr class="denom-row zero-count" data-value="5">
                                    <td class="ps-4">
                                        <span class="denom-badge badge-5">₹ 5</span>
                                    </td>
                                    <td class="text-muted text-center fs-14">×</td>
                                    <td>
                                        <input type="number" min="0" step="1" class="form-control count-input border-dashed" placeholder="0">
                                    </td>
                                    <td class="text-muted text-center fs-14">=</td>
                                    <td class="pe-4 subtotal-display text-primary">₹ 0</td>
                                </tr> --}}
                                <!-- Coins -->
                                {{-- <tr class="denom-row zero-count" data-value="2">
                                    <td class="ps-4">
                                        <span class="denom-badge badge-coin">₹ 2 (Coin)</span>
                                    </td>
                                    <td class="text-muted text-center fs-14">×</td>
                                    <td>
                                        <input type="number" min="0" step="1" class="form-control count-input border-dashed" placeholder="0">
                                    </td>
                                    <td class="text-muted text-center fs-14">=</td>
                                    <td class="pe-4 subtotal-display text-primary">₹ 0</td>
                                </tr>
                                <tr class="denom-row zero-count" data-value="1">
                                    <td class="ps-4">
                                        <span class="denom-badge badge-coin">₹ 1 (Coin)</span>
                                    </td>
                                    <td class="text-muted text-center fs-14">×</td>
                                    <td>
                                        <input type="number" min="0" step="1" class="form-control count-input border-dashed" placeholder="0">
                                    </td>
                                    <td class="text-muted text-center fs-14">=</td>
                                    <td class="pe-4 subtotal-display text-primary">₹ 0</td>
                                </tr> --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Panel Section -->
        <div class="col-lg-4">
            <div class="card border-0 sticky-top" style="top: 85px; z-index: 10;">
                <div class="card-body p-4">
                    <!-- Grand Total Box -->
                    <div class="total-box text-center mb-4">
                        <span class="total-amount-label">Grand Total</span>
                        <div class="total-amount-val" id="grand-total">₹ 0</div>
                    </div>

                    <!-- Total In Words Box -->
                    <div class="mb-4">
                        <label class="form-label text-muted fw-semibold">Amount in Words (INR)</label>
                        <div class="words-box text-center text-primary" id="amount-in-words">
                            Rupees Zero Only
                        </div>
                    </div>

                    <!-- Notes Input Area -->
                    <div class="mb-4">
                        <label for="notes" class="form-label text-muted fw-semibold">Remarks / Notes</label>
                        <textarea class="form-control border-dashed d-print-none" id="notes" rows="4" placeholder="Enter notes (e.g. Counter name, verification notes)"></textarea>
                        <div class="d-none d-print-block print-notes-val border p-2 bg-light rounded" style="min-height: 80px; font-size: 13px; white-space: pre-wrap; word-break: break-word;">
                            N/A
                        </div>
                    </div>

                    <!-- Print Button Actions -->
                    <div class="d-grid d-print-none">
                        <button type="button" class="btn btn-primary d-flex align-items-center justify-content-center gap-2 py-2" id="btn-print" disabled>
                            <i class="iconoir-printer fs-18"></i>
                            <span class="fw-bold">Print Counter Slip</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Footer (Signature Line, only visible on print) -->
    <div class="print-footer mt-5">
        <div class="row pt-5">
            <div class="col-6 text-start">
                <div style="border-top: 1px solid #000; width: 180px; margin-top: 40px; display: inline-block;"></div>
                <p class="mt-1 fw-bold fs-12">Counter / Operator Signature</p>
            </div>
            <div class="col-6 text-end">
                <div style="border-top: 1px solid #000; width: 180px; margin-top: 40px; display: inline-block;"></div>
                <p class="mt-1 fw-bold fs-12">Verified By / Manager Signature</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pageScripts')
<script>
$(document).ready(function() {
    // Sync current print date
    function updatePrintDate() {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        $('#print-date').text(new Date().toLocaleDateString('en-IN', options));
    }
    updatePrintDate();

    // Trigger update calculation on change of counts
    $('#denom-table').on('input', '.count-input', function() {
        // Enforce integer inputs only (strip decimal values or negative signs)
        let valStr = $(this).val();
        if (valStr !== '') {
            let cleanVal = parseInt(valStr.replace(/[^0-9]/g, ''), 10);
            if (isNaN(cleanVal) || cleanVal < 0) {
                cleanVal = 0;
            }
            $(this).val(cleanVal === 0 ? '' : cleanVal);
        }
        
        calculateTotals();
    });

    // Reset board
    $('#btn-reset').on('click', function() {
        $('.count-input').val('');
        $('#notes').val('');
        calculateTotals();
        toastr.info("Calculator reset successfully.");
    });

    // Print breakdown
    $('#btn-print').on('click', function() {
        updatePrintDate();
        window.print();
    });

    // Sync notes text to print display on input change
    $('#notes').on('input', function() {
        let notesText = $(this).val().trim();
        $('.print-notes-val').text(notesText !== '' ? notesText : 'N/A');
    });

    function calculateTotals() {
        let grandTotal = 0;

        $('.denom-row').each(function() {
            let denomVal = parseInt($(this).data('value'), 10);
            let countVal = parseInt($(this).find('.count-input').val(), 10) || 0;
            let subtotal = denomVal * countVal;

            grandTotal += subtotal;

            // Update row subtotal display
            $(this).find('.subtotal-display').text('₹ ' + subtotal.toLocaleString('en-IN'));

            // Sync print count display
            $(this).find('.print-count-val').text(countVal);

            // Control print class visibility (hide zero-count rows during printing)
            if (countVal > 0) {
                $(this).removeClass('zero-count');
            } else {
                $(this).addClass('zero-count');
            }
        });

        // Format and update grand total display
        $('#grand-total').text('₹ ' + grandTotal.toLocaleString('en-IN'));

        // Update total in words
        let words = convertNumberToIndianWords(grandTotal);
        $('#amount-in-words').text(words);

        // Sync notes on calculation update
        let notesText = $('#notes').val().trim();
        $('.print-notes-val').text(notesText !== '' ? notesText : 'N/A');

        // Toggle print button state
        $('#btn-print').prop('disabled', grandTotal <= 0);
    }

    // Indian Numbering Word Converter function
    function convertNumberToIndianWords(amount) {
        if (amount === 0) return "Rupees Zero Only";
        
        let num = Math.floor(amount);
        
        const singleDigits = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine"];
        const teenDigits = ["Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen"];
        const doubleDigits = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];
        
        function getWord(n) {
            let str = "";
            if (n >= 100) {
                str += singleDigits[Math.floor(n / 100)] + " Hundred ";
                n %= 100;
            }
            if (n >= 10 && n < 20) {
                str += teenDigits[n - 10] + " ";
            } else if (n >= 20) {
                str += doubleDigits[Math.floor(n / 10)] + " " + singleDigits[n % 10] + " ";
            } else if (n > 0) {
                str += singleDigits[n] + " ";
            }
            return str;
        }
        
        let words = "";
        
        // Crore (1,00,00,000)
        if (num >= 10000000) {
            words += getWord(Math.floor(num / 10000000)) + "Crore ";
            num %= 10000000;
        }
        // Lakh (1,00,000)
        if (num >= 100000) {
            words += getWord(Math.floor(num / 100000)) + "Lakh ";
            num %= 100000;
        }
        // Thousand (1,000)
        if (num >= 1000) {
            words += getWord(Math.floor(num / 1000)) + "Thousand ";
            num %= 1000;
        }
        // Rest (100)
        if (num > 0) {
            words += getWord(num);
        }
        
        return "Rupees " + words.trim() + " Only";
    }

    // Run initial calc
    calculateTotals();
});
</script>
@endsection
