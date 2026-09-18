<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
// use App\Models\BankAccount; // Removed
use Illuminate\Http\Request;

class BankingController extends Controller
{
  public function index(Request $request)
  {
    // Get Active Banks from JSON
    $bankConfig = \App\Models\BankConfig::first();
    $banks = collect($bankConfig->bank_accounts ?? [])->filter(function($acc) {
        $acc = (object) $acc;
        return isset($acc->status) && $acc->status;
    })->values();

    return response()->json([
      'data'    => $banks,
      'status'  => 200,
      'message' => 'Lấy danh sách ngân hàng thành công',
    ], 200);
  }
}
