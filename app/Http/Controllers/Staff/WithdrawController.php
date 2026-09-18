<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CollaTransaction;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;

class WithdrawController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();

        $payload = $request->validate([
            'amount'         => 'required|integer|min:10000',
            'bank_name'      => 'required|string',
            'account_number' => 'required|string',
            'account_name'   => 'required|string',
            'user_note'      => 'nullable|string',
        ]);

        if ($user->colla_balance < $payload['amount']) {
            return redirect()->back()->with('error', 'Số dư hoa hồng không đủ để thực hiện giao dịch này.');
        }

        // Decrement balance
        $user->decrement('colla_balance', $payload['amount']);

        // Create transaction
        CollaTransaction::create([
            'user_id'        => $user->id,
            'username'       => $user->username,
            'type'           => 'staff_withdraw',
            'amount'         => $payload['amount'],
            'status'         => 'Pending',
            'order_id'       => 'WDE-' . strtoupper(Helper::randomString(8)),
            'description'    => 'Rút tiền hoa hồng về ngân hàng',
            'balance_before' => $user->colla_balance + $payload['amount'],
            'balance_after'  => $user->colla_balance,
            'payment_info'   => [
                'bank_name'      => $payload['bank_name'],
                'account_number' => $payload['account_number'],
                'account_name'   => $payload['account_name'],
            ],
            'user_note'      => $payload['user_note'] ?? '',
        ]);

        Helper::addHistory("Nhân viên {$user->username} yêu cầu rút " . Helper::formatCurrency($payload['amount']) . " về ngân hàng");

        return redirect()->back()->with('success', 'Yêu cầu rút tiền đã được gửi. Vui lòng chờ admin phê duyệt.');
    }
}
