@extends('layouts.comptalayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')

@section('extra-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">

        <div class="col-12 col-sm-12 col-md-12">
            <h3>{{__('messages.ENTREE ARTICLE')}}</h3>
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('ent.histo')}}" class="btn btn-primary"><i class="fa fa-info"></i> {{__('messages.Historique des entrees')}}</a>
            </div>
            <div class="col-12 col-md-4 float-left">
                <a href="{{route('sor.index')}}" class="btn btn-danger"><i class="fa fa-user"></i> {{__('messages.Sortie article')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-right">
                <a href="{{route('tr.index')}}" class="btn btn-warning"><i class="fa fa-database"></i> {{__('messages.Transfert article')}}</a>
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
                        <table class="table table-striped table-bordered contour_table" id="pdt_con">

                        </table>
                    </div>
                    <div class="info-box">
                        @include('entree/form')
                    </div>
                </div>
            </div>

            <div class="col-md-6 float-right">
                <div class="form-group cool-md-12 float-left">
                    <select name="mag_source" id="mag_source" class="form-control" onchange="actualiser()">
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
                                <td class="price">{{__('messages.Qte Initiale')}}</td>
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
                                                        <label class="control-label col-md-12">{{__('messages.Lot')}} </label>
                                                        <input type="text" name="lot" id="lot" class="form-control" readonly/>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.Qte Initiale')}}: </label>
                                                        <input type="text" name="ini" id="ini" class="form-control" readonly/>
                                                    </td>
                                                    <td width="50%">
                                                        <label class="control-label col-md-12">{{__('messages.Quantite ajoutee')}}: </label>
                                                        <input type="text" name="qte" id="qte" class="form-control" required="required" value="0"/>
                                                        <input type="hidden" name="pa" id="pa" class="form-control"/>
                                                    </td>
                                                </tr>
                                            </table>

                                        </div>
                                        <div class="form-group" align="center">
                                            <input type="hidden" name="pdt_num" id="pdt_num" />
                                            <input type="hidden" name="hidden_idcon" id="hidden_idcon" />
                                            <input type="hidden" name="idqp" id="idqp" />
                                            <input type="hidden" name="hidden_ent_num" id="hidden_ent_num" />
                                            <input type="hidden" name="hidden_mag_num" id="hidden_mag_num" />
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
                url:"ent.magasins",
                dataType:"json",
                success:function(data)
                {
                    $('#mag_source').empty();
                    $('#mag_source').append('<option id=0  value=0>- {{__('messages.Choisir un magasin')}} -</option>');
                    for (var i = 0; i < data.length; i++) {
                        $('#mag_source').append('<option id=' + data[i].mag_num + ' value=' + data[i].mag_num + '>' + data[i].mag_lib +'</option>');
                    }
                    $('#mag_source').change();
                }
            })
        }

        function getProduits(mag_num){
            $('#pdt_sour').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "ent.pdt_sour/" + mag_num
                },
                columns: [
                    {
                        data: 'lot',
                        name: 'lot'
                    },
                    {
                        data: 'pdt_lib',
                        name: 'pdt_lib'
                    },
                    {
                        data: 'qter',
                        name: 'qter'
                    },
                    {
                        data: 'id',
                        name: 'id',
                        render: function (data, type, row) {
                            return "<a href='#' id='" + row.id + "' class='btn btn-primary select'><i class='fa fa-check'></i></a>"
                        }
                    }
                ]
            })
        }

        function actualiser() {
            ent_num = document.getElementById("ent_num").value;
            mag_source = document.getElementById("mag_source").value;
            document.getElementById("mag_num").value=mag_source;
            if (mag_source!=0){
                $('#pdt_con').load('ent.pdt_con/'+ent_num+'/'+mag_source);
                getProduits(mag_source)
                $("div.dataTables_filter input").focus();
            }
        }

        $(document).ready(function(){
            actualiser();
            magasin();

            $("div.dataTables_filter input").focus();

            $('#add_form').on('submit', function(event){
                event.preventDefault();
                var ent_num = document.getElementById('ent_num').value;
                var mag_source = document.getElementById('mag_source').value;
                $.ajax({
                    url:"{{ route('ent.add') }}",
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
                            $('#pdt_con').load('ent.pdt_con/'+ent_num+'/'+mag_source);

                            $("div.dataTables_filter input").val('');
                            $("div.dataTables_filter input").focus();
                        }
                        $('#form_result').html(html);
                    }
                })
            });

            $(document).on('click', '.select', function(){
                var id = $(this).attr('id');
                var ent_num = document.getElementById('ent_num').value;
                document.getElementById('hidden_ent_num').value = ent_num;

                var mag_source = document.getElementById('mag_source').value;
                document.getElementById('hidden_mag_num').value = mag_source;
                document.getElementById('mag_num').value = mag_source;

                $('#form_result').html('');
                $.ajax({
                    url:"ent.select/"+id,
                    dataType:"json",
                    success:function(data){
                        $('#idqp').val(data.id);
                        $('#lot').val(data.lot);
                        $('#pa').val(data.pa);
                        $('#pdt_lib').val(data.pdt_lib);
                        $('#pdt_num').val(data.pdt_num);
                        $('#qte').val('');
                        $('#ini').val(data.qter);
                        $('#hidden_idcon').val("");
                        $('#action_button').val("{{__('messages.Ajouter')}}");
                        $('#addModal').modal('show');
                        setTimeout(function() {$('#qte').focus();}, 200);
                    }
                })
            });

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');
                var ent_num = document.getElementById('ent_num').value;
                document.getElementById('hidden_ent_num').value = ent_num;

                var mag_source = document.getElementById('mag_source').value;
                document.getElementById('hidden_mag_num').value = mag_source;
                document.getElementById('mag_num').value = mag_source;
                $('#form_result').html('');
                $.ajax({
                    url:"ent.select_edit/"+id,
                    dataType:"json",
                    success:function(data){
                        console.log(id,data)
                        $('#pdt_lib').val(data.pdt_lib);
                        $('#pdt_num').val(data.pdt_num);
                        $('#idqp').val(data.idqp);
                        $('#lot').val(data.lot);
                        $('#pa').val(data.pa);
                        $('#ini').val(data.ini);
                        $('#qte').val(data.qte);
                        $('#hidden_idcon').val(id);
                        $('#addModal').modal('show');
                        setTimeout(function() {$('#qte').focus();}, 200);
                    }
                })
            });


            $(document).on('click', '.delete', function(){
                id = $(this).attr('id');
                ent_num = document.getElementById('ent_num').value;
                mag_source = document.getElementById('mag_num').value;
                $.ajax({
                    url:"ent.delete/"+id,
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_client').DataTable().ajax.reload();
                        }, 100);
                        $('#pdt_con').load('ent.pdt_con/'+ent_num+'/'+mag_source);
                        $("div.dataTables_filter input").focus();
                    }
                })
            });

        });
    </script>
@endsection
