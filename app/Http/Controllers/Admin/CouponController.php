<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Group;
use App\Models\GroupV2;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::orderBy('created_at', 'desc')->get();

        // Fetch groups for the product selector
        $products = collect();

        $products = $products->merge(Group::all()->map(function ($g) {
            $g->uni_id = 'account-' . $g->id;
            $g->type_label = 'Shop Nick';
            $g->display_name = $g->name;
            return $g;
        }));

        $products = $products->merge(GroupV2::all()->map(function ($g) {
            $g->uni_id = 'account_v2-' . $g->id;
            $g->type_label = 'Shop Nick V2';
            $g->display_name = $g->name;
            return $g;
        }));

        $products = $products->merge(\App\Models\GBGroup::all()->map(function ($g) {
            $g->uni_id = 'boosting-' . $g->id;
            $g->type_label = 'Cày Thuê';
            $g->display_name = $g->name;
            return $g;
        }));

        $products = $products->merge(\App\Models\ItemGroup::all()->map(function ($g) {
            $g->uni_id = 'item-' . $g->id;
            $g->type_label = 'Vật Phẩm';
            $g->display_name = $g->name;
            return $g;
        }));

        return view('admin.coupons.index', compact('coupons', 'products'));
    }

    public function show($id)
    {
        $coupon = Coupon::findOrFail($id);

        $transactions = \App\Models\Transaction::where('extras->coupon_code', $coupon->coupon_code)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.coupons.show', compact('coupon', 'transactions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'coupon_code' => [
                'required',
                'string',
                'max:190',
                Rule::unique('coupons')->ignore($request->id),
            ],
            'discount_type' => 'required|in:percentage,amount',
            'discount_value' => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'user_usage_limit' => 'nullable|integer|min:0',
            'start_datetime' => 'nullable|date',
            'end_datetime' => 'nullable|date|after_or_equal:start_datetime',
            'product_ids' => 'nullable|array',
        ]);

        $data = $request->except(['_token', 'id']);

        // Handle update or create
        if ($request->id) {
            $coupon = Coupon::findOrFail($request->id);
            $coupon->update($data);
            $message = 'Cập nhật mã giảm giá thành công';
        }
        else {
            Coupon::create($data);
            $message = 'Thêm mã giảm giá thành công';
        }

        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:coupons,id']);
        Coupon::destroy($request->id);

        return response()->json([
            'status' => true,
            'message' => 'Xóa mã giảm giá thành công'
        ]);
    }
}
