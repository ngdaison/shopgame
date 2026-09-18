<?php

namespace App\Http\Controllers\Cron;

use Helper;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WalletLog;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
  public function deposit(Request $request, $uuid)
  {
    if ($uuid !== prj_key()) {
      return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
    }

    // check if the header is valid
    $config = Helper::getApiConfig('web2m_token', null);

    if (!isset($config['access_token'])) {
      return response()->json(['status' => false, 'message' => 'Service not available'], 503);
    }

    $accessToken = null;

    try {
      $accessToken = decrypt($config['access_token']);
    } catch (\Exception $e) {
      return response()->json(['status' => false, 'message' => $e->getMessage()], 503);
    }

    $bearerToken = substr($_SERVER['HTTP_AUTHORIZATION'], 7);

    if ($bearerToken !== $accessToken) {
      return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
    }

    $payload = $request->all();

    if (isset($payload['status']) && $payload['status'] !== true) {
      return response()->json([
        'status'  => false,
        'message' => 'Invalid payload',
      ], 400);
    }

    $transactions = $payload['data'] ?? [];

    if (empty($transactions)) {
      return response()->json([
        'status'  => false,
        'message' => 'No transactions found',
      ], 400);
    }

    $info     = Helper::getConfig('deposit_info');
    $prefix   = $info['prefix'] ?? 'hello ';
    $discount = $info['discount'] ?? 0;

    /*
    {
            "id": "3053756",
            "type": "IN",
            "transactionID": 45202,
            "transactionNum": 45202,
            "amount": 99000,
            "description": "NAP86246 GD 029529-031025 21:07:41",
            "bank": "ACB",
            "accountNumber": "5818861",
            "date": "10/03/2025",
            "checksum": "1847f879f495bb173830752493cbc893"
        }
            */
    $failed  = 0;
    $success = 0;
    foreach ($transactions as $transaction) {
      $id            = $transaction['id'] ?? null;
      $transactionID = $transaction['transactionID'] ?? null;
      $amount        = $transaction['amount'] ?? 0;
      $description   = $transaction['description'] ?? '';
      $bank          = $transaction['bank'] ?? '';
      $accountNumber = $transaction['accountNumber'] ?? '';
      $date          = $transaction['date'] ?? '';
      $checksum      = $transaction['checksum'] ?? '';

      if (empty($transactionID) || empty($amount) || empty($description) || empty($bank) || empty($accountNumber) || empty($date) || empty($checksum)) {
        $failed++;
        continue;
      }

      $userId = Helper::parseOrderId($description, $prefix);

      if ($userId === null || $userId === 0) {
        $failed++;
        continue;
      }

      $user = User::find($userId);

      if ($user === null) {
        $failed++;
        continue;
      }


      $exists = Transaction::where('order_id', $transactionID)->first();

      if ($exists !== null) {
        $failed++;
        continue;
      }

      $code            = 'ATM-' . Helper::randomString(7, true);
      $amount_discount = $amount;

      if ($discount > 0) {
        $amount_discount = $amount_discount + ($amount_discount * $discount) / 100;
      }

      $user->increment('balance', $amount);
      $user->increment('total_deposit', $amount);

      $user->transactions()->create([
        'code'           => $code,
        'amount'         => $amount,
        'order_id'       => $transactionID,
        'balance_after'  => $user->balance,
        'balance_before' => $user->balance - $amount,
        'type'           => 'deposit-bank',
        'extras'         => $transaction,
        'status'         => 'paid',
        'content'        => '[' . $id . '] ' . strtoupper($bank) . ' | ' . $transactionID . ' | Rev: ' . Helper::formatCurrency($amount_discount) . ' - Discount: ' . $discount . '%',
        'user_id'        => $user->id,
        'username'       => $user->username,
      ]);

      $this->updateCommision($user->id, $amount);

      $success++;
    }

    return response()->json([
      'status'  => true,
      'message' => 'Task completed',
      'data'    => [
        'failed'  => $failed,
        'success' => $success,
      ],
    ], 200);
  }

  private static function updateCommision($userId, $amount)
  {
    $user = User::find($userId);

    if ($user === null) {
      return;
    }

    $parent = $user->referrer;

    if ($parent === null) {
      return;
    }

    $affConfig = Helper::getConfig('affiliate_config');
    $percent = $affConfig['comm_percent'] ?? 10;

    $commission = ($amount * $percent) / 100;

    $parent->increment('balance_1', $commission);

    $log = WalletLog::create([
      'type'           => 'commission',
      'amount'         => $commission,
      'status'         => 'Completed',
      'user_id'        => $parent->id,
      'username'       => $parent->username,
      'sys_note'       => $user->username,
      'user_note'      => 'Referral commission - ' . $percent . '%',
      'user_action'    => 'increment',
      'ip_address'     => '127.0.0.1',
      'balance_after'  => $parent->balance_1,
      'balance_before' => $parent->balance_1 - $commission
    ]);

    return $log;
  }
}
