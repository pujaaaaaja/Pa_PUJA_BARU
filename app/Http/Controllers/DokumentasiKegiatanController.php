<?php

namespace App\Http\Controllers;

use App\Models\DokumentasiKegiatan;
use App\Http\Requests\StoreDokumentasiKegiatanRequest;
use App\Models\Kegiatan;
use App\Models\Foto;
use App\Models\Kontrak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DokumentasiKegiatanController extends Controller
{
    /**
     * Mengonfirmasi kehadiran pegawai dan melanjutkan tahapan kegiatan.
     *
     * @param  \App\Models\Kegiatan  $kegiatan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirmKehadiran(Kegiatan $kegiatan)
    {
        // Otorisasi: Pastikan pegawai adalah bagian dari tim kegiatan ini
        $user = Auth::user();
        if (!$kegiatan->tim->pegawai->contains($user->id)) {
            return back()->with('error', 'Anda tidak memiliki akses untuk kegiatan ini.');
        }

        // Validasi: Pastikan tahapan saat ini adalah 'perjalanan_dinas'
        if ($kegiatan->tahapan !== 'perjalanan_dinas') {
            return back()->with('error', 'Aksi tidak dapat dilakukan pada tahapan ini.');
        }

        // Update tahapan kegiatan
        $kegiatan->tahapan = 'dokumentasi_observasi';
        $kegiatan->save();

        return redirect()->route('kegiatan.myIndex', ['tahapan' => 'dokumentasi_observasi'])->with('success', 'Kehadiran berhasil dikonfirmasi.');
    }

    /**
     * Menyimpan dokumentasi observasi baru (catatan dan foto).
     *
     * @param  \App\Http\Requests\StoreDokumentasiKegiatanRequest  $request
     * @param  \App\Models\Kegiatan  $kegiatan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeObservasi(StoreDokumentasiKegiatanRequest $request, Kegiatan $kegiatan)
    {
        try {
            DB::beginTransaction();

            if ($kegiatan->tahapan !== 'dokumentasi_observasi') {
                return back()->with('error', 'Aksi tidak dapat dilakukan pada tahapan ini.');
            }

            $dokumentasi = DokumentasiKegiatan::create([
                'kegiatan_id' => $kegiatan->id,
                'catatan_kebutuhan' => $request->catatan_kebutuhan,
                'detail_pelaksanaan' => $request->detail_pelaksanaan,
                'tipe' => 'observasi',
                'user_id' => Auth::id(),
            ]);

            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $file) {
                    $path = $file->store('dokumentasi_fotos', 'public');
                    Foto::create([
                        'dokumentasi_kegiatan_id' => $dokumentasi->id,
                        'path' => $path,
                    ]);
                }
            }
            
            $kegiatan->tahapan = 'menunggu_penyerahan';
            $kegiatan->save();

            DB::commit();

            return redirect()->route('kegiatan.myIndex', ['tahapan' => 'menunggu_penyerahan'])->with('success', 'Dokumentasi observasi berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menyimpan dokumentasi observasi: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }

    /**
     * Menyimpan dokumentasi penyerahan baru.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Kegiatan  $kegiatan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storePenyerahan(Request $request, Kegiatan $kegiatan)
    {
        $this->authorize('update', $kegiatan);

        if ($kegiatan->tahapan !== 'menunggu_penyerahan') {
            return back()->with('error', 'Tidak dapat merekam penyerahan pada tahapan ini.');
        }

        $request->validate([
            'nama_dokumentasi' => 'required|string|max:255',
            'file_sktl_penyerahan' => 'required|file|mimes:pdf,doc,docx|max:2048',
            'file_kontrak' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $dokumentasi = $kegiatan->dokumentasiKegiatans()->create([
                'user_id' => Auth::id(),
                'tipe' => 'penyerahan',
                'nama_dokumentasi' => $request->nama_dokumentasi,
                'tanggal_dokumentasi' => now(),
                'file_sktl_penyerahan' => $request->file('file_sktl_penyerahan')->store('sktl_penyerahan', 'public'),
            ]);

            if ($request->hasFile('file_kontrak')) {
                Kontrak::create([
                    'dokumentasi_kegiatan_id' => $dokumentasi->id,
                    'path' => $request->file('file_kontrak')->store('kontrak_pihak_ketiga', 'public'),
                    'user_id' => Auth::id(),
                ]);
            }

            $kegiatan->tahapan = 'penyelesaian';
            $kegiatan->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menyimpan dokumentasi penyerahan: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data penyerahan.');
        }

        return redirect()->route('kegiatan.myIndex', ['tahapan' => 'penyelesaian'])->with('success', 'Dokumentasi penyerahan berhasil direkam.');
    }
}
