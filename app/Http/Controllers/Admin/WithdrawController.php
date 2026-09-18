<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawRequest;
use Helper;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{
  public function index(Request $request)
  {
    $query = WithdrawRequest::with(['inventoryVar', 'user']);

    $limitDate = auth()->user()->getHistoryLimitDate();
    if ($limitDate) {
      $query->where('created_at', '>=', $limitDate);
    }

    $records = $query->orderBy('id', 'desc')
      ->get();

    $pageTitle = 'Admin: Customer Withdraws';

    return view('admin.withdraws.index', compact('records', 'pageTitle'));
  }

  public function update(Request $request, $id)
  {
    $payload = $request->validate([
      'status'     => 'required|string|in:Pending,Approved,Rejected',
      'admin_note' => 'nullable|string',
    ]);

    $withdrawal = WithdrawRequest::findOrFail($id);

    $withdrawal->update($payload);

    Helper::addHistory("Cập nhật trạng thái yêu cầu rút thưởng #{$withdrawal->id} thành {$payload['status']}");

    session()->flash('success', 'Cập nhật trạng thái yêu cầu rút thưởng thành công.');

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật trạng thái yêu cầu rút thưởng thành công.',
    ]);
  }
}
