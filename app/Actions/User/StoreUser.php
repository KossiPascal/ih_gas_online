<?php

namespace App\Actions\User;

use App\Models\Centre;
use App\Models\User;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreUser
{
    use AsAction;

    /** @param  array<mixed>  $data */
    public function handle(User $user, array $data)
    {
        $user->fill(Arr::except($data, ['password', 'profils']));

        if (isset($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        $user->fill(['cms' => Centre::find($data['centre_id'], 'nom')->nom])->save();

        $user->profils()->syncWithPivotValues($data['profils'], ['date_ajout' => now()]);
    }
}
