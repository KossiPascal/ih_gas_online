<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoriePolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->allows('categorie', 'lister');
    }

    public function create(User $user): bool
    {
        return $user->allows('categorie', 'editer');
    }

    public function update(User $user): bool
    {
        return $user->allows('categorie', 'editer');
    }

    public function delete(User $user): bool
    {
        return $user->allows('categorie', 'supprimer');
    }
}
