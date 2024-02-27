<?php

namespace App\Http\Controllers\Ajax;

use App\Actions\Profil\DeleteProfil;
use App\Actions\Profil\StoreProfil;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfilRequest;
use App\Models\Profil;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProfilController extends Controller
{
    public function index()
    {
        $query = Profil::query()->where('statut', true);

        return datatables()->of($query)
            ->addColumn('action', function (Profil $profil) {
                return <<<EOT
                    <div class="actions">
                        <button
                            type="button"
                            data-id="{$profil->profil_id}"
                            class="droits btn btn-info btn-sm"
                        >
                            Droits
                        </button>

                        <button
                            type="button"
                            data-id="{$profil->profil_id}"
                            class="edit btn btn-success btn-sm"
                        >
                            <i class="fa fa-edit"></i>
                        </button>

                        <button
                            type="button"
                            data-id="{$profil->profil_id}"
                            class="delete btn btn-danger btn-sm"
                            onclick="
                                $('#delete-profil-data').data('id', $(this).data('id'));
                                $('#delete-modal').modal('show');
                            "
                        >
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                EOT;
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function store(StoreProfilRequest $request)
    {
        DB::transaction(fn () => StoreProfil::run(new Profil(), $request->validated()));

        return new JsonResponse(status: 201);
    }

    public function edit(Profil $profil)
    {
        return new JsonResponse($profil->load('droits:droit_id'));
    }

    public function update(StoreProfilRequest $request, Profil $profil)
    {
        DB::transaction(fn () => StoreProfil::run($profil, $request->validated()));

        return new JsonResponse(status: 204);
    }

    public function destroy(Profil $profil)
    {
        DB::transaction(fn () => DeleteProfil::run($profil));

        return new JsonResponse(status: 204);
    }
}
