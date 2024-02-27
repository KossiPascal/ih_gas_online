@extends('layouts.printlayout')

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">
        <table width="100%" border="0">
            <tr>
                <td width="33%">
                    <table>
                        <tr>
                            <td width="15%">
                                <img src="{{asset('images/logo.png')}}" width="80" height="40">
                            </td>
                            <td width="85%">
                                <div style="font-size: 15px;">{{$centre->nom}}</div>
                                <div style="font-size: 8px;">{{$centre->service}}</div>
                                <div style="font-size: 8px;">{{$centre->adresse}}</div>
                                <div style="font-size: 8px;">{{$centre->telephone}}</div>
                            </td>
                        </tr>
                    </table>
                    <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                        <tr>
                            <td style="font-size: 10px;">RECU N° :<b>{{$vente->ven_num}}</b>  /  Date : {{$date_vente.' '.$vente->heure_vente}}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px">{{__{'messages.Caissier'}}} :<b>{{Auth::user()->name}}</b>/ Patient :<b>{{$vente->pat_num}}</td>
                        </tr>
                    </table>

                    <table style="width: 100%; border: 1px solid; border-radius: 0px" cellspacing="0" cellpadding="3">
                        <thead>
                        <tr style="border-radius: 0px; background-color: #E5CC75";>
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
                                <td  width="51%" style="font-size:10px; border: 1px solid;">{{$produit->pdt_lib}}</td>
                                <td  width="18%" style="font-size:10px; border: 1px solid; text-align: right">{{getPrice3($produit->pu)}}</td>
                                <td  width="12%" style="font-size:10px; border: 1px solid; text-align: right">{{getPrice3($produit->qte)}}</td>
                                <td  width="19%" style="font-size:10px; border: 1px solid; text-align: right">{{getPrice3($produit->mont)}}</td>
                            </tr>
                        @endforeach
                        <?php $count++;?>

                        </tbody>
                    </table>
                    <table class="table-bordered float-right" style="width: 100%; border: 1px solid; border-color: #0b2e13; border-radius: 0px">
                        <tr>
                            <td colspan="4" style="font-size:10px">{{__{'messages.ASSURANCE'}}} :  <b>{{$vente->mut_lib}}</b> - - - {{__{'messages.MONTANT'}}}  : <b>{{getPrice3($vente->ven_mont)}}</b> </td>
                        </tr>
                        <tr>
                            <td colspan="4" style="font-size:10px">{{__{'messages.PRISE EN CHARGE'}}} : <b>{{getPrice3($vente->ven_pec)}}</b> - - - {{__{'messages.NET A PAYER'}}}  : <b>{{getPrice3($vente->net_apayer)}}</b></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="font-size:10px">{{__{'messages.MONTANT RECU'}}}: <b>{{getPrice3($vente->ven_rem)}}</b> - - - {{__('messages.RELIQUAT')}}  : <b>{{getPrice3($vente->ven_rel)}}</b></td>
                        </tr>
                    </table>
                    <table border="0">
                        <tr>
                            <td colspan="4" style="font-size:10px; text-align: center">{{__('messages.Bonne guerison')}} </td>
                        </tr>
                    </table>
                </td>
                <td width="33%">
                    <table>
                        <tr>
                            <td width="15%">
                                <img src="{{asset('images/logo.png')}}" width="80" height="40">
                            </td>
                            <td width="85%">
                                <div style="font-size: 15px;">{{$centre->nom}}</div>
                                <div style="font-size: 8px;">{{$centre->service}}</div>
                                <div style="font-size: 8px;">{{$centre->adresse}}</div>
                                <div style="font-size: 8px;">{{$centre->telephone}}</div>
                            </td>
                        </tr>
                    </table>
                    <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                        <tr>
                            <td style="font-size: 10px;">RECU N° :<b>{{$vente->ven_num}}</b>  /  Date : {{$date_vente.' '.$vente->heure_vente}}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px">{{__{'messages.Caissier'}}} :<b>{{Auth::user()->name}}</b>/ Patient :<b>{{$vente->pat_num}}</td>
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
                                <td  width="51%" style="font-size:10px; border: 1px solid;">{{$produit->pdt_lib}}</td>
                                <td  width="18%" style="font-size:10px; border: 1px solid; text-align: right">{{getPrice3($produit->pu)}}</td>
                                <td  width="12%" style="font-size:10px; border: 1px solid; text-align: right">{{getPrice3($produit->qte)}}</td>
                                <td  width="19%" style="font-size:10px; border: 1px solid; text-align: right">{{getPrice3($produit->mont)}}</td>
                            </tr>
                        @endforeach
                        <?php $count++;?>

                        </tbody>
                    </table>
                    <table class="table-bordered float-right" style="width: 100%; border: 1px solid; border-color: #0b2e13; border-radius: 0px">
                        <tr>
                            <td colspan="4" style="font-size:10px">{{__{'messages.ASSURANCE'}}} :  <b>{{$vente->mut_lib}}</b> - - - {{__{'messages.MONTANT'}}}  : <b>{{getPrice3($vente->ven_mont)}}</b> </td>
                        </tr>
                        <tr>
                            <td colspan="4" style="font-size:10px">{{__{'messages.PRISE EN CHARGE'}}} : <b>{{getPrice3($vente->ven_pec)}}</b> - - - {{__{'messages.NET A PAYER'}}}  : <b>{{getPrice3($vente->net_apayer)}}</b></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="font-size:10px">{{__{'messages.MONTANT RECU'}}}: <b>{{getPrice3($vente->ven_rem)}}</b> - - - {{__('messages.RELIQUAT')}}  : <b>{{getPrice3($vente->ven_rel)}}</b></td>
                        </tr>
                    </table>
                    <table border="0">
                        <tr>
                            <td colspan="4" style="font-size:10px; text-align: center">{{__('messages.Bonne guerison')}} </td>
                        </tr>
                    </table>
                </td>
                <td width="33%">
                    <table>
                        <tr>
                            <td width="15%">
                                <img src="{{asset('images/logo.png')}}" width="80" height="40">
                            </td>
                            <td width="85%">
                                <div style="font-size: 15px;">{{$centre->nom}}</div>
                                <div style="font-size: 8px;">{{$centre->service}}</div>
                                <div style="font-size: 8px;">{{$centre->adresse}}</div>
                                <div style="font-size: 8px;">{{$centre->telephone}}</div>
                            </td>
                        </tr>
                    </table>
                    <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                        <tr>
                            <td style="font-size: 10px;">RECU N° :<b>{{$vente->ven_num}}</b>  /  Date : {{$date_vente.' '.$vente->heure_vente}}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 10px">{{__{'messages.Caissier'}}} :<b>{{Auth::user()->name}}</b>/ Patient :<b>{{$vente->pat_num}}</td>
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
                                <td  width="51%" style="font-size:10px; border: 1px solid;">{{$produit->pdt_lib}}</td>
                                <td  width="18%" style="font-size:10px; border: 1px solid; text-align: right">{{getPrice3($produit->pu)}}</td>
                                <td  width="12%" style="font-size:10px; border: 1px solid; text-align: right">{{getPrice3($produit->qte)}}</td>
                                <td  width="19%" style="font-size:10px; border: 1px solid; text-align: right">{{getPrice3($produit->mont)}}</td>
                            </tr>
                        @endforeach
                        <?php $count++;?>

                        </tbody>
                    </table>
                    <table class="table-bordered float-right" style="width: 100%; border: 1px solid; border-color: #0b2e13; border-radius: 0px">
                        <tr>
                            <td colspan="4" style="font-size:10px">{{__{'messages.ASSURANCE'}}} :  <b>{{$vente->mut_lib}}</b> - - - {{__{'messages.MONTANT'}}}  : <b>{{getPrice3($vente->ven_mont)}}</b> </td>
                        </tr>
                        <tr>
                            <td colspan="4" style="font-size:10px">{{__{'messages.PRISE EN CHARGE'}}} : <b>{{getPrice3($vente->ven_pec)}}</b> - - - {{__{'messages.NET A PAYER'}}}  : <b>{{getPrice3($vente->net_apayer)}}</b></td>
                        </tr>
                        <tr>
                            <td colspan="4" style="font-size:10px">{{__{'messages.MONTANT RECU'}}}: <b>{{getPrice3($vente->ven_rem)}}</b> - - - {{__('messages.RELIQUAT')}}  : <b>{{getPrice3($vente->ven_rel)}}</b></td>
                        </tr>
                    </table>
                    <table border="0">
                        <tr>
                            <td colspan="4"><div style="font-size:10px; text-align: center">{{__('messages.Bonne guerison')}}</div> </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td width="33%">&nbsp;</td>
                <td width="33%">&nbsp;</td>
                <td width="33%"><p>&nbsp;</p>
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                    <p>&nbsp;</p>
                    <p><a href="{{route('vente.index')}}" class='btn btn-danger'><i class='fa fa-close'></i>Terminer</a></p></td>
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
            //window.location.replace('http://192.168.1.2/PCSOFT_V4/public/vente')
        }

        $(document).ready(function(){

        });
    </script>
@endsection

