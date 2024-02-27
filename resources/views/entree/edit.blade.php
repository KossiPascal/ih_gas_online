@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')

@section('extra-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">
        <h3 class="ml-5">{{__('messages.MODIFIER UNE RECEPTION')}}</h3>
        <div class="col-12 col-sm-12 col-md-12">
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('rec.histo')}}" class="btn btn-success"><i class="fa fa-info"></i>{{__('messages.Historique des Receptions')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('cmde.index')}}" class="btn btn-primary"><i class="fa fa-database"></i>{{__('messages.Nouvelle commande')}}</a>
            </div>
            <div class="col-12 col-md-4 float-right">
                <a href="{{route('cmde.histo')}}" class="btn btn-danger"><i class="fa fa-user"></i> {{__('messages.Historique des Cmdes')}}</a>
            </div>
        </div>
        <div class="col-md-12 float-left">
            <div class="col-md-8 float-left">
                <h5 class="ml-3">{{__('messages.PRODUITS SELECTIONNES')}}</h5>
            </div>

            <div class="col-md-4 float-right">
                <h5> {{__('messages.LES COMMANDES')}}</h5>
            </div>
        </div>

        <div class="col-md-12 float-left">
            <div class="col-md-8 float-left">
                <div class="contour_div">
                    <div class="contour_table">
                        <table class="table table-striped table-bordered contour_table" id="pdt_rec">

                        </table>
                    </div>
                    <div class="info-box">
                        @include('reception/formedit')
                    </div>
                </div>
            </div>

            <div class="col-md-4 float-right">
                <div class="info-box">
                    <div class="table-responsive div_style">
                        <table id="pdt_cmde" class="display table table-striped table-bordered data-table">
                            <thead>
                            <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                                <td class="description">{{__('messages.Produit')}}</td>
                                <td class="price">{{__('messages.Qte Cmde')}}</td>
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
                                                        <input type="text" name="pdt_lib" id="pdt_lib" class="form-control" readonly/>
                                                    </td>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.Qte deja livree')}}: </label>
                                                        <input type="hidden" name="pdt_ref" id="pdt_ref" class="form-control" readonly/>
                                                        <input type="hidden" name="qte_cmde" id="qte_cmde" class="form-control" readonly/>
                                                        <input type="text" name="qte_liv" id="qte_liv" class="form-control" readonly/>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.Lot')}} </label>
                                                        <input type="text" name="lot" id="lot" class="form-control" value="Neant"/>
                                                    </td>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.Quantite recue')}} </label>
                                                        <input type="text" name="qte" id="qte" class="form-control" required="required" value="0"/>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.Prix achat')}} </label>
                                                        <input type="text" name="pa" id="pa" class="form-control" required="required" value="0"/>
                                                    </td>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.Coeficient de marge')}}: </label>
                                                        <input type="text" name="coef" id="coef" class="form-control" required="required" value="1.3"/>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12" id="datefab">{{__('messages.Fabrique')}} </label>
                                                        <input type="date" name="date_fab" id="date_fab" class="form-control"/>
                                                    </td>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12" id="dateexp">{{__('messages.Expire')}} </label>
                                                        <input type="date" name="date_exp" id="date_exp" class="form-control"/>
                                                    </td>
                                                </tr>
                                            </table>

                                        </div>
                                        <div class="form-group" align="center">
                                            <input type="hidden" name="action" id="action" />
                                            <input type="hidden" name="pdt_num" id="pdt_num" />
                                            <input type="hidden" name="hidden_idcon" id="hidden_idcon" />
                                            <input type="hidden" name="hidden_rec_num" id="hidden_rec_num" />
                                            <input type="hidden" name="hidden_cmde_num" id="hidden_cmde_num" />
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
                                    <h5 align="center" style="margin:0;">{{__('messages.Retirer ce produit')}}</h5>
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
            rec_num = document.getElementById("rec_num").value;
            cmde_num = document.getElementById("cmde_num").value;
            $('#pdt_cmde').load('rec.pdt_cmde/'+cmde_num);
            $('#pdt_rec').load('rec.pdt_rec/'+rec_num+'/'+cmde_num);
            $("div.dataTables_filter input").focus();
            console.log(rec_num,cmde_num);
        }

        $(document).ready(function(){
            $('#liste_produit').DataTable({
                language: {
                    searchS: "{{__('messages.Recherche produit')}}"
                }
            });
            actualiser();

            $("div.dataTables_filter input").focus();

            $('#add_form').on('submit', function(event){
                event.preventDefault();
                var rec_num = document.getElementById('rec_num').value;
                var cmde_num = document.getElementById('cmde_num').value;
                $.ajax({
                    url:"{{ route('rec.add') }}",
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
                            $('#pdt_rec').load('rec.pdt_rec/'+rec_num+'/'+cmde_num);

                            $("div.dataTables_filter input").focus();
                        }
                        $('#form_result').html(html);
                    }
                })
            });

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');
                var rec_num = document.getElementById('rec_num').value;
                document.getElementById('hidden_rec_num').value = rec_num;

                var cmde_num = document.getElementById('cmde_num').value;
                document.getElementById('hidden_cmde_num').value = cmde_num;
                $('#form_result').html('');
                $.ajax({
                    url:"rec.select_edit/"+id+"/"+cmde_num,
                    dataType:"json",
                    success:function(data){
                        if(data.produit.pdt_type=='Perissable'){
                            document.getElementById('date_fab').hidden=false;
                            document.getElementById('datefab').hidden=false;
                            document.getElementById('date_exp').hidden=false;
                            document.getElementById('dateexp').hidden=false;
                        }else{
                            document.getElementById('date_fab').hidden=true;
                            document.getElementById('datefab').hidden=true;
                            document.getElementById('date_exp').hidden=true;
                            document.getElementById('dateexp').hidden=true;
                        }

                        $('#pdt_ref').val(data.produit.pdt_ref);
                        $('#pdt_lib').val(data.produit.pdt_lib);
                        $('#pdt_num').val(data.produit.pdt_num);
                        $('#hidden_rec_num').val(rec_num);
                        $('#hidden_idcon').val(data.produit.id);
                        $('#lot').val(data.produit.lot);
                        $('#qte').val(data.produit.qte);
                        $('#qte_liv').val(data.qte_liv-data.produit.qte);
                        $('#pa').val(data.produit.pa);
                        $('#coef').val(data.produit.coef);
                        $('#date_fab').val(data.produit.date_fab);
                        $('#date_exp').val(data.produit.date_exp);
                        $('#action_button').val("Valider");
                        $('#addModal').modal('show');
                        setTimeout(function() {$('#lot').focus();}, 200);
                    }
                })
            });

            $(document).on('click', '.select', function(){
                var id = $(this).attr('id');
                var rec_num = document.getElementById('rec_num').value;
                document.getElementById('hidden_rec_num').value = rec_num;

                var cmde_num = document.getElementById('cmde_num').value;
                document.getElementById('hidden_cmde_num').value = cmde_num;

                $('#form_result').html('');
                $.ajax({
                    url:"rec.select/"+id+"/"+cmde_num,
                    dataType:"json",
                    success:function(data){
                        if(data.produit.pdt_type=='Perissable'){
                            document.getElementById('date_fab').hidden=false;
                            document.getElementById('datefab').hidden=false;
                            document.getElementById('date_exp').hidden=false;
                            document.getElementById('dateexp').hidden=false;
                        }else{
                            document.getElementById('date_fab').hidden=true;
                            document.getElementById('datefab').hidden=true;
                            document.getElementById('date_exp').hidden=true;
                            document.getElementById('dateexp').hidden=true;
                        }

                        $('#pdt_ref').val(data.produit.pdt_ref);
                        $('#pdt_lib').val(data.produit.pdt_lib);
                        $('#pdt_num').val(data.produit.pdt_num);
                        $('#lot').val(data.produit.lot);
                        $('#qte').val(data.produit.qte-data.qte_liv);
                        $('#qte_cmde').val(data.produit.qte);
                        $('#qte_liv').val(data.qte_liv);
                        $('#pa').val(data.produit.pa);
                        $('#hidden_rec_num').val(rec_num);
                        $('#hidden_idcon').val("");
                        $('#action_button').val("{{__('messages.Ajouter')}}");
                        $('#addModal').modal('show');
                        setTimeout(function() {$('#lot').focus();}, 200);
                    }
                })
            });


            $(document).on('click', '.delete', function(){
                id = $(this).attr('id');
                rec_num = document.getElementById('rec_num').value;
                $.ajax({
                    url:"rec.delete/"+id,
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_client').DataTable().ajax.reload();
                        }, 100);
                        $('#pdt_rec').load('rec.pdt_rec/'+rec_num+'/'+cmde_num);
                        $("div.dataTables_filter input").focus();
                    }
                })
            });

        });
    </script>
@endsection
