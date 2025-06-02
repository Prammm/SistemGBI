<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController; // Add this line
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
    
    // Profile routes - Add these lines
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/anggota', [ProfileController::class, 'updateAnggota'])->name('profile.update.anggota');
    
    Route::resource('anggota', AnggotaController::class)->parameters([
        'anggota' => 'anggota'
    ]);
    Route::resource('keluarga', KeluargaController::class);
    Route::resource('hubungan', HubunganKeluargaController::class);
    
    // IMPORTANT: Custom kegiatan routes MUST come BEFORE the resource route
    Route::get('kegiatan/calendar', [KegiatanController::class, 'calendar'])->name('kegiatan.calendar');
    Route::get('kegiatan/events', [KegiatanController::class, 'getEvents'])->name('kegiatan.events');
    // Now the resource route
    Route::resource('kegiatan', KegiatanController::class);
    
    Route::resource('pelaksanaan', PelaksanaanKegiatanController::class);
    Route::delete('pelaksanaan/{pelaksanaan}/destroy-series', [PelaksanaanKegiatanController::class, 'destroyRecurringSeries'])
        ->name('pelaksanaan.destroy-series');
    
    // Optional: Route to view all schedules in a recurring series
    Route::get('pelaksanaan/{pelaksanaan}/series', [PelaksanaanKegiatanController::class, 'showRecurringSeries'])
        ->name('pelaksanaan.series');
    Route::resource('kehadiran', KehadiranController::class);
    Route::resource('komsel', KomselController::class);
    Route::resource('pelayanan', PelayananController::class);

    Route::get('/anggota', [AnggotaController::class, 'index'])
        ->name('anggota.index')
        ->middleware(['auth', 'permission:view_anggota']);


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
    Route::post('keluarga/{keluarga}/add-member', [KeluargaController::class, 'addMember'])->name('keluarga.add-member');
    Route::delete('keluarga/{keluarga}/remove-member/{anggota}', [KeluargaController::class, 'removeMember'])->name('keluarga.remove-member');
    Route::get('kehadiran/scan/{id?}', [KehadiranController::class, 'scan'])->name('kehadiran.scan');
    Route::get('kehadiran/scan-process/{id}', [KehadiranController::class, 'processQR'])->name('kehadiran.scan-process');
    Route::get('kehadiran/laporan', [KehadiranController::class, 'laporan'])->name('kehadiran.laporan');
    Route::post('kehadiran/laporan/generate', [KehadiranController::class, 'generateLaporan'])->name('kehadiran.laporan.generate');
    Route::post('komsel/{komsel}/pertemuan', [KomselController::class, 'tambahPertemuan'])->name('komsel.tambah-pertemuan');
    Route::get('komsel/{komsel}/jadwalkan', [KomselController::class, 'jadwalkanPertemuan'])->name('komsel.jadwalkan');
    Route::get('komsel/absensi/{pelaksanaan}', [KomselController::class, 'absensi'])->name('komsel.absensi');
    Route::post('komsel/absensi/{pelaksanaan}', [KomselController::class, 'storeAbsensi'])->name('komsel.store-absensi');
    Route::get('pelayanan', [PelayananController::class, 'index'])->name('pelayanan.index');
    Route::get('pelayanan/create', [PelayananController::class, 'create'])->name('pelayanan.create');
    Route::post('pelayanan/store', [PelayananController::class, 'store'])->name('pelayanan.store');
    Route::get('pelayanan/konfirmasi/{id}/{status}', [PelayananController::class, 'konfirmasi'])->name('pelayanan.konfirmasi');
    Route::delete('pelayanan/{id}', [PelayananController::class, 'destroy'])->name('pelayanan.destroy');
    Route::get('pelayanan/generator', [PelayananController::class, 'showGenerator'])->name('pelayanan.generator');
    Route::post('pelayanan/generate', [PelayananController::class, 'generateSchedule'])->name('pelayanan.generate');
    Route::get('notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::get('notifikasi/send-pelayanan', [NotifikasiController::class, 'sendPelayananReminders'])->name('notifikasi.send-pelayanan');
    Route::get('notifikasi/send-komsel', [NotifikasiController::class, 'sendKomselReminders'])->name('notifikasi.send-komsel');
    Route::get('notifikasi/send-ibadah', [NotifikasiController::class, 'sendIbadahReminders'])->name('notifikasi.send-ibadah');
});