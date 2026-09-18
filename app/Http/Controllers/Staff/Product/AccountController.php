<?php

namespace App\Http\Controllers\Staff\Product;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\ListItem;
use App\Models\ListItemArchive;
use App\Models\User;
use Helper;
use Illuminate\Http\Request;

class AccountController extends Controller
{
  //
  public function index(Request $request)
  {
    $user = User::find(auth()->user()->id);

    // back to home if user is not a booster
    if (!is_array($user->colla_type) || !in_array('account', $user->colla_type)) {
      return redirect()->route('staff.dashboard');
    }

    $groups = Group::orderBy('id', 'desc')->whereIn('id', $user->staff_group_ids)->get();

    return view('staff.products.accounts.groups', compact('groups'));
  }

  public function items(Request $request, $id)
  {
    $user = User::find(auth()->user()->id);

    // back to home if user is not a booster
    if (!is_array($user->colla_type) || !in_array('account', $user->colla_type)) {
      return redirect()->route('staff.dashboard');
    }

    if (!in_array($id, $user->staff_group_ids)) {
      return redirect()->route('staff.products.accounts.groups');
    }

    $group = Group::findOrFail($id);

    $items = $group->items()->orderBy('id', 'desc')->paginate(10);

    return view('staff.products.accounts.items', compact('group', 'items', 'user'));
  }

  public function store(Request $request)
  {
    $payload = $request->validate([
      'id'            => 'required|exists:groups,id',
      'type'          => 'nullable|string|in:account,account_group',
      'name'          => 'nullable|string|max:255',
      'code'          => 'nullable|integer|unique:item_data',
      'cost'          => 'required|numeric|min:0',
      'price'         => 'required|numeric|min:0',
      'status'        => 'required|boolean',
      'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1004800',
      'discount'      => 'required|integer|min:0|max:100',
      'list_skin'     => 'nullable|string',
      'list_item'     => 'nullable|string',
      'list_champ'    => 'nullable|string',
      'list_image'    => 'nullable|string',

      // dot kich
      'cf_the_loai'   => 'nullable|string',
      'cf_vip_ingame' => 'nullable|integer',
      'cf_vip_amount' => 'nullable|integer',

      'highlights'    => 'nullable|string',
      'description'   => 'nullable|string',
    ]);
    $user    = User::find(auth()->user()->id);
    $type    = $payload['type'] ?? 'account';
    $group   = Group::findOrFail($payload['id']);

    if (!in_array($group->id, $user->staff_group_ids)) {
      return redirect()->route('staff.products.accounts.groups');
    }

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public', 'items/' . $group->id);
    }

    $payload['list_image'] = explode("\n", $payload['list_image']);
    $payload['list_image'] = array_map(function ($item) {
      return str_replace("\r", '', $item);
    }, $payload['list_image']);
    $payload['list_image'] = array_filter($payload['list_image'], function ($item) {
      return !empty(trim($item));
    });

    $listItem = explode(PHP_EOL, $payload['list_item']);
    $listItem = array_map(function ($item) {
      return str_replace("\r", '', $item);
    }, $listItem);
    $listItem = array_filter($listItem, function ($item) {
      return !empty(trim($item));
    });

    if (count($listItem) === 0) {
      return redirect()->back()->with('error', 'Vui lòng nhập danh sách tài khoản');
    }

    $highlights = explode(PHP_EOL, $payload['highlights']);
    $highlights = array_map(function ($item) {
      return str_replace("\r", '', $item);
    }, $highlights);
    $highlights = array_filter($highlights, function ($item) {
      return !empty(trim($item));
    });
    $highlights = array_map(function ($item) {
      $item = explode(':', $item);
      if (count($item) === 2) {
        return [
          'name'  => trim($item[0]),
          'value' => trim($item[1]),
        ];
      }

      return trim($item[0]);
    }, $highlights);

    $autoCode = true;
    if (count($listItem) === 1 && !empty($payload['code'])) {
      $autoCode = false;
    }

    $listChamp = explode('|', $request->input('list_champ', ''));
    $listChamp = array_map(function ($item) {
      $item = explode('-', $item);
      if (count($item) === 2) {
        return [
          'id'   => trim($item[0]),
          'name' => trim($item[1]),
        ];
      }

      return $item;
    }, $listChamp);

    $listChamp = array_filter($listChamp, function ($item) {
      return !empty($item['id']);
    });

    $listSkin = explode('|', $request->input('list_skin', ''));
    $listSkin = array_map(function ($item) {
      $item = explode('-', $item);
      if (count($item) === 2) {
        return [
          'id'   => trim($item[0]),
          'name' => trim($item[1]),
        ];
      }

      return $item;
    }, $listSkin);
    $listSkin = array_filter($listSkin, function ($item) {
      return !empty($item['id']);
    });

    $listSkin = array_map(function ($item) {
      $item['id'] = str_replace('championsskin_', '', $item['id']);

      return $item;
    }, $listSkin);

    $rawSkins = implode(',', array_map(function ($item) {
      return $item['name'];
    }, $listSkin));

    $created = [];

    if ($type === 'account') {
      foreach ($listItem as $item) {
        $data      = parseItem($item);
        $code      = $autoCode ? ListItem::generateCode() : $payload['code'];
        $created[] = ListItem::create([
          'name'        => $payload['name'] ?? $code,
          'code'        => $code,
          'cost'        => $payload['cost'],
          'price'       => $payload['price'],
          'discount'    => $payload['discount'],
          'status'      => $payload['status'],
          'image'       => $payload['image'] ?? null,
          'username'    => $data['username'],
          'password'    => $data['password'],
          'extra_data'  => $data['extra_data'],
          'list_skin'   => $listSkin,
          'raw_skins'   => $rawSkins,
          'list_champ'  => $listChamp,
          'highlights'  => $highlights,
          'description' => Helper::htmlPurifier($payload['description'] ?? ''),
          'list_image'  => $payload['list_image'] ?? [],
          'priority'    => 0,
          'group_id'    => $group->id,
          'buyer_name'  => null,
          'buyer_code'  => null,

          'staff_name'  => $user->username,
        ]);
      }
    } else {
      $code = $autoCode ? ListItem::generateCode() : $payload['code'];
      $item = ListItem::create([
        'name'        => $payload['name'] ?? $code,
        'code'        => $code,
        'type'        => 'account_group',
        'cost'        => $payload['cost'],
        'price'       => $payload['price'],
        'discount'    => $payload['discount'],
        'status'      => $payload['status'],
        'image'       => $payload['image'] ?? null,
        'username'    => '-',
        'password'    => '-',
        'extra_data'  => '-',
        'list_skin'   => $listSkin,
        'raw_skins'   => $rawSkins,
        'list_champ'  => $listChamp,
        'highlights'  => $highlights,
        'description' => Helper::htmlPurifier($payload['description'] ?? ''),
        'list_image'  => $payload['list_image'] ?? [],
        'priority'    => 0,
        'group_id'    => $group->id,
        'buyer_name'  => null,
        'buyer_code'  => null,
        'staff_name'  => $user->username,
      ]);

      if ($item) {
        foreach ($listItem as $account) {
          $data = parseItem($account);

          $created[] = ListItemArchive::create([
            'code'       => $item->id,
            'username'   => $data['username'],
            'password'   => $data['password'],
            'extra_data' => $data['extra_data'],
          ]);

        }
      }
    }

    Helper::addHistory('Thêm ' . count($created) . ' sản phẩm cho nhóm ' . $group->name);

    return redirect()->back()->with('success', 'Thêm ' . count($created) . ' tài khoản vào nhóm ' . $group->name . ' thành công');
  }

  public function show(Request $request, $id)
  {
    $user = User::find(auth()->user()->id);

    // back to home if user is not a booster
    if (!is_array($user->colla_type) || !in_array('account', $user->colla_type)) {
      return redirect()->route('staff.dashboard');
    }

    $item = ListItem::findOrFail($id);

    if (!in_array($item->group_id, $user->staff_group_ids) || $item->staff_name !== $user->username) {
      return redirect()->route('staff.products.accounts.groups');
    }

    return view('staff.products.accounts.show', compact('item'));
  }

  public function update(Request $request)
  {
    $payload = $request->validate([
      'id'            => 'required|exists:list_items,id',
      'name'          => 'nullable|string|max:255',
      'code'          => 'nullable|integer|unique:list_items,code,' . $request->id . ',id',
      'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1004800',
      'cost'          => 'nullable|numeric|min:0',
      'price'         => 'required|numeric|min:0',
      'status'        => 'required|boolean',
      'discount'      => 'required|integer|min:0|max:100',
      'username'      => 'required|string',
      'password'      => 'required|string',
      'list_skin'     => 'nullable|string',
      'list_champ'    => 'nullable|string',

      // dot kich
      'cf_the_loai'   => 'nullable|string',
      'cf_vip_ingame' => 'nullable|integer',
      'cf_vip_amount' => 'nullable|integer',

      'extra_data'    => 'nullable|string',
      'list_image'    => 'nullable|string',
      'highlights'    => 'nullable|string',
      'description'   => 'nullable|string',
    ]);

    $user = User::find(auth()->user()->id);
    $item = ListItem::where('staff_name', $user->username)->findOrFail($payload['id']);

    if ($request->hasFile('image')) {
      $payload['image'] = Helper::uploadFile($request->file('image'), 'public', 'items/' . $item->group_id);
    }

    $payload['list_image'] = explode("\n", $payload['list_image']);
    $payload['list_image'] = array_map(function ($item) {
      return str_replace("\r", '', $item);
    }, $payload['list_image']);
    $payload['list_image'] = array_filter($payload['list_image'], function ($item) {
      return !empty(trim($item));
    });

    $highlights = explode(PHP_EOL, $payload['highlights']);
    $highlights = array_map(function ($item) {
      return str_replace("\r", '', $item);
    }, $highlights);
    $highlights = array_filter($highlights, function ($item) {
      return !empty(trim($item));
    });
    $highlights = array_map(function ($item) {
      $item = explode(':', $item);
      if (count($item) === 2) {
        return [
          'name'  => trim($item[0]),
          'value' => trim($item[1]),
        ];
      }

      return trim($item[0]);
    }, $highlights);

    $listChamp = explode('|', $request->input('list_champ', ''));
    $listChamp = array_map(function ($item) {
      $item = explode('-', $item);
      if (count($item) === 2) {
        return [
          'id'   => trim($item[0]),
          'name' => trim($item[1]),
        ];
      }

      return $item;
    }, $listChamp);

    $listChamp = array_filter($listChamp, function ($item) {
      return !empty($item['id']);
    });

    $listSkin = explode('|', $request->input('list_skin', ''));
    $listSkin = array_map(function ($item) {
      $item = explode('-', $item);
      if (count($item) === 2) {
        return [
          'id'   => trim($item[0]),
          'name' => trim($item[1]),
        ];
      }

      return $item;
    }, $listSkin);
    $listSkin = array_filter($listSkin, function ($item) {
      return !empty($item['id']);
    });

    $listSkin = array_map(function ($item) {
      $item['id'] = str_replace('championsskin_', '', $item['id']);

      return $item;
    }, $listSkin);

    $payload['raw_skins'] = implode(',', array_map(function ($item) {
      return $item['name'];
    }, $listSkin));

    $payload['list_skin']   = $listSkin;
    $payload['list_champ']  = $listChamp;
    $payload['highlights']  = $highlights;
    $payload['description'] = Helper::htmlPurifier($payload['description'] ?? '');


    if ($payload['status']) {
      $payload['buyer_name'] = null;
      $payload['buyer_code'] = null;
    }

    $item->update($payload);

    Helper::addHistory('Cập nhật sản phẩm #' . $item->code);

    return redirect()->back()->with('success', 'Cập nhật sản phẩm #' . $item->code . ' thành công');
  }

  public function delete(Request $request)
  {
    $payload = $request->validate([
      'id' => 'required|exists:list_items,id',
    ]);
    $user    = User::find(auth()->user()->id);
    $item    = ListItem::where('staff_name', $user->username)->findOrFail($payload['id']);

    $item->delete();

    // Helper::deleteFile($item->image);
    // foreach ($item->list_image as $image) {
    //   Helper::deleteFile($image);
    // }

    Helper::addHistory('Xóa sản phẩm #' . $item->code);

    return response()->json([
      'status'  => 200,
      'message' => 'Xóa sản phẩm #' . $item->code . ' thành công',
    ]);
  }

  public function deleteList(Request $request)
  {
    $payload = $request->validate([
      'ids' => 'required|array',
    ]);
    $user    = User::find(auth()->user()->id);
    $items   = ListItem::whereIn('id', $payload['ids'])->where('staff_name', $user->username)->get();

    // foreach ($items as $item) {
    //   $item->delete();

    //   Helper::deleteFile($item->image);
    //   foreach ($item->list_image as $image) {
    //     Helper::deleteFile($image);
    //   }
    // }

    Helper::addHistory('Xóa ' . count($items) . ' sản phẩm ở accounts v1');

    return response()->json([
      'status'  => 200,
      'message' => 'Xóa ' . count($items) . ' sản phẩm thành công',
    ]);
  }
}
