<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\User;
use App\Models\VoucherLog;
use App\Models\WalletLog;
use App\Utils\ServerCtl;
use App\Utils\Service;
use Helper;
use Illuminate\Http\Request;

class PageController extends Controller
{
  public function privacyPolicy()
  {
    return view('pages.privacy-policy');
  }

  public function termsOfService(Request $request)
  {
    return view('pages.terms-of-service');
  }

  public function affiliates()
  {
    $user = User::findOrFail(auth()->id());
    $config = Helper::getConfig('affiliate_config');
    $histories = WalletLog::where('user_id', auth()->id())
      ->whereIn('type', ['affiliate', 'commission'])
      ->orderBy('id', 'desc')
      ->limit(100)
      ->get();


    if (empty($user->referral_code)) {
      $user->referral_code = strtoupper(\Illuminate\Support\Str::random(8));
      $user->save();
    }

    // 1. Try to find by user_id (Best)
    $affiliate = Affiliate::where('user_id', $user->id)->first();

    if (!$affiliate) {
      // 2. Fallback: Try to find by referral code (Strong link, if username changed)
      if ($user->referral_code) {
        $affiliate = Affiliate::where('code', $user->referral_code)->first();
      }

      // 3. Fallback: Try to find by username (Legacy link)
      if (!$affiliate) {
        $affiliate = Affiliate::where('username', $user->username)->first();
      }

      if ($affiliate) {
        // Found a legacy/orphaned record, claim it!
        $affiliate->update([
          'user_id' => $user->id,
          'username' => $user->username
        ]);
      }
      else {
        // 4. Create new if absolutely nothing found
        $affiliate = Affiliate::create([
          'user_id' => $user->id,
          'username' => $user->username,
          'code' => $user->referral_code
        ]);
      }
    }
    else {
      // Found by ID, ensure details are synced
      if ($affiliate->username !== $user->username) {
        $affiliate->update(['username' => $user->username]);
      }
    }

    $totalCommission = WalletLog::where('user_id', auth()->id())->where('type', 'commission')->sum('amount');
    $totalOrders = $affiliate->total_account_buy + $affiliate->total_item_buy + $affiliate->total_boost_buy;

    $withdrawals = \App\Models\CollaTransaction::where('user_id', auth()->id())
      ->where('type', 'affiliate_withdraw')
      ->orderBy('id', 'desc')
      ->limit(100)
      ->get();

    $banks = $user->banks()->where('status', true)->get();

    return view('pages.affiliates', compact('user', 'config', 'histories', 'affiliate', 'totalCommission', 'totalOrders', 'withdrawals', 'banks'));
  }

  public function apiDocs()
  {
    return view('pages.api-docs');
  }

  public function websiteBuilder()
  {
    return view('pages.website-builder');
  }

  public function contact()
  {
    return view('pages.contact');
  }
}
