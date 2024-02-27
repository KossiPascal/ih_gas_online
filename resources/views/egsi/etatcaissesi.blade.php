@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')


@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <div class="col-md-12">
            <div class="col-md-4 float-left">
                <h4 class="ml-5">{{__('messages.VENTE GLOBALE')}}</h4>
            </div>

            <div class="col-md-4 float-left">
                <a href="{{route('eg.etatcaissedps')}}" class="btn btn-primary">{{__('messages.VENTE PAR DPS')}}</a>
            </div>

            <div class="col-md-4 float-right">
                <a href="{{route('eg.etatcaissecentre')}}" class="btn btn-warning">{{__('messages.VENTE PAR CENTRE')}}</a>
            </div>
        </div>

        <div class="info-box mb-1">
            <div class="row col-md-12">
                <div class="col-12 col-md-2">
                    <input type="date" name="from_date" id="from_date" class="form-control" placeholder="{{__('messages.Date Debut')}}" />
                </div>
                <div class="col-12 col-md-2">
                    <input type="date" name="to_date" id="to_date" class="form-control" placeholder="{{__('messages.Date Fin')}}" />
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
                            <td class="price">{{__('messages.Produit')}}</td>
                            <td class="price">{{__('messages.Prix Unitaire')}}</td>
                            <td class="price">{{__('messages.Quantite')}}</td>
                            <td class="quantity">{{__('messages.Montant')}}</td>
                            <td class="total">{{__('messages.Prise en charge')}}</td>
                            <td class="total">{{__('messages.Net payer')}}</td>
                        </tr>
                        </thead>
                        <tfoot>
                        <tr>
                            <th colspan="3" style="text-align:right">{{__('messages.Total')}}:</th>
                            <th></th>
                            <th></th>
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
            /* $('.input-daterange').datepicker({
                todayBtn:'linked',
                format:'yyyy-mm-dd',
                autoclose:true
            });*/

            load_data();

            function load_data(from_date = '', to_date = '')
            {
                $('#histo_prod').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url:'{{ route("eg.etatcaissesi") }}',
                        data:{from_date:from_date, to_date:to_date}
                    },
                    columns: [
                        {
                            data:'libelle',
                            name:'libelle'
                        },{
                            data:'pu',
                            name:'pu'
                        },
                        {
                            data:'qte',
                            name:'qte'
                        },
                        {
                            data:'mont',
                            name:'mont'
                        },
                        {
                            data:'pec',
                            name:'pec'
                        },
                        {
                            data:'net',
                            name:'net'
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
                        mont = api
                            .column( 3 )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0 );
                        
                        pec = api
                            .column( 4 )
                            .data()
                            .reduce( function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0 ); 
                            
                        net = api
                            .column( 5 )
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
                            ' Montant : '+ mont +' Franc CFA)'
                        );

                         // Update footer
                         $( api.column( 4 ).footer() ).html(
                            ' Pris en charge : '+ pec +' Franc CFA)'
                        );

                         // Update footer
                         $( api.column( 5 ).footer() ).html(
                            ' Net payer : '+ net +' Franc CFA)'
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

            $('#imprimer').click(function(){
                var debut = document.getElementById('from_date').value;
                var fin = document.getElementById('to_date').value;

                var newWin = window.open();
                var the_url = "eg.print_efsi/"+debut+"/"+fin;
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
