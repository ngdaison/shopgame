<?php

namespace App\Http\Controllers\Admin\Account;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Group;
use Helper;
use Illuminate\Http\Request;

class GroupController extends Controller
{
  public function index(Request $request)
  {
    $query = Group::orderBy('priority', 'desc');

    if ($request->has('category_id') && !empty($request->category_id)) {
      $query->whereHas('categories', function($q) use ($request) {
          $q->where('categories.id', $request->category_id);
      });
    }

    $groups     = $query->get();
    $categories = Category::orderBy('priority', 'desc')->get();

    return view('admin.accounts.groups.index', compact('groups', 'categories'));
  }

  public function create()
  {
    $categories = Category::all();

    return view('admin.accounts.groups.create', compact('categories'));
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'category_id' => 'required',
      'category_id.*' => 'exists:categories,id',
      'name'      => 'required|string|max:255',
      'sub_name'  => 'nullable|string|max:255',
      'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1004800',
      'descr'     => 'nullable|string',
      'meta_seo'  => 'nullable|array',
      'descr_seo' => 'nullable|string',
      'status'    => 'required|boolean',
      'priority'  => 'nullable|integer',
      'warranty_hours' => 'nullable|integer',
      'game_type' => 'required|string|in:game-khac,lien-minh,dot-kich,thue-dot-kich',
    ]);

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public');
    }

    $payload['slug']      = Group::generateSlug($payload['name']);
    $payload['descr']     = Helper::htmlPurifier($payload['descr'] ?? '');
    $payload['descr_seo'] = Helper::htmlPurifier($payload['descr_seo'] ?? '');

    $categoryIds = (array) $payload['category_id'];
    $payload['category_id'] = $categoryIds[0] ?? null;

    $group = Group::create(array_merge($payload, [
      'username'      => auth()->user()->username,
      'category_name' => '', // Deprecated
    ]));

    $group->categories()->sync($categoryIds);

    Helper::addHistory('Thêm nhóm ' . $payload['name']);

    return response()->json([
      'status' => true,
      'message' => 'Đã thêm nhóm ' . $payload['name'] . ' thành công',
    ]);
  }

  public function edit($id)
  {
    $categories = Category::all();
    $group      = Group::findOrFail($id);

    return view('admin.accounts.groups.edit', compact('categories', 'group'));
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'category_id' => 'nullable',
      'category_id.*' => 'exists:categories,id',
      'id'        => 'required|exists:groups,id',
      'name'      => 'required|string|max:255',
      'sub_name'  => 'nullable|string|max:255',
      'descr'     => 'nullable|string',
      'meta_seo'  => 'nullable|array',
      'descr_seo' => 'nullable|string',
      'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1004800',
      'status'    => 'required|boolean',
      'priority'  => 'nullable|integer',
      'warranty_hours' => 'nullable|integer',
      'game_type' => 'required|string|in:game-khac,lien-minh,dot-kich,thue-dot-kich',
    ]);

    $group = Group::findOrFail($payload['id']);

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public');
      if ($payload['image']) {
        Helper::deleteFile($group->image);
      }
    }

    $payload['descr']     = Helper::htmlPurifier($payload['descr'] ?? '');
    $payload['descr_seo'] = Helper::htmlPurifier($payload['descr_seo'] ?? '');
    $payload['category_name'] = ''; // Deprecated

    if ($request->has('category_id')) {
      $categoryIds = (array) $request->category_id;
      $payload['category_id'] = $categoryIds[0] ?? null;
      $group->update($payload);
      $group->categories()->sync($categoryIds);
    } else {
      $group->update($payload);
    }

    Helper::addHistory('Cập nhật nhóm ' . $payload['name']);

    return response()->json([
      'status' => true,
      'message' => 'Cập nhật nhóm #' . $payload['id'] . ' thành công',
    ]);
  }

  public function delete(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required|exists:groups,id',
    ]);

    $group = Group::findOrFail($payload['id']);

    if ($group->items()->count() > 0) {
      return response()->json([
        'status'  => 400,
        'message' => 'Nhóm này đang có tài khoản, không thể xóa',
      ], 400);
    }

    Helper::deleteFile($group->image);
    Helper::addHistory('Xóa nhóm ' . $group->name);

    $group->delete();

    return response()->json([
      'status'  => 200,
      'message' => 'Xóa nhóm thành công',
    ]);
  }

  public function updatePriority(Request $request)
  {
    $payload = $request->validate([
      'id'       => 'required|exists:groups,id',
      'priority' => 'required|integer',
    ]);

    $item = Group::findOrFail($payload['id']);
    $item->update(['priority' => $payload['priority']]);

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật thứ tự thành công!',
    ]);
  }
}
