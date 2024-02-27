<?php

namespace App\Http\Controllers\Ajax;

use App\Actions\User\DeleteUser;
use App\Actions\User\StoreUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('view', 'utilisateur');

        $query = User::query()
            ->with('centre', 'profils')
            ->select('users.*');

        return datatables()->of($query)
            ->addColumn('profils', function (User $user) {
                return $user->profils
                    ->map(fn ($map) => $map->nom)
                    ->implode('<br>');
            })
            ->addColumn('action', function (User $user) {
                return <<<EOT
                    <div class="actions">
                        <button
                            type="button"
                            data-id="{$user->id}"
                            class="edit btn btn-success btn-sm"
                        >
                            <i class="fa fa-edit"></i>
                        </button>

                        <button
                            type="button"
                            data-id="{$user->id}"
                            class="delete btn btn-danger btn-sm"
                        >
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                EOT;
            })
            ->rawColumns(['profils', 'action'])
            ->toJson();
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', 'utilisateur');

        DB::transaction(fn () => StoreUser::run(new User(), $request->validated()));

        return new JsonResponse(status: 201);
    }

    public function edit(User $user)
    {
        $this->authorize('update', 'utilisateur');

        return new JsonResponse($user->load('profils:profil_id'));
    }

    public function update(StoreUserRequest $request, User $user)
    {
        $this->authorize('update', 'utilisateur');

        DB::transaction(fn () => StoreUser::run($user, $request->validated()));

        return new JsonResponse(status: 204);
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', 'utilisateur');

        DB::transaction(fn () => DeleteUser::run($user));

        return new JsonResponse(status: 204);
    }
}
