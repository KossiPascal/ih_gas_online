<?php
    $options = ['method'=>'post','url'=>action([\App\Http\Controllers\TransfertDpsController::class,'store'])];
?>


{!! Form::model($transfert,$options) !!}
<div class="form-group">
    {!! Form::label(__('messages.Date Transfert')) !!}
    {!! Form::date('date_transfert',date('Y-m-d'),['class'=>'form-control','required'=>'required']) !!}
</div>
<div class="form-group">
    {!! Form::label(__('messages.Code Transfert')) !!}
    {!! Form::text('code',$code,['class'=>'form-control','readonly','id'=>'code']) !!}
</div>
<div class="form-group">
    <select name="reception_dps_id" id="reception_dps_id" class="form-control" onchange="actualiser()">
        @foreach($reception_dps as $key=>$reception_dps)
            <option value= "{!! $reception_dps !!}"> {!! $reception_dps !!} </option>
        @endforeach
    </select>
    <input type="hidden" name="commande_id" id="commande_id" />
</div>
<div class="form-group">
    <button class="btn btn-success"><i class="fa fa-save"></i>{{__('messages.Enregistrer')}}</button>
</div>


{!! Form::close() !!}
