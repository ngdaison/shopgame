<?php

namespace App\Http\Controllers\Cron;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Paypal;
use App\Models\Usdt;
use App\Models\UsdtConfig;
use App\Models\PerfectMoney;
use App\Models\PerfectMoneyConfig;
use App\Models\SecuritySetting;
use App\Models\WalletLog;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class DepositCronController extends Controller
{
  public function check(Request $request, $dpType = null)
  {
    if ($dpType === null) {
        $dpType = 'check';
    }

    $type = $request->input('type', 'all');

    // Spam protection
    $cacheKey = 'cron_deposit_' . $dpType . '_' . $type;
    if (Cache::has($cacheKey)) {
      return response()->json([
        'status'  => 400,
        'message' => 'Vui lòng không spam, đợi 5 giây',
      ], 400);
    }

    Cache::put($cacheKey, true, 5);

    $show             = $request->input('show', false);
    $debug            = $request->input('debug', false);
    $debug_1          = $request->input('debug_1', false);
    $api_name         = null;
    $api_token        = null;

    $account_password = null;
    $list_transaction = [];
    $bankConfig       = \App\Models\BankConfig::first();
    $depositInfo      = Helper::getConfig('deposit_info');
    $prefix           = $depositInfo['prefix'] ?? 'NAP';
    $discount         = $depositInfo['discount'] ?? 0;

    if ($type === 'card' || $type === 'all') {
      $this->checkCard();
      if ($type === 'card') {
          return response()->json([
              'status'  => 200,
              'message' => 'Completed card check',
          ], 200);
      }
    }

    // Map input type/bank_code to API provider keys
    // Web2m Maps
    $web2mMaps = [
        'Vietcombank'    => 'historyapivcbv3',
        'MBBank'         => 'historyapimbv3',
        'MBBank_OpenAPI' => 'historyapiopenmbv3',
        'Techcombank'    => 'historyapitcbv3',
        'BIDV'           => 'historyapibidvv3',
        'BIDV_OpenAPI'   => 'historyapiopenbidvv3',
        'ACB'            => 'historyapiacbv3',
        'ACB_OpenAPI'    => 'historyapiacbv3', // Assuming same, user didn't specify diff
        'TPBank'         => 'historyapitpbv3',
        'Vietinbank'     => 'historyapivtbv3',
        'Seabank'        => 'historyapiseabankv3',
        'TheSieuRe'      => 'historyapithesieure',
        'Momo'     => 'historyapimomo',
    ];

    // STC Maps
    $stcMaps = [
        'Vietcombank'  => 'historyapivcbv3',
        'MBBank'       => 'historyapimbv3',
        'Techcombank'  => 'historyapitcbv3',
        'ACB'          => 'historyapiacbv3',
        'BIDV'         => 'historyapibidvv3',
        'TPBank'       => 'historyapitpbv3',
        'Vietinbank'   => 'historyapiviettinv3', // Note viettin
        'Seabank'      => 'historyapiseabankv3',
        'ViettelMoney' => 'historyapiviettel',
        'TheSieuRe'    => 'historyapithesieure',
        'VPBank'       => 'historyapivpbankv3', // Guessed based on pattern, or standard STC
         'Momo'     => 'historyapimomo',
    ];

    // Find accounts matching the requested type (case-insensitive bank_code)
    // If type is 'thesieure', look for bank_code 'TheSieuRe'
    // If type is 'momo', look for bank_code 'Momo'
    // Else look for bank_code matching type
    
    // Helper to normalize search
    $searchCode = $type;
    
    // Get Active Bank Accounts
    // We filter roughly by bank_code. 
    // Since $type might be lowercase "vietcombank", and DB has "Vietcombank", we use LIKE or simple mapping if needed.
    // For now, let's assume strict mapping or use the $web2mMaps keys to normalize.
    
    // Reverse lookup or just query?
    // Let's query all active and filter in PHP to be safe with casing, or use LIKE
    // Get Active Bank Accounts
    // $bankConfig already fetched above
    $allAccounts = collect($bankConfig->bank_accounts ?? []);

    // Filter active accounts matching the type
    $accounts = $allAccounts->filter(function($acc) use ($searchCode) {
        $acc = (object) $acc; // cast to object for easier access
        if (empty($acc->status)) return false;
        if (empty($acc->bank_code)) return false;
        
        if (!$searchCode || $searchCode === 'all') return true;
        
        return str_contains(strtolower($acc->bank_code), strtolower($searchCode));
    });

    if ($accounts->isEmpty()) {
         // Fallback
    }

    foreach ($accounts as $account) {
        $account = (object) $account; // Ensure object access
        $api_name = null;
        $domain = '';
        $is_custom_endpoint = false;

        if ($account->provider === 'web2m') {
            $domain = 'https://api.web2m.com';
            $api_name = $web2mMaps[$account->bank_code] ?? null;
        } elseif ($account->provider === 'stc') {
            $domain = 'https://api.sieuthicode.net';
            $api_name = $stcMaps[$account->bank_code] ?? null;
        }

        if (!$api_name || empty($account->token)) continue;

        $url = '';
        if ($account->bank_code === 'TheSieuRe' || $account->bank_code === 'Momo') {
             // Special formats often: domain/api_name/token
             $url = "{$domain}/{$api_name}/{$account->token}";
             $is_custom_endpoint = true; 
        } else {
             // Standard: domain/api_name/password/number/token
             // Ensure fields exist
             if (empty($account->password) || empty($account->number)) continue;
             $url = "{$domain}/{$api_name}/{$account->password}/{$account->number}/{$account->token}";
        }

        try {
            $response = Http::get($url);
            
            if ($response->failed()) continue;

            $json = $response->json();
            $fetchedTransactions = [];

            // Parsing Logic based on Provider/Bank
            // Web2m Momo: momoMsg -> tranList
            // TheSieuRe: tranList
            // Standard: transactions
            
            // SieuThiCode ViettelMoney: data -> content ? (User provided sample: data: { content: [...] })
            // Need specific check for ViettelMoney on STC
            
            if ($account->bank_code === 'ViettelMoney' && $account->provider === 'stc') {
                 $fetchedTransactions = $json['data']['content'] ?? [];
            } elseif ($account->bank_code === 'Momo') {
                 $fetchedTransactions = $json['momoMsg']['tranList'] ?? [];
            } elseif ($account->bank_code === 'TheSieuRe') {
                 $fetchedTransactions = $json['tranList'] ?? [];
            } else {
                 $fetchedTransactions = $json['transactions'] ?? [];
                 // STC sometimes wraps in 'transactions' too? 
                 // User sample for STC general: { status: .., transactions: [...] }
            }

            foreach ($fetchedTransactions as $value) {
                // Normalize data to standard format
                // Needed: amount, description, transactionID, transactionDate(optional)
                
                $amount = 0;
                $desc = '';
                $tid = '';
                $date = null;
                $type_in_out = 'IN'; // Default match

                if ($account->bank_code === 'ViettelMoney' && $account->provider === 'stc') {
                     // Sample: amount: "1000", description: "...", bankTransId: "...", transDate: "...", paymentType: "CREDIT"
                     // credit is IN? Yes usually.
                     if (($value['paymentType'] ?? '') !== 'CREDIT') continue;
                     
                     $amount = (float) $value['amount'];
                     $desc = $value['description'];
                     $tid = $value['bankTransId'];
                     $date = $value['transDate'];

                } elseif ($account->bank_code === 'TheSieuRe') {
                     // Sample: amount: "15600", description: "...", transId: "..."
                     $amount = (float) str_replace([',', 'đ'], '', $value['amount']);
                     $desc = $value['description'] ?? '';
                     $tid = $value['transId'] ?? ($value['description'] ?? uniqid());
                     $date = null;

                } elseif ($account->bank_code === 'Momo') {
                     // Sample: amount: 1000, comment: "...", tranId: "...", clientTime: ...
                     $amount = (float) $value['amount'];
                     $desc = $value['comment'] ?? '';
                     $tid = (string) $value['tranId'];
                     $date = $value['clientTime'] ?? null;

                } else {
                     // Standard Web2m/STC
                     // Sample: type: "IN", amount: 260000, description: "...", transactionID: "..."
                     if (isset($value['type']) && strtoupper($value['type']) !== 'IN') continue;
                     
                     $amount = (float) $value['amount'];
                     $desc = $value['description'];
                     $tid = (string) $value['transactionID'];
                     $date = $value['transactionDate'] ?? null;
                }

                // Filter by Prefix
                if (!str_contains(strtolower($desc), strtolower($prefix))) {
                    \Illuminate\Support\Facades\Log::info("CronDeposit: Prefix mismatch. Desc: '$desc', Expected: '$prefix'");
                    continue;
                }

                $list_transaction[] = [
                    'amount'          => $amount,
                    'description'     => $desc,
                    'transactionID'   => $tid,
                    'transactionDate' => $date,
                    'bank_code'       => $account->bank_code ?? $type, // Capture bank_code
                ];
            }

        } catch (\Exception $e) {
            // Log or ignore specific bank failure to allow others to process
            continue;
        }
    }

    if ($debug) {
      return response()->json([
        'data' => $response->json(),
        'code' => $response->status(),
      ], 200);
    }

    if ($debug_1) {
      return response()->json([
        'data' => $list_transaction,
        'code' => $response->status(),
      ], 200);
    }




    // cắt lấy 50 giao dịch mới nhất
    $list_transaction = array_slice($list_transaction, 0, 50);

    if (count($list_transaction) === 0) {
      return response()->json([
        'data'    => $show ? ($response->json() ?? []) : [],
        'status'  => 200,
        'message' => 'No transactions found #2',
      ], 200);
    }

    if ($dpType === 'check') {

      foreach ($list_transaction as $item) {
        $userId = Helper::parseOrderId($item['description'], $prefix);

        if ($userId === null || $userId === 0) {
          if ($show) {
            echo 'Không tìm thấy user id trong giao dịch #' . $item['transactionID'] . ' / ' . $item['description'] . '<br />';
          }
           \Illuminate\Support\Facades\Log::info("CronDeposit: No UserID found in desc: " . $item['description']);
          continue;
        }

        $user = User::find($userId);

        if ($user === null) {
          if ($show) {
            echo 'Không tìm thấy user #' . $userId . ' trong giao dịch hệ thống [MySQL]<br />';
          }
          \Illuminate\Support\Facades\Log::info("CronDeposit: User ID $userId not found in DB.");
          continue;
        }

        $code            = 'ATM-' . Helper::randomString(7, true);
        $realAmount      = (float) $item['amount'];
        $description     = $item['description'];
        $transactionID   = (string) $item['transactionID'];
        $transactionDate = $item['transactionDate'];

        $exists = $this->checkInvoice($transactionID);

        if ($exists !== null) {
          if ($show) {
            echo 'Giao dịch #' . $transactionID . ' đã tồn tại trong hệ thống [MySQL]<br />';
          }
          \Illuminate\Support\Facades\Log::info("CronDeposit: Transaction $transactionID already exists.");
          continue;
        }

        $amount = $realAmount;

        if ($discount > 0) {
          $amount = $amount + ($amount * $discount) / 100;
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
          'extras'         => $item,
          'status'         => 'paid',
          'content'        => 'AUTO Deposit ' . strtoupper($item['bank_code'] ?? $type ?? 'BANK') . ' - ' . $transactionID . ' - Rev: ' . Helper::formatCurrency($realAmount) . ' - Discount: ' . $discount . '%',
          'user_id'        => $user->id,
          'username'       => $user->username,
        ]);
        
        // Notification
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'type'    => 'deposit',
            'title'   => 'Nạp tiền thành công',
            'content' => 'Bạn đã nạp thành công ' . Helper::formatCurrency($amount) . ' qua ' . strtoupper($item['bank_code'] ?? 'BANK') . '.',
            'icon'    => 'fa fa-money-bill ps-1',
            'is_read' => false
        ]);
        
        // Add Banking Record
        \App\Models\Banking::create([
             'trans_id'       => $code,
             'user_id'        => $user->id,
             'amount'         => $amount,
             'balance_before' => $user->balance - $amount,
             'balance_after'  => $user->balance,
             'content'        => 'AUTO Deposit ' . strtoupper($item['bank_code'] ?? $type ?? 'BANK') . ' - ' . $transactionID . ' - Discount: ' . $discount . '%',
             'status'         => 'paid',
             'bank_code'      => $item['bank_code'] ?? strtoupper($type),
             'username'       => $user->username,
        ]);

        $ref = $user->referrer;
        if ($ref !== NULL) {
          $affiliate = $ref->affiliate;
          if ($affiliate !== NULL) {
            $affiliate->increment('total_deposit', $amount);
          }
        }

        Helper::updateCommission($user->id, $amount, 'deposit');

        if ($show) {
          echo 'Giao dịch #' . $transactionID . ', số tiền ' . Helper::formatCurrency($amount) . ' thành công<br />';
        }
      }

      if ($show === false) {
        return response()->json([
          'data'    => [
            'total_valid' => count($list_transaction),
          ],
          'status'  => 200,
          'message' => 'Completed check transactions',
        ], 200);
      } else {
        return 'Completed check transactions';
      }
    } else if ($dpType === 'invoice') {
      foreach ($transactions as $item) {
        $invoiceId = Helper::parseOrderId($item['description'], $prefix);

        if ($invoiceId === null || $invoiceId === 0) {
          if ($show) {
            echo 'Không tìm thấy hoá đơn trong giao dịch #' . $item['transactionID'] . ' / ' . $item['description'] . '<br />';
          }

          continue;
        }

        $invoice = Invoice::where('code', $prefix . $invoiceId)->where('status', 'processing')->first();

        if ($invoice === null) {
          if ($show) {
            echo 'Không tìm thấy hóa đơn #' . $invoiceId . ' trong giao dịch hệ thống [MySQL]<br />';
          }

          continue;
        }

        if ($invoice->amount > $item['amount']) {
          if ($show) {
            echo 'Số tiền giao dịch #' . $item['transactionID'] . ' không đủ để thanh toán hóa đơn #' . $invoiceId . '<br />';
          }

          continue;
        }

        $user = User::find($invoice->user_id);

        if ($user === null) {
          if ($show) {
            echo 'Không tìm thấy user #' . $invoice->user_id . ' trong giao dịch hệ thống [MySQL]<br />';
          }

          continue;
        }

        $code            = 'ATM-' . Helper::randomString(7);
        $amount          = (float) $item['amount'];
        $description     = $item['description'];
        $transactionID   = $item['transactionID'];
        $transactionDate = $item['transactionDate'];

        $exists = $this->checkInvoice($transactionID);

        if ($exists !== null) {
          if ($show) {
            echo 'Giao dịch #' . $transactionID . ' đã tồn tại trong hệ thống [MySQL]<br />';
          }

          continue;
        }

        $user->increment('balance', $amount);
        $user->increment('total_deposit', $amount);

        $user->transactions()->create([
          'code'           => $code,
          'amount'         => $amount,
          'order_id'       => $transactionID,
          'balance_after'  => $user->balance,
          'balance_before' => $user->balance - $amount,
          'type'           => 'deposit',
          'extras'         => $item,
          'status'         => 'paid',
          'content'        => 'AUTO Deposit ' . strtoupper($type) . ' - ' . $transactionID,
          'user_id'        => $user->id,
          'username'       => $user->username,
        ]);

        $invoice->update([
          'status'      => 'completed',
          'paid_at'     => now(),
          'expired_at'  => now(),
          'description' => 'AUTO Deposit ' . strtoupper($type) . ' - ' . $transactionID,
        ]);

        if ($show) {
          echo 'Giao dịch #' . $transactionID . ', số tiền ' . Helper::formatCurrency($amount) . ' thành công<br />';
        }
      }

      if ($show === false) {
        return response()->json([
          'data'    => [
            'total_valid' => count($list_transaction),
          ],
          'status'  => 200,
          'message' => 'Completed check transactions',
        ], 200);
      } else {
        return 'Completed check transactions';
      }
    }
  }



  protected function checkInvoice($transactionID)
  {
    return Transaction::where('order_id', $transactionID)->first();
  }

  private function checkCard()
  {
    $config = Helper::getApiConfig('charging_card');

    if (!isset($config['api_url']) || !isset($config['partner_id']) || !isset($config['partner_key'])) {
      return response()->json([
        'status'  => 400,
        'message' => 'API Token is not set',
      ], 400);
    }

    $cards = Card::where('status', 'Processing')->get();

    if (count($cards) === 0) {
      return response()->json([
        'status'  => 200,
        'message' => 'No cards found',
      ], 200);
    }

    foreach ($cards as $item) {
      $fees = $config['fees'][strtoupper($item->type)] ?? 20;

      // Check specific fees for request amount (initially)
      if (isset($config['specific_fees'][strtoupper($item->type)][$item->amount])) {
          $fees = $config['specific_fees'][strtoupper($item->type)][$item->amount];
      }

      $result = Http::post($config['api_url'] . '/chargingws/v2', [
        'telco'      => strtoupper($item->type),
        'code'       => $item->code,
        'serial'     => $item->serial,
        'amount'     => $item->amount,
        'request_id' => $item->request_id,
        'partner_id' => $config['partner_id'],
        'sign'       => md5($config['partner_key'] . $item->code . $item->serial),
        'command'    => 'check',
      ])->json();

      if (!isset($result['status'])) {
        continue;
      }

      switch ($result['status']) {
        case 1:
          $client = User::find($item->user_id);
          if ($client === null) {
            echo '<span style="color: green">' . $item->id . '</span>/<span style="color: red">' . $item->serial . '</span> => KHÔNG TÌM THẤY USER';
            break;
          }

          $amount = $result['declared_value'];

          // Re-calculate fees based on ACTUAL declared value
          if (isset($config['specific_fees'][strtoupper($item->type)][$amount])) {
              $fees = $config['specific_fees'][strtoupper($item->type)][$amount];
          }

          $real_amount = $amount - ($amount * $fees) / 100;

          if (in_array(domain(), ['ducmomgamer.top'])) {
            $real_amount = $real_amount * 2;
          }

          $code = 'CARD-' . Helper::randomString(6, true);

          $client->increment('balance', $real_amount);
          $client->increment('total_deposit', $real_amount);

          $client->transactions()->create([
            'code'           => $code,
            'amount'         => $real_amount,
            'balance_after'  => $client->balance,
            'balance_before' => $client->balance - $real_amount,
            'type'           => 'deposit-card',
            'extras'         => [
              'card_id' => $item->id,
            ],
            'status'         => 'paid',
            'content'        => 'Nạp thẻ thành công #' . $item->serial . '; phí ' . $fees . '%',
            'user_id'        => $client->id,
            'username'       => $client->username,
          ]);

          $item->update([
            'value'            => $amount,
            'status'           => 'Completed',
            'amount'           => $real_amount,
            'content'          => $result['message'],
            'transaction_code' => $code,
          ]);

          // $ref = $client->referrer;
          // if ($ref !== null) {
          //   $affiliate = $ref->affiliate;
          //   if ($affiliate !== null) {
          //     $affiliate->increment('total_deposit', $amount);
          //   }
          // }

          Helper::updateCommission($client->id, $real_amount, 'deposit');

          echo '<span style="color: green">ID: ' . $item->id . '</span>; <span style="color: red">' . $item->serial . '</span> => ' . ($result['message'] ?? 'Unknow error') . '<br />';
          break;
        case 2:
          $item->update([
            'status'  => 'Cancelled',
            'amount'  => 0,
            'content' => $result['message'] ?? 'Unknow error',
          ]);
          echo 'ID: <span style="color: green">' . $item->id . '</span>; SERIAL: <span style="color: red">' . $item->serial . '</span> => ' . ($result['message'] ?? 'Unknow error') . '<br />';
          break;
        case 3:
          $item->update([
            'status'  => 'Error',
            'amount'  => 0,
            'content' => $result['message'] ?? 'Unknow error',
          ]);
          echo 'ID: <span style="color: green">' . $item->id . '</span>; SERIAL: <span style="color: red">' . $item->serial . '</span> => ' . ($result['message'] ?? 'Unknow error') . '<br />';
          break;
        case 4:
          echo ' Hệ thống bảo trì';
          break;
        case 99:
          echo 'ID: <span style="color: green">' . $item->id . '</span>; SERIAL: <span style="color: red">' . $item->serial . '</span> => ' . ($result['message'] ?? 'Unknow error') . '<br />';
          break;
        default:
          echo '<span style="color: green">' . $item->id . '</span>/<span style="color: red">' . $item->serial . '</span> => ' . ($result['message'] ?? 'Unknow error') . '<br />';
          break;
      }
    }
  }

  public function cardCallback(Request $request)
  {
    file_put_contents(base_path('data.json'), $request->all());

    $validate = Validator::make($request->all(), [
      'status'         => 'required|integer',
      'message'        => 'required|string',
      'request_id'     => 'required',
      'declared_value' => 'required',
      'value'          => 'required',
      'amount'         => 'required',
      'code'           => 'required|string',
      'serial'         => 'required|string',
      'telco'          => 'required|string',
      'trans_id'       => 'required|integer',
      'callback_sign'  => 'required|string',
    ]);


    if ($validate->fails()) {
      return response()->json([
        'status'  => 400,
        'message' => 'Dữ liệu không hợp lệ',
      ], 400);
    }

    $payload = $request->all();

    $config = Helper::getApiConfig('charging_card');

    if (!isset($config['partner_key']) || !isset($config['fees'])) {
      return response()->json([
        'status'  => 400,
        'message' => 'API Token is not set',
      ], 400);
    }

    $fees = $config['fees'][$payload['telco']] ?? 20;

    $amount = (int) $payload['declared_value'];

    // Check specific fees
    if (isset($config['specific_fees'][$payload['telco']][$amount])) {
        $fees = $config['specific_fees'][$payload['telco']][$amount];
    }

    $item = Card::where('request_id', $payload['request_id'])
      ->where('order_id', $payload['trans_id'])
      ->where('status', 'Processing')
      ->first();

    if ($item === null) {
      return response()->json([
        'status'  => 400,
        'message' => 'Không tìm thấy giao dịch này',
      ], 400);
    }

    $sign = md5($config['partner_key'] . $payload['code'] . $payload['serial']);

    if ($sign !== $payload['callback_sign']) {
      return response()->json([
        'status'  => 400,
        'message' => 'Sai chữ ký',
      ], 400);
    }

    switch ($payload['status']) {
      case 1:
        $client = User::find($item->user_id);
        if ($client === null) {
          return response()->json([
            'status'  => 400,
            'message' => 'Không tìm thấy user',
          ], 400);
        }

        $amount = (int) $payload['declared_value'];

        $real_amount = $amount - ($amount * $fees) / 100;

        if (in_array(domain(), ['ducmomgamer.top'])) {
          $real_amount = $real_amount * 2;
        }

        $code = 'CARD-' . Helper::randomString(6, true);

        $client->increment('balance', $real_amount);
        $client->increment('total_deposit', $real_amount);

        $client->transactions()->create([
          'code'           => $code,
          'amount'         => $real_amount,
          'balance_after'  => $client->balance,
          'balance_before' => $client->balance - $real_amount,
          'type'           => 'deposit-card',
          'extras'         => [
            'card_id' => $item->id,
          ],
          'status'         => 'paid',
          'content'        => 'Nạp thẻ thành công #' . $item->serial . '; phí ' . $fees . '%',
          'user_id'        => $client->id,
          'username'       => $client->username,
        ]);

        $item->update([
          'value'            => $amount,
          'status'           => 'Completed',
          'amount'           => $real_amount,
          'content'          => $payload['message'],
          'transaction_code' => $code,
        ]);

        $ref = $client->referrer;
        if ($ref !== null) {
          $affiliate = $ref->affiliate;
          if ($affiliate !== null) {
            $affiliate->increment('total_deposit', $amount);
          }
        }

        Helper::updateCommission($client->id, $real_amount, 'deposit');

        return response()->json([
          'data'    => [
            'code'    => $code,
            'amount'  => $real_amount,
            'balance' => $client->balance,
          ],
          'status'  => 200,
          'message' => 'Nạp thẻ thành công',
        ], 200);
      case 2:
        $item->update([
          'status'  => 'Cancelled',
          'amount'  => 0,
          'content' => $payload['message'] ?? 'Unknow error',
        ]);

        return response()->json([
          'data'    => [
            'id'     => $item->id,
            'serial' => $item->serial,
          ],
          'status'  => 400,
          'message' => $payload['message'] ?? 'Unknow error',
        ], 400);
      case 3:
        $item->update([
          'status'  => 'Error',
          'amount'  => 0,
          'content' => $payload['message'] ?? 'Unknow error',
        ]);

        return response()->json([
          'data'    => [
            'id'     => $item->id,
            'serial' => $item->serial,
          ],
          'status'  => 400,
          'message' => $payload['message'] ?? 'Unknow error',
        ], 400);
      case 4:
        echo ' Hệ thống bảo trì';
        break;
      default:
        return response()->json([
          'data'    => [
            'id'     => $item->id,
            'serial' => $item->serial,
            'status' => $payload['status'],
          ],
          'status'  => 400,
          'message' => $payload['message'] ?? 'Unknow error',
        ], 400);
    }
  }

  public function fpaymentCallback(Request $request)
  {
    $payload = $request->validate([
      'request_id'     => 'required|string',
      'token'          => 'required|string',
      'received'       => 'required|numeric',
      'status'         => 'required|string',
      'from_address'   => 'nullable|string',
      'transaction_id' => 'nullable|string',
    ]);

    $usdtConfig = UsdtConfig::firstOrCreate(['id' => 1]);
    $config = $usdtConfig->config;
    $token     = $payload['token'];
    $status    = $payload['status'];
    $address   = $payload['from_address'];
    $transId   = $payload['transaction_id'];
    $exchange  = $config['exchange'] ?? 23000;
    $received  = (double) number_format($payload['received'], 3);
    $requestId = $payload['request_id'];

    //
    $invoice = Usdt::where('request_id', $requestId)->where('status', 'processing')->first();

    if ($invoice === null) {
      return response()->json([
        'status'  => 400,
        'message' => 'Invoice not found',
      ], 400);
    }

    $user = User::find($invoice->user_id);

    if ($user === null) {
      $invoice->update([
        'status'      => 'cancelled',
        'description' => 'Không tìm thấy người dùng',
      ]);
      return response()->json([
        'status'  => 400,
        'message' => 'User not found',
      ], 400);
    }

    if ($status === 'completed') {
      $realAmount = $invoice->amount; //$received * ($exchange ?? 23000);

      $invoice->update([
        'status'      => 'completed',
        'paid_at'     => now(),
        'expired_at'  => now(),
        'trans_id'    => $transId,
        'content'     => 'Deposit FPayment - ' . $transId . ' - Rev ' . $received . '$',
      ]);

      $user->increment('balance', $realAmount);
      $user->increment('total_deposit', $realAmount);

      $user->transactions()->create([
        'code'           => $transId,
        'amount'         => $realAmount,
        'order_id'       => $transId,
        'balance_after'  => $user->balance,
        'balance_before' => $user->balance - $realAmount,
        'type'           => 'usdt',
        'extras'         => $payload,
        'status'         => 'paid',
        'content'        => 'Thanh toán hoá đơn #' . $requestId,
        'user_id'        => $user->id,
        'username'       => $user->username,
      ]);


      // $ref = $user->referrer;
      // if ($ref !== null) {
      //   $affiliate = $ref->affiliate;
      //   if ($affiliate !== null) {
      //     $affiliate->increment('total_deposit', $realAmount);
      //   }
      // }


      Helper::updateCommission($user->id, $realAmount, 'deposit');

      return response()->json([
        'data'    => [
          'balance' => $realAmount,
        ],
        'status'  => 200,
        'message' => 'Thanh toán thành công',
      ], 200);
    } else if ($status === 'expired') {
      $invoice->update([
        'status'      => 'expired',
        'content'     => 'Không nhận được thanh toán',
      ]);

      return response()->json([
        'status'  => 400,
        'message' => 'Thanh toán thất bại',
      ], 400);
    }
  }

  public function pmCallback(Request $request)
  {
    $payload = $request->validate([
      'PAYMENT_ID'        => 'required|string',
      'PAYEE_ACCOUNT'     => 'required|string',
      'PAYMENT_AMOUNT'    => 'required|numeric',
      'PAYMENT_UNITS'     => 'required|string',
      'PAYMENT_BATCH_NUM' => 'required|string',
      'PAYER_ACCOUNT'     => 'required|string',
      'TIMESTAMPGMT'      => 'required|string',
      'V2_HASH'           => 'required|string',
    ]);

    $pmConfig = PerfectMoneyConfig::firstOrCreate(['id' => 1]);
    $config = $pmConfig->config;

    if (!isset($config['account_id'])) {
      return response()->json([
        'status'  => 400,
        'message' => 'Chưa cấu hình tài khoản Perfect Money.',
      ], 400);
    }

    $string    = $payload['PAYMENT_ID'] . ':' . $payload['PAYEE_ACCOUNT'] . ':' . $payload['PAYMENT_AMOUNT'] . ':' . $payload['PAYMENT_UNITS'] . ':' . $payload['PAYMENT_BATCH_NUM'] . ':' . $payload['PAYER_ACCOUNT'] . ':' . strtoupper(md5($config['passphrase'])) . ':' . $payload['TIMESTAMPGMT'];
    $hashed    = strtoupper(md5($string));
    $amount    = (double) $payload['PAYMENT_AMOUNT'];
    $requestId = $payload['PAYMENT_ID'];

    if ($payload['V2_HASH'] !== $hashed) {
      return response()->json([
        'status'  => 400,
        'message' => 'Invalid hash',
      ], 400);
    }

    $invoice = PerfectMoney::where('request_id', $requestId)->where('status', 'processing')->first();

    if ($invoice === null) {
      return response()->json([
        'status'  => 400,
        'message' => 'Invoice not found',
      ], 400);
    }

    $balance = $amount * ($config['exchange'] ?? 0);

    $user = User::find($invoice->user_id);

    if ($user === null) {
      $invoice->update([
        'status'      => 'cancelled',
        'description' => 'Không tìm thấy người dùng',
      ]);
      return response()->json([
        'status'  => 400,
        'message' => 'User not found',
      ], 400);
    }

    $invoice->update([
      'status'      => 'completed',
      'paid_at'     => now(),
      'expired_at'  => now(),
      'trans_id'    => $payload['PAYMENT_BATCH_NUM'],
      'content'     => 'Deposit Perfect Money - ' . $payload['PAYMENT_BATCH_NUM'] . ' - Rev ' . $amount . '$',
    ]);

    $user->increment('balance', $balance);
    $user->increment('total_deposit', $balance);

    $user->transactions()->create([
      'code'           => $payload['PAYMENT_BATCH_NUM'],
      'amount'         => $balance,
      'order_id'       => $payload['PAYMENT_BATCH_NUM'],
      'balance_after'  => $user->balance,
      'balance_before' => $user->balance - $balance,
      'type'           => 'perfect_money',
      'extras'         => $payload,
      'status'         => 'paid',
      'content'        => 'Thanh toán hoá đơn #' . $requestId,
      'user_id'        => $user->id,
      'username'       => $user->username,
    ]);

    // $ref = $user->referrer;
    // if ($ref !== null) {
    //   $affiliate = $ref->affiliate;
    //   if ($affiliate !== null) {
    //     $affiliate->increment('total_deposit', $balance);
    //   }
    // }

    Helper::updateCommission($user->id, $balance, 'deposit');

    return response()->json([
      'data'    => [
        'balance' => $balance,
      ],
      'status'  => 200,
      'message' => 'Thanh toán thành công',
    ], 200);
  }

  /**
   * SePay Webhook Callback Handler
   * Signature Header: X-SePay-Signature (sha256=<hex>)
   * Timestamp Header: X-SePay-Timestamp (<unix_seconds>)
   * Raw Payload: php://input
   */
  public function sepayCallback(Request $request)
  {
    $rawPayload = $request->getContent();
    $data = json_decode($rawPayload, true);

    if (!is_array($data) || empty($data)) {
        $data = $request->all();
    }

    \Illuminate\Support\Facades\Log::info('SePay Webhook Received', [
        'content_type' => $request->header('Content-Type'),
        'headers'      => $request->headers->all(),
        'body'         => $data
    ]);

    $accountNumber = $data['accountNumber'] ?? $data['account_number'] ?? $data['account'] ?? null;
    $transferType  = strtolower($data['transferType'] ?? $data['type'] ?? $data['transfer_type'] ?? 'in');
    $amount        = (float) ($data['transferAmount'] ?? $data['amount'] ?? $data['transfer_amount'] ?? 0);
    $sepayId       = (string) ($data['id'] ?? '');
    $refCode       = (string) ($data['referenceCode'] ?? '');
    $payCode       = (string) ($data['code'] ?? '');
    $transId       = !empty($sepayId) ? $sepayId : (!empty($refCode) ? $refCode : $payCode);

    // Only process incoming transfers (transferType === 'in')
    if ($transferType !== 'in' && $amount <= 0) {
        return response()->json(['status' => true, 'message' => 'Ignored non-incoming transaction'], 200);
    }

    if (empty($transId)) {
        return response()->json(['status' => false, 'message' => 'Missing transaction ID'], 400);
    }

    // Get Bank Config
    $bankConfig = \App\Models\BankConfig::first();
    $allAccounts = collect($bankConfig->bank_accounts ?? []);

    // Find account by accountNumber or provider === 'sepay'
    $matchingAccount = $allAccounts->first(function($acc) use ($accountNumber) {
        $acc = (object) $acc;
        if (isset($acc->provider) && $acc->provider === 'sepay') {
            if (empty($accountNumber)) return true;
            return isset($acc->number) && (string)$acc->number === (string)$accountNumber;
        }
        return false;
    }) ?? $allAccounts->first(function($acc) {
        $acc = (object) $acc;
        return isset($acc->provider) && $acc->provider === 'sepay';
    });

    // HMAC-SHA256 Verification if Secret Key is configured
    $secret = null;
    if ($matchingAccount) {
        $matchingAccount = (object)$matchingAccount;
        $secret = $matchingAccount->token ?? $matchingAccount->password ?? null;
    }

    if (!empty($secret)) {
        $signature = $request->header('X-SePay-Signature') ?? $_SERVER['HTTP_X_SEPAY_SIGNATURE'] ?? '';
        $timestamp = $request->header('X-SePay-Timestamp') ?? $_SERVER['HTTP_X_SEPAY_TIMESTAMP'] ?? '';

        $expected = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $rawPayload, $secret);
        $isValid  = hash_equals($expected, $signature);

        if (!$isValid && !empty($rawPayload)) {
            $fallbackPayload = http_build_query($request->all());
            $expectedFallback = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $fallbackPayload, $secret);
            if (hash_equals($expectedFallback, $signature)) {
                $isValid = true;
            }
        }

        if (!$isValid) {
            \Illuminate\Support\Facades\Log::warning('SePay Webhook Invalid Signature', [
                'expected'  => $expected,
                'signature' => $signature,
                'timestamp' => $timestamp
            ]);
            return response()->json(['status' => false, 'message' => 'Invalid signature'], 401);
        }
    }

    // Check if transaction already exists (Anti-replay/duplicate using SePay ID & referenceCode)
    $checkKeys = array_filter([$sepayId, 'sepay_' . $sepayId, $refCode, $payCode]);
    $exists = Transaction::whereIn('order_id', $checkKeys)->first();
    if ($exists !== null) {
        return response()->json(['success' => true, 'message' => 'Transaction already processed'], 200);
    }

    // Parse User ID from code, subAccount, content, or description
    $depositInfo = Helper::getConfig('deposit_info');
    $prefix      = $depositInfo['prefix'] ?? 'NAP';
    $discount    = (float) ($depositInfo['discount'] ?? 0);

    $userId = null;
    foreach ([$data['code'] ?? null, $data['subAccount'] ?? null, $data['content'] ?? null, $data['description'] ?? null] as $field) {
        if (!empty($field)) {
            $userId = Helper::parseOrderId($field, $prefix);
            if ($userId !== null && $userId !== 0) {
                break;
            }
        }
    }

    if ($userId === null || $userId === 0) {
        \Illuminate\Support\Facades\Log::info("SePay Webhook: User ID not found in transaction content/code.");
        return response()->json(['success' => false, 'message' => 'User ID not found in transaction'], 200);
    }

    $user = User::find($userId);
    if ($user === null) {
        \Illuminate\Support\Facades\Log::info("SePay Webhook: User #{$userId} not found in DB.");
        return response()->json(['success' => false, 'message' => 'User not found in system'], 200);
    }

    // Calculate real deposit amount
    $realAmount = $amount;
    if ($discount > 0) {
        $amount = $amount + ($amount * $discount) / 100;
    }

    $user->increment('balance', $amount);
    $user->increment('total_deposit', $amount);

    $code = 'ATM-' . Helper::randomString(7, true);
    $bankCode = $data['gateway'] ?? $data['bank_code'] ?? 'Bank';

    $user->transactions()->create([
      'code'           => $code,
      'amount'         => $amount,
      'order_id'       => (string) $transId,
      'balance_after'  => $user->balance,
      'balance_before' => $user->balance - $amount,
      'type'           => 'deposit-bank',
      'extras'         => $data,
      'status'         => 'paid',
      'content'        => 'AUTO Deposit ' . strtoupper($bankCode) . ' - ' . $transId . ' - Rev: ' . Helper::formatCurrency($realAmount) . ' - Discount: ' . $discount . '%',
      'user_id'        => $user->id,
      'username'       => $user->username,
    ]);

    \App\Models\Notification::create([
        'user_id' => $user->id,
        'type'    => 'deposit',
        'title'   => 'Nạp tiền thành công',
        'content' => 'Bạn đã nạp thành công ' . Helper::formatCurrency($amount) . ' qua ' . strtoupper($bankCode) . '.',
        'icon'    => 'fa fa-money-bill ps-1',
        'is_read' => false
    ]);

    \App\Models\Banking::create([
         'trans_id'       => $code,
         'user_id'        => $user->id,
         'amount'         => $amount,
         'balance_before' => $user->balance - $amount,
         'balance_after'  => $user->balance,
         'content'        => 'AUTO Deposit ' . strtoupper($bankCode) . ' - ' . $transId . ' - Discount: ' . $discount . '%',
         'status'         => 'paid',
         'bank_code'      => strtoupper($bankCode),
         'username'       => $user->username,
    ]);

    $ref = $user->referrer;
    if ($ref !== null && $ref->affiliate !== null) {
        $ref->affiliate->increment('total_deposit', $amount);
    }

    Helper::updateCommission($user->id, $amount, 'deposit');

    return response()->json(['success' => true, 'message' => 'Webhook processed successfully'], 200);
  }
}
