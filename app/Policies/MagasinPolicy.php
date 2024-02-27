<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MagasinPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->allows('magasin', 'lister');
    }

    public function create(User $user): bool
    {
        return $user->allows('magasin', 'editer');
    }

    public function update(User $user): bool
    {
        return $user->allows('magasin', 'editer');
    }

    public function delete(User $user): bool
    {
        return $user->allows('magasin', 'supprimer');
    }
}
