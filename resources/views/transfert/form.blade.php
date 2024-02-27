<?php
use App\Http\Controllers\TransfertController;
if ($transfert->code){
    $options = ['method'=>'put','url'=>action([TransfertController::class,'update'],$transfert)];
}else{
    $options = ['method'=>'post','url'=>action([TransfertController::class,'store'])];
    }
?>

{!! Form::model($transfert,$options) !!}
    <table>
        <tr>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Date Transfert')) !!}
                    {!! Form::date('date_transfert',date('Y-m-d'),['class'=>'form-control','required'=>'required']) !!}
                </div>
            </td>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Code Transfert')) !!}
                    {!! Form::text('code',$code,['class'=>'form-control','readonly','id'=>'code']) !!}
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="form-group">
                    <label>{{__('messages.Magasin Destination')}}</label>
                    <select name="magasin_destination" id="magasin_destination" class="form-control">
                        @foreach($magasins as $key=>$magasin)
                            <option value= "{!! $magasin !!}"> {!! $magasin !!} </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="magasinsource" id="magasinsource">
                </div>
            </td>
            <td>
                <button class="btn btn-success"><i class="fa fa-save"></i>{{__('messages.Enregistrer')}}</button>
            </td>
        </tr>
    </table>
<br>

{!! Form::close() !!}
