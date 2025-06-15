@extends('layouts.app')

@section('title', 'Master Posisi Pelayanan')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Master Posisi Pelayanan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Master Posisi</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-list me-1"></i>
            Daftar Posisi Pelayanan
            
            <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#addPositionModal">
                <i class="fas fa-plus"></i> Tambah Posisi
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="positionsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Posisi</th>
                            <th>Kategori</th>
                            <th>Urutan</th>
                            <th>Workload Score</th>
                            <th>Status</th>
                            <th>Usage</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($positions as $position)
                            <tr>
                                <td>
                                    <strong>{{ $position->nama_posisi }}</strong>
                                    @if($position->deskripsi)
                                        <br><small class="text-muted">{{ $position->deskripsi }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $position->kategori }}</span>
                                </td>
                                <td>{{ $position->urutan }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $position->workload_score }}</span>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               {{ $position->is_active ? 'checked' : '' }}
                                               onchange="toggleStatus({{ $position->id_posisi }})">
                                        <label class="form-check-label">
                                            {{ $position->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $specCount = $position->spesialisasi()->count();
                                        $scheduleCount = $position->jadwalPelayanan()->count();
                                    @endphp
                                    <small class="text-muted">
                                        {{ $specCount }} spesialisasi<br>
                                        {{ $scheduleCount }} jadwal
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" 
                                                onclick="editPosition({{ $position->id_posisi }})"
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if($specCount == 0 && $scheduleCount == 0)
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deletePosition({{ $position->id_posisi }})"
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Position Modal -->
<div class="modal fade" id="addPositionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Posisi Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPositionForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Posisi</label>
                        <input type="text" class="form-control" name="nama_posisi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                            <option value="new">+ Kategori Baru</option>
                        </select>
                        <input type="text" class="form-control mt-2" name="kategori_baru" 
                               placeholder="Nama kategori baru..." style="display: none;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Urutan</label>
                        <input type="number" class="form-control" name="urutan" value="0" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Workload Score (1-10)</label>
                        <input type="range" class="form-range" name="workload_score" min="1" max="10" value="3" 
                               oninput="this.nextElementSibling.textContent = this.value">
                        <div class="text-center"><span class="badge bg-info">3</span></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control" name="deskripsi" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Position Modal -->
<div class="modal fade" id="editPositionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Posisi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPositionForm">
                <input type="hidden" name="position_id">
                <div class="modal-body">
                    <!-- Same fields as add modal -->
                    <div class="mb-3">
                        <label class="form-label">Nama Posisi</label>
                        <input type="text" class="form-control" name="nama_posisi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                            <option value="new">+ Kategori Baru</option>
                        </select>
                        <input type="text" class="form-control mt-2" name="kategori_baru" 
                               placeholder="Nama kategori baru..." style="display: none;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Urutan</label>
                        <input type="number" class="form-control" name="urutan" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Workload Score (1-10)</label>
                        <input type="range" class="form-range" name="workload_score" min="1" max="10" 
                               oninput="this.nextElementSibling.textContent = this.value">
                        <div class="text-center"><span class="badge bg-info">3</span></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi (Opsional)</label>
                        <textarea class="form-control" name="deskripsi" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Handle category selection
    $('select[name="kategori"]').change(function() {
        const newCategoryInput = $(this).siblings('input[name="kategori_baru"]');
        if ($(this).val() === 'new') {
            newCategoryInput.show().prop('required', true);
        } else {
            newCategoryInput.hide().prop('required', false);
        }
    });
    
    // Add position form
    $('#addPositionForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const kategori = formData.get('kategori');
        
        // Use new category if selected
        if (kategori === 'new') {
            formData.set('kategori', formData.get('kategori_baru'));
        }
        
        $.ajax({
            url: '{{ route("master.posisi.store") }}',
            method: 'POST',
            data: Object.fromEntries(formData),
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    alert('✓ ' + response.message);
                    location.reload();
                } else {
                    alert('✗ ' + response.message);
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    let errorMsg = 'Validation errors:\n';
                    Object.keys(errors).forEach(key => {
                        errorMsg += '- ' + errors[key][0] + '\n';
                    });
                    alert(errorMsg);
                } else {
                    alert('✗ Terjadi kesalahan saat menyimpan');
                }
            }
        });
    });
    
    // Edit position form
    $('#editPositionForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const positionId = formData.get('position_id');
        const kategori = formData.get('kategori');
        
        // Use new category if selected
        if (kategori === 'new') {
            formData.set('kategori', formData.get('kategori_baru'));
        }
        
        $.ajax({
            url: `/master/posisi/${positionId}`,
            method: 'PUT',
            data: Object.fromEntries(formData),
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    alert('✓ ' + response.message);
                    location.reload();
                } else {
                    alert('✗ ' + response.message);
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    let errorMsg = 'Validation errors:\n';
                    Object.keys(errors).forEach(key => {
                        errorMsg += '- ' + errors[key][0] + '\n';
                    });
                    alert(errorMsg);
                } else {
                    alert('✗ Terjadi kesalahan saat mengupdate');
                }
            }
        });
    });
});

function toggleStatus(positionId) {
    $.ajax({
        url: `/master/posisi/${positionId}/toggle-status`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                // Update label
                const row = $(`tr:has(input[onchange*="${positionId}"])`);
                const label = row.find('.form-check-label');
                label.text(response.is_active ? 'Aktif' : 'Nonaktif');
            } else {
                alert('✗ ' + response.message);
            }
        },
        error: function() {
            alert('✗ Terjadi kesalahan saat mengubah status');
        }
    });
}

function editPosition(positionId) {
    // Get position data first
    const row = $(`tr:has(button[onclick*="editPosition(${positionId})"])`);
    const positionName = row.find('td:first strong').text();
    const kategori = row.find('.badge').text();
    const urutan = row.find('td:nth-child(3)').text();
    const workloadScore = row.find('td:nth-child(4) .badge').text();
    const deskripsi = row.find('td:first small').text();
    
    // Populate form
    const form = $('#editPositionForm');
    form.find('input[name="position_id"]').val(positionId);
    form.find('input[name="nama_posisi"]').val(positionName);
    form.find('select[name="kategori"]').val(kategori);
    form.find('input[name="urutan"]').val(urutan);
    form.find('input[name="workload_score"]').val(workloadScore);
    form.find('input[name="workload_score"]').next().find('span').text(workloadScore);
    form.find('textarea[name="deskripsi"]').val(deskripsi);
    
    // Show modal
    $('#editPositionModal').modal('show');
}

function deletePosition(positionId) {
    if (confirm('Yakin ingin menghapus posisi ini?')) {
        $.ajax({
            url: `/master/posisi/${positionId}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    alert('✓ ' + response.message);
                    location.reload();
                } else {
                    alert('✗ ' + response.message);
                }
            },
            error: function() {
                alert('✗ Terjadi kesalahan saat menghapus');
            }
        });
    }
}
</script>
@endsection