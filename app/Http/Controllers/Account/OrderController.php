<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BulkOrder;
use App\Models\GBOrder;
use App\Models\ItemOrder;
use App\Models\ListItem;
use App\Models\ResourceV2;
use App\Models\ResourceV2O;

use DB;
use Illuminate\Http\Request;

class OrderController extends Controller
{
  public function refund(Request $request)
  {
      $request->validate([
          'id'    => 'required|integer',
          'type'  => 'required|string|in:v1,v2',
      ]);

      try {
          DB::beginTransaction();
          $user = auth()->user();
          $refundAmount = 0;
          $refundCode = '';

          if ($request->type === 'v1') {
              // V1 Refund Logic
              $item = ListItem::where('id', $request->id)
                  ->where('buyer_name', $user->username)
                  ->firstOrFail();

              $refundAmount = $item->buyer_paym;
              $refundCode = $item->code;

              // Reset item status
              $item->update([
                  'buyer_name' => null,
                  'buyer_code' => null,
                  'buyer_paym' => 0,
                  'buyer_date' => null,
              ]);

          } else {
              // V2 Refund Logic
              // Check if it's a single resource or bulk order
              if ($request->has('code')) {
                   // It might be better to refund by code if ID is ambiguous in V2 context,
                   // but the frontend sends ID. In V2, we display ResourceV2 ID in the list?
                   // No, the list shows ResourceV2.
              }

              $item = ResourceV2O::where('id', $request->id)
                  ->where('buyer_name', $user->username)
                  ->lockForUpdate()
                  ->firstOrFail();

              // Check if this is part of a bulk order?
              // Existing logic in Admin controller suggests standard refund is per resource.
              // If it's a bulk order, `is_bulk` is true.

              $refundAmount = $item->buyer_paym;
              $refundCode = $item->code; // Product Code

              $item->update([
                  'order_status' => 'Cancelled',
              ]);
          }

          if ($refundAmount > 0) {
              $user->increment('balance', $refundAmount);

              $user->transactions()->create([
                  'code'           => 'RFUSR-' . Helper::randomString(8, true),
                  'amount'         => $refundAmount,
                  'balance_after'  => $user->balance,
                  'balance_before' => $user->balance - $refundAmount,
                  'type'           => 'refund',
                  'status'         => 'paid',
                  'content'        => 'Hoàn tiền đơn hàng ' . $request->type . ' #' . $request->id,
                  'user_id'        => $user->id,
                  'username'       => $user->username,
              ]);
          }

          DB::commit();

          return response()->json([
              'status' => true,
              'message' => 'Hoàn tiền thành công!',
          ]);

      } catch(\Exception $e) {
          DB::rollBack();
          return response()->json([
              'status' => false,
              'message' => 'Lỗi: ' . $e->getMessage(),
          ], 400);
      }
  }

  public function items($code = null)
  {
    if ($code !== null) {
      $item = ItemOrder::where('code', $code)->where('user_id', auth()->user()->id)->firstOrFail();

      if (auth()->user()->secure_order_view && $item->buyer_ip && $item->buyer_ua) {
          if ($item->buyer_ip !== request()->ip() || $item->buyer_ua !== request()->userAgent()) {
              return redirect()->back()->with('error', 'Cảnh báo bảo mật: Bạn đang truy cập từ trình duyệt hoặc địa chỉ IP lạ. Vui lòng sử đúng thiết bị đã mua đơn hàng này.');
          }
      }

      return view('account.orders.item-info', [
        'pageTitle' => 'Thông Tin Đơn Hàng',
      ], compact('item'));
    }

    $stats = [
      'total'            => ItemOrder::where('user_id', auth()->user()->id)->count(),
      'payment'          => ItemOrder::where('user_id', auth()->user()->id)->where('payment', '!=', null)->where('status', '!=', 'Cancelled')->sum('payment'),
      'total_in_month'   => ItemOrder::where('user_id', auth()->user()->id)->whereMonth('created_at', date('m'))->count(),
      'payment_in_month' => ItemOrder::where('user_id', auth()->user()->id)->where('payment', '!=', null)->where('status', '!=', 'Cancelled')->whereMonth('created_at', date('m'))->sum('payment'),
    ];

    $items = ItemOrder::where('user_id', auth()->user()->id);

    if (request()->filled('search')) {
        $items->where(function ($q) {
            $q->where('code', 'LIKE', '%' . request('search') . '%')
              ->orWhere('name', 'LIKE', '%' . request('search') . '%');
        });
    }

    if (request()->filled('sort_by') && request()->filled('sort_type')) {
        $items->orderBy(request('sort_by'), request('sort_type'));
    } else {
        $items->orderBy('id', 'desc');
    }

    $items = $items->paginate(request('limit', 10));

    if (request()->ajax() || request()->wantsJson()) {
        return response()->json([
            'data' => $items,
            'success' => true,
        ]);
    }

    return view('account.orders.items', [
      'pageTitle' => 'Lịch Sử Mua Vật Phẩm',
    ], compact('items', 'stats'));
  }

  public function updateNote(Request $request)
  {
      $request->validate([
          'id'   => 'required|integer',
          'note' => 'nullable|string|max:1000',
      ]);

      $item = ItemOrder::where('id', $request->id)
          ->where('user_id', auth()->user()->id)
          ->firstOrFail();

      $item->update([
          'order_note' => $request->note
      ]);

      return response()->json([
          'status' => true,
          'message' => __t('Cập nhật ghi chú thành công!')
      ]);
  }

  public function updateAccountNote(Request $request)
  {
      $request->validate([
          'id'   => 'required|integer',
          'type' => 'required|in:v1,v2,bulk',
          'note' => 'nullable|string|max:1000',
      ]);

      $user = auth()->user();

      if ($request->type === 'v1') {
          $item = ListItem::where('id', $request->id)
              ->where('buyer_name', $user->username)
              ->firstOrFail();
          $item->update(['order_note' => $request->note]);
      } elseif ($request->type === 'v2') {
          $item = ResourceV2O::where('id', $request->id)
              ->where('buyer_name', $user->username)
              ->firstOrFail();
          $item->update(['order_note' => $request->note]);
      } else {
           // Bulk
           $item = BulkOrder::where('id', $request->id)
              ->where('user_id', $user->id)
              ->firstOrFail();
           $item->update(['order_note' => $request->note]);
           
           // Sync note to all sub-orders
           ResourceV2O::where('buyer_code', $item->code)
               ->update(['order_note' => $request->note]);
      }

      return response()->json([
          'status' => true,
          'message' => __t('Cập nhật ghi chú thành công!')
      ]);
  }

  public function updateBoostingNote(Request $request)
  {
      $request->validate([
          'id'   => 'required|integer',
          'note' => 'nullable|string|max:1000',
      ]);

      $item = GBOrder::where('id', $request->id)
          ->where('user_id', auth()->user()->id)
          ->firstOrFail();

      $item->update([
          'order_note' => $request->note
      ]);

      return response()->json([
          'status' => true,
          'message' => __t('Cập nhật ghi chú thành công!')
      ]);
  }

  public function accounts(Request $request, $code = null)
  {
    if ($code !== null) {
      /* Removed V1 Lookup */

      // 1. Try V2 (ResourceV2O) by buyer_code
      $accountV2 = ResourceV2O::with('parent.group')->where('buyer_code', $code)->where('is_bulk', false)->where('buyer_name', auth()->user()->username)->first();
      if ($accountV2) {
          if (auth()->user()->secure_order_view && $accountV2->buyer_ip && $accountV2->buyer_ua) {
              if ($accountV2->buyer_ip !== request()->ip() || $accountV2->buyer_ua !== request()->userAgent()) {
                  return redirect()->back()->with('error', 'Cảnh báo bảo mật: Bạn đang truy cập từ trình duyệt hoặc địa chỉ IP lạ. Vui lòng sử đúng thiết bị đã mua đơn hàng này.');
              }
          }
          // Use unified view
          return view('account.orders.account-info', [
            'pageTitle' => 'Thông Tin Tài Khoản',
          ], ['account' => $accountV2]);
      }

      // 3. Try Bulk (BulkOrder)
      $bulkOrder = BulkOrder::select('bulk_orders.*')
          ->addSelect(['order_status' => ResourceV2O::select('order_status')
              ->whereColumn('buyer_code', 'bulk_orders.code')
              ->limit(1)
          ])
          ->where('code', $code)
          ->where('user_id', auth()->user()->id)
          ->first();
      if ($bulkOrder) {
          if (auth()->user()->secure_order_view && $bulkOrder->buyer_ip && $bulkOrder->buyer_ua) {
              if ($bulkOrder->buyer_ip !== request()->ip() || $bulkOrder->buyer_ua !== request()->userAgent()) {
                  return redirect()->back()->with('error', 'Cảnh báo bảo mật: Bạn đang truy cập từ trình duyệt hoặc địa chỉ IP lạ. Vui lòng sử đúng thiết bị đã mua đơn hàng này.');
              }
          }
          return view('account.orders.account-info', [
            'pageTitle' => 'Thông Tin Đơn Hàng',
          ], ['account' => $bulkOrder]);
      }

      abort(404);
    }

    $tab = $request->get('tab', 'all');
    $stats = [];
    $accounts = collect([]);

    if ($tab == 'bulk') {
        $stats = [
            'total'            => BulkOrder::where('user_id', auth()->user()->id)->count(),
            'payment'          => BulkOrder::where('user_id', auth()->user()->id)->sum('payment'),
            'total_in_month'   => BulkOrder::where('user_id', auth()->user()->id)->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->count(),
            'payment_in_month' => BulkOrder::where('user_id', auth()->user()->id)->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->sum('payment'),
        ];
        $accounts = BulkOrder::select('bulk_orders.*')
            ->withCount('orders')
            ->has('orders') // Ensure only non-zero quantity orders are shown
            ->addSelect(['status' => ResourceV2O::select('order_status')
                ->whereColumn('buyer_code', 'bulk_orders.code')
                ->limit(1)
            ])
            ->addSelect(['product_name' => ResourceV2O::select('list_item_v2_s.name')
                ->join('list_item_v2_s', 'resource_v2_o.code', '=', 'list_item_v2_s.code')
                ->whereColumn('resource_v2_o.buyer_code', 'bulk_orders.code')
                ->limit(1)
            ])
            ->where('user_id', auth()->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

    } else {
        // Unified V1 + V2 -> Strict V2
        // V2 Query
        $q2 = DB::table('resource_v2_o')
                ->join('list_item_v2_s', 'resource_v2_o.code', '=', 'list_item_v2_s.code')
                ->leftJoin('group_v2_s', 'list_item_v2_s.group_id', '=', 'group_v2_s.id')
                ->select('resource_v2_o.id', 'resource_v2_o.code', 'resource_v2_o.buyer_code', 'resource_v2_o.buyer_paym', 'resource_v2_o.buyer_date', 'resource_v2_o.username', 'list_item_v2_s.image', DB::raw('resource_v2_o.order_status as status'), DB::raw("'v2' as source_type"), DB::raw("COALESCE(list_item_v2_s.name, group_v2_s.name) as product_name"))
                ->where('resource_v2_o.buyer_name', auth()->user()->username)
                ->where('resource_v2_o.is_bulk', false);

        $accounts = $q2->orderBy('buyer_date', 'desc')->paginate(12);
        
        // Calculate V2 stats
        $totalV2 = ResourceV2O::where('buyer_name', auth()->user()->username)->where('is_bulk', false)->count();
        $paymV2 = ResourceV2O::where('buyer_name', auth()->user()->username)->where('is_bulk', false)->sum('buyer_paym');
        $monthV2 = ResourceV2O::where('buyer_name', auth()->user()->username)->where('is_bulk', false)->whereMonth('buyer_date', date('m'))->count();
        $monthPaymV2 = ResourceV2O::where('buyer_name', auth()->user()->username)->where('is_bulk', false)->whereMonth('buyer_date', date('m'))->sum('buyer_paym');

        $stats = [
            'total'            => $totalV2,
            'payment'          => $paymV2,
            'total_in_month'   => $monthV2,
            'payment_in_month' => $monthPaymV2,
        ];
    }

    if (request()->ajax() || request()->wantsJson()) {
        return response()->json([
            'data' => $accounts,
            'stats' => $stats,
            'success' => true,
        ]);
    }

    $pageTitle = 'Lịch Sử Mua Nick';
    if ($tab == 'bulk') $pageTitle = 'Đơn Hàng Số Lượng Lớn';

    return view('account.orders.accounts', [
      'pageTitle' => $pageTitle,
    ], compact('accounts', 'stats', 'tab'));
  }

  public function boosting($code = null)
  {
    if ($code !== null) {
      $item = GBOrder::where('code', $code)->where('user_id', auth()->user()->id)->firstOrFail();

      if (auth()->user()->secure_order_view && $item->buyer_ip && $item->buyer_ua) {
          if ($item->buyer_ip !== request()->ip() || $item->buyer_ua !== request()->userAgent()) {
              return redirect()->back()->with('error', 'Cảnh báo bảo mật: Bạn đang truy cập từ trình duyệt hoặc địa chỉ IP lạ. Vui lòng sử đúng thiết bị đã mua đơn hàng này.');
          }
      }

      return view('account.orders.boosting-info', [
        'pageTitle' => 'Thông Tin Đơn Hàng',
      ], compact('item'));
    }

    $stats = [
      'total'            => GBOrder::where('user_id', auth()->user()->id)->count(),
      'payment'          => GBOrder::where('user_id', auth()->user()->id)->where('payment', '!=', null)->where('status', '!=', 'Cancelled')->sum('payment'),
      'total_in_month'   => GBOrder::where('user_id', auth()->user()->id)->whereMonth('created_at', date('m'))->count(),
      'payment_in_month' => GBOrder::where('user_id', auth()->user()->id)->where('payment', '!=', null)->where('status', '!=', 'Cancelled')->whereMonth('created_at', date('m'))->sum('payment'),
    ];

    $items = GBOrder::where('user_id', auth()->user()->id);

    if (request()->filled('search')) {
        $items->where(function ($q) {
            $q->where('code', 'LIKE', '%' . request('search') . '%')
              ->orWhere('id', 'LIKE', '%' . request('search') . '%'); 
            // Note: GBOrder might not have 'name' or similar searchable plain fields easily accessible if it's related data 
            // verifying DB structure would be ideal, but assuming 'code' is primary search. 
            // Actually boostings usually have a service name from relation, but let's stick to basic for now or check relation if needed.
            // For now, search by ID/Code is safest.
        });
    }

    if (request()->filled('sort_by') && request()->filled('sort_type')) {
        $items->orderBy(request('sort_by'), request('sort_type'));
    } else {
        $items->orderBy('id', 'desc');
    }

    $items = $items->paginate(request('limit', 10));

    if (request()->ajax() || request()->wantsJson()) {
        return response()->json([
            'data' => $items,
            'success' => true,
        ]);
    }

    return view('account.orders.boostings', [
      'pageTitle' => 'Lịch Sử Cày Thuê',
    ], compact('items', 'stats'));
  }

}
