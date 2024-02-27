@extends('layouts.comptalayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')

@section('extra-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">
        <div class="col-12 col-sm-12 col-md-12">
            <div class="col-12 col-sm-6 col-md-6 float-left">
                <h3 class="ml-5">{{__('messages.LISTE DES CREDITS')}}</h3>
            </div>
            <div class="col-12 col-sm-6 col-md-6 float-right">
                <a href="{{route('vente.credit_liste')}}" class="btn btn-danger">{{__('messages.IMPRIMER LA LISTE DES CREDITS')}}</a>
            </div>
        </div>

        <div class="info-box">
            <div class="table-responsive">
                <table id="historiques" class="table table-striped table-bordered data-table">
                    <thead>
                        <tr>
                            <th>{{__('messages.Date')}}</th>
                            <th>{{__('messages.Vente')}}</th>
                            <th>{{__('messages.Montant')}}</th>
                            <th>{{__('messages.Deja paye')}}</th>
                            <th>{{__('messages.Reste')}}</th>
                            <th>{{__('messages.Patient')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $count =1;?>
                    @foreach($les_credits as $credit)
                        <tr>
                            <td>{{$credit->ven_date}}</td>
                            <td>{{$credit->ven_num}}</td>
                            <td>{{$credit->ven_net}}</td>
                            <td>{{$credit->total_reg}}</td>
                            <td>{{$credit->reste}}</td>
                            <td>{{$credit->pat_num}}</td>
                        </tr>
                    @endforeach
                    <?php $count++;?>
                    </tbody>
                </table>
            </div>

            <!--Ajouter un produit -->
            <div id="reglementModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{__('messages.Nouveau Reglement')}}</h4>
                        </div>
                        <div class="modal-body">
                            <span id="form_result"></span>
                            <form method="post" id="reg_form" class="form-horizontal">
                                @csrf

                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Date Reglement')}} </label>
                                    <input type="date" name="reg_date" id="reg_date"  value="{{date('Y-m-d')}}" class="form-control" required="required"/>
                                </div>


                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Montant de la vente')}}</label>
                                    <input type="text" name="ven_net" id="ven_net" class="form-control" readonly/>
                                    <label class="control-label col-md-12" >{{__('messages.Montant deja regle')}}</label>
                                    <input type="text" name="mont_reg" id="mont_reg" class="form-control" readonly/>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Montant a regler')}}</label>
                                    <input type="text" name="reg_mont" id="reg_mont" class="form-control" required="required"/>
                                </div>

                                <div class="form-group" align="center">
                                    <input type="hidden" name="reg_num" id="reg_num" />
                                    <input type="submit" name="action_button" id="action_button" class="btn btn-success" value="{{__('messages.Enregistrer')}}" />
                                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Quitter')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
@endsection

@section('extra-js')
    <script>
        $(document).ready(function(){
            $('#historiques').DataTable({
                processing: true,
                serverSide: true
            });
        });
    </script>
@endsection
