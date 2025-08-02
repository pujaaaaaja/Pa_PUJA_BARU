<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tim extends Model
{
    use HasFactory;

    protected $fillable = ['nama_tim'];

    /**
     * Relasi many-to-many antara Tim dan User (Pegawai).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function pegawais(): BelongsToMany
    {
        // Mendefinisikan bahwa sebuah Tim memiliki banyak User (pegawai)
        // melalui tabel pivot 'pegawai_tim'.
        return $this->belongsToMany(User::class, 'pegawai_tim', 'tim_id', 'user_id');
    }

    /**
     * Relasi one-to-many antara Tim dan Kegiatan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kegiatans(): HasMany
    {
        return $this->hasMany(Kegiatan::class);
    }
}
