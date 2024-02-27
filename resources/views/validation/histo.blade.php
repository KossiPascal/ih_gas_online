@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')


@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
    <h3 class="ml-5">{{__('messages.HISTORIQUE DES VALIDATIONS')}}</h3>
        <div class="col-12 col-sm-12 col-md-12">
            <div class="col-12 col-md-4 float-left">
                <a href="{{route('val.index')}}" class="btn btn-success"><i class="fa fa-plus"></i> {{__('messages.Validation Commande')}}</a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 float-left">
                <a href="{{route('val.histo')}}" class="btn btn-danger"><i class="fa fa-list"></i> {{__('messages.Historique des validations')}}</a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 float-right">
                <a href="{{route('val.index')}}" class="btn btn-warning"><i class="fa fa-check"></i> {{__('messages.Suivi Commandes')}}</a>
            </div>
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
                    <button type="button" name="refresh" id="reset" class="btn btn-danger">{{__('messages.Actualiser')}}</button>
                </div>
            </div>
            <!-- /.info-box-content -->
        </div>

        <div class="col-md-12">
            <div class="info-box">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="histo_cmde">
                        <thead>
                        <tr class="cart_menu">
                            <td class="description">{{__('messages.Date validation')}}</td>
                            <td class="price">{{__('messages.Code Cmde')}}</td>
                            <td class="price">{{__('messages.cout')}}</td>
                            <td class="price">{{__('messages.Centre')}}</td>
                            <td class="total">{{__('messages.Utilisateur')}}</td>
                            <td class="total">{{__('messages.Etat')}}</td>
                            <td>{{__('messages.Actions')}}</td>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <!--Ajouter une observation -->
        <div id="observationModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">{{__('messages.Noter une observation')}}</h4>
                    </div>
                    <div class="modal-body">
                        <span id="form_result"></span>
                        <form method="post" id="observation_form" class="form-horizontal">
                            @csrf

                            <div class="form-group">
                                <label class="control-label col-md-12" >{{__('messages.Infos Commande')}} : </label>
                                <input type="text" name="infocmde" id="infocmde" class="form-control" readonly/>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-12" >{{__('messages.Date Observation')}} : </label>
                                <input type="date" name="date" id="date" class="form-control" required="required"/>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-12" >{{__('messages.Observation ')}}: </label>
                                <input type="text" name="observation" id="observation" class="form-control" required="required"/>
                            </div>


                            <div class="form-group" align="center">
                                <input type="hidden" name="commande_id" id="commande_id" />
                                <input type="hidden" name="validation_id" id="validation_id"/>
                                <input type="hidden" name="centre_id" id="centre_id" />
                                <input type="submit" name="action_button" id="action_button" class="btn btn-success" value="{{__('messages.Enregistrer')}}" />
                                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Quitter')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!--Info cmde -->
        <main id="infosCmdeModal" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        {{__('messages.Details commande')}}
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <div class="col-md-12">
                                <table id="details_cmde" class="table">

                                </table>
                            </div>
                        </div>

                        <div class="form-group" align="center">
                            <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Quitter')}}</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>


    </main>
@endsection

@section('extra-js')
    <script>
        $(document).ready(function(){
            $('.input-daterange').datepicker({
                todayBtn:'linked',
                format:'yyyy-mm-dd',
                autoclose:true
            });

            load_data();

            function load_data(from_date = '', to_date = '') {
                $('#histo_cmde').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url:'{{ route("val.histo") }}',
                        data:{from_date:from_date, to_date:to_date}
                    },
                    columns: [
                        {
                            data:'date',
                            name:'date'
                        },
                        {
                            data:'code',
                            name:'code'
                        },
                        {
                            data:'montant',
                            name:'montant'
                        },
                        {
                            data:'nom_centre',
                            name:'nom_centre'
                        },
                        {
                            data:'name',
                            name:'name'
                        },
                        {
                            data:'etat',
                            name:'etat'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false
                        }

                    ],
                    'rowCallback': function(row, data, index) {
                        if (data.observation == 'RAS') {
                            $(row).find('td:eq(5)').css('background-color', 'green').css('color', 'white');
                        }
                        if (data.cmde_etat == 'Annulee') {
                            $(row).find('td:eq(5)').css('background-color', 'red').css('color', 'white');
                        }
                        if (data.cmde_etat == 'Partielle') {
                            $(row).find('td:eq(5)').css('background-color', 'yellow').css('color', 'black');
                        }
                    }
                });
            }

            $('#filter').click(function(){
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                if(from_date != '' && to_date != '')
                {
                    $('#histo_cmde').DataTable().destroy();
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

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');

                $('#form_result').html('');
                $.ajax({
                    url:"val.val/"+id,
                    dataType:"json",
                    success:function(data){
                        $('.modal-title').text("{{__('messages.Modifier cette donnees')}}");
                        $('#infocmde').val('Cmde Num '+data.code+' / Centre: '+data.nom_centre);
                        $('#validation_id').val(data.validation_id);
                        $('#date').val(data.date);
                        $('#commande_id').val(data.commande_id);
                        $('#centre_id').val(data.centre_id);
                        $('#observation').val(data.observation);
                        $('#source_action').val("2");
                        $('#observationModal').modal('show');
                        setTimeout(function() {$('#observation').focus();}, 200);
                    }
                })
            });

            $('#observation_form').on('submit', function(event){
                event.preventDefault();
                $.ajax({
                    url:"{{ route('val.update_val') }}",
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
                            $('#observation_form')[0].reset();
                            $('#observationModal').modal('hide');
                            $('#liste_cmde').DataTable().ajax.reload();
                        }
                        $('#form_result').html(html);
                    }
                })
            });

            $(document).on('click', '.details', function(){
                cmde_num = $(this).attr('id');
                $('#details_cmde').load('val.details/'+cmde_num);
                $('#infosCmdeModal').modal('show');
            });
        });
    </script>
@endsection
