<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
  public function index(Request $request)
  {
    $query = Invoice::with('user:id,username,domain')
      ->whereNull('admin_deleted_at')
      ->orderBy('id', 'desc');

    // Filter by Role Limit Date
    $limitDate = auth()->user()->getHistoryLimitDate();
    if ($limitDate) {
        $query->where('created_at', '>=', $limitDate);
    }

    if ($request->has('type')) {
      $query->where('type', $request->type);
    }

    $invoices = $query->get();

    return view('admin.invoices.index', compact('invoices'));
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required|exists:invoices,id',
      'type' => 'required|in:paid,cancelled',
    ]);

    $invoice = Invoice::find($payload['id']);

    if ($invoice->status !== 'processing') {
      return response()->json([
        'status' => false,
        'message' => 'Hoá đơn đã được thanh toán hoặc đã bị hủy',
      ]);
    }

    if ($payload['type'] === 'paid') {
      $invoice->status = 'completed';
      $invoice->paid_at = now();
      $invoice->description = 'Thanh toán bởi admin';
      $invoice->save();

      $client = User::find($invoice->user_id);

      if ($client) {
        $client->increment('balance', $invoice->amount);
        $client->increment('total_deposit', $invoice->amount);

        $client->transactions()->create([
          'code' => $invoice->code,
          'amount' => $invoice->amount,
          'balance_before' => $client->balance - $invoice->amount,
          'balance_after' => $client->balance,
          'type' => 'deposit-banking',
          'extras' => [],
          'status' => 'completed',
          'content' => 'Admin đã cập nhật hoá đơn',
          'user_id' => $client->id,
          'username' => $client->username,
        ]);
      }
    }
    else {
      $invoice->status = 'cancelled';
      $invoice->description = 'Huỷ bởi admin';
      $invoice->save();
    }

    session()->flash('success', 'Cập nhật hoá đơn thành công');

    return response()->json([
      'status' => true,
      'message' => 'Cập nhật hoá đơn thành công',
    ]);
  }

  public function delete(Request $request)
  {
    if (auth()->user()->hasRole('Product Manager')) {
      return response()->json([
        'status' => false,
        'message' => 'Bạn không có quyền ẩn hoá đơn!'
      ], 403);
    }

    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'integer|exists:invoices,id',
    ]);

    $protectedIds = Invoice::whereIn('id', $request->ids)
      ->whereHas('user', function ($q) {
        $q->where('role', 'like', '%Product Manager%');
      })
      ->pluck('id')
      ->toArray();

    $idsToDelete = array_diff($request->ids, $protectedIds);

    if (count($idsToDelete) > 0) {
      Invoice::whereIn('id', $idsToDelete)->update([
        'admin_deleted_at' => now(),
      ]);
    }

    if (count($protectedIds) > 0) {
      return response()->json([
        'status' => true,
        'message' => 'Đã ẩn ' . count($idsToDelete) . ' hoá đơn. Không thể ẩn ' . count($protectedIds) . ' hoá đơn của Product Manager!',
      ]);
    }

    return response()->json([
      'status' => true,
      'message' => 'Hoá đơn đã được ẩn thành công!',
    ]);
  }

  public function clearAll(Request $request)
  {
    if (auth()->user()->hasRole('Product Manager')) {
      return response()->json([
        'status' => false,
        'message' => 'Bạn không có quyền dọn dẹp hoá đơn!'
      ], 403);
    }

    Invoice::whereNull('admin_deleted_at')
      ->where(function ($query) {
        $query->whereDoesntHave('user')
          ->orWhereHas('user', function ($q) {
            $q->where('role', 'not like', '%Product Manager%');
          });
      })
      ->update(['admin_deleted_at' => now()]);

    return response()->json([
      'status' => true,
      'message' => 'Toàn bộ hoá đơn đã được dọn dẹp thành công!',
    ]);
  }
}
