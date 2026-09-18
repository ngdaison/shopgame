<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\CollaTransaction;
use App\Models\CollaWithdraw;
use App\Models\GBOrder;
use App\Models\ItemOrder;
use App\Models\ListItem;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
  public function index(Request $request)
  {

    $user  = auth()->user();
    $stats = [];


    // items orders
    $stats['items'] = [
      // count
      'Tổng Đơn'       => ItemOrder::where('assigned_to', $user->username)->where('status', 'Pending')->count(),
      'Đơn Đang Làm'   => ItemOrder::where('assigned_to', $user->username)->where('status', 'Processing')->count() + ItemOrder::where('assigned_to', $user->username)->where('status', 'Assigned')->count(),
      'Đơn Hoàn Thành' => ItemOrder::where('assigned_to', $user->username)->where('status', 'Completed')->count(),
      'Đơn Đã Huỷ'     => ItemOrder::where('assigned_to', $user->username)->where('status', 'Cancelled')->count(),

    ];

    // boosting orders
    $stats['boostings'] = [
      // count
      'Tổng Đơn'       => GBOrder::where('assigned_to', $user->username)->where('status', 'Pending')->count(),
      'Đơn Đang Làm'   => GBOrder::where('assigned_to', $user->username)->where('status', 'Processing')->count() + GBOrder::where('assigned_to', $user->username)->where('status', 'Assigned')->count(),
      'Đơn Hoàn Thành' => GBOrder::where('assigned_to', $user->username)->where('status', 'Completed')->count(),
      'Đơn Đã Huỷ'     => GBOrder::where('assigned_to', $user->username)->where('status', 'Cancelled')->count(),
    ];


    // accounts orders
    $stats['accounts'] = [
      'Tổng Đơn'      => ListItem::where('staff_name', $user->username)->where('buyer_name', null)->count(),
      'Đã bán'        => ListItem::where('staff_name', $user->username)->where('buyer_name', '!=', null)->count(),
      'Tổng hoa hồng' => ListItem::where('staff_name', $user->username)->where('buyer_name', '!=', null)->where('staff_status', 'Completed')->sum('staff_payment'),
      'Hoa hồng chờ'  => ListItem::where('staff_name', $user->username)->where('buyer_name', '!=', null)->where('staff_status', 'WaitPayment')->sum('staff_payment'),
    ];

    // Holding transactions
    $stats['transactions'] = [
      'Doanh Thu Chờ'   => GBOrder::where('assigned_to', $user->username)->where('assigned_status', 'WaitPayment')->sum('assigned_payment') + ItemOrder::where('assigned_to', $user->username)->where('assigned_status', 'WaitPayment')->sum('assigned_payment'),
      'Doanh Thu Ngày'  => GBOrder::where('assigned_to', $user->username)->where('assigned_status', 'Completed')->whereDate('assigned_at', now())->sum('assigned_payment') + ItemOrder::where('assigned_to', $user->username)->where('assigned_status', 'Completed')->whereDate('assigned_at', now())->sum('assigned_payment'),
      'Doanh Thu Tuần'  => GBOrder::where('assigned_to', $user->username)->where('assigned_status', 'Completed')->whereBetween('assigned_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('assigned_payment') + ItemOrder::where('assigned_to', $user->username)->where('assigned_status', 'Completed')->whereBetween('assigned_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('assigned_payment'),
      'Doanh Thu Tháng' => GBOrder::where('assigned_to', $user->username)->where('assigned_status', 'Completed')->whereMonth('assigned_at', date('m'))->sum('assigned_payment') + ItemOrder::where('assigned_to', $user->username)->where('assigned_status', 'Completed')->whereMonth('assigned_at', date('m'))->sum('assigned_payment'),
    ];

    // users
    $stats['users'] = [
      'Số Dư'  => $user->colla_balance,
      'Hoa Hồng Affiliate' => $user->balance_1 ?? 0,

    ];
    // show recent transactions
    $transactions = CollaTransaction::where('user_id', $user->id)
      ->where('type', '!=', 'affiliate_withdraw')
      ->orderBy('id', 'desc')
      ->limit(20)
      ->get();

    // show withdraw requests (staff only)
    $withdraws = CollaTransaction::where('user_id', $user->id)
      ->whereIn('type', ['staff_withdraw'])
      ->orderBy('id', 'desc')
      ->limit(20)
      ->get();

    $banks = $user->banks()->where('status', true)->get();

    return view('staff.dashboard', compact('stats', 'transactions', 'withdraws', 'banks'));
  }
  public function cronCheck(Request $request)
  {
    if (Cache::has('cronCheck')) {
      return 'Cron job is running';
    }

    Cache::put('cronCheck', now(), 5);

    $orders = ItemOrder::where('assigned_to', '!=', null)->where('assigned_status', 'WaitPayment')->where('assigned_complain', 0)->get();
    foreach ($orders as $order) {
      $diffInMinutes = now()->diffInMinutes($order->assigned_completed);
      if ($diffInMinutes >= 1440) {
        $order->update([
          'assigned_note'   => 'đã thanh toán',
          'assigned_status' => 'Completed',
        ]);
        $client = User::where('username', $order->assigned_to)->first();

        if ($client) {
          $client->increment('colla_balance', $order->assigned_payment);
          CollaTransaction::create([
            'type'           => 'items',
            'user_id'        => $client->id,
            'username'       => $client->username,
            'amount'         => $order->assigned_payment,
            'status'         => 'Completed',
            'reference'      => $order->code,
            'description'    => 'Hoàn thành đơn hàng #' . $order->code,
            'balance_before' => $client->colla_balance - $order->assigned_payment,
            'balance_after'  => $client->colla_balance,
          ]);
        }

        echo 'Order #' . $order->code . ' - ' . Helper::formatCurrency($order->assigned_payment) . ' has been completed <br />';
      } else {
        echo 'Order #' . $order->code . ' is still pending - wait ' . number_format($diffInMinutes / 24, 2) . 'h <br />';
      }
    }

    $orders = GBOrder::where('assigned_to', '!=', null)->where('assigned_status', 'WaitPayment')->where('assigned_complain', 0)->get();
    foreach ($orders as $order) {
      $diffInMinutes = now()->diffInMinutes($order->assigned_completed);
      if ($diffInMinutes >= 1440) {
        $order->update([
          'assigned_note'   => 'đã thanh toán',
          'assigned_status' => 'Completed',
        ]);
        $client = User::where('username', $order->assigned_to)->first();

        if ($client) {
          $client->increment('colla_balance', $order->assigned_payment);
          CollaTransaction::create([
            'type'           => 'boosting',
            'user_id'        => $client->id,
            'username'       => $client->username,
            'amount'         => $order->assigned_payment,
            'status'         => 'Completed',
            'reference'      => $order->code,
            'description'    => 'Hoàn thành đơn hàng #' . $order->code,
            'balance_before' => $client->colla_balance - $order->assigned_payment,
            'balance_after'  => $client->colla_balance,
          ]);
        }



        echo 'Order #' . $order->code . ' - ' . Helper::formatCurrency($order->assigned_payment) . ' has been completed <br />';
      } else {
        echo 'Order #' . $order->code . ' is still pending - wait ' . number_format($diffInMinutes / 24, 2) . 'h <br />';
      }
    }

    $orders = ListItem::where('buyer_name', '!=', null)->where('staff_status', 'WaitPayment')->get();
    foreach ($orders as $order) {
      $diffInMinutes = now()->diffInMinutes($order->staff_completed_at);
      if ($diffInMinutes >= 30) {
        $order->update([
          'staff_status' => 'Completed',
        ]);
        $client = User::where('username', $order->staff_name)->first();

        if ($client) {
          $client->increment('colla_balance', $order->staff_payment);
          CollaTransaction::create([
            'type'           => 'accounts',
            'user_id'        => $client->id,
            'username'       => $client->username,
            'amount'         => $order->staff_payment,
            'status'         => 'Completed',
            'reference'      => $order->code,
            'description'    => 'Thanh toán tài khoản #' . $order->code,
            'balance_before' => $client->colla_balance - $order->staff_payment,
            'balance_after'  => $client->colla_balance,
          ]);
        }

        echo 'Order #' . $order->code . ' - ' . Helper::formatCurrency($order->staff_payment) . ' has been completed <br />';
      } else {
        echo 'Order #' . $order->code . ' is still pending - time: ' . $diffInMinutes . 'm <br />';
      }
    }

    echo 'Cron job has been completed';
  }
}
