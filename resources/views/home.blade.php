@extends('layouts.app')
@section('title', 'Dashboard')
@section('header_title', $header_title ?? App\Http\Traits\Traits::getGreeting() . ', ' . Auth::user()->name . '!')
@section('tagline', $tagline ?? "Here's your overview.")

@section('pageCss')
    <link rel="stylesheet" href="{{ asset('vendor-assets/libs/daterangepicker/daterangepicker.css') }}" />
@endsection

@section('content')
    <div class="container-fluid py-3">

        <!-- Spinner Overlay -->
        <div id="dashboardSpinner"
            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:var(--bs-tertiary-bg); opacity: 0.7; z-index:9999; text-align:center; padding-top:200px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- DATE SELECTOR -->
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-end align-items-start gap-2">
                <div class="d-flex flex-column align-items-end">
                    <div class="d-flex gap-2">
                        <a href="#" id="exportDeviceBtn" class="btn btn-primary d-flex align-items-center">
                            <i data-feather="download" class="me-1"></i> Export Devices
                        </a>
                        <a href="#" id="exportAccessoryBtn" class="btn btn-secondary d-flex align-items-center">
                            <i data-feather="download" class="me-1"></i> Export Accessories
                        </a>
                    </div>
                    <div class="text-muted small mt-1">
                        * Exports only <b>Sold</b> items for the period.
                    </div>
                </div>
                <div id="reportrange" class="border px-3 py-2 rounded shadow-sm ms-2" style="cursor:pointer; width:auto;">
                    <i data-feather="calendar"></i>
                    <span class="mx-1"></span>
                    <i data-feather="chevron-down"></i>
                </div>
            </div>
        </div>


        <!-- TOP CARDS -->
        <div class="row g-3">
            <!-- Mobile Sales -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div class="me-3 bg-primary text-white rounded-circle p-2 shadow-sm">
                            <i class="iconoir-smartphone-device iconoir" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold" id="mobileSalesCount">0</h3>
                            <p class="text-muted small mb-1">Period Mobile Sales</p>
                            <span class="fw-semibold">Revenue: <span id="mobileSalesRevenue">₹0</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accessory Sales -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div class="me-3 bg-warning text-white rounded-circle p-2 shadow-sm">
                            <i class="iconoir-headset iconoir" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold" id="accessorySalesCount">0</h3>
                            <p class="text-muted small mb-1">Period Acc Sales</p>
                            <span class="fw-semibold">Revenue: <span id="accessorySalesRevenue">₹0</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profit -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div class="me-3 bg-success text-white rounded-circle p-2 shadow-sm">
                            <i class="las la-rupee-sign" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold" id="periodProfit">₹0</h3>
                            <p class="text-muted small mb-0">Period Profit</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Mobiles -->
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div class="me-3 text-white rounded-circle p-2 shadow-sm" style="background-color:#0d9488;">
                            <i class="iconoir-phone-paused iconoir" style="font-size: 1.5rem;"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold" id="availableMobileCount">0</h3>
                            <p class="text-muted small mb-1">Available Mobiles</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CHART + LOW STOCK -->
        <div class="row mt-2 g-2">
            <div class="col-xl-8 col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Turnover & Profit</h5>
                    </div>
                    <div class="card-body">
                        <div id="revenueProfitChart" style="height:350px;"></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Low Stock Accessories</h5>
                    </div>
                    <div class="card-body" id="lowStockContainer">
                        <!-- Loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageScripts')
    <script src="{{ asset('vendor-assets/libs/daterangepicker/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('vendor-assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <script>
        $(function () {
            var start = moment('{{ $startDate }}');
            var end = moment('{{ $endDate }}');

            function fetchDashboard(start, end) {
                $('#dashboardSpinner').show();

                $.ajax({
                    url: "{{ route('dashboard.ajax') }}",
                    type: "POST",
                    data: {
                        start_date: start.format('YYYY-MM-DD'),
                        end_date: end.format('YYYY-MM-DD'),
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (res) {
                        // Animated counters
                        $('#mobileSalesCount').prop('Counter', 0).animate({
                            Counter: res.mobileSalesCount
                        }, {
                            duration: 1000,
                            step: function (now) {
                                $(this).text(Math.ceil(now));
                            }
                        });
                        $('#mobileSalesRevenue').prop('Counter', 0).animate({
                            Counter: res.mobileSalesRevenue
                        }, {
                            duration: 1000,
                            step: function (now) {
                                $(this).text('₹' + now.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            }
                        });
                        $('#accessorySalesCount').prop('Counter', 0).animate({
                            Counter: res.accessorySalesCount
                        }, {
                            duration: 1000,
                            step: function (now) {
                                $(this).text(Math.ceil(now));
                            }
                        });
                        $('#accessorySalesRevenue').prop('Counter', 0).animate({
                            Counter: res.accessorySalesRevenue
                        }, {
                            duration: 1000,
                            step: function (now) {
                                $(this).text('₹' + now.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            }
                        });
                        $('#periodProfit').prop('Counter', 0).animate({
                            Counter: res.profit
                        }, {
                            duration: 1000,
                            step: function (now) {
                                $(this).text('₹' + now.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                            }
                        });
                        $('#availableMobileCount').prop('Counter', 0).animate({
                            Counter: res.availableMobileCount
                        }, {
                            duration: 1000,
                            step: function (now) {
                                $(this).text(Math.ceil(now));
                            }
                        });

                        // Chart update
                        if (window.chart) {
                            window.chart.updateOptions({
                                xaxis: {
                                    categories: res.dates
                                },
                                series: [{
                                    name: 'Turnover',
                                    data: res.chartData.revenue
                                },
                                {
                                    name: 'Profit',
                                    data: res.chartData.profit
                                }
                                ]
                            });
                        }

                        // Low stock table
                        $('#lowStockContainer').html(res.lowStockHtml);
                    },
                    complete: function () {
                        $('#dashboardSpinner').hide();
                    }
                });
            }

            $(function () {
                var start = moment('{{ $startDate }}');
                var end = moment('{{ $endDate }}');

                // Set initial display
                $('#reportrange span').html(start.format('MMM D, YYYY') + ' - ' + end.format(
                    'MMM D, YYYY'));

                $('#reportrange').daterangepicker({
                    startDate: start,
                    endDate: end,
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment()
                            .subtract(1, 'month').endOf('month')
                        ]
                    }
                }, function (start, end) {
                    // Update span whenever date changes
                    $('#reportrange span').html(start.format('MMM D, YYYY') + ' - ' + end.format(
                        'MMM D, YYYY'));
                    fetchDashboard(start, end);
                });
            });


            // Initial fetch
            fetchDashboard(start, end);

            // ApexChart
            if (typeof ApexCharts !== 'undefined') {
                window.chart = new ApexCharts(document.querySelector("#revenueProfitChart"), {
                    chart: {
                        type: 'line', 
                        height: 400,
                        fontFamily: 'inherit',
                        toolbar: { show: false },
                        zoom: { enabled: false }
                    },
                    series: [{
                        name: 'Turnover',
                        type: 'column', // Revert to columns for clearer distinction
                        data: []
                    }, {
                        name: 'Profit',
                        type: 'line',
                        data: []
                    }],
                    stroke: {
                        width: [0, 4], 
                        curve: 'smooth'
                    },
                    plotOptions: {
                        bar: {
                            columnWidth: '40%',
                            borderRadius: 2
                        }
                    },
                    colors: ['rgba(248, 125, 31, 0.7)', '#10b981'], 
                    fill: {
                        opacity: [0.7, 1],
                    },
                    dataLabels: {
                        enabled: false
                    },
                    markers: {
                        size: 4,
                        strokeWidth: 2,
                        hover: { size: 6 }
                    },
                    xaxis: {
                        categories: [],
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        labels: {
                            rotate: -45,
                            rotateAlways: true,
                            hideOverlappingLabels: true,
                            style: { fontSize: '11px' }
                        }
                    },
                    yaxis: [{
                        title: { text: 'Turnover', style: { color: '#f87d1f', fontWeight: 600 } },
                        labels: {
                            formatter: function (value) { return "₹" + Math.round(value).toLocaleString(); },
                            style: { colors: '#f87d1f' }
                        }
                    }, {
                        opposite: true,
                        title: { text: 'Profit', style: { color: '#10b981', fontWeight: 600 } },
                        labels: {
                            formatter: function (value) { return "₹" + Math.round(value).toLocaleString(); },
                            style: { colors: '#10b981' }
                        }
                    }],
                    grid: {
                        borderColor: 'rgba(0,0,0,0.05)',
                        strokeDashArray: 4,
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'center',
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                        y: {
                            formatter: function (val) { return "₹" + val.toLocaleString(); }
                        }
                    }
                });
                chart.render();
            } else {
                console.error('ApexCharts library not loaded. Please check if the file exists and is accessible.');
            }

            // Export Buttons Logic
            $('#exportDeviceBtn').click(function (e) {
                e.preventDefault();
                var start = $('#reportrange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                var end = $('#reportrange').data('daterangepicker').endDate.format('YYYY-MM-DD');
                window.location.href = "{{ route('dashboard.export') }}?type=device&start_date=" + start +
                    "&end_date=" + end;
            });

            $('#exportAccessoryBtn').click(function (e) {
                e.preventDefault();
                var start = $('#reportrange').data('daterangepicker').startDate.format('YYYY-MM-DD');
                var end = $('#reportrange').data('daterangepicker').endDate.format('YYYY-MM-DD');
                window.location.href = "{{ route('dashboard.export') }}?type=accessory&start_date=" + start +
                    "&end_date=" + end;
            });
        });
    </script>
@endsection