<?php

namespace App\Actions\Profil;

use App\Models\Profil;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreProfil
{
    use AsAction;

    /** @param  array<mixed>  $data */
    public function handle(Profil $profil, array $data)
    {
        $profil->fill(Arr::except($data, 'droits'))
            ->fill(['statut' => true])
            ->save();

        $profil->droits()->sync($data['droits']);
    }
}
