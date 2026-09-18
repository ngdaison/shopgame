<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
  public function index(Request $request)
  {
    $categories = [];

    // Account V1 Categories
    foreach (Category::where('status', 'active')->has('accountGroups')->get() as $val1) {
      $categories[] = [
        'id'   => $val1->id,
        'name' => $val1->name,
        'data' => $val1->accountGroups()->where('status', true)->get(['groups.id', 'groups.name', 'groups.image', 'groups.slug', 'groups.created_at']),
        'type' => 'v1',
      ];
    }

    // Account V2 Categories
    foreach (Category::where('status', 'active')->has('accountV2Groups')->get() as $val2) {
      $categories[] = [
        'id'   => $val2->id,
        'name' => $val2->name,
        'data' => $val2->accountV2Groups()->where('status', true)->get(['group_v2_s.id', 'group_v2_s.name', 'group_v2_s.image', 'group_v2_s.slug', 'group_v2_s.created_at'])?->makeHidden('items'),
        'type' => 'v2',
      ];
    }

    return response()->json([
      'data'    => $categories,
      'meta'    => [
        'total' => count($categories),
      ],
      'status'  => 200,
      'message' => 'Lấy danh sách danh mục thành công',
    ]);
  }
}
