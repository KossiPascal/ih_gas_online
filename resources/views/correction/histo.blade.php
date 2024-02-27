@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')


@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <div class="col-md-12">
            <div class="col-md-6 float-left">
                <h4 class="ml-5">{{__('messages.HISTORIQUE DES CORRECTIONS')}}</h4>
            </div>

            <div class="col-md-6 float-right">
                <a href="{{route('cs.index')}}" class="btn btn-warning">{{__('messages.NOUVELLE CORRECTION')}}</a>
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
                    <button type="button" name="imprimer" id="imprimer" class="btn btn-success">{{__('messages.Imprimer')}}</button>
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
                            <td class="description">{{__('messages.Date')}} </td>
                            <td class="total">{{__('messages.cout')}}</td>
                            <td class="total">{{__('messages.Motif')}}</td>
                            <td class="total">{{__('messages.Magasin')}}</td>
                            <td class="total">{{__('messages.Utilisateur')}}</td>
                            <td>{{__('messages.Editer')}}</td>
                            <td>{{__('messages.Imprimer')}}</td>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th colspan="6" style="text-align:right">{{__('messages.Total')}}:</th>
                            <th></th>
                        </tr>
                        </tfoot>
                    </table>
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
                        url:'{{ route("cs.histo") }}',
                        data:{from_date:from_date, to_date:to_date}
                    },
                    columns: [
                        {
                            data:'date_cs',
                            name:'date_cs'
                        },
                        {
                            data:'cout',
                            name:'cout'
                        },
                        {
                            data:'motif',
                            name:'motif'
                        },
                        {
                            data:'libelle',
                            name:'libelle'
                        },
                        {
                            data:'name',
                            name:'name'
                        },
                        {
                            data:'correction_stock_id',
                            name:'correction_stock_id',
                            render:function (data, type, row) {
                                return "<a href='cs/"+row.correction_stock_id+"/edit' class='btn btn-success'><i class='fa fa-edit'></i></a>"}
                        },
                        {
                            data:'correction_stock_id',
                            name:'correction_stock_id',
                            render:function (data, type, row) {
                                return "<a href='cs/"+row.correction_stock_id+"' class='btn btn-info'><i class='fa fa-print'></i></a>"}
                        }

                    ],"footerCallback": function ( row, data, start, end, display ) {
                        var api = this.api(), data;

                        // Remove the formatting to get integer data for summation
                        var intVal = function ( i ) {
                            return typeof i === 'string' ?
                                i.replace(/[\$,]/g, '')*1 :
                                typeof i === 'number' ?
                                    i : 0;
                        };

                        // Total over all pages
                        total = api
                            .column( 3 )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0 );

                        // Total over this page
                        pageTotal = api
                            .column( 3, { page: 'current'} )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0 );

                        // Update footer
                        $( api.column( 3 ).footer() ).html(
                            'Page : '+pageTotal +'Franc CFA ( TOTAL : '+ total +' Franc CFA)'
                        );
                    }
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
        });
    </script>
@endsection
