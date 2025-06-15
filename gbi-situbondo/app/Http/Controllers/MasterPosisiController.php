<?php

namespace App\Http\Controllers;

use App\Models\MasterPosisiPelayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MasterPosisiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:manage_system')->except(['index', 'getPositions']);
    }
    
    public function index()
    {
        $positions = MasterPosisiPelayanan::orderBy('kategori')->orderBy('urutan')->get();
        $categories = MasterPosisiPelayanan::distinct('kategori')->pluck('kategori');
        
        return view('master.posisi.index', compact('positions', 'categories'));
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_posisi' => 'required|string|max:255|unique:master_posisi_pelayanan,nama_posisi',
            'kategori' => 'required|string|max:255',
            'urutan' => 'required|integer|min:0',
            'workload_score' => 'required|integer|min:1|max:10',
            'deskripsi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $position = MasterPosisiPelayanan::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Posisi berhasil ditambahkan',
                'data' => $position
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        $position = MasterPosisiPelayanan::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'nama_posisi' => 'required|string|max:255|unique:master_posisi_pelayanan,nama_posisi,' . $id . ',id_posisi',
            'kategori' => 'required|string|max:255',
            'urutan' => 'required|integer|min:0',
            'workload_score' => 'required|integer|min:1|max:10',
            'deskripsi' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $position->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Posisi berhasil diupdate',
                'data' => $position
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $position = MasterPosisiPelayanan::findOrFail($id);
            
            // Check if position is being used
            $isUsed = $position->spesialisasi()->exists() || $position->jadwalPelayanan()->exists();
            
            if ($isUsed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Posisi tidak dapat dihapus karena sedang digunakan. Nonaktifkan saja.'
                ], 422);
            }
            
            $position->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Posisi berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get positions for AJAX calls
     */
    public function getPositions()
    {
        $positions = MasterPosisiPelayanan::getActivePositionsByCategory();
        
        return response()->json([
            'success' => true,
            'data' => $positions
        ]);
    }
    
    /**
     * Toggle active status
     */
    public function toggleStatus($id)
    {
        try {
            $position = MasterPosisiPelayanan::findOrFail($id);
            $position->is_active = !$position->is_active;
            $position->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diubah',
                'is_active' => $position->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}