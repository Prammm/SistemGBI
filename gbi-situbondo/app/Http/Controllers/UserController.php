<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Anggota;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_users')->only(['index', 'show']);
        $this->middleware('permission:create_users')->only(['create', 'store']);
        $this->middleware('permission:edit_users')->only(['edit', 'update']);
        $this->middleware('permission:delete_users')->only('destroy');
    }

    public function index()
    {
        $users = User::with(['role', 'anggota'])->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $anggota = Anggota::whereDoesntHave('user')->get();
        return view('users.create', compact('roles', 'anggota'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'id_role' => 'required|exists:roles,id_role',
            'id_anggota' => 'nullable|exists:anggota,id_anggota',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'id_role' => $request->id_role,
            'id_anggota' => $request->id_anggota,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $anggota = Anggota::whereDoesntHave('user')->orWhere('id_anggota', $user->id_anggota)->get();
        return view('users.edit', compact('user', 'roles', 'anggota'));
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'id_role' => 'required|exists:roles,id_role',
            'id_anggota' => 'nullable|exists:anggota,id_anggota',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->name = $request->name;
        $user->email = $request->email;
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        $user->id_role = $request->id_role;
        $user->id_anggota = $request->id_anggota;
        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}