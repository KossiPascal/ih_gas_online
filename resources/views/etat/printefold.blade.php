@extends('layouts.printlayout')

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">
        <table>
            <tr>
                <td width="15%">
                    <img src="../public/images/logo.png" width="80" height="40">
                </td>
                <td width="85%">
                    <div style="font-size: 15px;">{{$centre->nom}}</div>
                    <div style="font-size:10px;">{{$centre->service}}</div>
                    <div style="font-size:15px;">{{$centre->adresse}}</div>
                    <div style="font-size:15px;">{{$centre->telephone}}</div>
                </td>
            </tr>
        </table>
        <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
            <tr>
                <td width="100%" style="font-size: 15px; text-align: center">{{__('messages.ETAT FINANCIER DE LA PERIODE DU')}} : <b>{{$debut}}</b>  {{__('messages.AU')}} <b>{{$fin}}</b> </td>
            </tr>
            <tr>
                <td width="100%" style="font-size: 15px;">USER :{{Auth::user()->name}} </td>
            </tr>
        </table>

        @foreach($catcon as $categorie)
            {{$ventes = DB::table('concernerventes')
            ->join('produits','produits.pdt_num','=','concernerventes.pdt_num')
            ->join('ventes','ventes.ven_num','=','concernerventes.ven_num')
            ->selectRaw('produits.pdt_lib,concernerventes.pu,sum(concernerventes.qte) as qte, sum(concernerventes.mont) as mont, sum(concernerventes.pec) as pec, sum(concernerventes.net) as net')
            ->whereBetween('ventes.ven_date', array($debut, $fin))
            ->where('ventes.user_id','=',Auth::user()->id)
            ->where('produits.cat_num','=',$categorie->cat_num)
            ->groupBy('produits.pdt_lib','concernerventes.pu')
            ->get()}}

            {{$total_cat = DB::table('concernerventes')
            ->join('produits','produits.pdt_num','=','concernerventes.pdt_num')
            ->join('ventes','ventes.ven_num','=','concernerventes.ven_num')
            ->selectRaw('produits.cat_num, sum(concernerventes.mont) as mont, sum(concernerventes.pec) as pec, sum(concernerventes.net) as net')
            ->whereBetween('ventes.ven_date', array($debut, $fin))
            ->where('ventes.user_id','=',Auth::user()->id)
            ->where('produits.cat_num','=',$categorie->cat_num)
            ->groupBy('produits.cat_num')
            ->get()}}
            {{$total_cat = (object) $total_cat[0]}}

            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">

                <tr style="border-radius: 15px; background-color: #E5CC75";>
                    <th style="font-size: 15px;" width="50%">{{$categorie->cat_num}}</th>
                </tr>
            </table>
            <table style="width: 100%; border: 1px solid; border-radius: 10px" cellspacing="0" cellpadding="3">
                <thead>
                <tr style="border-radius: 15px; background-color: #A2ACC4";>
                    <th style="font-size: 15px;" width="35%">{{__{'messages.Produit'}}}</th>
                    <th style="font-size: 15px;" width="12%">{{__{'messages.PU'}}}</th>
                    <th style="font-size: 15px;" width="10%">{{__{'messages.Qte'}}}</th>
                    <th style="font-size: 15px;" width="14%">{{__{'messages.Montant'}}}</th>
                    <th style="font-size: 15px;" width="14%">{{__{'messages.Prise en charge'}}}</th>
                    <th style="font-size: 15px;" width="14%">{{__{'messages.Net'}}}</th>
                </tr>
                </thead>
                <tbody>';

                @foreach($ventes as $produit){
                    <tr style="border-collapse: collapse; border: 1px solid">
                        <td  width="35%" style="font-size:15px; border: 1px solid;">{{$produit->pdt_lib}}</td>
                        <td  width="12%" style="font-size:15px; border: 1px solid; text-align: right">{{getPrice3($produit->pu)}}</td>
                        <td  width="10%" style="font-size:15px; border: 1px solid; text-align: right">{{getPrice3($produit->qte)}}</td>
                        <td  width="15%" style="font-size:15px; border: 1px solid; text-align: right">{{getPrice3($produit->mont)}}</td>
                        <td  width="15%" style="font-size:15px; border: 1px solid; text-align: right">{{getPrice3($produit->pec)}}</td>
                        <td  width="15%" style="font-size:15px; border: 1px solid; text-align: right">{{getPrice3($produit->net)}}</td>
                    </tr>
                @endforeach
                <tr style="border-collapse: collapse; border: 1px solid; background-color: #C5C8CE">
                    <td colspan="3"  width="51%" style="font-size:15px; border: 1px solid"><b>{{__('messages.Total Categories')}}</b></td>
                    <td  width="19%" style="font-size:15px; border: 1px solid; text-align: right"><b>{{getPrice3($total_cat->mont)}}</b></td>
                    <td  width="19%" style="font-size:15px; border: 1px solid; text-align: right"><b>{{getPrice3($total_cat->pec)}}</b></td>
                    <td  width="19%" style="font-size:15px; border: 1px solid; text-align: right"><b>{{getPrice3($total_cat->net)}}</b></td>
                </tr>
                </tbody>
            </table>
        @endforeach

            <table class="table-bordered" style="width: 100%; border: 1px solid; border-color: #000000; border-radius: 0px">
                <tr>
                    <td width="33%" style="font-size: 17px;">{{__('messages.Recette Totale')}} : <b>{{getPrice3($vmomt)}}</b> </td>
                    <td width="33%" style="font-size: 17px;">{{__('messages.Prise en  charge')}}: <b>{{getPrice3($vpec)}}</b> </td>
                    <td width="33%" style="font-size: 17px;">{{__('messages.Recette net')}} : <b>{{getPrice3($vnet)}}</b> </td>
                </tr>
            </table>

    </main>
@endsection
@section('extra-js')
    <script language="JavaScript">

        function mafonction() {
            //window.print();
            //window.location.replace('http://192.168.1.2/PCSOFT_V4/public/vente')
        }

        $(document).ready(function(){

        });
    </script>
@endsection

