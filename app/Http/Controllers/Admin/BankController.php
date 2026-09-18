<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banking;
use App\Models\BankConfig;
use App\Models\Transaction;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Config;
use Carbon\Carbon;

class BankController extends Controller
{
  public function index()
  {
    return redirect()->route('admin.deposit.banks');
  }

  public function deposit()
  {
    // Stats from Banking History Table
    $query = Banking::query();
    auth()->user()->applyHistoryLimit($query);

    $success_statuses = ['completed', 'success', 'paid'];

    $total = (clone $query)->whereIn('status', $success_statuses)->sum('amount');
    $week = (clone $query)->whereIn('status', $success_statuses)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('amount');
    $month = (clone $query)->whereIn('status', $success_statuses)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount');
    $today = (clone $query)->whereIn('status', $success_statuses)->whereDate('created_at', now()->toDateString())->sum('amount');

    $stats['banks'] = [
      'total' => $total,
      'month' => $month,
      'week' => $week,
      'today' => $today,
    ];

    $stats['t_banks'] = [
      'total' => 'Toàn thời gian',
      'month' => 'Tháng ' . now()->format('m'),
      'week' => 'Trong tuần',
      'today' => 'Hôm nay',
    ];

    // Chart Data
    $startDate = now()->startOfMonth();
    $endDate = now()->endOfMonth();

    $chartQuery = Banking::whereIn('status', $success_statuses)
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

    // Get Banks from JSON
    $bankConfig = BankConfig::first();
    // Convert array to objects/collections for view compatibility if needed, OR adjust view.
    // Let's pass array directly but mapped to objects for easier Blade usage OR modify Blade.
    // For minimal view changes, converting to object helps:
    $banks = collect($bankConfig->bank_accounts ?? [])->map(function ($item) {
      return (object)$item;
    });

    $config = Config::where('name', 'deposit_info')->first();

    return view('admin.banks.deposit', compact('stats', 'banks', 'dates', 'data_chart', 'config'));
  }

  public function config()
  {
    // Get bank accounts from JSON
    $bankConfig = BankConfig::first();
    $banks = collect($bankConfig->bank_accounts ?? [])->map(function ($item) {
      return (object)$item;
    });

    // Using legacy Config model or new bank_config.config column?
    // Plan said "updateConfig" updates the new column. 
    // "config" method view likely expects $config object.
    // Let's use the new column data if available, or fallback to legacy.

    $configData = $bankConfig->config ?? [];

    // Mocking a config object structure for view
    $config = new Config();
    $config->name = 'deposit_info';
    $config->value = $configData;

    $notice = \App\Models\SystemNotice::where('name', 'page_deposit_bank')->first();
    return view('admin.banks.config', compact('config', 'banks', 'notice'));
  }

  public function updateConfig(Request $request)
  {
    $data = $request->validate([
      'status' => 'required|in:on,off',
      'prefix' => 'required|string',
      'discount' => 'nullable|numeric',
      'min' => 'required|numeric',
      'max' => 'required|numeric',
      'note' => 'nullable|string',
    ]);

    $bankConfig = BankConfig::firstOrNew(['id' => 1]);

    $newConfig = [
      'prefix' => $data['prefix'],
      'discount' => $data['discount'] ?? 0,
      'status' => $data['status'] === 'on' ? 1 : 0,
      'description' => '',
      'min' => $data['min'],
      'max' => $data['max'],
    ];

    $bankConfig->config = $newConfig;
    $bankConfig->save();

    // Maintain sync with legacy configs if critical
    Config::updateOrCreate(
    ['name' => 'deposit_info'],
    ['value' => $newConfig]
    );

    \App\Models\SystemNotice::updateOrCreate(
    ['name' => 'page_deposit_bank'],
    ['value' => $data['note']]
    );

    if ($request->hasFile('qr_code')) {
      $path = $request->file('qr_code')->store('banks/qr_codes', 'public');
      $qrCodeUrl = asset('storage/' . $path);

      // Update in bank config
      $bankConfig->qr_code = $qrCodeUrl;
      $bankConfig->save();

    // Also update in legacy config if needed, though structure might differ
    }

    Helper::addHistory('Cập nhật cấu hình nạp tiền ngân hàng');

    return response()->json([
      'status' => true,
      'message' => 'Cập nhật cấu hình thành công'
    ]);
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'name' => 'required|string',
      'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
      'owner' => 'required|string',
      'number' => 'required|string',
      'branch' => 'nullable|string',
      'password' => 'nullable|string',
      'token' => 'nullable|string',
      'provider' => 'nullable|string',
      'bank_code' => 'nullable|string',
      'status' => 'nullable|boolean',
    ]);

    if (!isset($payload['status']))
      $payload['status'] = true;

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'));
    }

    $payload['bank_name'] = $payload['name'];
    unset($payload['name']);

    // Generate UUID
    $payload['id'] = (string)\Illuminate\Support\Str::uuid();
    $payload['created_at'] = now()->toDateTimeString();

    $bankConfig = BankConfig::firstOrNew(['id' => 1]);
    $accounts = $bankConfig->bank_accounts ?? [];
    $accounts[] = $payload; // Append new account
    $bankConfig->bank_accounts = $accounts;
    $bankConfig->save();

    Helper::addHistory('Thêm tài khoản ' . $payload['number'] . ', ngân hàng ' . $payload['bank_name']);

    return response()->json([
      'status' => true,
      'message' => 'Thêm tài khoản ngân hàng thành công.',
    ]);
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required', // String UUID or int
      'name' => 'required|string',
      'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
      'owner' => 'required|string',
      'number' => 'required|string',
      'branch' => 'nullable|string',
      'password' => 'nullable|string',
      'token' => 'nullable|string',
      'provider' => 'nullable|string',
      'bank_code' => 'nullable|string',
      'status' => 'required|boolean',
    ]);

    $bankConfig = BankConfig::firstOrFail();
    $accounts = $bankConfig->bank_accounts ?? [];

    $foundIndex = -1;
    foreach ($accounts as $index => $acc) {
      if (($acc['id'] ?? null) == $request->id) {
        $foundIndex = $index;
        break;
      }
    }

    if ($foundIndex === -1) {
      return response()->json(['status' => false, 'message' => 'Không tìm thấy tài khoản']);
    }

    $currentAccount = $accounts[$foundIndex];

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'));
    }
    else {
      $payload['image'] = $currentAccount['image'] ?? '';
    }

    $payload['bank_name'] = $payload['name'];
    unset($payload['name']);
    $payload['id'] = $request->id; // Preserve ID

    $accounts[$foundIndex] = array_merge($currentAccount, $payload); // Merge to keep other fields?

    $bankConfig->bank_accounts = $accounts;
    $bankConfig->save();

    Helper::addHistory('Cập nhật tài khoản ngân hàng ' . $payload['number'] . ' #' . $payload['id']);

    return response()->json([
      'status' => true,
      'message' => 'Cập nhật tài khoản ngân hàng #' . $payload['id'] . ' thành công.',
    ]);
  }

  public function delete(Request $request)
  {
    $request->validate([
      'id' => 'required',
    ]);

    $bankConfig = BankConfig::firstOrFail();
    $accounts = $bankConfig->bank_accounts ?? [];

    $newAccounts = [];
    $found = false;
    foreach ($accounts as $acc) {
      if (($acc['id'] ?? null) == $request->id) {
        $found = true;
        Helper::addHistory('Xóa tài khoản ngân hàng ' . ($acc['number'] ?? '') . ' #' . ($acc['id'] ?? ''));
        continue;
      }
      $newAccounts[] = $acc;
    }

    if (!$found) {
      return response()->json(['status' => false, 'message' => 'Không tìm thấy tài khoản']);
    }

    $bankConfig->bank_accounts = $newAccounts;
    $bankConfig->save();

    return response()->json([
      'status' => true,
      'message' => 'Xóa tài khoản ngân hàng thành công.',
    ]);
  }
}
