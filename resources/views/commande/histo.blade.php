@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')


@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
    <h3 class="ml-5">{{__('messages.HISTORIQUE DES COMMANDES')}}</h3>
        <div class="col-12 col-sm-12 col-md-12">
            <div class="col-12 col-md-4 float-left">
                <a href="{{route('cmde.index')}}" class="btn btn-success"><i class="fa fa-plus"></i>{{__('messages.Nouvelle commande')}}</a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 float-left">
                <a href="{{route('cmde.histo')}}" class="btn btn-danger"><i class="fa fa-info"></i>{{__('messages.Historique des Cmdes')}}</a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 float-right">
                <a href="{{route('rec.index')}}" class="btn btn-warning"><i class="fa fa-check"></i> {{__('messages.Reception Commande')}}</a>
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
                            <td class="description">{{__('messages.Date')}} </td>
                            <td class="price">{{__('messages.Code')}}</td>
                            <td class="price">{{__('messages.cout')}}</td>
                            <td class="price">{{__('messages.Fournisseur')}}</td>
                            <td class="price">{{__('messages.Etat')}}</td>
                            <td class="price">{{__('messages.Taux de satisfaction')}}</td>
                            <td class="total">{{__('messages.Utilisateur')}}</td>
                            <td>{{__('messages.Editer')}}</td>
                            <td>{{__('messages.Infos')}}</td>
                            <td>{{__('messages.Imprimer')}}</td>
                            <td>{{__('messages.Annuler')}}</td>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <!--Supprimer le produit -->
        <main id="confirmModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">{{__('messages.Confirmation')}}</h2>
                    </div>
                    <div class="modal-body">
                        <h5 align="center" style="margin:0;">{{__('messages.annuler commande')}}</h5>
                    </div>
                    <div class="modal-footer">
                        <button type="button" name="ok_button" id="ok_button" class="btn btn-danger">{{__('messages.Oui')}}</button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal">{{__('messages.Annuler')}}</button>
                    </div>
                </div>
            </div>
        </main>

        <!--Info suppression -->
        <main id="infoModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title">{{__('messages.Information')}}</h2>
                    </div>
                    <div class="modal-body">
                        <h5 align="center" style="margin:0;">{{__('messages.Impossible annuler commande')}}</h5>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{{__('messages.Fermer')}}</button>
                    </div>
                </div>
            </div>
        </main>

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
                        url:'{{ route("cmde.histo") }}',
                        data:{from_date:from_date, to_date:to_date}
                    },
                    columns: [
                        {
                            data:'date_commande',
                            name:'date_commande'
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
                            data:'nom',
                            name:'nom'
                        },
                        {
                            data:'etat',
                            name:'etat'
                        },
                        {
                            data:'taux',
                            name:'taux'
                        },
                        {
                            data:'name',
                            name:'name'
                        },
                        {
                            data:'commande_id',
                            name:'commande_id',
                            render:function (data, type, row) {
                                return "<a href='#t' class='btn btn-success'><i class='fa fa-edit'></i></a>"}
                        },
                        {
                            data:'commande_id',
                            name:'commande_id',
                            render:function (data, type, row) {
                                return "<a href='#' id='"+row.commande_id+"' class='btn btn-warning infos'><i class='fa fa-info'></i></a>"}
                        },
                        {
                            data:'commande_id',
                            name:'commande_id',
                            render:function (data, type, row) {
                                return "<a href='cmde/"+row.commande_id+"' class='btn btn-info'><i class='fa fa-print'></i></a>"}
                        },
                        {
                            data:'commande_id',
                            name:'commande_id',
                            render:function (data, type, row) {
                                return "<a href='#' id='"+row.commande_id+"' class='btn btn-danger delete'><i class='fa fa-trash'></i></a>"}
                        }

                    ],
                    'rowCallback': function(row, data, index) {
                        if (data.cmde_etat == 'Livree') {
                            $(row).find('td:eq(4)').css('background-color', 'green').css('color', 'white');
                        }
                        if (data.cmde_etat == 'Annulee') {
                            $(row).find('td:eq(4)').css('background-color', 'red').css('color', 'white');
                        }
                        if (data.cmde_etat == 'Partielle') {
                            $(row).find('td:eq(4)').css('background-color', 'yellow').css('color', 'black');
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
                    alert('Selectionner la periode');
                }
            });

            $('#reset').click(function(){
                $('#from_date').val('');
                $('#to_date').val('');
                $('#histo_cmde').DataTable().destroy();
                load_data();
            });

            var commande_id;
            $(document).on('click', '.delete', function(){
                commande_id = $(this).attr('id');
                $.ajax({
                    url:"cmde.rech_cmde/"+commande_id,
                    dataType:"json",
                    success:function(data)
                    {
                        if ((data.cmde_etat=='Partielle') || (data.cmde_etat=='Livree')){
                            $('#infoModal').modal('show');
                        }else {
                            $('.modal-title').text("Confirmation");
                            $('#ok_button').text('Oui');
                            $('#confirmModal').modal('show');
                        }
                    }
                })
            });

            $('#ok_button').click(function(){
                $.ajax({
                    url:"cmde.delete_cmde/"+commande_id,
                    beforeSend:function(){
                        $('#ok_button').text('Suppression...');
                    },
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#histo_cmde').DataTable().ajax.reload();
                        }, 500);
                    }
                })
            });

            $(document).on('click', '.infos', function(){
                cmde_num = $(this).attr('id');
                $('#details_cmde').load('cmde.infos/'+cmde_num);
                $('#infosCmdeModal').modal('show');
            });
        });
    </script>
@endsection
