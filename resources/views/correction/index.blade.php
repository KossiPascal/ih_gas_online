@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')

@section('extra-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">
        <div class="col-md-12"><h3 class="ml-5" style="text-align:center">{{__('messages.CORRECTION DU STOCK')}}</h3></div>
        <div class="col-md-12">
            <div class="col-md-6 float-left">
                <h3 class="ml-5"></h3>
            </div>

            <div class="col-md-6 float-right" style="text-align: right">
                <a href="{{route('cs.histo')}}" class="btn btn-warning"><i class="fa fa-list"></i> {{__('messages.HISTORIQUE DES CORRECTIONS')}}</a>
            </div>
        </div>
        <div class="col-md-12 float-left">
            <div class="col-md-6 float-left">
                <h5 class="ml-5">{{__('messages.PRODUITS CONCERNES')}}</h5>
            </div>

            <div class="col-md-6 float-right">
                <h5> {{__('messages.LISTE DES PRODUITS')}} </h5>
            </div>
        </div>

        <div class="col-md-12 float-left">
            <div class="col-md-7 float-left">
                <div class="contour_table">
                    <table class="table table-striped table-bordered contour_table" id="pdt_con">
                        <thead>
                        <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                            <td class="description">{{__('messages.Produit')}}</td>
                            <td class="description">{{__('messages.Lot')}}</td>
                            <td class="price">{{__('messages.Operation')}}</td>
                            <td class="quantity">{{__('messages.Qte')}}</td>
                            <td></td>
                            <td></td>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <div class="info-box">
                    @include('correction/form')
                </div>
            </div>

            <div class="col-md-5 float-right">
                <div class="form-group cool-md-12 float-left">
                    <select name="magasin_id" id="magasin_id" class="form-control" onchange="actualiser()">
                        @foreach($magasins as $key=>$magasin)
                            <option value= "{!! $magasin !!}"> {!! $magasin !!} </option>
                        @endforeach
                    </select>
                </div>
                <div class="info-box">
                    <div class="table-responsive div_style">
                        <table id="pdt_mag" class="display table table-striped table-bordered data-table">
                            <thead>
                            <tr>
                                <th>{{__('messages.Lot')}}</th>
                                <th>{{__('messages.Libelle')}}</th>
                                <th>{{__('messages.Qte')}}</th>
                                <th>{{__('messages.Select')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>

                    <!--Ajouter un client -->
                    <div id="addpdtModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">{{__('messages.Ajouter un nouveau produit')}}</h4>
                                </div>
                                <div class="modal-body">
                                    <span class="form_result" id="form_result"></span>
                                    <form method="post" id="addForm" class="form-horizontal">
                                        @csrf

                                        <div class="form-group">
                                            <label class="control-label col-md-12" id="pdt_lib">{{__('messages.Denomination')}}: </label>
                                        </div>
                                        <div class="form-group">
                                            {!! Form::label(__('messages.Type de correction')) !!}
                                            {!! Form::select('type',$cs_type,null,['class'=>'form-control','id'=>'type']) !!}
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-md-12" >{{__('messages.Quantite a corriger')}}: </label>
                                            <input type="text" name="qte" id="qte" class="form-control" required="required"/>
                                            <input type="hidden" name="qtes" id="qtes" class="form-control" required="required"/>
                                        </div>
                                        <div class="form-group" align="center">
                                            <input type="hidden" name="idcon" id="idcon" />
                                            <input type="hidden" name="stock_produit_id" id="stock_produit_id" />
                                            <input type="hidden" name="hidden_code" id="hidden_code" />
                                            <input type="hidden" name="produit_id" id="produit_id" />
                                            <input type="hidden" name="libelle" id="libelle" />
                                            <input type="hidden" name="pu" id="pu" />
                                            <input type="hidden" name="lot" id="lot" />
                                            <input type="hidden" name="mag_id" id="mag_id" />
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
                url:"rec.magasins",
                dataType:"json",
                success:function(data)
                {
                    $('#magasin_id').empty();
                    $('#magasin_id').append('<option id=0  value=0>- {{__('messages.Choisir un magasin')}} -</option>');
                    for (var i = 0; i < data.length; i++) {
                        $('#magasin_id').append('<option id=' + data[i].magasin_id + ' value=' + data[i].magasin_id + '>' + data[i].libelle +'</option>');
                    }
                    $('#magasin_id').change();
                }
            })
        }
        function actualiser() {
            code_cs = document.getElementById("code_cs").value;
            magasin_id = document.getElementById("magasin_id").value;
            if (magasin_id!=0){
                //$('#pdt_mag').load('cs.pdt_mag/'+magasin_id);
                getProduits(magasin_id);
                $('#pdt_con').load('cs.pdt_con/'+code_cs+'/'+magasin_id);
                $("div.dataTables_filter input").focus();
                document.getElementById("magnum").value=magasin_id;
                document.getElementById("mag_id").value=magasin_id;
            }
            rechmont();
            $("div.dataTables_filter input").focus();
        }

        function getProduits(magasin_id){
            $('#pdt_mag').DataTable().destroy();
            $('#pdt_mag').DataTable({
                processing: true,
                serverSide: true,
                paging: false,
                searching: true,
                ajax: {
                    url: 'cs.pdt_mag/'+magasin_id
                },
                columns: [
                    {
                        data: 'lot',
                        name: 'lot'
                    },
                    {
                        data: 'nom_commercial',
                        name: 'nom_commercial'
                    },
                    {
                        data: 'qte',
                        name: 'qte'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    }
                ]
            })
        }

        function rechmont() {
            code_cs = document.getElementById("code_cs").value;
            magasin_id = document.getElementById("magasin_id").value;
            if (magasin_id!=0){
                $.ajax({
                    url:"cs.rech_mont/"+code_cs,
                    success:function(data)
                    {
                        document.getElementById('cout').value = data;
                        $("div.dataTables_filter input").focus();
                    }
                });
            }else {
                document.getElementById('cout').value = 0;
            }
        }

        $(document).ready(function(){
            actualiser()
            magasin();
            $("div.dataTables_filter input").focus();

            $('#addForm').on('submit', function(event){
                event.preventDefault();
                var code_cs = document.getElementById('code_cs').value;
                var magasin_id = document.getElementById('magasin_id').value;
                $.ajax({
                    url:"{{ route('cs.add') }}",
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
                            rechmont();
                            $('#addForm')[0].reset();
                            $('#addpdtModal').modal('hide');
                            $('#pdt_con').load('cs.pdt_con/'+code_cs+'/'+magasin_id);
                            $("div.dataTables_filter input").val('');
                            $("div.dataTables_filter input").focus();
                        }
                        $('#form_result').html(html);
                        rechmont();
                    }
                })
            });

            $(document).on('click', '.select', function(){
                var id = $(this).attr('id');
                var code_cs = document.getElementById('code_cs').value;
                document.getElementById('code_cs').value = code_cs;
                $('#form_result').html('');
                $.ajax({
                    url:"cs.select/"+id,
                    dataType:"json",
                    success:function(data){
                        $('#stock_produit_id').val(data.stock.stock_produit_id);
                        $('#qte').val('');
                        $('#pdt_lib').html('PRODUIT : '+data.stock.libelle+' / Prix Vente : '+data.produit.prix_vente);
                        $('#produit_id').val(data.produit.produit_id);
                        $('#libelle').val(data.stock.libelle);
                        $('#pu').val(data.produit.prix_vente);
                        $('#lot').val(data.stock.lot);
                        $('#idcon').val('');
                        $('#qtes').val(data.stock.qte);
                        $('#hidden_code').val(code_cs);
                        $('.modal-title').text("{{__('messages.Saisir la quantite a corriger')}}");
                        $('#action_button').val("Valider");
                        $('#addpdtModal').modal('show');
                        setTimeout(function() {$('#qte').focus();}, 200);
                    }
                })
            });

            $(document).on('click', '.edit', function(){
                var id = $(this).attr('id');
                var code_cs = document.getElementById('code_cs').value;
                document.getElementById('code_cs').value = code_cs;
                magasin_id = document.getElementById("magasin_id").value;
                $('#form_result').html('');
                $.ajax({
                    url:"cs.select_edit/"+id,
                    dataType:"json",
                    success:function(data){
                        $('#pdt_lib').html('PRODUIT : '+data.produit.libelle+' / Prix Vente : '+data.produit.pu);
                        $('#produit_id').val(data.produit.produit_id);
                        $('#libelle').val(data.produit.libelle);
                        $('#pu').val(data.produit.pu);
                        $('#stock_produit_id').val(data.produit.stock_produit_id);
                        $('#lot').val(data.produit.lot);
                        $('#type').val(data.produit.motif);
                        $('#idcon').val(id);
                        $('#qte').val(data.produit.qte);
                        $('#qtes').val(data.stock.qte);
                        $('#hidden_code').val(code_cs);
                        $('.modal-title').text("{{__('messages.Saisir la quantite a corriger')}}");
                        $('#action_button').val("Valider");
                        $('#mag_id').val(magasin_id);
                        $('#addpdtModal').modal('show');
                        setTimeout(function() {$('#qte').focus();}, 200);
                    }
                })
            });


            $(document).on('click', '.delete', function(){
                id = $(this).attr('id');
                code_cs = document.getElementById('code_cs').value;
                magasin_id = document.getElementById('magasin_id').value;
                $.ajax({
                    url:"cs.delete/"+id,
                    success:function(data)
                    {
                        $('#pdt_con').load('cs.pdt_con/'+code_cs+'/'+magasin_id);
                        $("div.dataTables_filter input").focus();
                    }
                })
                rechmont();
            });

        });
    </script>
@endsection
