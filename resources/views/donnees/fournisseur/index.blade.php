@extends('layouts.adminlayout')
@section('title','PCSOFT V4: Gestion Categorie')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <div class="col-12 col-sm-12 col-md-12 float-left">
            <div class="col-md-6 float-left">
                <h3 class="ml-5">{{__('messages.GESTION DES FOURNISSEURS')}}</h3>
            </div>
            <div class="col-md-6 float-right">
                <button type="button" name="create_four" id="create_four" class="btn btn-success"><i class="fa fa-plus"></i> {{__('messages.Nouveau Fournisseur')}}</button>
            </div>
        </div>

        <div class="col-12 col-sm-12 col-md-12">
            <div class="col-12 col-sm-3 col-md-3 float-left">
                <a href="{{route('cat.index')}}" class="btn btn-success">{{__('messages.Les Categories')}}</a>
            </div>
            <div class="col-12 col-sm-3 col-md-3 float-left">
                <a href="{{route('pdt.index')}}" class="btn btn-danger">{{__('messages.Les Produits')}}</a>
            </div>
            <div class="col-12 col-sm-3 col-md-3 float-left">
                <a href="{{route('mag.index')}}" class="btn btn-primary">{{__('messages.Les Magasins')}}</a>
            </div>
            <div class="col-12 col-sm-3 col-md-3 float-right">
                <a href="{{route('ass.index')}}" class="btn btn-outline-success">{{__('messages.Les Assurances')}}</a>
            </div>

        </div>
        <br>
        <br>
        <div class="info-box">
            <div class="table-responsive">
                <table id="liste_four" class="table table-striped table-bordered data-table">
                    <thead>
                    <tr>
                        <th>{{__('messages.Nom')}}</th>
                        <th>{{__('messages.Adresse')}}</th>
                        <th>{{__('messages.Ville')}}</th>
                        <th>{{__('messages.Telephone')}}</th>
                        <th>{{__('messages.Email')}}</th>
                        <th>{{__('messages.Actions')}}</th>
                    </tr>
                    </thead>
                </table>
            </div>

            <!--Ajouter un produit -->
            <div id="fourModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{__('messages.Creer un nouveau fournisseur')}}</h4>
                        </div>
                        <div class="modal-body">
                            <span id="form_result"></span>
                            <form method="post" id="four_form" class="form-horizontal">
                                @csrf

                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Nom fournisseur')}} : </label>
                                    <input type="text" name="nom" id="nom" class="form-control" required="required"/>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Adresse Founisseur')}} : </label>
                                    <input type="text" name="adresse" id="adresse" class="form-control" required="required"/>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Ville Founisseur')}} : </label>
                                    <input type="text" name="ville" id="ville" class="form-control" required="required"/>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Telephone Founisseur')}} : </label>
                                    <input type="text" name="telephone" id="telephone" class="form-control" required="required"/>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Email Founisseur')}} : </label>
                                    <input type="text" name="email" id="email" class="form-control" required="required"/>
                                </div>

                                <div class="form-group" align="center">
                                    <input type="hidden" name="fournisseur_id" id="fournisseur_id" />
                                    <input type="submit" name="action_button" id="action_button" class="btn btn-success" value="{{__('messages.Enregistrer')}}" />
                                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Quitter')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!--Supprimer le produit -->
            <main id="confirmModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title">{{__('messages.Confirmation')}}</h2>
                        </div>
                        <div class="modal-body">
                            <h5 align="center" style="margin:0;">{{__('messages.Etes vous sure de supprimer ce fourniseur')}}?</h5>
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

            $('#liste_four').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    url: "{{ route('four.index') }}",
                },
                columns:[
                    {
                        data: 'nom',
                        name: 'nom'
                    },
                    {
                        data: 'adresse',
                        name: 'adresse'
                    },
                    {
                        data: 'ville',
                        name: 'ville'
                    },
                    {
                        data: 'telephone',
                        name: 'telephone'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    }
                ]
            });

            $('#create_four').click(function(){
                $('.modal-title').text("{{__('messages.Creer un fournisseur')}}");
                $('#action_button').val("{{__('messgaes.Ajouter')}}");
                $('#fournisseur_id').val("");
                $('#nom').val("");
                $('#adresse').val("");
                $('#ville').val("");
                $('#email').val("");
                $('#form_result').html('');
                $('#telephone').val("+228");
                $('#fourModal').modal('show');
            });

            $('#four_form').on('submit', function(event){
                event.preventDefault();
                $.ajax({
                    url:"{{ route('four.store') }}",
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
                            $('#four_form')[0].reset();
                            $('#liste_four').DataTable().ajax.reload();
                        }
                        $('#form_result').html(html);
                    }
                })
            });

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');

                $('#form_result').html('');
                $.ajax({
                    url:"four/"+id+"/edit",
                    dataType:"json",
                    success:function(data){
                        $('#nom').val(data.nom);
                        $('#adresse').val(data.adresse);
                        $('#ville').val(data.ville);
                        $('#email').val(data.email);
                        $('#telephone').val(data.telephone);
                        $('#fournisseur_id').val(id);
                        $('.modal-title').text("{{__('messages.Editer un founisseur')}}");
                        $('#action_button').val("{{__('messgaes.Valider')}}");
                        $('#fourModal').modal('show');
                    }
                })
            });


            var cat_num;
            $(document).on('click', '.delete', function(){
                cat_num = $(this).attr('id');
                $('.modal-title').text("{{__('messgaes.Confirmation')}}");
                $('#ok_button').text('{{__('messgaes.Oui')}}');
                $('#confirmModal').modal('show');
            });

            $('#ok_button').click(function(){
                $.ajax({
                    url:"four.delete/"+cat_num,
                    beforeSend:function(){
                        $('#ok_button').text('{{__('messgaes.Suppression')}}...');
                    },
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_four').DataTable().ajax.reload();
                        }, 500);
                    }
                })
            });

        });
    </script>
@endsection
