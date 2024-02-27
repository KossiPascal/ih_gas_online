@extends('layouts.adminlayout')
@section('title','GESTIMMOB V2: Gestion Proprietaire')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <h3 class="ml-5">{{__('messages.GESTION DE LA TRESORERIE')}}</h3>
        <div class="col-12 col-sm-12 col-md-12">
            <div class="col-12 col-md-6 float-left">

            </div>
            <div class="col-12 col-sm-6 col-md-5 float-right">
                <button type="button" name="create_pro" id="create_pro" class="btn btn-success"><i class="fa fa-plus"></i> {{__('messages.Nouvelle Operation')}}</button>
            </div>
            <!-- /.info-box-content -->
        </div>
        <br>
        <div class="info-box mb-1">
            <div class="row input-daterange">
                <div class="col-12 col-md-3">
                    <input type="text" name="from_date" id="from_date" class="form-control" placeholder="{{__('messages.Date Debut')}}" readonly />
                </div>
                <div class="col-12 col-md-3">
                    <input type="text" name="to_date" id="to_date" class="form-control" placeholder="{{__('messages.Date Fin')}}" readonly />
                </div>

                <div class="col-12 col-md-6">
                    <button type="button" name="filter" id="filter" class="btn btn-primary">{{__('messages.Rechercher')}}</button>
                    <button type="button" name="reset" id="reset" class="btn btn-danger">{{__('messages.Actualiser')}}</button>
                    <button type="button" name="imprimer" id="imprimer" class="btn btn-warning">{{__('messages.Imprimer')}}</button>
                </div>
            </div>
            <!-- /.info-box-content -->
        </div>
        <br>
        <div class="info-box">
            <div class="table-responsive">
                <table id="liste_operation" class="table table-striped table-bordered data-table">
                    <thead>
                    <tr>
                        <th>{{__('messages.DATE')}}</th>
                        <th>{{__('messages.OPERATION')}}</th>
                        <th>{{__('messages.INITIALE')}}</th>
                        <th>{{__('messages.ENTREE')}}</th>
                        <th>{{__('messages.SORTIE')}}</th>
                        <th>{{__('messages.SOLDE')}}</th>
                        <th>{{__('messages.OPERANT')}}</th>
                        <th>{{__('messages.EDIT')}}</th>
                    </tr>
                    </thead>
                </table>
            </div>

            <!--Ajouter une operation -->
            <div id="operationModal" class="modal fade" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{__('messages.Nouvelle Operation')}}</h4>
                        </div>
                        <div class="modal-body">
                            <span id="form_result"></span>
                            <form method="post" id="op_form" class="form-horizontal">
                                @csrf
                                <table class="table">
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <label class="control-label col-md-12" >{{__('messages.Type operation')}} </label>
                                                <select name="type_operation" id="type_operation" class="form-control" required="required">
                                                    @foreach($type_operation as $key=>$operation)
                                                        <option value= "{!! $operation !!}"> {!! $operation !!} </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <label class="control-label col-md-12" >{{__('messages.Date')}} : </label>
                                                <input type="date" name="date" id="date" value="{{date('Y-m-d')}}" class="form-control" required="required"/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <div class="form-group">
                                                <label class="control-label col-md-12" >{{__('messages.Libelle')}} </label>
                                                <input type="text" name="libelle" id="libelle" class="form-control" required="required"/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <label class="control-label col-md-12" >{{__('messages.Montant')}} : </label>
                                                <input type="text" name="montant" id="montant" class="form-control" required="required"/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <label class="control-label col-md-12" >{{__('messages.Operateur')}} : </label>
                                                <input type="text" name="operant" id="operant" class="form-control" required="required"/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="action" id="action" />
                                            <input type="hidden" name="operation_id" id="operation_id" />
                                            <input type="submit" name="action_button" id="action_button" class="btn btn-success" value="{{__('messages.Enregistrer')}}" />
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Quitter')}}</button>
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!--Details operation -->
            <div id="detailsModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modaltitle">{{__('messages.Details Operation')}}</h4>
                        </div>
                        <div class="modal-body">
                            <span id="form_result"></span>
                            <form method="post" id="op_form" class="form-horizontal">
                                @csrf

                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('nessages.Type operation')}} </label>
                                    <input type="text" name="optype" id="optype" class="form-control" readonly/>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Date')}} : </label>
                                    <input type="text" name="opdate" id="opdate" class="form-control" readonly/>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Libelle')}} </label>
                                    <input type="text" name="oplib" id="oplib" class="form-control" readonly/>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Operateur')}} : </label>
                                    <input type="text" name="opoperateur" id="opoperateur" class="form-control" readonly/>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Montant')}} : </label>
                                    <input type="text" name="opmont" id="opmont" class="form-control" readonly/>
                                </div>

                                <div class="form-group" align="center">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Fermer')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!--Informations -->
            <main id="infosModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2>Infos</h2>
                        </div>
                        <div class="modal-body">
                            <h5 align="center" style="margin:0;">{{__('messages.Impossible de modifier un emolument')}}.</h5>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">{{__('messages.Fermer')}}</button>
                        </div>
                    </div>
                </div>
            </main>

        </div>
    </main>
@endsection

@section('extra-js')
    <script>
        function type_operation() {
            $.ajax({
                url:"op.operations",
                dataType:"json",
                success:function(data)
                {
                    $('#to_id').empty();
                    $('#to_id').append('<option id=0  value=0>-{{__('messages.Choisir operation')}}-</option>');
                    for (var i = 0; i < data.length; i++) {
                        $('#to_id').append('<option id=' + data[i].to_id + ' value=' + data[i].to_id + '>' + data[i].to_lib +'</option>');
                    }
                    $('#to_id').change();
                }
            })
        }

        function proprietaires() {
            $.ajax({
                url:"op.proprietaires",
                dataType:"json",
                success:function(data)
                {
                    $('#pro_id').empty();
                    $('#pro_id').append('<option id=0  value=0>-{{__('messages.Choisir un proprietaire')}}-</option>');
                    for (var i = 0; i < data.length; i++) {
                        $('#pro_id').append('<option id=' + data[i].pro_id + ' value=' + data[i].pro_id + '>' + data[i].pro_np +'</option>');
                    }
                    $('#pro_id').change();
                }
            })
        }

        function rech_to() {
            id = document.getElementById('to_id').value;
            $.ajax({
                url:"op.rech_op/"+id,
                dataType:"json",
                success:function(data)
                {
                    document.getElementById('libelle').value = data.to_lib;
                    document.getElementById('to_nc').value = data.to_nc;
                }
            })
        }

        $(document).ready(function(){
            proprietaires();

            $('.input-daterange').datepicker({
                todayBtn:'linked',
                format:'yyyy-mm-dd',
                autoclose:true
            });
            type_operation();

            load_data();

            function load_data(from_date = '', to_date = '') {
                $('#liste_operation').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route("op.index") }}',
                        data: {from_date: from_date, to_date: to_date}
                    },
                    columns: [
                        {
                            data: 'date',
                            name: 'date'
                        },
                        {
                            data: 'libelle',
                            name: 'libelle'
                        },
                        {
                            data: 'initiale',
                            name: 'initiale'
                        },
                        {
                            data: 'entree',
                            name: 'entree'
                        },
                        {
                            data: 'sortie',
                            name: 'sortie'
                        },
                        {
                            data: 'solde',
                            name: 'solde'
                        },
                        {
                            data: 'operant',
                            name: 'operant'
                        },
                        {
                            data: 'operation_id',
                            name: 'operation_id',
                            render: function (data, type, row) {
                                return "<a href='#' id='" + row.operation_id + "' class='btn btn-success editer'><i class='fa fa-edit'></i></a>"
                            }
                        }
                    ]
                })
            };

            $('#filter').click(function(){
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                if(from_date != '' && to_date != '')
                {
                    $('#liste_operation').DataTable().destroy();
                    load_data(from_date, to_date);
                }
                else
                {
                    alert('{{__('messages.Selectionner la periode')}}');
                }
            });

            $('#reset').click(function(){
                $('#from_date').val('');
                $('#to_date').val('');
                $('#histo_cmde').DataTable().destroy();
                load_data();
            });

            $('#create_pro').click(function(){
                $('.modal-title').text("{{__('messages.Nouvelle Operation')}}");
                $('#action_button').val("{{__('messages.Enregistrer')}}");
                $('#action').val("{{__('messages.Ajouter')}}");
                type_operation();
                proprietaires();
                $('#op_form')[0].reset();
                $('#operation_id').val('');
                $('#form_result').val('');
                $('#operationModal').modal('show');
            });

            $('#op_form').on('submit', function(event){
                event.preventDefault();
                $.ajax({
                    url:"{{ route('op.store') }}",
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
                        if(data.success)
                        {
                            html = '<div class="alert alert-success">' + data.success + '</div>';
                            $('#op_form')[0].reset();
                            $('#operation_id').val('');
                            $('#liste_operation').DataTable().ajax.reload();
                        }

                        if(data.error)
                        {
                            html = '<div class="alert alert-danger">' + data.error + '</div>';
                        }
                        $('#form_result').html(html);
                    }
                })
            });

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');
                $('#form_result').html('');

                $.ajax({
                    url:"op/"+id+"/edit",
                    dataType:"json",
                    success:function(data){
                        $('#op_type').empty();
                        $('#op_type').append('<option id=' + data.op_type + ' value=' + data.op_type + '>' + data.op_type +'</option>');
                        $('#op_type').change();

                        $('#date').val(data.date);
                        $('#libelle').val(data.libelle);
                        $('#operant').val(data.operant);
                        if(data.entree==0){
                            $('#montant').val(data.sortie);
                        }else{
                            $('#montant').val(data.entree);
                        }
                        $('#operation_id').val(id);
                        $('.modal-title').text("{{__('messages.Editer cette operation')}}");
                        $('#action_button').val("{{__('messages.Valider')}}");
                        $('#action').val("{{__('messages.Editer')}}");
                        $('#operationModal').modal('show');
                    }
                })
            });

            $(document).on('click', '.details', function(){
                var id = $(this).attr('id');
                $('#form_result').html('');

                $.ajax({
                    url:"op/"+id+"/edit",
                    dataType:"json",
                    success:function(data){
                        $('#optype').val(data.op_type);
                        $('#pronp').val(data.pro_np);
                        $('#opdate').val(data.date);
                        $('#oplib').val(data.libelle);
                        $('#opoperateur').val(data.operant);
                        if(data.entree==0){
                            $('#opmont').val(data.sortie);
                        }else{
                            $('#opmont').val(data.entree);
                        }
                        $('#detailsModal').modal('show');
                    }
                })


            });

            /*$('#imprimer').click(function(){
                var debut = document.getElementById('from_date').value;
                var fin = document.getElementById('to_date').value;

                if(debut != '' && fin != '')
                {
                    var newWin = window.open();
                    var the_url = "op.histo/"+debut+"/"+fin;
                    $.ajax({
                        type: "GET", url: the_url, data: {},
                        success: function(data){
                            //console.log(data);
                            newWin.document.write(data.data);
                        }
                        ,error: function() {
                        }
                    });
                }
                else
                {
                    alert('Selectionner la periode');
                }
            });*/

            $('#imprimer').click(function(){
                var debut = document.getElementById('from_date').value;
                var fin = document.getElementById('to_date').value;

                if(debut != '' && fin != '')
                {
                    var newWin = window.open();
                    var the_url = "op.etat_pro/"+debut+"/"+fin;
                    $.ajax({
                        type: "GET", url: the_url, data: {},
                        success: function(data){
                            //console.log(data);
                            newWin.document.write(data.data);
                        }
                        ,error: function() {
                        }
                    });
                }
                else
                {
                    alert('{{__('messages.Selectionner la periode')}}');
                }
            });

        });
    </script>
@endsection
