<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
  public function index(Request $request)
  {
    $query = Inventory::query();
    auth()->user()->applyHistoryLimit($query);
    $inventories = $query->orderBy('id', 'desc')->get();

    return view('admin.inventory.index', compact('inventories'));
  }
}
