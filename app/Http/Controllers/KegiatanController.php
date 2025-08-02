<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Http\Requests\StoreKegiatanRequest;
use App\Http\Requests\UpdateKegiatanRequest;
use App\Http\Resources\KegiatanResource;
use App\Http\Resources\ProposalResource;
use App\Http\Resources\TimResource;
use App\Models\Proposal;
use App\Models\Tim;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class KegiatanController extends Controller
{
        /**
     * Display a listing of the kegiatans for the current user.
     */
    public function myIndex()
    {
        $user = Auth::user();

        // Query dasar untuk mengambil kegiatan yang terkait dengan user yang login
        $query = Kegiatan::whereHas('tim.users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['proposal', 'tim']);

        // Ambil nilai 'tahapan' dari query string di URL.
        // Ini digunakan untuk fungsionalitas tab di frontend.
        $tahapan = request('tahapan');

        // Jika ada parameter 'tahapan', filter berdasarkan itu.
        // Jika tidak, tampilkan tab default 'perjalanan_dinas'.
        if ($tahapan) {
            $query->where('tahapan', $tahapan);
        } else {
            $query->where('tahapan', 'perjalanan_dinas');
        }

        // Lakukan paginasi pada hasil query
        $kegiatans = $query->latest()->paginate(10)->onEachSide(1);

        // Render komponen React 'Kegiatan/MyIndex' dengan data yang diperlukan
        return Inertia::render('Kegiatan/MyIndex', [
            'kegiatans' => KegiatanResource::collection($kegiatans),
            'queryParams' => request()->query() ?: null,
            'success' => session('success'),
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Kegiatan::class);
        $query = Kegiatan::query()->with(['proposal', 'tim', 'createdBy']);

        $sortField = request("sort_field", 'created_at');
        $sortDirection = request("sort_direction", "desc");

        if (request("nama_kegiatan")) {
            $query->where("nama_kegiatan", "like", "%" . request("nama_kegiatan") . "%");
        }
        if (request("status_kegiatan")) {
            $query->where("status_kegiatan", request("status_kegiatan"));
        }

        $kegiatans = $query->orderBy($sortField, $sortDirection)
            ->paginate(10)
            ->onEachSide(1);

        return Inertia::render('Kegiatan/Index', [
            'kegiatans' => KegiatanResource::collection($kegiatans),
            'queryParams' => request()->query() ?: null,
            'success' => session('success'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Kegiatan::class);

        $proposals = Proposal::where('status', 'disetujui')->get();
        $tims = Tim::all();

        return Inertia::render('Kegiatan/Create', [
            'proposals' => ProposalResource::collection($proposals),
            'tims' => TimResource::collection($tims),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKegiatanRequest $request)
    {
        $this->authorize('create', Kegiatan::class);
        $data = $request->validated();

        $data['created_by'] = Auth::id();
        $data['tahapan'] = 'Perjalanan Dinas';

        $sktl = $data['sktl_path'] ?? null;
        if ($sktl) {
            $data['sktl_path'] = $sktl->store('kegiatan_sktl', 'public');
        }

        Kegiatan::create($data);

        return to_route('dashboard')->with('success', 'Kegiatan berhasil dibuat dan tugas telah diberikan kepada tim.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Kegiatan $kegiatan)
    {
        $this->authorize('view', $kegiatan);

        // PERBAIKAN: Tambahkan 'tim.pegawais' untuk memuat daftar anggota tim.
        // Ini akan mengatasi error 'map' di frontend.
        $kegiatan->load(['proposal', 'tim.pegawais', 'createdBy', 'dokumentasi.fotos', 'beritaAcara']);

        return Inertia::render('Kegiatan/Show', [
            'kegiatan' => new KegiatanResource($kegiatan)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kegiatan $kegiatan)
    {
        $this->authorize('update', $kegiatan);
        $proposals = Proposal::where('status', 'disetujui')->get();
        $tims = Tim::all();

        return Inertia::render('Kegiatan/Edit', [
            'kegiatan' => new KegiatanResource($kegiatan),
            'proposals' => ProposalResource::collection($proposals),
            'tims' => TimResource::collection($tims),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKegiatanRequest $request, Kegiatan $kegiatan)
    {
        $this->authorize('update', $kegiatan);
        $data = $request->validated();

        $sktl = $data['sktl_path'] ?? null;
        if ($sktl && $request->hasFile('sktl_path')) {
            if ($kegiatan->sktl_path) {
                Storage::disk('public')->delete($kegiatan->sktl_path);
            }
            $data['sktl_path'] = $sktl->store('kegiatan_sktl', 'public');
        }

        $kegiatan->update($data);

        return to_route('kegiatan.index')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kegiatan $kegiatan)
    {
        $this->authorize('delete', $kegiatan);
        $nama_kegiatan = $kegiatan->nama_kegiatan;
        
        if ($kegiatan->sktl_path) {
            Storage::disk('public')->delete($kegiatan->sktl_path);
        }
        if ($kegiatan->sktl_penyerahan_path) {
            Storage::disk('public')->delete($kegiatan->sktl_penyerahan_path);
        }

        $kegiatan->delete();
        
        return to_route('kegiatan.index')->with('success', "Kegiatan \"$nama_kegiatan\" berhasil dihapus.");
    }
}
