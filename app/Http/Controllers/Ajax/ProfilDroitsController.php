<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\Profil;
use Illuminate\Http\JsonResponse;

class ProfilDroitsController extends Controller
{
    public function __invoke(Profil $profil)
    {
        return new JsonResponse($profil->load('droits'));
    }
}
