<?php

namespace App\Http\Controllers\Admin\Item;

use App\Http\Controllers\Controller;
use App\Models\Ingames;
use App\Models\ItemData;
use Helper;
use Illuminate\Http\Request;

class InGameController extends Controller
{
  public function index(Request $request)
  {
    $ingames = Ingames::orderBy('id', 'desc')->get();

    return view('admin.items.ingames.index', compact('ingames'));
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'name'    => 'required|string',
      'status'  => 'required|boolean',
      'content' => 'required|string',
    ]);
    $name    = $payload['name'];
    $content = $payload['content'];

    // textarea to array => trim, unique, remove empty
    $content = array_values(array_unique(array_filter(array_map('trim', explode("\n", $content)))));
    $content = array_map('trim', $content);

    Ingames::create([
      'name'    => $name,
      'status'  => $payload['status'],
      'content' => $content,
    ]);

    Helper::addHistory('Thêm Ingame ' . $name);

    session()->flash('success', 'Thêm Ingame thành công');

    return response()->json([
      'status'  => true,
      'message' => 'Thêm Ingame thành công',
    ]);
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id'      => 'required|integer',
      'name'    => 'required|string',
      'status'  => 'required|boolean',
      'content' => 'required|string',
    ]);
    $id      = $payload['id'];
    $name    = $payload['name'];
    $content = $payload['content'];

    $ingame = Ingames::findOrFail($id);

    // textarea to array => trim, unique, remove empty
    $content = array_values(array_unique(array_filter(array_map('trim', explode("\n", $content)))));
    $content = array_map('trim', $content);

    $ingame->update([
      'name'    => $name,
      'status'  => $payload['status'],
      'content' => $content,
    ]);

    $status = $payload['status'] ? true : false;
    if ($status === true) {
      ItemData::where('type', 'user')->update([
        'ingame_id' => $id
      ]);
      // update other ingames to false
      Ingames::where('id', '!=', $id)->update([
        'status' => false
      ]);
    } else {
      // update all items to null
      ItemData::where('ingame_id', $id)->update([
        'ingame_id' => null
      ]);
    }

    Helper::addHistory('Cập nhật Ingame #' . $id);

    session()->flash('success', 'Cập nhật Ingame thành công');

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật Ingame thành công',
    ]);
  }

  public function delete(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required|integer',
    ]);
    $id      = $payload['id'];

    $ingame = Ingames::findOrFail($id);
    $ingame->delete();

    Helper::addHistory('Xóa Ingame #' . $id);

    session()->flash('success', 'Xóa Ingame thành công');

    return response()->json([
      'status'  => true,
      'message' => 'Xóa Ingame thành công',
    ]);
  }
}
