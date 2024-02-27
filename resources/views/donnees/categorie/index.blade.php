@extends('layouts.adminlayout')
@section('title','PCSOFT V4: Gestion Categorie')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <div class="col-12 col-sm-12 col-md-12 float-left">
            <div class="col-md-6 float-left">
                <h3 class="ml-5">{{__('messages.GESTION DES CATEGORIES')}}</h3>
            </div>
            <div class="col-md-6 float-right">
                <button type="button" name="create_cat" id="create_cat" class="btn btn-success"><i class="fa fa-plus"></i> {{__('messages.Nouvelle Categorie')}}</button>
            </div>
        </div>
        <span id="asso_result"></span>
        <div class="col-12 col-sm-12 col-md-12">
            <div class="col-12 col-sm-3 col-md-3 float-left">
                <a href="{{route('pdt.index')}}" class="btn btn-danger">{{__('messages.Les Produits')}}</a>
            </div>
            <div class="col-12 col-sm-3 col-md-3 float-left">
                <a href="{{route('mag.index')}}" class="btn btn-primary">{{__('messages.Les Magasins')}}</a>
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
                <table id="liste_cat" class="table table-striped table-bordered data-table">
                    <thead>
                    <tr>
                        <th>{{__('messages.Libelle')}}</th>
                        <th>{{__('messages.Type de stockage')}}</th>
                        <th>{{__('messages.Actions')}}</th>
                        <th>{{__('messages.Imprimer')}}</th>
                    </tr>
                    </thead>
                </table>
            </div>

            <!--Ajouter un produit -->
            <div id="categorieModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{__('messgaes.Creer un nouvelle categorie')}}</h4>
                        </div>
                        <div class="modal-body">
                            <span id="form_result"></span>
                            <form method="post" id="cat_form" class="form-horizontal">
                                @csrf

                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Libelle')}} </label>
                                    <input type="text" name="libelle" id="libelle" class="form-control" required="required"/>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Type de stockage des produits')}} </label>
                                    <select name="type" id="type" class="form-control">
                                        <option value="">{{__('messages.Selectionner un type')}}</option>
                                        <option value="Non_stockable">{{__('messages.Non_Stockable')}}</option>
                                        <option value="Stockable">{{__('messages.Stockable')}}</option>
                                    </select>
                                </div>

                                <div class="form-group" align="center">
                                    <input type="hidden" name="categorie_id" id="categorie_id" />
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
                            {{__('messages.COUT DE LA CATEGORIE')}}
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="col-md-12">
                                    <table id="coutStock" class="table table-responsive table-striped table-hover table-bordered">

                                    </table>
                                </div>
                            </div>

                            <div class="form-group" align="center">
                                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Quitter')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!--Supprimer le produit -->
            <main id="confirmModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title">{{__('messages.Confirmation')}}</h2>
                        </div>
                        <div class="modal-body">
                            <h5 align="center" style="margin:0;">{{__('messages.Etes vous sure de supprimer cette categorie')}}?</h5>
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
            $('#liste_cat').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    url: "{{ route('cat.index') }}",
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
                        data:'categorie_id',
                        name:'categorie_id',
                        render:function (data, type, row) {
                            return "<a href='cat.imprimer/"+row.categorie_id+"' class='btn btn-primary btn-sm'><i class='fa fa-print'></i></a>"}
                    }
                ]
            });

            $('#create_cat').click(function(){
                $('.modal-title').text("{{__('messages.Creer une categorie')}}");
                $('#action_button').val("{{__('messages.Ajouter')}}");
                $('#categorie_id').val('');
                $('#libelle').val('');
                $('#categorieModal').modal('show');
                $('#form_result').html('');
            });

            $('#cat_form').on('submit', function(event){
                event.preventDefault();
                $.ajax({
                    url:"{{ route('cat.store') }}",
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
                            $('#cat_form')[0].reset();
                            $('#liste_cat').DataTable().ajax.reload();
                        }
                        $('#form_result').html(html);
                    }
                })
            });

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');
                $('#form_result').html('');
                $.ajax({
                    url:"cat/"+id+"/edit",
                    dataType:"json",
                    success:function(html){
                        $('#libelle').val(html.data.libelle);
                        $('#type').val(html.data.type);
                        $('#categorie_id').val(id);
                        $('.modal-title').text("{{__('messages.Editer une categorie')}}");
                        $('#action_button').val("{{__('messages.Editer')}}");
                        $('#categorieModal').modal('show');
                    }
                })
            });

            var categorie_id;
            $(document).on('click', '.delete', function(){
                categorie_id = $(this).attr('id');
                $('.modal-title').text("{{__('messages.Confirmation')}}");
                $('#ok_button').text('{{__('messages.Oui')}}');
                $('#confirmModal').modal('show');
            });

            $('#ok_button').click(function(){
                $.ajax({
                    url:"cat.delete/"+categorie_id,
                    beforeSend:function(){
                        $('#ok_button').text('{{__('messages.Suppression')}}...');
                    },
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_cat').DataTable().ajax.reload();
                        }, 500);
                    }
                })
            });

            $(document).on('click', '.cout', function(){
                categorie_id = $(this).attr('id');
                $('#coutStock').load('cat.cout_stock/'+categorie_id);
                $('#coutstoskModal').modal('show');
            });

            $('#ok_button').click(function(){
                $.ajax({
                    url:"cat.delete/"+categorie_id,
                    beforeSend:function(){
                        $('#ok_button').text('{{__('messages.Suppression')}}...');
                    },
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_cat').DataTable().ajax.reload();
                        }, 500);
                    }
                })
            });

        });
    </script>
@endsection
