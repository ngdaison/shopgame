<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Automation;
use Illuminate\Http\Request;
use Helper;

class AutomationController extends Controller
{
    public function index()
    {
        $automations = Automation::orderBy('id', 'desc')->get();
        return view('admin.automations.index', compact('automations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'time_in_hours' => 'required|integer|min:1',
            'status' => 'required|boolean'
        ]);

        if (in_array($request->type, ['delete_no_deposit_user', 'delete_inactive_no_deposit_user', 'delete_logs', 'delete_notifications'])) {
            $exists = Automation::where('type', $request->type)->exists();
            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Loại công việc này chỉ được phép tồn tại 1 Task!'
                ]);
            }
        }

        Automation::create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Thêm mới thành công!'
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:automations,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'time_in_hours' => 'required|integer|min:1',
            'status' => 'required|boolean'
        ]);

        $automation = Automation::findOrFail($request->id);

        if (in_array($request->type, ['delete_no_deposit_user', 'delete_inactive_no_deposit_user', 'delete_logs', 'delete_notifications']) && $automation->type !== $request->type) {
            $exists = Automation::where('type', $request->type)->exists();
            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Loại công việc này chỉ được phép tồn tại 1 Task!'
                ]);
            }
        }

        $automation->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật thành công!'
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:automations,id'
        ]);

        Automation::destroy($request->id);

        return response()->json([
            'status' => true,
            'message' => 'Xóa thành công!'
        ]);
    }

    public function status(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:automations,id',
            'status' => 'required|boolean'
        ]);

        Automation::where('id', $request->id)->update(['status' => $request->status]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật trạng thái thành công!'
        ]);
    }
}
