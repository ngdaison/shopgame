<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use App\Models\InventoryVar;

use App\Models\User;
use Helper;
use Illuminate\Http\Request;

class GiftRewardController extends Controller
{
  public function claim(Request $request)
  {
    $user = User::find($request->user()->id);

    if ($user->received_gift !== false) {
      return response()->json([
        'status'  => 400,
        'message' => 'Bạn đã nhận thưởng trước đó, không nhận được nữa.',
      ], 400);
    }

    $config      = Helper::getConfig('get_gift');
    $inventories = InventoryVar::where('is_active', true)->get();

    if (!isset($config['status']) || !$config['status']) {
      return response()->json([
        'status'  => 400,
        'message' => 'Chức năng này hiện không hỗ trợ sử dụng.',
      ], 400);
    }

    if (!isset($config['min']) || !isset($config['max'])) {
      return response()->json([
        'status'  => 400,
        'message' => 'Lỗi hệ thống, vui lòng quay lại sau.',
      ], 400);
    }

    if (!is_numeric($config['min']) || !is_numeric($config['max'])) {
      return response()->json([
        'status'  => 400,
        'message' => 'Lỗi hệ thống, vui lòng quay lại sau.',
      ], 400);
    }

    if ($inventories->isEmpty()) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không có phần thưởng nào khả dụng để nhận.',
      ], 400);
    }

    $user->update([
      'received_gift' => true,
    ]);
    $rewarded = [];

    foreach ($inventories as $inventory) {
      $giftValue = mt_rand($config['min'], $config['max']);

      if ($giftValue <= 0) {
        continue;
      }

      $inventoryStock = $user->inventories()->where('var_id', $inventory->id)->first();

      if ($inventoryStock === null) {
        $inventoryStock = $user->inventories()->create([
          'name'      => $inventory->name,
          'value'     => 0,
          'var_id'    => $inventory->id,
          'user_id'   => $user->id,
          'username'  => $user->username,
          'is_active' => true,
        ]);
      }

      if ($inventoryStock === null) {
        return response()->json([
          'data'    => null,
          'status'  => 400,
          'message' => 'Không xử lý được dữ liệu, vui lòng thử lại sau #4',
        ], 400);
      }

      $inventoryStock->increment('value', $giftValue);

      $inventoryLog = InventoryLog::create([
        'unit'         => $inventory->unit,
        'unit_id'      => $inventory->id,

        'type'         => 'spin',
        'value'        => $giftValue,
        'content'      => 'Nhận thưởng cho người mới',

        'after_value'  => $inventoryStock->value,
        'before_value' => $inventoryStock->value - $giftValue,

        'user_id'      => $user->id,
        'username'     => $user->username,

        'source'       => 'gift_reward',
        'source_id'    => -1,
      ]);

      $rewarded[] = "<span class='text-primary'>{$giftValue}</span> <small class='text-danger-500'>{$inventory->unit}</small>";
    }

    return response()->json([
      'status'  => 200,
      'message' => 'Bạn đã nhận được ' . implode(', ', $rewarded) . '!',
    ]);
  }
}
