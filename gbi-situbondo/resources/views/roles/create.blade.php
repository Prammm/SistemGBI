@extends('layouts.app')

@section('title', 'Tambah Role')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Tambah Role</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Manajemen Role</a></li>
        <li class="breadcrumb-item active">Tambah Role</li>
    </ol>
    
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-tag me-1"></i>
                    Form Tambah Role
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form action="{{ route('roles.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="nama_role" class="form-label">Nama Role</label>
                            <input type="text" class="form-control @error('nama_role') is-invalid @enderror" id="nama_role" name="nama_role" value="{{ old('nama_role') }}" required>
                            @error('nama_role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Permissions</label>
                            <div class="card">
                                <div class="card-body">
                                    <div class="mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                            <label class="form-check-label fw-bold" for="selectAll">
                                                Pilih Semua
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        @php
                                            $groupedPermissions = $permissions->groupBy(function($permission) {
                                                return explode('_', $permission->nama_permission)[1] ?? 'other';
                                            });
                                        @endphp
                                        
                                        @foreach($groupedPermissions as $group => $items)
                                            <div class="col-md-4 mb-3">
                                                <h6 class="border-bottom pb-2">{{ ucfirst($group) }}</h6>
                                                @foreach($items as $permission)
                                                    <div class="form-check">
                                                        <input class="form-check-input permission-checkbox" type="checkbox" id="permission_{{ $permission->id_permission }}" name="permissions[]" value="{{ $permission->id_permission }}" {{ in_array($permission->id_permission, old('permissions', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="permission_{{ $permission->id_permission }}">
                                                            {{ ucfirst(explode('_', $permission->nama_permission)[0]) }} {{ ucfirst($group) }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @error('permissions')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Handle select all checkbox
        $('#selectAll').click(function() {
            $('.permission-checkbox').prop('checked', this.checked);
        });
        
        // Update selectAll status based on individual checkboxes
        $('.permission-checkbox').click(function() {
            if($('.permission-checkbox:checked').length == $('.permission-checkbox').length) {
                $('#selectAll').prop('checked', true);
            } else {
                $('#selectAll').prop('checked', false);
            }
        });
        
        // Set initial state of selectAll
        if($('.permission-checkbox:checked').length == $('.permission-checkbox').length && $('.permission-checkbox').length > 0) {
            $('#selectAll').prop('checked', true);
        }
    });
</script>
@endsection