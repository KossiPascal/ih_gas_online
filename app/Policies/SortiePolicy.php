<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SortiePolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->allows('sortie', 'lister');
    }

    public function create(User $user): bool
    {
        return $user->allows('sortie', 'editer');
    }

    public function update(User $user): bool
    {
        return $user->allows('sortie', 'editer');
    }

    public function delete(User $user): bool
    {
        return $user->allows('sortie', 'supprimer');
    }
}
