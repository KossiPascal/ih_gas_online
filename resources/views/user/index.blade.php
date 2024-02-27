@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: Gestion Produit')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <h3 class="ml-5">{{__('messages.GESTION DES UTILISATEURS')}}</h3>
        <div class="col-12 col-md-12">
            <div class="col-12 col-sm-3 col-md-3 float-left">
                <button type="button" name="create_user" id="create_user" class="btn btn-success"><i class="fa fa-user"></i> {{__('messages.Creer un Utilisateur')}}</button>
            </div>
            <div class="col-12 col-sm-3 col-md-3 float-left">
                    <a href="{{route('user.index')}}" class="btn btn-primary"><i class="fa fa-user"></i> {{__('messages.Les Profils')}}</a>
            </div>
            <div class="col-12 col-sm-3 col-md-3 float-left">
                    <a href="#" class="btn btn-warning"><i class="fa fa-database"></i> {{__('messages.Initialisation de la base')}}</a>
            </div>
            <div class="col-12 col-sm-3 col-md-3 float-right">
                <a href="{{route('centre.index')}}" class="btn btn-danger"><i class="fa fa-info"></i> {{__('messages.Information de la structure')}}</a>
            </div>
        </div>

        <div class="info-box">
            <div class="table-responsive">
                <table id="liste_user" class="table table-striped table-bordered data-table">
                    <thead>
                    <tr>
                        <th>{{__('messages.Nom et Prenom')}}</th>
                        <th>{{__('messages.Compte')}}</th>
                        <th>{{__('messages.Profil')}}</th>
                        <th>{{__('messages.Formation Sanitaire')}}</th>
                        <th>{{__('messages.DPS')}}</th>
                        <th>{{__('messages.Actions')}}</th>
                    </tr>
                    </thead>
                </table>
            </div>

            <!--Ajouter un produit -->
            <div id="userModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{__('messages.Creer un nouveau utilisateur')}}</h4>
                        </div>
                        <div class="modal-body">
                            <span id="form_result"></span>
                            <form method="post" id="user_form" class="form-horizontal">
                                @csrf

                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Nom et Prenom')}} : </label>
                                    <input type="text" name="name" id="name" class="form-control" required="required"/>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Compte')}} : </label>
                                    <input type="text" name="email" id="email" class="form-control" required="required"/>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-12" > {{__('messages.Mot de passe')}}: </label>
                                    <input type="password" name="password" id="password" class="form-control" required="required"/>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('Profil d utilisateur') !!}
                                    {!! Form::select('profil_id[]',$profils,null,['class'=>'form-control','id'=>'profil_id','multiple']) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label(__('messages.Formation Sanitaire')) !!}
                                    {!! Form::select('centre_id',$centres,null,['class'=>'form-control','id'=>'centre_id']) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label(__('messages.Direction Prefectorale')) !!}
                                    {!! Form::select('dps_id',$directions,null,['class'=>'form-control','id'=>'dps_id']) !!}
                                </div>

                                <div class="form-group" align="center">
                                    <input type="hidden" name="id" id="id" />
                                    <input type="hidden" name="type" id="type" value="DPS"/>
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
                            <h5 align="center" style="margin:0;">{{__('messages.Etes vous sure de supprimer cet utilisateur')}}?</h5>
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
            $('#liste_user').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    url: "{{ route('user.user') }}",
                },
                columns:[
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'nom',
                        name: 'nom'
                    },
                    {
                        data: 'nom_centre',
                        name: 'nom_centre'
                    },
                    {
                        data: 'dps_nom',
                        name: 'dps_nom'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    }
                ]
            });

            $('#create_user').click(function(){
                $('.modal-title').text("{{__('messages.Creer un Utilissteur')}}");
                $('#action_button').val("{{__('messages.Ajouter')}}");
                $('#name').val("");
                $('#email').val("");
                $('#password').val("");
                $('#id').val("");
                $('#userModal').modal('show');
            });

            $('#user_form').on('submit', function(event){
                event.preventDefault();
                
                $.ajax({
                    url:"{{ route('user.usersave') }}",
                    method:"POST",
                    data: new FormData(this),
                    contentType: false,
                    cache:false,
                    processData: false,
                    dataType:"json",
                    success:function(data)
                    {
                        console.log(data+"SAVE CLICKED")
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
                        if(data.warning)
                        {
                            html = '<div class="alert alert-warning">' + data.warning + '</div>';
                            $('#user_form')[0].reset();
                            $('#liste_user').DataTable().ajax.reload();
                        }
                        if(data.error)
                        {
                            html = '<div class="alert alert-danger">' + data.error + '</div>';
                            $('#user_form')[0].reset();
                            $('#liste_user').DataTable().ajax.reload();
                        }

                        if(data.success)
                        {
                            html = '<div class="alert alert-success">' + data.success + '</div>';
                            $('#user_form')[0].reset();
                            $('#liste_user').DataTable().ajax.reload();
                        }
                        $('#form_result').html(html);
                    }
                })
            });

            $(document).on('click', '.edituser', function(){
                var id = $(this).attr('id');
                $('#form_result').html('');
                $.ajax({
                    url:"user.edituser/"+id,
                    dataType:"json",
                    success:function(data){
                        $('#name').val(data.name);
                        $('#password').val(data.password);
                        $('#email').val(data.email);
                        $('#profil_id').val(data.profil_id);
                        $('#centre_id').val(data.centre_id);
                        $('#dps_id').val(data.dps_id);
                        $('#id').val(data.id);
                        $('#type').val("FS");
                        $('.modal-title').text("Editer un utilisateur");
                        $('#action_button').val("{{__('messages.Valider')}}");
                        $('#userModal').modal('show');
                    }
                })
            });


            var id;
            $(document).on('click', '.delete', function(){
                id = $(this).attr('id');
                $('.modal-title').text("{{__('messages.Confirmation')}}");
                $('#ok_button').text('Oui');
                $('#confirmModal').modal('show');
            });

            $('#ok_button').click(function(){
                $.ajax({
                    url:"user.deleteuser/"+id,
                    beforeSend:function(){
                        $('#ok_button').text('{{__('messages.Suppression')}}...');
                    },
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_user').DataTable().ajax.reload();
                        }, 1000);
                    }
                })
            });

        });
    </script>
@endsection
