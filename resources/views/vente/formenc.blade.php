<?php
    use App\Http\Controllers\VenteController;
    $options = ['method'=>'post','url'=>action([VenteController::class,'validerCaisse'])];
?>


{!! Form::model($vente,$options) !!}
    <table class="table">
        <tr>
            <td width="50%">
                <div class="form-group">
                    {!! Form::label('ven_date','Date') !!}
                    {!! Form::date('ven_date',$vente->ven_date,['class'=>'form-control','required'=>'required','id'=>'ven_date','onChange'=>'rech_code()']) !!}
                </div>
            </td>
            <td width="50%">
                <div class="form-group">
                    {!! Form::label(__('messages.Taux de prise en charge')) !!}
                    {!! Form::text('mut_taux',$vente->mut_taux,['class'=>'form-control','readonly','id'=>'mut_taux']) !!}
                    <input type="hidden" name="mut_num" id="mut_num" readonly>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="form-group">
                    {!! Form::label('ven_num',(__('messages.Vente Code'))) !!}
                    {!! Form::text('ven_num',$vente->ven_num,['class'=>'form-control','readonly','id'=>'ven_num']) !!}
                </div>
            </td>
            <td>
                <div class="form-group">
                    {!! Form::label('ven_mont',(__('messages.Montant total'))) !!}
                    {!! Form::text('ven_mont',$vente->ven_mont,['class'=>'form-control','readonly','id'=>'ven_mont']) !!}
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="form-group">
                    {!! Form::label('ven_pec',(__('messages.Montant Prise en charge'))) !!}
                    {!! Form::text('ven_pec',$vente->ven_pec,['class'=>'form-control','readonly','id'=>'ven_pec']) !!}
                </div>
            </td>
            <td>
                <div class="form-group">
                    {!! Form::label('ven_net',(__('messages.Net a payer'))) !!}
                    {!! Form::text('ven_net',$vente->ven_net,['class'=>'form-control','readonly','id'=>'ven_net']) !!}
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="form-group">
                    {!! Form::label('ven_rem',(__('messages.Montant recu'))) !!}
                    {!! Form::text('ven_rem',$vente->ven_rem,['class'=>'form-control','id'=>'ven_rem','required'=>'required']) !!}
                </div>
            </td>
            <td>
                <div class="form-group">
                    {!! Form::label('ven_rel',(__('messages.Reliquat'))) !!}
                    {!! Form::text('ven_rel',0,['class'=>'form-control','readonly','id'=>'ven_rel']) !!}
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
                    {!! Form::text('pat_num',$vente->pat_num,['class'=>'form-control','readonly']) !!}
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
