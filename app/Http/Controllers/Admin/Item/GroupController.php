<?php

namespace App\Http\Controllers\Admin\Item;

use App\Http\Controllers\Controller;
use App\Models\ItemGroup;
use Helper;
use Illuminate\Http\Request;

class GroupController extends Controller
{
  public function index(Request $request)
  {
    $query = ItemGroup::orderBy('priority', 'desc');

    if ($request->has('category_id') && !empty($request->category_id)) {
      $query->whereHas('categories', function($q) use ($request) {
          $q->where('categories.id', $request->category_id);
      });
    }

    $groups     = $query->get();
    $categories = \App\Models\Category::orderBy('priority', 'desc')->get();

    return view('admin.items.groups.index', compact('groups', 'categories'));
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'category_id' => 'required',
      'category_id.*' => 'exists:categories,id',
      'name'       => 'required|string|max:255',
      'sub_name'   => 'nullable|string|max:255',
      'descr'      => 'nullable|string|max:1024',
      'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1004800',
      'image_package' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1004800',
      'status'     => 'required|boolean',
      'priority'   => 'nullable|integer',
      'warranty_hours' => 'nullable|integer',
      'is_center'  => 'required|boolean',
    ]);

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public');
    }

    if ($request->hasFile('image_package')) {
      $payload['image_package'] = Helper::uploadFile($request->file('image_package'), 'public');
    }

    $payload['slug'] = ItemGroup::generateSlug($payload['name']);

    $categoryIds = (array) $payload['category_id'];
    $payload['category_id'] = $categoryIds[0] ?? null;

    $group = ItemGroup::create(array_merge($payload, [
      'username'      => auth()->user()->username,
      'category_name' => '', // Deprecated
    ]));

    $group->categories()->sync($categoryIds);

    Helper::addHistory('Thêm nhóm vật phẩm ' . $payload['name']);

    return response()->json([
      'status' => true,
      'message' => 'Thêm nhóm thành công',
    ]);
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'category_id' => 'nullable',
      'category_id.*' => 'exists:categories,id',
      'id'         => 'required|exists:item_groups,id',
      'descr'      => 'nullable|string|max:1024',
      'name'       => 'required|string|max:255',
      'sub_name'   => 'nullable|string|max:255',
      'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1004800',
      'image_package' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1004800',
      'status'     => 'required|boolean',
      'priority'   => 'nullable|integer',
      'warranty_hours' => 'nullable|integer',
      'is_center'  => 'required|boolean',
    ]);

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public');
    }

    if ($request->hasFile('image_package')) {
      $payload['image_package'] = Helper::uploadFile($request->file('image_package'), 'public');
    }

    $group = ItemGroup::findOrFail($payload['id']);

    $payload['slug'] = ItemGroup::generateSlug($payload['name']);
    $payload['category_name'] = ''; // Deprecated

    if ($request->has('category_id')) {
      $categoryIds = (array) $request->category_id;
      $payload['category_id'] = $categoryIds[0] ?? null;
      $group->categories()->sync($categoryIds);
    } else {
      // If category_id is not present in the request, we should not update the category_id column
      // and also not sync categories, leaving them as they are.
      unset($payload['category_id']);
    }

    $group->update($payload);

    Helper::addHistory('Cập nhật nhóm vật phẩm ' . $payload['name']);

    return response()->json([
      'status' => true,
      'message' => 'Cập nhật nhóm #' . $payload['id'] . ' thành công',
    ]);
  }

  public function delete(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required|exists:item_groups,id',
    ]);

    $group = ItemGroup::findOrFail($payload['id']);

    if ($group->data()->count() > 0) {
      return response()->json([
        'status'  => 400,
        'message' => 'Nhóm này đang có tài khoản, không thể xóa',
      ], 400);
    }

    Helper::addHistory('Xóa nhóm vật phẩm ' . $group->name);

    $group->delete();

    return response()->json([
      'status'  => 200,
      'message' => 'Xóa nhóm thành công',
    ]);
  }

  public function updatePriority(Request $request)
  {
    $payload = $request->validate([
      'id'       => 'required|exists:item_groups,id',
      'priority' => 'required|integer',
    ]);

    $item = ItemGroup::findOrFail($payload['id']);
    $item->update(['priority' => $payload['priority']]);

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật thứ tự thành công!',
    ]);
  }
}
