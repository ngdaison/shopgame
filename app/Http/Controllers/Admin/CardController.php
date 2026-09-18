<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Card;

use App\Models\ApiConfig;
use Illuminate\Http\Request; // Import Request
use Helper; // Import Helper

class CardController extends Controller
{
    public function index(Request $request)
    {
        // Stats
        $success_statuses = [Card::STATUS_COMPLETED, Card::STATUS_SUCCESS, 'completed', 'success', 'paid', 'Paid'];
        $query = \Illuminate\Support\Facades\DB::table('cards')->whereIn('status', $success_statuses);

        // Apply history limit
        $limitDate = auth()->user()->getHistoryLimitDate();
        if ($limitDate) {
            $query->where('created_at', '>=', $limitDate);
        }

        // Filter by Admin Hide for stats too if needed? Usually stats should reflect reality. 
        // But let's assume stats should also respect admin hide if they want to "clean up" the view.
        $query->whereNull('admin_deleted_at');

        $total = (clone $query)->sum('amount');
        $week = (clone $query)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('amount');
        $month = (clone $query)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount');
        $today = (clone $query)->whereDate('created_at', now()->toDateString())->sum('amount');

        $stats['cards'] = [
            'total' => $total,
            'month' => $month,
            'week' => $week,
            'today' => $today,
        ];
        $stats['t_cards'] = [
            'total' => 'Toàn thời gian',
            'month' => 'Tháng ' . now()->format('m'),
            'week' => 'Trong tuần',
            'today' => 'Hôm nay',
        ];

        // Chart Data (Daily for current month)
        $startDateVal = now()->startOfMonth();
        $endDateVal = now()->endOfMonth();

        $chartQuery = \Illuminate\Support\Facades\DB::table('cards')
            ->whereIn('status', $success_statuses)
            ->whereBetween('created_at', [$startDateVal, $endDateVal]);

        if ($limitDate) {
            $chartQuery->where('created_at', '>=', $limitDate);
        }

        $chartData = $chartQuery->selectRaw('DATE(created_at) as date, SUM(amount) as total_amount')
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

        $query = Card::query()->with('user:id,username,domain,role');

        // Filter by Admin Hide
        $query->whereNull('admin_deleted_at');

        // Filter by Role Limit Date
        $limitDate = auth()->user()->getHistoryLimitDate();
        if ($limitDate) {
            $query->where('created_at', '>=', $limitDate);
        }

        if ($request->filled('username')) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('code')) { // Pin
            $query->where('code', 'like', '%' . $request->code . '%');
        }
        if ($request->filled('serial')) {
            $query->where('serial', 'like', '%' . $request->serial . '%');
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_range')) {
            $datesRange = explode(' - ', $request->date_range);
            if (count($datesRange) == 2) {
                $query->whereBetween('created_at', [
                    \Carbon\Carbon::createFromFormat('d/m/Y', $datesRange[0])->startOfDay(),
                    \Carbon\Carbon::createFromFormat('d/m/Y', $datesRange[1])->endOfDay()
                ]);
            }
        }

        $cards = $query->orderBy('id', 'desc')->limit(2000)->get();

        return view('admin.cards.index', compact('cards', 'stats', 'dates', 'data_chart'));
    }

    public function config()
    {
        $config = ApiConfig::where('name', 'charging_card')->first();
        $notice = \App\Models\SystemNotice::firstOrNew(['name' => 'page_deposit_card']); // Shared notice
        $depositPort = \App\Models\Config::where('name', 'deposit_port')->first()->value ?? [];

        return view('admin.cards.config', compact('config', 'notice', 'depositPort'));
    }

    public function updateConfig(Request $request)
    {
        $data = $request->validate([
            'status' => 'required|in:on,off',
            'api_url' => 'nullable|string',
            'partner_id' => 'nullable|string',
            'partner_key' => 'nullable|string',
            'fee' => 'nullable|integer|min:0', // Made nullable as global fee is removed from UI
            'types_list' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        // Parse types_list to fees and mapping array
        // Format: DISPLAY_CODE|API_CODE|FEE  or DISPLAY_CODE|FEE (backward compat)
        $fees = [];
        $mapping = [];
        $specific_fees = [];
        $allowed_denominations = [];

        if (!empty($data['types_list'])) {
            $lines = explode("\n", $data['types_list']);
            foreach ($lines as $line) {
                $parts = explode('|', trim($line));
                $count = count($parts);

                if ($count >= 4) {
                    // Format: DISPLAY_CODE|API_CODE|FEE|DENOM_CONFIG
                    $displayCode = strtoupper(trim($parts[0]));
                    $apiCode = strtoupper(trim($parts[1]));
                    $fee = floatval(preg_replace('/[^0-9.]/', '', trim($parts[2])));

                    $fees[$displayCode] = $fee;
                    $mapping[$displayCode] = $apiCode;

                    // Parse Denominations
                    $denomConfig = trim($parts[3]);
                    if ($denomConfig) {
                        $denoms = explode(',', $denomConfig);
                        $allowed = [];
                        $specs = [];
                        foreach ($denoms as $dItem) {
                            $dParts = explode(':', trim($dItem));
                            $amount = (int)preg_replace('/[^0-9]/', '', $dParts[0]);
                            if ($amount > 0) {
                                $allowed[] = $amount;
                                if (count($dParts) > 1) {
                                    $specFee = floatval(preg_replace('/[^0-9.]/', '', $dParts[1]));
                                    $specs[$amount] = $specFee;
                                }
                            }
                        }
                        $allowed_denominations[$displayCode] = $allowed;
                        if (!empty($specs)) {
                            $specific_fees[$displayCode] = $specs;
                        }
                    }

                }
                elseif ($count == 3) {
                    // Format: DISPLAY_CODE|API_CODE|FEE
                    $displayCode = strtoupper(trim($parts[0]));
                    $apiCode = strtoupper(trim($parts[1]));
                    $fee = floatval(preg_replace('/[^0-9.]/', '', trim($parts[2])));

                    $fees[$displayCode] = $fee;
                    $mapping[$displayCode] = $apiCode;
                }
                elseif ($count == 2) {
                    // Format: CODE|FEE (Simple) -> API_CODE = DISPLAY_CODE
                    $displayCode = strtoupper(trim($parts[0]));
                    $fee = floatval(preg_replace('/[^0-9.]/', '', trim($parts[1])));

                    $fees[$displayCode] = $fee;
                    $mapping[$displayCode] = $displayCode;
                }
            }
        }
        $data['fees'] = $fees;
        $data['mapping'] = $mapping;
        $data['specific_fees'] = $specific_fees;
        $data['allowed_denominations'] = $allowed_denominations;

        ApiConfig::updateOrCreate(
        ['name' => 'charging_card'],
        ['value' => $data]
        );

        // Consolidate 'status' to 'deposit_port' config for cards
        $depositPort = \App\Models\Config::where('name', 'deposit_port')->first()->value ?? [];
        $depositPort['cards'] = ($data['status'] === 'on') ? 1 : 0;
        \App\Models\Config::updateOrCreate(['name' => 'deposit_port'], ['value' => $depositPort]);

        // Consolidate 'note' to 'page_deposit_card' notice
        \App\Models\SystemNotice::updateOrCreate(
        ['name' => 'page_deposit_card'],
        ['value' => $data['note']]
        );

        Helper::addHistory('Cập nhật cấu hình đổi thẻ cào');

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật cấu hình thành công'
        ]);
    }

    public function delete(Request $request)
    {
        if (auth()->user()->hasRole('Product Manager')) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền ẩn lịch sử nạp tiền!'
            ], 403);
        }

        $payload = $request->validate([
            'ids' => 'required|array',
        ]);

        $protectedIds = Card::whereIn('id', $payload['ids'])
            ->whereHas('user', function($q) {
                $q->where('role', 'like', '%Product Manager%');
            })
            ->pluck('id')
            ->toArray();
        
        $idsToDelete = array_diff($payload['ids'], $protectedIds);

        if (count($idsToDelete) > 0) {
            Card::whereIn('id', $idsToDelete)->update([
                'admin_deleted_at' => now(),
            ]);
        }

        if (count($protectedIds) > 0) {
            return response()->json([
                'status' => true,
                'message' => 'Đã ẩn ' . count($idsToDelete) . ' lịch sử. Không thể ẩn ' . count($protectedIds) . ' lịch sử của Product Manager!',
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Lịch sử đã được ẩn thành công!',
        ]);
    }

    public function clearAll(Request $request)
    {
        if (auth()->user()->hasRole('Product Manager')) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền dọn dẹp lịch sử nạp tiền!'
            ], 403);
        }

        // Hide all cards except those belonging to Product Managers
        Card::whereNull('admin_deleted_at')
            ->where(function ($query) {
                $query->whereDoesntHave('user')
                    ->orWhereHas('user', function ($q) {
                        $q->where('role', 'not like', '%Product Manager%');
                    });
            })
            ->update(['admin_deleted_at' => now()]);

        return response()->json([
            'status' => true,
            'message' => 'Toàn bộ lịch sử nạp tiền đã được dọn dẹp thành công!',
        ]);
    }
}
