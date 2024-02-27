@extends('layouts.adminlayout')
@section('title','PCSOFT V4: Gestion magasin')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <div class="col-12 col-sm-12 col-md-12 float-left">
            <div class="col-md-6 float-left">
                <h3 class="ml-5">{{__('messages.GESTION DES MAGASINS')}}</h3>
            </div>
            <div class="col-md-6 float-right">
                <button type="button" name="create_mag" id="create_mag" class="btn btn-success"><i class="fa fa-plus"></i> {{__('messages.Nouveau magasin')}}</button>
            </div>
        </div>

        <div class="col-12 col-sm-12 col-md-12">
            <div class="col-12 col-sm-3 col-md-3 float-left">
                <a href="{{route('cat.index')}}" class="btn btn-danger">{{__('messages.Les Categories')}}</a>
            </div>
            <div class="col-12 col-sm-3 col-md-3 float-left">
                <a href="{{route('pdt.index')}}" class="btn btn-primary">{{__('messages.Les Produits')}}</a>
            </div>
            <div class="col-12 col-sm-3 col-md-3 float-left">
                <a href="{{route('ass.index')}}" class="btn btn-warning">{{__('messages.Les Assurances')}}</a>
            </div>
            <div class="col-12 col-sm-3 col-md-3 float-right">
                <a href="{{route('four.index')}}" class="btn btn-outline-success">{{__('messages.Les Fournisseurs')}}</a>
            </div>
        </div>
        <br>
        <br>
        <div class="info-box">
            <div class="table-responsive">
                <table id="liste_mag" class="table table-striped table-bordered data-table">
                    <thead>
                    <tr>
                        <th>{{__('messages.Libelle')}}</th>
                        <th>{{__('messages.Type de magasin')}}</th>
                        <th>{{__('messages.Actions')}}</th>
                        <th>{{__('messages.Cout du magasin')}}</th>
                        <th>{{__('messages.Etat du stock')}}</th>
                    </tr>
                    </thead>
                </table>
            </div>

            <!--Ajouter un produit -->
            <div id="magasinModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{__('messages.Creer un nouveau magasin')}}</h4>
                        </div>
                        <div class="modal-body">
                            <span id="form_result"></span>
                            <form method="post" id="mag_form" class="form-horizontal">
                                @csrf

                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Libelle')}} : </label>
                                    <input type="text" name="libelle" id="libelle" class="form-control" required="required"/>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Type de magasin')}} </label>
                                    <select name="type" id="type" class="form-control">
                                        <option value="">{{__('messages.Selectionner un type')}}</option>
                                        <option value="Magasin_Stockage">{{__('messages.Magasin_Stockage')}}</option>
                                        <option value="Stock_AEC">{{__('messages.Stock_AEC')}}</option>
                                    </select>
                                </div>

                                <div class="form-group" align="center">
                                    <input type="hidden" name="magasin_id" id="magasin_id" />
                                    <input type="submit" name="action_button" id="action_button" class="btn btn-success" value="{{__('messages.Enregistrer')}}" />
                                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Quitter')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!--Info stock -->
            <main id="coutstoskModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title">{{__('messages.Confirmation')}}</h2>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-12" >{{__('messages.COUT MAGASIN')}} : </label>
                            <div class="col-md-12">
                                <input type="text" name="mag" id="mag" class="form-control" readonly/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-12" >{{__('messages.COUT DU STOCK')}} : </label>
                            <input type="text" name="cout_mag" id="cout_mag" class="form-control" readonly/>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-primary" data-dismiss="modal">{{__('messages.Fermer')}}</button>
                        </div>
                    </div>
                </div>
            </main>

            <!--Supprimer le magasin -->
            <main id="confirmModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title">{{__('messages.Confirmation')}}</h2>
                        </div>
                        <div class="modal-body">
                            <h5 align="center" style="margin:0;">{{__('messages.Etes vous sure de supprimer ce magasin')}}?</h5>
                        </div>
                        <div class="modal-footer">
                            <button type="button" name="ok_button" id="ok_button" class="btn btn-danger">{{__('messages.Oui')}}</button>
                            <button type="button" class="btn btn-primary" data-dismiss="modal">{{__('messages.Annuler')}}</button>
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

            $('#liste_mag').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    url: "{{ route('mag.index') }}",
                },
                columns:[
                    {
                        data: 'libelle',
                        name: 'libelle'
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                    {
                        data:'magasin_id',
                        name:'magasin_id',
                        render:function (data, type, row) {
                            return "<a href='#' id='"+row.magasin_id+"' class='btn btn-info cout_stock'><i class='fa fa-info'></i></a>"}
                    },
                    {
                        data:'magasin_id',
                        name:'magasin_id',
                        render:function (data, type, row) {
                            return "<a href='mag.stock/"+row.magasin_id+"' class='btn btn-success'><i class='fa fa-print'></i></a>"}
                    }
                ]
            });

            $('#create_mag').click(function(){
                $('.modal-title').text("{{__('messages.Creer une magasin')}}");
                $('#action_button').val("{{__('messages.Enregistrer')}}");
                $('#magasin_id').val('');
                $('#libelle').val('');
                $('#magasinModal').modal('show');
                $('#form_result').html('');
            });

            $('#mag_form').on('submit', function(event){
                event.preventDefault();
                $.ajax({
                    url:"{{ route('mag.store') }}",
                    method:"POST",
                    data: new FormData(this),
                    contentType: false,
                    cache:false,
                    processData: false,
                    dataType:"json",
                    success:function(data)
                    {
                        var html = '';
                        if(data.errors)
                        {
                            html = '<div class="alert alert-danger">';
                            for(var count = 0; count < data.errors.length; count++)
                            {
                                html += '<p>' + data.errors[count] + '</p>';
                            }
                            html += '</div>';
                        }
                        if(data.error)
                        {
                            html = '<div class="alert alert-danger">' + data.error + '</div>';
                        }
                        if(data.success)
                        {
                            html = '<div class="alert alert-success">' + data.success + '</div>';
                            $('#mag_form')[0].reset();
                            $('#liste_mag').DataTable().ajax.reload();
                        }
                        $('#form_result').html(html);
                    }
                })
            });

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');

                $('#form_result').html('');
                $.ajax({
                    url:"mag/"+id+"/edit",
                    dataType:"json",
                    success:function(data){
                        $('#libelle').val(data.libelle);
                        $('#type').val(data.type);
                        $('#magasin_id').val(id);
                        $('.modal-title').text("{{__('messages.Editer une magasin')}}");
                        $('#action_button').val("{{__('messages.Editer')}}");
                        $('#magasinModal').modal('show');
                    }
                })
            });

            $(document).on('click', '.cout_stock', function(){
                var id = $(this).attr('id');
                $('#coutstoskModal').html('');
                $.ajax({
                    url:"mag.cout_stock/"+id,
                    dataType:"json",
                    success:function(data){
                        $('#cout_mag').val(data.total);
                        $('#coutstoskModal').modal('show');
                    }
                })
            });


            var magasin_id;
            $(document).on('click', '.delete', function(){
                magasin_id = $(this).attr('id');
                $('.modal-title').text("{{__('messages.Confirmation')}}");
                $('#ok_button').text('{{__('messages.Oui')}}');
                $('#confirmModal').modal('show');
            });

            $('#ok_button').click(function(){
                $.ajax({
                    url:"mag.delete/"+magasin_id,
                    beforeSend:function(){
                        $('#ok_button').text('{{__('messages.Suppression')}}...');
                    },
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_mag').DataTable().ajax.reload();
                        }, 500);
                    }
                })
            });

        });
    </script>
@endsection
