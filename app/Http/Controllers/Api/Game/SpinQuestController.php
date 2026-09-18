<?php

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use App\Models\ServiceCategory;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;

class SpinQuestController extends Controller
{
  public function turn(Request $request)
  {
    $payload   = $request->validate([
      'id' => 'required|integer',
    ]);
    $user      = User::findOrFail(auth()->user()->id);
    $unit      = Helper::getConfig('mng_withdraw') ?? null;
    $type      = 'legacy';
    $spinQuest = ServiceCategory::where('id', $payload['id'])
                  ->where('product_type', 'spin')
                  ->where('status', true)
                  ->firstOrFail();

    if (!$unit && !$spinQuest->invar_id) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không có dữ liệu phần thưởng, vui lòng thử lại #1',
      ], 400);
    }

    if ($unit && !$spinQuest->invar_id) {
      $unit = $unit['unit'] ?? null;

      if (!$unit) {
        return response()->json([
          'status'  => 400,
          'message' => 'Không có dữ liệu phần thưởng, vui lòng thử lại #2',
        ], 400);
      }

      $type = 'legacy';
    } else if ($spinQuest->invar_id) {
      $unit = $spinQuest->inventoryVar;

      if (!$unit) {
        return response()->json([
          'status'  => 400,
          'message' => 'Không có dữ liệu phần thưởng, vui lòng thử lại #3',
        ], 400);
      }

      $type = 'inventory';
    }

    if ($user->balance < $spinQuest->price) {
      return response()->json([
        'status'  => 400,
        'message' => 'Số dư không đủ để chơi trò chơi này, vui lòng nạp thêm.',
      ], 400);
    }

    if ($spinQuest->canPlay() !== true) {
      return response()->json([
        'status'  => 400,
        'message' => 'Trò chơi đang bảo trì, vui lòng thử lại sau #1',
      ], 400);
    }

    $result = $spinQuest->playGame();

    if (!$result || ($result['data'] === null && $result['location'] === null)) {
      return response()->json([
        'status'  => 400,
        'message' => 'Trò chơi đang bảo trì, vui lòng thử lại sau #2',
      ], 400);
    }

    if (!$user->decrement('balance', $spinQuest->price)) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không thể thực hiện trừ tiền trong tài khoản của bạn #3',
      ], 400);
    }

    $kqNhan = $result['data']['value'];
    $spinQuest->increment('play_times', 1);

    if (!is_numeric($kqNhan)) {
      $exp = explode('-', $kqNhan);
      if (count($exp) === 2 && is_numeric($exp[0]) && is_numeric($exp[1])) {
        $kqNhan = mt_rand($exp[0], $exp[1]);
      } else {
        $user->increment('balance', $spinQuest->price);
        return response()->json([
          'status'  => 400,
          'message' => 'Không xử lý được dữ liệu nhận được từ trò chơi này #6',
        ], 400);
      }
    }

    if ($type === 'legacy') {
      $user->transactions()->create([
        'code'           => 'MNG-' . Helper::randomString(7, true),
        'amount'         => $spinQuest->price,
        'cost_amount'    => 0,
        'balance_after'  => $user->balance,
        'balance_before' => $user->balance + $spinQuest->price,
        'type'           => 'play-game',
        'extras'         => [
          'id'   => $spinQuest->id,
          'type' => 'spin-quest',
        ],
        'status'         => 'paid',
        'content'        => "Chơi trò chơi {$spinQuest->name}, rev: {$kqNhan} {$unit}",
        'user_id'        => $user->id,
        'username'       => $user->username,
      ]);

      $user->increment('balance_2', $kqNhan);

      $content = "Bạn đã nhận được {$kqNhan} {$unit} từ trò chơi này!";
    } else if ($type === 'inventory') {
      $user->transactions()->create([
        'code'           => 'MNG-' . Helper::randomString(7, true),
        'amount'         => $spinQuest->price,
        'cost_amount'    => 0,
        'balance_after'  => $user->balance,
        'balance_before' => $user->balance + $spinQuest->price,
        'type'           => 'play-game',
        'extras'         => [
          'id'   => $spinQuest->id,
          'type' => 'spin-quest',
        ],
        'status'         => 'paid',
        'content'        => "Chơi trò chơi {$spinQuest->name}, rev: {$kqNhan} {$unit->unit}",
        'user_id'        => $user->id,
        'username'       => $user->username,
      ]);

      $inventoryStock = $user->inventories()->where('var_id', $unit->id)->first();

      if ($inventoryStock === null) {
        $inventoryStock = $user->inventories()->create([
          'name'      => $unit->name,
          'value'     => 0,
          'var_id'    => $unit->id,
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

      $inventoryStock->increment('value', $kqNhan);

      $respData = [
        'prize'  => [
          'name'  => $unit->unit,
          'value' => $kqNhan,
          'index' => $result['location'],
        ],
        'unit'   => $unit->unit,
        'value'  => $kqNhan,
        'amount' => $inventoryStock->value,
        'review' => 'Bạn đã quay trúng ' . number_format($kqNhan, 0, ',', '.') . ' ' . $unit->unit . '!',
      ];

      $inventoryLog = InventoryLog::create([
        'unit'         => $respData['unit'],
        'unit_id'      => $unit->id,

        'type'         => 'spin',
        'value'        => $respData['value'],
        'content'      => $respData['review'],

        'after_value'  => $inventoryStock->value,
        'before_value' => $inventoryStock->value - $kqNhan,

        'user_id'      => $user->id,
        'username'     => $user->username,

        'source'       => 'lucky_wheel',
        'source_id'    => $spinQuest->id,
      ]);
      $content      = $respData['review'];
    }

    return response()->json([
      'status'   => 200,
      'message'  => $content,
      'location' => $result['location'] ?? null,
    ]);
  }

  public function turnTest(Request $request)
  {
    $payload   = $request->validate([
      'id' => 'required|integer',
    ]);
    $user      = User::findOrFail(auth()->user()->id);
    $unit      = Helper::getConfig('mng_withdraw') ?? null;
    $type      = 'legacy';
    $spinQuest = ServiceCategory::where('id', $payload['id'])
                  ->where('product_type', 'spin')
                  ->where('status', true)
                  ->firstOrFail();

    if (!$unit && !$spinQuest->invar_id) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không có dữ liệu phần thưởng, vui lòng thử lại #1',
      ], 400);
    }

    if ($unit && !$spinQuest->invar_id) {
      $unit = $unit['unit'] ?? null;

      if (!$unit) {
        return response()->json([
          'status'  => 400,
          'message' => 'Không có dữ liệu phần thưởng, vui lòng thử lại #2',
        ], 400);
      }

      $type = 'legacy';
    } else if ($spinQuest->invar_id) {
      $unit = $spinQuest->inventoryVar;

      if (!$unit) {
        return response()->json([
          'status'  => 400,
          'message' => 'Không có dữ liệu phần thưởng, vui lòng thử lại #3',
        ], 400);
      }

      $type = 'inventory';
    }

    if ($spinQuest->canPlay() !== true) {
      return response()->json([
        'status'  => 400,
        'message' => 'Trò chơi đang bảo trì, vui lòng thử lại sau #1',
      ], 400);
    }

    $result = $spinQuest->playGame(true);

    if (!$result || ($result['data'] === null && $result['location'] === null)) {
      return response()->json([
        'status'  => 400,
        'message' => 'Trò chơi đang bảo trì, vui lòng thử lại sau #2',
      ], 400);
    }

    $kqNhan = $result['data']['value'];

    if (!is_numeric($kqNhan)) {
      $exp = explode('-', $kqNhan);
      if (count($exp) === 2 && is_numeric($exp[0]) && is_numeric($exp[1])) {
        $kqNhan = mt_rand($exp[0], $exp[1]);
      } else {
        return response()->json([
          'status'  => 400,
          'message' => 'Không xử lý được dữ liệu nhận được từ trò chơi này #6',
        ], 400);
      }
    }

    if ($type === 'legacy') {
      $content = "Bạn đã nhận được {$kqNhan} {$unit} từ trò chơi này!";
    } else if ($type === 'inventory') {
      $respData = [
        'prize'  => [
          'name'  => $unit->unit,
          'value' => $kqNhan,
          'index' => $result['location'],
        ],
        'unit'   => $unit->unit,
        'value'  => $kqNhan,
        'amount' => 0,
        'review' => 'Bạn đã quay trúng ' . number_format($kqNhan, 0, ',', '.') . ' ' . $unit->unit . '!',
      ];

      $content = $respData['review'];
    }

    return response()->json([
      'status'   => 200,
      'message'  => $content,
      'location' => $result['location'] ?? null,
    ]);
  }
}
