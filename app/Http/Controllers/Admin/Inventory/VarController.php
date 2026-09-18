<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryVar;
use Helper;
use Illuminate\Http\Request;

class VarController extends Controller
{
  public function index(Request $request)
  {
    $vars = InventoryVar::orderBy('id', 'desc')->get();

    return view('admin.inventory.var.index', compact('vars'));
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'name'          => 'required|string',
      'descr'         => 'nullable|string',
      'unit'          => 'required|string',
      'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
      'is_active'     => 'required|boolean',
      'form_inputs'   => 'nullable|string',
      'form_packages' => 'nullable|string',
      'min_withdraw'  => 'required|integer|min:0',
      'max_withdraw'  => 'required|integer|min:0',

    ]);

    $payload['is_active'] = $payload['is_active'] ? true : false;

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public', 'inventory_vars');
    }

    // Process form_inputs
    $inputs = [];
    if ($request->filled('form_inputs')) {
      $lines = explode("\n", $request->input('form_inputs'));
      foreach ($lines as $line) {
        $parts = explode('|', trim($line));
        if (count($parts) >= 2) {
          $input = [
            'label'   => $parts[0],
            'type'    => $parts[1],
            'options' => []
          ];
          if (count($parts) >= 3) {
            $input['options'] = explode(',', $parts[2]);
          }
          $inputs[] = $input;
        }
      }
    }
    $payload['form_inputs'] = $inputs;

    // Process form_packages
    $packages = [];
    if ($request->filled('form_packages')) {
      $lines = explode("\n", $request->input('form_packages'));
      foreach ($lines as $line) {
        if (trim($line) !== '') {
          $val            = trim($line);
          $packages[$val] = $val . ' ' . $payload['unit'];
        }
      }
    }
    $payload['form_packages'] = $packages;

    $inventoryVar = InventoryVar::create($payload);

    Helper::addHistory("Tạo loại phần thưởng mới thành công: $inventoryVar->name");

    return response()->json([
      'status'  => true,
      'message' => 'Tạo loại phần thưởng mới thành công',
    ]);
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id'            => 'required|integer|exists:inventory_vars,id',
      'name'          => 'required|string',
      'descr'         => 'nullable|string',
      'unit'          => 'required|string',
      'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10048',
      'is_active'     => 'required|boolean',
      'form_inputs'   => 'nullable|string',
      'form_packages' => 'nullable|string',
      'min_withdraw'  => 'required|integer|min:0',
      'max_withdraw'  => 'required|integer|min:0',
    ]);

    $inventoryVar = InventoryVar::find($payload['id']);

    if ($inventoryVar === null) {
      return redirect()->route('admin.inventories.vars')->with('error', 'Không tìm thấy loại phần thưởng này');
    }

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public', 'inventory_vars');
    }

    // Process form_inputs
    $inputs = [];
    if ($request->filled('form_inputs')) {
      $lines = explode("\n", $request->input('form_inputs'));
      foreach ($lines as $line) {
        $parts = explode('|', trim($line));
        if (count($parts) >= 2) {
          $input = [
            'label'   => $parts[0],
            'type'    => $parts[1],
            'options' => []
          ];
          if (count($parts) >= 3) {
            $input['options'] = explode(',', $parts[2]);
          }
          $inputs[] = $input;
        }
      }
    }
    $payload['form_inputs'] = $inputs;

    // Process form_packages
    $packages = [];
    if ($request->filled('form_packages')) {
      $lines = explode("\n", $request->input('form_packages'));
      foreach ($lines as $line) {
        if (trim($line) !== '') {
          $val            = trim($line);
          $packages[$val] = $val . ' ' . $payload['unit'];
        }
      }
    }
    $payload['form_packages'] = $packages;

    $inventoryVar->update($payload);

    Helper::addHistory("Cập nhật loại phần thưởng #" . $inventoryVar->id . ": $inventoryVar->name");

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật loại phần thưởng thành công',
    ]);
  }

  public function delete(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required|integer|exists:inventory_vars,id',
    ]);

    $inventoryVar = InventoryVar::find($payload['id']);

    if ($inventoryVar === null) {
      return redirect()->route('admin.inventories.vars')->with('error', 'Không tìm thấy loại phẩn thưởng này');
    }

    $inventoryVar->delete();

    Helper::addHistory("Deleted inventory var: $inventoryVar->name");

    return response()->json([
      'status'  => true,
      'message' => 'Đã xoá loại phần thưởng ' . $inventoryVar->unit . ' thành công',
    ]);
  }
}
