<?php
use App\Http\Controllers\EntreeController;
if ($entree->ent_num){
    $options = ['method'=>'put','url'=>action([EntreeController::class,'update'],$entree)];
}else{
    $options = ['method'=>'post','url'=>action([EntreeController::class,'store'])];
}
?>

{!! Form::model($entree,$options) !!}
    <table>
        <tr>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Date Entree')) !!}
                    {!! Form::date('ent_date',date('Y-m-d'),['class'=>'form-control','required'=>'required']) !!}
                </div>
            </td>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Code Entree')) !!}
                    {!! Form::text('ent_num',$ent_num,['class'=>'form-control','readonly','id'=>'ent_num']) !!}
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="form-group">
                    {!! Form::label(__('messages.Motif Entree')) !!}
                    {!! Form::text('ent_motif',null,['class'=>'form-control']) !!}
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
