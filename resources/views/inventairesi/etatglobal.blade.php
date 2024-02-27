@extends('layouts.adminlayout')
@section('title','PCSOFT V4: Gestion Categorie')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <span id="asso_result"></span>
        <div class="col-12 col-sm-12 col-md-12">
            <h3 class="ml-5">{{__('messages.ETAT DU STOCK GLOBAL')}}</h3>
            <div class="col-12 col-sm-4 col-md-3 float-left">
                <a href="{{route('invsi.print_etatglobal')}}" class="btn btn-success"><i class="fa fa-print"></i> {{__('messages.Imprimer cet etat')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-3 float-left">
                <a href="{{route('invsi.exportEG')}}" class="btn bleu_claire"><i class="fa fa-file-export"></i> {{__('messages.Exporter en Excell')}} </a>
            </div>
            <div class="col-12 col-sm-4 col-md-3 float-left">
                <a href="{{route('invsi.etatmagasin')}}" class="btn btn-warning jaune">{{__('messages.Etat du stock magasin')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-3 float-right">
                <a href="{{route('invsi.date_per')}}" class="btn btn-danger violet">{{__('messages.Controle des dates')}}</a>
            </div>
        </div>
        <br><br>
        <div class="col-md-6 float-left">
                <select name="centre_id" id="centre_id" class="form-control" onchange="actualiser()">
                    @foreach($centres as $key=>$centre)
                        <option value= "{!! $centre->centre_id !!}"> {!! $centre->nom_centre !!} </option>
                    @endforeach
                </select>
        </div>
        <div class="col-md-6 float-right">
                <button type="button" name="imprimer" class="btn btn-primary imprimer">{{__('messages.Imprimer le stock')}}</button>
            </div>
        <div class="info-box">
            <div class="table-responsive">
                <table id="liste_pdt" class="table table-striped table-bordered data-table">
                    <thead>
                    <tr>
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
                                    <table id="details_pdt" class="table table-responsive table-striped table-hover table-bordered">

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
        var centre_id=0;
        function centre() {
            $.ajax({
                url:"invsi.centres",
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
            centre_id = document.getElementById('centre_id').value;
            if (centre_id!=0){
                getProduits(centre_id);
            }
        }

        function getProduits(centre_id){
            $('#liste_pdt').DataTable().destroy();
            $('#liste_pdt').DataTable({
                processing: true,
                serverSide: true,
                paging: false,
                searching: true,
                ajax: {
                    url: "invsi.etatproduitcentre/" + centre_id
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
            centre();
            actualiser();

            $(document).on('click', '.details', function(){
                var id = $(this).attr('id');
                $('#details_pdt').load('invsi.details_pdt/'+id+'/'+centre_id);
                $('#produitDetails').modal('show');
            });


            var cat_num;
            $(document).on('click', '.delete', function(){
                cat_num = $(this).attr('id');
                $('.modal-title').text("{{__('messages.Confirmation')}}");
                $('#ok_button').text('{{__('messages.Oui')}}');
                $('#confirmModal').modal('show');
            });

            $('#ok_button').click(function(){
                $.ajax({
                    url:"cat.delete/"+cat_num,
                    beforeSend:function(){
                        $('#ok_button').text('{{__('messages.Suppression')}}...');
                    },
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_pdt').DataTable().ajax.reload();
                        }, 500);
                    }
                })
            });

            $(document).on('click', '.asso', function(){
                cat_num = $(this).attr('id');
                $('.modal-title').text("{{__('messages.Confirmation')}}");
                $('#okbutton').text('{{__('messages.Oui')}}');
                $('#assoModal').modal('show');
            });

            $(document).on('click', '.imprimer', function(){
                var centre_id = document.getElementById('centre_id').value;

                var newWin = window.open();
                var the_url = "invsi.print_etatglobal/"+centre_id;
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
