@extends('layouts.printlayout')

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">
        <table>
            <tr>
                <td style="text-align: center; font-size: 15px;">{{__('messages.ENCAISSE')}}</td>
                <td style="text-align: center; font-size: 15px;">{{$entete}}</td>
            </tr>
            <tr>
                <td width="15%">
                    <img src="{{asset('images/logo.png')}}" width="80" height="40">
                </td>
                <td width="85%">
                    <div style="font-size: 15px;">{{$centre->nom_centre}}</div>
                    <div style="font-size: 8px;">{{$centre->services}}</div>
                    <div style="font-size: 8px;">{{$centre->adresse}}</div>
                    <div style="font-size: 8px;">{{$centre->telephone}}</div>
                </td>
            </tr>
        </table>
        <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
            <tr>
                <td style="font-size: 10px;">DUPLICATA N° :<b>{{$vente->code}}</b>  /  {{__{'messages.Date'}}} : {{$date_vente.' '.$vente->heure_vente}}</td>
            </tr>
            <tr>
                <td style="font-size: 10px">{{__{'messages.Caissier'}}} :<b>{{Auth::user()->name}}</b>/ {{__{'messages.Patient'}}} :<b>{{$vente->nom_prenom}}</td>
            </tr>
        </table>

        <table style="width: 100%; border: 1px solid; border-radius: 0px" cellspacing="0" cellpadding="3">
            <thead>
            <tr style="border-radius: 10px; background-color: #E5CC75";>
                <th style="font-size: 10px;" width="50%">{{__{'messages.Produit'}}}</th>
                <th style="font-size: 10px;" width="15%">{{__{'messages.PU'}}}</th>
                <th style="font-size: 10px;" width="15%">{{__{'messages.Qte'}}}</th>
                <th style="font-size: 10px;" width="20%">{{__{'messages.Montant'}}}</th>
            </tr>
            </thead>
            <tbody>
            <?php $count =1;?>
            @foreach($produits as $produit)
                <tr style="border-collapse: collapse; border: 1px solid">
                    <td  width="51%" style="font-size:10px; border: 1px solid;">{{$produit->libelle}}</td>
                    <td  width="18%" style="font-size:10px; border: 1px solid; text-align: right">{{($produit->pu)}}</td>
                    <td  width="12%" style="font-size:10px; border: 1px solid; text-align: right">{{($produit->qte)}}</td>
                    <td  width="19%" style="font-size:10px; border: 1px solid; text-align: right">{{($produit->mont)}}</td>
                </tr>
            @endforeach
            <?php $count++;?>

            </tbody>
        </table>
        <table class="table-bordered float-right" style="width: 100%; border: 1px solid; border-color: #0b2e13; border-radius: 0px">
            <tr>
                <td colspan="4" style="font-size:10px">{{__{'messages.ASSURANCE'}}} :  <b>{{$vente->nom}}</b> - - - {{__{'messages.MONTANT'}}}  : <b>{{($vente->montant_total)}}</b> </td>
            </tr>
            <tr>
                <td colspan="4" style="font-size:10px">{{__{'messages.PRISE EN CHARGE'}}} : <b>{{($vente->prise_en_charge)}}</b> - - - {{__{'messages.NET A PAYER'}}}  : <b>{{($vente->net_apayer)}}</b></td>
            </tr>
            <tr>
                <td colspan="4" style="font-size:10px">{{__{'messages.MONTANT RECU'}}}: <b>{{($vente->montant_recu)}}</b> - - - {{$texte}}  : <b>{{($reste)}}</b></td>
            </tr>
        </table>
        <table border="0">
            <tr>
                <td colspan="4" style="font-size:10px; text-align: center">{{__('messages.Bonne guerison')}} </td>
            </tr>
            <tr>
                <td colspan="4" style="font-size:12px; text-align: center">{{__('messages.Ceci est un dupplicata et non un recu original')}} </td>
            </tr>
        </table>

        <div style="page-break-after: always"></div>

        <table>
            <tr>
                <td style="text-align: center; font-size: 15px;">{{__('messages.NON ENCAISSE')}}</td>
                <td style="text-align: center; font-size: 15px;color: #f80202">{{$entete}}</td>
            </tr>
            <tr>
                <td width="15%">
                    <img src="{{asset('images/logo.png')}}" width="80" height="40">
                </td>
                <td width="85%">
                    <div style="font-size: 15px;">{{$centre->nom_centre}}</div>
                    <div style="font-size: 8px;">{{$centre->services}}</div>
                    <div style="font-size: 8px;">{{$centre->adresse}}</div>
                    <div style="font-size: 8px;">{{$centre->telephone}}</div>
                </td>
            </tr>
        </table>
        <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
            <tr>
                <td style="font-size: 10px;">DUPLICATA N° :<b>{{$vente->code}}</b>  /  {{__{'messages.Date'}}} : {{$date_vente.' '.$vente->heure_vente}}</td>
            </tr>
            <tr>
                <td style="font-size: 10px">{{__{'messages.Caissier'}}} :<b>{{Auth::user()->name}}</b>/ {{__{'messages.Patient'}}} :<b>{{$vente->nom_prenom}}</td>
            </tr>
        </table>

        <table style="width: 100%; border: 1px solid; border-radius: 0px" cellspacing="0" cellpadding="3">
            <thead>
            <tr style="border-radius: 10px; background-color: #E5CC75";>
                <th style="font-size: 10px;" width="50%">{{__{'messages.Produit'}}}</th>
                <th style="font-size: 10px;" width="15%">{{__{'messages.PU'}}}</th>
                <th style="font-size: 10px;" width="15%">{{__{'messages.Qte'}}}</th>
                <th style="font-size: 10px;" width="20%">{{__{'messages.Montant'}}}</th>
            </tr>
            </thead>
            <tbody>
            <?php $count =1;?>
            @foreach($produits as $produit)
                <tr style="border-collapse: collapse; border: 1px solid">
                    <td  width="51%" style="font-size:10px; border: 1px solid;">{{$produit->libelle}}</td>
                    <td  width="18%" style="font-size:10px; border: 1px solid; text-align: right">{{($produit->pu)}}</td>
                    <td  width="12%" style="font-size:10px; border: 1px solid; text-align: right">{{($produit->qte)}}</td>
                    <td  width="19%" style="font-size:10px; border: 1px solid; text-align: right">{{($produit->mont)}}</td>
                </tr>
            @endforeach
            <?php $count++;?>

            </tbody>
        </table>
        <table class="table-bordered float-right" style="width: 100%; border: 1px solid; border-color: #0b2e13; border-radius: 0px">
            <tr>
                <td colspan="4" style="font-size:10px">{{__{'messages.ASSURANCE'}}} :  <b>{{$vente->nom}}</b> - - - {{__{'messages.MONTANT'}}}  : <b>{{($vente->montant_total)}}</b> </td>
            </tr>
            <tr>
                <td colspan="4" style="font-size:10px">{{__{'messages.PRISE EN CHARGE'}}} : <b>{{($vente->prise_en_charge)}}</b> - - - {{__{'messages.NET A PAYER'}}}  : <b>{{($vente->net_apayer)}}</b></td>
            </tr>
            <tr>
                <td colspan="4" style="font-size:10px">{{__{'messages.MONTANT RECU'}}}: <b>{{($vente->montant_recu)}}</b> - - - {{$texte}}  : <b>{{($reste)}}</b></td>
            </tr>
        </table>
        <table border="0">
            <tr>
                <td colspan="4" style="font-size:10px; text-align: center;font-style: italic">{{__('messages.Bonne guerison')}}</td>
            </tr>
            <tr>
                <td colspan="4" style="font-size:12px; text-align: center">{{__('messages.Ceci est un dupplicata et non un recu original')}} </td>
            </tr>
        </table>
        <table>
            <tr>
                <td width="33%">
                    <p><a href="{{route('vente.histo')}}" class='btn btn-danger'><i class='fa fa-close'></i>{{__('messages.Terminer')}}</a></p></td>
            </tr>
        </table>


    </main>
@endsection
@section('extra-js')
    <script language="JavaScript">
        function fermer() {
            window.opener = false;
            self.close();
        }

        function mafonction() {
            window.print();
        }

        $(document).ready(function(){

        });
    </script>
@endsection

