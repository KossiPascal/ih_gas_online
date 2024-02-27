@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: Gestion Fournisseur')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: underline; margin-top: 5px;">
        <h3 class="ml-5">{{__('messages.MON COMPTE')}}</h3>
        <div class="panel panel-info">
            <div class="panel-heading"></div>
            <div class="panel-body">
                <div class="col-12 col-sm-4 col-md-4 float-left">
                    {!! Form::model($user,['method'=>'put','url'=>action('UserController@updatemoncompte',$user)]) !!}
                    <div class="form-group">
                        {!! Form::text('id',null,['class'=>'form-control','readonly']) !!}
                    </div>

                    <div class="form-group">
                        {!! Form::label('name',(__('messages.Nom et Prenom'))) !!}
                        {!! Form::text('name',null,['class'=>'form-control','required'=>'required']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('email',(__('messages.Compte utilsateur'))) !!}
                        {!! Form::text('email',null,['class'=>'form-control','required'=>'required']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('password',(__('messages.Mot de passe'))) !!}
                        {!! Form::password('password',null,['class'=>'form-control','required'=>'required']) !!}
                    </div>
                    <button class="btn btn-primary"><i class="fa fa-save"></i>{{__('messages.Enregistrer')}}</button>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>

    </main>
@endsection
