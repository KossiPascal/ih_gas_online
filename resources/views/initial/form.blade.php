<?php
use App\Http\Controllers\InitialisationController;
if ($initialisation->ini_num){
    $options = ['method'=>'put','url'=>action([InitialisationController::class,'update'],$initialisation)];
}else{
    $options = ['method'=>'post','url'=>action([InitialisationController::class,'store'])];
}
?>


{!! Form::model($initialisation,$options) !!}
    <div class="form-group">
        {!! Form::label('ini_date',(__('messages.Date Initialisation'))) !!}
        {!! Form::date('ini_date',date('Y-m-d'),['class'=>'form-control','required'=>'required']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('ini_num',(__('messages.Code regularisation'))) !!}
        {!! Form::text('ini_num',$ini_num,['class'=>'form-control','readonly','id'=>'ini_num']) !!}
    </div>
    <div class="form-group">
        <label>{{__('messages.Magasin')}}</label>
        <select name="mag_num" id="mag_num" class="form-control">
            @foreach($magasins as $key=>$magasin)
                <option value= "{!! $magasin !!}"> {!! $magasin !!} </option>
            @endforeach
        </select>
    </div>

    <button class="btn btn-success"><i class="fa fa-save"></i>{{__('messages.Enregistrer')}}</button>
    <div class="form-group">
        <p>&nbsp;</p>
    </div>


{!! Form::close() !!}
