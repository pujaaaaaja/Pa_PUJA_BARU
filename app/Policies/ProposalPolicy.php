<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProposalPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'kadis']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Proposal $proposal): bool
    {
        // Izinkan jika user adalah admin, kadis, atau pemilik proposal
        return $user->hasRole(['admin', 'kadis']) || $user->id === $proposal->pengusul_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('pengusul');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Proposal $proposal = null): bool
    {
        // PERBAIKAN: Argumen $proposal dibuat opsional untuk menangani
        // pemeriksaan hak akses umum (misalnya dari UI) dan spesifik.

        // Jika ini adalah pemeriksaan umum (tidak ada instance proposal spesifik),
        // izinkan jika pengguna memiliki peran yang berpotensi bisa mengedit.
        if (is_null($proposal)) {
            return $user->hasRole(['kadis', 'pengusul', 'admin']);
        }

        // Jika ini adalah pemeriksaan spesifik (ada instance proposal),
        // terapkan logika otorisasi yang detail.
        return $user->hasRole('kadis') || $user->id === $proposal->pengusul_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Proposal $proposal): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Proposal $proposal): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Proposal $proposal): bool
    {
        return $user->hasRole('admin');
    }
}
