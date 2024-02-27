@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')


@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <div class="col-12 col-sm-12 col-md-12">
            <h5 class="ml-5">{{__('messages.INVENTAIRE GLOBAL')}}</h5>
            <div class="col-12 col-md-4 float-left">
                <a href="{{route('inv.invglobal')}}" class="btn btn-success"><i class="fa fa-globe"></i> {{__('messages.Inventaire Global')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('inv.invmagasin')}}" class="btn btn-primary"><i class="fa fa-database"></i>{{__('messages.Inventaire par Magasin')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-right">
                <a href="{{route('inv.invproduit')}}" class="btn btn-warning"><i class="fa fa-info"></i>{{__('messages.Fiche de Stock')}}</a>
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
                    <button type="button" name="filter" id="filter" class="btn btn-warning">{{__('messages.Rechercher')}}</button>
                    <button type="button" name="imprimer" id="imprimer" class="btn btn-primary">{{__('messages.Imprimer')}}</button>
                    <button type="button" name="refresh" id="reset" class="btn btn-danger">{{__('messages.Actualiser')}}</button>
                </div>
            </div>
            <!-- /.info-box-content -->
        </div>

        <div class="col-md-12">
            <div class="info-box">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="inventaires">
                        <thead>
                        <tr class="cart_menu">
                            <td class="price">{{__('messages.Produit')}}</td>
                            <td class="price">{{__('messages.Initial')}}</td>
                            <td class="price">{{__('messages.Achat')}}</td>
                            <td class="price">{{__('messages.Sortie')}}</td>
                            <td class="total">{{__('messages.Solde')}}</td>
                            <td>{{__('messages.Infos')}}</td>
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
                $('#inventaires').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url:'{{ route("inv.invglobal") }}',
                        data:{from_date:from_date, to_date:to_date}
                    },
                    columns: [
                        {
                            data:'pdt_lib',
                            name:'pdt_lib'
                        },
                        {
                            data:'pdt_ini',
                            name:'pdt_ini'
                        },
                        {
                            data:'pdt_ent',
                            name:'pdt_ent'
                        },
                        {
                            data:'pdt_sor',
                            name:'pdt_sor'
                        },
                        {
                            data:'pdt_act',
                            name:'pdt_act'
                        },
                        {
                            data:'produit_id',
                            name:'produit_id',
                            render:function (data, type, row) {
                                return "<a href='#' id='"+row.produit_id+"' class='btn btn-danger details'><i class='fa fa-info'></i></a>"}
                        }

                    ]
                });
            }

            $('#filter').click(function(){
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                if(from_date != '' && to_date != '')
                {
                    $('#inventaires').DataTable().destroy();
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
                $('#inventaires').DataTable().destroy();
                load_data();
            });

            $('#imprimer').click(function(){
                var debut = document.getElementById('from_date').value;
                var fin = document.getElementById('to_date').value;

                var newWin = window.open();
                var the_url = "inv.print_invglobal/"+debut+"/"+fin;
                $.ajax({
                    type: "GET", url: the_url, data: {},
                    success: function(data){
                        newWin.document.write(data);;
                    }
                    ,error: function() {
                    }
                });
            });

            $(document).on('click', '.details', function(){
                var id = $(this).attr('id');
                var debut = document.getElementById('from_date').value;
                var fin = document.getElementById('to_date').value;

                var newWin = window.open();
                var the_url = "inv.details/"+debut+"/"+fin+"/"+id;
                $.ajax({
                    type: "GET", url: the_url, data: {},
                    success: function(data){
                        newWin.document.write(data);;
                    }
                    ,error: function() {
                    }
                });
            });
        });
    </script>
@endsection
