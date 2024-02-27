@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: Gestion Produit')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <h3 class="ml-5">{{__('messages.GESTION DES PROFILS')}}</h3>
        <div class="col-12 col-sm-12 col-md-12">
            <div class="col-12 col-md-3 float-left">
                <button type="button" name="create_profil" id="create_profil" class="btn btn-success"><i class="fa fa-user"></i> {{__('messages.Creer un Profil')}}</button>
            </div>
            <div class="col-12 col-sm-3 col-md-3 float-left">
                    <a href="{{route('user.user')}}" class="btn btn-primary"><i class="fa fa-database"></i> {{__('messages.Les Utilisateurs')}}</a>
            </div>
            <div class="col-12 col-sm-3 col-md-3 float-left">
                    <a href="#" class="btn btn-warning"><i class="fa fa-database"></i> {{__('messages.Initialisation de la base')}}</a>
            </div>
            <div class="col-12 col-sm-3 col-md-3 float-right">
                <a href="{{route('centre.index')}}" class="btn btn-danger"><i class="fa fa-info"></i> {{__('messages.Information de la structure')}}</a>
            </div>
        </div>
        <br>
        <br>
        <div class="info-box">
            <div class="table-responsive">
                <table id="liste_profil" class="table table-striped table-bordered data-table">
                    <thead>
                    <tr>
                        <th>{{__('messages.Libelle')}}</th>
                        <th>{{__('messages.Supprimer')}}</th>
                        <th>{{__('messages.Editer')}}</th>
                    </tr>
                    </thead>
                </table>
            </div>

            <!--Ajouter un produit -->
            <div id="profilModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{__('messages.Creer un nouveau profil')}}</h4>
                        </div>
                        <div class="modal-body">
                            <span id="form_result"></span>
                            <form method="post" id="user_form" class="form-horizontal">
                                @csrf

                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Nom du profil')}} : </label>
                                    <input type="text" name="nom" id="nom" class="form-control" required="required"/>
                                </div>

                                <div class="form-group">
                                    <title class="title">{{__('messages.Cocher les droits du profil')}}</title>
                                    @foreach($droits as $droit)
                                        <div class="form-group form-check">
                                           <input type="checkbox" class="form-check-input" name="droits[]" value="{{$droit->droit_id}}" id="{{$droit->droit_id}}" @foreach($profil->droits() as $profilDroit) @if($profilDroit==$droit->statut) checked @endif @endforeach>
                                            <label for="{{$droit->droit_id}}" class="form-check-label" >{{$droit->nom}}</label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="form-group" align="center">
                                    <input type="hidden" name="profil_id" id="profil_id" />
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
                            <h5 align="center" style="margin:0;">{{__('messages.Etes vous sure de supprimer ce profil')}}?</h5>
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

            $('#liste_profil').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    url: "{{ route('user.index') }}",
                },
                columns:[
                    {
                        data: 'nom',
                        name: 'nom'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    },
                    {
                        data:'profil_id',
                        name:'profil_id',
                        render:function (data, type, row) {
                            return "<a href='user.editprofil/"+row.profil_id+"' class='btn btn-success'><i class='fa fa-edit'></i></a>"}
                    }
                ]
            });

            $('#create_profil').click(function(){
                $('.modal-title').text("{{__('messages.Creer un profil')}}");
                $('#action_button').val("{{__('messages.Ajouter')}}");
                $('#nom').val("");
                $('#profil_id').val("");
                $('#form_result').html('');
                $('#profilModal').modal('show');
            });

            $('#user_form').on('submit', function(event){
                event.preventDefault();
                $.ajax({
                    url:"{{ route('user.createprofil') }}",
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
                            html = '<div class="alert alert-warning">' + data.error + '</div>';
                            $('#user_form')[0].reset();
                            $('#liste_profil').DataTable().ajax.reload();
                        }

                        if(data.success)
                        {
                            html = '<div class="alert alert-success">' + data.success + '</div>';
                            $('#user_form')[0].reset();
                            $('#liste_profil').DataTable().ajax.reload();
                        }
                        $('#form_result').html(html);
                    }
                })
            });

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');
                $('#form_result').html('');
                $.ajax({
                    url:"user.editprofil/"+id,
                    dataType:"json",
                    success:function(data){
                        $('#nom').val(data.nom);
                        $('#profil_id').val(data.profil_id);
                        $('.modal-title').text("{{__('messages.Editer un profil')}}");
                        $('#action_button').val("{{__('messages.Valider')}}");
                        $('#profilModal').modal('show');
                    }
                })
            });


            var profil_id;
            $(document).on('click', '.delete', function(){
                profil_id = $(this).attr('id');
                $('.modal-title').text("{{__('messages.Confirmation')}}");
                $('#ok_button').text('{{__('messages.Oui')}}');
                $('#confirmModal').modal('show');
            });

            $('#ok_button').click(function(){
                $.ajax({
                    url:"user.deleteprofil/"+profil_id,
                    beforeSend:function(){
                        $('#ok_button').text('{{__('messages.Suppression')}}...');
                    },
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_profil').DataTable().ajax.reload();
                        }, 1000);
                    }
                })
            });

        });
    </script>
@endsection
