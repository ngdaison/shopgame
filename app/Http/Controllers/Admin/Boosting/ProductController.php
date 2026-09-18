<?php

namespace App\Http\Controllers\Admin\Boosting;

use App\Http\Controllers\Controller;
use App\Models\GBPackage;
use App\Models\GBProduct;
use Illuminate\Support\Facades\DB;
use Helper;
use Illuminate\Http\Request;

class ProductController extends Controller
{
  public function index(Request $request, $id = null)
  {
    $package = GBPackage::findOrFail($id);
    
    $defaultProduct = DB::table('product_form_defaults')
        ->where('group_id', $id)
        ->where('type', 'boosting')
        ->value('data');

    return view('admin.boosting.products.index', compact('package', 'defaultProduct'));
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'id'       => 'required|integer|exists:g_b_packages,id',
      'name'     => 'required|string',
      'price'    => 'required|numeric',
      'descr'    => 'nullable|string',
      'status'   => 'required|boolean',
      'priority' => 'nullable|integer',
      'warranty_hours' => 'nullable|integer',
    ]);

    $package = GBPackage::findOrFail($payload['id']);

    $package->products()->create(array_merge($payload, [
      'code'       => GBProduct::generateCode(),
      'package_id' => $package->id
    ]));

    Helper::addHistory('Thêm sản phẩm cày thuê ' . $payload['name']);

    return response()->json([
      'status' => true,
      'message' => 'Thêm sản phẩm thành công',
    ]);
  }

  public function saveDefault(Request $request)
  {
      $request->validate([
          'id' => 'required|integer', 
          'name' => 'nullable|string',
          'price' => 'nullable|numeric',
          'status' => 'nullable|boolean',
          'warranty_hours' => 'nullable|integer',
          'descr' => 'nullable|string',
      ]);
      
      $package = GBPackage::findOrFail($request->id);

      DB::table('product_form_defaults')->updateOrInsert(
          [
              'group_id' => $package->id,
              'type' => 'boosting'
          ],
          [
              'data' => json_encode($request->only([
                  'name', 'price', 'status', 'warranty_hours', 'descr'
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

  public function show(Request $request, $id)
  {
    $product = GBProduct::findOrFail($id);

    return view('admin.boosting.products.show', compact('product'));
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id'       => 'required|integer|exists:g_b_products,id',
      'name'     => 'required|string',
      'price'    => 'required|numeric',
      'descr'    => 'nullable|string',
      'status'   => 'required|boolean',
      'priority' => 'nullable|integer',
      'warranty_hours' => 'nullable|integer',
    ]);

    $product = GBProduct::findOrFail($payload['id']);

    $product->update($payload);

    Helper::addHistory('Cập nhật sản phẩm cày thuê ' . $payload['name']);

    return response()->json([
      'status' => true,
      'message' => 'Cập nhật sản phẩm thành công',
    ]);
  }

  public function delete(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required|integer|exists:g_b_products,id'
    ]);

    $product = GBProduct::findOrFail($payload['id']);

    $product->delete();

    Helper::addHistory('Xóa sản phẩm cày thuê ' . $product->name);

    return response()->json([
      'status'  => 200,
      'message' => 'Xóa sản phẩm thành công',
    ], 200);
  }

  public function updatePriority(Request $request)
  {
    $payload = $request->validate([
      'id'       => 'required|exists:g_b_products,id',
      'priority' => 'required|integer',
    ]);

    $item = GBProduct::findOrFail($payload['id']);
    $item->update(['priority' => $payload['priority']]);

    return response()->json([
      'status'  => true,
      'message' => 'Cập nhật thứ tự thành công!',
    ]);
  }
}
