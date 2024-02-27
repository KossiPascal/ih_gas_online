<?php

namespace App\Actions\Profil;

use App\Models\Profil;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteProfil
{
    use AsAction;

    public function handle(Profil $profil)
    {
        $profil->fill(['statut' => false])->save();
    }
}
