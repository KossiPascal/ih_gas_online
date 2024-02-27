<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReceptionCommandePolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->allows('reception_commande', 'lister');
    }

    public function create(User $user): bool
    {
        return $user->allows('reception_commande', 'editer');
    }

    public function update(User $user): bool
    {
        return $user->allows('reception_commande', 'editer');
    }

    public function delete(User $user): bool
    {
        return $user->allows('reception_commande', 'supprimer');
    }
}
