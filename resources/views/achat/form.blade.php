<?php
use App\Http\Controllers\AchatController;
if ($reception->reception_id){
    $options = ['method'=>'put','url'=>action([AchatController::class,'update'],$reception)];
}else{
    $options = ['method'=>'post','url'=>action([AchatController::class,'store'])];
}
?>


{!! Form::model($reception,$options) !!}
<table>
    <tr>
        <td>
            <div class="form-group">
                {!! Form::label(__('messages.Date Achat')) !!}
                {!! Form::date('date_reception',date('Y-m-d'),['class'=>'form-control','required'=>'required']) !!}
            </div>
        </td>
        <td>
            <div class="form-group">
                {!! Form::label(__('messages.Code Achat')) !!}
                {!! Form::text('code',$reception_id,['class'=>'form-control','readonly','id'=>'code']) !!}
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="form-group">
                <label>{{__('messages.Magasin')}}</label>
                <select name="magasin_id" id="magasin_id" class="form-control">
                    @foreach($magasins as $key=>$magasin)
                        <option value= "{!! $magasin !!}"> {!! $magasin !!} </option>
                    @endforeach
                </select>
            </div>
        </td>
        <td>

        </td>
    </tr>
    <tr>
        <td>
            <div class="form-group">
                <button class="btn btn-danger"><i class="fa fab-backdrop" id="annuler"></i>{{__('messages.Annuler')}}</button>
            </div>
        </td>
        <td>
            <div class="form-group">
                <button class="btn btn-success"><i class="fa fa-save"></i>{{__('messages.Enregistrer')}}</button>
            </div>
        </td>
    </tr>
</table>


{!! Form::close() !!}
