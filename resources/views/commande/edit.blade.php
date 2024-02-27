@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')

@section('extra-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">
        <div class="col-12 col-sm-12 col-md-12">
            <h3 class="ml-5">{{__('messages.EDITER UNE COMMANDE')}}</h3>
            <div class="col-12 col-md-4 float-left">
                <a href="{{route('cmde.histo')}}" class="btn btn-danger"><i class="fa fa-user"></i> {{ __('messages.Historique des Commandes') }}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('rec.index')}}" class="btn btn-primary"><i class="fa fa-database"></i>{{ __('messages.Recevoir une cmde') }}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-3 float-right">
                <a href="{{route('rec.histo')}}" class="btn btn-warning"><i class="fa fa-info"></i>{{ __('messages.Historique des Receptions') }}</a>
            </div>
        </div>
        <br><br>
        <div class="col-md-12 float-left">
            <div class="col-md-4 float-left">
                <h5 class="ml-5">{{ __('messages.PRODUITS SELECTIONNES') }}</h5>
            </div>

            <div class="col-md-4 float-left">
                <button type="button" name="create_pdt" id="create_pdt" class="btn btn-success"><i class="fa fa-plus"></i>{{ __('messages.Nouveau Produit') }}</button>
            </div>

            <div class="col-md-4 float-right">
                <h5> {{ __('messages.LISTE DES PRODUITS') }}</h5>
            </div>
        </div>
        <br><br>
        <div class="col-md-12 float-left">
            <div class="col-md-7 float-left">
                <div class="contour_div">
                    <div class="contour_table">
                        <table class="table table-striped table-bordered contour_table" id="pdt_selected">

                        </table>
                    </div>
                    <div class="info-box">
                        @include('commande/form')
                    </div>
                </div>

            </div>

            <div class="col-md-5 float-right">
                <div class="info-box">
                    <div class="table-responsive div_style">
                        <table id="liste_produit" class="display table table-striped table-bordered data-table">
                            <thead>
                            <tr>
                                <th>{{ __('messages.Libelle') }}</th>
                                <th>{{ __('messages.Prix achat') }}</th>
                            </tr>
                            </thead>
                        </table>
                    </div>

                    <!--Ajouter un produit -->
                    <div id="addModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">{{ __('messages.Ajouter un Produit') }}</h4>
                                </div>
                                <div class="modal-body">
                                    <span class="form_result" id="form_result"></span>
                                    <form method="post" id="add_form" class="form-horizontal">
                                        @csrf
                                        <div class="form-group">
                                            <table width="100%">
                                                <tr>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{ __('messages.Reference') }} </label>
                                                        <input type="text" name="reference" id="reference" class="form-control" readonly/>
                                                    </td>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{ __('messages.Libelle') }} </label>
                                                        <input type="text" name="nom_commercial" id="nom_commercial" class="form-control" readonly/>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{ __('messages.Quantite') }}: </label>
                                                        <input type="text" name="qte" id="qte" class="form-control" required="required" value="0"/>
                                                    </td>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{ __('messages.Prix achat') }} </label>
                                                        <input type="text" name=prix_achat id=prix_achat class="form-control" value="0" required="required"/>
                                                    </td>
                                                </tr>
                                            </table>

                                        </div>
                                        <div class="form-group" align="center">
                                            <input type="hidden" name="produit_id" id="produit_id" />
                                            <input type="hidden" name="hidden_idcon" id="hidden_idcon" />
                                            <input type="hidden" name="hidden_code" id="hidden_code" />
                                            <input type="submit" name="action_button" id="action_button" class="btn btn-success" value="{{ __('messages.Ajouter') }}" />
                                            <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{ __('messages.Annuler') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Creer un nouveau produit -->
                    <div id="produitModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">{{ __('messages.Creer un nouveau produit') }}</h4>
                                </div>
                                <div class="modal-body">
                                    <span id="formresult"></span>
                                    <form method="post" id="pdtform" class="form-horizontal">
                                        @csrf
                                        <table class="responsive-table table">
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-12" >{{ __('messages.Reference Produit') }} </label>
                                                        <input type="text" name="referencepdt" id="pdtreference" class="form-control"/>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-12" >{{ __('messages.Libelle Produit') }}</label>
                                                        <input type="text" name="pdtnom_commercial" id="pdtnom_commercial" class="form-control" required="required"/>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-12" >{{ __('messages.Famille therapeutique') }} : </label>
                                                        <input type="text" name="pdtfamille_therapeutique" id="pdtfamille_therapeutique" class="form-control"/>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-12" >{{ __('messages.DCI') }}: </label>
                                                        <input type="text" name="pdtdci" id="pdtdci" class="form-control" required="required"/>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-12" >{{ __('messages.Prix achat') }} </label>
                                                        <input type="text" name="pdtprix_achat" id="pdtprix_achat" class="form-control"/>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-12" >{{ __('messages.Prix de vente') }} </label>
                                                        <input type="text" name="pdtprix_vente" id="pdtprix_vente" class="form-control" required="required"/>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-12" >{{ __('messages.Stock minimum') }} </label>
                                                        <input type="text" name="pdtstock_minimal" id="pdtstock_minimal" class="form-control"/>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group" id="pv_group">
                                                        <label class="control-label col-md-12" >{{ __('messages.Stock maximun') }} </label>
                                                        <input type="text" name="pdtstock_maximal" id="pdtstock_maximal" class="form-control"/>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-12" >{{__('messages.Ce produit achete')}}</label>
                                                        <select name="pdttobuy" id="pdttobuy" class="form-control">
                                                            <option id="true" value="true">{{__('messages.Oui')}}</option>
                                                            <option id="false" value="false">{{__('messages.Non')}}</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-12" >{{__('messages.Ce produit vend')}}</label>
                                                        <select name="pdttosell" id="pdttosell" class="form-control">
                                                            <option id="true" value="true">{{__('messages.Oui')}}</option>
                                                            <option id="false" value="false">{{__('messages.Non')}}</option>
                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-12" >{{__('messages.Type de produit')}} </label>
                                                        <select name="pdttype" id="pdttype" class="form-control">
                                                            <option id="Perisable" value="Perisable">{{__('messages.Selectionner un type')}}</option>
                                                            <option id="Non_Perisable" value="Non_Perissable">{{__('messages.Non_Perissable')}}</option>
                                                            <option id="Perisable" value="Perissable">{{__('messages.Perissable')}}</option>
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                <div class="form-group">
                                                        {!! Form::label(__('messages.Selectionner la categorie')) !!}
                                                        {!! Form::select('categorieid',$categories,null,['class'=>'form-control','id'=>'categorieid','onchange'=>'verifType()']) !!}
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <div class="form-group" align="center">
                                                        <input type="hidden" name="produitid" id="produitid" />
                                                        <input type="submit" name="action_button" id="action_button" class="btn btn-success" value="{{__('messages.Enregistrer')}}" />
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group" align="center">
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Quitter')}}</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Annuler toute la commande -->
                    <main id="confirmModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="modal-title">{{__('messages.Confirmation')}}</h2>
                                </div>
                                <div class="modal-body">
                                    <h5 align="center" style="margin:0;">{{__('messages.annuler produits commande')}}</h5>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" name="ok_button" id="ok_button" class="btn btn-danger">{{__('messages.Oui')}}</button>
                                    <button type="button" class="btn btn-primary" data-dismiss="modal">{{__('messages.Annuler')}}</button>
                                </div>
                            </div>
                        </div>
                    </main>

                </div>
            </div>
        </div>
    </main>
@endsection

@section('extra-js')
    <script>
        function actualiser() {
            code = document.getElementById("code").value;
            $('#pdt_selected').load('cmde.rech_pdtcon/'+code);
            $("div.dataTables_filter input").focus();
        }

        function fournisseur() {
            $.ajax({
                url:"cmde.fournisseurs",
                dataType:"json",
                success:function(data)
                {
                    $('#fournisseur_id').empty();
                    $('#fournisseur_id').append('<option id=0  value=0>- {{__('messages.Choisir un fournisseur')}} -</option>');
                    for (var i = 0; i < data.length; i++) {
                        $('#fournisseur_id').append('<option id=' + data[i].fournisseur_id + ' value=' + data[i].fournisseur_id + '>' + data[i].nom +' - '+ data[i].adresse+'</option>');
                    }
                    $('#fournisseur_id').change();
                }
            })
        }

        function rech_mont() {
            code = document.getElementById("code").value;
            $.ajax({
                url:"cmde.rech_mont/"+code,
                dataType:"json",
                success:function(data)
                {
                    document.getElementById("montant").value=data;
                }
            })
        }
        $(document).ready(function(){
            rech_mont();
            fournisseur();
            actualiser();
            $('#liste_produit').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    url: "{{ route('pdt.index') }}",
                },
                columns:[
                    {
                        data: 'nom_commercial',
                        name: 'nom_commercial',
                        render:function (data, type, row) {
                            return "<a href='#' id='"+row.produit_id+"' class='select'>"+row.nom_commercial+"</a>"}
                    },
                    {
                        data: 'prix_achat',
                        name: 'prix_achat'
                    }
                ]
            });

            $("div.dataTables_filter input").focus();

            $('#add_form').on('submit', function(event){
                event.preventDefault();
                var code = document.getElementById('code').value;
                console.log(code);
                $.ajax({
                    url:"{{ route('cmde.add') }}",
                    method:"POST",
                    data: new FormData(this),
                    contentType: false,
                    cache:false,
                    processData: false,
                    dataType:"json",
                    success:function(data)
                    {
                        var html = '';
                        if(data.errors)
                        {
                            html = '<div class="alert alert-danger">';
                            for(var count = 0; count < data.errors.length; count++)
                            {
                                html += '<p>' + data.errors[count] + '</p>';
                            }
                            html += '</div>';
                        }
                        if(data.error)
                        {
                            html = '<div class="alert alert-danger">' + data.error + '</div>';
                        }
                        if(data.success)
                        {
                            html = '<div class="alert alert-success">' + data.success + '</div>';
                            rech_mont();
                            $('#add_form')[0].reset();
                            $('#addModal').modal('hide');
                            $('#pdt_selected').load('cmde.rech_pdtcon/'+code);
                            $("div.dataTables_filter input").val('');
                            $("div.dataTables_filter input").focus();
                        }
                        $('#form_result').html(html);
                    }
                })
            });

            $('#create_pdt').click(function(){
                $('.modal-title').text("{{__('messages.Creer un Produit')}}");
                $('#actionbutton').val("{{__('messages.Ajouter')}}");
                $('#formresult').val("");
                $('#pdtref').val("");
                $('#pdtlib').val("");
                $('#pdtpv').val("");
                $('#pdtmin').val("");
                $('#pdtmax').val("");
                $('#pdttype').val("");
                $('#produitModal').modal('show');
            });

            $('#pdtform').on('submit', function(event){
                console.log('Creer un produit')
                event.preventDefault();
                $.ajax({
                    url:"{{ route('pdt.storenp') }}",
                    method:"POST",
                    data: new FormData(this),
                    contentType: false,
                    cache:false,
                    processData: false,
                    dataType:"json",
                    success:function(data)
                    {
                        var html = '';
                        if(data.errors)
                        {
                            html = '<div class="alert alert-danger">';
                            for(var count = 0; count < data.errors.length; count++)
                            {
                                html += '<p>' + data.errors[count] + '</p>';
                            }
                            html += '</div>';
                        }
                        if(data.error)
                        {
                            html = '<div class="alert alert-danger">' + data.error + '</div>';
                        }

                        if(data.success)
                        {
                            html = '<div class="alert alert-success">' + data.success + '</div>';
                            $('#pdtform')[0].reset();
                            $('#produitModal').modal('hide');
                            $('#liste_produit').DataTable().ajax.reload();
                        }

                        $('#formresult').html(html);
                    }
                })
            });

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');
                var code = document.getElementById('code').value;
                document.getElementById('hidden_code').value = code;

                $('#form_result').html('');
                $.ajax({
                    url:"cmde.select_edit/"+id,
                    dataType:"json",
                    success:function(data){
                        $('#reference').val(data.reference);
                        $('#nom_commercial').val(data.libelle);
                        $('#produit_id').val(data.produit_id);
                        $('#hidden_code').val(code);
                        $('#hidden_idcon').val(data.produit_commande_id);
                        $('#qte').val(data.qte);
                        $('#prix_achat').val(data.prix_achat);
                        $('#action_button').val("{{__('messages.Valider')}}");
                        $('#addModal').modal('show');
                        setTimeout(function() {$('#qte').focus();}, 200);
                    }
                })
            });

            $(document).on('click', '.select', function(){
                var id = $(this).attr('id');
                var code = document.getElementById('code').value;
                document.getElementById('hidden_code').value = code;

                $('#form_result').html('');
                $.ajax({
                    url:"cmde.select/"+id,
                    dataType:"json",
                    success:function(data){
                        console.log(data)
                        $('#reference').val(data.reference);
                        $('#nom_commercial').val(data.nom_commercial);
                        $('#produit_id').val(data.produit_id);
                        $('#qte').val('');
                        $('#prix_achat').val(data.prix_achat);
                        $('#hidden_code').val(code);
                        $('#hidden_idcon').val("");
                        $('#action_button').val("{{__('messages.Ajouter')}}");
                        $('#addModal').modal('show');
                        setTimeout(function() {$('#qte').focus();}, 200);
                    }
                })
            });


            $(document).on('click', '.delete', function(){
                id = $(this).attr('id');
                code = document.getElementById('code').value;
                $.ajax({
                    url:"cmde.delete/"+id,
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_client').DataTable().ajax.reload();
                        }, 100);

                        rech_mont();
                        $('#pdt_selected').load('cmde.rech_pdtcon/'+code);
                        $("div.dataTables_filter input").focus();
                    }
                })
            });

        });
    </script>
@endsection
