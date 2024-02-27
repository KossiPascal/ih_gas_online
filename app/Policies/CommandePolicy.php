<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommandePolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->allows('commande', 'lister');
    }

    public function create(User $user): bool
    {
        return $user->allows('commande', 'editer');
    }

    public function update(User $user): bool
    {
        return $user->allows('commande', 'editer');
    }

    public function cancel(User $user): bool
    {
        return $user->allows('commande', 'annuler');
    }

    public function delete(User $user): bool
    {
        return $user->allows('commande', 'supprimer');
    }
}
