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

    //  Pelayanan Routes
    Route::prefix('pelayanan')->name('pelayanan.')->group(function () {
        // Basic CRUD
        Route::get('/', [PelayananController::class, 'index'])->name('index');
        Route::get('/create', [PelayananController::class, 'create'])->name('create');
        Route::post('/store', [PelayananController::class, 'store'])->name('store');
        Route::get('/konfirmasi/{id}/{status}', [PelayananController::class, 'konfirmasi'])->name('konfirmasi');
        Route::delete('/{id}', [PelayananController::class, 'destroy'])->name('destroy');

        // History & Analytics
        Route::get('/history', [PelayananController::class, 'history'])->name('history');
        Route::get('/history/export', [PelayananController::class, 'exportHistory'])->name('history.export');
        Route::get('/history/report', [PelayananController::class, 'generateHistoryReport'])->name('history.report');
        
        // Auto-replacement API endpoints
        Route::get('/api/replacement-candidates/{replacement_id}', [PelayananController::class, 'getReplacementCandidates'])
            ->name('api.replacement-candidates');
        Route::get('/api/schedule-replacement-candidates/{jadwal_id}', [PelayananController::class, 'getScheduleReplacementCandidates'])
            ->name('api.schedule-replacement-candidates');
        Route::post('/api/assign-replacement', [PelayananController::class, 'assignReplacement'])
            ->name('api.assign-replacement');
        Route::post('/api/mark-no-replacement', [PelayananController::class, 'markNoReplacement'])
            ->name('api.mark-no-replacement');
        
        // Schedule details API
        Route::get('/api/schedule-details/{jadwal_id}', [PelayananController::class, 'getScheduleDetails'])
            ->name('api.schedule-details');
        
        // Advanced Generator
        Route::get('/generator', [PelayananController::class, 'showGenerator'])->name('generator');
        Route::post('/generate', [PelayananController::class, 'generateSchedule'])->name('generate');
        Route::get('/api/recurring-events', [PelayananController::class, 'getRecurringEvents'])
            ->name('api.recurring-events');
        
        // Replacement management dashboard
        Route::get('/replacements', [PelayananController::class, 'replacementDashboard'])
            ->name('replacements')
            ->middleware('permission:edit_pelayanan');
        Route::post('/replacements/{id}/resolve', [PelayananController::class, 'resolveReplacement'])
            ->name('replacements.resolve')
            ->middleware('permission:edit_pelayanan');
        
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
        Route::post('/send-notifications', [NotifikasiController::class, 'sendPelayananReminders'])->name('send-notifications');
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

    // UPDATED: Kehadiran Routes - removed personalReport and komselReport
    Route::prefix('kehadiran')->name('kehadiran.')->group(function () {
        // Existing routes
        Route::get('scan/{id?}', [KehadiranController::class, 'scan'])->name('scan');
        Route::get('scan-process/{id}', [KehadiranController::class, 'processQR'])->name('scan-process');
        Route::get('laporan', [KehadiranController::class, 'laporan'])->name('laporan');
        Route::post('laporan/generate', [KehadiranController::class, 'generateLaporan'])->name('laporan.generate');
        
        // QR and Family Attendance Routes
        Route::get('family-attendance/{id_pelaksanaan}', [KehadiranController::class, 'familyAttendance'])->name('family-attendance');
        Route::post('store-family-attendance', [KehadiranController::class, 'storeFamilyAttendance'])->name('store-family-attendance');
    });

    // API Routes for QR Scanner
    Route::prefix('api/kehadiran')->name('api.kehadiran.')->group(function () {
        Route::post('validate-qr', function(\Illuminate\Http\Request $request) {
            $user = \Illuminate\Support\Facades\Auth::user();
            $qrData = $request->input('qr_data');
            
            // Validate QR format
            if (!str_contains($qrData, 'kehadiran/scan-process/')) {
                return response()->json(['valid' => false, 'message' => 'QR Code tidak valid']);
            }
            
            // Extract pelaksanaan ID
            preg_match('/scan-process\/(\d+)/', $qrData, $matches);
            if (!isset($matches[1])) {
                return response()->json(['valid' => false, 'message' => 'Format QR Code salah']);
            }
            
            $pelaksanaanId = $matches[1];
            $pelaksanaan = \App\Models\PelaksanaanKegiatan::find($pelaksanaanId);
            
            if (!$pelaksanaan) {
                return response()->json(['valid' => false, 'message' => 'Kegiatan tidak ditemukan']);
            }
            
            // Check if user can attend this event
            if ($user->id_role > 3) {
                $anggota = $user->anggota;
                if (!$anggota) {
                    return response()->json(['valid' => false, 'message' => 'Profil anggota tidak lengkap']);
                }
                
                // Check if it's a komsel event and user is member
                if ($pelaksanaan->kegiatan && $pelaksanaan->kegiatan->tipe_kegiatan == 'komsel') {
                    $komselName = str_replace('Komsel - ', '', $pelaksanaan->kegiatan->nama_kegiatan);
                    $isMember = $anggota->komsel->contains('nama_komsel', $komselName);
                    
                    if (!$isMember) {
                        return response()->json(['valid' => false, 'message' => 'Anda bukan anggota komsel ini']);
                    }
                }
            }
            
            return response()->json([
                'valid' => true, 
                'redirect_url' => route('kehadiran.scan-process', $pelaksanaanId),
                'event_name' => $pelaksanaan->kegiatan->nama_kegiatan ?? 'Kegiatan'
            ]);
        })->name('validate-qr');
        
        Route::get('user-family/{pelaksanaan_id}', function($pelaksanaanId) {
            $user = \Illuminate\Support\Facades\Auth::user();
            
            if (!$user->id_anggota) {
                return response()->json(['family_members' => []]);
            }
            
            $anggota = \App\Models\Anggota::find($user->id_anggota);
            if (!$anggota || !$anggota->id_keluarga) {
                return response()->json(['family_members' => []]);
            }
            
            $familyMembers = \App\Models\Anggota::where('id_keluarga', $anggota->id_keluarga)
                ->where('id_anggota', '!=', $anggota->id_anggota)
                ->get()
                ->map(function($member) use ($anggota, $pelaksanaanId) {
                    $attended = \App\Models\Kehadiran::where('id_anggota', $member->id_anggota)
                        ->where('id_pelaksanaan', $pelaksanaanId)
                        ->exists();
                    
                    return [
                        'id' => $member->id_anggota,
                        'nama' => $member->nama,
                        'hubungan' => $anggota->getHubunganDengan($member->id_anggota),
                        'attended' => $attended
                    ];
                });
            
            return response()->json(['family_members' => $familyMembers]);
        })->name('user-family');
    });

    // Permission-based access to anggota
    Route::get('/anggota', [AnggotaController::class, 'index'])
        ->name('anggota.index')
        ->middleware(['auth', 'permission:view_anggota']);

    // UPDATED: Role-based Laporan routes with new methods
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        
        // General reports (Admin/Pengurus only)
        Route::get('/kehadiran', [LaporanController::class, 'kehadiran'])
            ->name('kehadiran')
            ->middleware('permission:view_laporan');
        Route::get('/pelayanan', [LaporanController::class, 'pelayanan'])
            ->name('pelayanan')
            ->middleware('permission:view_laporan');
        Route::get('/komsel', [LaporanController::class, 'komsel'])
            ->name('komsel')
            ->middleware('permission:view_laporan');
        Route::get('/anggota', [LaporanController::class, 'anggota'])
            ->name('anggota')
            ->middleware('permission:view_laporan');
        Route::get('/dashboard', [LaporanController::class, 'dashboard'])
            ->name('dashboard')
            ->middleware('permission:view_laporan');
        
        // NEW: Personal reports (moved from KehadiranController)
        Route::get('/personal-report', [LaporanController::class, 'personalReport'])
            ->name('personal-report')
            ->middleware('attendance.access');
        Route::get('/komsel-report', [LaporanController::class, 'komselReport'])
            ->name('komsel-report')
            ->middleware('attendance.access');
        Route::get('/personal-service-report', [LaporanController::class, 'personalServiceReport'])
            ->name('personal-service-report')
            ->middleware('attendance.access');
        
        // Export routes
        Route::get('/export/{jenis}/{format?}', [LaporanController::class, 'export'])->name('export');
    });

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    
    // Keluarga management
    Route::post('keluarga/{keluarga}/add-member', [KeluargaController::class, 'addMember'])->name('keluarga.add-member');
    Route::delete('keluarga/{keluarga}/remove-member/{anggota}', [KeluargaController::class, 'removeMember'])->name('keluarga.remove-member');
    
    // Komsel management
    Route::get('komsel/{komsel}/kehadiran/{pertemuan}', [KomselController::class, 'showKehadiran'])->name('komsel.show-kehadiran'); 
    Route::post('komsel/{komsel}/pertemuan', [KomselController::class, 'tambahPertemuan'])->name('komsel.tambah-pertemuan');
    Route::get('komsel/{komsel}/jadwalkan', [KomselController::class, 'jadwalkanPertemuan'])->name('komsel.jadwalkan');
    Route::get('komsel/absensi/{pelaksanaan}', [KomselController::class, 'absensi'])->name('komsel.absensi');
    Route::post('komsel/absensi/{pelaksanaan}', [KomselController::class, 'storeAbsensi'])->name('komsel.store-absensi');
    
    // Notifikasi
    Route::prefix('notifikasi')->name('notifikasi.')->middleware(['auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\NotifikasiController::class, 'index'])->name('index');
        
        // Manual reminder triggers (Admin/Pengurus only)
        Route::post('/send-pelayanan', [App\Http\Controllers\NotifikasiController::class, 'sendPelayananReminders'])
            ->name('send-pelayanan')
            ->middleware('permission:edit_pelayanan');
            
        Route::post('/send-komsel', [App\Http\Controllers\NotifikasiController::class, 'sendKomselReminders'])
            ->name('send-komsel')
            ->middleware('permission:edit_komsel');
            
        Route::post('/send-ibadah', [App\Http\Controllers\NotifikasiController::class, 'sendIbadahReminders'])
            ->name('send-ibadah')
            ->middleware('permission:edit_kegiatan');
        
        // Absence checking (Admin/Pengurus only)
        Route::post('/check-absences', [App\Http\Controllers\NotifikasiController::class, 'checkAbsences'])
            ->name('check-absences')
            ->middleware('permission:view_laporan');
        
        // Test email functionality (Admin only)
        Route::post('/test-email', [App\Http\Controllers\NotifikasiController::class, 'testEmail'])
            ->name('test-email')
            ->middleware('permission:manage_system');
        
        // API endpoints for AJAX notifications
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/count', function() {
                $user = Auth::user();
                $count = 0;
                
                if ($user->id_anggota) {
                    $count = \App\Models\JadwalPelayanan::where('id_anggota', $user->id_anggota)
                        ->where('status_konfirmasi', 'belum')
                        ->whereHas('pelaksanaan', function($q) {
                            $q->where('tanggal_kegiatan', '>=', Carbon\Carbon::now()->format('Y-m-d'));
                        })
                        ->count();
                }
                
                return response()->json(['count' => $count]);
            })->name('count');
            
            Route::get('/recent', function() {
                $user = Auth::user();
                $notifications = [];
                
                if ($user->id_anggota) {
                    $recent = \App\Models\JadwalPelayanan::with(['pelaksanaan.kegiatan'])
                        ->where('id_anggota', $user->id_anggota)
                        ->where('status_konfirmasi', 'belum')
                        ->whereHas('pelaksanaan', function($q) {
                            $q->where('tanggal_kegiatan', '>=', Carbon\Carbon::now()->format('Y-m-d'))
                            ->where('tanggal_kegiatan', '<=', Carbon\Carbon::now()->addDays(7)->format('Y-m-d'));
                        })
                        ->orderBy('tanggal_pelayanan')
                        ->limit(5)
                        ->get();
                    
                    foreach ($recent as $jadwal) {
                        $notifications[] = [
                            'title' => 'Konfirmasi Pelayanan',
                            'message' => 'Sebagai ' . $jadwal->posisi . ' pada ' . Carbon\Carbon::parse($jadwal->tanggal_pelayanan)->format('d/m/Y'),
                            'url' => route('pelayanan.index'),
                            'time' => Carbon\Carbon::parse($jadwal->tanggal_pelayanan)->diffForHumans(),
                            'type' => 'pelayanan'
                        ];
                    }
                }
                
                return response()->json(['notifications' => $notifications]);
            })->name('recent');
            
            Route::post('/mark-read/{id}', function($id) {
                // Implementation for marking notifications as read
                // This would require a notifications table if you want persistent notification status
                return response()->json(['success' => true]);
            })->name('mark-read');
        });
    });
});

Route::middleware(['auth', 'permission:manage_system'])->group(function () {
    Route::prefix('master/posisi')->name('master.posisi.')->group(function () {
        Route::get('/', [App\Http\Controllers\MasterPosisiController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\MasterPosisiController::class, 'store'])->name('store');
        Route::put('/{id}', [App\Http\Controllers\MasterPosisiController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\MasterPosisiController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [App\Http\Controllers\MasterPosisiController::class, 'toggleStatus'])->name('toggle-status');
    });
});

// Public API for positions (used in forms)
Route::get('/api/master-posisi/positions', [App\Http\Controllers\MasterPosisiController::class, 'getPositions'])
    ->name('api.master-posisi.positions');

// Enhanced Pelayanan API routes
Route::middleware(['auth'])->prefix('pelayanan/api')->name('pelayanan.api.')->group(function () {
    // Change assignee endpoints (Petugas+ only)
    Route::middleware(['permission:edit_pelayanan'])->group(function () {
        Route::get('/find-replacement-for-change/{jadwal_id}', [App\Http\Controllers\PelayananController::class, 'findReplacementForChange'])
            ->name('find-replacement-for-change');
        Route::post('/change-assignee', [App\Http\Controllers\PelayananController::class, 'changeAssignee'])
            ->name('change-assignee');
    });
    
    // Auto-reject endpoint (Admin only)
    Route::middleware(['permission:manage_system'])->group(function () {
        Route::post('/auto-reject-expired', [App\Http\Controllers\PelayananController::class, 'autoRejectExpiredSchedules'])
            ->name('auto-reject-expired');
        Route::post('/update-schedule/{id}', [PelayananController::class, 'updateSchedule'])
            ->name('api.update-schedule');
    });
});

// Helper function - MOVED OUTSIDE routes
if (!function_exists('canAccessFeature')) {
    function canAccessFeature($feature, $user = null) {
        $user = $user ?: \Illuminate\Support\Facades\Auth::user();
        
        if (!$user) return false;
        
        switch ($feature) {
            case 'manual_attendance':
                return $user->id_role <= 3; // Admin, Pengurus, Petugas Pelayanan
                
            case 'view_all_members':
                return $user->id_role <= 3;
                
            case 'personal_report':
                return $user->id_anggota !== null;
                
            case 'komsel_report':
                if (!$user->id_anggota) return false;
                return \App\Models\Komsel::where('id_pemimpin', $user->id_anggota)->exists();
                
            case 'family_attendance':
                if (!$user->id_anggota) return false;
                $anggota = \App\Models\Anggota::find($user->id_anggota);
                return $anggota && $anggota->id_keluarga && 
                    \App\Models\Anggota::where('id_keluarga', $anggota->id_keluarga)
                        ->where('id_anggota', '!=', $anggota->id_anggota)
                        ->exists();
                
            default:
                return false;
        }
    }
}