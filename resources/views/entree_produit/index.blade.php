@extends('layouts.adminlayout')

@section('title', 'PCSOFT V4: ' . trans('messages.entree_produits'))

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none margin-top: 5px">
        <div class="row">
            <div class="col-md-8">
                <h3 class="ml-5">{{ trans('messages.entree_produits') }}</h3>
            </div>

            @can('create', 'entree')
                <div class="col-md-4">
                    <button type="button" id="create-product-entry" class="btn btn-success float-right">
                        <i class="fa fa-plus"></i> {{ trans('messages.nouvel_entree_produit') }}
                    </button>
                </div>
            @endcan
        </div>

        <div class="info-box mt-5">
            <x-datatable id="list-entries" url="{{ route('ajax.entree-produits.index') }}" :columns="json_encode([
                ['data' => 'entree_produit_id', 'name' => 'entree_produit_id'],
                ['data' => 'stock_produit.libelle', 'name' => 'stock_produit.libelle'],
                ['data' => 'unite', 'name' => 'unite'],
                ['data' => 'qte', 'name' => 'qte'],
                ['data' => 'lot', 'name' => 'lot'],
                ['data' => 'motif', 'name' => 'motif'],
                ['data' => 'user.name', 'name' => 'user.name'],
                ['data' => 'created_at', 'name' => 'created_at'],
            ])">
                <x-slot:head>
                    <tr>
                        <th>#</th>
                        <th>{{ trans('messages.Produit') }}</th>
                        <th>{{ trans('messages.Unite') }}</th>
                        <th>{{ trans('messages.Qte') }}</th>
                        <th>{{ trans('messages.Lot') }}</th>
                        <th>{{ trans('messages.Motif') }}</th>
                        <th>{{ trans('messages.Utilisateur') }}</th>
                        <th>{{ trans('messages.date_entree') }}</th>
                    </tr>
                </x-slot:head>
            </x-datatable>

            <x-modals.form id="profil-form" create-id="create-product-entry" list-id="list-entries"
                url="{{ route('ajax.entree-produits.store') }}">
                <x-slot:title>
                    {{ trans('messages.nouvel_entree_produit') }}
                </x-slot>

                <x-form.fields.select name="magasin_id" label="Magasin" required>
                    <option value="">{{ trans('messages.selectionnez_magasin') }}</option>

                    @foreach ($stores as $store)
                        <option value="{{ $store->magasin_id }}">{{ $store->libelle }}</option>
                    @endforeach
                </x-form.fields.select>

                <p class="mt-4"><strong>{{ trans('messages.produits_concernes') }}</strong></p>

                <button type="button" id="add-product"
                    class="btn btn-secondary btn-sm">{{ trans('messages.Ajouter') }}</button>

                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-striped table-sm" id="products-list">
                        <thead>
                            <tr>
                                <th>{{ trans('messages.Produit') }}</th>
                                <th>{{ trans('messages.Unite') }}</th>
                                <th>{{ trans('messages.Qte recue') }}</th>
                                <th>{{ trans('messages.Lot') }}</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody></tbody>
                    </table>
                </div>

                <x-form.fields.textarea name="motif" label="{{ trans('messages.Motif') }}" required
                    placeholder="{{ trans('messages.Motif') }}" />
            </x-modals.form>

            <x-modals.delete id="delete-profil-data" list-id="list-entries"
                url="{{ route('ajax.entree-produits.index') }}" />
        </div>
    </main>
@endsection

@section('extra-js')
    <script>
        $(document).ready(function() {
            $('#magasin_id').on('change', function() {
                const $that = $(this)

                const val = $(this).val()

                const $product = $('#products-list tbody')

                if (!val) {
                    return
                }

                var stores = window.stores || []

                const store = stores.find((store) => store.id == val)

                if (!store) {
                    $.ajax({
                        url: '/ajax/magasins/' + val + '/produits',
                        method: 'get',
                        dataType: 'json',
                    }).done(function(resp) {
                        stores.push({
                            'id': val,
                            'products': resp
                        })

                        window.stores = stores
                    })
                }

                $product.html(``)
            })

            $(document).on('click', '#add-product', function() {
                $magasin = $('#magasin_id')

                var stores = window.stores || []

                const store = stores.find((store) => store.id == $magasin.val())

                console.log('store', store)

                if (!store) {
                    return
                }

                const options = store.products.map((p) => {
                    return `
                        <option value="${p.stock_produit_id}">
                            ${p.libelle} ({{ trans('messages.qte_dispo') }}: ${p.qte})
                        </option>`
                })

                const $product = $('#products-list tbody')

                const i = $product.find('tr').length + 1

                $product.append(`
<tr>
    <td>
        <div class="form-group">
            <select name="produits[${i}][id]" class="form-control" required placeholder="Produit">
                <option value="">{{ trans('messages.selectionnez_produit') }}</option>
                ${options}
            </select>
        </div>
    </td>
    <td>
        <div class="form-group">
            <input type="number" name="produits[${i}][unite]" class="form-control" min="1" required placeholder="{{ trans('messages.Unite') }}">
        </div>
    </td>
    <td>
        <div class="form-group">
            <input type="number" name="produits[${i}][qte]" class="form-control" min="1" required placeholder="{{ trans('messages.qte_recue') }}">
        </div>
    </td>
    <td>
        <div class="form-group">
            <input type="text" name="produits[${i}][lot]" class="form-control" required placeholder="{{ trans('messages.Lot') }}">
        </div>
    </td>
    <td>
        <button type="button" class="remove-product btn btn-danger btn-sm">
            <i class="fa fa-trash"></i>
        </button>
    </td>
</tr>
                `)
            })

            $(document).on('click', '.remove-product', function() {
                $(this).closest('tr').remove()
            })
        })
    </script>
@endsection
