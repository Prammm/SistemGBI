<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\KomselController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HubunganKeluargaController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\KehadiranController;
use App\Http\Controllers\KeluargaController;
use App\Http\Controllers\PelaksanaanKegiatanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PelayananController;
use App\Http\Controllers\LaporanController;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/anggota', [ProfileController::class, 'updateAnggota'])->name('profile.update.anggota');
    
    Route::resource('anggota', AnggotaController::class)->parameters([
        'anggota' => 'anggota'
    ]);
    Route::resource('keluarga', KeluargaController::class);
    Route::resource('hubungan', HubunganKeluargaController::class);
    
    // Kegiatan routes - Custom routes MUST come BEFORE resource route
    Route::get('kegiatan/calendar', [KegiatanController::class, 'calendar'])->name('kegiatan.calendar');
    Route::get('kegiatan/events', [KegiatanController::class, 'getEvents'])->name('kegiatan.events');
    Route::resource('kegiatan', KegiatanController::class);
    
    Route::resource('pelaksanaan', PelaksanaanKegiatanController::class);
    Route::delete('pelaksanaan/{pelaksanaan}/destroy-series', [PelaksanaanKegiatanController::class, 'destroyRecurringSeries'])
        ->name('pelaksanaan.destroy-series');
    Route::get('pelaksanaan/{pelaksanaan}/series', [PelaksanaanKegiatanController::class, 'showRecurringSeries'])
        ->name('pelaksanaan.series');
    
    Route::resource('kehadiran', KehadiranController::class);
    Route::resource('komsel', KomselController::class);

    // Enhanced Pelayanan Routes
    Route::prefix('pelayanan')->name('pelayanan.')->group(function () {
        // Basic CRUD
        Route::get('/', [PelayananController::class, 'index'])->name('index');
        Route::get('/create', [PelayananController::class, 'create'])->name('create');
        Route::post('/store', [PelayananController::class, 'store'])->name('store');
        Route::get('/konfirmasi/{id}/{status}', [PelayananController::class, 'konfirmasi'])->name('konfirmasi');
        Route::delete('/{id}', [PelayananController::class, 'destroy'])->name('destroy');
        
        // Advanced Generator
        Route::get('/generator', [PelayananController::class, 'showGenerator'])->name('generator');
        Route::post('/generate', [PelayananController::class, 'generateSchedule'])->name('generate');
        Route::post('/bulk-generate', [PelayananController::class, 'bulkGenerate'])->name('bulk-generate');
        
        // Availability Management
        Route::get('/availability/{id?}', [PelayananController::class, 'editAvailability'])->name('availability');
        Route::post('/save-availability', [PelayananController::class, 'saveAvailability'])->name('save-availability');
        
        // Member Management & Profiles
        Route::get('/members', [PelayananController::class, 'members'])->name('members');
        Route::get('/member-profile/{id}', [PelayananController::class, 'memberProfile'])->name('member-profile');
        Route::get('/member-history/{id}', [PelayananController::class, 'memberHistory'])->name('member-history');
        Route::get('/assign-regular/{id}', [PelayananController::class, 'assignRegular'])->name('assign-regular');
        Route::post('/save-regular-assignment/{id}', [PelayananController::class, 'saveRegularAssignment'])->name('save-regular-assignment');
        Route::get('/members/export', [PelayananController::class, 'exportMembers'])->name('members.export');
        
        // Analytics & Reports
        Route::get('/analytics', [PelayananController::class, 'analytics'])->name('analytics');
        Route::get('/export', [PelayananController::class, 'export'])->name('export');
        
        // Debug & Setup Routes
        Route::get('/debug-data', [PelayananController::class, 'debugData'])->name('debug-data');
        Route::get('/setup-sample', [PelayananController::class, 'setupSampleData'])->name('setup-sample');
        
        // Notifications & Conflict Resolution
        Route::post('/send-notifications', [PelayananController::class, 'sendNotifications'])->name('send-notifications');
        Route::post('/resolve-conflicts', [PelayananController::class, 'resolveConflicts'])->name('resolve-conflicts');
        
        // API endpoints for AJAX calls
        Route::get('/api/anggota-availability/{id}', function($id) {
            $anggota = \App\Models\Anggota::with('spesialisasi')->findOrFail($id);
            return response()->json([
                'ketersediaan_hari' => $anggota->ketersediaan_hari,
                'ketersediaan_jam' => $anggota->ketersediaan_jam,
                'blackout_dates' => $anggota->blackout_dates,
                'spesialisasi' => $anggota->spesialisasi->map(function($s) {
                    return [
                        'posisi' => $s->posisi,
                        'is_reguler' => $s->is_reguler,
                        'prioritas' => $s->prioritas
                    ];
                })
            ]);
        })->name('api.anggota-availability');
        
        Route::get('/api/position-candidates/{posisi}/{pelaksanaan_id}', function($posisi, $pelaksanaanId) {
            $pelaksanaan = \App\Models\PelaksanaanKegiatan::findOrFail($pelaksanaanId);
            $eventDate = $pelaksanaan->tanggal_kegiatan;
            $eventStart = $pelaksanaan->jam_mulai;
            $eventEnd = $pelaksanaan->jam_selesai;
            
            $candidates = \App\Models\Anggota::with('spesialisasi')
                ->whereHas('spesialisasi', function($q) use ($posisi) {
                    $q->where('posisi', $posisi);
                })
                ->get()
                ->filter(function($anggota) use ($eventDate, $eventStart, $eventEnd) {
                    return $anggota->isAvailable($eventDate, $eventStart, $eventEnd);
                })
                ->map(function($anggota) use ($posisi) {
                    $spec = $anggota->spesialisasi->where('posisi', $posisi)->first();
                    return [
                        'id' => $anggota->id_anggota,
                        'nama' => $anggota->nama,
                        'is_reguler' => $spec ? $spec->is_reguler : false,
                        'prioritas' => $spec ? $spec->prioritas : 5,
                        'last_service' => $anggota->getLastServiceDate($posisi),
                        'rest_days' => $anggota->getRestDays($posisi),
                        'frequency' => $anggota->getServiceFrequency(3, $posisi)
                    ];
                });
            
            return response()->json($candidates->values());
        })->name('api.position-candidates');
        
        Route::get('/api/workload-distribution/{start_date}/{end_date}', function($startDate, $endDate) {
            $distribution = \App\Models\SchedulingHistory::getWorkloadDistribution($startDate, $endDate);
            return response()->json($distribution);
        })->name('api.workload-distribution');
        
        Route::post('/api/preview-generate', function(\Illuminate\Http\Request $request) {
            // Generate preview without actually saving
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'positions' => 'required|array',
                'anggota' => 'required|array',
                'algorithm' => 'required|string',
                'id_pelaksanaan' => 'sometimes|array'
            ]);
            
            if ($validator->fails()) {
                return response()->json(['error' => 'Invalid input'], 400);
            }
            
            // Simulate assignment logic
            $positions = $request->positions;
            $anggotaIds = $request->anggota;
            $algorithm = $request->algorithm;
            
            $preview = [
                'total_positions' => count($positions),
                'total_anggota' => count($anggotaIds),
                'ratio' => count($anggotaIds) / count($positions),
                'estimated_success_rate' => min(95, (count($anggotaIds) / count($positions)) * 80),
                'conflicts' => [],
                'recommendations' => []
            ];
            
            if ($preview['ratio'] < 1) {
                $preview['conflicts'][] = 'Jumlah anggota kurang dari posisi yang dibutuhkan';
                $preview['recommendations'][] = 'Tambah anggota atau kurangi posisi';
            }
            
            if ($preview['ratio'] > 5) {
                $preview['recommendations'][] = 'Pertimbangkan rotasi yang lebih sering';
            }
            
            return response()->json($preview);
        })->name('api.preview-generate');
    });

    // Permission-based access to anggota
    Route::get('/anggota', [AnggotaController::class, 'index'])
        ->name('anggota.index')
        ->middleware(['auth', 'permission:view_anggota']);

    // Laporan routes
    Route::prefix('laporan')->name('laporan.')->middleware(['auth'])->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/kehadiran', [LaporanController::class, 'kehadiran'])->name('kehadiran');
        Route::get('/pelayanan', [LaporanController::class, 'pelayanan'])->name('pelayanan');
        Route::get('/komsel', [LaporanController::class, 'komsel'])->name('komsel');
        Route::get('/anggota', [LaporanController::class, 'anggota'])->name('anggota');
        Route::get('/dashboard', [LaporanController::class, 'dashboard'])->name('dashboard');
        Route::get('/export/{jenis}/{format?}', [LaporanController::class, 'export'])->name('export');
    });

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    
    // Keluarga management
    Route::post('keluarga/{keluarga}/add-member', [KeluargaController::class, 'addMember'])->name('keluarga.add-member');
    Route::delete('keluarga/{keluarga}/remove-member/{anggota}', [KeluargaController::class, 'removeMember'])->name('keluarga.remove-member');
    
    // Kehadiran management
    Route::get('kehadiran/scan/{id?}', [KehadiranController::class, 'scan'])->name('kehadiran.scan');
    Route::get('kehadiran/scan-process/{id}', [KehadiranController::class, 'processQR'])->name('kehadiran.scan-process');
    Route::get('kehadiran/laporan', [KehadiranController::class, 'laporan'])->name('kehadiran.laporan');
    Route::post('kehadiran/laporan/generate', [KehadiranController::class, 'generateLaporan'])->name('kehadiran.laporan.generate');
    
    // Komsel management
    Route::post('komsel/{komsel}/pertemuan', [KomselController::class, 'tambahPertemuan'])->name('komsel.tambah-pertemuan');
    Route::get('komsel/{komsel}/jadwalkan', [KomselController::class, 'jadwalkanPertemuan'])->name('komsel.jadwalkan');
    Route::get('komsel/absensi/{pelaksanaan}', [KomselController::class, 'absensi'])->name('komsel.absensi');
    Route::post('komsel/absensi/{pelaksanaan}', [KomselController::class, 'storeAbsensi'])->name('komsel.store-absensi');
    
    // Notifikasi
    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('notifikasi/send-pelayanan', [NotifikasiController::class, 'sendPelayananReminders'])->name('notifikasi.send-pelayanan');
    Route::get('notifikasi/send-komsel', [NotifikasiController::class, 'sendKomselReminders'])->name('notifikasi.send-komsel');
    Route::get('notifikasi/send-ibadah', [NotifikasiController::class, 'sendIbadahReminders'])->name('notifikasi.send-ibadah');
});