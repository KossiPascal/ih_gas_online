@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')

@section('extra-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">

        <div class="col-12 col-sm-12 col-md-12">
            <h3 class="ml-5">{{__('messages.RECEVOIR UNE COMMANDE')}}</h3>
            <div class="col-12 col-sm-6 col-md-4 float-left">
                <a href="{{route('recdps.histo')}}" class="btn btn-danger"><i class="fa fa-list"></i>{{__('messages.Historique des receptions')}}</a>
            </div>
            <div class="col-12 col-md-4 float-left">
                <a href="{{route('recdps.index')}}" class="btn btn-success"><i class="fa fa-plus"></i> {{__('messages.Suivi Commande')}}</a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 float-right">
                <a href="{{route('trdps.index')}}" class="btn btn-warning"><i class="fa fa-check"></i> {{__('messages.Transfert vers FS')}}</a>
            </div>
        </div>
        <div class="col-md-12 float-left">
            <div class="col-md-7 float-left">
                <h5 class="ml-3">{{__('messages.PRODUITS SELECTIONNES')}}</h5>
            </div>

            <div class="col-md-5 float-right">
                <h5> {{__('messages.LES COMMANDES')}}</h5>
            </div>
        </div>

        <div class="col-md-12 float-left">
            <div class="col-md-6 float-left">
                <div class="contour_div">
                    <div class="contour_table">
                        <table class="table table-striped table-bordered contour_table" id="pdt_rec">

                        </table>
                    </div>
                    <div class="info-box">
                        @include('receptiondps/form')
                    </div>
                </div>
            </div>

            <div class="col-md-6 float-right">
                <div class="form-group cool-md-12 float-left">
                    <select name="commande_id" id="commande_id" class="form-control" onchange="actualiser()">
                        @foreach($commandes as $key=>$commande)
                            <option value= "{!! $commande !!}"> {!! $commande !!} </option>
                        @endforeach
                    </select>
                </div>
                <div class="info-box">
                    <div class="table-responsive div_style">
                        <table id="pdt_cmde" class="display table table-striped table-bordered data-table">
                            <thead>
                                <tr class="cart_menu">
                                    <td class="description">{{__('messages.Produit')}}</td>
                                    <td class="price">{{__('messages.Qte Cmde')}}</td>
                                  <td class="price">{{__('messages.Qte Recue')}}</td>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <!--Ajouter un produit -->
                    <div id="addModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">{{__('messages.Ajouter un produit')}}</h4>
                                </div>
                                <div class="modal-body">
                                    <span class="form_result" id="form_result"></span>
                                    <form method="post" id="add_form" class="form-horizontal">
                                        @csrf
                                        <div class="form-group">
                                            <table width="100%">
                                                <tr>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.Libelle')}} </label>
                                                        <input type="text" name="libelle" id="libelle" class="form-control" readonly/>
                                                    </td>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.Qte deja livree')}}: </label>
                                                        <input type="hidden" name="reference" id="reference" class="form-control" readonly/>
                                                        <input type="hidden" name="qte_cmde" id="qte_cmde" class="form-control" readonly/>
                                                        <input type="text" name="qte_liv" id="qte_liv" class="form-control" readonly/>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.Quantite recue')}}: </label>
                                                        <input type="text" name="qte" id="qte" class="form-control" required="required" value="0"/>
                                                    </td>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.Observation')}} </label>
                                                        <input type="text" name="remarque" id="remarque" class="form-control" value="Neant"/>
                                                    </td>
                                                </tr>
                                            </table>

                                        </div>
                                        <div class="form-group" align="center">
                                            <input type="hidden" name="produit_id" id="produit_id" />
                                            <input type="hidden" name="produit_reception_dps_id" id="produit_reception_dps_id" />
                                            <input type="hidden" name="hidden_code" id="hidden_code" />
                                            <input type="hidden" name="hidden_commande_id" id="hidden_commande_id" />
                                            <input type="submit" name="action_button" id="action_button" class="btn btn-success" value="{{__('messages.Ajouter')}}" />
                                            <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Annuler')}}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Supprimer le client -->
                    <main id="confirmModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="modal-title">{{__('messages.Confirmation')}}</h2>
                                </div>
                                <div class="modal-body">
                                    <h5 align="center" style="margin:0;">{{__('messages.Retirer ce produit')}} ?</h5>
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
        function commande() {
            $.ajax({
                url:"recdps.commandes",
                dataType:"json",
                success:function(data)
                {
                    $('#commande_id').empty();
                    $('#commande_id').append('<option id="0"  value="0">- {{__('messages.Choisir une commande')}} -</option>');
                    for (var i = 0; i < data.length; i++) {
                        $('#commande_id').append('<option id=' + data[i].commande_id + ' value=' + data[i].commande_id + '>'+ data[i].code +' - ' + data[i].nom_centre +'</option>');
                    }
                    $('#commande_id').change();
                }
            })
        }

        function actualiser() {
            code = document.getElementById("code").value;
            commande_id = document.getElementById("commande_id").value;
            console.log(code,commande_id)
            if (commande_id!=0){
                $('#pdt_cmde').load('recdps.pdt_cmde/'+commande_id);
                $('#pdt_rec').load('recdps.pdt_rec/'+code+'/'+commande_id);
                $("div.dataTables_filter input").focus();
                document.getElementById("hidden_commande_id").value=commande_id;
                document.getElementById("cmdenum").value=commande_id;
            }
        }

        $(document).ready(function(){
            $('#pdt_cmde').DataTable({
                language: {
                    searchS: "{{__('messages.Recherche produit')}}"
                }
            });
            commande();
            actualiser();

            $("div.dataTables_filter input").focus();

            $('#add_form').on('submit', function(event){
                event.preventDefault();
                var code = document.getElementById('code').value;
                var commande_id = document.getElementById('commande_id').value;
                $.ajax({
                    url:"{{ route('recdps.add') }}",
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
                            $('#add_form')[0].reset();
                            $('#addModal').modal('hide');
                            console.log('update ou insert '+data.success+'-'+code+'-'+commande_id)
                            $('#pdt_rec').load('recdps.pdtrec/'+code+'/'+commande_id);

                            $("div.dataTables_filter input").val('');
                            $("div.dataTables_filter input").focus();
                        }
                        $('#form_result').html(html);
                    }
                })
            });

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');
                var code = document.getElementById('code').value;
                document.getElementById('hidden_code').value = code;

                var commande_id = document.getElementById('commande_id').value;
                document.getElementById('hidden_commande_id').value = commande_id;
                $('#form_result').html('');
                $.ajax({
                    url:"recdps.select_edit/"+id+"/"+commande_id,
                    dataType:"json",
                    success:function(data){
                        $('#reference').val(data.produit.reference);
                        $('#libelle').val(data.produit.libelle);
                        $('#produit_id').val(data.produit.produit_id);
                        $('#hidden_code').val(code);
                        $('#produit_reception_dps_id').val(data.produit.produit_reception_dps_id);
                        $('#qte').val(data.produit.qte_recue);
                        $('#remarque').val(data.produit.remarque)
                        $('#qte_cmde').val(data.produit.qte_commandee);
                        $('#qte_liv').val(data.qte_liv-data.produit.qte_recue);
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

                var commande_id = document.getElementById('commande_id').value;
                document.getElementById('hidden_commande_id').value = commande_id;

                $('#form_result').html('');
                $.ajax({
                    url:"recdps.select/"+id+"/"+commande_id,
                    dataType:"json",
                    success:function(data){

                        $('#reference').val(data.reference);
                        $('#libelle').val(data.libelle);
                        $('#produit_id').val(data.produit_id);
                        $('#qte').val(data.qte-data.qte_liv);
                        $('#qte_cmde').val(data.qte);
                        $('#qte_liv').val(data.qte_liv);
                        $('#remarque').val('');
                        $('#hidden_code').val(code);
                        $('#produit_reception_dps_id').val("");
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
                    url:"recdps.delete/"+id,
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_client').DataTable().ajax.reload();
                        }, 100);
                        $('#pdt_rec').load('recdps.pdtrec/'+code+'/'+commande_id);
                        $("div.dataTables_filter input").focus();
                    }
                })
            });



        });
    </script>
@endsection
