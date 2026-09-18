<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Config;
use App\Models\PerfectMoney;
use App\Models\PerfectMoneyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Helper;

class PerfectMoneyController extends Controller
{
    public function index()
    {
        // Stats
        $query = PerfectMoney::query();
        auth()->user()->applyHistoryLimit($query);
        $success_statuses = [Invoice::STATUS_COMPLETED, Invoice::STATUS_PAID, 'completed', 'Completed', 'paid', 'Paid', 'Success', 'success'];

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

        $chartQuery = PerfectMoney::whereIn('status', $success_statuses)
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

        return view('admin.perfect_money.index', compact('stats', 'dates', 'data_chart'));
    }

    public function config()
    {
        $pm = PerfectMoneyConfig::firstOrCreate(['id' => 1]);
        $config = $pm->config;
        $notice = \App\Models\SystemNotice::firstOrNew(['name' => 'page_deposit_pmoney']);
        $depositPort = Config::where('name', 'deposit_port')->first()->value ?? [];

        return view('admin.perfect_money.config', compact('config', 'notice', 'depositPort', 'pm'));
    }

    public function updateConfig(Request $request)
    {
        $data = $request->validate([
            'status' => 'required|in:1,0',
            'rate' => 'required|numeric|min:0',
            'currency' => 'nullable|string',
            'account_id' => 'nullable|string',
            'passphrase' => 'nullable|string',
            'note' => 'nullable|string'
        ]);

        PerfectMoneyConfig::updateOrCreate(
            ['id' => 1],
            ['config' => $data]
        );

        // Sync status to deposit_port
        $depositPort = Config::where('name', 'deposit_port')->first()->value ?? [];
        $depositPort['perfect_money'] = ($data['status'] == 1) ? 1 : 0;
        Config::updateOrCreate(['name' => 'deposit_port'], ['value' => $depositPort]);

        // Sync note to notice
        \App\Models\SystemNotice::updateOrCreate(
            ['name' => 'page_deposit_pmoney'],
            ['value' => $data['note']]
        );

        Helper::addHistory('Cập nhật cấu hình nạp tiền Perfect Money');

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật cấu hình thành công'
        ]);
    }
}
