<?php

namespace App\Http\Controllers;

use App\Enums\TahapanKegiatan;
use App\Http\Requests\StoreDokumentasiKegiatanRequest;
use App\Http\Resources\KegiatanResource;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DokumentasiKegiatanController extends Controller
{
    /**
     * Menampilkan form untuk membuat dokumentasi baru.
     * Metode ini digunakan untuk menampilkan form pada tahapan yang sesuai.
     */
    public function create(Kegiatan $kegiatan)
    {
        // Otorisasi: Memastikan pengguna yang login boleh membuat dokumentasi untuk kegiatan ini.
        $this->authorize('update', $kegiatan);

        // Mengembalikan view Inertia dengan data kegiatan yang diperlukan.
        return Inertia::render('Dokumentasi/Create', [
            'kegiatan' => new KegiatanResource($kegiatan)
        ]);
    }

    /**
     * Menyimpan dokumentasi observasi baru.
     * Aksi untuk Pegawai.
     */
    public function storeObservasi(Request $request, Kegiatan $kegiatan)
    {
        $this->authorize('update', $kegiatan); // Memastikan pegawai adalah anggota tim

        // Validasi bahwa kegiatan berada pada tahap yang benar
        if ($kegiatan->tahapan !== TahapanKegiatan::PERJALANAN_DINAS) {
            return back()->with('error', 'Tidak dapat merekam observasi pada tahapan ini.');
        }

        $data = $request->validate([
            'catatan_kebutuhan' => 'required|string',
            'detail_pelaksanaan' => 'required|string',
            'fotos' => 'required|array',
            'fotos.*' => 'required|file|image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // PERBAIKAN: Menggunakan nama relasi yang benar ('dokumentasi').
            $dokumentasi = $kegiatan->dokumentasi()->create([
                'user_id' => Auth::id(),
                'tipe' => 'observasi',
                'catatan_kebutuhan' => $data['catatan_kebutuhan'],
                'detail_pelaksanaan' => $data['detail_pelaksanaan'],
                'tanggal_dokumentasi' => now(),
            ]);

            // Simpan foto-foto dokumentasi
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $file) {
                    $path = $file->store('dokumentasi_foto', 'public');
                    $dokumentasi->fotos()->create([
                        'path' => $path,
                        'user_id' => Auth::id(),
                    ]);
                }
            }

            // Setelah dokumentasi observasi selesai, update tahapan kegiatan
            $kegiatan->tahapan = TahapanKegiatan::DOKUMENTASI_OBSERVASI;
            $kegiatan->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }

        return to_route('kegiatan.myIndex')->with('success', 'Dokumentasi observasi berhasil direkam.');
    }

    /**
     * Menyimpan dokumentasi penyerahan baru.
     * Aksi untuk Pegawai.
     */
    public function storePenyerahan(Request $request, Kegiatan $kegiatan)
    {
        $this->authorize('update', $kegiatan); // Memastikan pegawai adalah anggota tim

        // Validasi bahwa kegiatan berada pada tahap yang benar
        if ($kegiatan->tahapan !== TahapanKegiatan::DOKUMENTASI_PENYERAHAN) {
            return back()->with('error', 'Tidak dapat merekam penyerahan pada tahapan ini.');
        }

        $data = $request->validate([
            'nama_dokumentasi' => 'required|string',
            'kontrak_path' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'fotos' => 'nullable|array',
            'fotos.*' => 'nullable|file|image|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // PERBAIKAN: Menggunakan nama relasi yang benar ('dokumentasi').
            $dokumentasi = $kegiatan->dokumentasi()->create([
                'user_id' => Auth::id(),
                'tipe' => 'penyerahan',
                'nama_dokumentasi' => $data['nama_dokumentasi'],
                'tanggal_dokumentasi' => now(),
            ]);

            // Simpan file kontrak jika ada
            if ($request->hasFile('kontrak_path')) {
                $path = $request->file('kontrak_path')->store('kontrak_pihak_ketiga', 'public');
                $dokumentasi->kontraks()->create([
                    'path' => $path,
                    'user_id' => Auth::id(),
                ]);
            }

            // Simpan foto-foto dokumentasi penyerahan
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $file) {
                    $path = $file->store('dokumentasi_foto_penyerahan', 'public');
                    $dokumentasi->fotos()->create([
                        'path' => $path,
                        'user_id' => Auth::id(),
                    ]);
                }
            }
            
            // PERBAIKAN: Update tahapan ke 'Penyelesaian' setelah penyerahan.
            $kegiatan->tahapan = TahapanKegiatan::SELESAI;
            $kegiatan->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }

        return to_route('kegiatan.myIndex')->with('success', 'Dokumentasi penyerahan berhasil direkam. Kegiatan siap untuk diselesaikan.');
    }

    /**
     * Metode ini bisa diimplementasikan jika ada kebutuhan untuk menyimpan
     * dokumentasi secara umum, namun saat ini tidak digunakan dalam alur utama.
     */
    public function store(StoreDokumentasiKegiatanRequest $request)
    {
        // Logika untuk menyimpan dokumentasi umum bisa ditambahkan di sini.
    }
}
