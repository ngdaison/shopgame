<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::orderBy('created_at', 'desc')->get();
        return view('admin.promotions.index', compact('promotions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'min_deposit' => 'required|numeric|min:0',
            'max_deposit' => 'required|numeric|gt:min_deposit',
            'bonus_value' => 'required|numeric|min:0',
            'bonus_type' => 'required|in:percentage,fixed',
            'payment_methods' => 'nullable|array',
            'payment_methods.*' => 'in:banking,card,usdt,paypal,perfect_money',
        ]);

        try {
            Promotion::create([
                'description' => $request->description,
                'min_deposit' => $request->min_deposit,
                'max_deposit' => $request->max_deposit,
                'bonus_value' => $request->bonus_value,
                'bonus_type' => $request->bonus_type,
                'payment_methods' => $request->payment_methods ?? [],
                'status' => $request->status ?? 1,
            ]);

            return response()->json(['status' => true, 'message' => 'Thêm khuyến mãi thành công']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:promotions,id',
            'min_deposit' => 'required|numeric|min:0',
            'max_deposit' => 'required|numeric|gt:min_deposit',
            'bonus_value' => 'required|numeric|min:0',
            'bonus_type' => 'required|in:percentage,fixed',
            'payment_methods' => 'nullable|array',
            'payment_methods.*' => 'in:banking,card,usdt,paypal,perfect_money',
        ]);

        try {
            $promotion = Promotion::find($request->id);
            $promotion->update([
                'description' => $request->description,
                'min_deposit' => $request->min_deposit,
                'max_deposit' => $request->max_deposit,
                'bonus_value' => $request->bonus_value,
                'bonus_type' => $request->bonus_type,
                'payment_methods' => $request->payment_methods ?? [],
                'status' => $request->status ?? 1,
            ]);

            return response()->json(['status' => true, 'message' => 'Cập nhật khuyến mãi thành công']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request)
    {
        try {
            Promotion::destroy($request->id);
            return response()->json(['status' => true, 'message' => 'Xóa khuyến mãi thành công']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }
}
