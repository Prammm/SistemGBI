<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\JadwalPelayanan;
use App\Models\PelaksanaanKegiatan;
use App\Models\Komsel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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
        
        // For member, show their own notifications
        if ($user->id_anggota) {
            // Get upcoming services
            $pelayanan = JadwalPelayanan::with(['kegiatan'])
                ->where('id_anggota', $user->id_anggota)
                ->where('tanggal_pelayanan', '>=', Carbon::now()->format('Y-m-d'))
                ->orderBy('tanggal_pelayanan')
                ->get();
                
            foreach ($pelayanan as $p) {
                $notifications[] = [
                    'type' => 'pelayanan',
                    'title' => 'Jadwal Pelayanan: ' . $p->kegiatan->nama_kegiatan,
                    'description' => 'Anda dijadwalkan untuk melayani sebagai ' . $p->posisi . ' pada ' . Carbon::parse($p->tanggal_pelayanan)->format('d/m/Y'),
                    'date' => $p->tanggal_pelayanan,
                    'status' => $p->status_konfirmasi,
                    'id' => $p->id_pelayanan,
                    'url' => route('pelayanan.index'),
                ];
            }
            
            // Get upcoming komsel meetings
            $anggota = Anggota::find($user->id_anggota);
            
            if ($anggota) {
                // Get user's komsel
                $komselIds = $anggota->komsel->pluck('id_komsel')->toArray();
                
                if (!empty($komselIds)) {
                    $komselKegiatan = [];
                    
                    foreach ($komselIds as $komselId) {
                        $komsel = Komsel::find($komselId);
                        $kegiatan = \App\Models\Kegiatan::where('nama_kegiatan', 'Komsel - ' . $komsel->nama_komsel)
                            ->where('tipe_kegiatan', 'komsel')
                            ->first();
                            
                        if ($kegiatan) {
                            $komselKegiatan[] = $kegiatan->id_kegiatan;
                        }
                    }
                    
                    if (!empty($komselKegiatan)) {
                        $pertemuan = PelaksanaanKegiatan::with('kegiatan')
                            ->whereIn('id_kegiatan', $komselKegiatan)
                            ->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'))
                            ->orderBy('tanggal_kegiatan')
                            ->get();
                            
                        foreach ($pertemuan as $p) {
                            $notifications[] = [
                                'type' => 'komsel',
                                'title' => $p->kegiatan->nama_kegiatan,
                                'description' => 'Pertemuan komsel pada ' . Carbon::parse($p->tanggal_kegiatan)->format('d/m/Y') . ' pukul ' . Carbon::parse($p->jam_mulai)->format('H:i'),
                                'date' => $p->tanggal_kegiatan,
                                'id' => $p->id_pelaksanaan,
                                'url' => route('komsel.index'),
                            ];
                        }
                    }
                }
            }
            
            // Get upcoming services for all members
            $ibadah = PelaksanaanKegiatan::with('kegiatan')
                ->whereHas('kegiatan', function($query) {
                    $query->where('tipe_kegiatan', 'ibadah');
                })
                ->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'))
                ->orderBy('tanggal_kegiatan')
                ->limit(5)
                ->get();
                
            foreach ($ibadah as $i) {
                $notifications[] = [
                    'type' => 'ibadah',
                    'title' => $i->kegiatan->nama_kegiatan,
                    'description' => 'Ibadah pada ' . Carbon::parse($i->tanggal_kegiatan)->format('d/m/Y') . ' pukul ' . Carbon::parse($i->jam_mulai)->format('H:i'),
                    'date' => $i->tanggal_kegiatan,
                    'id' => $i->id_pelaksanaan,
                    'url' => route('pelaksanaan.show', $i->id_pelaksanaan),
                ];
            }
        }
        // For admin and pengurus, show all notifications
        else if ($user->id_role <= 3) {
            // Get all upcoming services
            $pelayanan = JadwalPelayanan::with(['kegiatan', 'anggota'])
                ->where('tanggal_pelayanan', '>=', Carbon::now()->format('Y-m-d'))
                ->where('tanggal_pelayanan', '<=', Carbon::now()->addDays(7)->format('Y-m-d'))
                ->orderBy('tanggal_pelayanan')
                ->get()
                ->groupBy('tanggal_pelayanan');
                
            foreach ($pelayanan as $tanggal => $jadwalList) {
                $petugas = [];
                
                foreach ($jadwalList as $jadwal) {
                    $status = '';
                    
                    switch ($jadwal->status_konfirmasi) {
                        case 'belum':
                            $status = '⚠️ Belum Konfirmasi';
                            break;
                        case 'terima':
                            $status = '✅ Diterima';
                            break;
                        case 'tolak':
                            $status = '❌ Ditolak';
                            break;
                    }
                    
                    $petugas[] = $jadwal->anggota->nama . ' (' . $jadwal->posisi . ') - ' . $status;
                }
                
                $notifications[] = [
                    'type' => 'pelayanan',
                    'title' => 'Jadwal Pelayanan: ' . $jadwalList->first()->kegiatan->nama_kegiatan,
                    'description' => 'Tanggal: ' . Carbon::parse($tanggal)->format('d/m/Y') . "\n" . implode("\n", $petugas),
                    'date' => $tanggal,
                    'id' => $jadwalList->first()->id_pelayanan,
                    'url' => route('pelayanan.index'),
                ];
            }
            
            // Get all upcoming ibadah
            $ibadah = PelaksanaanKegiatan::with('kegiatan')
                ->whereHas('kegiatan', function($query) {
                    $query->where('tipe_kegiatan', 'ibadah');
                })
                ->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'))
                ->orderBy('tanggal_kegiatan')
                ->limit(5)
                ->get();
                
            foreach ($ibadah as $i) {
                $notifications[] = [
                    'type' => 'ibadah',
                    'title' => $i->kegiatan->nama_kegiatan,
                    'description' => 'Ibadah pada ' . Carbon::parse($i->tanggal_kegiatan)->format('d/m/Y') . ' pukul ' . Carbon::parse($i->jam_mulai)->format('H:i'),
                    'date' => $i->tanggal_kegiatan,
                    'id' => $i->id_pelaksanaan,
                    'url' => route('pelaksanaan.show', $i->id_pelaksanaan),
                ];
            }
            
            // Get all upcoming komsel
            $komsel = PelaksanaanKegiatan::with('kegiatan')
                ->whereHas('kegiatan', function($query) {
                    $query->where('tipe_kegiatan', 'komsel');
                })
                ->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'))
                ->orderBy('tanggal_kegiatan')
                ->limit(5)
                ->get();
                
            foreach ($komsel as $k) {
                $notifications[] = [
                    'type' => 'komsel',
                    'title' => $k->kegiatan->nama_kegiatan,
                    'description' => 'Pertemuan komsel pada ' . Carbon::parse($k->tanggal_kegiatan)->format('d/m/Y') . ' pukul ' . Carbon::parse($k->jam_mulai)->format('H:i'),
                    'date' => $k->tanggal_kegiatan,
                    'id' => $k->id_pelaksanaan,
                    'url' => route('komsel.index'),
                ];
            }
        }
        
        // Sort notifications by date
        usort($notifications, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });
        
        return view('notifikasi.index', compact('notifications'));
    }
    
    public function sendPelayananReminders()
    {
        // Only admin and pengurus can send reminders
        if (Auth::user()->id_role > 2) {
            return redirect()->route('notifikasi.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengirim pengingat.');
        }
        
        // Get pelayanan for tomorrow
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $pelayanan = JadwalPelayanan::with(['kegiatan', 'anggota'])
            ->where('tanggal_pelayanan', $tomorrow)
            ->get();
            
        $sent = 0;
        
        foreach ($pelayanan as $p) {
            if (!$p->anggota || !$p->anggota->email) {
                continue;
            }
            
            try {
                Mail::to($p->anggota->email)->send(new PelayananReminder($p));
                $sent++;
            } catch (\Exception $e) {
                \Log::error('Gagal mengirim email pengingat pelayanan: ' . $e->getMessage());
            }
        }
        
        return redirect()->route('notifikasi.index')
            ->with('success', 'Berhasil mengirim ' . $sent . ' pengingat pelayanan untuk besok.');
    }
    
    public function sendKomselReminders()
    {
        // Only admin and pengurus can send reminders
        if (Auth::user()->id_role > 2) {
            return redirect()->route('notifikasi.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengirim pengingat.');
        }
        
        // Get komsel meetings for tomorrow
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $pertemuan = PelaksanaanKegiatan::with('kegiatan')
            ->whereHas('kegiatan', function($query) {
                $query->where('tipe_kegiatan', 'komsel');
            })
            ->where('tanggal_kegiatan', $tomorrow)
            ->get();
            
        $sent = 0;
        
        foreach ($pertemuan as $p) {
            // Extract komsel name from kegiatan name
            $komselName = str_replace('Komsel - ', '', $p->kegiatan->nama_kegiatan);
            
            // Get komsel
            $komsel = Komsel::where('nama_komsel', $komselName)->first();
            
            if (!$komsel) {
                continue;
            }
            
            // Get all komsel members
            $anggota = $komsel->anggota;
            
            foreach ($anggota as $a) {
                if (!$a->email) {
                    continue;
                }
                
                try {
                    Mail::to($a->email)->send(new KomselReminder($p, $komsel));
                    $sent++;
                } catch (\Exception $e) {
                    \Log::error('Gagal mengirim email pengingat komsel: ' . $e->getMessage());
                }
            }
        }
        
        return redirect()->route('notifikasi.index')
            ->with('success', 'Berhasil mengirim ' . $sent . ' pengingat pertemuan komsel untuk besok.');
    }
    
    public function sendIbadahReminders()
    {
        // Only admin and pengurus can send reminders
        if (Auth::user()->id_role > 2) {
            return redirect()->route('notifikasi.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengirim pengingat.');
        }
        
        // Get ibadah for tomorrow
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $ibadah = PelaksanaanKegiatan::with('kegiatan')
            ->whereHas('kegiatan', function($query) {
                $query->where('tipe_kegiatan', 'ibadah');
            })
            ->where('tanggal_kegiatan', $tomorrow)
            ->get();
            
        $sent = 0;
        
        // Get all anggota with email
        $anggota = Anggota::whereNotNull('email')->get();
        
        foreach ($ibadah as $i) {
            foreach ($anggota as $a) {
                try {
                    Mail::to($a->email)->send(new IbadahReminder($i));
                    $sent++;
                } catch (\Exception $e) {
                    \Log::error('Gagal mengirim email pengingat ibadah: ' . $e->getMessage());
                }
            }
        }
        
        return redirect()->route('notifikasi.index')
            ->with('success', 'Berhasil mengirim ' . $sent . ' pengingat ibadah untuk besok.');
    }
}