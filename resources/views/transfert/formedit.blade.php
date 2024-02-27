<?php
use App\Http\Controllers\TransfertController;
    $options = ['method'=>'put','url'=>action([TransfertController::class,'update'],$transfert)];
?>

{!! Form::model($transfert,$options) !!}
    <table>
        <tr>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Date Transfert')) !!}
                    {!! Form::date('tr_date',date('Y-m-d'),['class'=>'form-control','required'=>'required']) !!}
                </div>
            </td>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Code Transfert')) !!}
                    {!! Form::text('tr_num',$tr_num,['class'=>'form-control','readonly','id'=>'tr_num']) !!}
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="form-group">
                    <label>{{__('messages.Magasin Destination')}}</label>
                    {!! Form::select('mag_source',$mag_dest,['class'=>'form-control','id'=>'mag_source']) !!}
                    <input type="hidden" name="mag_sour" id="mag_sour">
                </div>
            </td>
            <td>
                <button class="btn btn-success"><i class="fa fa-save"></i>{{__('messages.Enregistrer')}}</button>
            </td>
        </tr>
    </table>

{!! Form::close() !!}
