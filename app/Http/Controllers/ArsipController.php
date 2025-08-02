<?php

namespace App\Http\Controllers;

use App\Http\Resources\KegiatanResource;
use App\Models\Kegiatan;
use Inertia\Inertia;

class ArsipController extends Controller
{
    /**
     * Menampilkan daftar kegiatan yang sudah diarsipkan (selesai).
     */
    public function index()
    {
        $kegiatans = Kegiatan::where('tahapan', 'selesai')
            ->with('proposal', 'tim')
            ->latest('updated_at')
            ->paginate(10);

        return Inertia::render('Arsip/Index', [
            'kegiatans' => KegiatanResource::collection($kegiatans),
        ]);
    }

    /**
     * Menampilkan detail lengkap dari satu kegiatan yang diarsipkan.
     *
     * @param \App\Models\Kegiatan $kegiatan
     * @return \Inertia\Response
     */
    public function show(Kegiatan $kegiatan)
    {
        // Eager load semua relasi yang dibutuhkan untuk halaman detail
        $kegiatan->load([
            'proposal.user', // Muat proposal beserta data pengusulnya
            'tim.pegawai',   // Muat tim beserta data para pegawainya
            'dokumentasiKegiatans' => function ($query) {
                // Muat semua dokumentasi beserta relasi spesifiknya
                $query->with(['user', 'fotos', 'kontraks']);
            },
            'beritaAcaras.user' // Muat berita acara beserta data pengunggahnya
        ]);

        return Inertia::render('Arsip/Show', [
            'kegiatan' => new KegiatanResource($kegiatan),
        ]);
    }
}
