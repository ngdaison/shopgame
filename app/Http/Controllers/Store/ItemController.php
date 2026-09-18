<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ItemController extends Controller
{
  public function index($slug)
  {
    $group = \App\Models\ItemGroup::where('status', true)->where('slug', $slug)->first();

    if (!$group) {
        $serviceCategory = \App\Models\ServiceCategory::where('status', true)->where('slug', $slug)->firstOrFail();
        
        // Check for specific Robux view requirement
        // Assuming 'robux' product_type warrants the special view
        if ($serviceCategory->product_type === 'robux') {
            // 'play_times' is reused to store the specific Tax Rate for this service
            $tax = is_numeric($serviceCategory->play_times) ? $serviceCategory->play_times : setting('tax_robux', 0.7);

             $globalRate = setting('rate_robux');
             // Robust fallback: Check General Config directly if setting() returns nothing (or default 0) but we expect a potential string
             if (!$globalRate || $globalRate === 0) {
                 $generalConfig = \Helper::getConfig('general');
                 $globalRate = $generalConfig['rate_robux'] ?? 0;
             }

             $servicePrice = $serviceCategory->price;
             // Ensure we don't cast complex strings (like "100,500|50") to float, which would lose data or error.
             if ($servicePrice > 0) {
                 $rate = $servicePrice;
             } else {
                 $rate = $globalRate;
             }
             
             \Log::info('ItemRobux Debug', [
                 'service_id' => $serviceCategory->id,
                 'raw_price' => $serviceCategory->price,
                 'float_price' => (float)$serviceCategory->price,
                 'global_rate' => $globalRate,
                 'final_rate' => $rate
             ]);

             return view('store.item-robux', [
                'group' => $serviceCategory,
                'pageTitle' => 'Mua ' . $serviceCategory->name,
                'robux_rate' => $rate,
                'robux_tax' => $tax,
                'robux_type' => $serviceCategory->robux_type ?? '120h'
             ]);
        }
        
        // Fallback or other service types handling if needed
        abort(404);
    }

    $loginWith    = [];
    $defaultLogin = ["Riot", "Garena", "Steam", "Facebook", "Google", "Roblox", "Other"];
    //

    if (count($group->login_with ?? []) === 0) {
      foreach ($defaultLogin as $value) {
        $loginWith[] = [
          'value' => ucfirst($value),
          'label' => __t('Đăng nhập bằng') . ' ' . ucfirst($value),
        ];
      }
    } else {
      foreach ($group->login_with as $value) {
        $loginWith[] = [
          'value' => ucfirst($value),
          'label' => __t('Đăng nhập bằng') . ' ' . ucfirst($value),
        ];
      }
    }

    $packages = $group->packages()->where('status', true)->get();

    return view('store.item', compact('group', 'loginWith', 'packages'), [
      'pageTitle' => 'Xem sản phẩm ' . $group->name,
    ]);
  }

  public function list()
  {
    $groups = \App\Models\ItemGroup::where('status', true)->orderBy('priority', 'desc')->get();

    $category = new \App\Models\Category([
        'name' => 'Vật phẩm',
        'slug' => 'vat-pham',
        'image' => null
    ]);

    $category->setRelation('itemGroups', $groups);

    // Fetch Robux Services
    $robuxServices = \App\Models\ServiceCategory::where('status', true)
        ->where('product_type', 'robux')
        ->orderBy('priority', 'desc')
        ->get();
    
    $category->setRelation('robuxServices', $robuxServices);

    $categories = collect([$category]);

    return view('store.item-list', compact('categories'), [
      'pageTitle' => 'Danh sách vật phẩm',
    ]);
  }


}
