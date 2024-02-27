<?php
    use App\Http\Controllers\ConfectionkitController;
    if ($confectionkit->ck_num){
        $options = ['method'=>'put','url'=>action([[ConfectionkitController::class,'update']],$confectionkit)];
    }else{
        $options = ['method'=>'post','url'=>action([ConfectionkitController::class,'store'])];
    }
?>

{!! Form::model($confectionkit,$options) !!}
    <div class="form-group">
        {!! Form::label('ck_date',(__('messages.Date de confection'))) !!}
        {!! Form::date('ck_date',date('Y-m-d'),['class'=>'form-control','required'=>'required']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('ck_num',(__('messages.Code de confection'))) !!}
        {!! Form::text('ck_num',$ck_num,['class'=>'form-control','readonly','id'=>'ck_num']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('pdt_num',(__('messages.Selection kit'))) !!}
        {!! Form::select('pdt_num',$kits,null,['class'=>'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('ck_qte',(__('messages.Nombre kit concu'))) !!}
        {!! Form::text('ck_qte',null,['class'=>'form-control','required'=>'required','id'=>'ck_qte']) !!}
    </div>
    <button class="btn btn-success"><i class="fa fa-save"></i>{{__('messages.Enregistrer')}}</button>
    <div class="form-group">
        <p>&nbsp;</p>
    </div>


{!! Form::close() !!}
