<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_roles')->only(['index', 'show']);
        $this->middleware('permission:create_roles')->only(['create', 'store']);
        $this->middleware('permission:edit_roles')->only(['edit', 'update']);
        $this->middleware('permission:delete_roles')->only('destroy');
    }

    public function index()
    {
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_role' => 'required|string|max:255|unique:roles,nama_role',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id_permission',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $role = Role::create([
                'nama_role' => $request->nama_role,
            ]);

            foreach ($request->permissions as $permissionId) {
                RolePermission::create([
                    'id_role' => $role->id_role,
                    'id_permission' => $permissionId,
                ]);
            }

            DB::commit();
            return redirect()->route('roles.index')
                ->with('success', 'Role berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat membuat role: ' . $e->getMessage());
        }
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        return view('roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id_permission')->toArray();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validator = Validator::make($request->all(), [
            'nama_role' => 'required|string|max:255|unique:roles,nama_role,' . $role->id_role . ',id_role',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id_permission',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $role->nama_role = $request->nama_role;
            $role->save();

            // Hapus semua permission yang ada
            RolePermission::where('id_role', $role->id_role)->delete();

            // Tambahkan permission baru
            foreach ($request->permissions as $permissionId) {
                RolePermission::create([
                    'id_role' => $role->id_role,
                    'id_permission' => $permissionId,
                ]);
            }

            DB::commit();
            return redirect()->route('roles.index')
                ->with('success', 'Role berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui role: ' . $e->getMessage());
        }
    }

    public function destroy(Role $role)
    {
        // Cek apakah ada user yang menggunakan role ini
        $userCount = $role->users()->count();
        if ($userCount > 0) {
            return redirect()->route('roles.index')
                ->with('error', 'Tidak dapat menghapus role karena masih digunakan oleh ' . $userCount . ' pengguna.');
        }

        DB::beginTransaction();

        try {
            // Hapus semua permission role
            RolePermission::where('id_role', $role->id_role)->delete();
            
            // Hapus role
            $role->delete();

            DB::commit();
            return redirect()->route('roles.index')
                ->with('success', 'Role berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus role: ' . $e->getMessage());
        }
    }
}