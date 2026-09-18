<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\PinGroup;
use App\Models\Transaction;
use App\Models\User;
use Helper;
use App\Models\Campaign;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class HomeController extends Controller
{
  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Contracts\Support\Renderable
   */
  public function index()
  {
    $pin_groups = PinGroup::orderBy('id', 'desc')->where('status', true)->get();
    $categories = \App\Models\Category::where('status', 'active')->orderBy('priority', 'desc')->get();
    $serviceCategories = \App\Models\ServiceCategory::where('status', true)
      ->where('product_type', 'category')
      ->orderBy('priority', 'desc')
      ->get();

    // Configurable Category Slug for Robux Section
    $robuxCategorySlug = theme_config('home_robux_slug', 'dich-vu-robux');
    $robuxCategory = \App\Models\Category::where('slug', $robuxCategorySlug)->first();

    $robuxServices = collect(); // Default empty
    $robuxTitle = '';

    if ($robuxCategory) {
      $robuxServices = $robuxCategory->robuxServices()->where('status', true)->orderBy('priority', 'desc')->get();
      $robuxTitle = $robuxCategory->name;
    }

    $minigamePos = theme_config('minigame_pos', 'bottom');

    $top10UserDeposit = \App\Models\Transaction::selectRaw('username, sum(amount) as total')
      ->whereIn('type', ['deposit-bank', 'deposit-card', 'deposit'])
      ->groupBy('username')
      ->orderBy('total', 'desc')
      ->whereMonth('created_at', date('m'))
      ->whereYear('created_at', date('Y'))
      ->limit(7)
      ->get();

    // except role admin
    foreach ($top10UserDeposit as $key => $value) {
      $user = User::where('username', $value->username)->first();

      if ($user !== null && $user->isAdmin()) {
        unset($top10UserDeposit[$key]);
      }
      else {
        $value->user_fullname = $user ? ($user->fullname ?? $user->full_name) : null;
      }
    }

    // Process Fake Top Deposit settings
    try {
      $fakeTopConfig = theme_config('fake_top_deposit');

      if (!empty($fakeTopConfig) && is_string($fakeTopConfig)) {
        $e = explode("\n", $fakeTopConfig);
        if (is_array($e)) {
          foreach ($e as $line) {
            if (empty(trim($line)))
              continue;

            $p = explode('|', trim($line));
            if (count($p) >= 2) {
              $name = trim($p[0]);
              $amount = preg_replace('/[^0-9]/', '', trim($p[1]));

              if (is_numeric($amount)) {
                $fakeDto = new \stdClass();
                $fakeDto->total = (int)$amount;
                $fakeDto->prefix = '+';

                if (substr($name, -1) === '*') {
                  $fakeDto->username = rtrim($name, '*');
                  $fakeDto->user_fullname = null;
                }
                else {
                  $fakeDto->username = $name;
                  $fakeDto->user_fullname = $name;
                }

                $top10UserDeposit->push($fakeDto);
              }
            }
          }

          $top10UserDeposit = $top10UserDeposit->sortByDesc('total')->take(7);
        }
      }
    }
    catch (\Throwable $e) {
    // Quietly fail
    }

    // $transactions = \App\Models\Transaction::where('type', 'account-buy')
    //   ->whereOr('type', 'account-v2-buy')
    //   ->where('created_at', '>=', now()->subHours(24))
    //   ->orderBy('id', 'desc')
    $transactions = Transaction::where(function ($sub) {
      $sub->where('type', 'account-buy')
        ->orWhere('type', 'account-v2-buy');
    })->where('created_at', '>=', now()->subHours(24))
      ->orderBy('id', 'desc')
      ->get();

    $listAccountBuy = "";

    $lang = currentLang();

    if ($lang === 'vn') {
      foreach ($transactions as $transaction) {
        $listAccountBuy .= "<span style=\"color: #504099\">" . Helper::hideUsername($transaction->username) . "</span> " . __t('cách đây') . " <span style=\"color: #E25E3E\">" . Helper::getTimeAgo($transaction->created_at) . "</span> " . __t('đã mua tài khoản') . " <span style=\"color: #279EFF\">#" . ($transaction->extras['code'] ?? $transaction['extras']['account_id'] ?? '-') . "</span> - " . __t('Giá') . " <span style=\"color: #4D2DB7\">" . Helper::formatCurrency($transaction->amount) . "</span> | \n";
      }
    }
    else {
      foreach ($transactions as $transaction) {
        $listAccountBuy .= "<span style=\"color: #504099\">" . Helper::hideUsername($transaction->username) . "</span> purchased account <span style=\"color: #279EFF\">#" . ($transaction->extras['code'] ?? $transaction['extras']['account_id'] ?? '-') . "</span> <span style=\"color: #E25E3E\">" . Helper::getTimeAgo($transaction->created_at) . "</span> for <span style=\"color: #4D2DB7\">" . Helper::formatCurrency($transaction->amount) . "</span> | \n";
      }
    }

    return view('index', compact('pin_groups', 'categories', 'serviceCategories', 'robuxServices', 'robuxTitle', 'minigamePos', 'top10UserDeposit', 'listAccountBuy'), [
      'pageTitle' => 'Mua Tài Khoản / Vật Phẩm',
    ]);
  }

  public function categories()
  {
    $categories = \App\Models\Category::where('status', 'active')->get();
    // Also get service categories if needed to be merged or displayed
    $serviceCategories = \App\Models\ServiceCategory::where('status', true)
      ->where('product_type', 'category')
      ->get();

    $categories = $categories->merge($serviceCategories)->sortByDesc('priority');

    $bconfig = Helper::getConfig('theme_custom');

    return view('categories', compact('categories', 'bconfig'), [
      'pageTitle' => 'Danh mục sản phẩm',
    ]);
  }

  public function category($slug)
  {
    // Redirect specific categories to their dedicated pages
    $redirects = [
      'tai-khoan' => 'store.account.list',
      'tai-khoan-v2' => 'store.accountv2.list',
      'vat-pham' => 'store.item.list',
      'cay-thue' => 'store.boosting.list',
    ];

    if (array_key_exists($slug, $redirects)) {
      return redirect()->route($redirects[$slug]);
    }

    // Try regular Category first
    $category = \App\Models\Category::where('slug', $slug)->first();

    // If not found, try ServiceCategory using robust slug matching
    if (!$category) {
      $serviceCategories = \App\Models\ServiceCategory::where('status', true)->get();
      foreach ($serviceCategories as $sc) {
        if (\Illuminate\Support\Str::slug($sc->name) === $slug) {
          $category = $sc;
          break;
        }
      }
    }

    if (!$category) {
      // Fallback to ID if numeric (legacy support)
      if (is_numeric($slug)) {
        $category = \App\Models\ServiceCategory::find($slug);
      }
    }

    if (!$category) {
      abort(404, 'Không tìm thấy chuyên mục');
    }

    $categories = collect([$category]);
    $bconfig = Helper::getConfig('theme_custom');

    return view('category', compact('category', 'categories', 'bconfig'), [
      'pageTitle' => $category->name,
    ]);
  }

  public function ref($ref = null)
  {
    if (is_null($ref)) {
      return redirect()->route('home');
    }

    $affiliate = Affiliate::where('code', $ref)->first();
    $campaign = Campaign::where('tracking_code', $ref)->first();

    if (!$affiliate && !$campaign) {
      abort(404);
    }

    // If affiliate exists, track it
    if ($affiliate) {
      if (!Auth::check()) {
        if (Cookie::has('ref_id') && Cookie::get('ref_id') === $affiliate->code) {
        // Already referenced, just redirect
        }
        else {
          // set cookie for ref, expire after 7 days
          Cookie::queue('ref_id', $affiliate->code, 10080);
          // +1 click
          $affiliate->increment('clicks');
        }
      }
    }

    // Always redirect to home with utm_source, so Middleware can pick it up
    // If it was an affiliate, ref=code. If not, ref=campaign_code.
    return redirect()->route('home', ['utm_source' => $ref]);
  }

  public function heartbeat()
  {
    if (Auth::check()) {
      $user = Auth::user();
      $expiresAt = now()->addMinutes(10);
      \Illuminate\Support\Facades\Cache::put('user-is-online-' . $user->id, true, $expiresAt);

      $user->update(['last_action' => now()]);

      // Lấy thông báo mới nhất chưa đọc
      $notifications = \App\Models\Notification::where('user_id', $user->id)
        ->where('is_read', false)
        ->where('created_at', '>=', now()->subMinutes(1))
        ->get();

      return response()->json([
        'status' => true,
        'notifications' => $notifications
      ]);
    }
    return response()->json(['status' => true]);
  }

  public function offline()
  {
    if (Auth::check()) {
      \Illuminate\Support\Facades\Cache::forget('user-is-online-' . Auth::id());
    }
    return response()->json(['status' => true]);
  }
}
