<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles
        $roles = [
            ['nama_role' => 'Admin'],
            ['nama_role' => 'Pengurus Gereja'],
            ['nama_role' => 'Pengurus Pelayanan'],
            ['nama_role' => 'Anggota Jemaat'],
        ];
        
        foreach ($roles as $role) {
            Role::create($role);
        }
        
        // Permissions
        $permissions = [
            // Keanggotaan
            ['nama_permission' => 'view_anggota'],
            ['nama_permission' => 'create_anggota'],
            ['nama_permission' => 'edit_anggota'],
            ['nama_permission' => 'delete_anggota'],
            
            // Keluarga
            ['nama_permission' => 'view_keluarga'],
            ['nama_permission' => 'create_keluarga'],
            ['nama_permission' => 'edit_keluarga'],
            ['nama_permission' => 'delete_keluarga'],
            
            // Komsel
            ['nama_permission' => 'view_komsel'],
            ['nama_permission' => 'create_komsel'],
            ['nama_permission' => 'edit_komsel'],
            ['nama_permission' => 'delete_komsel'],
            
            // Kegiatan
            ['nama_permission' => 'view_kegiatan'],
            ['nama_permission' => 'create_kegiatan'],
            ['nama_permission' => 'edit_kegiatan'],
            ['nama_permission' => 'delete_kegiatan'],
            
            // Kehadiran
            ['nama_permission' => 'view_kehadiran'],
            ['nama_permission' => 'create_kehadiran'],
            ['nama_permission' => 'edit_kehadiran'],
            
            // Jadwal Pelayanan
            ['nama_permission' => 'view_jadwal_pelayanan'],
            ['nama_permission' => 'create_jadwal_pelayanan'],
            ['nama_permission' => 'edit_jadwal_pelayanan'],
            ['nama_permission' => 'delete_jadwal_pelayanan'],
            
            // Users
            ['nama_permission' => 'view_users'],
            ['nama_permission' => 'create_users'],
            ['nama_permission' => 'edit_users'],
            ['nama_permission' => 'delete_users'],
            
            // Roles
            ['nama_permission' => 'view_roles'],
            ['nama_permission' => 'create_roles'],
            ['nama_permission' => 'edit_roles'],
            ['nama_permission' => 'delete_roles'],
        ];
        
        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
        
        // Assign permissions to roles
        $rolePermissions = [
            // Admin has all permissions
            1 => range(1, count($permissions)),
            
            // Pengurus Gereja
            2 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22],
            
            // Pengurus Pelayanan
            3 => [1, 9, 13, 17, 18, 19, 20, 21],
            
            // Anggota Jemaat
            4 => [1, 9, 13, 17, 19],
        ];
        
        foreach ($rolePermissions as $roleId => $permissionIds) {
            foreach ($permissionIds as $permissionId) {
                RolePermission::create([
                    'id_role' => $roleId,
                    'id_permission' => $permissionId,
                ]);
            }
        }
    }
}
