<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProduitPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->allows('produit', 'lister');
    }

    public function create(User $user): bool
    {
        return $user->allows('produit', 'editer');
    }

    public function update(User $user): bool
    {
        return $user->allows('produit', 'editer');
    }

    public function delete(User $user): bool
    {
        return $user->allows('produit', 'supprimer');
    }
}
