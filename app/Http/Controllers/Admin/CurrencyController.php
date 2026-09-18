<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Currency;
use App\Services\CurrencyRateService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Helper; 
use App\Models\DomainSetting; // Assuming DomainSetting is used for availableDomains

class CurrencyController extends Controller
{
    public function index(CurrencyRateService $service)
    {
        $currencies = Currency::orderBy('id', 'desc')->get();
        $availableDomains = DomainSetting::select('domain')->distinct()->pluck('domain');
        $supported = $service->getSupportedCurrencies();

        return view('admin.currency.index', compact('currencies', 'availableDomains', 'supported'));
    }

    public function store(Request $request, CurrencyRateService $service)
    {
        $payload = $request->validate([
            'name'         => 'required|string|max:255',
            'code'         => 'required|string|max:10',
            'symbol_left'  => 'nullable|string|max:10',
            'symbol_right' => 'nullable|string|max:10',
            'decimals'     => 'required|integer|min:0|max:8',
            'rate'         => 'nullable|numeric|min:0',
            'separator'    => 'required|string|max:1',
            'status'       => 'required|boolean',
            'rate_mode'    => 'required|in:auto,manual',
        ]);

        if ($payload['rate_mode'] === 'auto') {
            $rates = $service->getLatestRates(['VND', $payload['code']]);
            $vndRate = $rates['VND'] ?? null;
            $codeRate = $rates[$payload['code']] ?? null;

            if ($vndRate && $codeRate) {
                $payload['rate'] = $vndRate / $codeRate;
                $payload['last_synced_at'] = now();
            }
        } elseif ($payload['rate_mode'] === 'manual' && isset($payload['rate'])) {
            $payload['rate'] = $payload['rate'] * 1000;
        }

        Currency::create($payload);

        // Clear cache
        Cache::forget('currency_' . $payload['code']);

        return response()->json([
            'status' => true,
            'message' => 'Thêm tiền tệ thành công',
        ]);
    }

    public function update(Request $request, CurrencyRateService $service)
    {
        $payload = $request->validate([
            'id'           => 'required|exists:currencies,id',
            'name'         => 'required|string|max:255',
            'code'         => 'required|string|max:10',
            'symbol_left'  => 'nullable|string|max:10',
            'symbol_right' => 'nullable|string|max:10',
            'decimals'     => 'required|integer|min:0|max:8',
            'rate'         => 'nullable|numeric|min:0',
            'separator'    => 'required|string|max:1',
            'status'       => 'required|boolean',
            'rate_mode'    => 'required|in:auto,manual',
            'domain'       => 'nullable|string|max:255',
            'is_default'   => 'nullable|boolean',
        ]);

        $currency = Currency::findOrFail($payload['id']);

        if ($payload['rate_mode'] === 'auto') {
            $rates = $service->getLatestRates(['VND', $payload['code']]);
            $vndRate = $rates['VND'] ?? null;
            $codeRate = $rates[$payload['code']] ?? null;

            if ($vndRate && $codeRate) {
                $payload['rate'] = $vndRate / $codeRate;
                $payload['last_synced_at'] = now();
            }
        } elseif ($payload['rate_mode'] === 'manual' && isset($payload['rate'])) {
            $payload['rate'] = $payload['rate'] * 1000;
        }

        $currency->update($payload);

        // Clear cache
        Cache::forget('currency_' . $payload['code']);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật tiền tệ #' . $payload['id'] . ' thành công',
        ]);
    }

    public function destroy($id)
    {
        $currency = Currency::findOrFail($id);

        if ($currency->is_default) {
            return response()->json(['status' => false, 'message' => 'Không thể xoá tiền tệ mặc định.']);
        }

        $currency->delete();
        Cache::forget('currency_' . $currency->code);
        return response()->json(['status' => true, 'message' => 'Xoá tiền tệ thành công.']);
    }

    public function syncAll(CurrencyRateService $service)
    {
        $currencies = Currency::where('rate_mode', 'auto')->where('status', true)->get();
        
        if ($currencies->isEmpty()) {
            return redirect()->back()->with('warning', 'Không có tiền tệ nào đang bật chế độ Auto.');
        }

        $codes = $currencies->pluck('code')->toArray();
        $codes[] = 'VND'; // Ensure VND is fetched for cross-rate
        $rates = $service->getLatestRates($codes);

        if (!$rates) {
            return redirect()->back()->with('error', 'Kết nối API thất bại. Vui lòng kiểm tra cấu hình.');
        }

        $count = 0;
        $vndRate = $rates['VND'] ?? 1;

        foreach ($currencies as $currency) {
            if (isset($rates[$currency->code]) && $rates[$currency->code] > 0) {
                // Rate = VndRate / CodeRate (Code -> VND)
                // Example: VND (~25000) / USD (1) = 25000. 
                $newRate = $vndRate / $rates[$currency->code];
                
                $currency->update([
                    'rate' => $newRate,
                    'last_synced_at' => now()
                ]);
                Cache::forget('currency_' . $currency->code);
                $count++;
            }
        }

        return redirect()->back()->with('success', 'Đã cập nhật tỷ giá cho ' . $count . ' tiền tệ.');
    }
}
