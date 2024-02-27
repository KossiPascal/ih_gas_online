@extends('layouts.caisselayout')
@section('title','PCSOFT V4: BON DE COMMANDE')

@section('extra-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">
        <div class="col-md-12">
            <div class="col-md-4 float-left">
                <h3 class="ml-5">{{__('messages.FICHE ENCAISSEMENT')}}</h3>
            </div>

            <div class="col-md-4 float-left">
                <a href="{{route('vente.histo')}}" class="btn btn-warning"><i class="fa fa-list"></i> {{__('messages.HISTORIQUE DES VENTES')}}</a>
            </div>

            <div class="col-md-4 float-right">
                <a href="{{route('vente.ventes')}}" class="btn btn-danger"><i class="fa fa-list"></i> {{__('messages.ENCAISSER')}}</a>
            </div>
        </div>
        <span class="annuler_result" id="annuler_result"></span>
        <div class="col-md-12 float-left">
            <div class="col-md-7 float-left">
                <h5 class="ml-7">{{__('messagesPRODUITS SELECTIONNES')}}</h5>
            </div>

            <div class="col-md-5 float-right">
                <a> {{__('messages.LISTE DES PRODUITS')}}</a>
            </div>
        </div>

        <div class="col-md-12 float-left">
            <div class="col-md-7 float-left">
                <div class="info-box">
                    @include('vente/formenc')
                </div>
            </div>

            <div class="col-md-5 float-right">
                <div class="table-responsive">
                    <table id="liste_produit" class="display table table-striped table-bordered data-table">
                        <thead>
                        <tr>
                            <th>{{__('messages.Libelle')}}</th>
                            <th>{{__('messages.PU')}}</th>
                            <th>{{__('messages.Qte')}}</th>
                            <th>{{__('messages.MONTANT')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $count =1;?>
                        @foreach($pdtcon as $produit)
                            <tr>
                                <td width="6" style="text-align: right">{{($produit->pdt_lib)}}</td>
                                <td width="6" style="text-align: right">{{($produit->pu)}}</td>
                                <td width="5" style="text-align: right">{{($produit->qte)}}</td>
                                <td width="5" style="text-align: right">{{($produit->mont)}}</td>
                            </tr>
                        @endforeach
                        <?php $count++;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('extra-js')
    <script>

        $(document).ready(function(){

            ven_rem.onchange = function () {
                var ven_net = document.getElementById('ven_net').value;
                var ven_rem = document.getElementById('ven_rem').value;
                document.getElementById('ven_rel').value = ven_rem-ven_net;
            };

        });
    </script>
@endsection
