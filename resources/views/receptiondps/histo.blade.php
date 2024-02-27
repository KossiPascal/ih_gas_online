@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')


@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <h3 class="ml-5">{{__('messages.HISTORIQUE DES RECEPTIONS')}}</h3>
        <div class="col-12 col-sm-12 col-md-12">
            <div class="col-12 col-md-4 float-left">
                <a href="{{route('recdps.index')}}" class="btn btn-success"><i class="fa fa-plus"></i> {{__('messages.Recevoir Une comande')}}</a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 float-left">
                <a href="{{route('recdps.histo')}}" class="btn btn-danger"><i class="fa fa-list"></i> {{__('messages.Suivi Commande')}}</a>
            </div>
            <div class="col-12 col-sm-6 col-md-4 float-right">
                <a href="{{route('trdps.index')}}" class="btn btn-warning"><i class="fa fa-check"></i> {{__('messages.Transfert vers FS')}}</a>
            </div>
        </div>

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
                    <table class="table table-striped table-bordered" id="histo_prod">
                        <thead>
                        <tr class="cart_menu">
                            <td class="description">{{__('messages.Date Reception')}}</td>
                            <td class="price">{{__('messages.Code Commande')}}</td>
                            <td class="price">{{__('messages.Date Commande')}}</td>
                            <td class="price">{{__('messages.Montant')}}</td>
                            <td class="price">{{__('messages.Fournisseur')}}</td>
                            <td class="total">{{__('messages.Centre')}}</td>
                            <td class="total">{{__('messages.Taux de satisfaction')}}</td>
                            <td>{{__('messages.Editer')}}</td>
                            <td>{{__('messages.Details')}}</td>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!--Info cmde -->
    <main id="infosRecModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    {{__('messages.Details Reception')}}
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <div class="col-md-12">
                            <table id="details_rec" class="table">

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

            function load_data(from_date = '', to_date = '')
            {
                $('#histo_prod').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url:'{{ route("recdps.histo") }}',
                        data:{from_date:from_date, to_date:to_date}
                    },
                    columns: [
                        {
                            data:'date_reception',
                            name:'date_reception'
                        },
                        {
                            data:'code',
                            name:'code'
                        },
                        {
                            data:'date_commande',
                            name:'date_commande'
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
                            data:'nom_centre',
                            name:'nom_centre'
                        },
                        {
                            data:'taux',
                            name:'taux'
                        },
                        {
                            data:'reception_dps_id',
                            name:'reception_dps_id',
                            render:function (data, type, row) {
                                return "<a href='recdps/"+row.reception_dps_id+"' class='btn btn-primary'><i class='fa fa-edit'></i></a>"}
                        },
                        {
                            data:'reception_dps_id',
                            name:'reception_dps_id',
                            render:function (data, type, row) {
                                return "<a href='#' id='"+row.reception_dps_id+"' class='details btn btn-info'><i class='fa fa-print'></i></a>"}
                        }

                    ]
                });
            }

            $('#filter').click(function(){
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                if(from_date != '' && to_date != '')
                {
                    $('#histo_prod').DataTable().destroy();
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
                $('#histo_prod').DataTable().destroy();
                load_data();
            });

            $(document).on('click', '.details', function(){
                cmde_num = $(this).attr('id');
                $('#details_rec').load('recdps.details_rec/'+cmde_num);
                $('#infosRecModal').modal('show');
            });
        });
    </script>
@endsection
