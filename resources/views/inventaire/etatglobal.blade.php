@extends('layouts.adminlayout')
@section('title','PCSOFT V4: Gestion Categorie')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <span id="asso_result"></span>
        <div class="col-12 col-sm-12 col-md-12">
            <h3 class="ml-5">{{__('messages.ETAT DU STOCK GLOBAL')}}</h3>
            <div class="col-12 col-sm-4 col-md-3 float-left">
                <a href="{{route('inv.print_etatglobal')}}" class="btn btn-success"><i class="fa fa-print"></i> {{__('messages.Imprimer cet etat')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-3 float-left">
                <a href="{{route('inv.exportEG')}}" class="btn bleu_claire"><i class="fa fa-file-export"></i> {{__('messages.Exporter en Excell')}} </a>
            </div>
            <div class="col-12 col-sm-4 col-md-3 float-left">
                <a href="{{route('inv.magasin')}}" class="btn btn-warning jaune">{{__('messages.Etat du stock magasin')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-3 float-right">
                <a href="{{route('inv.date_per')}}" class="btn btn-danger violet">{{__('messages.Controle des dates')}}</a>
            </div>
        </div>
        <br>
        <br>
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
        $(document).ready(function(){
            $('#liste_pdt').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    url: "{{ route('inv.etatglobal') }}",
                },
                columns:[
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
            });


            $(document).on('click', '.details', function(){
                var id = $(this).attr('id');
                $('#details_pdt').load('inv.details_pdt/'+id);
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

            $('#okbutton').click(function(){
                $.ajax({
                    url:"cat.affecter/"+cat_num,
                    beforeSend:function(){
                        $('#ok_button').text('{{__('messages.Traitement')}}...');
                    },
                    success:function(data)
                    {
                        if(data.error)
                        {
                            html = '<div class="alert alert-danger">' + data.error + '</div>';
                        }
                        if(data.success)
                        {
                            html = '<div class="alert alert-success">' + data.success + '</div>';
                        }
                        setTimeout(function(){
                            $('#assoModal').modal('hide');
                            $('#liste_pdt').DataTable().ajax.reload();
                        }, 500);
                        $('#asso_result').html(html);
                    }
                })
            });

        });
    </script>
@endsection
