@extends('layouts.app')

@section('title', 'Manajemen Role')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Manajemen Role</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Manajemen Role</li>
    </ol>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-tag me-1"></i>
            Daftar Role
            @if(Auth::user()->hasPermission('create_roles'))
                <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm float-end">
                    <i class="fas fa-plus"></i> Tambah Role
                </a>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatablesSimple" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Role</th>
                            <th>Jumlah Pengguna</th>
                            <th>Jumlah Permission</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                            <tr>
                                <td>{{ $role->nama_role }}</td>
                                <td>{{ $role->users->count() }}</td>
                                <td>{{ $role->permissions->count() }}</td>
                                <td>
                                    @if(Auth::user()->hasPermission('view_roles'))
                                        <a href="{{ route('roles.show', $role->id_role) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif
                                    
                                    @if(Auth::user()->hasPermission('edit_roles'))
                                        <a href="{{ route('roles.edit', $role->id_role) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    
                                    @if(Auth::user()->hasPermission('delete_roles') && $role->id_role != 1)
                                        <form action="{{ route('roles.destroy', $role->id_role) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus role ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection