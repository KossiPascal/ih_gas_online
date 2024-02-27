@extends('layouts.adminlayout')
@section('title','PCSOFT V4: Gestion Categorie')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <span id="asso_result"></span>
        <div class="col-12 col-sm-12 col-md-12">
            <h3 class="ml-5">ETAT DU STOCK PAR CENTRE</h3>
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="#" class="btn btn-dark"><i class="fa fa-print"></i> Imprimer cet etat</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('eg.stockglobaldps')}}" class="btn btn-warning">Etat du stock global</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-right">
                <a href="{{route('eg.date_perdps')}}" class="btn btn-danger">Controle des dates</a>
            </div>
        </div><br><br>
        <div class="form-group col-md-12 float-left">
            <div class="col-md-6 float-left">
                <select name="centre_id" id="centre_id" class="form-control" onchange="actualiser()">
                    @foreach($centres as $key=>$centre)
                        <option value= "{!! $centre !!}"> {!! $centre !!} </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 float-right">
                <button type="button" name="imprimer" class="btn btn-primary imprimer">Imprimer le stock de ce centre</button>
            </div>
        </div>
        <br>
        <br>
        <div class="info-box">
            <div class="table-responsive">
                <table id="pdt_centre" class="table table-striped table-bordered data-table">
                    <thead>
                    <tr class="cart_menu">
                        <th>Reference</th>
                        <th>Libelle</th>
                        <th>Quantite</th>
                        <th>Actions</th>
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
                                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>Quitter</button>
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
        function centre() {
            $.ajax({
                url:"eg.centresdps",
                dataType:"json",
                success:function(data)
                {
                    $('#centre_id').empty();
                    $('#centre_id').append('<option id="0"  value="0">- Choisir un centre -</option>');
                    for (var i = 0; i < data.length; i++) {
                        $('#centre_id').append('<option id=' + data[i].centre_id + ' value=' + data[i].centre_id + '>' + data[i].nom_centre +'</option>');
                    }
                    $('#centre_id').change();
                }
            })
        }

        function actualiser(){
            var centre_id = document.getElementById('centre_id').value;
            if (centre_id!=0){
                getProduits(centre_id);
            }
        }

        function getProduits(centre_id){
            $('#pdt_centre').DataTable().destroy();
            $('#pdt_centre').DataTable({
                processing: true,
                serverSide: true,
                paging: false,
                searching: true,
                ajax: {
                    url: "eg.getEtatcentre/" + centre_id
                },
                columns: [
                    {
                        data: 'reference',
                        name: 'reference'
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
            centre();
            //actualiser();


            $(document).on('click', '.details', function(){
                var id = $(this).attr('id');
                $('#details_pdt').load('eg.details_pdt_dps/'+id);
                $('#produitDetails').modal('show');
            });

            $(document).on('click', '.imprimer', function(){
                var centre_id = document.getElementById('centre_id').value;

                var newWin = window.open();
                var the_url = "eg.print_etatcentre/"+centre_id;
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
