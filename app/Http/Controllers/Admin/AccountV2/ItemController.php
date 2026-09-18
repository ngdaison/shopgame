<?php

namespace App\Http\Controllers\Admin\AccountV2;

use App\Http\Controllers\Controller;
use App\Models\GroupV2;
use App\Models\ListItemV2;
use App\Models\ResourceV2S;
use App\Models\ResourceV2O;
use App\Models\AccountV2Api;
use Helper;
use Illuminate\Http\Request;

class ItemController extends Controller
{
  public function index(Request $request, $id = null)
  {
    if ($id === null) {
      $payload = $request->validate([
        'sold'       => 'nullable|in:0,1',
        'group'      => 'nullable|integer',
        'username'   => 'nullable|string',
        'buyer_name' => 'nullable|string',
        'start_date' => 'nullable|date',
        'end_date'   => 'nullable|date',
        'domain'     => 'nullable|string',
      ]);

      if (isset($payload['sold']) && $payload['sold'] === '1') {
        $items = ResourceV2O::orderBy('id', 'desc')->where('buyer_name', '!=', null);
      } elseif (isset($payload['sold']) && $payload['sold'] === '0') {
        $items = ResourceV2S::orderBy('id', 'desc')->where('buyer_name', null);
      } else {
        $items = ResourceV2S::orderBy('id', 'desc');
      }

      if (isset($payload['username']) && $payload['username'] !== null) {
        $items = $items->where('username', 'like', '%' . $payload['username'] . '%');
      }

      if (isset($payload['buyer_name']) && $payload['buyer_name'] !== null) {
        $items = $items->where('buyer_name', 'like', '%' . $payload['buyer_name'] . '%');
      }

      if (isset($payload['start_date']) && $payload['start_date'] !== null) {
        $items = $items->whereDate('buyer_date', '>=', $payload['start_date']);
      }

      if (isset($payload['end_date']) && $payload['end_date'] !== null) {
        $items = $items->whereDate('buyer_date', '<=', $payload['end_date']);
      }

      if (isset($payload['domain']) && $payload['domain'] !== null) {
        $items = $items->where('domain', 'like', '%' . $payload['domain'] . '%');
      }

      $items  = $items->get();
      $groups = ListItemV2::orderBy('priority', 'desc')->get();
      $apis   = AccountV2Api::all();
      $apiProducts = $this->formatApiProducts($apis);

      return view('admin.accountsv2.items.stock', compact('items', 'groups', 'apis', 'apiProducts'));
    } else {
      $group  = GroupV2::findOrFail($id);
      $groups = ListItemV2::orderBy('priority', 'desc')->get();
      $apis   = AccountV2Api::all();
      $apiProducts = $this->formatApiProducts($apis);

      return view('admin.accountsv2.items.index', compact('group', 'groups', 'apis', 'apiProducts'));
    }
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'id'          => 'required|exists:group_v2_s,id',
      'type'        => 'nullable|string|in:account',
      'name'        => 'nullable|string|max:255',
      'code'        => 'nullable|integer|unique:list_item_v2_s,code',
      'cost'        => 'nullable|numeric|min:0',
      'price'       => 'required|numeric|min:0',
      'status'      => 'required|boolean',
      'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1004800',
      'is_bulk'     => 'nullable|integer',
      'discount'    => 'required|integer|min:0|max:100',
      'priority'    => 'nullable|integer',
      'list_image'  => 'nullable|array',

      'highlights'  => 'nullable|string',
      'description' => 'nullable|string',
      'allow_preorder' => 'nullable|boolean',
      'warranty_hours' => 'nullable|integer',
      'client_type' => 'nullable|string|in:normal,api',
      'api_domain'  => 'exclude_if:client_type,normal|required_without:api_config_id|nullable|string',
      'api_config_id' => 'exclude_if:client_type,normal|nullable|exists:account_v2_apis,id',
      'api_key'     => 'exclude_if:client_type,normal|required_without:api_config_id|nullable|string',
      'api_id'      => 'exclude_if:client_type,normal|required|nullable',
      'api_coupon'  => 'exclude_if:client_type,normal|nullable|string',
    ]);

    $group = GroupV2::findOrFail($payload['id']);

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public', 'items/' . $group->id);
    }

    $listItem = explode(PHP_EOL, $payload['list_item'] ?? '');
    $listItem = array_map(function ($item) {
      return str_replace("\r", '', $item);
    }, $listItem);
    $listItem = array_filter($listItem, function ($item) {
      return !empty(trim($item));
    });

    // Remove the mandatory stock check if not needed
    // if (count($listItem) === 0) {
    //   return response()->json([
    //     'status' => false,
    //     'message' => 'Vui lòng nhập danh sách tài khoản',
    //   ]);
    // }

    $highlights = explode(PHP_EOL, $payload['highlights'] ?? '');
    $highlights = array_map(function ($item) {
      return str_replace("\r", '', $item);
    }, $highlights);
    $highlights = array_filter($highlights, function ($item) {
      return !empty(trim($item));
    });
    $highlights = array_map(function ($item) {
      $item = explode(':', $item);
      if (count($item) === 2) {
        return [
          'name'  => trim($item[0]),
          'value' => trim($item[1]),
        ];
      }

      return trim($item[0]);
    }, $highlights);


    $autoCode = true;
    if (!empty($payload['code'])) {
      $autoCode = false;
    }

    // Sanitize API configuration fields
    if (isset($payload['client_type']) && $payload['client_type'] === 'api') {
      $payload['api_domain'] = trim(strip_tags($payload['api_domain'] ?? ''));
      $payload['api_key']    = trim(strip_tags($payload['api_key'] ?? ''));
      $payload['api_id']     = is_array($payload['api_id']) ? implode(',', $payload['api_id']) : trim(strip_tags($payload['api_id'] ?? ''));
      $payload['api_coupon'] = trim(strip_tags($payload['api_coupon'] ?? ''));
    }

    $code = $autoCode ? ListItemV2::generateCode() : $payload['code'];
    $item = ListItemV2::create([
      'name'        => $payload['name'] ?? $code,
      'code'        => $code,
      'type'        => 'account_group',
      'cost'        => $payload['cost'] ?? 0,
      'price'       => $payload['price'],
      'discount'    => $payload['discount'],
      'status'      => $payload['status'],
      'image'       => $payload['image'] ?? null,
      'is_bulk'     => $payload['is_bulk'] ?? 1,
      'allow_preorder' => $payload['allow_preorder'] ?? false,
      'highlights'  => $highlights,
      'description' => Helper::htmlPurifier($payload['description'] ?? ''),
      'list_image'  => $payload['list_image'] ?? [],
      'priority'    => 0,
      'group_id'    => $group->id,
      'warranty_hours' => $payload['warranty_hours'] ?? 0,
      'client_type' => $payload['client_type'] ?? 'normal',
      'api_domain'  => $payload['api_domain'] ?? null,
      'api_config_id' => $payload['api_config_id'] ?? null,
      'api_key'     => $payload['api_key'] ?? null,
      'api_id'      => $payload['api_id'] ?? null,
      'api_coupon'  => $payload['api_coupon'] ?? null,
    ]);

    $created = [];

    // Chỉ tạo resource nếu sản phẩm là loại normal (kho trong hệ thống)
    // Không tạo resource cho sản phẩm API (kết nối từ website khác)
    if ($item && $payload['client_type'] !== 'api') {
      foreach ($listItem as $account) {
        $created[] = ResourceV2S::create([
          'code'     => $item->code,
          'username' => $account,
        ]);
      }
    }

    Helper::addHistory('[V2] Thêm ' . count($created) . ' sản phẩm cho nhóm ' . $group->name);

    $message = count($created) > 0 
      ? 'Thêm ' . count($created) . ' tài khoản vào nhóm ' . $group->name . ' thành công'
      : 'Thêm sản phẩm vào nhóm ' . $group->name . ' thành công';

    return response()->json([
      'status' => true,
      'message' => $message,
    ]);
  }

  public function show($id)
  {
    $item = ListItemV2::findOrFail($id);
    $apis = AccountV2Api::all();
    $apiProducts = $this->formatApiProducts($apis);

    return view('admin.accountsv2.items.show', compact('item', 'apis', 'apiProducts'));
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id'          => 'required|exists:list_item_v2_s,id',
      'name'        => 'nullable|string|max:255',
      'code'        => 'nullable|integer|unique:list_item_v2_s,code,' . $request->id . ',id',
      'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1004800',
      'cost'        => 'nullable|numeric|min:0',
      'price'       => 'required|numeric|min:0',
      'status'      => 'required|boolean',
      'is_bulk'     => 'nullable|integer',
      'priority'    => 'nullable|integer',
      'discount'    => 'required|integer|min:0|max:100',
      'list_image'  => 'nullable|array',
      'highlights'  => 'nullable|string',
      'description' => 'nullable|string',
      'allow_preorder' => 'required|in:0,1',
      'warranty_hours' => 'nullable|integer',
      'client_type' => 'nullable|string|in:normal,api',
      'api_domain'  => 'exclude_if:client_type,normal|required_without:api_config_id|nullable|string',
      'api_config_id' => 'exclude_if:client_type,normal|nullable|exists:account_v2_apis,id',
      'api_key'     => 'exclude_if:client_type,normal|required_without:api_config_id|nullable|string',
      'api_id'      => 'exclude_if:client_type,normal|required|nullable',
      'api_coupon'  => 'exclude_if:client_type,normal|nullable|string',
    ]);

    $item = ListItemV2::findOrFail($payload['id']);

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public', 'items/' . $item->group_id);
    }

    $highlights = explode(PHP_EOL, $payload['highlights'] ?? '');
    $highlights = array_map(function ($item) {
      return str_replace("\r", '', $item);
    }, $highlights);
    $highlights = array_filter($highlights, function ($item) {
      return !empty(trim($item));
    });
    $highlights = array_map(function ($item) {
      $item = explode(':', $item);
      if (count($item) === 2) {
        return [
          'name'  => trim($item[0]),
          'value' => trim($item[1]),
        ];
      }

      return trim($item[0]);
    }, $highlights);

    $payload['highlights']  = $highlights;
    $payload['description'] = Helper::htmlPurifier($payload['description'] ?? '');
    $payload['cost']        = $payload['cost'] ?? 0;

    // Sanitize API configuration fields
    if (isset($payload['client_type']) && $payload['client_type'] === 'api') {
      $payload['api_domain'] = trim(strip_tags($payload['api_domain'] ?? ''));
      $payload['api_key']    = trim(strip_tags($payload['api_key'] ?? ''));
      $payload['api_id']     = is_array($payload['api_id']) ? implode(',', $payload['api_id']) : trim(strip_tags($payload['api_id'] ?? ''));
      $payload['api_coupon'] = trim(strip_tags($payload['api_coupon'] ?? ''));
    }

    // Ensure allow_preorder is set as integer
    $payload['allow_preorder'] = (int) $payload['allow_preorder'];

    if (empty($payload['code'])) {
      unset($payload['code']);
    }

    $item->update($payload);

    $listItem = explode(PHP_EOL, $payload['list_item'] ?? '');
    $listItem = array_map(function ($item) {
      return str_replace("\r", '', $item);
    }, $listItem);
    $listItem = array_filter($listItem, function ($item) {
      return !empty(trim($item));
    });

    $created = [];
    
    // Chỉ tạo resource nếu sản phẩm là loại normal (kho trong hệ thống)
    // Không tạo resource cho sản phẩm API (kết nối từ website khác)
    if ($payload['client_type'] !== 'api') {
      foreach ($listItem as $account) {
        $created[] = ResourceV2S::create([
          'code'     => $item->code,
          'username' => $account,
        ]);
      }
    }

    $historyMsg = '[V2] Cập nhật sản phẩm #' . $item->code . ' -> ' . ($payload['code'] ?? $item->code);
    if (count($created) > 0) {
      $historyMsg .= ' và thêm ' . count($created) . ' tài khoản';
    }
    Helper::addHistory($historyMsg);

    $message = 'Cập nhật sản phẩm #' . $item->code . ' thành công';
    if (count($created) > 0) {
      $message .= ' (đã thêm ' . count($created) . ' tài khoản mới)';
    }

    return response()->json([
      'status' => true,
      'message' => $message,
    ]);
  }

  public function delete(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required|exists:list_item_v2_s,id',
    ]);

    $item = ListItemV2::findOrFail($payload['id']);

    if ($item->resources()->count() > 0) {
      return response()->json([
        'status'  => 400,
        'message' => 'Sản phẩm này đang có tài khoản, không thể xóa',
      ], 400);
    }

    $item->delete();

    Helper::deleteFile($item->image);
    foreach ($item->list_image as $image) {
      Helper::deleteFile($image);
    }

    Helper::addHistory('[V2] Xóa sản phẩm #' . $item->code);

    return response()->json([
      'status'  => 200,
      'message' => 'Xóa sản phẩm #' . $item->code . ' thành công',
    ]);
  }

  public function deleteList(Request $request)
  {
    $validated = $request->validate([
      'ids'   => 'required|array',
      'ids.*' => 'required|exists:list_item_v2_s,id',
    ]);

    $successCount = 0;
    $errorCount   = 0;

    foreach ($validated['ids'] as $id) {
      try {
        $item = ListItemV2::findOrFail($id);

        if ($item->resources()->count() > 0) {
          $errorCount++;
          continue;
        }

        $item->delete();
        Helper::deleteFile($item->image);
        foreach ($item->list_image as $image) {
          Helper::deleteFile($image);
        }
        $successCount++;
      } catch (\Exception $e) {
        $errorCount++;
      }
    }

    Helper::addHistory('[V2] Xóa danh sách sản phẩm: ' . implode(', ', $validated['ids']));

    return response()->json([
      'success' => true,
      'message' => "Đã xóa {$successCount} sản phẩm thành công" . ($errorCount > 0 ? ", thất bại {$errorCount} sản phẩm (do còn tài khoản hoặc lỗi)" : ""),
    ]);
  }

  public function updateList(Request $request)
  {
    $validated = $request->validate([
      'ids'      => 'required|array',
      'ids.*'    => 'required|exists:list_item_v2_s,id',
      'price'    => 'nullable|integer',
      'cost'     => 'nullable|integer',
      'discount' => 'nullable|integer',
    ]);

    try {
      \Illuminate\Support\Facades\DB::beginTransaction();

      foreach ($validated['ids'] as $id) {
        $item = ListItemV2::findOrFail($id);

        if (isset($validated['price'])) {
          $item->price = $validated['price'];
        }

        if (isset($validated['cost'])) {
          $item->cost = $validated['cost'];
        }

        if (isset($validated['discount'])) {
          $item->discount = $validated['discount'];
        }

        $item->save();
      }

      Helper::addHistory('[V2] Cập nhật danh sách sản phẩm: ' . implode(', ', $validated['ids']));

      \Illuminate\Support\Facades\DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Cập nhật thành công',
      ]);
    } catch (\Exception $e) {
      \Illuminate\Support\Facades\DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
      ], 500);
    }
  }

  public function updatePriority(Request $request)
  {
    $payload = $request->validate([
      'id'       => 'required|exists:list_item_v2_s,id',
      'priority' => 'required|integer',
    ]);

    $item = ListItemV2::findOrFail($payload['id']);
    $item->update(['priority' => $payload['priority']]);

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật thứ tự thành công!',
    ]);
  }

  public function getApiProducts(Request $request)
  {
      $payload = $request->validate([
          'api_config_id' => 'required|exists:account_v2_apis,id',
      ]);

      $api = AccountV2Api::findOrFail($payload['api_config_id']);

      try {
          $response = \Illuminate\Support\Facades\Http::get(rtrim($api->url, '/') . '/api/products.php', [
              'api_key' => $api->api_key,
          ]);

          if ($response->successful()) {
              $data = $response->json();
              
              // Cache the raw categories data
              $categoriesData = $data['categories'] ?? [];
              $api->setProducts($categoriesData)->save();
              
              // Flatten products for the response
              $flatProducts = [];
              foreach ($categoriesData as $category) {
                  if (isset($category['products']) && is_array($category['products'])) {
                      foreach ($category['products'] as $product) {
                          $flatProducts[] = [
                              'external_id' => $product['id'] ?? null,
                              'name' => $product['name'] ?? '',
                              'price' => $product['price'] ?? 0,
                              'amount' => $product['amount'] ?? 0,
                              'description' => $product['description'] ?? null,
                              'flag' => $product['flag'] ?? null,
                              'min' => $product['min'] ?? 1,
                              'max' => $product['max'] ?? null,
                          ];
                      }
                  }
              }
              
              return response()->json([
                  'status' => 'success',
                  'msg' => 'Lấy dữ liệu thành công!',
                  'products' => $flatProducts
              ]);
          }

          return response()->json([
              'status' => false,
              'message' => 'Không thể kết nối với API nguồn: ' . $response->body()
          ]);
      } catch (\Exception $e) {
          return response()->json([
              'status' => false,
              'message' => 'Lỗi kết nối API: ' . $e->getMessage()
          ]);
      }
  }

  /**
   * Format API products for frontend
   * Flattens products from cached products_data (removes category grouping)
   * Returns products directly as a flat array for each API config
   */
  private function formatApiProducts($apis)
  {
      $formatted = [];
      foreach ($apis as $api) {
          $categoriesData = $api->getProducts();
          $flatProducts = [];
          
          if (!empty($categoriesData) && is_array($categoriesData)) {
              // Flatten products from all categories
              foreach ($categoriesData as $category) {
                  if (isset($category['products']) && is_array($category['products'])) {
                      foreach ($category['products'] as $product) {
                          // Keep all fields: id, name, price, amount, description, flag, min, max
                          $flatProducts[] = [
                              'external_id' => $product['id'] ?? null,
                              'name' => $product['name'] ?? '',
                              'price' => $product['price'] ?? 0,
                              'amount' => $product['amount'] ?? 0,
                              'description' => $product['description'] ?? null,
                              'flag' => $product['flag'] ?? null,
                              'min' => $product['min'] ?? 1,
                              'max' => $product['max'] ?? null,
                          ];
                      }
                  }
              }
          }
          
          if (!empty($flatProducts)) {
              $formatted[$api->id] = $flatProducts;
          }
      }
      return $formatted;
  }
}
