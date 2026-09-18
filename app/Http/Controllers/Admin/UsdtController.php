<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Config;
use App\Models\Usdt;
use App\Models\UsdtConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Helper;

class UsdtController extends Controller
{
    public function index(Request $request)
    {
        // Stats
        $queryBase = Usdt::query();
        auth()->user()->applyHistoryLimit($queryBase);

        $success_statuses = [Invoice::STATUS_COMPLETED, Invoice::STATUS_PAID, 'completed', 'Completed', 'paid', 'Paid', 'success', 'Success'];

        $total = (clone $queryBase)->whereIn('status', $success_statuses)->sum('amount');
        $week  = (clone $queryBase)->whereIn('status', $success_statuses)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('amount');
        $month = (clone $queryBase)->whereIn('status', $success_statuses)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount');
        $today = (clone $queryBase)->whereIn('status', $success_statuses)->whereDate('created_at', now()->toDateString())->sum('amount');

        $stats['banks'] = [ // Standardized key to match banks page
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
        $startDateVal = now()->startOfMonth();
        $endDateVal = now()->endOfMonth();
        
        $chartData = (clone $queryBase)
            ->whereIn('status', $success_statuses)
            ->whereBetween('created_at', [$startDateVal, $endDateVal])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total_amount')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $dates = [];
        $data_chart = [];
        
        $period = \Carbon\CarbonPeriod::create($startDateVal, $endDateVal);
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $displayDate = $date->format('d/m/Y');
            $dates[] = $displayDate;
            $data_chart[] = $chartData[$formattedDate]->total_amount ?? 0;
        }

        // Return view without transactions (handled by DataTable)
        return view('admin.usdt.deposit', compact('stats', 'dates', 'data_chart'));
    }

    public function config()
    {
        $usdt = \App\Models\UsdtConfig::firstOrCreate(['id' => 1]);
        $config = $usdt->config;
        $notice = \App\Models\SystemNotice::firstOrNew(['name' => 'page_deposit_crypto']);
        $depositPort = \App\Models\Config::where('name', 'deposit_port')->first()->value ?? [];

        return view('admin.usdt.config', compact('config', 'notice', 'depositPort', 'usdt'));
    }

    public function updateConfig(Request $request)
    {
        $data = $request->validate([
            'status' => 'required|in:1,0',
            'merchant_id' => 'nullable|string',
            'api_token' => 'nullable|string', // Check Cron param name 'token' or 'api_token'? Cron uses 'token' in callback.
            'min' => 'nullable|numeric|min:0',
            'max' => 'nullable|numeric|min:0',
            'exchange' => 'nullable|numeric',
            'note' => 'nullable|string',
            'type' => 'nullable|string'
        ]);

        try {
            \App\Models\UsdtConfig::updateOrCreate(
                ['id' => 1],
                ['config' => $data]
            );

            // Sync status to deposit_port
            $depositPort = \App\Models\Config::where('name', 'deposit_port')->first()->value ?? [];
            if (!is_array($depositPort)) $depositPort = []; // Safety check
            $depositPort['crypto'] = ($data['status'] == 1) ? 1 : 0;
            \App\Models\Config::updateOrCreate(['name' => 'deposit_port'], ['value' => $depositPort]);

            // Sync note to notice
            \App\Models\SystemNotice::updateOrCreate(
                ['name' => 'page_deposit_crypto'],
                ['value' => $data['note']]
            );

            Helper::addHistory('Cập nhật cấu hình USDT');

            return response()->json(['status' => true, 'message' => 'Cập nhật thành công']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}
