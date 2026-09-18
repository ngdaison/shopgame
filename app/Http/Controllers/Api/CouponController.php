<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
            'cart_total' => 'required|numeric|min:0',
            'product_ids' => 'nullable|array', // ['account-1', 'item-2', ...]
        ]);

        $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

        if (!$coupon) {
            return response()->json([
                'status' => false,
                'message' => 'Mã giảm giá không tồn tại.'
            ]);
        }

        $cartTotal = $request->cart_total;
        $productIds = $request->product_ids ?? [];

        if (!$coupon->isApplicable($cartTotal, $productIds)) {
            // Determine why failed for better message
            if ($coupon->min_order_value && $cartTotal < $coupon->min_order_value) {
                return response()->json([
                    'status' => false,
                    'message' => 'Đơn hàng chưa đạt giá trị tối thiểu: ' . number_format($coupon->min_order_value)
                ]);
            }
            if (!empty($coupon->product_ids)) {
                 return response()->json([
                    'status' => false,
                    'message' => 'Mã giảm giá không áp dụng cho các sản phẩm trong giỏ.'
                ]);
            }
            return response()->json([
                'status' => false,
                'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.'
            ]);
        }

        // Calculate discount
        $discountAmount = $coupon->calculateDiscount($cartTotal);

        return response()->json([
            'status' => true,
            'message' => 'Áp dụng mã giảm giá thành công.',
            'data' => [
                'code' => $coupon->coupon_code,
                'discount_amount' => $discountAmount,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
            ]
        ]);
    }
}
