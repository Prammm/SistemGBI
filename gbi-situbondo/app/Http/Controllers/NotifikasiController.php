<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\JadwalPelayanan;
use App\Models\PelaksanaanKegiatan;
use App\Models\Komsel;
use App\Models\User;
use App\Jobs\ProcessAbsenceNotifications;
use App\Jobs\SendDailyReminders;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use App\Mail\PelayananReminder;
use App\Mail\KomselReminder;
use App\Mail\IbadahReminder;

class NotifikasiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $user = Auth::user();
        $notifications = [];
        
        // Get notifications based on user role
        if ($user->id_anggota) {
            // Personal notifications for members
            $notifications = array_merge($notifications, $this->getPersonalNotifications($user));
        }
        
        if ($user->id_role <= 3) {
            // Administrative notifications for staff
            $notifications = array_merge($notifications, $this->getAdministrativeNotifications($user));
        }
        
        // Sort notifications by date and priority
        usort($notifications, function($a, $b) {
            // FIX: Pastikan $a['date'] dan $b['date'] dalam format yang benar
            try {
                // Coba parse dengan berbagai format yang mungkin
                $dateA = $this->parseDate($a['date']);
                $dateB = $this->parseDate($b['date']);
                
                // Prioritize by urgency then by date
                if ($a['priority'] !== $b['priority']) {
                    return $b['priority'] - $a['priority']; // Higher priority first
                }
                
                return $dateA->diffInDays(Carbon::now()) - $dateB->diffInDays(Carbon::now());
            } catch (\Exception $e) {
                // Jika ada error parsing, fallback ke priority saja
                return $b['priority'] - $a['priority'];
            }
        });
        
        // Get notification statistics
        $stats = $this->getNotificationStats($user);
        
        return view('notifikasi.index', compact('notifications', 'stats'));
    }
    
    /**
     * Helper method untuk parse tanggal dengan berbagai format
     */
    private function parseDate($date)
    {
        if ($date instanceof Carbon) {
            return $date;
        }
        
        if (is_string($date)) {
            // Try different date formats
            $formats = [
                'Y-m-d',           // 2025-06-15
                'd/m/Y',           // 15/06/2025
                'm/d/Y',           // 06/15/2025
                'd-m-Y',           // 15-06-2025
                'Y-m-d H:i:s',     // 2025-06-15 10:30:00
            ];
            
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $date);
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            // Fallback: try Carbon parse
            try {
                return Carbon::parse($date);
            } catch (\Exception $e) {
                // Last resort: return current date
                return Carbon::now();
            }
        }
        
        return Carbon::now(); // Default fallback
    }
    
    private function getPersonalNotifications($user)
    {
        $notifications = [];
        $anggota = $user->anggota;
        
        if (!$anggota) return $notifications;
        
        // Upcoming service assignments
        $upcomingServices = JadwalPelayanan::with(['pelaksanaan.kegiatan'])
            ->where('id_anggota', $anggota->id_anggota)
            ->where('tanggal_pelayanan', '>=', Carbon::now()->format('Y-m-d'))
            ->where('tanggal_pelayanan', '<=', Carbon::now()->addDays(7)->format('Y-m-d'))
            ->orderBy('tanggal_pelayanan')
            ->get();
            
        foreach ($upcomingServices as $service) {
            $daysDiff = Carbon::now()->diffInDays(Carbon::parse($service->tanggal_pelayanan), false);
            $urgency = $daysDiff <= 1 ? 'high' : ($daysDiff <= 3 ? 'medium' : 'low');
            
            $notifications[] = [
                'type' => 'pelayanan',
                'priority' => $urgency === 'high' ? 3 : ($urgency === 'medium' ? 2 : 1),
                'title' => 'Jadwal Pelayanan: ' . ($service->pelaksanaan->kegiatan->nama_kegiatan ?? 'Kegiatan'),
                'description' => "Anda dijadwalkan sebagai {$service->posisi} pada " . 
                               Carbon::parse($service->tanggal_pelayanan)->format('d/m/Y'),
                'date' => $service->tanggal_pelayanan, // FIX: Gunakan format database (Y-m-d)
                'status' => $service->status_konfirmasi,
                'urgency' => $urgency,
                'id' => $service->id_pelayanan,
                'url' => route('pelayanan.index'),
                'actions' => [
                    'confirm' => route('pelayanan.konfirmasi', ['id' => $service->id_pelayanan, 'status' => 'terima']),
                    'reject' => route('pelayanan.konfirmasi', ['id' => $service->id_pelayanan, 'status' => 'tolak'])
                ]
            ];
        }
        
        // Upcoming komsel meetings
        $komselMeetings = $this->getUpcomingKomselMeetings($anggota);
        foreach ($komselMeetings as $meeting) {
            $daysDiff = Carbon::now()->diffInDays(Carbon::parse($meeting->tanggal_kegiatan), false);
            
            $notifications[] = [
                'type' => 'komsel',
                'priority' => $daysDiff <= 1 ? 2 : 1,
                'title' => $meeting->kegiatan->nama_kegiatan,
                'description' => 'Pertemuan komsel pada ' . 
                               Carbon::parse($meeting->tanggal_kegiatan)->format('d/m/Y') . 
                               ' pukul ' . Carbon::parse($meeting->jam_mulai)->format('H:i'),
                'date' => $meeting->tanggal_kegiatan, // FIX: Gunakan format database (Y-m-d)
                'urgency' => $daysDiff <= 1 ? 'high' : 'medium',
                'id' => $meeting->id_pelaksanaan,
                'url' => route('komsel.index'),
            ];
        }
        
        return $notifications;
    }
    
    private function getAdministrativeNotifications($user)
    {
        $notifications = [];
        
        // Pending confirmations
        $pendingConfirmations = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
            ->where('status_konfirmasi', 'belum')
            ->whereHas('pelaksanaan', function($q) {
                $q->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'))
                  ->where('tanggal_kegiatan', '<=', Carbon::now()->addDays(7)->format('Y-m-d'));
            })
            ->count();
            
        if ($pendingConfirmations > 0) {
            $notifications[] = [
                'type' => 'admin_alert',
                'priority' => 3,
                'title' => 'Konfirmasi Pelayanan Tertunda',
                'description' => "{$pendingConfirmations} jadwal pelayanan belum dikonfirmasi untuk minggu ini",
                'date' => Carbon::now()->format('Y-m-d'), // FIX: Gunakan format Y-m-d
                'urgency' => 'high',
                'url' => route('pelayanan.index'),
                'count' => $pendingConfirmations
            ];
        }
        
        // Upcoming events without adequate staff
        $understaffedEvents = $this->getUnderstaffedEvents();
        foreach ($understaffedEvents as $event) {
            $notifications[] = [
                'type' => 'staffing_alert',
                'priority' => 2,
                'title' => 'Kekurangan Petugas: ' . $event['name'],
                'description' => "Kegiatan pada {$event['date']} memerlukan {$event['missing']} petugas tambahan",
                'date' => Carbon::createFromFormat('d/m/Y', $event['date'])->format('Y-m-d'), // FIX: Convert ke format Y-m-d
                'urgency' => 'medium',
                'url' => route('pelayanan.create', ['id_pelaksanaan' => $event['id']]),
            ];
        }
        
        return $notifications;
    }
    
    private function getUpcomingKomselMeetings($anggota)
    {
        $komselNames = $anggota->komsel->pluck('nama_komsel')->toArray();
        $komselActivityPatterns = array_map(function($name) {
            return 'Komsel - ' . $name;
        }, $komselNames);
        
        if (empty($komselActivityPatterns)) {
            return collect();
        }
        
        return PelaksanaanKegiatan::with('kegiatan')
            ->whereHas('kegiatan', function($query) use ($komselActivityPatterns) {
                $query->where('tipe_kegiatan', 'komsel')
                    ->whereIn('nama_kegiatan', $komselActivityPatterns);
            })
            ->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'))
            ->where('tanggal_kegiatan', '<=', Carbon::now()->addDays(7)->format('Y-m-d'))
            ->orderBy('tanggal_kegiatan')
            ->get();
    }
    
    private function getUnderstaffedEvents()
    {
        // FIX: Query manual untuk menghindari missing relationship
        $upcomingEvents = PelaksanaanKegiatan::with(['kegiatan'])
            ->whereHas('kegiatan', function($q) {
                $q->where('tipe_kegiatan', 'ibadah');
            })
            ->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'))
            ->where('tanggal_kegiatan', '<=', Carbon::now()->addDays(14)->format('Y-m-d'))
            ->get();
            
        $understaffed = [];
        foreach ($upcomingEvents as $event) {
            // Query jadwal pelayanan secara manual
            $staffCount = JadwalPelayanan::where('id_pelaksanaan', $event->id_pelaksanaan)->count();
            $requiredStaff = 8; // Example minimum staff requirement
            
            if ($staffCount < $requiredStaff) {
                $understaffed[] = [
                    'id' => $event->id_pelaksanaan,
                    'name' => $event->kegiatan->nama_kegiatan,
                    'date' => Carbon::parse($event->tanggal_kegiatan)->format('d/m/Y'), // Keep d/m/Y for display
                    'current' => $staffCount,
                    'required' => $requiredStaff,
                    'missing' => $requiredStaff - $staffCount
                ];
            }
        }
        
        return $understaffed;
    }
    
    private function getNotificationStats($user)
    {
        $stats = [
            'total' => 0,
            'high_priority' => 0,
            'pending_confirmations' => 0,
            'upcoming_services' => 0
        ];
        
        if ($user->id_anggota) {
            $stats['pending_confirmations'] = JadwalPelayanan::where('id_anggota', $user->id_anggota)
                ->where('status_konfirmasi', 'belum')
                ->whereHas('pelaksanaan', function($q) {
                    $q->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'));
                })
                ->count();
                
            $stats['upcoming_services'] = JadwalPelayanan::where('id_anggota', $user->id_anggota)
                ->where('tanggal_pelayanan', '>=', Carbon::now()->format('Y-m-d'))
                ->where('tanggal_pelayanan', '<=', Carbon::now()->addDays(7)->format('Y-m-d'))
                ->count();
        }
        
        if ($user->id_role <= 3) {
            $stats['pending_confirmations'] = JadwalPelayanan::where('status_konfirmasi', 'belum')
                ->whereHas('pelaksanaan', function($q) {
                    $q->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'))
                      ->where('tanggal_kegiatan', '<=', Carbon::now()->addDays(7)->format('Y-m-d'));
                })
                ->count();
        }
        
        $stats['high_priority'] = $stats['pending_confirmations'];
        $stats['total'] = $stats['pending_confirmations'] + $stats['upcoming_services'];
        
        return $stats;
    }
    
    // Enhanced reminder methods with job dispatching
    public function sendPelayananReminders(Request $request)
    {
        if (Auth::user()->id_role > 2) {
            return redirect()->route('notifikasi.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengirim pengingat.');
        }
        
        $when = $request->get('when', 'day_before');
        $date = $request->get('date'); // Optional specific date
        
        try {
            // Calculate target date
            if ($date) {
                $targetDate = Carbon::parse($date);
            } else {
                switch ($when) {
                    case 'week_before':
                        $targetDate = Carbon::now()->addWeek();
                        break;
                    case 'day_of':
                        $targetDate = Carbon::now();
                        break;
                    case 'day_before':
                    default:
                        $targetDate = Carbon::now()->addDay();
                        break;
                }
            }
            
            // Get jadwal pelayanan for target date
            $jadwalPelayanan = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
                ->where('tanggal_pelayanan', $targetDate->format('Y-m-d'))
                ->where('status_konfirmasi', 'belum')
                ->get();
            
            if ($jadwalPelayanan->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada jadwal pelayanan yang perlu dikonfirmasi untuk tanggal ' . $targetDate->format('d/m/Y')
                ]);
            }
            
            $sentCount = 0;
            $failedCount = 0;
            
            foreach ($jadwalPelayanan as $jadwal) {
                if (!$jadwal->anggota || !$jadwal->anggota->email) {
                    $failedCount++;
                    continue;
                }
                
                try {
                    // Send email directly using Mail facade
                    Mail::to($jadwal->anggota->email)
                        ->send(new \App\Mail\PelayananReminder($jadwal, $when));
                    
                    $sentCount++;
                    
                    Log::info("Pelayanan reminder sent successfully", [
                        'jadwal_id' => $jadwal->id_pelayanan,
                        'email' => $jadwal->anggota->email,
                        'anggota' => $jadwal->anggota->nama
                    ]);
                } catch (\Exception $e) {
                    $failedCount++;
                    Log::error("Failed to send pelayanan reminder", [
                        'jadwal_id' => $jadwal->id_pelayanan,
                        'email' => $jadwal->anggota->email,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $message = "Pengingat pelayanan berhasil dikirim ke {$sentCount} anggota";
            if ($failedCount > 0) {
                $message .= ". {$failedCount} email gagal dikirim.";
            }
            
            // Return JSON for AJAX or redirect for form submission
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount
                ]);
            }
            
            return redirect()->route('pelayanan.index')->with('success', $message);
            
        } catch (\Exception $e) {
            Log::error("Failed to send pelayanan reminders", [
                'error' => $e->getMessage(),
                'user' => Auth::user()->email,
                'when' => $when
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengirim pengingat: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('pelayanan.index')
                ->with('error', 'Terjadi kesalahan saat mengirim pengingat pelayanan: ' . $e->getMessage());
        }
    }
    
    public function sendKomselReminders(Request $request)
    {
        if (Auth::user()->id_role > 2) {
            return redirect()->route('notifikasi.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengirim pengingat.');
        }
        
        $when = $request->get('when', 'day_before');
        $date = $request->get('date');
        
        try {
            // Calculate target date
            if ($date) {
                $targetDate = Carbon::parse($date);
            } else {
                switch ($when) {
                    case 'week_before':
                        $targetDate = Carbon::now()->addWeek();
                        break;
                    case 'day_of':
                        $targetDate = Carbon::now();
                        break;
                    case 'day_before':
                    default:
                        $targetDate = Carbon::now()->addDay();
                        break;
                }
            }
            
            // Get komsel events for target date
            $komselEvents = PelaksanaanKegiatan::with(['kegiatan'])
                ->whereHas('kegiatan', function($q) {
                    $q->where('tipe_kegiatan', 'komsel');
                })
                ->where('tanggal_kegiatan', $targetDate->format('Y-m-d'))
                ->get();
            
            if ($komselEvents->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada pertemuan komsel untuk tanggal ' . $targetDate->format('d/m/Y')
                ]);
            }
            
            $sentCount = 0;
            $failedCount = 0;
            
            foreach ($komselEvents as $event) {
                $komselName = str_replace('Komsel - ', '', $event->kegiatan->nama_kegiatan);
                $komsel = \App\Models\Komsel::where('nama_komsel', $komselName)->first();
                
                if (!$komsel) continue;
                
                foreach ($komsel->anggota as $anggota) {
                    if (!$anggota->email) {
                        $failedCount++;
                        continue;
                    }
                    
                    try {
                        Mail::to($anggota->email)
                            ->send(new \App\Mail\KomselReminder($event, $komsel, $anggota, $when));
                        
                        $sentCount++;
                        
                        Log::info("Komsel reminder sent successfully", [
                            'event_id' => $event->id_pelaksanaan,
                            'email' => $anggota->email,
                            'komsel' => $komselName
                        ]);
                    } catch (\Exception $e) {
                        $failedCount++;
                        Log::error("Failed to send komsel reminder", [
                            'event_id' => $event->id_pelaksanaan,
                            'email' => $anggota->email,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            $message = "Pengingat komsel berhasil dikirim ke {$sentCount} anggota";
            if ($failedCount > 0) {
                $message .= ". {$failedCount} email gagal dikirim.";
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount
                ]);
            }
            
            return redirect()->route('notifikasi.index')->with('success', $message);
            
        } catch (\Exception $e) {
            Log::error("Failed to send komsel reminders", [
                'error' => $e->getMessage(),
                'user' => Auth::user()->email
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengirim pengingat komsel: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('notifikasi.index')
                ->with('error', 'Terjadi kesalahan saat mengirim pengingat komsel: ' . $e->getMessage());
        }
    }
    
    public function sendIbadahReminders(Request $request)
    {
        if (Auth::user()->id_role > 2) {
            return redirect()->route('notifikasi.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengirim pengingat.');
        }
        
        $when = $request->get('when', 'day_before');
        $date = $request->get('date');
        
        try {
            // Calculate target date
            if ($date) {
                $targetDate = Carbon::parse($date);
            } else {
                switch ($when) {
                    case 'week_before':
                        $targetDate = Carbon::now()->addWeek();
                        break;
                    case 'day_of':
                        $targetDate = Carbon::now();
                        break;
                    case 'day_before':
                    default:
                        $targetDate = Carbon::now()->addDay();
                        break;
                }
            }
            
            // Get ibadah events for target date
            $ibadahEvents = PelaksanaanKegiatan::with(['kegiatan'])
                ->whereHas('kegiatan', function($q) {
                    $q->where('tipe_kegiatan', 'ibadah');
                })
                ->where('tanggal_kegiatan', $targetDate->format('Y-m-d'))
                ->get();
            
            if ($ibadahEvents->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada ibadah untuk tanggal ' . $targetDate->format('d/m/Y')
                ]);
            }
            
            // Get all anggota with email
            $anggota = \App\Models\Anggota::whereNotNull('email')->get();
            
            if ($anggota->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada anggota dengan email yang terdaftar'
                ]);
            }
            
            $sentCount = 0;
            $failedCount = 0;
            
            foreach ($ibadahEvents as $event) {
                foreach ($anggota as $member) {
                    try {
                        Mail::to($member->email)
                            ->send(new \App\Mail\IbadahReminder($event, $member, $when));
                        
                        $sentCount++;
                        
                        Log::info("Ibadah reminder sent successfully", [
                            'event_id' => $event->id_pelaksanaan,
                            'email' => $member->email
                        ]);
                    } catch (\Exception $e) {
                        $failedCount++;
                        Log::error("Failed to send ibadah reminder", [
                            'event_id' => $event->id_pelaksanaan,
                            'email' => $member->email,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            $message = "Pengingat ibadah berhasil dikirim ke {$sentCount} anggota";
            if ($failedCount > 0) {
                $message .= ". {$failedCount} email gagal dikirim.";
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount
                ]);
            }
            
            return redirect()->route('notifikasi.index')->with('success', $message);
            
        } catch (\Exception $e) {
            Log::error("Failed to send ibadah reminders", [
                'error' => $e->getMessage(),
                'user' => Auth::user()->email
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengirim pengingat ibadah: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('notifikasi.index')
                ->with('error', 'Terjadi kesalahan saat mengirim pengingat ibadah: ' . $e->getMessage());
        }
    }
    
    public function checkAbsences(Request $request)
    {
        if (Auth::user()->id_role > 2) {
            return redirect()->route('notifikasi.index')
                ->with('error', 'Anda tidak memiliki akses untuk memeriksa absensi berturut-turut.');
        }
        
        $days = $request->get('days', 30);
        $threshold = $request->get('threshold', 3);
        
        try {
            ProcessAbsenceNotifications::dispatch($days, $threshold)->onQueue('notifications');
            
            Log::info("Absence check dispatched", [
                'days' => $days,
                'threshold' => $threshold,
                'user' => Auth::user()->email
            ]);
            
            return redirect()->route('notifikasi.index')
                ->with('success', "Pemeriksaan absensi berturut-turut (threshold: {$threshold}) sedang diproses.");
        } catch (\Exception $e) {
            Log::error("Failed to dispatch absence check", [
                'error' => $e->getMessage(),
                'user' => Auth::user()->email
            ]);
            
            return redirect()->route('notifikasi.index')
                ->with('error', 'Terjadi kesalahan saat memproses pemeriksaan absensi.');
        }
    }
    
    public function testEmail(Request $request)
    {
        if (Auth::user()->id_role > 1) {
            return redirect()->route('notifikasi.index')
                ->with('error', 'Hanya admin yang dapat mengirim test email.');
        }
        
        $testType = $request->get('type', 'pelayanan');
        
        try {
            switch ($testType) {
                case 'pelayanan':
                    // Send test pelayanan reminder
                    Artisan::call('notification:send-reminders', [
                        '--type' => 'pelayanan',
                        '--when' => 'day_before',
                        '--dry-run' => true
                    ]);
                    break;
                    
                case 'absence':
                    // Send test absence check
                    Artisan::call('notification:check-absences', [
                        '--days' => 7,
                        '--threshold' => 2,
                        '--dry-run' => true
                    ]);
                    break;
            }
            
            $output = Artisan::output();
            
            return redirect()->route('notifikasi.index')
                ->with('success', 'Test email berhasil dijalankan. Lihat log untuk detail.')
                ->with('info', 'Output: ' . substr($output, 0, 200) . '...');
                
        } catch (\Exception $e) {
            return redirect()->route('notifikasi.index')
                ->with('error', 'Test email gagal: ' . $e->getMessage());
        }
    }
}