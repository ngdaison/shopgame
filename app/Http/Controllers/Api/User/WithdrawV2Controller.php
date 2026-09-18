<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\WithdrawRequest;
use Helper;
use Illuminate\Http\Request;

class WithdrawV2Controller extends Controller
{
  public function histories(Request $request)
  {
    $limit = $request->query('limit', 20);
    $search = $request->query('search');

    $records = WithdrawRequest::where('user_id', auth()->id())
      ->when($request->filled('search'), function ($query) use ($request) {
        $search = $request->query('search');
        $query->where(function ($q) use ($search) {
          $q->where('code', 'like', "%{$search}%")
            ->orWhere('name', 'like', "%{$search}%");
        });
      })
      ->orderBy('id', 'desc')
      ->paginate($limit);

    return response()->json($records);
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'id'          => 'required|exists:inventories,id',
      'amount'      => 'required|integer|min:1',
      'arr_inputs'  => 'required|array',
    ]);

    $inventory = Inventory::where('user_id', auth()->id())
      ->where('id', $payload['id'])
      ->with('inventory_var')
      ->firstOrFail();

    $var = $inventory->inventory_var;

    if (!$var || !$var->is_active) {
      return response()->json(['message' => 'Loại vật phẩm này hiện không hỗ trợ rút.'], 400);
    }

    if ($payload['amount'] > $inventory->value) {
      return response()->json(['message' => 'Số dư vật phẩm không đủ.'], 400);
    }

    if ($payload['amount'] < $var->min_withdraw) {
      return response()->json(['message' => 'Số lượng rút tối thiểu là ' . number_format($var->min_withdraw)], 400);
    }

    if ($payload['amount'] > $var->max_withdraw && $var->max_withdraw > 0) {
      return response()->json(['message' => 'Số lượng rút tối đa là ' . number_format($var->max_withdraw)], 400);
    }

    // Process inputs
    $user_inputs = [];
    foreach ($var->form_inputs as $index => $input) {
      $val = $payload['arr_inputs'][$index] ?? null;
      if (empty($val)) {
        return response()->json(['message' => 'Vui lòng nhập đầy đủ thông tin: ' . $input['label']], 400);
      }
      $user_inputs[] = [
        'label' => $input['label'],
        'value' => $val
      ];
    }

    // Create withdrawal request
    $withdrawRequest = WithdrawRequest::create([
      'code'        => 'WD' . time() . rand(100, 999),
      'user_id'     => auth()->id(),
      'username'    => auth()->user()->username,
      'var_id'      => $var->id,
      'unit'        => $var->unit,
      'name'        => $var->name,
      'amount'      => $payload['amount'],
      'after_value' => $inventory->value - $payload['amount'],
      'user_inputs' => $user_inputs,
      'status'      => 'Pending',
    ]);

    // Deduct inventory
    $inventory->decrement('value', $payload['amount']);

    // Log history
    Helper::addHistory("Yêu cầu rút thưởng {$payload['amount']} {$var->unit} cho game {$var->name}");

    return response()->json([
      'status'  => 200,
      'message' => 'Gửi yêu cầu rút thưởng thành công! Vui lòng chờ admin duyệt.',
    ]);
  }
}
