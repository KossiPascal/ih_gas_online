@extends('layouts.adminlayout')
@section('title','PCSOFT V4: Gestion Categorie')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <span id="asso_result"></span>
        <div class="col-12 col-sm-12 col-md-12">
            <h3 class="ml-5">ETAT DU STOCK GLOBAL</h3>  
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('eg.etatdps')}}" class="btn btn-dark"><i class="fa fa-print"></i> Stock d'une DPS</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-left">
            <a href="{{route('eg.etatcentre')}}" class="btn btn-danger violet">Stock d'un centre</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-right">
                <a href="{{route('eg.date_per')}}" class="btn btn-warning"><i class="fa fa-file-export"></i> Etat des dates </a>
            </div>
        </div>
        <br>
        <br>
        <div class="info-box">
            <div class="table-responsive">
                <table id="liste_pdt" class="table table-striped table-bordered data-table">
                    <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Libelle</th>
                        <th>Quantite</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                </table>
            </div>

            <!--Details un materiel -->
            <div id="produitDetails" class="modal fade" role="dialog" style="width: 100%">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="col-md-12">
                                    <table id="details_pdt" class="table table-responsive table-striped table-hover table-bordered">

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
        <h4><a href="{{route('eg.print_egstock')}}" class="btn btn-success"><i class="fa fa-print"></i> Imprimer cet etat</a></h4>
    </main>
@endsection

@section('extra-js')
    <script>
        $(document).ready(function(){
            $('#liste_pdt').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    url: "{{ route('eg.stockglobal') }}",
                },
                columns:[
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
                ]
            });


            $(document).on('click', '.details', function(){
                var id = $(this).attr('id');
                $('#details_pdt').load('eg.details_pdt/'+id);
                $('#produitDetails').modal('show');
            });

        });
    </script>
@endsection
