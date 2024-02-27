@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: Gestion Produit')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        
        <div class="col-12 col-md-12">
            <div class="col-12 col-sm-6 col-md-6 float-left">
            <h3 class="ml-5">GESTION DES UTILISATEURS DPS</h3>
            </div>
          
            <div class="col-12 col-sm-6 col-md-6 float-right">
                <button type="button" name="create_user" id="create_user" class="btn btn-success"><i class="fa fa-user"></i> Creer un Utilisateur</button>
            </div>
        </div>

        <div class="info-box">
            <div class="table-responsive">
                <table id="liste_user" class="table table-striped table-bordered data-table">
                    <thead>
                    <tr>
                        <th>Nom et Prenom</th>
                        <th>Compte</th>
                        <th>Profil</th>
                        <th>DPS</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                </table>
            </div>

            <!--Ajouter un produit -->
            <div id="userModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Creer un nouveau utilisateur</h4>
                        </div>
                        <div class="modal-body">
                            <span id="form_result"></span>
                            <form method="post" id="user_form" class="form-horizontal">
                                @csrf

                                <div class="form-group">
                                    <label class="control-label col-md-12" >Nom et Prenom : </label>
                                    <input type="text" name="name" id="name" class="form-control" required="required"/>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-12" >Compte : </label>
                                    <input type="text" name="email" id="email" class="form-control" required="required"/>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-12" > Mot de passe: </label>
                                    <input type="password" name="password" id="password" class="form-control" required="required"/>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('Profil d utilisateur') !!}
                                    {!! Form::select('profil_id[]',$profils,null,['class'=>'form-control','id'=>'profil_id','multiple']) !!}
                                </div>

                                <div class="form-group" align="center">
                                    <input type="hidden" name="id" id="id" />
                                    <input type="hidden" name="type" id="type" value="DPS"/>
                                    <input type="hidden" name="centre_id" id="centre_id" value="{{Auth::user()->centre_id}}"/>
                                    <input type="hidden" name="dps_id" id="dps_id" value="{{Auth::user()->dps_id}}"/>
                                    <input type="submit" name="action_button" id="action_button" class="btn btn-success" value="Enregistrer" />
                                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>Quitter</button>
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
                            <h2 class="modal-title">Confirmation</h2>
                        </div>
                        <div class="modal-body">
                            <h5 align="center" style="margin:0;">Etes vous sure de supprimer cet utilisateur?</h5>
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
            $("#profil_id").mousedown(function(e){
                e.preventDefault();

                var select = this;
                var scroll = select.scrollTop;

                e.target.selected = !e.target.selected;

                setTimeout(function(){select.scrollTop = scroll;}, 0);

                $(select).focus();
            });

            $('#liste_user').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    url: "{{ route('user.userdps') }}",
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
                $('.modal-title').text("Creer un Utilissteur");
                $('#form_result').html("");
                $('#action_button').val("Ajouter");
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
                            $('#user_form')[0].reset();
                            $('#liste_user').DataTable().ajax.reload();
                        }

                        if(data.warning)
                        {
                            html = '<div class="alert alert-warning">' + data.warning + '</div>';
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
                        $('#type').val("DPS");
                        $('#id').val(data.id);
                        $('.modal-title').text("Editer un utilisateur");
                        $('#action_button').val("Valider");
                        $('#userModal').modal('show');
                    }
                })
            });


            var id;
            $(document).on('click', '.deleteuser', function(){
                id = $(this).attr('id');
                $('.modal-title').text("Confirmation");
                $('#ok_button').text('Oui');
                $('#confirmModal').modal('show');
            });

            $('#ok_button').click(function(){
                $.ajax({
                    url:"user.deleteuser/"+id,
                    beforeSend:function(){
                        $('#ok_button').text('Suppression...');
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
