@extends('layouts.adminlayout')
@section('title','PCSOFT V4: Gestion Categorie')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <span id="asso_result"></span>
        <div class="col-12 col-sm-12 col-md-12">
            <h3 class="ml-5">{{__('messages.ETAT DU STOCK PAR MAGASIN')}}</h3>
            <div class="col-12 col-sm-4 col-md-3 float-left">
                <a href="#" class="btn btn-success"><i class="fa fa-print"></i> {{__('messages.Imprimer cet etat')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-3 float-left">
                <form method="get" action="{{route('inv.exportEM')}}">
                    <input type="hidden" name="magasin" id="magasin" value="0">
                    <button type="submit" class="btn btn-primary"> <i class="fa fa-file-export"></i> {{__('messages.Exporter en Excell')}}</button>
                </form>
            </div>
            <div class="col-12 col-sm-4 col-md-3 float-left">
                <a href="{{route('inv.etatglobal')}}" class="btn btn-warning">{{__('messages.Etat du stock global')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-3 float-right">
                <a href="{{route('inv.date_per')}}" class="btn btn-danger">{{__('messages.Controle des dates')}}</a>
            </div>
        </div><br><br>
        <div class="form-group col-md-12 float-left">
            <div class="col-md-6 float-left">
                <select name="magasin_id" id="magasin_id" class="form-control" onchange="actualiser()">
                    @foreach($magasins as $key=>$magasin)
                        <option value= "{!! $magasin !!}"> {!! $magasin !!} </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 float-right">
                <button type="button" name="imprimer" class="btn btn-primary imprimer">{{__('messages.Imprimer le stock de ce magasin')}}</button>
            </div>
        </div>
        <br>
        <br>
        <div class="info-box">
            <div class="table-responsive">
                <table id="pdt_mag" class="table table-striped table-bordered data-table">
                    <thead>
                    <tr class="cart_menu">
                        <th>{{__('messages.Reference')}}</th>
                        <th>{{__('messages.Libelle')}}</th>
                        <th>{{__('messages.Prix de vente')}}</th>
                        <th>{{__('messages.Qte en Stock')}}</th>
                        <th>{{__('messages.Minimum')}}</th>
                        <th>{{__('messages.Maximum')}}</th>
                        <th>{{__('messages.Actions')}}</th>
                    </tr>
                    </thead>
                </table>
            </div>

            <!--Details un materiel -->
            <div id="produitDetails" class="modal fade" role="dialog" style="width: auto">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="col-md-12">
                                    <table id="details_pdt" class="table-responsive table-striped table-hover table-bordered">

                                    </table>
                                </div>
                            </div>

                            <div class="form-group" align="center">
                                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Quitter')}}</button>
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
                url:"inv.magasins",
                dataType:"json",
                success:function(data)
                {
                    $('#magasin_id').empty();
                    $('#magasin_id').append('<option id="0"  value="0">- {{__('messages.Choisir un magasin')}} -</option>');
                    for (var i = 0; i < data.length; i++) {
                        $('#magasin_id').append('<option id=' + data[i].magasin_id + ' value=' + data[i].magasin_id + '>' + data[i].libelle +'</option>');
                    }
                    $('#magasin_id').change();
                }
            })
        }

        function actualiser(){
            var magasin_id = document.getElementById('magasin_id').value;
            document.getElementById('magasin').value=magasin_id;
            if (magasin_id!=0){
                //$('#pdt_mag').load('inv.etatmagasin/'+magasin_id);
                getProduits(magasin_id);
            }
        }

        function getProduits(magasin_id){
            $('#pdt_mag').DataTable().destroy();
            $('#pdt_mag').DataTable({
                processing: true,
                serverSide: true,
                paging: false,
                searching: true,
                ajax: {
                    url: "inv.etatmagasin/" + magasin_id
                },
                columns: [
                    {
                        data: 'reference',
                        name: 'reference'
                    },
                    {
                        data: 'libelle',
                        name: 'libelle'
                    },
                    {
                        data: 'pv',
                        name: 'pv'
                    },
                    {
                        data: 'qte',
                        name: 'qte'
                    },
                    {
                        data: 'min',
                        name: 'min'
                    },
                    {
                        data: 'max',
                        name: 'max'
                    },
                    {
                        data: 'produit_id',
                        name: 'produit_id',
                        render: function (data, type, row) {
                            return "<a href='#' id='" + row.produit_id + "' class='btn btn-primary details'><i class='fa fa-info'></i></a>"
                        }
                    }
                ],
                'rowCallback': function(row, data, index) {
                    if (data.qte == '0') {
                        $(row).find('tr').css('background-color', 'red').css('color', 'white');
                    }
                    if (data.qte <= data.min && data.qte > '0') {
                        $(row).find('td:eq(3)').css('background-color', 'yellow').css('color', 'black');
                    }
                    if (data.qte >= data.max) {
                        $(row).find('td:eq(5)').css('background-color', 'gray').css('color', 'white');
                    }
                }
            })
        }

        $(document).ready(function(){
            magasin();
            actualiser();


            $(document).on('click', '.details', function(){
                var id = $(this).attr('id');
                $('#details_pdt').load('inv.details_pdt/'+id);
                $('#produitDetails').modal('show');
            });

            $(document).on('click', '.imprimer', function(){
                var magasin_id = document.getElementById('magasin_id').value;

                var newWin = window.open();
                var the_url = "inv.print_etatmagasin/"+magasin_id;
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
