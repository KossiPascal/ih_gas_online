<?php
use App\Http\Controllers\ConfectionkitController;
if ($kit->ck_num){
    $options = ['method'=>'put','url'=>action([[ConfectionkitController::class,'update']],$kit)];
}else{
    $options = ['method'=>'post','url'=>action([ConfectionkitController::class,'store'])];
}
?>

{!! Form::model($kit,$options) !!}
    <table>
        <tr>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Date Confection')) !!}
                    {!! Form::date('ck_date',date('Y-m-d'),['class'=>'form-control','required'=>'required']) !!}
                </div>
            </td>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Code Confection')) !!}
                    {!! Form::text('ck_num',$ck_num,['class'=>'form-control','readonly','id'=>'ck_num']) !!}
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="form-group">
                    <label>{{__('messages.Magasin de destination')}}</label>
                    <select name="mag_dest" id="mag_dest" class="form-control">
                        @foreach($magasins as $key=>$magasin)
                            <option value= "{!! $magasin !!}"> {!! $magasin !!} </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="mag_sour" id="mag_sour" readonly value="0">
                </div>
            </td>
            <td>
                <div class="form-group">
                    <label>{{__('messages.Kit concu')}}</label>
                    <select name="pdt_num" id="pdt_num" class="form-control" onchange="rechKit()">
                        @foreach($kits as $key=>$kit)
                            <option value= "{!! $kit !!}"> {!! $kit !!} </option>
                        @endforeach
                    </select>
                    {!! Form::hidden('pa',0,['class'=>'form-control','id'=>'pa']) !!}
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Quantite concue')) !!}
                    {!! Form::text('ck_qte',null,['class'=>'form-control']) !!}
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
