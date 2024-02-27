@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: Gestion Produit')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <h3 class="ml-5">{{__('messages.EDITER UN  PROFIL')}}</h3>
        <div class="col-12 col-sm-12 col-md-12">
            <div class="col-12 col-md-3 float-left">
            <a href="{{route('user.index')}}" class="btn btn-success"><i class="fa fa-user"></i> {{__('messages.Nouveau Profil')}}</a>
            </div>
            <div class="col-12 col-sm-3 col-md-3 float-left">
                    <a href="{{route('user.user')}}" class="btn btn-primary"><i class="fa fa-user"></i> {{__('messages.Les Utilisateurs')}}</a>
            </div>
            <div class="col-12 col-sm-3 col-md-3 float-left">
                    <a href="#" class="btn btn-warning"><i class="fa fa-database"></i> {{__('messages.Initialisation de la base')}}</a>
            </div>
            <div class="col-12 col-sm-3 col-md-3 float-right">
                <a href="{{route('centre.index')}}" class="btn btn-danger"><i class="fa fa-info"></i> {{__('messages.Information de la structure')}}</a>
            </div>
        </div>
        <br>
        <br>
        <div class="card">
            <div class="card-body">
                <form method="post" id="user_form" class="form-horizontal" action="{{route('user.updateprofil')}}">
                    @csrf
                    <input type="hidden" class="form-control" name="id" value="{{$profil->profil_id}}">

                    <div class="form-group">
                            <input type="hidden" class="form-control" name="profil_id" value="{{$profil->profil_id}}" id="{{$profil->profil_id}}">
                            <input type="text" class="form-control" name="nom" value="{{$profil->nom}}" id="{{$profil->nom}}">
                    </div>
                    <div class="form-group">
                        <title class="title">{{__('messages.Cocher les droits du profil')}}</title>
                        @foreach($droits as $droit)
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" name="droits[]" value="{{$droit->droit_id}}" id="{{$droit->droit_id}}" @foreach($droit_profil as $profilDroit) @if($profilDroit->droit_id==$droit->droit_id) checked @endif @endforeach>
                                <label for="{{$droit->droit_id}}" class="form-check-label" >{{$droit->nom}}</label>
                            </div>
                        @endforeach
                    </div>

                    <div class="form-group" align="center">
                        <input type="hidden" name="profil_id" id="profil_id" />
                        <input type="submit" name="action_button" id="action_button" class="btn btn-success" value="{{__('messages.Enregistrer')}}" />
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Quitter')}}</button>
                    </div>
                </form>
            </div>

        </div>
    </main>
@endsection

@section('extra-js')
    <script>

    </script>
@endsection
