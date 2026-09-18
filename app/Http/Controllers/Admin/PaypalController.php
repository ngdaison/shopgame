<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Config;
use App\Models\Paypal;
use App\Models\PaypalConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Helper;

class PaypalController extends Controller
{
    public function index()
    {
        // Stats
        $query = Paypal::query();
        auth()->user()->applyHistoryLimit($query);
        $success_statuses = [Invoice::STATUS_COMPLETED, Invoice::STATUS_PAID, 'completed', 'Completed', 'paid', 'Paid', 'success', 'Success'];

        $total = (clone $query)->whereIn('status', $success_statuses)->sum('amount');
        $week  = (clone $query)->whereIn('status', $success_statuses)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('amount');
        $month = (clone $query)->whereIn('status', $success_statuses)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount');
        $today = (clone $query)->whereIn('status', $success_statuses)->whereDate('created_at', now()->toDateString())->sum('amount');

        $stats['banks']   = [
            'total' => $total,
            'month' => $month,
            'week'  => $week,
            'today' => $today,
        ];
        $stats['t_banks'] = [
            'total' => 'Toàn thời gian',
            'month' => 'Tháng ' . now()->format('m'),
            'week'  => 'Trong tuần',
            'today' => 'Hôm nay',
        ];

        // Chart Data (Daily for current month)
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        $chartQuery = Paypal::whereIn('status', $success_statuses)
            ->whereBetween('created_at', [$startDate, $endDate]);
        auth()->user()->applyHistoryLimit($chartQuery);

        $chartData = $chartQuery->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $dates = [];
        $data_chart = [];

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $displayDate = $date->format('d/m/Y');
            $dates[] = $displayDate;
            $data_chart[] = $chartData[$formattedDate]->total_amount ?? 0;
        }

        return view('admin.paypal.index', compact('stats', 'dates', 'data_chart'));
    }

    public function config()
    {
        $paypal = PaypalConfig::firstOrCreate(['id' => 1]);
        $config = $paypal->config;
        $notice = \App\Models\SystemNotice::firstOrNew(['name' => 'page_deposit_paypal']);
        $depositPort = Config::where('name', 'deposit_port')->first()->value ?? [];

        return view('admin.paypal.config', compact('config', 'notice', 'depositPort', 'paypal'));
    }

    public function updateConfig(Request $request)
    {
        $data = $request->validate([
            'status' => 'required|in:1,0',
            'rate' => 'required|numeric|min:0',
            'client_id' => 'nullable|string',
            'client_secret' => 'nullable|string',
            'note' => 'nullable|string'
        ]);

        PaypalConfig::updateOrCreate(
            ['id' => 1],
            ['config' => $data]
        );

        // Sync status to deposit_port
        $depositPort = Config::where('name', 'deposit_port')->first()->value ?? [];
        $depositPort['paypal'] = ($data['status'] == 1) ? 1 : 0;
        Config::updateOrCreate(['name' => 'deposit_port'], ['value' => $depositPort]);

        // Sync note to notice
        \App\Models\SystemNotice::updateOrCreate(
            ['name' => 'page_deposit_paypal'],
            ['value' => $data['note']]
        );

        Helper::addHistory('Cập nhật cấu hình nạp tiền Paypal');

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật cấu hình thành công'
        ]);
    }
}
