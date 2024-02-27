@extends('layouts.caisselayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')


@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <div class="col-md-12">
            <div class="col-md-6 float-left">
                <h4 class="ml-5">{{__('messages.LISTE DES VENTES DE LA PHARMACIE')}}</h4>
            </div>

            <div class="col-md-6 float-right">
                <a href="{{route('vente.index')}}" class="btn btn-warning">{{_('messages.NOUVELLE VENTE')}}</a>
            </div>
        </div>

        <div class="col-md-12">
            <div class="info-box">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="liste_ventes">
                        <thead>
                        <tr class="cart_menu" style="background-color: #00b0e8">
                            <td class="description">{{__('messages.Date')}} </td>
                            <td class="price">{{__('messages.Code')}}</td>
                            <td class="quantity">{{__('messages.Patient')}}</td>
                            <td class="total">{{__('messages.Net payer')}}</td>
                            <td class="total">{{__('messages.Assurance')}}</td>
                            <td>{{__('messages.Selectionner')}}</td>
                        </tr>
                        </thead>
                        <tbody>
                            <?php $count =1;?>
                                @foreach($ventes as $vente)
                                    <tr>
                                        <td width="6" style="text-align: right">{{($vente->ven_date)}}</td>
                                        <td width="6" style="text-align: right">{{($vente->ven_num)}}</td>
                                        <td width="5" style="text-align: left">{{($vente->pat_num)}}</td>
                                        <td width="5" style="text-align: right">{{($vente->ven_net)}}</td>
                                        <td width="5" style="text-align: right">{{($vente->mut_lib)}}</td>
                                        <td width="5" style="text-align: right">
                                            <a href="{{route('vente.selectionner',$vente->ven_num)}}" class="btn btn-sm btn-warning"><i class="fa fa-check"></i> </a>
                                        </td>
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
            $('#liste_ventes').DataTable({
                language: {
                    searchS: "Recherche une vente"
                }
            });
        });
    </script>
@endsection
