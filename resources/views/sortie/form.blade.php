<?php
use App\Http\Controllers\SortieController;
if ($sortie->sor_num){
    $options = ['method'=>'put','url'=>action([SortieController::class,'update'],$sortie)];
}else{
    $options = ['method'=>'post','url'=>action([SortieController::class,'store'])];
}
?>

{!! Form::model($sortie,$options) !!}
    <table>
        <tr>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Date sortie')) !!}
                    {!! Form::date('sor_date',date('Y-m-d'),['class'=>'form-control','required'=>'required']) !!}
                </div>
            </td>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Code sortie')) !!}
                    {!! Form::text('sor_num',$sor_num,['class'=>'form-control','readonly','id'=>'sor_num']) !!}
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="form-group">
                    {!! Form::label(__('messages.Motif du sortie')) !!}
                    {!! Form::text('sor_motif',null,['class'=>'form-control']) !!}
                    <input type="hidden" name="mag_num" id="mag_num" readonly value="0">
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <button class="btn btn-success"><i class="fa fa-save"></i>{{__('messages.Enregistrer')}}</button>
            </td>
        </tr>
    </table>

{!! Form::close() !!}
