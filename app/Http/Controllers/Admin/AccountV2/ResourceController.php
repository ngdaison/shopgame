<?php

namespace App\Http\Controllers\Admin\AccountV2;

use App\Http\Controllers\Controller;
use App\Models\ListItemV2;
use App\Models\ResourceV2S;
use Helper;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
  public function index($id)
  {
    $item = ListItemV2::findOrFail($id);

    // Nếu sản phẩm là loại API (kết nối từ website khác), không cho phép truy cập
    if ($item->client_type === 'api' && $item->api_config_id) {
      return redirect()->back()->with('error', 'Sản phẩm này sử dụng API bên ngoài, không thể quản lý kho hàng tại đây');
    }

    return view('admin.accountsv2.resources.index', compact('item'));
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'id'       => 'required|exists:list_item_v2_s,id',
      'accounts' => 'required|string',
    ]);

    $item = ListItemV2::findOrFail($payload['id']);

    // Nếu sản phẩm là loại API (kết nối từ website khác), không cho phép thêm tài khoản
    if ($item->client_type === 'api' && $item->api_config_id) {
      return response()->json([
        'status'  => false,
        'message' => 'Sản phẩm này sử dụng API bên ngoài, không thể thêm dữ liệu acc tại đây',
      ]);
    }

    $listAccount = explode(PHP_EOL, $payload['accounts']);
    $listAccount = array_map(function ($item) {
      return str_replace("\r", '', $item);
    }, $listAccount);
    $listAccount = array_filter($listAccount, function ($item) {
      return !empty(trim($item));
    });

    if (count($listAccount) === 0) {
      return redirect()->back()->with('error', 'Vui lòng nhập danh sách tài khoản');
    }

    $created = [];

    foreach ($listAccount as $account) {
      $created[] = ResourceV2S::create([
        'code'       => $item->code,
        'username'   => $account,
      ]);
    }

    Helper::addHistory('[V2] Thêm ' . count($created) . ' tài khoản vào ' . $item->name);

    Helper::addHistory('[V2] Thêm ' . count($created) . ' tài khoản vào ' . $item->name);

    return response()->json([
      'status'  => true,
      'message' => 'Thêm ' . count($created) . ' tài khoản vào ' . $item->name . ' thành công',
    ]);
  }

  public function show(Request $request, $id)
  {
    $resource = ResourceV2S::findOrFail($id);

    if (!$resource) {
      return redirect()->back()->with('error', 'Không tìm thấy tài khoản');
    }

    return view('admin.accountsv2.resources.show', compact('resource'));
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id'         => 'required|exists:resource_v2_s,id',
      'username'   => 'nullable|string',
    ]);

    $resource = ResourceV2S::findOrFail($payload['id']);

    $resource->update($payload);

    Helper::addHistory('[V2] Cập nhật tài khoản ' . $resource->username);

    Helper::addHistory('[V2] Cập nhật tài khoản ' . $resource->username);

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật tài khoản ' . $resource->username . ' thành công',
    ]);
  }


  public function export(Request $request)
  {
    $payload = $request->validate([
      'ids' => 'required|array',
    ]);

    $resources = ResourceV2S::whereIn('id', $payload['ids'])->get();

    $output = "XUẤT NGÀY " . now() . " \n";

    // foreach ($resources as $resource) {
    //   $output .= "Tài khoản: " . $resource->username . "\n";
    //   $output .= "Mật khẩu: " . $resource->password . "\n";
    //   $output .= "Additional: " . $resource->extra_data . "\n";
    //   $output .= "------------------------\n";
    // }

    foreach ($resources as $resource) {
      $output .= $resource->username . "\n";
    }

    $filename = 'export-' . now()->format('d-m-Y') . '.txt';

    $output .= 'Tổng số tài khoản: ' . $resources->count();

    Helper::addHistory('[V2] Xuất ' . $resources->count() . ' tài khoản');

    return response()->json([
      'name'    => $filename,
      'data'    => $output,
      'status'  => true,
      'message' => 'Xuất tài ' . $resources->count() . ' khoản thành công',
    ]);
  }


  public function delete(Request $request)
  {
    $payload = $request->validate([
      'ids' => 'required|array',
    ]);

    $resources = ResourceV2S::whereIn('id', $payload['ids'])->get();

    $deleted = [];

    foreach ($resources as $resource) {
      if ($resource->delete()) {
        $deleted[] = $resource->username;
      }
    }

    Helper::addHistory('[V2] Xóa tài khoản ' . implode(', ', $deleted));

    Helper::addHistory('[V2] Xóa tài khoản ' . implode(', ', $deleted));

    return response()->json([
      'status'  => true,
      'message' => 'Xóa tài ' . $resources->count() . ' khoản thành công',
    ]);
  }
}
