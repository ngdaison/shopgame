@extends('partner.layouts.master')
@section('title', 'Đối tác: Dashboard')
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
                    <p class="mb-0 text-muted" style="font-size: 12px; font-weight: 500;">{{ $periodMeta['label'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
<script>
    // --- Helper Functions ---
    const formatCurrency = (value) => {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value).replace('₫', 'đ');
    }
    const formatNumber = (value) => {
        return new Intl.NumberFormat('vi-VN').format(value);
    }

    // --- Counter Animation ---
    const runCounterAnimation = (selector = '.counter-value') => {
        $(selector).each(function() {
            const $this = $(this);
            const target = parseFloat($this.data('target'));
            const isCurrency = $this.data('currency');
            
            // Direct Display
            let display = isCurrency ? formatCurrency(target) : formatNumber(target);
            $this.text(display);
        });
    }

    // --- Load Data ---
    const loadAllKpis = () => {
        $.ajax({
            url: '{{ route("partner.api.kpis") }}',
            type: 'GET',
            data: { service: 'general' },
            dataType: 'json',
            cache: false,
            success: function(response) {
                const genData = response;
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
                    runCounterAnimation();
                }
            },
            error: function(err) {
                console.error('Error loading KPIs:', err);
                toastr.error('Error loading dashboard data');
            }
        });
    }

    $(document).ready(() => {
        loadAllKpis();
    });

</script>
@endsection
