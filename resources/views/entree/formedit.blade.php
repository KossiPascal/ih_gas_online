<?php
    $options = ['method'=>'put','url'=>action([\App\Http\Controllers\ReceptionController::class,'update'],$reception)];
?>


{!! Form::model($reception,$options) !!}
<table class="table">
    <tr>
        <td>
            <div class="form-group">
                {!! Form::label(__('messages.Date Reception')) !!}
                {!! Form::date('rec_date',$reception->rec_date,['class'=>'form-control','required'=>'required']) !!}
            </div>
        </td>
        <td>
            <div class="form-group">
                {!! Form::label(__('messages.Code Reception')) !!}
                {!! Form::text('rec_num',$rec_num,['class'=>'form-control','readonly','id'=>'rec_num']) !!}
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="form-group">
                {!! Form::label(__('messages.Commande')) !!}
                {!! Form::text('cmde_num',$commandes,['class'=>'form-control','readonly','id'=>'cmde_num']) !!}
            </div>
        </td>
        <td>
            <div class="form-group">
                {!! Form::label(__('messages.Magasin')) !!}
                {!! Form::text('magnum',$magasins->mag_lib,['class'=>'form-control','readonly']) !!}
                {!! Form::hidden('mag_num',$magasins->mag_num,['class'=>'form-control','readonly','id'=>'mag_num']) !!}
            </div>
        </td>
    </tr>
</table>

<div class="form-group">
    <button class="btn btn-success"><i class="fa fa-save"></i>{{__('messages.Enregistrer')}}</button>
</div>


{!! Form::close() !!}
