<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\ListItem;

class AccountController extends Controller
{
  public function index($slug)
  {
    $group = \App\Models\Group::where('status', true)->where('slug', $slug)->firstOrFail();

    $meta_seo = $group->meta_seo;

    return view('store.account', compact('group', 'meta_seo'), [
      'pageTitle' => 'Xem sản phẩm ' . $group->name,
    ]);
  }

  public function list()
  {
    $groups = \App\Models\Group::where('status', true)->whereHas('categories', function($q) {
        $q->where('name', 'NOT LIKE', '%Cày Thuê%')->where('name', 'NOT LIKE', '%Vật Phẩm%');
    })->orderBy('priority', 'desc')->get();

    $category = new \App\Models\Category([
        'name' => 'Tài khoản', 
        'slug' => 'tai-khoan',
        'image' => null // Optional
    ]);
    
    $category->setRelation('accountGroups', $groups);
    $categories = collect([$category]);

    return view('store.account-list', compact('categories'), [
      'pageTitle' => 'Danh sách tài khoản',
    ]);
  }

  public function show($code)
  {
    $item = ListItem::where('code', $code)->firstOrFail();

    if ($item === null) {
      return redirect(route('home'))->with('error', 'Không tìm thấy sản phẩm này!');
    }

    if ($item->is_sold === true && $item->buyer_name !== auth()->user()?->username) {
      // return redirect()->back()->with('error', 'Sản phẩm này đã được bán!');
      return abort(403);
    }

    return view('store.account-show', compact('item'), [
      'pageTitle' => 'Xem sản phẩm ' . $item->name,
    ]);
  }
}