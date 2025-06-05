<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions
        $permissions = [
            // Basic CRUD permissions
            'view_anggota',
            'create_anggota',
            'edit_anggota',
            'delete_anggota',
            
            'view_keluarga',
            'create_keluarga',
            'edit_keluarga',
            'delete_keluarga',
            
            'view_komsel',
            'create_komsel',
            'edit_komsel',
            'delete_komsel',
            
            'view_kegiatan',
            'create_kegiatan',
            'edit_kegiatan',
            'delete_kegiatan',
            
            'view_pelaksanaan',
            'create_pelaksanaan',
            'edit_pelaksanaan',
            'delete_pelaksanaan',
            
            'view_kehadiran',
            'create_kehadiran',
            'edit_kehadiran',
            'delete_kehadiran',
            
            'view_pelayanan',
            'create_pelayanan',
            'edit_pelayanan',
            'delete_pelayanan',
            
            // Report permissions
            'view_laporan',
            'export_laporan',
            
            // Role and User management
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
            
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            
            // Advanced permissions
            'manage_system',
            'view_analytics',
            'bulk_operations',
        ];
        
        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'nama_permission' => $permission
            ]);
        }
        
        // Define roles and their permissions
        $rolePermissions = [
            // Admin (id_role = 1) - Full access
            1 => $permissions,
            
            // Pengurus Gereja (id_role = 2) - Full access except system management
            2 => array_filter($permissions, function($perm) {
                return !in_array($perm, ['manage_system', 'view_users', 'create_users', 'edit_users', 'delete_users', 'view_roles', 'create_roles', 'edit_roles', 'delete_roles']);
            }),
            
            // Petugas Pelayanan (id_role = 3) - Limited access
            3 => [
                'view_anggota',
                'view_keluarga',
                'view_komsel',
                'view_kegiatan',
                'view_pelaksanaan',
                'view_kehadiran',
                'create_kehadiran',
                'edit_kehadiran',
                'view_pelayanan',
                'create_pelayanan',
                'edit_pelayanan',
                'view_laporan',
                'export_laporan',
            ],
            
            // Anggota Jemaat (id_role = 4) - Very limited access
            4 => [
                'view_kehadiran',
                'create_kehadiran', // Only for self and family
                'view_pelayanan', // Only own services
            ],
        ];
        
        // Assign permissions to roles
        foreach ($rolePermissions as $roleId => $perms) {
            // Clear existing permissions for this role
            RolePermission::where('id_role', $roleId)->delete();
            
            foreach ($perms as $permissionName) {
                $permission = Permission::where('nama_permission', $permissionName)->first();
                if ($permission) {
                    RolePermission::firstOrCreate([
                        'id_role' => $roleId,
                        'id_permission' => $permission->id_permission
                    ]);
                }
            }
        }
    }
}