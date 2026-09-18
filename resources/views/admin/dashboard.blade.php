@extends('admin.layouts.master')
@section('title', 'Admin: Dashboard')
@section('css')
<style>
    /* Chart Cards */
    .card-chart {
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(8, 21, 66, 0.05);
        border: none;
        margin-bottom: 24px;
    }
    .card-chart .card-header {
        background: transparent;
        border-bottom: 1px solid #f0f0f0;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .card-chart .card-title {
        font-size: 16px;
        font-weight: 700;
        text-transform: uppercase;
        margin: 0;
        color: #1b1b1b;
    }
    .card-chart .card-menu {
        color: #888;
        cursor: pointer;
    }
    .card-chart .card-body {
        padding: 20px;
    }

    /* Tabs */
    .service-tabs .nav-link {
        font-weight: 600;
        color: #6c757d;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 10px 20px;
        transition: all 0.3s;
    }
    .service-tabs .nav-link.active {
        color: #7366ff;
        border-bottom: 2px solid #7366ff;
        background: transparent;
    }
    .service-tabs .nav-link:hover {
        color: #7366ff;
    }
    
    /* KPI Cards */
    .kpi-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(8, 21, 66, 0.05);
        border: none;
        border-top: 3px solid #7366ff; /* Default color */
        text-align: center;
        padding: 20px 15px;
        height: 100%;
        margin-bottom: 24px;
        transition: transform 0.3s;
    }
    .kpi-card:hover {
        transform: translateY(-5px);
    }
    .kpi-card h3 {
        font-size: 24px;
        font-weight: 700;
        color: #000;
        margin-bottom: 10px;
    }
    .kpi-card p {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 0;
        font-weight: 500;
        text-transform: uppercase;
    }
    
    /* Responsive Grid */
    .chart-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }
    
    /* General Matrix Card Style */
    .matrix-card {
        background: #fff;
        border-radius: 10px;
        padding: 20px;
        display: flex;
        align-items: center;
        box-shadow: 0 0 15px rgba(8, 21, 66, 0.05);
        border: none;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .matrix-icon-box {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 24px;
        color: #fff;
    }
    .matrix-info h5 {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 5px;
        font-weight: 600;
    }
    .matrix-info h3 {
        font-size: 20px;
        font-weight: 700;
        margin: 0;
        color: #2c323f;
    }
    .matrix-badge {
        position: absolute;
        bottom: 15px;
        right: 15px;
        font-size: 10px;
        padding: 3px 8px;
        border-radius: 5px;
        font-weight: 600;
        opacity: 0.2; /* Subtler look */
        background: currentColor;
    }
    /* Colors */
    .color-purple { background-color: #7366ff; }
    .color-blue { background-color: #51bb25; }
    .color-orange { background-color: #f8d62b; }
    .color-red { background-color: #f73164; }
    
    .text-purple { color: #7366ff; }
    .text-blue { color: #51bb25; }
    .text-orange { color: #f8d62b; }
    .text-red { color: #f73164; }

    @media (max-width: 992px) {
        .chart-grid, .kpi-grid {
             grid-template-columns: 1fr;
        }
    }
    
    /* Service Tabs Custom Pills */
    .service-tabs-pill {
        background-color: #f8f9fa !important; 
        border-radius: 12px;
        padding: 6px !important;
    }
    .service-tabs-pill .nav-item {
        margin: 0 4px;
    }
    .service-tabs-pill .nav-link {
        border-radius: 8px;
        color: #59667a;
        font-weight: 600;
        padding: 10px 15px;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    .service-tabs-pill .nav-link:hover {
        color: #7366ff;
        background-color: rgba(255,255,255,0.5);
    }
    .service-tabs-pill .nav-link.active {
        background-color: #fff !important;
        color: #7366ff !important;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        border: 1px solid #eee;
    }

    /* Fullscreen mode for charts */
    .card-chart.fullscreen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 9999;
        margin: 0;
        border-radius: 0;
        background: #fff;
    }
    .card-chart.fullscreen .card-body {
        height: calc(100vh - 70px);
    }
    .card-chart.fullscreen .card-body #chart-account,
    .card-chart.fullscreen .card-body #chart-item,
    .card-chart.fullscreen .card-body #chart-rent,
    .card-chart.fullscreen .card-body #chart-deposit {
        height: 100% !important;
    }

    /* Lock scroll */
    body.chart-fullscreen-locked {
        overflow: hidden !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-0">
    <!-- PART A: General Statistics (3x4 Matrix) -->
    <div id="general-stats-container">
        @php
            $matrixConfig = [
                'month' => ['label' => 'Tháng ' . date('m'), 'badge' => 'bg-light-primary'],
                'week'  => ['label' => 'Tuần này', 'badge' => 'bg-light-secondary'],
                'today' => ['label' => 'Hôm nay', 'badge' => 'bg-light-success'],
            ];
            $metrics = [
                'members' => ['icon' => 'fa-users', 'color' => 'color-purple', 'text' => 'text-purple', 'badge' => 'badge-light-primary', 'title' => 'Thành viên đăng ký', 'is_currency' => false],
                'orders'  => ['icon' => 'fa-shopping-cart', 'color' => 'color-blue', 'text' => 'text-blue', 'badge' => 'badge-light-info', 'title' => 'Đơn hàng đã bán', 'is_currency' => false],
                'revenue' => ['icon' => 'fa-bar-chart', 'color' => 'color-orange', 'text' => 'text-orange', 'badge' => 'badge-light-warning', 'title' => 'Doanh thu đơn hàng', 'is_currency' => true],
                'profit'  => ['icon' => 'fa-money', 'color' => 'color-red', 'text' => 'text-red', 'badge' => 'badge-light-danger', 'title' => 'Lợi nhuận đơn hàng', 'is_currency' => true],
            ];
        @endphp

        @foreach($matrixConfig as $periodKey => $periodMeta)
        <div class="kpi-grid mb-4">
            @foreach($metrics as $metricKey => $metric)
            <div class="matrix-card">
                <div class="matrix-icon-box {{ $metric['color'] }}">
                    <i class="fa {{ $metric['icon'] }}"></i>
                </div>
                <div class="matrix-info">
                    <h5>{{ $metric['title'] }}</h5>
                    <h3>
                        <span class="counter-value" 
                              id="gen-{{ $periodKey }}-{{ $metricKey }}" 
                              data-target="0" 
                              data-currency="{{ $metric['is_currency'] ? 'true' : 'false' }}">
                              <i class="fa fa-spinner fa-spin" style="font-size: 16px;"></i>
                        </span>
                    </h3>
                </div>
                <span class="badge {{ $metric['badge'] }} {{ $metric['text'] }}" style="position:absolute; bottom:10px; right:10px;">{{ $periodMeta['label'] }}</span>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>

    <!-- PART B: Service Statistics Tabs -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 style="font-weight: 700; text-transform: uppercase; margin-bottom: 20px; border-left: 4px solid #7366ff; padding-left: 10px;">Thống Kê Dịch Vụ</h5>
            
            <!-- Tabs (Pills Style) -->
            <ul class="nav nav-pills nav-fill service-tabs-pill mb-4 p-2 bg-light rounded" id="serviceTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-service="account" onclick="loadKpis('account', this)">
                        <i class="fa fa-user me-2"></i> Tài Khoản
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-service="account_v2" onclick="loadKpis('account_v2', this)">
                        <i class="fa fa-user-plus me-2"></i> Tài Khoản V2
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-service="item" onclick="loadKpis('item', this)">
                         <i class="fa fa-cube me-2"></i> Vật Phẩm
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-service="rent" onclick="loadKpis('rent', this)">
                         <i class="fa fa-trophy me-2"></i> Cày Thuê
                    </button>
                </li>
            </ul>

            <!-- KPI Content (Static Grid for Skeleton Loading) -->
            <div id="service-stats-container">
                 @php
                    // Define configuration for the 12 cards to match the API response order
                    // API returns: 
                    // [0] Total Orders, [1] Total Revenue, [2] Orders Today, [3] Orders Yesterday
                    // [4] Rev Today, [5] Rev Yesterday, [6] Rev Week, [7] Rev Month
                    // [8] Profit Total, [9] Profit Today, [10] Profit Month, [11] Profit Last Month
                    $svcMetrics = [
                        ['icon' => 'fa-shopping-cart', 'color' => 'color-blue', 'title' => 'Tổng Đơn Hàng'],
                        ['icon' => 'fa-bar-chart', 'color' => 'color-orange', 'title' => 'Tổng Doanh Thu'],
                        ['icon' => 'fa-clock-o', 'color' => 'color-purple', 'title' => 'Đơn Hàng Hôm Nay'],
                        ['icon' => 'fa-clock-o', 'color' => 'color-purple', 'title' => 'Đơn Hàng Hôm Qua'],
                        
                        ['icon' => 'fa-money', 'color' => 'color-blue', 'title' => 'Doanh Thu Hôm Nay'],
                        ['icon' => 'fa-money', 'color' => 'color-blue', 'title' => 'Doanh Thu Hôm Qua'],
                        ['icon' => 'fa-money', 'color' => 'color-blue', 'title' => 'Doanh Thu Tuần'],
                        ['icon' => 'fa-money', 'color' => 'color-blue', 'title' => 'Doanh Thu Tháng'],
                        
                        ['icon' => 'fa-line-chart', 'color' => 'color-red', 'title' => 'Tổng Lợi Nhuận'],
                        ['icon' => 'fa-line-chart', 'color' => 'color-red', 'title' => 'Lợi Nhuận Hôm Nay'],
                        ['icon' => 'fa-line-chart', 'color' => 'color-red', 'title' => 'Lợi Nhuận Tháng'],
                        ['icon' => 'fa-line-chart', 'color' => 'color-red', 'title' => 'Lợi Nhuận Tháng Trước'],
                    ];
                @endphp
                
                <div class="kpi-grid">
                    @foreach($svcMetrics as $index => $metric)
                    <div class="matrix-card">
                        <div class="matrix-icon-box {{ $metric['color'] }}">
                            <i class="fa {{ $metric['icon'] }}"></i>
                        </div>
                        <div class="matrix-info">
                            <h5 class="svc-title-{{ $index }}">{{ $metric['title'] }}</h5>
                            <h3>
                                <span class="counter-value svc-value" 
                                      id="svc-card-{{ $index }}" 
                                      data-target="0" 
                                      data-currency="false">
                                      <i class="fa fa-spinner fa-spin" style="font-size: 16px;"></i>
                                </span>
                            </h3>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- PART C: Global Revenue Charts -->
    <div class="row">
        <div class="col-12">
            <div class="chart-grid">
                <!-- Chart 1: Revenue Account (Merged) -->
                <div class="card card-chart">
                    <div class="card-header">
                        <h5 class="card-title">DOANH THU TÀI KHOẢN</h5>
                        <i class="fa fa-bars card-menu"></i>
                    </div>
                    <div class="card-body">
                        <div id="chart-account"></div>
                    </div>
                </div>

                <!-- Chart 2: Revenue Item -->
                <div class="card card-chart">
                    <div class="card-header">
                        <h5 class="card-title">DOANH THU VẬT PHẨM</h5>
                        <i class="fa fa-bars card-menu"></i>
                    </div>
                    <div class="card-body">
                        <div id="chart-item"></div>
                    </div>
                </div>

                <!-- Chart 3: Revenue Rent/Boosting -->
                <div class="card card-chart">
                    <div class="card-header">
                        <h5 class="card-title">DOANH THU CÀY THUÊ</h5>
                        <i class="fa fa-bars card-menu"></i>
                    </div>
                    <div class="card-body">
                        <div id="chart-rent"></div>
                    </div>
                </div>

                <!-- Chart 4: Global Deposit -->
                <div class="card card-chart">
                    <div class="card-header">
                        <h5 class="card-title">THỐNG KÊ NẠP TIỀN</h5>
                        <i class="fa fa-bars card-menu"></i>
                    </div>
                    <div class="card-body">
                        <div id="chart-deposit"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // --- Global Chart Registry ---
    window.dashboardCharts = {};
    // Store original configs to revert later
    window.chartConfigs = {
        'chart-account': { default: '8px', fullscreen: '15px' },
        'chart-item':    { default: '8px', fullscreen: '15px' },
        'chart-rent':    { default: '8px', fullscreen: '15px' },
        'chart-deposit': { default: '50%', fullscreen: '50%' }
    };

    // --- Fullscreen Toggle Logic ---
    $(document).on('click', '.card-menu', function() {
        const card = $(this).closest('.card-chart');
        const chartDiv = card.find('.card-body > div').first();
        const chartId = chartDiv.attr('id');
        
        // Toggle Fullscreen class
        if (card.hasClass('fullscreen')) {
            // --- Exit Fullscreen ---
            card.removeClass('fullscreen');
            $('body').removeClass('chart-fullscreen-locked');

            // Revert Chart Options
            if (window.dashboardCharts[chartId] && window.chartConfigs[chartId]) {
                window.dashboardCharts[chartId].updateOptions({
                    chart: { height: 350 }, // Revert to fixed height
                    plotOptions: { bar: { columnWidth: window.chartConfigs[chartId].default } }
                });
            }
        } else {
            // --- Enter Fullscreen ---
            card.addClass('fullscreen');
            $('body').addClass('chart-fullscreen-locked');

            // Update Chart Options
            if (window.dashboardCharts[chartId] && window.chartConfigs[chartId]) {
                window.dashboardCharts[chartId].updateOptions({
                    chart: { height: '100%' }, // Force full height
                    plotOptions: { bar: { columnWidth: window.chartConfigs[chartId].fullscreen } }
                });
            }
        }
        
        // Trigger resize for safety
        setTimeout(() => { window.dispatchEvent(new Event('resize')); }, 100);
    });

    // --- Helper Functions ---
    const formatCurrency = (value) => {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value).replace('₫', 'đ');
    }
    const formatNumber = (value) => {
        return new Intl.NumberFormat('vi-VN').format(value);
    }

    // --- Counter Animation (REMOVED: Direct Display) ---
    const runCounterAnimation = (selector = '.counter-value') => {
        $(selector).each(function() {
            const $this = $(this);
            const target = parseFloat($this.data('target'));
            const isCurrency = $this.data('currency');
            
            // Stop any running animation (just in case)
            $this.stop();

            // Direct Display without animation
            let display = isCurrency ? formatCurrency(target) : formatNumber(target);
            $this.text(display);
        });
    }

    // --- Chart rendering ---
    const renderChart = (elementId, labels, seriesData, colors = ['#44b4ff', '#8e54e9'], columnWidth = '8px', borderRadius = 0) => {
        var options = {
            series: seriesData,
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: columnWidth,
                    borderRadius: borderRadius
                },
            },
            dataLabels: { enabled: false },
            stroke: { show: false },
            xaxis: {
                categories: labels,
                labels: { style: { fontSize: '10px' } },
                axisBorder: { show: true, color: '#e0e0e0' }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return formatNumber(val);
                    },
                    style: { fontSize: '10px' }
                }
            },
            fill: { opacity: 1 },
            colors: colors,
            grid: { borderColor: '#f1f1f1' },
            legend: {
                show: true,
                position: 'bottom',
                horizontalAlign: 'center'
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return formatCurrency(val);
                    }
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#" + elementId), options);
        chart.render();
        
        // Store instance
        window.dashboardCharts[elementId] = chart;
    }

    // --- Global Data Cache ---
    window.dashboardData = {};

    // Domain Filter (injected from Controller)
    const currentDomain = '{{ $domain ?? "" }}';

    // --- Render Service Stats from Cache ---
    const renderServiceStats = (service) => {
        const data = window.dashboardData[service];
        if (!data) return;

        // Populate cards
        data.forEach((item, index) => {
            const elId = `#svc-card-${index}`;
            const el = $(elId);
            if (el.length) {
                el.data('target', item.value);
                el.data('currency', item.is_currency);
            }
        });

        // Trigger animation
        runCounterAnimation('#service-stats-container .counter-value');
    }

    // --- Load ALL KPIs (Bulk) ---
    const loadAllKpis = () => {
        $.ajax({
            url: '{{ route("admin.dashboard.api.kpis") }}',
            type: 'GET',
            data: { service: 'all', domain: currentDomain },
            dataType: 'json',
            cache: false,
            success: function(response) {
                // Store in global cache
                window.dashboardData = response;

                // 1. Render General Stats Matrix
                const genData = response.general;
                if (genData) {
                    ['month', 'week', 'today'].forEach(period => {
                        if (genData[period]) {
                            const stats = genData[period];
                            Object.keys(stats).forEach(key => {
                                const elId = `#gen-${period}-${key}`;
                                const el = $(elId);
                                if (el.length) {
                                    el.data('target', stats[key]);
                                }
                            });
                        }
                    });
                    runCounterAnimation('#general-stats-container .counter-value');
                }

                // 2. Render Default Service Tab (Account)
                renderServiceStats('account');
            },
            error: function(err) {
                console.error('Error loading KPIs:', err);
                toastr.error('Error loading dashboard data');
            }
        });
    }

    // --- Load Charts Data ---
    const loadCharts = () => {
        $.ajax({
            url: '{{ route("admin.dashboard.api.revenue") }}',
            type: 'GET',
            data: { domain: currentDomain },
            dataType: 'json',
            success: function(data) {
                const labels = data.labels;

                // Chart 1: Account
                renderChart('chart-account', labels, [
                    { name: 'Doanh thu', data: data.charts.account.revenue },
                    { name: 'Lợi nhuận', data: data.charts.account.profit }
                ]);

                // Chart 2: Item
                renderChart('chart-item', labels, [
                    { name: 'Doanh thu', data: data.charts.item.revenue },
                    { name: 'Lợi nhuận', data: data.charts.item.profit }
                ]);

                // Chart 3: Rent
                renderChart('chart-rent', labels, [
                    { name: 'Doanh thu', data: data.charts.rent.revenue },
                    { name: 'Lợi nhuận', data: data.charts.rent.profit }
                ]);

                // Chart 4: Deposit (Single series)
                renderChart('chart-deposit', labels, [
                    { name: 'Paid', data: data.charts.deposit.total }
                ], ['#1a56db'], '50%', 0);
            },
            error: function(err) {
                console.error('Error loading charts:', err);
                toastr.error('Failed to load charts data');
            }
        });
    }
    
    // --- Window Scope function for HTML onclick ---
    window.loadKpis = (service, tabElement) => {
        if (tabElement) {
             // UI Update
            $('.service-tabs-pill .nav-link').removeClass('active');
            $(tabElement).addClass('active');
        }
        renderServiceStats(service);
    };

    $(document).ready(() => {
        loadCharts();
        
        // Load All Data
        loadAllKpis();
    });
</script>
@endsection
