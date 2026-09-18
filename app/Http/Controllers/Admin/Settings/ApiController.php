<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\ApiConfig;
use Illuminate\Http\Request;

class ApiController extends Controller
{
  public function index()
  {
    $apis = [];

    foreach (ApiConfig::all() as $item) {
      $apis[strtolower($item->name)] = $item->value;
    }

    return view('admin.settings.apis', $apis);
  }

  public function update(Request $request)
  {
    $type = $request->input('type', null);
    $value = [];

    // if (in_array($type, ['web2m_vietcombank', 'web2m_tpbank', 'web2m_mbbank', 'web2m_acb'])) {
    if (strpos($type, 'web2m_') !== false || strpos($type, 'stc_') !== false) {
      $value = $request->validate([
        'api_token' => 'nullable|string|max:255',
        'account_number' => 'nullable|string|max:255',
        'account_password' => 'nullable|string|max:255',
      ]);
    }
    elseif ($type === 'charging_card') {
      $value = $request->validate([
        'fees' => 'nullable|array',
        'api_url' => 'nullable|url|max:255',
        'partner_id' => 'nullable|string|max:255',
        'partner_key' => 'nullable|string|max:255',
      ]);
    }
    elseif ($type === 'smtp_detail') {
      $value = $request->validate([
        'host' => 'nullable|string|max:255',
        'port' => 'nullable|integer|max:65535',
        'user' => 'nullable|string|max:255',
        'pass' => 'nullable|string|max:255',
        'is_active' => 'nullable|boolean',
        'encryption' => 'nullable|string|in:tls,ssl,',
      ]);
    }
    elseif ($type === 'web2m_momo' || $type === 'web2m_tsr') {
      $value = $request->validate([
        'api_token' => 'nullable|string|max:255',
      ]);
    }
    else if (in_array($type, ['auth_facebook', 'auth_google', 'auth_discord'])) {
      $value = $request->validate([
        'client_key' => 'nullable|string|max:255',
        'redirect_url' => 'nullable|url',
        'client_secret' => 'nullable|string|max:255',
        'client_status' => 'nullable|boolean',
      ]);
    }
    else if ($type === 'fpayment') {
      $value = $request->validate([
        'exchange' => 'required|integer',
        'token_wallet' => 'required|string',
        'address_wallet' => 'required|string',
      ]);
    }
    else if ($type === 'perfect_money') {
      $value = $request->validate([
        'exchange' => 'required|integer',
        'account_id' => 'required|string',
        'passphrase' => 'required|string',
      ]);
    }
    else if ($type === 'paypal') {
      $value = $request->validate([
        'exchange' => 'required|integer',
        'client_id' => 'required|string',
        'client_secret' => 'required|string',
      ]);
    }
    else if ($type === 'imgbb') {
      $value = $request->validate([
        'client_key' => 'nullable|string',
      ]);
    }
    else if ($type === 's3aws') {
      $value = $request->validate([
        'AWS_ACCESS_KEY_ID' => 'nullable|string',
        'AWS_SECRET_ACCESS_KEY' => 'nullable|string',
        'AWS_DEFAULT_REGION' => 'nullable|string',
        'AWS_BUCKET' => 'nullable|string',
      ]);
    }
    else if ($type === 'do_spaces') {
      $value = $request->validate([
        'DO_SPACES_KEY' => 'nullable|string',
        'DO_SPACES_SECRET' => 'nullable|string',
        'DO_SPACES_REGION' => 'nullable|string',
        'DO_SPACES_BUCKET' => 'nullable|string',
        'DO_SPACES_URL' => 'nullable|string',
      ]);
    }
    else if ($type === 'currency_freaks') {
      $value = $request->validate([
        'api_key' => 'required|string',
      ]);
    }
    else if ($type === 'chatgpt') {
      $value = $request->validate([
        'chatgpt_api_key' => 'nullable|string|max:255',
        'chatgpt_model' => 'nullable|string|max:255',
        'max_tokens' => 'nullable|integer|min:1',
        'temperature' => 'nullable|numeric|between:0,2',
      ]);
    }
    else {
      return response()->json([
        'status' => false,
        'message' => 'Cấu hình API không tồn tại.',
      ]);
    }

    $config = ApiConfig::firstOrCreate(['name' => $type], ['value' => []]);

    $config->update([
      'value' => $value,
    ]);

    session()->flash('success', 'Cập nhật API thành công [' . $type . '].');

    return response()->json([
      'status' => true,
      'message' => 'Cập nhật API thành công [' . $type . '].',
    ]);
  }
}
