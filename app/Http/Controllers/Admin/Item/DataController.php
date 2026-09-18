<?php

namespace App\Http\Controllers\Admin\Item;

use App\Http\Controllers\Controller;
use App\Models\Ingames;
use App\Models\ItemGroup;
use App\Models\ItemData;
use Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
{
  public function index(Request $request, $id = null)
  {
    $group    = ItemGroup::findOrFail($id);
    $packages = $group->packages;
    $ingames  = Ingames::orderBy('id', 'desc')->where('status', true)->get();

    $defaultProduct = DB::table('product_form_defaults')
        ->where('group_id', $id)
        ->where('type', 'items')
        ->value('data');

    return view('admin.items.data.index', compact('group', 'ingames', 'packages', 'defaultProduct'));
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'id'          => 'required|exists:item_groups,id',
      'type'        => 'required|string|in:addfriend,user_pass,user,gamepass',
      'name'        => 'required|string',
      'code'        => 'nullable|integer|unique:item_data',
      'price'       => 'required|integer',
      'robux'       => 'required|integer',
      'image'       => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:1004800',
      'discount'    => 'required|integer',
      'status'      => 'required|boolean',
      'ingame_id'   => 'nullable|exists:ingames,id',
      'package_id'  => 'nullable|array',
      'package_id.*' => 'exists:item_packages,id',
      'highlights'  => 'nullable|string',
      'description' => 'nullable|string',
      'warranty_hours' => 'nullable|integer',
    ]);

    $group = ItemGroup::findOrFail($payload['id']);

    $payload['group_id']  = $group->id;
    $payload['ingame_id'] = Ingames::where('status', true)->first()->id ?? null;

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public', 'items/' . $group->id);
    }

    $highlights = explode(PHP_EOL, $payload['highlights'] ?? '');
    $highlights = array_map(function ($item) {
      return trim($item);
    }, $highlights);
    $highlights = array_filter($highlights, function ($item) {
      return !empty($item);
    });

    $payload['highlights'] = array_values($highlights);

    $autoCode = true;
    if (!empty($payload['code'])) {
      $autoCode = false;
    }

    $payload['code'] = $autoCode ? ItemData::generateCode() : $payload['code'];

    $data = ItemData::create($payload);
    $data->packages()->sync($packageIds);

    Helper::addHistory('[ITEMS] Thêm sản phẩm ' . $data->name . ' vào nhóm ' . $group->name);

    return response()->json([
      'status' => true,
      'message' => 'Thêm sản phẩm vào nhóm thành công',
    ]);
  }

  public function saveDefault(Request $request)
  {
      $request->validate([
          'id' => 'required|exists:item_groups,id',
          'name' => 'nullable|string',
          'price' => 'nullable|integer',
          'robux' => 'nullable|integer',
          'discount' => 'nullable|integer',
          'status' => 'nullable|boolean',
          'warranty_hours' => 'nullable|integer',
          'package_id' => 'nullable|array',
          'description' => 'nullable|string',
          'highlights' => 'nullable|string',
      ]);

      $group = ItemGroup::findOrFail($request->id);

      DB::table('product_form_defaults')->updateOrInsert(
          [
              'group_id' => $group->id,
              'type' => 'items'
          ],
          [
              'data' => json_encode($request->only([
                  'name', 'price', 'robux', 'discount', 'status', 'warranty_hours', 'package_id', 'description', 'highlights'
              ])),
              'updated_at' => now(),
              'created_at' => now()
          ]
      );

      return response()->json([
          'status' => true,
          'message' => 'Lưu mặc định thành công!',
      ]);
  }

  public function show($id)
  {
    $item     = ItemData::findOrFail($id);
    $group    = $item->group;
    $packages = $group->packages;
    $ingames  = Ingames::orderBy('id', 'desc')->where('status', true)->get();

    return view('admin.items.data.show', compact('item', 'ingames', 'packages'));
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id'          => 'required|exists:item_data,id',
      'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1004800',
      'code'        => 'nullable|integer|unique:item_data,code,' . $request->id . ',id',
      'type'        => 'required|string|in:addfriend,user_pass,user,gamepass',
      'name'        => 'required|string',
      'price'       => 'required|integer',
      'robux'       => 'required|integer',
      'discount'    => 'required|integer',
      'status'      => 'required|boolean',
      'package_id'  => 'nullable|array',
      'package_id.*' => 'exists:item_packages,id',
      'highlights'  => 'nullable|string',
      'description' => 'nullable|string',
      'warranty_hours' => 'nullable|integer',
    ]);

    $item = ItemData::findOrFail($payload['id']);

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public', 'items/' . $item->group_id);
    }

    $highlights = explode(PHP_EOL, $payload['highlights'] ?? '');
    $highlights = array_map(function ($item) {
      return trim($item);
    }, $highlights);
    $highlights = array_filter($highlights, function ($item) {
      return !empty($item);
    });

    $payload['ingame_id']  = Ingames::where('status', true)->first()->id ?? null;
    $payload['highlights'] = array_values($highlights);

    if ($request->has('package_id')) {
      $packageIds = (array) $request->package_id;
      $payload['package_id'] = $packageIds[0] ?? null;
      $item->packages()->sync($packageIds);
    } else {
      // If package_id is not present in the request, we should not update the package_id column
      // and also not sync packages, leaving them as they are.
      unset($payload['package_id']);
    }

    $item->update($payload);

    Helper::addHistory('[ITEMS] Cập nhật sản phẩm ' . $item->name);

    return response()->json([
      'status' => true,
      'message' => 'Cập nhật sản phẩm thành công',
    ]);
  }

  public function updateList(Request $request)
  {
    $validated = $request->validate([
      'ids'      => 'required|array',
      'ids.*'    => 'required|exists:item_data,id',
      'rate'     => 'nullable|integer',
      'price'    => 'nullable|integer',
      'discount' => 'nullable|integer',
    ]);

    try {
      DB::beginTransaction();

      foreach ($validated['ids'] as $id) {
        $item = ItemData::findOrFail($id);

        if (isset($validated['price'])) {
          $item->price = $validated['price'];
        }

        if (isset($validated['discount'])) {
          $item->discount = $validated['discount'];
        }

        if (isset($payload['rate']) && $item->robux) {
          $item->price = $item->robux * $validated['rate'];
        }

        $item->save(); // Thêm dòng này để lưu thay đổi
      }

      Helper::addHistory('[ITEMS] Cập nhật danh sách sản phẩm: ' . implode(', ', $validated['ids']));

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Cập nhật thành công',
      ]);

    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'success' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
      ], 500);
    }
  }

  public function delete(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required|exists:item_data,id',
    ]);

    $data = ItemData::findOrFail($payload['id']);

    Helper::addHistory('Xóa sản phẩm ' . $data->name . ' trong nhóm ' . $data->group->name);

    $data->delete();

    return response()->json([
      'status'  => 200,
      'message' => 'Xóa sản phẩm thành công',
    ]);
  }

  public function updatePriority(Request $request)
  {
    $payload = $request->validate([
      'id'       => 'required|exists:item_data,id',
      'priority' => 'required|integer',
    ]);

    $item = ItemData::findOrFail($payload['id']);
    $item->update(['priority' => $payload['priority']]);

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật thứ tự thành công!',
    ]);
  }
}
