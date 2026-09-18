<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\ListItemV2;

class AccountV2Controller extends Controller
{
  public function index($slug)
  {
    $group = \App\Models\GroupV2::where('status', true)->where('slug', $slug)->firstOrFail();

    $meta_seo = $group->meta_seo;

    return view('store.account-v2', compact('group', 'meta_seo'), [
      'pageTitle' => 'Xem sản phẩm ' . $group->name,
    ]);
  }

  public function list()
  {
    $groups = \App\Models\GroupV2::where('status', true)->orderBy('priority', 'desc')->get();

    $category = new \App\Models\Category([
        'name' => 'Tài khoản v2',
        'slug' => 'tai-khoan-v2',
        'image' => null
    ]);

    $category->setRelation('accountV2Groups', $groups);
    $categories = collect([$category]);

    return view('store.account-v2-list', compact('categories'), [
      'pageTitle' => 'Danh sách tài khoản V2',
    ]);
  }

  public function show($code)
  {
    $item = ListItemV2::where('code', $code)->firstOrFail();

    if ($item === null) {
      return redirect(route('home'))->with('error', 'Không tìm thấy sản phẩm này!');
    }

    return view('store.account-show-v2', compact('item'), [
      'pageTitle' => 'Xem sản phẩm ' . $item->name,
    ]);
  }
}
