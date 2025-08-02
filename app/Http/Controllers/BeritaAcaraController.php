<?php

namespace App\Http\Controllers;

use App\Models\BeritaAcara;
use App\Http\Requests\StoreBeritaAcaraRequest;
use App\Http\Requests\UpdateBeritaAcaraRequest;
use App\Models\Kegiatan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BeritaAcaraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Menyimpan laporan akhir (Berita Acara) dan menyelesaikan kegiatan.
     *
     * @param  \App\Http\Requests\StoreBeritaAcaraRequest  $request
     * @param  \App\Models\Kegiatan  $kegiatan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreBeritaAcaraRequest $request, Kegiatan $kegiatan)
    {
        // Otorisasi untuk memastikan pengguna adalah bagian dari tim
        $this->authorize('update', $kegiatan);

        // Validasi tahapan
        if ($kegiatan->tahapan !== 'penyelesaian') {
            return back()->with('error', 'Tidak dapat menyelesaikan kegiatan pada tahapan ini.');
        }

        // Data yang divalidasi datang dari StoreBeritaAcaraRequest
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // Simpan file Berita Acara
            $path = $request->file('file_berita_acara')->store('berita_acara', 'public');

            // Buat record Berita Acara
            BeritaAcara::create([
                'kegiatan_id' => $kegiatan->id,
                'path' => $path,
                'user_id' => Auth::id(),
            ]);

            // Update status akhir dan tahapan kegiatan
            $kegiatan->update([
                'tahapan' => 'selesai',
                'status_akhir' => $validated['status_akhir'],
                'detail_akhir_kegiatan' => $validated['detail_akhir_kegiatan'],
                'tanggal_penyelesaian' => now(),
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menyelesaikan kegiatan: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data penyelesaian.');
        }

        return redirect()->route('kegiatan.myIndex', ['tahapan' => 'selesai'])->with('success', 'Kegiatan berhasil diselesaikan.');
    }

    // ... sisa fungsi lainnya
}
