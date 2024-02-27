    <?php
        use App\Http\Controllers\UserController;
        if ($user->id){
            $options = ['method'=>'put','url'=>action([UserController::class,'store'],$user)];
        }else{
            $options = ['method'=>'post','url'=>action([UserController::class,'store'])];
        }
    ?>


    {!! Form::model($user,['method'=>'put','url'=>action('UserController@update',$user)]) !!}
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
                {!! Form::text('password',null,['class'=>'form-control','required'=>'required']) !!}
            </div>

            <div class="form-group">
                {!! Form::label('ut',(__('messages.Type Utilisateur'))) !!}
                {!! Form::select('ut',$tus,null,['class'=>'form-control']) !!}
            </div>

            <button class="btn btn-primary"><i class="fa fa-save"></i>{{__('messages.Enregistrer')}}</button>

    {!! Form::close() !!}
