<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->allows('stock', 'lister');
    }

    public function fix(User $user): bool
    {
        return $user->allows('stock', 'corriger');
    }

    public function inventory(User $user): bool
    {
        return $user->allows('stock', 'inventaire');
    }
}
