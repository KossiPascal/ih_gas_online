@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: Gestion Produit')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <h3 class="ml-5">INFORMATIONS DU CENTRE</h3>
        <div class="col-12 col-sm-12 col-md-12">
            <div class="col-12 col-md-4 float-left">
                <a href="{{route('user.index')}}" class="btn btn-success"><i class="fa fa-user"></i> Gestion Utilisateur</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="#" class="btn btn-primary"><i class="fa fa-database"></i> Initialisation du stock</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-right">
                <a href="#" class="btn btn-danger"><i class="fa fa-info"></i> Information de la structure</a>
            </div>
        </div>
        <br>
        <div class="info-box">
            <div class="table-responsive">
                <table id="liste_produit" class="table table-striped table-bordered data-table">
                    <thead>
                    <tr>
                        <th>Nom du centre</th>
                        <th>Adresse</th>
                        <th>Les services</th>
                        <th>Telephone</th>
                        <th>Imprimante</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                </table>
            </div>

            <!--Ajouter un produit -->
            <div id="produitModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Editer mon compte</h4>
                        </div>
                        <div class="modal-body">
                            <span id="form_result"></span>
                            <form method="post" id="pdt_form" class="form-horizontal">
                                @csrf

                                <div class="form-group">
                                    <label class="control-label col-md-12" >Nom du centre: </label>
                                    <input type="text" name="nom" id="nom" class="form-control" required="required"/>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-12" >Adresse : </label>
                                    <input type="text" name="adresse" id="adresse" class="form-control" required="required"/>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-12" > Les services: </label>
                                    <input type="text" name="service" id="service" class="form-control" required="required"/>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-12" > Telephone: </label>
                                    <input type="text" name="telephone" id="telephone" class="form-control" required="required"/>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-md-12" > Selectionner le format d impression </label>
                                    <select name="impression" id="impression" class="form-control">
                                        <option value="Format_A5">Format_A5</option>
                                        <option value="Ticket_Caisse">Ticket_Caisse</option>
                                    </select>
                                </div>

                                <div class="form-group" align="center">
                                    <input type="submit" name="action_button" id="action_button" class="btn btn-success" value="Enregistrer" />
                                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>Quitter</button>
                                </div>
                            </form>
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

            $('#liste_produit').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    url: "{{ route('centre.index') }}",
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
                        data: 'service',
                        name: 'service'
                    },
                    {
                        data: 'telephone',
                        name: 'telephone'
                    },
                    {
                        data: 'impression',
                        name: 'impression'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    }
                ]
            });

            $('#create_pdt').click(function(){
                $('.modal-title').text("Editer mes infos");
                $('#action_button').val("Ajouter");
                $('#action').val("Ajouter");
                $('#produitModal').modal('show');
            });

            $('#pdt_form').on('submit', function(event){
                event.preventDefault();
                $.ajax({
                    url:"{{ route('centre.updatecentre') }}",
                    method:"POST",
                    data:new FormData(this),
                    contentType: false,
                    cache: false,
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
                        if(data.success)
                        {
                            html = '<div class="alert alert-success">' + data.success + '</div>';
                            $('#pdt_form')[0].reset();
                            $('#produitModal').modal('show');
                            $('#liste_produit').DataTable().ajax.reload();
                        }
                        $('#form_result').html(html);
                    }
                });
            });

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');
                $('#form_result').html('');
                $.ajax({
                    url:"centre/"+id+"/edit",
                    dataType:"json",
                    success:function(html){
                        $('#nom').val(html.data.nom);
                        $('#adresse').val(html.data.adresse);
                        $('#service').val(html.data.service);
                        $('#telephone').val(html.data.telephone);
                        $('#impression').val(html.data.impression);
                        $('#hidden_id').val(html.data.id);
                        $('.modal-title').text("Editer mes infos");
                        $('#action_button').val("Editer");
                        $('#action').val("Editer");
                        $('#produitModal').modal('show');
                    }
                })
            });

        });
    </script>
@endsection
