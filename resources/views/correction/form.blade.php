<?php
use App\Http\Controllers\CSController;
if ($cs->code_cs){
    $options = ['method'=>'put','url'=>action([CSController::class,'update'],$cs)];
}else{
    $options = ['method'=>'post','url'=>action([CSController::class,'store'])];
}
?>


{!! Form::model($cs,$options) !!}
    <table class="table-responsive">
        <tr>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Date Correction')) !!}
                    {!! Form::date('date_cs',date('Y-m-d'),['class'=>'form-control','required'=>'required']) !!}
                    {!! Form::hidden('code_cs',$code_cs,['class'=>'form-control','readonly','id'=>'code_cs']) !!}
                </div>
            </td>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Cout correction')) !!}
                    {!! Form::text('cout',0,['class'=>'form-control','readonly','id'=>'cout']) !!}
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class="form-group">
                    {!! Form::label(__('messages.Motif correction')) !!}
                    {!! Form::text('motif_cs',null,['class'=>'form-control','id'=>'motif_cs']) !!}
                    <input type="hidden" name="magnum" id="magnum">
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <button class="btn btn-success"><i class="fa fa-save"></i>{{__('messages.Enregistrer')}}</button>
            </td>
        </tr>
    </table>

    <div class="form-group">
        <p>&nbsp;</p>
    </div>


{!! Form::close() !!}
