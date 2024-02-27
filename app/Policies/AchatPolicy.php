<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AchatPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->allows('achat', 'lister');
    }

    public function create(User $user): bool
    {
        return $user->allows('achat', 'editer');
    }

    public function update(User $user): bool
    {
        return $user->allows('achat', 'editer');
    }

    public function delete(User $user): bool
    {
        return $user->allows('achat', 'supprimer');
    }
}
