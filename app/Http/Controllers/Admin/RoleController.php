<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = \App\Models\Role::all();
        $permissionsMap = \App\Models\Role::getPermissionsMap();
        return view('admin.roles.index', compact('roles', 'permissionsMap'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'level' => 'nullable|integer|min:0',
            'hide_old_history' => 'nullable|date'
        ], [
            'name.required' => 'Tên vai trò không được để trống.',
            'name.unique' => 'Tên vai trò này đã tồn tại.',
            'level.integer' => 'Trường cấp bậc phải là một số nguyên.',
            'level.min' => 'Cấp bậc không được nhỏ hơn 0.'
        ]);

        $level = $request->level ?? 0;
        if ($level > auth()->user()->getRoleLevel()) {
            return back()->with('error', 'Bạn không thể tạo vai trò có cấp bậc cao hơn cấp bậc của chính mình.');
        }

        \App\Models\Role::create([
            'name' => $request->name,
            'level' => $level,
            'hide_old_history' => $request->hide_old_history,
            'permissions' => []
        ]);

        return back()->with('success', 'Thêm vai trò thành công!');
    }

    public function edit($id)
    {
        $role = \App\Models\Role::findOrFail($id);
        $permissionsMap = \App\Models\Role::getPermissionsMap();
        return view('admin.roles.edit', compact('role', 'permissionsMap'));
    }

    public function update(Request $request, $id)
    {
        $role = \App\Models\Role::findOrFail($id);
        $oldName = $role->name;
        
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'level' => 'nullable|integer|min:0',
            'hide_old_history' => 'nullable|date',
            'permissions' => 'nullable|array'
        ], [
            'name.required' => 'Tên vai trò không được để trống.',
            'name.unique' => 'Tên vai trò này đã tồn tại.',
            'level.integer' => 'Trường cấp bậc phải là một số nguyên.',
            'level.min' => 'Cấp bậc không được nhỏ hơn 0.'
        ]);

        // Check level hierarchy
        if ($role->level > auth()->user()->getRoleLevel()) {
             return back()->with('error', 'Bạn không thể chỉnh sửa vai trò có cấp bậc cao hơn cấp bậc của chính mình.');
        }

        $level = $request->level ?? 0;
        if ($level > auth()->user()->getRoleLevel()) {
            return back()->with('error', 'Bạn không thể đặt cấp bậc cao hơn cấp bậc của chính mình.');
        }

        $role->update([
            'name' => $request->name,
            'level' => $level,
            'hide_old_history' => $request->hide_old_history,
            'permissions' => $request->permissions ?? []
        ]);

        if ($oldName !== $request->name) {
            $users = \App\Models\User::where('role', 'like', "%{$oldName}%")->get();
            foreach ($users as $u) {
                if (empty($u->role)) continue;
                $roles = array_map('trim', explode(',', $u->role));
                if (($key = array_search($oldName, $roles)) !== false) {
                    $roles[$key] = $request->name;
                    $u->update(['role' => implode(',', $roles)]);
                }
            }
        }

        return back()->with('success', 'Cập nhật vai trò thành công!');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:roles,id'
        ]);

        $role = \App\Models\Role::findOrFail($request->id);
        
        if ($role->level > auth()->user()->getRoleLevel()) {
            return back()->with('error', 'Bạn không thể xóa vai trò có cấp bậc cao hơn cấp bậc của chính mình.');
        }

        $oldName = $role->name;
        $role->delete();

        $users = \App\Models\User::where('role', 'like', "%{$oldName}%")->get();
        foreach ($users as $u) {
            if (empty($u->role)) continue;
            $roles = array_map('trim', explode(',', $u->role));
            if (($key = array_search($oldName, $roles)) !== false) {
                unset($roles[$key]);
                $u->update(['role' => implode(',', array_filter($roles))]);
            }
        }

        return back()->with('success', 'Xóa vai trò thành công!');
    }
}
