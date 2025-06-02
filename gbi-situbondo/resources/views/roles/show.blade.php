@extends('layouts.app')

@section('title', 'Detail Role')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Detail Role</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Manajemen Role</a></li>
        <li class="breadcrumb-item active">Detail Role</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Informasi Role
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Nama Role</div>
                        <div class="col-md-8">{{ $role->nama_role }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Jumlah Pengguna</div>
                        <div class="col-md-8">{{ $role->users->count() }} pengguna</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Jumlah Permission</div>
                        <div class="col-md-8">{{ $role->permissions->count() }} permission</div>
                    </div>
                </div>
                <div class="card-footer">
                    @if(Auth::user()->hasPermission('edit_roles'))
                        <a href="{{ route('roles.edit', $role->id_role) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @endif
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-lock me-1"></i>
                    Daftar Permission
                </div>
                <div class="card-body">
                    @php
                        $groupedPermissions = $role->permissions->groupBy(function($permission) {
                            return explode('_', $permission->nama_permission)[1] ?? 'other';
                        });
                    @endphp
                    
                    <div class="row">
                        @foreach($groupedPermissions as $group => $items)
                            <div class="col-md-6 mb-3">
                                <h6 class="border-bottom pb-2">{{ ucfirst($group) }}</h6>
                                <ul class="list-unstyled">
                                    @foreach($items as $permission)
                                        <li>
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            {{ ucfirst(explode('_', $permission->nama_permission)[0]) }} {{ ucfirst($group) }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-users me-1"></i>
                    Pengguna dengan Role {{ $role->nama_role }}
                </div>
                <div class="card-body">
                    @if($role->users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Anggota</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($role->users as $user)
                                        <tr>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @if($user->anggota)
                                                    <a href="{{ route('anggota.show', $user->anggota->id_anggota) }}">
                                                        {{ $user->anggota->nama }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if(Auth::user()->hasPermission('view_users'))
                                                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                                
                                                @if(Auth::user()->hasPermission('edit_users'))
                                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center">Tidak ada pengguna dengan role ini.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection