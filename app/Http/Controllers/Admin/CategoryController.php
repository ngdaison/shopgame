<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Helper;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
  public function index()
  {
    $categories = Category::all();

    return view('admin.categories.index', compact('categories'));
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'name'     => 'required|string|max:255',
      'sub_name' => 'nullable|string|max:255',
      'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10048',
      'status'   => 'required|in:active,draft',
      'priority' => 'nullable|integer',
    ]);

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public');
    }

    $payload['slug']     = Category::generateSlug($payload['name']);
    $payload['username'] = auth()->user()->username;

    Category::create($payload);

    Helper::addHistory('Thêm danh mục tập trung: ' . $payload['name']);

    return response()->json([
      'status' => true,
      'message' => 'Thêm danh mục thành công',
    ]);
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id'       => 'required|exists:categories,id',
      'name'     => 'required|string|max:255',
      'sub_name' => 'nullable|string|max:255',
      'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10048',
      'status'   => 'required|in:active,draft',
      'priority' => 'nullable|integer',
    ]);

    $category = Category::findOrFail($payload['id']);

    if ($request->hasFile('image')) {
      if ($category->image) {
        Helper::deleteFile($category->image);
      }
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public');
    }

    if ($category->name !== $payload['name']) {
        $payload['slug'] = Category::generateSlug($payload['name'], $category->id);
    }

    $category->update($payload);

    Helper::addHistory('Cập nhật danh mục tập trung: ' . $payload['name']);

    return response()->json([
      'status' => true,
      'message' => 'Cập nhật danh mục thành công',
    ]);
  }

  public function delete(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required|exists:categories,id',
    ]);

    $category = Category::findOrFail($payload['id']);

    Helper::addHistory('Xóa danh mục tập trung: ' . $category->name);

    if ($category->image) {
      Helper::deleteFile($category->image);
    }

    $category->delete();

    return response()->json([
      'status'  => true,
      'message' => 'Xóa danh mục thành công',
    ]);
  }

  public function updatePriority(Request $request)
  {
    $payload = $request->validate([
      'id'       => 'required|exists:categories,id',
      'priority' => 'required|integer',
    ]);

    $item = Category::findOrFail($payload['id']);
    $item->update(['priority' => $payload['priority']]);

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật thứ tự thành công!',
    ]);
  }
}
