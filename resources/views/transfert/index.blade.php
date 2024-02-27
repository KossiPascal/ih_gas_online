@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')

@section('extra-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">
        <h3 class="ml-5">{{__('messages.TRANSFERT D ARTICLE')}}</h3>
        <div class="col-12 col-sm-12 col-md-12">
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('tr.histo')}}" class="btn btn-primary"><i class="fa fa-info"></i> {{__('messages.Historique des Transferts')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="#" class="btn btn-warning"><i class="fa fa-database"></i>{{__('messages.Entree article')}}</a>
            </div>
            <div class="col-12 col-md-4 float-right">
                <a href="#" class="btn btn-danger"><i class="fa fa-user"></i> {{__('messages.Sortie article')}}</a>
            </div>
        </div>
        <div class="col-md-12 float-left">
            <div class="col-md-8 float-left">
                <h5 class="ml-3">{{__('messages.PRODUITS SELECTIONNES')}}</h5>
            </div>

            <div class="col-md-4 float-right">
                <h5> {{__('messages.MAGASIN SOURCE')}}</h5>
            </div>
        </div>

        <div class="col-md-12 float-left">
            <div class="col-md-6 float-left">
                <div class="contour_div">
                    <div class="contour_table">
                        <table class="table table-striped table-bordered contour_table" id="pdt_dest">

                        </table>
                    </div>
                    <div class="info-box">
                        @include('transfert/form')
                    </div>
                </div>
            </div>

            <div class="col-md-6 float-right">
                <div class="form-group cool-md-12 float-left">
                    <select name="magasin_source" id="magasin_source" class="form-control" onchange="actualiser()">
                        @foreach($magasins as $key=>$magasin)
                            <option value= "{!! $magasin !!}"> {!! $magasin !!} </option>
                        @endforeach
                    </select>
                </div>
                <div class="info-box">
                    <div class="table-responsive div_style">
                        <table id="pdt_sour" class="display table table-striped table-bordered data-table">
                            <thead>
                            <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                                <td class="description">{{__('messages.Lot')}}</td>
                                <td class="description">{{__('messages.Produit')}}</td>
                                <td class="price">{{__('messages.Qte dispo')}}</td>
                                <td class="price">{{__('messages.Select')}}</td>
                            </tr>
                            </thead>
                        </table>
                    </div>

                    <!--Ajouter un produit -->
                    <div id="addModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Ajouter un produit</h4>
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
                                                        <input type="hidden" name="prix" id="prix" class="form-control" readonly/>
                                                    </td>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.Lot')}} </label>
                                                        <input type="text" name="lot" id="lot" class="form-control" readonly/>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.qte_dispo')}}: </label>
                                                        <input type="text" name="ini" id="ini" class="form-control" readonly/>
                                                    </td>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.Quantite transferee')}}: </label>
                                                        <input type="text" name="qte" id="qte" class="form-control" required="required" value="0"/>
                                                    </td>
                                                </tr>
                                            </table>

                                        </div>
                                        <div class="form-group" align="center">
                                            <input type="hidden" name="produit_id" id="produit_id" />
                                            <input type="hidden" name="hidden_idcon" id="hidden_idcon" />
                                            <input type="hidden" name="idqp" id="idqp" />
                                            <input type="hidden" name="hidden_code" id="hidden_code" />
                                            <input type="hidden" name="hidden_magasin_source" id="hidden_magasin_source" />
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
        function magasin() {
            $.ajax({
                url:"tr.magasins",
                dataType:"json",
                success:function(data)
                {
                    $('#magasin_source').empty();
                    $('#magasin_source').append('<option id=0  value=0>- {{__('messages.Choisir un magasin')}} -</option>');
                    for (var i = 0; i < data.length; i++) {
                        $('#magasin_source').append('<option id=' + data[i].magasin_id + ' value=' + data[i].magasin_id + '>' + data[i].libelle +'</option>');
                    }
                    $('#magasin_source').change();

                    $('#magasin_destination').empty();
                    $('#magasin_destination').append('<option id=0  value=0>- {{__('messages.Choisir un magasin de destination')}} -</option>');
                    for (var i = 0; i < data.length; i++) {
                        $('#magasin_destination').append('<option id=' + data[i].magasin_id + ' value=' + data[i].magasin_id + '>' + data[i].libelle +'</option>');
                    }
                    $('#magasin_destination').change();
                }
            })
        }

        function getProduits(magasin_id){
            $('#pdt_sour').DataTable().destroy();
            $('#pdt_sour').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "tr.pdt_sour/" + magasin_id
                },
                columns: [
                    {
                        data: 'lot',
                        name: 'lot'
                    },
                    {
                        data: 'libelle',
                        name: 'libelle'
                    },
                    {
                        data: 'qte',
                        name: 'qte'
                    },
                    {
                        data: 'stock_produit_id',
                        name: 'stock_produit_id',
                        render: function (data, type, row) {
                            return "<a href='#' id='" + row.stock_produit_id + "' class='btn btn-primary select'><i class='fa fa-check'></i></a>"
                        }
                    }
                ]
            })
        }

        function actualiser() {
            code = document.getElementById("code").value;
            magasin_source = document.getElementById("magasin_source").value;
            document.getElementById("magasinsource").value=magasin_source;
            $('#pdt_dest').load('tr.pdt_dest/'+code);
            if (magasin_source!=0){
                getProduits(magasin_source)
                $("div.dataTables_filter input").focus();
            }
        }

        $(document).ready(function(){
            actualiser();
            magasin();

            $("div.dataTables_filter input").focus();

            $('#add_form').on('submit', function(event){
                event.preventDefault();
                var code = document.getElementById('code').value;
                var magasin_source = document.getElementById('magasin_source').value;
                $.ajax({
                    url:"{{ route('tr.add') }}",
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
                            $('#pdt_dest').load('tr.pdt_dest/'+code);

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

                var magasin_source = document.getElementById('magasin_source').value;
                document.getElementById('hidden_magasin_source').value = magasin_source;
                //document.getElementById('mag_sour').value = magasin_source;
                $('#form_result').html('');
                $.ajax({
                    url:"tr.select_edit/"+id,
                    dataType:"json",
                    success:function(data){
                        console.log(id,data)
                        $('#prix').val(data.prix);
                        $('#libelle').val(data.libelle);
                        $('#produit_id').val(data.produit_id);
                        $('#idqp').val(id);
                        $('#lot').val(data.lot);
                        $('#ini').val(data.ini);
                        $('#qte').val(data.qte);
                        $('#hidden_idcon').val(id);
                        $('#addModal').modal('show');
                        setTimeout(function() {$('#qte').focus();}, 200);
                    }
                })
            });

            $(document).on('click', '.select', function(){
                var id = $(this).attr('id');
                var code = document.getElementById('code').value;
                document.getElementById('hidden_code').value = code;

                var magasin_source = document.getElementById('magasin_source').value;
                document.getElementById('hidden_magasin_source').value = magasin_source;
                //document.getElementById('mag_sour').value = magasin_source;

                $('#form_result').html('');
                $.ajax({
                    url:"tr.select/"+id,
                    dataType:"json",
                    success:function(data){
                        $('#idqp').val(data.stock_produit_id);
                        $('#lot').val(data.lot);
                        $('#libelle').val(data.libelle);
                        $('#produit_id').val(data.produit_id);
                        $('#qte').val('');
                        $('#ini').val(data.qte);
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
                //magasin_source = document.getElementById('mag_sour').value;
                $.ajax({
                    url:"tr.delete/"+id,
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_client').DataTable().ajax.reload();
                        }, 100);
                        $('#pdt_dest').load('tr.pdt_dest/'+code);
                        $("div.dataTables_filter input").focus();
                    }
                })
            });

        });
    </script>
@endsection
