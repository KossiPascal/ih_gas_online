<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FournisseurPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->allows('fournisseur', 'lister');
    }

    public function create(User $user): bool
    {
        return $user->allows('fournisseur', 'editer');
    }

    public function update(User $user): bool
    {
        return $user->allows('fournisseur', 'editer');
    }

    public function delete(User $user): bool
    {
        return $user->allows('fournisseur', 'supprimer');
    }
}
