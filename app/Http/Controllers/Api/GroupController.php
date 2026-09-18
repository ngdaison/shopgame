<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupV2;
use Illuminate\Http\Request;

class GroupController extends Controller
{
  public function index(Request $request)
  {
    $payload = $request->validate([
      'id'   => 'required|integer',
      'type' => 'required|in:v1,v2',
    ]);

    $id   = $payload['id'];
    $type = $payload['type'];

    if ($type === 'v1') {
      $groups = Group::where('category_id', $id)->where('status', true)->get([
        'id',
        'name',
        'slug',
        'image',
        'meta_seo',
        'descr_seo',
        'sold',
        'game_type',
        'category_id',
        'category_name',
        'created_at',
      ]);

      return response()->json([
        'data'   => $groups,
        'meta'   => [
          'total' => $groups->count(),
        ],
        'status' => 200,
      ]);
    } else if ($type === 'v2') {
      $groups = GroupV2::where('category_id', $id)->where('status', true)->get([
        'id',
        'name',
        'slug',
        'image',
        'meta_seo',
        'descr_seo',
        'sold',
        'game_type',
        'category_id',
        'category_name',
        'created_at',
      ])->makeHidden('items');

      return response()->json([
        'data'   => $groups,
        'meta'   => [
          'total' => $groups->count(),
        ],
        'status' => 200,
      ]);

    }
  }
}
