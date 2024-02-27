<?php
use App\Http\Controllers\VenteController;
if ($vente->vente_id){
    $options = ['method'=>'put','url'=>action([VenteController::class,'update'],$vente)];
}else{
    $options = ['method'=>'post','url'=>action([VenteController::class,'store'])];
}
?>


{!! Form::model($vente,$options) !!}
    <table class="table">
        <tr>
            <td width="50%">
                <div class="form-group">
                    {!! Form::label('Date') !!}
                    {!! Form::date('date_vente',date('Y-m-d'),['class'=>'form-control','required'=>'required','id'=>'date_vente','onChange'=>'rech_code()']) !!}
                </div>
            </td>
            <td width="50%">
                <div class="form-group">
                    {!! Form::label(__('messages.Taux de prise en charge')) !!}
                    {!! Form::text('taux',0,['class'=>'form-control','readonly','id'=>'taux']) !!}
                    <input type="hidden" name="assurance_id" id="assurance_id" readonly>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Vente Code')) !!}
                    {!! Form::text('code',$code,['class'=>'form-control','readonly','id'=>'code']) !!}
                </div>
            </td>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Montant total')) !!}
                    {!! Form::text('montant_total',0,['class'=>'form-control','readonly','id'=>'montant_total']) !!}
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Montant Prise en charge')) !!}
                    {!! Form::text('prise_en_charge',0,['class'=>'form-control','readonly','id'=>'prise_en_charge']) !!}
                </div>
            </td>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Net a payer')) !!}
                    {!! Form::text('net_apayer',0,['class'=>'form-control','readonly','id'=>'net_apayer']) !!}
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Montant recu')) !!}
                    {!! Form::text('montant_recu',0,['class'=>'form-control','id'=>'montant_recu']) !!}
                </div>
            </td>
            <td>
                <div class="form-group">
                    {!! Form::label(__('messages.Reliquat')) !!}
                    {!! Form::text('reliquat',0,['class'=>'form-control','readonly','id'=>'reliquat']) !!}
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
                    {!! Form::text('nom_prenom_patient','AMBULANT',['class'=>'form-control','required'=>'required','id'=>'nom_prenom_patient']) !!}
                    <input type="hidden" name="patient_id" id="patient_id" value="1">
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
