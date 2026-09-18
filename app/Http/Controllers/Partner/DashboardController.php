<?php

namespace App\Http\Controllers\Partner;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $domain = $user->domain;

        if (!$domain) {
            abort(403, 'Tài khoản chưa được gán tên miền quản lý.');
        }

        return view('partner.dashboard', compact('domain'));
    }

    public function apiRevenueCharts(Request $request)
    {
        $user = auth()->user();
        $domain = $user->domain;

        if (!$domain) {
            return response()->json(['status' => false, 'message' => 'No domain assigned']);
        }

        // Range: Default to current month
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $dates = [];
        $period = \Carbon\CarbonPeriod::create($start, $end);
        foreach ($period as $date) {
            $dates[] = $date->format('d/m');
        }

        // Helper to fetch daily Series
        $fetchSeries = function ($types, $isProfit = false) use ($start, $end, $domain) {
            $query = Transaction::whereIn('type', $types)
                ->whereBetween('created_at', [$start, $end])
                ->where('domain', $domain);

            $results = $query->selectRaw('DATE(created_at) as date, SUM(amount) as total_amount, SUM(cost_amount) as total_cost')
                ->groupBy('date')
                ->get()
                ->keyBy('date');

            $data = [];
            foreach (\Carbon\CarbonPeriod::create($start, $end) as $date) {
                $d = $date->format('Y-m-d');
                $val = $results[$d] ?? null;
                if (!$val) {
                    $data[] = 0;
                }
                else {
                    if ($isProfit) {
                        $data[] = $val->total_amount - $val->total_cost;
                    }
                    else {
                        $data[] = $val->total_amount;
                    }
                }
            }
            return $data;
        };

        // Chart 1: Account + Account V2
        $c1_revenue = $fetchSeries(['account-buy', 'account-v2-buy']);
        $c1_profit = $fetchSeries(['account-buy', 'account-v2-buy'], true);

        // Chart 2: Items
        $fetchNetRevenue = function ($buyTypes, $refundTypes) use ($start, $end, $domain) {
            $qBuys = Transaction::whereIn('type', $buyTypes)
                ->whereBetween('created_at', [$start, $end])
                ->where('domain', $domain);

            $qRefunds = Transaction::whereIn('type', $refundTypes)
                ->whereBetween('created_at', [$start, $end])
                ->where('domain', $domain);

            $buys = $qBuys->selectRaw('DATE(created_at) as date, SUM(amount) as val')
                ->groupBy('date')->pluck('val', 'date');
            $refunds = $qRefunds->selectRaw('DATE(created_at) as date, SUM(amount) as val')
                ->groupBy('date')->pluck('val', 'date');

            $data = [];
            foreach (\Carbon\CarbonPeriod::create($start, $end) as $date) {
                $d = $date->format('Y-m-d');
                $b = $buys[$d] ?? 0;
                $r = $refunds[$d] ?? 0;
                $data[] = $b - $r;
            }
            return $data;
        };

        $c2_revenue = $fetchSeries(['item-buy']);
        $c2_profit = $fetchNetRevenue(['item-buy'], ['item-refund']);

        // Chart 3: Boosting
        $c3_revenue = $fetchSeries(['boosting-buy']);
        $c3_profit = $fetchNetRevenue(['boosting-buy'], ['boosting-refund']);

        // Chart 4: Deposits (All types)
        $c4_deposit = $fetchSeries(['deposit-bank', 'deposit-card', 'deposit', 'deposit-momo', 'deposit-auto']);

        return response()->json([
            'labels' => $dates,
            'charts' => [
                'account' => [
                    'revenue' => $c1_revenue,
                    'profit' => $c1_profit
                ],
                'item' => [
                    'revenue' => $c2_revenue,
                    'profit' => $c2_profit
                ],
                'rent' => [
                    'revenue' => $c3_revenue,
                    'profit' => $c3_profit
                ],
                'deposit' => [
                    'total' => $c4_deposit
                ]
            ]
        ]);
    }

    public function apiServiceKpis(Request $request)
    {
        $user = auth()->user();
        $domain = $user->domain;

        if (!$domain) {
            return response()->json(['status' => false, 'message' => 'No domain assigned']);
        }

        $service = $request->input('service');
        $now = now();
        $yesterday = now()->subDay();

        // --- Helper Functions ---
        $sumRange = function ($q, $s, $e) use ($domain) {
            $qClone = clone $q;
            $qClone->where('domain', $domain);
            return $qClone->whereBetween('created_at', [$s, $e])->sum('amount');
        };
        $countRange = function ($q, $s, $e) use ($domain) {
            $qClone = clone $q;
            $qClone->where('domain', $domain);
            return $qClone->whereBetween('created_at', [$s, $e])->count();
        };

        // Closure to get Stats for a specific service type
        $getServiceStats = function ($srv) use ($now, $yesterday, $sumRange, $countRange, $domain) {
            $qBase = null;
            $qProfit = null;
            $profitFunc = null;

            if ($srv == 'account') {
                $qBase = Transaction::where('type', 'account-buy');
                $profitFunc = function ($s, $e) use ($qBase, $domain) {
                            $q = clone $qBase;
                            $q->where('domain', $domain);
                            return $q->whereBetween('created_at', [$s, $e])->sum('amount') -
                            (clone $q)->sum('cost_amount');
                        }
                            ;
                    }
                    elseif ($srv == 'account_v2') {
                        $qBase = Transaction::where('type', 'account-v2-buy');
                        $profitFunc = function ($s, $e) use ($qBase, $domain) {
                            $q = clone $qBase;
                            $q->where('domain', $domain);
                            return $q->whereBetween('created_at', [$s, $e])->sum('amount') -
                            (clone $q)->sum('cost_amount');
                        }
                            ;
                    }
                    elseif ($srv == 'item') {
                        $qBase = Transaction::where('type', 'item-buy');
                        $qRefund = Transaction::where('type', 'item-refund');
                        $profitFunc = function ($s, $e) use ($qBase, $qRefund, $domain) {
                            $qB = clone $qBase;
                            $qR = clone $qRefund;
                            $qB->where('domain', $domain);
                            $qR->where('domain', $domain);
                            return $qB->whereBetween('created_at', [$s, $e])->sum('amount') -
                            $qR->whereBetween('created_at', [$s, $e])->sum('amount');
                        }
                            ;
                    }
                    elseif ($srv == 'rent') {
                        $qBase = Transaction::where('type', 'boosting-buy');
                        $qRefund = Transaction::where('type', 'boosting-refund');
                        $profitFunc = function ($s, $e) use ($qBase, $qRefund, $domain) {
                            $qB = clone $qBase;
                            $qR = clone $qRefund;
                            $qB->where('domain', $domain);
                            $qR->where('domain', $domain);
                            return $qB->whereBetween('created_at', [$s, $e])->sum('amount') -
                            $qR->whereBetween('created_at', [$s, $e])->sum('amount');
                        }
                            ;
                    }
                    else {
                        return [];
                    }

                    // Apply Domain Filter to Base Query for Count/Sum outside range
                    $qBaseCount = clone $qBase;
                    $qBaseCount->where('domain', $domain);

                    // Build List (Exact Order for UI)
                    $list = [];
                    // Row 1
                    $list[] = ['label' => 'Tổng Đơn Hàng', 'value' => $qBaseCount->count(), 'is_currency' => false];
                    $list[] = ['label' => 'Tổng Doanh Thu', 'value' => $qBaseCount->sum('amount'), 'is_currency' => true];
                    $list[] = ['label' => 'Đơn Hàng Hôm Nay', 'value' => $countRange($qBase, $now->format('Y-m-d 00:00:00'), $now->format('Y-m-d 23:59:59')), 'is_currency' => false];
                    $list[] = ['label' => 'Đơn Hàng Hôm Qua', 'value' => $countRange($qBase, $yesterday->format('Y-m-d 00:00:00'), $yesterday->format('Y-m-d 23:59:59')), 'is_currency' => false];

                    // Row 2
                    $list[] = ['label' => 'Doanh Thu Hôm Nay', 'value' => $sumRange($qBase, $now->format('Y-m-d 00:00:00'), $now->format('Y-m-d 23:59:59')), 'is_currency' => true];
                    $list[] = ['label' => 'Doanh Thu Hôm Qua', 'value' => $sumRange($qBase, $yesterday->format('Y-m-d 00:00:00'), $yesterday->format('Y-m-d 23:59:59')), 'is_currency' => true];
                    $list[] = ['label' => 'Doanh Thu Tuần', 'value' => $sumRange($qBase, now()->startOfWeek(), now()->endOfWeek()), 'is_currency' => true];
                    $list[] = ['label' => 'Doanh Thu Tháng', 'value' => $sumRange($qBase, now()->startOfMonth(), now()->endOfMonth()), 'is_currency' => true];

                    // Row 3 (Profit)
                    $startOfTime = '2020-01-01 00:00:00';
                    $list[] = ['label' => 'Tổng Lợi Nhuận', 'value' => $profitFunc($startOfTime, $now), 'is_currency' => true];
                    $list[] = ['label' => 'Lợi Nhuận Hôm Nay', 'value' => $profitFunc($now->format('Y-m-d 00:00:00'), $now->format('Y-m-d 23:59:59')), 'is_currency' => true];
                    $list[] = ['label' => 'Lợi Nhuận Tháng ' . $now->format('m'), 'value' => $profitFunc(now()->startOfMonth(), now()->endOfMonth()), 'is_currency' => true];
                    $list[] = ['label' => 'Lợi Nhuận Tháng Trước', 'value' => $profitFunc(now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()), 'is_currency' => true];

                    return $list;
                };

        // General Stats Closure
        $getGeneralStats = function ($start, $end) use ($now, $domain) {
            // Members registered on this domain
            $members = User::where('domain', $domain)
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $types = ['account-buy', 'account-v2-buy', 'item-buy', 'boosting-buy'];
            $qTrans = Transaction::whereIn('type', $types)
                ->whereBetween('created_at', [$start, $end])
                ->where('domain', $domain);

            $orders = (clone $qTrans)->count();
            $revenue = (clone $qTrans)->sum('amount');

            $profit = 0;
            $qProfit = Transaction::whereIn('type', ['account-buy', 'account-v2-buy'])
                ->whereBetween('created_at', [$start, $end])
                ->where('domain', $domain);

            $profit += $qProfit->sum(DB::raw('amount - cost_amount'));

            $itemRevenue = Transaction::whereIn('type', ['item-buy', 'boosting-buy'])
                ->whereBetween('created_at', [$start, $end])
                ->where('domain', $domain);
            $itemRefund = Transaction::whereIn('type', ['item-refund', 'boosting-refund'])
                ->whereBetween('created_at', [$start, $end])
                ->where('domain', $domain);

            $profit += ($itemRevenue->sum('amount') - $itemRefund->sum('amount'));

            return [
            'members' => $members,
            'orders' => $orders,
            'revenue' => $revenue,
            'profit' => $profit
            ];
        };

        if ($service == 'all') {
            $data = [
                'general' => [
                    'month' => $getGeneralStats(now()->startOfMonth(), now()->endOfMonth()),
                    'week' => $getGeneralStats(now()->startOfWeek(), now()->endOfWeek()),
                    'today' => $getGeneralStats(now()->startOfDay(), now()->endOfDay())
                ],
                'account' => $getServiceStats('account'),
                'account_v2' => $getServiceStats('account_v2'),
                'item' => $getServiceStats('item'),
                'rent' => $getServiceStats('rent'),
            ];
            return response()->json($data);
        }
        elseif ($service == 'general') {
            $matrix = [
                'month' => $getGeneralStats(now()->startOfMonth(), now()->endOfMonth()),
                'week' => $getGeneralStats(now()->startOfWeek(), now()->endOfWeek()),
                'today' => $getGeneralStats(now()->startOfDay(), now()->endOfDay())
            ];
            return response()->json($matrix);
        }
        else {
            $data = $getServiceStats($service);
            if (empty($data)) {
                return response()->json(['error' => 'Invalid service'], 400);
            }
            return response()->json($data);
        }
    }
}
