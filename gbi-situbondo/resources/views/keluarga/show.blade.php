@extends('layouts.app')

@section('title', 'Detail Keluarga')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Detail Keluarga</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('keluarga.index') }}">Keluarga</a></li>
        <li class="breadcrumb-item active">Detail Keluarga</li>
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
    
    <div class="row">
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-home me-1"></i>
                    Informasi Keluarga
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Nama Keluarga</div>
                        <div class="col-md-8">{{ $keluarga->nama_keluarga }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Jumlah Anggota</div>
                        <div class="col-md-8">{{ count($anggota) }}</div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('keluarga.edit', $keluarga->id_keluarga) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('keluarga.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-plus me-1"></i>
                    Tambah Anggota Keluarga
                </div>
                <div class="card-body">
                    <form action="{{ route('keluarga.add-member', $keluarga->id_keluarga) }}" method="POST">
                        @csrf
                       <div class="mb-3">
                           <label for="id_anggota" class="form-label">Pilih Anggota</label>
                           <select class="form-select @error('id_anggota') is-invalid @enderror" id="id_anggota" name="id_anggota" required>
                               <option value="">Pilih Anggota</option>
                               @foreach(\App\Models\Anggota::whereNull('id_keluarga')->orWhere('id_keluarga', '!=', $keluarga->id_keluarga)->get() as $a)
                                   <option value="{{ $a->id_anggota }}" {{ old('id_anggota') == $a->id_anggota ? 'selected' : '' }}>
                                       {{ $a->nama }}
                                   </option>
                               @endforeach
                           </select>
                           @error('id_anggota')
                               <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                       </div>
                       
                       <div class="mb-3">
                           <label for="hubungan" class="form-label">Hubungan</label>
                           <select class="form-select @error('hubungan') is-invalid @enderror" id="hubungan" name="hubungan" required>
                               <option value="">Pilih Hubungan</option>
                               <option value="Kepala Keluarga" {{ old('hubungan') == 'Kepala Keluarga' ? 'selected' : '' }}>Kepala Keluarga</option>
                               <option value="Istri" {{ old('hubungan') == 'Istri' ? 'selected' : '' }}>Istri</option>
                               <option value="Anak" {{ old('hubungan') == 'Anak' ? 'selected' : '' }}>Anak</option>
                               <option value="Orang Tua" {{ old('hubungan') == 'Orang Tua' ? 'selected' : '' }}>Orang Tua</option>
                               <option value="Saudara" {{ old('hubungan') == 'Saudara' ? 'selected' : '' }}>Saudara</option>
                               <option value="Lainnya" {{ old('hubungan') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                           </select>
                           @error('hubungan')
                               <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                       </div>
                       
                       <div class="mb-3">
                           <label for="id_anggota_tujuan" class="form-label">Terkait Dengan (Opsional)</label>
                           <select class="form-select @error('id_anggota_tujuan') is-invalid @enderror" id="id_anggota_tujuan" name="id_anggota_tujuan">
                               <option value="">Pilih Anggota</option>
                               @foreach($anggota as $a)
                                   <option value="{{ $a->id_anggota }}" {{ old('id_anggota_tujuan') == $a->id_anggota ? 'selected' : '' }}>
                                       {{ $a->nama }}
                                   </option>
                               @endforeach
                           </select>
                           <small class="form-text text-muted">Pilih anggota yang terkait dengan hubungan, misalnya jika "Anak" maka pilih orang tuanya.</small>
                           @error('id_anggota_tujuan')
                               <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                       </div>
                       
                       <div class="mb-3">
                           <button type="submit" class="btn btn-primary">Tambah Anggota</button>
                       </div>
                   </form>
               </div>
           </div>
       </div>
       
       <div class="col-xl-8">
           <div class="card mb-4">
               <div class="card-header">
                   <i class="fas fa-users me-1"></i>
                   Anggota Keluarga
               </div>
               <div class="card-body">
                   @if(count($anggota) > 0)
                       <div class="table-responsive">
                           <table class="table table-bordered">
                               <thead>
                                   <tr>
                                       <th>Nama</th>
                                       <th>Jenis Kelamin</th>
                                       <th>Tanggal Lahir</th>
                                       <th>Hubungan</th>
                                       <th>Aksi</th>
                                   </tr>
                               </thead>
                               <tbody>
                                   @foreach($anggota as $a)
                                       <tr>
                                           <td>
                                               <a href="{{ route('anggota.show', $a->id_anggota) }}">
                                                   {{ $a->nama }}
                                               </a>
                                           </td>
                                           <td>{{ $a->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                           <td>{{ \Carbon\Carbon::parse($a->tanggal_lahir)->format('d/m/Y') }}</td>
                                           <td>
                                               @php
                                                   $hubunganText = '';
                                                   foreach($hubungan as $h) {
                                                       if($h->id_anggota == $a->id_anggota) {
                                                           $hubunganText .= $h->hubungan . ' dari ' . $h->anggotaTujuan->nama . ', ';
                                                       } elseif($h->id_anggota_tujuan == $a->id_anggota) {
                                                           $hubunganText .= 'Memiliki ' . $h->hubungan . ' ' . $h->anggota->nama . ', ';
                                                       }
                                                   }
                                                   echo rtrim($hubunganText, ', ') ?: '-';
                                               @endphp
                                           </td>
                                           <td>
                                               <form action="{{ route('keluarga.remove-member', ['keluarga' => $keluarga->id_keluarga, 'anggota' => $a->id_anggota]) }}" method="POST" class="d-inline">
                                                   @csrf
                                                   @method('DELETE')
                                                   <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus anggota ini dari keluarga?')">
                                                       <i class="fas fa-user-minus"></i> Hapus
                                                   </button>
                                               </form>
                                           </td>
                                       </tr>
                                   @endforeach
                               </tbody>
                           </table>
                       </div>
                   @else
                       <p class="text-center">Belum ada anggota dalam keluarga ini.</p>
                   @endif
               </div>
           </div>
           
           @if(count($hubungan) > 0)
           <div class="card mb-4">
               <div class="card-header">
                   <i class="fas fa-sitemap me-1"></i>
                   Hubungan Keluarga
               </div>
               <div class="card-body">
                   <div class="table-responsive">
                       <table class="table table-bordered">
                           <thead>
                               <tr>
                                   <th>Anggota</th>
                                   <th>Hubungan</th>
                                   <th>Anggota Tujuan</th>
                               </tr>
                           </thead>
                           <tbody>
                               @foreach($hubungan as $h)
                                   <tr>
                                       <td>
                                           <a href="{{ route('anggota.show', $h->id_anggota) }}">
                                               {{ $h->anggota->nama }}
                                           </a>
                                       </td>
                                       <td>{{ $h->hubungan }}</td>
                                       <td>
                                           <a href="{{ route('anggota.show', $h->id_anggota_tujuan) }}">
                                               {{ $h->anggotaTujuan->nama }}
                                           </a>
                                       </td>
                                   </tr>
                               @endforeach
                           </tbody>
                       </table>
                   </div>
               </div>
           </div>
           @endif
       </div>
   </div>
</div>
@endsection

@section('scripts')
<script>
   $(document).ready(function() {
       $('#id_anggota').select2({
           placeholder: "Pilih Anggota",
           allowClear: true
       });
       
       $('#id_anggota_tujuan').select2({
           placeholder: "Pilih Anggota Tujuan",
           allowClear: true
       });
   });
</script>
@endsection