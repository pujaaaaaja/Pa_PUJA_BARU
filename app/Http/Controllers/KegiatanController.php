<?php

namespace App\Http\Controllers;

use App\Http\Resources\KegiatanResource;
use App\Models\Kegiatan;
use App\Http\Requests\StoreKegiatanRequest;
use App\Http\Requests\UpdateKegiatanRequest;
use App\Models\Proposal;
use App\Models\Tim;
use Illuminate\Http\Request;
// 1. Import Auth facade
use Illuminate\Support\Facades\Auth;

class KegiatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Kegiatan::query();

        $sortField = request("sort_field", 'created_at');
        $sortDirection = request("sort_direction", "desc");

        if (request("nama_kegiatan")) {
            $query->where("nama_kegiatan", "like", "%" . request("nama_kegiatan") . "%");
        }
        if (request("status")) {
            $query->where("status", request("status"));
        }

        $kegiatans = $query->orderBy($sortField, $sortDirection)
            ->paginate(10)
            ->onEachSide(1);

        return inertia("Kegiatan/Index", [
            "kegiatans" => KegiatanResource::collection($kegiatans),
            'queryParams' => request()->query() ?: null,
            'success' => session('success'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $proposals = Proposal::where('status', 'disetujui')->get();
        $tims = Tim::all();
        return inertia("Kegiatan/Create", compact('proposals', 'tims'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKegiatanRequest $request)
    {
        $data = $request->validated();
        Kegiatan::create($data);

        return to_route('kegiatan.index')->with('success', 'Kegiatan berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Kegiatan $kegiatan)
    {
        return inertia('Kegiatan/Show', [
            'kegiatan' => new KegiatanResource($kegiatan),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kegiatan $kegiatan)
    {
        $proposals = Proposal::where('status', 'disetujui')->get();
        $tims = Tim::all();
        return inertia("Kegiatan/Edit", compact('kegiatan', 'proposals', 'tims'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKegiatanRequest $request, Kegiatan $kegiatan)
    {
        $data = $request->validated();
        $kegiatan->update($data);

        return to_route('kegiatan.index')->with('success', "Kegiatan \"$kegiatan->nama_kegiatan\" berhasil diupdate");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kegiatan $kegiatan)
    {
        $nama_kegiatan = $kegiatan->nama_kegiatan;
        $kegiatan->delete();
        return to_route('kegiatan.index')->with('success', "Kegiatan \"$nama_kegiatan\" berhasil dihapus");
    }

    public function myIndex()
    {
        // 2. Ganti auth()->user() dengan Auth::user()
        $user = Auth::user();
        $query = Kegiatan::query()->whereHas('tim.users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        $sortField = request("sort_field", 'created_at');
        $sortDirection = request("sort_direction", "desc");

        if (request("nama_kegiatan")) {
            $query->where("nama_kegiatan", "like", "%" . request("nama_kegiatan") . "%");
        }
        if (request("status")) {
            $query->where("status", request("status"));
        }

        $kegiatans = $query->orderBy($sortField, $sortDirection)
            ->paginate(10)
            ->onEachSide(1);

        return inertia("Kegiatan/MyIndex", [
            "kegiatans" => KegiatanResource::collection($kegiatans),
            'queryParams' => request()->query() ?: null,
            'success' => session('success'),
        ]);
    }
}
