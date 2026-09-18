<?php

namespace App\Http\Controllers\Admin\Boosting;

use App\Http\Controllers\Controller;
use App\Models\GBGroup;
use App\Models\GBPackage;
use Helper;
use Illuminate\Http\Request;

class PackageController extends Controller
{
  public function index(Request $request, $id = null)
  {
    $group = GBGroup::findOrFail($id);

    return view('admin.boosting.packages.index', compact('group'));
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'id'       => 'required|integer|exists:g_b_groups,id',
      'name'     => 'required|string',
      'status'   => 'required|boolean',
      'priority' => 'nullable|integer',
    ]);

    $group = GBGroup::findOrFail($payload['id']);

    $group->packages()->create(array_merge($payload, [
      'code'  => GBPackage::generateCode(),
      'price' => 0, // Default price as it's not used
    ]));

    Helper::addHistory('Thêm gói dịch vụ cày thuê ' . $payload['name']);

    return response()->json([
      'status' => true,
      'message' => 'Thêm gói dịch vụ cày thuê thành công',
    ]);
  }

  public function show(Request $request, $id)
  {
    $package = GBPackage::findOrFail($id);

    return view('admin.boosting.packages.show', compact('package'));
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id'       => 'required|integer|exists:g_b_packages,id',
      'name'     => 'required|string',
      'status'   => 'required|boolean',
      'priority' => 'nullable|integer',
    ]);

    $package = GBPackage::findOrFail($payload['id']);

    $package->update($payload);

    Helper::addHistory('Cập nhật gói dịch vụ cày thuê ' . $payload['name']);

    return response()->json([
      'status' => true,
      'message' => 'Cập nhật gói dịch vụ cày thuê thành công',
    ]);
  }

  public function delete(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required|integer|exists:g_b_packages,id'
    ]);

    $package = GBPackage::findOrFail($payload['id']);

    if ($package->products()->count() > 0) {
      return response()->json([
        'status'  => 400,
        'message' => 'Gói này đang có sản phẩm, không thể xóa',
      ], 400);
    }

    $package->delete();

    Helper::addHistory('Xóa gói dịch vụ cày thuê ' . $package->name);

    return response()->json([
      'status'  => 200,
      'message' => 'Xóa gói dịch vụ cày thuê thành công',
    ], 200);
  }

  public function updatePriority(Request $request)
  {
    $payload = $request->validate([
      'id'       => 'required|exists:g_b_packages,id',
      'priority' => 'required|integer',
    ]);

    $item = GBPackage::findOrFail($payload['id']);
    $item->update(['priority' => $payload['priority']]);

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật thứ tự thành công!',
    ]);
  }
}
