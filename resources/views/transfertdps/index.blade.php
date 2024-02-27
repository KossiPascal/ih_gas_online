@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE reception_si')

@section('extra-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">

        <div class="col-12 col-sm-12 col-md-12">
            <h3 class="ml-5">{{__('messages.TRANSFERT VERS FORMATION SANITAIRE')}}</h3>
            <div class="col-12 col-sm-6 col-md-4 float-left">
                <a href="{{route('trdps.histo')}}" class="btn btn-danger"><i class="fa fa-list"></i>{{__('messages.Historique des transerts')}}</a>
            </div>
            <div class="col-12 col-md-4 float-left">
                <a href="{{route('cmde.histo')}}" class="btn btn-success"><i class="fa fa-plus"></i> {{__('messages.Suivi Commande')}}</a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 float-right">
                <a href="{{route('recdps.index')}}" class="btn btn-warning"><i class="fa fa-check"></i> {{__('messages.Recevoir une cmde')}}</a>
            </div>
        </div>
        <div class="col-md-12 float-left">
            <div class="col-md-5 float-left">
                <h5 class="ml-3">{{__('messages.INFOS TRANSFERT')}}</h5>
            </div>

            <div class="col-md-7 float-right">
                <h5> {{__('messages.LES PRODUITS')}}</h5>
            </div>
        </div>

        <div class="col-md-12 float-left">
            <div class="col-md-5 float-left">
                <div class="contour_div">
                    <div class="info-box">
                        @include('transfertdps/form')
                    </div>
                </div>
            </div>

            <div class="col-md-7 float-right">
                <div class="info-box">
                    <div class="table-responsive div_style">
                        <table id="pdt_tr" class="display table table-striped table-bordered data-table">
                            <thead>
                            <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                                <td class="description">{{__('messages.Produit')}}</td>
                                <td class="price">{{__('messages.Qte recue')}}</td>
                                <td class="price">{{__('messages.Qte Transferee')}}</td>
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
                                                        <label class="control-label col-md-12">{{__('messages.Qte deja transferee')}}: </label>
                                                        <input type="hidden" name="reference" id="reference" class="form-control" readonly/>
                                                        <input type="hidden" name="qte_cmde" id="qte_cmde" class="form-control" readonly/>
                                                        <input type="text" name="qte_liv" id="qte_liv" class="form-control" readonly/>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.Quantite transferee')}}: </label>
                                                        <input type="text" name="qte_tr" id="qte_tr" class="form-control" required="required" value="0"/>
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
                                            <input type="hidden" name="hidden_reception_dps_id" id="hidden_reception_dps_id" />
                                            <input type="submit" name="action_button" id="action_button" class="btn btn-success" value="{{__('messages.Ajouter')}}" />
                                            <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Annuler')}}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('extra-js')
    <script>
        function reception_si() {
            $.ajax({
                url:"trdps.reception",
                dataType:"json",
                success:function(data)
                {
                    $('#reception_dps_id').empty();
                    $('#reception_dps_id').append('<option id="0"  value="0">- {{__('messages.Choisir une reception')}} -</option>');
                    for (var i = 0; i < data.length; i++) {
                        $('#reception_dps_id').append('<option id=' + data[i].reception_dps_id + ' value=' + data[i].reception_dps_id + '>'+ data[i].code +' - ' + data[i].nom_centre +'</option>');
                    }
                    $('#reception_dps_id').change();
                }
            })
        }

        function actualiser() {
            code = document.getElementById("code").value;
            reception_dps_id = document.getElementById("reception_dps_id").value;
            if (reception_dps_id!=0){
                $('#pdt_tr').load('trdps.pdt_tr/'+reception_dps_id);
                $("div.dataTables_filter input").focus();
                document.getElementById("hidden_reception_dps_id").value=reception_dps_id;
                getcommande(reception_dps_id);
            }
        }
        function getcommande(id){
            $.ajax({
                url:"trdps.getcommande/"+id,
                dataType:"json",
                success:function(data)
                {
                    document.getElementById("commande_id").value=data;
                }
            })
        }

        $(document).ready(function(){
            $('#pdt_cmde').DataTable({
                processing: true,
                serverSide: true,
                paging: false,
                searching: true,
                language: {
                    searchS: "{{__('messages.Recherche produit')}}"
                }
            });
            reception_si();
            actualiser();

            $("div.dataTables_filter input").focus();

            $('#add_form').on('submit', function(event){
                event.preventDefault();
                var code = document.getElementById('code').value;
                var reception_dps_id = document.getElementById('reception_dps_id').value;
                $.ajax({
                    url:"{{ route('trdps.add') }}",
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
                            $('#pdt_tr').load('trdps.pdt_tr/'+reception_dps_id);
                        }
                        $('#form_result').html(html);
                    }
                })
            });

            $(document).on('click', '.select', function(){
                var id = $(this).attr('id');

                var reception_dps_id = document.getElementById('reception_dps_id').value;
                document.getElementById('hidden_reception_dps_id').value = reception_dps_id;

                $('#form_result').html('');
                $.ajax({
                    url:"trdps.select/"+id,
                    dataType:"json",
                    success:function(data){
                        $('#reference').val(data.reference);
                        $('#libelle').val(data.produit.libelle);
                        $('#produit_id').val(data.produit.produit_id);
                        $('#qte_tr').val(data.produit.qte_transferee);
                        $('#qte_cmde').val(data.produit.qte_commandee);
                        $('#qte_liv').val(data.qte_liv-data.produit.qte_transferee);
                        $('#remarque').val(data.produit.remarque);
                        $('#hidden_code').val(code);
                        $('#produit_reception_dps_id').val(id);
                        $('#action_button').val("{{__('messages.Valider')}}");
                        $('#addModal').modal('show');
                        setTimeout(function() {$('#qte').focus();}, 200);
                    }
                })
            });


            $(document).on('click', '.delete', function(){
                id = $(this).attr('id');
                code = document.getElementById('code').value;
                $.ajax({
                    url:"trdps.delete/"+id,
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_client').DataTable().ajax.reload();
                        }, 100);
                        $('#pdt_rec').load('trdps.pdtrec/'+code+'/'+reception_dps_id);
                        $("div.dataTables_filter input").focus();
                    }
                })
            });

        });
    </script>
@endsection
