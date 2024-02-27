<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssurancePolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->allows('assurance', 'lister');
    }

    public function create(User $user): bool
    {
        return $user->allows('assurance', 'editer');
    }

    public function update(User $user): bool
    {
        return $user->allows('assurance', 'editer');
    }

    public function delete(User $user): bool
    {
        return $user->allows('assurance', 'supprimer');
    }
}
