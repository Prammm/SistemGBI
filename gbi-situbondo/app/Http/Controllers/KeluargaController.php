<?php

namespace App\Http\Controllers;

use App\Models\Keluarga;
use App\Models\Anggota;
use App\Models\HubunganKeluarga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KeluargaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_keluarga')->only(['index', 'show']);
        $this->middleware('permission:create_keluarga')->only(['create', 'store']);
        $this->middleware('permission:edit_keluarga')->only(['edit', 'update']);
        $this->middleware('permission:delete_keluarga')->only('destroy');
    }

    public function index()
    {
        $keluarga = Keluarga::withCount('anggota')->get();
        return view('keluarga.index', compact('keluarga'));
    }

    public function create()
    {
        return view('keluarga.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_keluarga' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $keluarga = Keluarga::create([
            'nama_keluarga' => $request->nama_keluarga,
        ]);

        return redirect()->route('keluarga.index')
            ->with('success', 'Keluarga berhasil dibuat.');
    }

    public function show(Keluarga $keluarga)
    {
        $anggota = Anggota::where('id_keluarga', $keluarga->id_keluarga)->get();
        $hubungan = HubunganKeluarga::whereIn('id_anggota', $anggota->pluck('id_anggota'))
            ->orWhereIn('id_anggota_tujuan', $anggota->pluck('id_anggota'))
            ->with(['anggota', 'anggotaTujuan'])
            ->get();
            
        return view('keluarga.show', compact('keluarga', 'anggota', 'hubungan'));
    }

    public function edit(Keluarga $keluarga)
    {
        return view('keluarga.edit', compact('keluarga'));
    }

    public function update(Request $request, Keluarga $keluarga)
    {
        $validator = Validator::make($request->all(), [
            'nama_keluarga' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $keluarga->update([
            'nama_keluarga' => $request->nama_keluarga,
        ]);

        return redirect()->route('keluarga.index')
            ->with('success', 'Keluarga berhasil diperbarui.');
    }

    public function destroy(Keluarga $keluarga)
    {
        // Check if keluarga has members
        $memberCount = Anggota::where('id_keluarga', $keluarga->id_keluarga)->count();
        
        if ($memberCount > 0) {
            return redirect()->route('keluarga.index')
                ->with('error', 'Tidak dapat menghapus keluarga karena masih memiliki ' . $memberCount . ' anggota.');
        }

        $keluarga->delete();
        
        return redirect()->route('keluarga.index')
            ->with('success', 'Keluarga berhasil dihapus.');
    }
    
    public function addMember(Request $request, Keluarga $keluarga)
    {
        $validator = Validator::make($request->all(), [
            'id_anggota' => 'required|exists:anggota,id_anggota',
            'hubungan' => 'required|string|max:255',
            'id_anggota_tujuan' => 'nullable|exists:anggota,id_anggota|different:id_anggota',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Update anggota's keluarga
            $anggota = Anggota::findOrFail($request->id_anggota);
            $anggota->id_keluarga = $keluarga->id_keluarga;
            $anggota->save();
            
            // Create family relationship if target is specified
            if ($request->filled('id_anggota_tujuan')) {
                HubunganKeluarga::create([
                    'hubungan' => $request->hubungan,
                    'id_anggota' => $request->id_anggota,
                    'id_anggota_tujuan' => $request->id_anggota_tujuan,
                ]);
            }
            
            DB::commit();
            return redirect()->route('keluarga.show', $keluarga->id_keluarga)
                ->with('success', 'Anggota berhasil ditambahkan ke keluarga.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menambahkan anggota ke keluarga: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function removeMember(Keluarga $keluarga, Anggota $anggota)
    {
        DB::beginTransaction();

        try {
            // Remove family relationships
            HubunganKeluarga::where('id_anggota', $anggota->id_anggota)
                ->orWhere('id_anggota_tujuan', $anggota->id_anggota)
                ->delete();
            
            // Remove from keluarga
            $anggota->id_keluarga = null;
            $anggota->save();
            
            DB::commit();
            return redirect()->route('keluarga.show', $keluarga->id_keluarga)
                ->with('success', 'Anggota berhasil dihapus dari keluarga.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus anggota dari keluarga: ' . $e->getMessage());
        }
    }
}