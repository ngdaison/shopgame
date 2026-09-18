<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Config;
use App\Models\Paypal;
use App\Models\PaypalConfig;
use App\Models\Usdt;
use App\Models\UsdtConfig;
use App\Models\PerfectMoney;
use App\Models\PerfectMoneyConfig;
use Helper;

class DepositController extends Controller
{
  public function index()
  {
    // Get Active Banks from JSON
    $bankConfig = \App\Models\BankConfig::first();
    $banks = collect($bankConfig->bank_accounts ?? [])->filter(function($acc) {
        $acc = (object) $acc;
        return isset($acc->status) && $acc->status;
    })->map(function($acc) { return (object) $acc; });
    $card_configs = $config = Helper::getApiConfig('charging_card');

    $info           = Helper::getConfig('deposit_info');
    $deposit_prefix = $info['prefix'] ?? 'hello ';
    $deposit_prefix .= auth()->user()->id;
    $deposit_amount = 10000;

    $fees = $card_configs['fees'] ?? [];

    $cardOn = true;
    if (!isset($card_configs['api_url']) || !isset($card_configs['partner_id']) || !isset($card_configs['partner_key'])) {
      $cardOn = false;
    }

    $depositPort = Helper::getConfig('deposit_port') ?? [];

    return view('account.deposits.index', [
      'pageTitle' => 'Nạp Tiền Tài Khoản',
    ], compact('banks', 'deposit_prefix', 'deposit_amount', 'card_configs', 'fees', 'cardOn', 'depositPort'));
  }

  public function banking()
  {
    $depositPort = Helper::getConfig('deposit_port');
    if (!($depositPort['bank'] ?? 0)) {
        return redirect()->back()->with('error', 'Chức năng nạp tiền qua Ngân hàng đang bảo trì.');
    }

    // Get Active Banks from JSON
    $bankConfig = \App\Models\BankConfig::first();
    $banks = collect($bankConfig->bank_accounts ?? [])->filter(function($acc) {
        $acc = (object) $acc;
        return isset($acc->status) && $acc->status;
    })->map(function($acc) { return (object) $acc; });
    $card_configs = $config = Helper::getApiConfig('charging_card');

    $info           = Helper::getConfig('deposit_info');
    $deposit_prefix = $info['prefix'] ?? 'hello ';
    $deposit_prefix .= auth()->user()->id;
    $deposit_amount = 10000;

    $fees = $card_configs['fees'] ?? [];

    $cardOn = true;
    if (!isset($card_configs['api_url']) || !isset($card_configs['partner_id']) || !isset($card_configs['partner_key'])) {
      $cardOn = false;
    }
   
    return view('account.deposits.banking', [
      'pageTitle' => 'Nạp Tiền Tài Khoản',
    ], compact('banks', 'deposit_prefix', 'deposit_amount', 'card_configs', 'fees', 'cardOn'));
  }
    public function cards()
  {
    $depositPort = Helper::getConfig('deposit_port');
    if (!($depositPort['cards'] ?? 0)) {
        return redirect()->back()->with('error', 'Chức năng nạp thẻ cào đang bảo trì.');
    }

    // Get Active Banks from JSON
    $bankConfig = \App\Models\BankConfig::first();
    $banks = collect($bankConfig->bank_accounts ?? [])->filter(function($acc) {
        $acc = (object) $acc;
        return isset($acc->status) && $acc->status;
    })->map(function($acc) { return (object) $acc; });
    $card_configs = Helper::getApiConfig('charging_card');

    $info           = Helper::getConfig('deposit_info');
    $deposit_prefix = $info['prefix'] ?? 'hello ';
    $deposit_prefix .= auth()->user()->id;
    $deposit_amount = 10000;

    $fees = $card_configs['fees'] ?? [];
    $specific_fees = $card_configs['specific_fees'] ?? [];
    $allowed_denominations = $card_configs['allowed_denominations'] ?? [];

    $cardOn = true;
    if (!isset($card_configs['api_url']) || !isset($card_configs['partner_id']) || !isset($card_configs['partner_key'])) {
      return redirect()->back()->with('error', 'Chưa cấu hình thẻ cào.');
      $cardOn = false;
    }

    return view('account.deposits.cards', [
      'pageTitle' => 'Nạp Tiền Tài Khoản Bằng Thẻ Cào',
    ], compact('banks', 'deposit_prefix', 'deposit_amount', 'card_configs', 'fees', 'cardOn', 'specific_fees', 'allowed_denominations'));
  }

  public function crypto()
  {
    $depositPort = Helper::getConfig('deposit_port');
    if (!($depositPort['crypto'] ?? 0)) {
        return redirect()->back()->with('error', 'Chức năng nạp tiền qua Crypto đang bảo trì.');
    }

    $usdtConfig = UsdtConfig::firstOrCreate(['id' => 1]);
    $config = $usdtConfig->config;

    if (!isset($config['merchant_id'])) {
      return redirect()->back()->with('error', 'Chưa cấu hình ví tiền điện tử.');
    }

    $invoices = Usdt::where('user_id', auth()->id())->orderBy('created_at', 'desc')->simplePaginate(10);

    return view('account.deposits.crypto', [
      'pageTitle' => 'Nạp Tiền Tài Khoản Bằng Crypto',
    ], compact('config', 'invoices'));
  }



  public function paypal()
  {
    $depositPort = Helper::getConfig('deposit_port');
    if (!($depositPort['paypal'] ?? 0)) {
        return redirect()->back()->with('error', 'Chức năng nạp tiền qua Paypal đang bảo trì.');
    }

    $paypalConfig = PaypalConfig::firstOrCreate(['id' => 1]);
    $config = $paypalConfig->config;

    if (!isset($config['client_id'])) {
      return redirect()->back()->with('error', 'Chưa cấu hình cổng thanh toán Paypal.');
    }

    $invoices = Paypal::where('user_id', auth()->id())->orderBy('created_at', 'desc')->simplePaginate(10);

    return view('account.deposits.paypal', [
      'pageTitle' => 'Nạp Tiền Tài Khoản Bằng Paypal',
    ], compact('config', 'invoices'));
  }

  public function perfectMoney()
  {
    $depositPort = Helper::getConfig('deposit_port');
    if (!($depositPort['perfect_money'] ?? 0)) {
        return redirect()->back()->with('error', 'Chức năng nạp tiền qua Perfect Money đang bảo trì.');
    }

    $pmConfig = PerfectMoneyConfig::firstOrCreate(['id' => 1]);
    $config = $pmConfig->config;

    if (!isset($config['account_id'])) {
      return redirect()->back()->with('error', 'Chưa cấu hình tài khoản Perfect Money.');
    }

    $user      = auth()->user();
    $invoice   = \App\Models\PerfectMoney::where('user_id', auth()->id())->where('status', 'processing')->first();
    $requestId = Helper::randomString(10);

    if ($invoice === null) {
      $invoice = \App\Models\PerfectMoney::create([
        'trans_id'    => 'PM-' . Helper::randomString(7, true),
        'status'      => 'processing',
        'amount'      => 0,
        'user_id'     => auth()->id(),
        'username'    => auth()->user()->username,
        'content'     => 'Nạp tiền tài khoản bằng Perfect Money',
      ]);
    }

    $params = [
      'API_URL'        => 'https://perfectmoney.is/api/step1.asp',
      'PAYMENT_ID'     => $invoice->request_id,
      // mã giao dịch không trùng lặp để lưu lên hệ thống
      'PAYEE_ACCOUNT'  => $config['account_id'],
      // mã tài khoản Perfect Money
      'PAYMENT_UNITS'  => 'USD',
      // đơn vị tiền tệ,
      'PAYEE_NAME'     => $user->username,
      // tên người thanh toán
      'PAYMENT_URL'    => route('account.deposits.perfect-money'),
      // URL của hoá đơn
      'NOPAYMENT_URL'  => route('account.deposits.perfect-money'),
      // URL của hoá đơn
      'STATUS_URL'     => route('cron.deposit.pm-callback'),
      // Webhook callback
      'SUGGESTED_MEMO' => 'Payment - ' . $invoice->trans_id
    ];

    $invoices = \App\Models\PerfectMoney::where('user_id', auth()->id())->where('status', 'completed')->orderBy('created_at', 'desc')->simplePaginate(10);

    return view('account.deposits.perfect_money', [
      'pageTitle' => 'Nạp Tiền Tài Khoản Bằng Perfect Money',
    ], compact('config', 'invoices', 'invoice', 'params'));
  }
}
