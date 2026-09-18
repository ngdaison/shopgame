<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;

class WithdrawV2Controller extends Controller
{
  public function index()
  {
    $inventories = Inventory::where('user_id', auth()->id())
      ->where('value', '>', 0)
      ->with('inventory_var')
      ->get();

    $pageTitle = 'Rút Thưởng Game';

    return view('account.withdraw.index-v2', compact('inventories', 'pageTitle'));
  }

  public function forms(Request $request)
  {
    $id = $request->query('id');
    $inventory = Inventory::where('user_id', auth()->id())
      ->where('id', $id)
      ->with('inventory_var')
      ->firstOrFail();

    return view('account.withdraw.forms-v2', compact('inventory'));
  }
}
