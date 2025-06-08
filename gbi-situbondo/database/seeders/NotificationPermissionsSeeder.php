<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\RolePermission;

class NotificationPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Add new permissions for notifications
        $newPermissions = [
            'send_notifications',
            'manage_email_settings',
            'view_notification_logs',
            'test_email_system'
        ];
        
        foreach ($newPermissions as $permission) {
            Permission::firstOrCreate([
                'nama_permission' => $permission
            ]);
        }
        
        // Assign to Admin (role 1)
        $adminPermissions = Permission::whereIn('nama_permission', $newPermissions)
            ->pluck('id_permission');
            
        foreach ($adminPermissions as $permissionId) {
            RolePermission::firstOrCreate([
                'id_role' => 1,
                'id_permission' => $permissionId
            ]);
        }
        
        // Assign limited permissions to Pengurus Gereja (role 2)
        $pengurusPermissions = Permission::whereIn('nama_permission', [
            'send_notifications',
            'view_notification_logs'
        ])->pluck('id_permission');
        
        foreach ($pengurusPermissions as $permissionId) {
            RolePermission::firstOrCreate([
                'id_role' => 2,
                'id_permission' => $permissionId
            ]);
        }
    }
}
