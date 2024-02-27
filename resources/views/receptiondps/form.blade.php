<?php
    $options = ['method'=>'post','url'=>action([\App\Http\Controllers\ReceptionDpsController::class,'store'])];
?>


{!! Form::model($reception,$options) !!}
<div class="form-group">
    {!! Form::label(__('messages.Date Reception')) !!}
    {!! Form::date('date_reception',date('Y-m-d'),['class'=>'form-control','required'=>'required']) !!}
</div>
<div class="form-group">
    {!! Form::label(__('messages.Code Reception')) !!}
    {!! Form::text('code',$code,['class'=>'form-control','readonly','id'=>'code']) !!}
    <input type="hidden" name="cmdenum" id="cmdenum" />
</div>
<div class="form-group">
    <button class="btn btn-success"><i class="fa fa-save"></i>{{__('messages.Enregistrer')}}</button>
</div>


{!! Form::close() !!}
