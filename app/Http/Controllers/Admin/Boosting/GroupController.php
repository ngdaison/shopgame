<?php

namespace App\Http\Controllers\Admin\Boosting;

use App\Http\Controllers\Controller;
use App\Models\GBGroup;
use Helper;
use Illuminate\Http\Request;

class GroupController extends Controller
{
  public function index(Request $request)
  {
    $query = GBGroup::orderBy('priority', 'desc');

    if ($request->has('category_id') && !empty($request->category_id)) {
      $query->whereHas('categories', function($q) use ($request) {
          $q->where('categories.id', $request->category_id);
      });
    }

    $groups     = $query->get();
    $categories = \App\Models\Category::orderBy('priority', 'desc')->get();

    return view('admin.boosting.groups.index', compact('groups', 'categories'));
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'category_id' => 'required',
      'category_id.*' => 'exists:categories,id',
      'name'     => 'required|string|max:255',
      'sub_name' => 'nullable|string|max:255',
      'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1004800',
      'descr'    => 'nullable|string',
      'status'   => 'required|boolean',
      'priority' => 'nullable|integer',
    ]);

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public');
    }


    $payload['slug'] = GBGroup::generateSlug($payload['name']);

    $categoryIds = (array) $payload['category_id'];
    $payload['category_id'] = $categoryIds[0] ?? null;

    $group = GBGroup::create(array_merge($payload, [
      'username'      => $request->user()->username,
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
      'id'       => 'required|exists:g_b_groups,id',
      'descr'    => 'nullable|string|max:20480',
      'name'     => 'required|string|max:255',
      'sub_name' => 'nullable|max:255',
      'status'   => 'required|boolean',
      'priority' => 'nullable|integer',
    ]);

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public');
    }

    $group = GBGroup::findOrFail($payload['id']);

    $payload['slug'] = GBGroup::generateSlug($payload['name']);
    $payload['category_name'] = ''; // Deprecated

    if ($request->has('category_id')) {
      $categoryIds = (array) $request->category_id;
      $payload['category_id'] = $categoryIds[0] ?? null;
      $group->update($payload);
      $group->categories()->sync($categoryIds);
    } else {
      $group->update($payload);
    }

    Helper::addHistory('Cập nhật nhóm vật phẩm ' . $payload['name']);

    return response()->json([
      'status' => true,
      'message' => 'Cập nhật nhóm #' . $payload['id'] . ' thành công',
    ]);
  }

  public function delete(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required|exists:g_b_groups,id',
    ]);

    $group = GBGroup::findOrFail($payload['id']);

    if ($group->packages()->count() > 0) {
      return response()->json([
        'status'  => 400,
        'message' => 'Nhóm này đang có gói dịch vụ, không thể xóa',
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
      'id'       => 'required|exists:g_b_groups,id',
      'priority' => 'required|integer',
    ]);

    $item = GBGroup::findOrFail($payload['id']);
    $item->update(['priority' => $payload['priority']]);

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật thứ tự thành công!',
    ]);
  }
}
