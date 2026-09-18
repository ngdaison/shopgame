<?php

namespace App\Http\Controllers\Admin\AccountV2;

use App\Http\Controllers\Controller;
use App\Models\ResourceV2;
use App\Models\ResourceV2O;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        return view('admin.accountsv2.orders.index');
    }

    public function refund(Request $request)
    {
        $payload = $request->validate([
            'id' => 'required|integer|exists:resource_v2_o,id',
        ]);

        try {
            DB::beginTransaction();

            $resource = ResourceV2O::lockForUpdate()->find($payload['id']);

            if (!$resource->buyer_name || $resource->order_status === 'Cancelled') {
                return response()->json([
                    'status' => false,
                    'message' => 'Tài khoản này chưa được bán hoặc đã hoàn tiền!',
                ]);
            }

            // Get all items in this order (excluding already refunded)
            $orderItems = ResourceV2O::lockForUpdate()
                ->where('buyer_code', $resource->buyer_code)
                ->whereNotNull('buyer_name')
                ->where('order_status', '!=', 'Cancelled')
                ->get();

            $totalRefund = 0;
            $refundCount = 0;
            $user = User::lockForUpdate()->where('username', $resource->buyer_name)->first();

            if ($user && $orderItems->count() > 0) {
                
                foreach ($orderItems as $item) {
                     $totalRefund += $item->buyer_paym;
                     $refundCount++;
                     
                     // Mark as refunded by setting order_status to Cancelled
                     $item->update([
                        'order_status' => 'Cancelled',
                     ]);
                }

                $user->increment('balance', $totalRefund);

                $user->transactions()->create([
                    'code'           => 'RF-' . Helper::randomString(8, true),
                    'amount'         => $totalRefund,
                    'balance_after'  => $user->balance,
                    'balance_before' => $user->balance - $totalRefund,
                    'type'           => 'refund',
                    'status'         => 'paid',
                    'content'        => 'Hoàn tiền đơn hàng #' . $resource->buyer_code . ' (SL: ' . $refundCount . ')',
                    'user_id'        => $user->id,
                    'username'       => $user->username,
                ]);
            }

            Helper::addHistory('[V2] Hoàn tiền đơn hàng #' . $payload['id'] . ' cho thành viên ' . ($user ? $user->username : 'Unknown'));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Hoàn tiền thành công!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request)
    {
        $payload = $request->validate([
            'buyer_code' => 'required|string',
            'status' => 'nullable|in:Completed,Processing,Cancelled',
            'admin_note' => 'nullable|string',
            'refund_quantity' => 'nullable|integer|min:0',
            'refund_reason' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Get all items in this order
            $orderItems = ResourceV2O::lockForUpdate()
                ->where('buyer_code', $payload['buyer_code'])
                ->whereNotNull('buyer_name')
                ->get();

            if ($orderItems->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy đơn hàng!',
                ]);
            }

            $firstItem = $orderItems->first();
            $user = User::lockForUpdate()->where('username', $firstItem->buyer_name)->first();

            // Handle partial refund
            $refundQty = $payload['refund_quantity'] ?? 0;
            if ($refundQty > 0) {
                $availableItems = $orderItems->where('order_status', '!=', 'Cancelled');
                
                if ($refundQty > $availableItems->count()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Số lượng hoàn tiền vượt quá số lượng còn lại!',
                    ]);
                }

                $totalRefund = 0;
                $refundedCount = 0;
                
                foreach ($availableItems->take($refundQty) as $item) {
                    $totalRefund += $item->buyer_paym;
                    $item->update(['order_status' => 'Cancelled']);
                    $refundedCount++;
                }

                if ($user && $totalRefund > 0) {
                    $user->increment('balance', $totalRefund);
                    $user->transactions()->create([
                        'code' => 'RF-' . Helper::randomString(8, true),
                        'amount' => $totalRefund,
                        'balance_after' => $user->balance,
                        'balance_before' => $user->balance - $totalRefund,
                        'type' => 'refund',
                        'status' => 'paid',
                        'content' => 'Hoàn tiền một phần đơn hàng #' . $payload['buyer_code'] . ' (SL: ' . $refundedCount . ') - ' . ($payload['refund_reason'] ?? ''),
                        'user_id' => $user->id,
                        'username' => $user->username,
                    ]);
                }

                Helper::addHistory('[V2] Hoàn tiền một phần đơn hàng #' . $payload['buyer_code'] . ' (SL: ' . $refundedCount . ')');
            }

            // Handle status change (full refund if Cancelled)
            if (isset($payload['status'])) {
                if ($payload['status'] === 'Cancelled') {
                    // Full refund
                    $availableItems = $orderItems->where('order_status', '!=', 'Cancelled');
                    $totalRefund = 0;
                    $refundCount = 0;

                    foreach ($availableItems as $item) {
                        $totalRefund += $item->buyer_paym;
                        $item->update(['order_status' => 'Cancelled']);
                        $refundCount++;
                    }

                    if ($user && $totalRefund > 0) {
                        $user->increment('balance', $totalRefund);
                        $user->transactions()->create([
                            'code' => 'RF-' . Helper::randomString(8, true),
                            'amount' => $totalRefund,
                            'balance_after' => $user->balance,
                            'balance_before' => $user->balance - $totalRefund,
                            'type' => 'refund',
                            'status' => 'paid',
                            'content' => 'Hoàn tiền đơn hàng #' . $payload['buyer_code'] . ' (SL: ' . $refundCount . ')',
                            'user_id' => $user->id,
                            'username' => $user->username,
                        ]);
                    }

                    Helper::addHistory('[V2] Hủy đơn hàng #' . $payload['buyer_code']);
                } else {
                    // Update status for non-cancelled items
                    $orderItems->where('order_status', '!=', 'Cancelled')
                        ->each(fn($item) => $item->update(['order_status' => $payload['status']]));
                }
            }

            /*
            // Update admin note (for all items in order)
            if (isset($payload['admin_note'])) {
                $orderItems->each(fn($item) => $item->update(['admin_note' => $payload['admin_note']]));
            }
            */

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Cập nhật đơn hàng thành công!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ]);
        }
    }

    public function delete(Request $request)
    {
        if (auth()->user()->hasRole('Product Manager')) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền ẩn đơn hàng!'
            ], 403);
        }

        $payload = $request->validate([
            'buyer_code' => 'required|string',
        ]);

        $protected = ResourceV2O::where('buyer_code', $payload['buyer_code'])
            ->whereHas('user', function($q) {
                $q->where('role', 'like', '%Product Manager%');
            })
            ->exists();
        
        if ($protected) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể ẩn đơn hàng của Product Manager!',
            ], 403);
        }

        ResourceV2O::where('buyer_code', $payload['buyer_code'])->update([
            'admin_deleted_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Đơn hàng đã được ẩn thành công!',
        ]);
    }

    public function clearAll(Request $request)
    {
        if (auth()->user()->hasRole('Product Manager')) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn không có quyền dọn dẹp đơn hàng!'
            ], 403);
        }

        // Hide all account v2 orders except those belonging to Product Managers
        ResourceV2O::whereNull('admin_deleted_at')
            ->where(function ($query) {
                // Using join with users through buyer_name (username)
                $query->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('users')
                        ->whereRaw('users.username = resource_v2_o.buyer_name')
                        ->where('users.role', 'like', '%Product Manager%');
                });
            })
            ->update(['admin_deleted_at' => now()]);

        return response()->json([
            'status' => true,
            'message' => 'Toàn bộ đơn hàng đã được dọn dẹp thành công!',
        ]);
    }
}
