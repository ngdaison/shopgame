<?php

namespace App\Http\Controllers\Admin\AccountV2;

use App\Http\Controllers\Controller;
use App\Models\AccountV2Api;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function index()
    {
        $apis = AccountV2Api::orderBy('id', 'desc')->get();
        return view('admin.accountsv2.api.index', compact('apis'));
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'name'     => 'required|string|max:255',
            'type'     => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'url'      => 'required|url|max:255',
            'api_key'  => 'required|string|max:255',
            'coupon'   => 'nullable|string|max:255',
        ]);

        $api = AccountV2Api::create($payload);

        // Auto sync after store
        $this->syncSingleApi($api);

        Helper::addHistory('[V2] Thêm cấu hình API: ' . $api->name);

        return response()->json([
            'status'  => true,
            'message' => 'Thêm cấu hình API thành công',
        ]);
    }

    public function update(Request $request)
    {
        $payload = $request->validate([
            'id'       => 'required|exists:account_v2_apis,id',
            'name'     => 'required|string|max:255',
            'type'     => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'url'      => 'required|url|max:255',
            'api_key'  => 'required|string|max:255',
            'coupon'   => 'nullable|string|max:255',
        ]);

        $api = AccountV2Api::findOrFail($payload['id']);
        $api->update($payload);

        // Auto sync after update
        $this->syncSingleApi($api);

        Helper::addHistory('[V2] Cập nhật cấu hình API: ' . $api->name);

        return response()->json([
            'status'  => true,
            'message' => 'Cập nhật cấu hình API thành công',
        ]);
    }

    public function delete(Request $request)
    {
        $payload = $request->validate([
            'id' => 'required|exists:account_v2_apis,id',
        ]);

        $api = AccountV2Api::findOrFail($payload['id']);
        
        if ($api->items()->count() > 0) {
            return response()->json([
                'status'  => false,
                'message' => 'Cấu hình API này đang được sử dụng bởi ' . $api->items()->count() . ' sản phẩm, không thể xóa',
            ]);
        }

        $api->delete();

        Helper::addHistory('[V2] Xóa cấu hình API: ' . $api->name);

        return response()->json([
            'status'  => true,
            'message' => 'Xóa cấu hình API thành công',
        ]);
    }

    public function syncSingleApi(AccountV2Api $api)
    {
        try {
            $response = Http::get(rtrim($api->url, '/') . '/api/products.php', [
                'api_key' => $api->api_key,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] && isset($data['categories'])) {
                    // Cache products data directly in account_v2_apis table
                    $api->setProducts($data['categories'])->save();
                    return true;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Sync error for API ' . $api->url . ': ' . $e->getMessage());
        }
        return false;
    }

    public function sync(Request $request)
    {
        if ($request->input('key') !== '1') {
            return response('Unauthorized', 401);
        }

        $apis = AccountV2Api::all();
        $count = 0;

        foreach ($apis as $api) {
            if ($this->syncSingleApi($api)) {
                $count++;
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Successfully synced ' . $count . ' API sources.',
        ]);
    }
}
