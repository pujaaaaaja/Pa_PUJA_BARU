<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_proposal',
        'deskripsi',
        'tujuan',
        'dokumen_path',
        'status',
        'tanggal_pengajuan',
        'alasan_penolakan',
        'pengusul_id',
    ];

    /**
     * PERBAIKAN: Menambahkan definisi relasi 'pengusul'.
     *
     * Metode ini mendefinisikan bahwa sebuah Proposal 'milik' (belongs to)
     * seorang User (sebagai pengusul).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pengusul(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengusul_id');
    }
}
