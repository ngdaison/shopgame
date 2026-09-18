<?php

namespace App\Http\Controllers\Admin\Service;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\InventoryVar;
use App\Models\ServiceCategory;
use App\Models\Category;
use Helper;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $items         = ServiceCategory::with('groups')->orderBy('priority', 'desc')->get();
        $shopGroups    = $this->getShopGroups();
        $categories    = $this->getCategories();
        $inventoryVars = InventoryVar::where('is_active', true)->orderBy('id', 'desc')->get();

        return view('admin.service.index', compact('items', 'shopGroups', 'categories', 'inventoryVars'));
    }

    public function edit($id)
    {
        $item          = ServiceCategory::with('groups')->findOrFail($id);
        $shopGroups    = $this->getShopGroups();
        $categories    = $this->getCategories();
        $inventoryVars = InventoryVar::where('is_active', true)->orderBy('id', 'desc')->get();

        return view('admin.service.edit', compact('item', 'shopGroups', 'categories', 'inventoryVars'));
    }

    private function getShopGroups()
    {
        $g1 = \App\Models\Group::where('status', true)->get()->map(function($g) {
            return (object)[ 'id' => $g->id, 'name' => "[Accounts] " . $g->name, 'model' => \App\Models\Group::class ];
        });
        $g2 = \App\Models\GroupV2::where('status', true)->get()->map(function($g) {
            return (object)[ 'id' => $g->id, 'name' => "[AccountV2] " . $g->name, 'model' => \App\Models\GroupV2::class ];
        });
        $g3 = \App\Models\GBGroup::where('status', true)->get()->map(function($g) {
            return (object)[ 'id' => $g->id, 'name' => "[Boosting] " . $g->name, 'model' => \App\Models\GBGroup::class ];
        });
        $g4 = \App\Models\ItemGroup::where('status', true)->get()->map(function($g) {
            return (object)[ 'id' => $g->id, 'name' => "[Item] " . $g->name, 'model' => \App\Models\ItemGroup::class ];
        });
        $g5 = \App\Models\ServiceCategory::where('status', true)->where('product_type', 'spin')->get()->map(function($g) {
            return (object)[ 'id' => $g->id, 'name' => "[Game] " . $g->name, 'model' => \App\Models\ServiceCategory::class ];
        });

        return $g1->concat($g2)->concat($g3)->concat($g4)->concat($g5);
    }
    
    private function getCategories()
    {
        return \App\Models\Category::where('status', 'active')->get()->map(function($g) {
            return (object)[ 'id' => $g->id, 'name' => "[Danh mục] " . $g->name, 'model' => \App\Models\Category::class ];
        });
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'sub_name'     => 'nullable|string|max:255',
            'image'        => 'nullable|image|max:10000',
            'status'       => 'required|boolean',
            'priority'     => 'nullable|integer',
            'product_type' => 'required|string|in:spin,category,robux',
            'display_mode' => 'nullable|string|in:list,grid',
            'robux_type'   => 'nullable|string|in:120h,genuine',
            // Spin fields
            'cover'        => 'nullable|image|max:10000',
            'invar_id'     => 'nullable|integer|exists:inventory_vars,id',
            'descr'        => 'nullable|string',
            'price'        => 'nullable', // Allow string for Rate Config logic
            'warranty_hours' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = Helper::uploadFile($request->file('image'), 'public');
        }

        if ($request->hasFile('cover')) {
            $data['cover'] = Helper::uploadFile($request->file('cover'), 'public');
        }

        if ($data['product_type'] === 'spin') {
            $data['prizes'] = [];
        }

        if ($data['product_type'] === 'robux' && $request->has('package_config')) {
            $prizes = [];
            $robuxType = $data['robux_type'] ?? '120h';

            if ($robuxType === 'genuine') {
                $packageItems = explode(',', $request->input('package_config'));
                foreach ($packageItems as $item) {
                    $parts = explode('|', trim($item));
                    if (count($parts) === 2) {
                        $robuxAmount = (int)trim($parts[0]);
                        $priceAmount = (int)trim($parts[1]);
                        if ($robuxAmount <= 0) continue;

                        $prizes[] = [
                            'value' => $robuxAmount,
                            'percent' => $priceAmount, 
                            'min' => $robuxAmount,
                            'max' => $robuxAmount,
                            'random' => false
                        ];
                    }
                }
                $data['price'] = 0;
                $data['play_times'] = 0;
            } else {
                $packageList = explode(',', $request->input('package_config'));
                $priceInput = $request->input('price'); 
                $rateConfig = [];
                $isTiered = false;
                
                if (strpos((string)$priceInput, '|') !== false) {
                    $isTiered = true;
                    $tiers = explode(',', $priceInput);
                    foreach ($tiers as $tier) {
                        $parts = explode('|', trim($tier));
                        if (count($parts) == 2) {
                            $rateConfig[] = [
                                'limit' => (int)trim($parts[0]),
                                'rate'  => (int)trim($parts[1])
                            ];
                        }
                    }
                    usort($rateConfig, function($a, $b) {
                        return $a['limit'] <=> $b['limit'];
                    });
                    $data['rate_config'] = $priceInput;
                    $data['price'] = $rateConfig[0]['rate'] ?? 0;
                } else {
                    $isTiered = false;
                    $data['price'] = (int)$priceInput; 
                    $data['rate_config'] = null;
                }

                foreach ($packageList as $pkg) {
                    $robuxAmount = (int)trim($pkg);
                    if ($robuxAmount <= 0) continue;
                    
                    $appliedRate = $data['price']; 
                    if ($isTiered && !empty($rateConfig)) {
                         $found = false;
                         foreach ($rateConfig as $tier) {
                             if ($robuxAmount <= $tier['limit']) {
                                 $appliedRate = $tier['rate'];
                                 $found = true;
                                 break;
                             }
                         }
                         if (!$found) {
                             $lastTier = end($rateConfig);
                             $appliedRate = $lastTier['rate'];
                         }
                    }
                    
                    $prizes[] = [
                        'value' => $robuxAmount,   
                        'percent' => $appliedRate, 
                        'min' => $robuxAmount,
                        'max' => $robuxAmount,
                        'random' => false
                    ];
                }
            }
            $data['prizes'] = $prizes;
        }

        $service = ServiceCategory::create($data);

    if (($data['product_type'] === 'category' || $data['product_type'] === 'spin' || $data['product_type'] === 'robux') && $request->has('groups')) {
        foreach ($request->input('groups') as $groupStr) {
                $parts = explode(':', $groupStr);
                if (count($parts) === 2) {
                    \Illuminate\Support\Facades\DB::table('service_category_group')->insert([
                        'service_category_id' => $service->id,
                        'group_id'            => $parts[1],
                        'group_type'          => $parts[0],
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);
                }
            }
        }

        Helper::addHistory("Tạo dịch vụ mới ({$service->name})");

        return response()->json([
            'status' => true,
            'message' => 'Thêm mới thành công',
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'id'           => 'required|integer|exists:service_categories,id',
            'name'         => 'required|string|max:255',
            'sub_name'     => 'nullable|string|max:255',
            'image'        => 'nullable|image|max:10000',
            'status'       => 'required|boolean',
            'priority'     => 'nullable|integer',
            'product_type' => 'required|string|in:spin,category,robux',
            'display_mode' => 'nullable|string|in:list,grid',
            'robux_type'   => 'nullable|string|in:120h,genuine',
            // Spin fields
            'cover'        => 'nullable|image|max:10000',
            'invar_id'     => 'nullable|integer|exists:inventory_vars,id',
            'descr'        => 'nullable|string',
            'price'        => 'nullable', // Allow string for Rate Config logic
            'play_times'   => 'nullable|integer', // Reused for Tax Rate in Robux services
            'warranty_hours' => 'nullable|integer',
        ]);

        $service = ServiceCategory::findOrFail($data['id']);

        if ($request->hasFile('image')) {
            $data['image'] = Helper::uploadFile($request->file('image'), 'public');
        }

        if ($request->hasFile('cover')) {
            $data['cover'] = Helper::uploadFile($request->file('cover'), 'public');
        }

        if ($data['product_type'] === 'spin' || $data['product_type'] === 'robux') {
            $data['descr'] = Helper::htmlPurifier($data['descr'] ?? '');
            
            // New Logic: Parse package_config string for Robux (Comma separated)
            if ($request->has('package_config') && $data['product_type'] === 'robux') {
                $prizes = [];
                $robuxType = $data['robux_type'] ?? '120h';

                if ($robuxType === 'genuine') {
                    // Logic for Genuine Robux: package_config format is robux|price (e.g., 100|10000, 200|20000)
                    $packageItems = explode(',', $request->input('package_config'));
                    foreach ($packageItems as $item) {
                        $parts = explode('|', trim($item));
                        if (count($parts) === 2) {
                            $robuxAmount = (int)trim($parts[0]);
                            $priceAmount = (int)trim($parts[1]);
                            if ($robuxAmount <= 0) continue;

                            $prizes[] = [
                                'value' => $robuxAmount,
                                'percent' => $priceAmount, // Store actual price in 'percent' field for genuine
                                'min' => $robuxAmount,
                                'max' => $robuxAmount,
                                'random' => false
                            ];
                        }
                    }
                    // For genuine, we don't use the global rate/tax logic
                    $data['price'] = 0;
                    $data['play_times'] = 0;
                } else {
                    // Standard Logic for Robux 120h: package_config is just list of Robux amounts
                    // 1. Parse Package List (100, 200, 500)
                    $packageList = explode(',', $request->input('package_config'));
                    
                    // 2. Parse Rate Config
                    $priceInput = $request->input('price'); 
                    $rateConfig = [];
                    $isTiered = false;
                    
                    if (strpos((string)$priceInput, '|') !== false) {
                        $isTiered = true;
                        $tiers = explode(',', $priceInput);
                        foreach ($tiers as $tier) {
                            $parts = explode('|', trim($tier));
                            if (count($parts) == 2) {
                                $rateConfig[] = [
                                    'limit' => (int)trim($parts[0]),
                                    'rate'  => (int)trim($parts[1])
                                ];
                            }
                        }
                        usort($rateConfig, function($a, $b) {
                            return $a['limit'] <=> $b['limit'];
                        });
                        $data['rate_config'] = $priceInput;
                        $data['price'] = $rateConfig[0]['rate'] ?? 0;
                    } else {
                        $isTiered = false;
                        $data['price'] = (int)$priceInput; 
                        $data['rate_config'] = null;
                    }

                    foreach ($packageList as $pkg) {
                        $robuxAmount = (int)trim($pkg);
                        if ($robuxAmount <= 0) continue;
                        
                        $appliedRate = $data['price']; 
                        if ($isTiered && !empty($rateConfig)) {
                             $found = false;
                             foreach ($rateConfig as $tier) {
                                 if ($robuxAmount <= $tier['limit']) {
                                     $appliedRate = $tier['rate'];
                                     $found = true;
                                     break;
                                 }
                             }
                             if (!$found) {
                                 $lastTier = end($rateConfig);
                                 $appliedRate = $lastTier['rate'];
                             }
                        }
                        
                        $prizes[] = [
                            'value' => $robuxAmount,   
                            'percent' => $appliedRate, 
                            'min' => $robuxAmount,
                            'max' => $robuxAmount,
                            'random' => false
                        ];
                    }
                }
                $data['prizes'] = $prizes;
            } elseif ($request->has('prizes')) {
                // Fallback / standard spin logic
                $prizes = [];
                foreach ($request->input('prizes') as $prize) {
                    $prize['value']   = $prize['value'] == '' ? 0 : $prize['value'];
                    $prize['percent'] = $prize['percent'] == '' ? 0 : $prize['percent'];

                    if (!is_numeric($prize['value'])) {
                        $range = explode('-', $prize['value']);
                        if (count($range) == 2) {
                            $prize['min']    = $range[0];
                            $prize['max']    = $range[1];
                            $prize['random'] = true;
                        }
                    } else {
                        $prize['min']    = $prize['value'];
                        $prize['max']    = $prize['value'];
                        $prize['random'] = false;
                    }
                    $prizes[] = $prize;
                }
                $data['prizes'] = $prizes;
            }
        }

        // Use explicit fill and save, removing ID to prevent collisions
        unset($data['id']);
        $service->fill($data);
        $service->save();

        if (($data['product_type'] === 'category' || $data['product_type'] === 'spin' || $data['product_type'] === 'robux') && $request->has('groups')) {
            \Illuminate\Support\Facades\DB::table('service_category_group')
                ->where('service_category_id', $service->id)
                ->delete();

            foreach ($request->input('groups') as $groupStr) {
                $parts = explode(':', $groupStr);
                if (count($parts) === 2) {
                    \Illuminate\Support\Facades\DB::table('service_category_group')->insert([
                        'service_category_id' => $service->id,
                        'group_id'            => $parts[1],
                        'group_type'          => $parts[0],
                        'created_at'          => now(),
                        'updated_at'          => now(),
                    ]);
                }
            }
        }

        Helper::addHistory("Cập nhật dịch vụ ({$service->name})");

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật thành công',
        ]);
    }

    public function updatePrize(Request $request)
    {
        $payload = $request->validate([
            'id'     => 'required|integer|exists:service_categories,id',
            'prizes' => 'required|array',
        ]);

        $service = ServiceCategory::findOrFail($payload['id']);

        $prizes = [];
        foreach ($payload['prizes'] as $prize) {
            $prize['value']   = $prize['value'] == '' ? 0 : $prize['value'];
            $prize['percent'] = $prize['percent'] == '' ? 0 : $prize['percent'];

            if (!is_numeric($prize['value'])) {
                $range = explode('-', $prize['value']);
                if (count($range) == 2) {
                    $prize['min']    = $range[0];
                    $prize['max']    = $range[1];
                    $prize['random'] = true;
                }
            } else {
                $prize['min']    = $prize['value'];
                $prize['max']    = $prize['value'];
                $prize['random'] = false;
            }

            $prizes[] = $prize;
        }

        $service->update(['prizes' => $prizes]);

        Helper::addHistory("Cập nhật giải thưởng cho ({$service->name})");

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật giải thưởng thành công',
        ]);
    }

    public function updatePriority(Request $request)
    {
        $payload = $request->validate([
            'id'       => 'required|integer|exists:service_categories,id',
            'priority' => 'required|integer',
        ]);

        $service = ServiceCategory::findOrFail($payload['id']);
        $service->update(['priority' => $payload['priority']]);

        return response()->json([
            'status'  => true,
            'message' => 'Cập nhật thứ tự thành công!',
        ]);
    }

    public function delete(Request $request)
    {
        $id = $request->input('id');
        $service = ServiceCategory::findOrFail($id);
        $service->delete();

        Helper::addHistory("Xoá dịch vụ ({$service->name})");

        return response()->json([
            'status'  => true,
            'message' => 'Xoá thành công',
        ]);
    }
}
