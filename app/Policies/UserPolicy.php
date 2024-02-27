<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->allows('utilisateur', 'lister');
    }

    public function create(User $user): bool
    {
        return $user->allows('utilisateur', 'editer');
    }

    public function update(User $user): bool
    {
        return $user->allows('utilisateur', 'editer');
    }

    public function delete(User $user): bool
    {
        return $user->allows('utilisateur', 'supprimer');
    }
}
