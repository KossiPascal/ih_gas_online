@extends('layouts.adminlayout')
@section('title','PCSOFT V4: BON DE COMMANDE')

@section('extra-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">

        <div class="col-md-12 float-left">
        <h1>{{__('messages.Selectionner stock vente')}}</h1>
            <table id="liste_produit" class="display table table-striped table-bordered data-table">
                <thead>
                    <tr>
                        <th>{{__('messages.Libelle')}}</th>
                        <th>{{__('messages.Type')}}</th>
                        <th>{{__('messages.Selectionner')}}</th>
                    </tr>
                </thead>
                <tbody>
                <?php $count =1;?>
                @foreach($magasins as $magasin)
                    <tr>
                        <td>{{$magasin->libelle}}</td>
                        <td style="text-align: left">{{($magasin->type)}}</td>
                        <td><a href="{{route('vente.mag_source',$magasin->magasin_id)}}" class="btn btn-danger">{{__('messages.Selectionner')}}</a></td>
                    </tr>
                @endforeach
                <?php $count++;?>
                </tbody>
            </table>
        </div>
    </main>
@endsection

@section('extra-js')

@endsection
