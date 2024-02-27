<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProfilePolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->allows('profil', 'lister');
    }

    public function create(User $user): bool
    {
        return $user->allows('profil', 'editer');
    }

    public function update(User $user): bool
    {
        return $user->allows('profil', 'editer');
    }

    public function delete(User $user): bool
    {
        return $user->allows('profil', 'supprimer');
    }
}
