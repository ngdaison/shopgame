<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Helper;
use App\Models\ResourceV2O;
use App\Models\User;

class DataController extends Controller
{
  public function accountsV1(Request $request)
  {
    $payload = $request->validate([

      'sold' => 'nullable|integer',
      'group' => 'nullable|integer',
      'buyer_name' => 'nullable|string|max:255',
      'username' => 'nullable|string|max:255',
      'start_date' => 'nullable|date',
      'end_date' => 'nullable|date',
      'domain' => 'nullable|string|max:255',

      'page' => 'nullable|integer|min:1',
      'limit' => 'nullable|integer|min:1',
      'search' => 'nullable|string|max:255',
      'sort_by' => 'nullable|string|max:255',
      'sort_type' => 'nullable|string|in:asc,desc',
    ]);
    $page = $payload['page'] ?? 1;
    $limit = $payload['limit'] ?? 10;
    $search = $payload['search'] ?? null;
    $offset = ($page - 1) * $limit;
    $sort_by = $payload['sort_by'] ?? 'id';

    if ($sort_by === '0' || empty($sort_by)) {
      $sort_by = 'id';
    }

    $sort_type = $payload['sort_type'] ?? 'asc';

    $query = \App\Models\ListItem::query();

    // Filter by Admin Hide
    $query->whereNull('admin_deleted_at');

    // Filter by Role Limit Date
    $limitDate = auth()->user()->getHistoryLimitDate();
    if ($limitDate) {
        $query->where('buyer_date', '>=', $limitDate);
    }

    if ($search) {
      // $query->where('code', 'like', '%' . $search . '%');
      $query->where(function ($sub) use ($search) {
        $sub->where('code', 'like', '%' . $search . '%')
          ->orWhere('name', 'like', '%' . $search . '%')
          ->orWhere('username', 'like', '%' . $search . '%');
      });
    }

    if (isset($payload['sold'])) {
      if ($payload['sold'] === '0') {
        $query->where('buyer_name', null);
      }
      else {
        $query->where('buyer_name', '!=', null);
      }

    }

    if ($payload['username'] ?? null) {
      $query->where('username', $payload['username']);
    }

    if ($payload['group'] ?? null) {
      $query->where('group_id', $payload['group']);
    }

    if ($payload['buyer_name'] ?? null) {
      $query->where('buyer_name', $payload['buyer_name']);
    }

    if ($payload['start_date'] ?? null) {
      $query->where('buyer_date', '>=', $payload['start_date']);
    }

    if ($payload['end_date'] ?? null) {
      $query->where('buyer_date', '<=', $payload['end_date']);
    }

    if ($payload['domain'] ?? null) {
      $query->where('domain', $payload['domain']);
    }

    $total = $query->count();

    $data = $query->skip($offset)
      ->take($limit)
      ->orderBy($sort_by, $sort_type)
      ->with(['group'])
      ->get();

    // Manually load user data for domain fallback (username is hidden in model)
    $usernames = $data->pluck('username')->unique()->filter();
    if ($usernames->count() > 0) {
      $users = User::whereIn('username', $usernames)
        ->select('id', 'username', 'domain')
        ->get()
        ->keyBy('username');
      $data->each(function ($item) use ($users) {
        if (isset($users[$item->username])) {
          $item->setRelation('user', $users[$item->username]);
        }
      });
    }

    // Transform items: fix image and make hidden fields visible
    $data = $data->transform(function ($item) {
      $rawImage = $item->getRawOriginal('image');
      $item->image = \Helper::getValidImage($rawImage ?? $item->image);
      return $item;
    })->makeVisible([
      'username',
      'buyer_name',
      'buyer_code',
      'buyer_paym',
      'buyer_date',
      'staff_name',
      'staff_status',
      'staff_payment',
      'domain',
    ]);

    return response()->json([
      'data' => [
        'meta' => [
          'page' => (int)$page,
          'total' => (int)$total,
          'limit' => (int)$limit,
        ],
        'data' => $data,
      ],
      'status' => 200,
      'message' => 'Get data success',
    ]);
  }

  public function accountsV2(Request $request)
  {
    try {
      $payload = $request->validate([
        'type' => 'nullable|string|in:products,resources',
        'sold' => 'nullable|integer',
        'group' => 'nullable|integer',
        'buyer_name' => 'nullable|string|max:255',
        'username' => 'nullable|string|max:255',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date',
        'domain' => 'nullable|string|max:255',

        'page' => 'nullable|integer|min:1',
        'limit' => 'nullable|integer|min:1',
        'search' => 'nullable|string|max:255',
        'sort_by' => 'nullable|string|max:255',
        'sort_type' => 'nullable|string|in:asc,desc',
      ]);
      $type = $payload['type'] ?? 'resources';
      $page = $payload['page'] ?? 1;
      $limit = $payload['limit'] ?? 10;
      $search = $payload['search'] ?? null;
      $offset = ($page - 1) * $limit;
      $sort_by = $payload['sort_by'] ?? 'id';

      if ($sort_by === '0' || empty($sort_by)) {
        $sort_by = 'id';
      }

      $sort_type = $payload['sort_type'] ?? 'asc';

      if ($type === 'products') {
        // Sanitize sort_by to avoid SQL errors on accessors
        $allowedSortColumns = ['id', 'priority', 'name', 'code', 'price', 'cost', 'discount', 'status', 'created_at'];
        if (!in_array($sort_by, $allowedSortColumns)) {
          $sort_by = 'id';
        }

        $query = \App\Models\ListItemV2::query();

        if ($search) {
          $query->where(function ($sub) use ($search) {
            $sub->where('code', 'like', '%' . $search . '%')
              ->orWhere('name', 'like', '%' . $search . '%');
          });
        }

        if ($payload['group'] ?? null) {
          $query->where('group_id', $payload['group']);
        }

        $total = $query->count();
        $data = $query->skip($offset)
          ->take($limit)
          ->orderBy($sort_by, $sort_type)
          ->get();

        // Pre-load revenue and other summary data to avoid N+1
        $codes = $data->pluck('code')->filter()->toArray();
        $revenues = \App\Models\ResourceV2O::whereIn('code', $codes)
          ->groupBy('code')
          ->select('code', \DB::raw('SUM(buyer_paym) as total_revenue'))
          ->get()
          ->pluck('total_revenue', 'code');

        $stocks = \App\Models\ResourceV2S::whereIn('code', $codes)
          ->whereNull('buyer_name')
          ->groupBy('code')
          ->select('code', \DB::raw('COUNT(*) as total_stock'))
          ->get()
          ->pluck('total_stock', 'code');

        $data->transform(function ($item) use ($revenues, $stocks) {
          // Manually build array to avoid triggering any model appends/accessors
          $rawImage = $item->getRawOriginal('image');

          return [
          'id' => $item->id,
          'name' => $item->name,
          'code' => $item->code,
          'price' => $item->price,
          'cost' => $item->cost,
          'discount' => $item->discount,
          'priority' => $item->priority,
          'status' => $item->status,
          'stock' => $stocks[$item->code] ?? 0,
          'revenue' => $revenues[$item->code] ?? 0,
          'image' => \Helper::getValidImage($rawImage),
          'created_at' => $item->created_at ? $item->created_at->toIso8601String() : null,
          ];
        });

        return response()->json([
          'data' => [
            'meta' => [
              'page' => (int)$page,
              'total' => (int)$total,
              'limit' => (int)$limit,
            ],
            'data' => $data,
          ],
          'status' => 200,
          'message' => 'Get data success',
        ]);
      }

      // Switch model based on sold parameter
      $isSold = isset($payload['sold']) && (string)$payload['sold'] === '1';
      $isUnsold = isset($payload['sold']) && (string)$payload['sold'] === '0';

      if ($isUnsold) {
        $query = \App\Models\ResourceV2S::query();
      }
      else {
        $query = ResourceV2O::query(); // Default to Orders for 'sold' and 'all'
        
        // Filter by Admin Hide
        $query->whereNull('admin_deleted_at');

        // Filter by Role Limit Date
        $limitDate = auth()->user()->getHistoryLimitDate();
        if ($limitDate) {
            $query->where('buyer_date', '>=', $limitDate);
        }
      }

      if ($search) {
        $query->where(function ($sub) use ($search) {
          $sub->where('code', 'like', '%' . $search . '%')
            ->orWhere('username', 'like', '%' . $search . '%')
            ->orWhere('buyer_name', 'like', '%' . $search . '%');
        });
      }

      if (isset($payload['sold'])) {
        if ((string)$payload['sold'] === '0') {
          $query->whereNull('buyer_name');
        }
        else if ((string)$payload['sold'] === '1') {
          $query->whereNotNull('buyer_name');
        }
      }

      if ($payload['username'] ?? null) {
        $query->where('username', 'like', '%' . $payload['username'] . '%');
      }

      if ($payload['group'] ?? null) {
        $query->whereHas('parent', function ($q) use ($payload) {
          $q->where('group_id', $payload['group']);
        });
      }

      if ($payload['buyer_name'] ?? null) {
        $query->where('buyer_name', 'like', '%' . $payload['buyer_name'] . '%');
      }

      if ($payload['start_date'] ?? null) {
        $query->where('buyer_date', '>=', $payload['start_date']);
      }

      if ($payload['end_date'] ?? null) {
        $query->where('buyer_date', '<=', $payload['end_date']);
      }

      if ($payload['domain'] ?? null) {
        $query->where('domain', 'like', '%' . $payload['domain'] . '%');
      }

      if ($request->input('unique_orders') && !$isUnsold) {
        // We only group by unique orders if we are looking at sold items (ResourceV2O)
        $uniqueQuery = ResourceV2O::query()
          ->when($search, function ($q) use ($search) {
          $q->where(function ($sub) use ($search) {
              $sub->where('code', 'like', '%' . $search . '%')
                ->orWhere('username', 'like', '%' . $search . '%')
                ->orWhere('buyer_name', 'like', '%' . $search . '%');
            }
            );
          })
          ->when(isset($payload['sold']), function ($q) use ($payload) {
          if ((string)$payload['sold'] === '0')
            $q->whereNull('buyer_name');
          else if ((string)$payload['sold'] === '1')
            $q->whereNotNull('buyer_name');
        });

        // Apply admin filters to unique query too
        $uniqueQuery->whereNull('admin_deleted_at');
        $limitDate = auth()->user()->getHistoryLimitDate();
        if ($limitDate) {
            $uniqueQuery->where('buyer_date', '>=', $limitDate);
        }

        $total = $uniqueQuery->clone()->distinct('buyer_code')->count('buyer_code');

        // Join with ListItemV2 to get image column
        $query->leftJoin('list_item_v2_s', 'resource_v2_o.code', '=', 'list_item_v2_s.code');

        $query->select(
          'resource_v2_o.buyer_code',
          \DB::raw('MAX(resource_v2_o.id) as id'),
          \DB::raw('SUM(resource_v2_o.buyer_paym) as total_payment'),
          \DB::raw('COUNT(resource_v2_o.id) as quantity'),
          \DB::raw('MAX(resource_v2_o.code) as code'),
          \DB::raw('MAX(resource_v2_o.buyer_name) as buyer_name'),
          \DB::raw('MAX(resource_v2_o.buyer_date) as buyer_date'),
          \DB::raw('MAX(resource_v2_o.order_status) as order_status'),
          \DB::raw('MAX(resource_v2_o.domain) as domain'),
          \DB::raw('MAX(resource_v2_o.username) as username'),
          \DB::raw('MAX(list_item_v2_s.image) as image')
        );
        $query->groupBy('resource_v2_o.buyer_code');
        $query->having('quantity', '>', 0);

        if ($sort_by === 'id') {
          $query->orderBy('buyer_date', $sort_type);
        }
        else {
          $allowedSort = ['buyer_code', 'buyer_name', 'buyer_date', 'total_payment', 'quantity', 'order_status'];
          if (in_array($sort_by, $allowedSort)) {
            $query->orderBy($sort_by, $sort_type);
          }
          else {
            $query->orderBy('buyer_date', $sort_type);
          }
        }
      }
      else {
        $total = $query->count();
        // Fallback sort if id is requested but maybe ambiguous or non-existent (relation sort hack)
        if ($sort_by === 'parent')
          $sort_by = 'code';
        $query->orderBy($sort_by, $sort_type);
      }

      // Handle relationship loading based on whether we're doing a grouped query
      $isGroupedQuery = $request->input('unique_orders') && !$isUnsold;

      if (!$isGroupedQuery) {
        $data = $query->skip($offset)
          ->take($limit)
          ->with('parent.group')
          ->get();

        // Manually load seller user info for domain fallback
        $sellerNames = $data->pluck('username')->unique()->filter();
        if ($sellerNames->count() > 0) {
          $sellers = User::whereIn('username', $sellerNames)
            ->select('id', 'username', 'domain', 'role')
            ->get()
            ->keyBy('username');
          $data->each(function ($item) use ($sellers) {
            if (isset($sellers[$item->username])) {
              $item->setRelation('user', $sellers[$item->username]);
            }
          });
        }

        // Also load buyer info for ResourceV2O (orders) to get role
        $buyerNames = $data->pluck('buyer_name')->unique()->filter();
        if ($buyerNames->count() > 0) {
            $buyers = User::whereIn('username', $buyerNames)
                ->select('username', 'role')
                ->get()
                ->keyBy('username');
            $data->each(function ($item) use ($buyers) {
                if (isset($buyers[$item->buyer_name])) {
                    $item->buyer_role = $buyers[$item->buyer_name]->role;
                }
            });
        }
      }
      else {
        // For grouped queries, get the raw data first without relationships
        $data = $query->skip($offset)
          ->take($limit)
          ->get();

        // Now manually load parent and user relationships for each item
        $codes = $data->pluck('code')->unique();
        $sellerNames = $data->pluck('username')->unique()->filter();

        if ($codes->count() > 0) {
          $parents = \App\Models\ListItemV2::with('group')
            ->whereIn('code', $codes)
            ->get()
            ->keyBy('code');

          // Attach parent relationship to each item
          $data->each(function ($item) use ($parents) {
            if (isset($parents[$item->code])) {
              $item->setRelation('parent', $parents[$item->code]);
            }
          });
        }

        if ($sellerNames->count() > 0) {
          $users = User::whereIn('username', $sellerNames)
            ->select('id', 'username', 'domain')
            ->get()
            ->keyBy('username');

          // Attach user relationship to each item (based on seller username, not buyer)
          $data->each(function ($item) use ($users) {
            if (isset($users[$item->username])) {
              $item->setRelation('user', $users[$item->username]);
            }
          });
        }
      }

      $data->transform(function ($item) {
        if ($item->parent) {
          // Hide expensive appends that cause N+1 queries
          $item->parent->makeHidden(['sold', 'amount', 'revenue', 'profit', 'original_price_str', 'price_str', 'price_discount']);
          $item->parent->makeVisible(['cost']);
        }

        // Safely handle getRawOriginal for image column which might not exist in all models/selects
        $rawImage = null;
        try {
          $rawImage = $item->getRawOriginal('image');
        }
        catch (\Exception $e) {
        }

        $item->image = \Helper::getValidImage($rawImage);
        return $item;
      });

      $data->makeVisible([
        'username',
        'buyer_name',
        'buyer_code',
        'buyer_paym',
        'buyer_date',
        'domain',
      ]);

      return response()->json([
        'data' => [
          'meta' => [
            'page' => (int)$page,
            'total' => (int)$total,
            'limit' => (int)$limit,
          ],
          'data' => $data,
        ],
        'status' => 200,
        'message' => 'Get data success',
      ]);
    }
    catch (\Exception $e) {
      \Log::error('AccountsV2 API Error: ' . $e->getMessage(), [
        'exception' => $e,
        'payload' => $request->all(),
      ]);

      return response()->json([
        'data' => null,
        'status' => 500,
        'message' => 'Error: ' . $e->getMessage(),
        'error' => $e->getMessage(),
        'trace' => env('APP_DEBUG') ? $e->getTraceAsString() : null,
      ], 500);
    }
  }
}
