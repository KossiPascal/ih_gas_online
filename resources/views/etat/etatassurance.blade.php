@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')


@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <div class="col-md-12">
            <div class="col-md-6 float-left">
                <h4 class="ml-5">{{__('messages.SITUATION DES ASSURANCES')}}</h4>
            </div>

            <div class="col-md-6 float-right">
                <a href="{{route('vente.etatcaisse')}}" class="btn btn-warning">{{__('messages.ETAT DES RECETTES')}}</a>
            </div>
        </div>

        <div class="info-box mb-1">
            <div class="row input-daterange">
                <div class="col-12 col-md-3">
                    <input type="text" name="debut" id="debut" class="form-control" placeholder="{{__('messages.Date Debut')}}" readonly />
                </div>
                <div class="col-12 col-md-3">
                    <input type="text" name="fin" id="fin" class="form-control" placeholder="{{__('messages.Date Fin')}}" readonly />
                </div>

                <div class="col-12 col-md-6">
                    <button type="button" name="filter" id="filter" class="btn btn-primary">{{__('messages.Rechercher')}}</button>
                    <button type="button" name="print" id="print" class="btn btn-success print">{{__('messages.Imprimer')}}</button>
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
                            <td class="description">{{__('messages.Assurance')}} </td>
                            <td class="price">{{__('messages.Montant Facture')}}</td>
                            <td class="price">{{__('messages.Prise en charge')}}</td>
                            <td>{{__('messages.Choisir')}}</td>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th style="text-align:right">{{__('messages.Total vente')}}:</th>
                            <th></th>
                            <th style="text-align:right">{{__('messages.Prise en charge')}}:</th>
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
            var assurance_id =1;
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
                        url:'{{ route("vente.etatassurance") }}',
                        data:{from_date:from_date, to_date:to_date}
                    },
                    columns: [
                        {
                            data:'nom',
                            name:'mut_lib'
                        },
                        {
                            data:'montant',
                            name:'montant'
                        },
                        {
                            data:'pec',
                            name:'pec'
                        },
                        {
                            data:'assurance_id',
                            name:'assurance_id',
                            render:function (data, type, row) {
                                return "<a href='#' class='btn btn-primary select' id='+"+row.assurance_id+"'><i class='fa fa-check'></i></a>"}
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
                            .column( 1 )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0 );
                        pec = api
                            .column( 2 )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0 );

                        // Total over this page
                        pageTotal = api
                            .column( 1, { page: 'current'} )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0 );

                        // Update footer
                        $( api.column( 1 ).footer() ).html(
                             total +' Franc CFA'
                        );

                        $( api.column( 3).footer() ).html(
                             pec +' Franc CFA'
                        );
                    }
                });
            }

            $(document).on('click', '.print', function(){
                var debut = document.getElementById('debut').value;
                var fin = document.getElementById('fin').value;
                //assurance_id = $(this).attr('id');

                console.log(debut, fin,assurance_id);
                var newWin = window.open();
                var the_url = "vente.print_etatassurance/"+debut+"/"+fin+"/"+assurance_id;
                $.ajax({
                    type: "GET", url: the_url, data: {},
                    success: function(data){
                        console.log(data);
                        newWin.document.write(data.data);
                    }
                    ,error: function() {
                    }
                });
            });

            $(document).on('click', '.select', function(){
                assurance_id = $(this).attr('id');
                console.log(assurance_id);
            });

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
