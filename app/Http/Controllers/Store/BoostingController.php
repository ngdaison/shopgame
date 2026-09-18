<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BoostingController extends Controller
{
  public function index($slug)
  {
    $group = \App\Models\GBGroup::where('status', true)->where('slug', $slug)->firstOrFail();

    return view('store.boosting', compact('group'), [
      'pageTitle' => 'Xem sản phẩm ' . $group->name,
    ]);
  }

  public function list()
  {
    $groups = \App\Models\GBGroup::where('status', true)->whereHas('categories', function($q) {
        $q->where('name', 'LIKE', '%Cày Thuê%');
    })->orderBy('priority', 'desc')->get();

    $category = new \App\Models\Category([
        'name' => 'Cày thuê',
        'slug' => 'cay-thue',
        'image' => null
    ]);

    $category->setRelation('gbGroups', $groups);
    $categories = collect([$category]);

    return view('store.boosting-list', compact('categories'), [
      'pageTitle' => 'Dịch vụ cày thuê',
    ]);
  }
}