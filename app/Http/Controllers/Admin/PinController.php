<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PinGroup;
use Helper;
use Illuminate\Http\Request;

class PinController extends Controller
{
  public function index(Request $request)
  {
    $pin_groups = PinGroup::orderBy('id', 'desc')->get();

    return view('admin.pins.index', compact('pin_groups'));
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'name'      => 'required|string',
      'link'      => 'required|string',
      'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:20048',
      'status'    => 'required|boolean',
      'open_type' => 'required|string|in:_blank,_self',
    ]);

    $payload['image'] = Helper::uploadFile($request->file('image'), 'public');

    if (!$payload['image']) {
      return back()->with('error', 'Failed to upload image');
    }

    $pin = PinGroup::create($payload);

    Helper::addHistory("Tạo danh mục ghim #$pin->id [$pin->name]");

    return response()->json([
      'status'  => true,
      'message' => 'Tạo danh mục ghim mới thành công',
    ]);
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id'        => 'required|integer',
      'name'      => 'required|string',
      'link'      => 'required|string',
      'image'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:20048',
      'status'    => 'required|boolean',
      'open_type' => 'required|string|in:_blank,_self',
    ]);

    $pin = PinGroup::findOrFail($payload['id']);

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public');
      if (isset($pin->image)) {
        Helper::deleteFile($pin->image);
      }
    }

    $pin->update($payload);

    Helper::addHistory("Cập nhật danh mục ghim #$pin->id [$pin->name]");

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật danh mục ghim thành công',
    ]);
  }

  public function delete(Request $request)
  {
    $pin = PinGroup::findOrFail($request->id);

    if (isset($pin->image)) {
      Helper::deleteFile($pin->image);
    }

    $pin->delete();

    Helper::addHistory("Xóa danh mục ghim #$pin->id [$pin->name]");

    return response()->json([
      'status'  => true,
      'message' => 'Xóa danh mục ghim thành công',
    ]);
  }
}
