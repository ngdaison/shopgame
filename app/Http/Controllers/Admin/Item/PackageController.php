<?php

namespace App\Http\Controllers\Admin\Item;

use App\Http\Controllers\Controller;
use App\Models\ItemGroup;
use App\Models\ItemPackage;
use Helper;
use Illuminate\Http\Request;

class PackageController extends Controller
{
  public function index($id)
  {
    $group = ItemGroup::findOrFail($id);
    $packages = ItemPackage::where('group_id', $id)->orderBy('priority', 'desc')->get();

    return view('admin.items.packages.index', compact('group', 'packages'));
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'group_id' => 'required|exists:item_groups,id',
      'name'     => 'required|string|max:255',
      'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1004800',
      'status'   => 'required|boolean',
      'priority' => 'nullable|integer',
    ]);

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public');
    }

    ItemPackage::create($payload);

    Helper::addHistory('Thêm gói vật phẩm ' . $payload['name']);

    return response()->json([
      'status' => true,
      'message' => 'Thêm gói thành công',
    ]);
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id'       => 'required|exists:item_packages,id',
      'name'     => 'required|string|max:255',
      'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1004800',
      'status'   => 'required|boolean',
      'priority' => 'nullable|integer',
    ]);

    $package = ItemPackage::findOrFail($payload['id']);

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public');
    }

    $package->update($payload);

    Helper::addHistory('Cập nhật gói vật phẩm ' . $payload['name']);

    return response()->json([
      'status' => true,
      'message' => 'Cập nhật gói #' . $payload['id'] . ' thành công',
    ]);
  }

  public function delete(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required|exists:item_packages,id',
    ]);

    $package = ItemPackage::findOrFail($payload['id']);

    if ($package->data()->count() > 0) {
      return response()->json([
        'status'  => 400,
        'message' => 'Gói này đang có sản phẩm, không thể xóa',
      ], 400);
    }

    Helper::addHistory('Xóa gói vật phẩm ' . $package->name);

    $package->delete();

    return response()->json([
      'status'  => 200,
      'message' => 'Xóa gói thành công',
    ]);
  }

  public function updatePriority(Request $request)
  {
    $payload = $request->validate([
      'id'       => 'required|exists:item_packages,id',
      'priority' => 'required|integer',
    ]);

    $item = ItemPackage::findOrFail($payload['id']);
    $item->update(['priority' => $payload['priority']]);

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật thứ tự thành công!',
    ]);
  }
}
