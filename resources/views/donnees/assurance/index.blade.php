@extends('layouts.adminlayout')
@section('title','PCSOFT V4: Gestion Categorie')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <div class="col-12 col-sm-12 col-md-12 float-left">
            <div class="col-md-6 float-left">
                <h3 class="ml-5">{{__('messages.GESTION DES ASSURANCES')}}</h3>
            </div>
            <div class="col-md-6 float-right">
                <button type="button" name="create_mut" id="create_mut" class="btn btn-success"><i class="fa fa-plus"></i> {{__('messages.Nouvele assurance/Mutulle')}}</button>
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
                <a href="{{route('mag.index')}}" class="btn btn-warning">{{__('messages.Les Magasins')}}</a>
            </div>
            <div class="col-12 col-sm-3 col-md-3 float-right">
                <a href="{{route('four.index')}}" class="btn btn-outline-success">{{__('messages.Les Fournisseurs')}}</a>
            </div>
        </div>
        <br>
        <br>
        <div class="info-box">
            <div class="table-responsive">
                <table id="liste_utuelle" class="table table-striped table-bordered data-table">
                    <thead>
                    <tr>
                        <th>{{__('messages.Libelle')}}</th>
                        <th>{{__('messages.Taux')}}</th>
                        <th>{{__('messages.Actions')}}</th>
                    </tr>
                    </thead>
                </table>
            </div>

            <!--Ajouter un produit -->
            <div id="mutuelleModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{__('messages.Creer un nouvelle mutuelle /assurance')}}</h4>
                        </div>
                        <div class="modal-body">
                            <span id="form_result"></span>
                            <form method="post" id="mut_form" class="form-horizontal">
                                @csrf

                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Nom de assurance ou mutuelle')}} : </label>
                                    <input type="text" name="nom" id="nom" class="form-control" required="required"/>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-12" >{{__('messages.Taux de prise en charge')}} : </label>
                                    <input type="text" name="taux" id="taux" class="form-control" required="required"/>
                                </div>

                                <div class="form-group" align="center">
                                    <input type="hidden" name="assurance_id" id="assurance_id" />
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
                            <h5 align="center" style="margin:0;">{{__('messages.Etes vous sure de supprimer cette assurance')}}?</h5>
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

            $('#liste_utuelle').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    url: "{{ route('ass.index') }}",
                },
                columns:[
                    {
                        data: 'nom',
                        name: 'nom'
                    },
                    {
                        data: 'taux',
                        name: 'taux'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    }
                ]
            });

            $('#create_mut').click(function(){
                $('.modal-title').text("{{__('messages.Creer une assurance/Mutuelle')}}");
                $('#action_button').val("{{__('messages.Ajouter')}}");
                $('#assurance_id').val("");
                $('#nom').val("");
                $('#taux').val("");
                $('#mutuelleModal').modal('show');
            });

            $('#mut_form').on('submit', function(event){
                event.preventDefault();
                $.ajax({
                    url:"{{ route('ass.store') }}",
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
                            $('#mut_form')[0].reset();
                            $('#liste_utuelle').DataTable().ajax.reload();
                        }
                        $('#form_result').html(html);
                    }
                })
            });

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');

                $('#form_result').html('');
                $.ajax({
                    url:"ass/"+id+"/edit",
                    dataType:"json",
                    success:function(html){
                        $('#nom').val(html.data.nom);
                        $('#taux').val(html.data.taux);
                        $('#assurance_id').val(id);
                        $('.modal-title').text("{{__('messages.Editer une mutuelle/assurance')}}");
                        $('#action_button').val("{{__('messages.Editer')}}");
                        $('#mutuelleModal').modal('show');
                    }
                })
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
                    url:"ass.delete/"+cat_num,
                    beforeSend:function(){
                        $('#ok_button').text('{{__('messages.Suppression')}}...');
                    },
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_utuelle').DataTable().ajax.reload();
                        }, 500);
                    }
                })
            });

        });
    </script>
@endsection
