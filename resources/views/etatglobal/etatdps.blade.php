@extends('layouts.adminlayout')
@section('title','PCSOFT V4: Gestion Categorie')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <span id="asso_result"></span>
        <div class="col-12 col-sm-12 col-md-12">
            <h3 class="ml-5">ETAT DU STOCK PAR DPS</h3>
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('eg.stockglobal')}}" class="btn btn-dark">Etat du stock global</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('eg.etatcentre')}}" class="btn btn-warning">Etat du stock par centre</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-right">
                <a href="{{route('eg.date_per')}}" class="btn btn-danger">Controle des dates</a>
            </div>
        </div><br><br>
        <div class="form-group col-md-12 float-left">
            <div class="col-md-6 float-left">
                <select name="dps_id" id="dps_id" class="form-control" onchange="actualiser()">
                    @foreach($directions as $key=>$direction)
                        <option value= "{!! $direction !!}"> {!! $direction !!} </option>
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
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="form-group">
                                <table id="details_pdt" class="table-responsive table-striped table-hover table-bordered">

                                </table>
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
        var dps_id=0;
        function direction() {
            $.ajax({
                url:"eg.directions",
                dataType:"json",
                success:function(data)
                {
                    $('#dps_id').empty();
                    $('#dps_id').append('<option id="0"  value="0">- Choisir un direction -</option>');
                    for (var i = 0; i < data.length; i++) {
                        $('#dps_id').append('<option id=' + data[i].dps_id + ' value=' + data[i].dps_id + '>' + data[i].dps_nom +'</option>');
                    }
                    $('#dps_id').change();
                }
            })
        }

        function actualiser(){
            dps_id = document.getElementById('dps_id').value;
            if (dps_id!=0){
                getProduits(dps_id);
            }
        }

        function getProduits(dps_id){
            $('#pdt_centre').DataTable().destroy();
            $('#pdt_centre').DataTable({
                processing: true,
                serverSide: true,
                paging: false,
                searching: true,
                ajax: {
                    url: "eg.getEtatdps/" + dps_id
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
            direction();
            //actualiser();


            $(document).on('click', '.details', function(){
                var id = $(this).attr('id');
                $('#details_pdt').load('eg.details_pdtdps/'+dps_id+'/'+id);
                $('#produitDetails').modal('show');
            });

            $(document).on('click', '.imprimer', function(){
                var dps_id = document.getElementById('dps_id').value;

                var newWin = window.open();
                var the_url = "eg.print_etatdps/"+dps_id;
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
