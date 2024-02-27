<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConfectionKitPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->allows('confection_kit', 'lister');
    }

    public function create(User $user): bool
    {
        return $user->allows('confection_kit', 'editer');
    }

    public function update(User $user): bool
    {
        return $user->allows('confection_kit', 'editer');
    }

    public function delete(User $user): bool
    {
        return $user->allows('confection_kit', 'supprimer');
    }
}
