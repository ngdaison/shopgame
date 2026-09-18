<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GBOrder;
use App\Models\ItemOrder;
use App\Models\SecuritySetting;
use Helper;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // 1. Authorize using API Key from security settings
        $other = SecuritySetting::get('security_other', []);
        $apiKey = $other['api_key'] ?? null;

        if (!$apiKey || $request->query('key') !== $apiKey) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $type = $request->query('orders');
        $id = $request->query('id');
        $status = $request->query('status');
        $adminNote = $request->query('admin_note');

        // Check for update request
        if ($status || $adminNote !== null || $id) {
            if (!$id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Thiếu tham số "id" để cập nhật đơn hàng'
                ], 400);
            }
            if (!$type) {
                return response()->json([
                    'status' => false,
                    'message' => 'Thiếu tham số "orders" (boosting hoặc items) để cập nhật đơn hàng'
                ], 400);
            }
            if (!in_array($type, ['boosting', 'items'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tham số "orders" không hợp lệ. Phải là "boosting" hoặc "items"'
                ], 400);
            }
            if (!$status && $adminNote === null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Thiếu tham số "status" hoặc "admin_note" để cập nhật'
                ], 400);
            }

            return $this->updateOrder($request, $id, $type, $status, $adminNote);
        }

        $results = [];

        // 2. Fetch Boosting Orders (GBOrder)
        if (!$type || $type === 'boosting') {
            $boostingOrders = GBOrder::orderBy('id', 'desc')->limit(100)->get();
            foreach ($boostingOrders as $order) {
                $results[] = $this->mapBoostingOrder($order);
            }
        }

        // 3. Fetch Item Orders (ItemOrder)
        if (!$type || $type === 'items') {
            $itemOrders = ItemOrder::orderBy('id', 'desc')->limit(100)->get();
            foreach ($itemOrders as $order) {
                $results[] = $this->mapItemOrder($order);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách đơn hàng thành công',
            'data' => $results
        ]);
    }

    protected function mapBoostingOrder($order)
    {
        $res = [
            'id' => $order->id,
            'name' => $order->name,
            'code' => $order->code,
            'payment' => $order->payment,
            'order_type' => 'boosting',
            'input_user' => $order->input_user ?? 0,
            'input_pass' => $order->input_pass ?? 0,
            'input_extra' => $order->input_extra ?? 0,
            'order_note' => $order->order_note ?? 0,
            'admin_note' => $order->admin_note ?? 0,
            'assigned_to' => $order->assigned_to ?? 0,
            'assigned_at' => $order->assigned_at ? $order->assigned_at->format('Y-m-d H:i:s') : 0,
            'assigned_completed' => $order->assigned_completed ? $order->assigned_completed->format('Y-m-d H:i:s') : 0,
            'assigned_payment' => ($order->assigned_payment == -1 || is_null($order->assigned_payment)) ? 0 : $order->assigned_payment,
            'assigned_status' => $order->assigned_status,
            'assigned_status_text' => $this->getAssignedStatus($order->assigned_status),
            'status' => strtolower($order->status),
            'created_at' => $order->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $order->updated_at->format('Y-m-d H:i:s'),
            'accounts' => $this->getFormattedAccounts($order->code),
        ];

        return $res;
    }

    protected function mapItemOrder($order)
    {
        $res = [
            'id' => $order->id,
            'name' => $order->name,
            'type' => $order->type,
            'code' => $order->code,
            'payment' => $order->payment,
            'order_type' => 'items',
            'input_user' => $order->input_user ?? 0,
            'input_pass' => $order->input_pass ?? 0,
            'input_auth' => $order->input_auth ?? 0,
            'input_contact' => $order->input_contact ?? 0,
            'order_note' => $order->order_note ?? 0,
            'admin_note' => $order->admin_note ?? 0,
            'assigned_to' => $order->assigned_to ?? 0,
            'assigned_at' => $order->assigned_at ? $order->assigned_at->format('Y-m-d H:i:s') : 0,
            'assigned_completed' => $order->assigned_completed ? $order->assigned_completed->format('Y-m-d H:i:s') : 0,
            'assigned_payment' => ($order->assigned_payment == -1 || is_null($order->assigned_payment)) ? 0 : $order->assigned_payment,
            'assigned_status' => $order->assigned_status,
            'assigned_status_text' => $this->getAssignedStatus($order->assigned_status),
            'status' => strtolower($order->status),
            'created_at' => $order->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $order->updated_at->format('Y-m-d H:i:s'),
            'accounts' => $this->getFormattedAccounts($order->code),
        ];

        return $res;
    }

    protected function updateOrder($request, $id, $type, $status, $adminNote)
    {
        $order = null;
        if ($type === 'boosting') {
            $order = GBOrder::find($id);
        } elseif ($type === 'items') {
            $order = ItemOrder::find($id);
        }

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }

        if ($status) {
            $order->status = $status;
        }
        if ($adminNote !== null) {
            $order->admin_note = $adminNote;
        }

        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'Order updated successfully',
            'data' => $type === 'boosting' ? $this->mapBoostingOrder($order) : $this->mapItemOrder($order)
        ]);
    }

    protected function getFormattedAccounts($code)
    {
        $resources = \App\Models\ResourceV2O::where('code', $code)->get();
        if ($resources->isEmpty()) {
            return [];
        }

        $formatted = [];
        foreach ($resources as $res) {
            $formatted[] = $res->data;
        }
        return $formatted;
    }

    protected function getAssignedStatus($status)
    {
        if ($status === 'Completed') {
            return 'Đã nhận';
        } elseif ($status === 'WaitPayment') {
            return 'Chờ duyệt';
        } else {
            return 'Chưa nhận';
        }
    }
}
