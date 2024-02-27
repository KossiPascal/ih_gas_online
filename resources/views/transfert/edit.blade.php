@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')

@section('extra-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">
        <h3 class="ml-5">{{__('messages.MODIFIER UN TRANSFERT ARTICLE')}}</h3>
        <div class="col-12 col-sm-12 col-md-12">
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('tr.index')}}" class="btn btn-primary"><i class="fa fa-info"></i> {{__('messages.Nouveau Transfert')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('ent.index')}}" class="btn btn-warning"><i class="fa fa-database"></i> {{__('messages.Entree article')}}</a>
            </div>
            <div class="col-12 col-md-4 float-right">
                <a href="{{route('sor.index')}}" class="btn btn-danger"><i class="fa fa-user"></i> {{__('messages.Sortie article')}}</a>
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
                        @include('transfert/formedit')
                    </div>
                </div>
            </div>

            <div class="col-md-6 float-right">
                <div class="form-group cool-md-12 float-left">
                    {!! Form::select('mag_source',$mag_sour,['class'=>'form-control','id'=>'mag_source','onChange'=>'actualiser()']) !!}
                </div>
                <div class="info-box">
                    <div class="table-responsive div_style">
                        <table id="pdt_sour" class="display table table-striped table-bordered data-table">
                            <thead>
                            <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                                <td class="description">{{__('messages.Lot')}}</td>
                                <td class="description">{{__('messages.Produit')}}</td>
                                <td class="price">{{__('messages.Qte dispo')}}</td>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $count =1;?>
                            @foreach($produits as $produit)
                                <tr>
                                    <td width="90">
                                        <a href="#" id="{{$produit->pdt_num}}" class="select">{{$produit->pdt_lib}}</a>
                                    </td>
                                    <td width="6" style="text-align: right">{{$produit->lot}}</td>
                                    <td width="6" style="text-align: right">{{$produit->qter}}</td>
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
                                            <input type="hidden" name="pdt_num" id="pdt_num" />
                                            <input type="hidden" name="hidden_idcon" id="hidden_idcon" />
                                            <input type="hidden" name="idqp" id="idqp" />
                                            <input type="hidden" name="hidden_tr_num" id="hidden_tr_num" />
                                            <input type="hidden" name="hidden_mag_source" id="hidden_mag_source" />
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
        function actualiser() {
            tr_num = document.getElementById("tr_num").value;
            mag_source = document.getElementById("mag_source").value;
            document.getElementById("mag_sour").value=mag_source;
            $('#pdt_dest').load('tr.pdt_dest/'+tr_num+'/'+mag_source);
            $("div.dataTables_filter input").focus();
            console.log(tr_num,mag_source);
        }

        $(document).ready(function(){
            $('#pdt_sour').DataTable({
                language: {
                    searchS: "{{__('messages.Recherche produit')}}"
                }
            });
            actualiser();
            $("div.dataTables_filter input").focus();

            $('#add_form').on('submit', function(event){
                event.preventDefault();
                var tr_num = document.getElementById('tr_num').value;
                var mag_source = document.getElementById('mag_source').value;
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
                            $('#pdt_dest').load('tr.pdt_dest/'+tr_num+'/'+mag_source);

                            $("div.dataTables_filter input").focus();
                        }
                        $('#form_result').html(html);
                    }
                })
            });

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');
                var tr_num = document.getElementById('tr_num').value;
                document.getElementById('hidden_tr_num').value = tr_num;

                var mag_source = document.getElementById('mag_source').value;
                document.getElementById('hidden_mag_source').value = mag_source;
                document.getElementById('mag_sour').value = mag_source;
                $('#form_result').html('');
                $.ajax({
                    url:"tr.select_edit/"+id,
                    dataType:"json",
                    success:function(data){
                        console.log(id,data)
                        $('#pdt_ref').val(data.pdt_ref);
                        $('#pdt_lib').val(data.pdt_lib);
                        $('#pdt_num').val(data.pdt_num);
                        $('#idqp').val(data.idqp);
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
                var tr_num = document.getElementById('tr_num').value;
                document.getElementById('hidden_tr_num').value = tr_num;

                var mag_source = document.getElementById('mag_source').value;
                document.getElementById('hidden_mag_source').value = mag_source;
                document.getElementById('mag_sour').value = mag_source;

                $('#form_result').html('');
                $.ajax({
                    url:"tr.select/"+id,
                    dataType:"json",
                    success:function(data){
                        $('#idqp').val(data.id);
                        $('#lot').val(data.lot);
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


            $(document).on('click', '.delete', function(){
                id = $(this).attr('id');
                tr_num = document.getElementById('tr_num').value;
                mag_source = document.getElementById('mag_sour').value;
                $.ajax({
                    url:"tr.delete/"+id,
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_client').DataTable().ajax.reload();
                        }, 100);
                        $('#pdt_dest').load('tr.pdt_dest/'+tr_num+'/'+mag_source);
                        $("div.dataTables_filter input").focus();
                    }
                })
            });

        });
    </script>
@endsection
