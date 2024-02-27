<?php
use App\Http\Controllers\ASCController;
if ($vente->ven_num){
    $options = ['method'=>'put','url'=>action([ASCController::class,'update'],$vente)];
}else{
    $options = ['method'=>'post','url'=>action([ASCController::class,'store'])];
}
?>


{!! Form::model($vente,$options) !!}
    <table class="table">
        <tr>
            <td width="50%">
                <div class="form-group">
                    {!! Form::label('ven_date','Date') !!}
                    {!! Form::date('ven_date',date('Y-m-d'),['class'=>'form-control','required'=>'required','id'=>'ven_date','onChange'=>'rech_code()']) !!}
                </div>
            </td>
            <td>
                <div class="form-group">
                    {!! Form::label('ven_num',' Code') !!}
                    {!! Form::text('ven_num',$ven_num,['class'=>'form-control','readonly','id'=>'ven_num']) !!}
                </div>
            </td>

        </tr>
        <tr>
            <td width="50%" colspan="2">
                <div class="form-group">
                    <input type="hidden" name="mut_taux" id="mut_taux" readonly value="0">
                    <input type="hidden" name="mut_num" id="mut_num" readonly value="1">
                    <input type="hidden" name="ven_mont" id="ven_mont" readonly>
                    <input type="hidden" name="ven_pec" id="ven_pec" readonly>
                    <input type="hidden" name="ven_net" id="ven_net" readonly>
                    <input type="hidden" name="ven_rem" id="ven_rem" readonly>
                    <input type="hidden" name="ven_rel" id="ven_rel" readonly>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="form-group">
                    <a href="#" class="btn btn-primary newPatien" id="newPatient">{{__('messages.Nouveau patient')}}</a>
                </div>
            </td>
            <td>
                <div class="form-group">
                    {!! Form::text('pat_num','AMBULANT',['class'=>'form-control','required'=>'required']) !!}
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <a href="#" class="btn btn-danger annuler" id="annuler">{{__('messages.Annuler la vente')}}</a>
            </td>

            <td>
                <button class="btn btn-success"><i class="fa fa-save"></i>{{__('messages.Enregistrer')}}</button>
            </td>
        </tr>
    </table>
    <div class="form-group">
        <p>&nbsp;</p>
    </div>

{!! Form::close() !!}
