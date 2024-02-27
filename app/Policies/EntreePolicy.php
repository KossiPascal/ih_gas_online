<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EntreePolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        info('check user can view entree');

        return $user->allows('entree', 'lister');
    }

    public function create(User $user): bool
    {
        return $user->allows('entree', 'editer');
    }

    public function update(User $user): bool
    {
        return $user->allows('entree', 'editer');
    }

    public function delete(User $user): bool
    {
        return $user->allows('entree', 'supprimer');
    }
}
