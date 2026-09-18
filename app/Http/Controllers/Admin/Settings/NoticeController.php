<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\SystemNotice;
use Helper;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = [];

        foreach (SystemNotice::all() as $item) {
            $notices[strtolower($item->name)] = $item->value;
        }

        return view('admin.settings.notices', $notices);
    }

    public function update(Request $request)
    {

        $request->validate([
            'content' => 'nullable|string',
        ]);

        $type = $request->input('type', null);

        $value = $request->input('content', null);

        $config = SystemNotice::firstOrCreate(['name' => $type], ['value' => '']);

        $config->update([
            'value' => Helper::htmlPurifier($value),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật thông báo thành công ['.$type.'].'
        ]);
    }
}
