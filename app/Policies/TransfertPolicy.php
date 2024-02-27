<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransfertPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->allows('transfert', 'lister');
    }

    public function create(User $user): bool
    {
        return $user->allows('transfert', 'editer');
    }

    public function update(User $user): bool
    {
        return $user->allows('transfert', 'editer');
    }

    public function delete(User $user): bool
    {
        return $user->allows('transfert', 'supprimer');
    }
}
