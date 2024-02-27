<?php
use App\Http\Controllers\CommandeController;
if ($commande->commande_id){
    $options = ['method'=>'put','url'=>action([CommandeController::class,'update'],$commande)];
}else{
    $options = ['method'=>'post','url'=>action([CommandeController::class,'store'])];
}
?>

<div class="col-md-12">
    {!! Form::model($commande,$options) !!}
        <table class="table-responsive">
            <tr>
                <td>
                    <div class="form-group">
                        {!! Form::label(__('messages.Date Commande')) !!}
                        {!! Form::date('date_commande',date('Y-m-d'),['class'=>'form-control','required'=>'required']) !!}
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        {!! Form::label(__('messages.Code Commande')) !!}
                        {!! Form::text('code',$code,['class'=>'form-control','readonly','id'=>'code']) !!}
                        <input type="hidden" name="commande_id" id="commande_id" />
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="form-group">
                        {!! Form::label(__('messages.Montant Commande')) !!}
                        {!! Form::text('montant',null,['class'=>'form-control','readonly','id'=>'montant']) !!}
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <label>{{__('messages.Fournisseur')}}</label>
                        <select name="fournisseur_id" id="fournisseur_id" class="form-control">
                            @foreach($fournisseurs as $key=>$fournisseur)
                                <option value= "{!! $fournisseur !!}"> {!! $fournisseur !!} </option>
                            @endforeach
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                </td>
                <td>
                    <button class="btn btn-success"><i class="fa fa-save"></i>{{__('messages.Enregistrer')}}</button>
                </td>
            </tr>
        </table>
        <br><br>

    {!! Form::close() !!}
</div>

