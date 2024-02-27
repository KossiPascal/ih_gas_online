<?php
    $options = ['method'=>'post','url'=>action([\App\Http\Controllers\ReceptionController::class,'store'])];
?>


{!! Form::model($reception,$options) !!}
<div class="form-group">
    {!! Form::label(__('messages.Date Reception')) !!}
    {!! Form::date('date_reception',date('Y-m-d'),['class'=>'form-control','required'=>'required']) !!}
</div>
<div class="form-group">
    {!! Form::label(__('messages.Code Reception')) !!}
    {!! Form::text('code',$code,['class'=>'form-control','readonly','id'=>'code']) !!}
</div>
<div class="form-group">
    <label>{{__('messages.Magasin')}}</label>
    <select name="magasin_id" id="magasin_id" class="form-control">
        @foreach($magasins as $key=>$magasin)
            <option value= "{!! $magasin !!}"> {!! $magasin !!} </option>
        @endforeach
    </select>
    <input type="hidden" name="cmdenum" id="cmdenum">
    <input type="hidden" name="reception_id" id="reception_id">
</div>
<div class="form-group">
    <button class="btn btn-success"><i class="fa fa-save"></i>{{__('messages.Enregistrer')}}</button>
</div>


{!! Form::close() !!}
