@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')

@section('extra-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">
        <div class="col-12 col-sm-12 col-md-12 float-left">
            <div class="col-12 col-md-6 float-left">
                <h3 class="ml-5">{{__('messages.MODIFIER INITIALISATION')}}</h3>
            </div>
            <div class="col-12 col-sm-6 col-md-4c float-right">
                <a href="{{route('ini.histo')}}" class="btn btn-warning"><i class="fa fa-list"></i> {{__('messages.Historique')}}</a>
            </div>
        </div>
        <br>
        <div class="col-12 col-sm-12 col-md-12">
            <div class="col-12 col-md-4 float-left">
                <a href="{{route('user.index')}}" class="btn btn-success"><i class="fa fa-user"></i> {{__('messages.Gestion Utilisateur')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('ini.index')}}" class="btn btn-primary"><i class="fa fa-database"></i> {{__('messages.Initialisation du stock')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-right">
                <a href="{{route('centre.index')}}" class="btn btn-danger"><i class="fa fa-info"></i> {{__('messages.Information de la structure')}}</a>
            </div>
        </div>
        <div class="col-md-12 float-left">
            <div class="col-md-6 float-left">
                <h5 class="ml-5">{{__('messages.PRODUITS SELECTIONNES')}}</h5>
            </div>

            <div class="col-md-6 float-right">
                <h3> {{__('messages.LISTE DES PRODUITS')}}</h3>
            </div>
        </div>

        <div class="col-md-12 float-left">
            <div class="col-md-8 float-left">
                <div class="contour_div">
                    <div class="contour_table">
                        <table class="table table-striped table-bordered contour_table" id="pdt_selected">

                        </table>
                    </div>
                    <div class="info-box">
                        @include('initial/form')
                    </div>
                </div>

            </div>

            <div class="col-md-4 float-right">
                <div class="info-box">
                    <div class="table-responsive div_style">
                        <table id="liste_produit" class="display table table-striped table-bordered data-table">
                            <thead>
                            <tr>
                                <th>{{__('messages.Libelle')}}</th>
                                <th>{{__('messages.Type')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $count =1;?>
                            @foreach($produits as $produit)
                                <tr>
                                    <td width="90">
                                        <a href="#" id="{{$produit->pdt_num}}" class="select">{{$produit->pdt_lib}}</a>
                                    </td>
                                    <td width="6" style="text-align: right">{{$produit->pdt_type}}</td>
                                </tr>
                            @endforeach
                            <?php $count++;?>
                            </tbody>
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
                                                        <label class="control-label col-md-12">{{__('messages.Reference')}} </label>
                                                        <input type="text" name="pdt_ref" id="pdt_ref" class="form-control" readonly/>
                                                    </td>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.Libelle')}} </label>
                                                        <input type="text" name="pdt_lib" id="pdt_lib" class="form-control" readonly/>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.Lot')}} </label>
                                                        <input type="text" name="lot" id="lot" class="form-control" value="Neant"/>
                                                    </td>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.Quantite initiale')}}: </label>
                                                        <input type="text" name="qtea" id="qtea" class="form-control" required="required" value="0"/>
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
                                            <input type="hidden" name="hidden_mag_num" id="hidden_mag_num" />
                                            <input type="hidden" name="hidden_ini_num" id="hidden_ini_num" />
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
                                    <button type="button" name="ok_button" id="ok_button" class="btn btn-danger">{{__('messages.oui')}}</button>
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
        /*function magasin() {
            $.ajax({
                url:"ini.magasins",
                dataType:"json",
                success:function(data)
                {
                    $('#mag_num').empty();
                    $('#mag_num').append('<option id=0  value=0>- Choisir un magasin -</option>');
                    for (var i = 0; i < data.length; i++) {
                        $('#mag_num').append('<option id=' + data[i].mag_num + ' value=' + data[i].mag_num + '>' + data[i].mag_lib +'</option>');
                    }
                    $('#mag_num').change();
                }
            })
        }*/
        $(document).ready(function(){
            //magasin();
            $('#liste_produit').DataTable({
                language: {
                    searchS: "{{__('messages.Recherche produit')}}"
                }
            });
            actualiser()

            function actualiser() {
                ini_num = document.getElementById("ini_num").value;
                $('#pdt_selected').load('ini.rech_pdtcon/'+ini_num);
                $("div.dataTables_filter input").focus();
            }

            $("div.dataTables_filter input").focus();

            $('#add_form').on('submit', function(event){
                event.preventDefault();
                var ini_num = document.getElementById('ini_num').value;
                console.log(ini_num);
                $.ajax({
                    url:"{{ route('ini.add') }}",
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
                            $('#pdt_selected').load('ini.rech_pdtcon/'+ini_num);
                            $("div.dataTables_filter input").val('');
                            $("div.dataTables_filter input").focus();
                        }
                        $('#form_result').html(html);
                    }
                })
            });

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');
                var ini_num = document.getElementById('ini_num').value;
                document.getElementById('hidden_ini_num').value = ini_num;

                var mag_num = document.getElementById('mag_num').value;
                document.getElementById('hidden_mag_num').value = mag_num;

                $('#form_result').html('');
                $.ajax({
                    url:"ini.select_edit/"+id,
                    dataType:"json",
                    success:function(data){
                        if(data.pdt_type=='Perissable'){
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

                        $('#pdt_ref').val(data.pdt_ref);
                        $('#pdt_lib').val(data.pdt_lib);
                        $('#pdt_num').val(data.pdt_num);
                        $('#hidden_ini_num').val(ini_num);
                        $('#hidden_idcon').val(data.id);
                        $('#lot').val(data.lot);
                        $('#qtea').val(data.qtea);
                        $('#pa').val(data.pa);
                        $('#coef').val(data.coef);
                        $('#date_fab').val(data.date_fab);
                        $('#date_exp').val(data.date_exp);
                        $('#action_button').val("Ajouter");
                        $('#addModal').modal('show');
                        setTimeout(function() {$('#lot').focus();}, 200);
                    }
                })
            });

            $(document).on('click', '.select', function(){
                var id = $(this).attr('id');
                var ini_num = document.getElementById('ini_num').value;
                document.getElementById('hidden_ini_num').value = ini_num;

                var mag_num = document.getElementById('mag_num').value;
                document.getElementById('hidden_mag_num').value = mag_num;

                $('#form_result').html('');
                $.ajax({
                    url:"ini.select/"+id,
                    dataType:"json",
                    success:function(data){
                        if(data.pdt_type=='Perissable'){
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

                        $('#pdt_ref').val(data.pdt_ref);
                        $('#pdt_lib').val(data.pdt_lib);
                        $('#pdt_num').val(data.pdt_num);
                        $('#lot').val(data.lot);
                        $('#qtea').val(data.qtea);
                        $('#pa').val(data.pa);
                        $('#hidden_ini_num').val(ini_num);
                        $('#hidden_idcon').val("");
                        $('#action_button').val("{{__('messages.Ajouter')}}");
                        $('#addModal').modal('show');
                        setTimeout(function() {$('#lot').focus();}, 200);
                    }
                })
            });


            $(document).on('click', '.delete', function(){
                id = $(this).attr('id');
                ini_num = document.getElementById('ini_num').value;
                $.ajax({
                    url:"ini.delete/"+id,
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_client').DataTable().ajax.reload();
                        }, 100);
                        $('#pdt_selected').load('ini.rech_pdtcon/'+ini_num);
                        $("div.dataTables_filter input").focus();
                    }
                })
            });

        });
    </script>
@endsection
