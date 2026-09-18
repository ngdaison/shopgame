<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionsMap = \App\Models\Role::getPermissionsMap();
        $allPerms = [];
        
        $flattenPermissions = function($items) use (&$flattenPermissions) {
            $perms = [];
            foreach ($items as $key => $value) {
                if (is_array($value)) {
                    $perms = array_merge($perms, $flattenPermissions($value));
                } else {
                    $perms[] = $key;
                }
            }
            return $perms;
        };

        foreach ($permissionsMap as $group) {
            $allPerms = array_merge($allPerms, $flattenPermissions($group));
        }

        \App\Models\Role::updateOrCreate(
            ['name' => 'Admin'],
            ['permissions' => $allPerms]
        );

        \App\Models\Role::updateOrCreate(
            ['name' => 'Cộng tác viên'],
            ['permissions' => []]
        );

        \App\Models\Role::updateOrCreate(
            ['name' => 'Đối tác'],
            ['permissions' => []]
        );
        
        \App\Models\Role::updateOrCreate(
            ['name' => 'Thông Tin'],
            ['permissions' => ['view_blog', 'edit_blog']]
        );
    }
}
