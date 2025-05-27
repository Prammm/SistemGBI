<?php

namespace App\Http\Controllers;

use App\Models\HubunganKeluarga;
use App\Models\Anggota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HubunganKeluargaController extends Controller
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
        $hubungan = HubunganKeluarga::with(['anggota', 'anggotaTujuan'])->get();
        return view('hubungan.index', compact('hubungan'));
    }

    public function create()
    {
        $anggota = Anggota::all();
        return view('hubungan.create', compact('anggota'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hubungan' => 'required|string|max:255',
            'id_anggota' => 'required|exists:anggota,id_anggota',
            'id_anggota_tujuan' => 'required|exists:anggota,id_anggota|different:id_anggota',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        HubunganKeluarga::create([
            'hubungan' => $request->hubungan,
            'id_anggota' => $request->id_anggota,
            'id_anggota_tujuan' => $request->id_anggota_tujuan,
        ]);

        return redirect()->route('hubungan.index')
            ->with('success', 'Hubungan keluarga berhasil dibuat.');
    }

    public function show(HubunganKeluarga $hubungan)
    {
        $hubungan->load(['anggota', 'anggotaTujuan']);
        return view('hubungan.show', compact('hubungan'));
    }

    public function edit(HubunganKeluarga $hubungan)
    {
        $anggota = Anggota::all();
        return view('hubungan.edit', compact('hubungan', 'anggota'));
    }

    public function update(Request $request, HubunganKeluarga $hubungan)
    {
        $validator = Validator::make($request->all(), [
            'hubungan' => 'required|string|max:255',
            'id_anggota' => 'required|exists:anggota,id_anggota',
            'id_anggota_tujuan' => 'required|exists:anggota,id_anggota|different:id_anggota',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $hubungan->update([
            'hubungan' => $request->hubungan,
            'id_anggota' => $request->id_anggota,
            'id_anggota_tujuan' => $request->id_anggota_tujuan,
        ]);

        return redirect()->route('hubungan.index')
            ->with('success', 'Hubungan keluarga berhasil diperbarui.');
    }

    public function destroy(HubunganKeluarga $hubungan)
    {
        $hubungan->delete();
        
        return redirect()->route('hubungan.index')
            ->with('success', 'Hubungan keluarga berhasil dihapus.');
    }
}