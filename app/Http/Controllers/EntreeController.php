<?php

namespace App\Http\Controllers;

use App\Models\Entree;
use Illuminate\Http\Request;

class EntreeController extends Controller
{
    public function index()
    {
        $this->authorize('manage-action',['entree','lister']);
    }

    public function create()
    {
        $this->authorize('create', 'entree');
    }

    public function store(Request $request)
    {
        $this->authorize('manage-action',['entree','creer']);
        $this->authorize('create', 'entree');
    }

    public function show(Entree $entree)
    {
        $this->authorize('view', 'entree');
    }

    public function edit(Entree $entree)
    {
        $this->authorize('manage-action',['entree','editer']);
        $this->authorize('update', 'entree');
    }

    public function update(Request $request, Entree $entree)
    {
        $this->authorize('manage-action',['entree','editer']);
        $this->authorize('update', 'entree');
    }

    public function destroy(Entree $entree)
    {
        $this->authorize('delete', 'entree');
    }
}
