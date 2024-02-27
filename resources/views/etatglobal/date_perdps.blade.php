@extends('layouts.adminlayout')
@section('title','PCSOFT V4: Gestion Produit')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">

        <div class="col-12 col-sm-12 col-md-12">
            <h3 class="ml-5">PRODUITS, LOTS, ET DATE DE PEREMPTION</h3>
            <div class="col-12 col-sm-4 col-md-4 float-left">
            <a href="#" class="btn btn-dark"><i class="fa fa-file-export"></i> Imprimer cet Etat </a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('eg.stockglobaldps')}}" class="btn btn-warning">Etat du stock par Global</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-right">
                <a href="{{route('eg.etatcentredps')}}" class="btn btn-danger"><i class="fa fa-file-export"></i> Etat du stock par centre </a>
            </div>
        </div>
        <br>
        <div class="info-box">
            <div class="table-responsive">
                <table id="liste_produit" class="table table-striped table-bordered data-table">
                    <thead>
                    <tr>
                        <th>Lot</th>
                        <th>Libelle</th>
                        <th>Prix de vente</th>
                        <th>Qte reste</th>
                        <th>Expire le</th>
                        <th>Perime depuis(Mois)</th>
                        <th>Centre</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                </table>
            </div>
            <!--Supprimer le produit -->
            <main id="confirmModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title">Confirmation</h2>
                        </div>
                        <div class="modal-body">
                            <h5 align="center" style="margin:0;">Etes vous sure de supprimer ce produit?</h5>
                        </div>
                        <div class="modal-footer">
                            <button type="button" name="ok_button" id="ok_button" class="btn btn-danger">Oui</button>
                            <button type="button" class="btn btn-primary" data-dismiss="modal">Annuler</button>
                        </div>
                    </div>
                </div>
            </main>

        </div>
    </main>
@endsection

@section('extra-js')
    <script>
        $(document).ready(function(){

            $('#liste_produit').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    url: "{{ route('eg.date_perdps') }}",
                },
                columns:[
                    {
                        data: 'lot',
                        name: 'lot'
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
                        data: 'date_peremption',
                        name: 'date_peremption'
                    },
                    {
                        data: 'mois',
                        name: 'mois'
                    },
                    {
                        data: 'nom_centre',
                        name: 'nom_centre'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    }
                ],
                'rowCallback': function(row, data, index) {
                    if (data.mois < '0') {
                        $(row).css('background-color', 'red').css('color', 'white');
                    }
                    if (data.mois <= '3' && data.mois >= '0') {
                        $(row).find('td:eq(5)').css('background-color', 'yellow').css('color', 'black');
                    }
                    if (data.mois <= '6' && data.mois > '3') {
                        $(row).find('td:eq(5)').css('background-color', 'gray').css('color', 'white');
                    }
                }
            });

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');
                $('#form_result').html('');
                $.ajax({
                    url:"produit/"+id+"/edit",
                    dataType:"json",
                    success:function(html){
                        $('#pdt_ref').val(html.data.pdt_ref);
                        $('#pdt_lib').val(html.data.pdt_lib);
                        $('#pdt_pa').val(html.data.pdt_pa);
                        $('#pdt_pv').val(html.data.pdt_pv);
                        $('#cat_num').val(html.data.cat_num);
                        $('#hidden_id').val(id);
                        $('.modal-title').text("Editer un produit");
                        $('#action_button').val("Editer");
                        $('#action').val("Editer");
                        $('#produitModal').modal('show');
                    }
                })
            });


            var pdt_num;
            $(document).on('click', '.delete', function(){
                pdt_num = $(this).attr('id');
                $('.modal-title').text("Confirmation");
                $('#ok_button').text('Oui');
                $('#confirmModal').modal('show');
            });

            $('#ok_button').click(function(){
                $.ajax({
                    url:"produit/destroy/"+pdt_num,
                    beforeSend:function(){
                        $('#ok_button').text('Suppression...');
                    },
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_produit').DataTable().ajax.reload();
                        }, 200);
                    }
                })
            });

        });
    </script>
@endsection
