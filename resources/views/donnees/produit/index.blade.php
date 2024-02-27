@extends('layouts.adminlayout')
@section('title','PCSOFT V4: Gestion Produit')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <div class="col-12 col-sm-12 col-md-12 float-left">
            <div class="col-md-6 float-left">
                <h3 class="ml-5">{{__('messages.GESTION DES PRODUITS')}}</h3>
            </div>
            <div class="col-md-6 float-right">
                <button type="button" name="create_pdt" id="create_pdt" class="btn btn-success"><i class="fa fa-plus"></i> {{__('messages.Nouveau produit')}}</button>
            </div>
        </div>

        <div class="col-12 col-sm-12 col-md-12">
            <div class="col-12 col-sm-3 col-md-3 float-left">
                <a href="{{route('cat.index')}}" class="btn btn-danger">{{__('messages.Les Categories')}}</a>
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
                <table id="liste_produit" class="table table-striped table-bordered data-table">
                    <thead>
                    <tr>
                        <th>{{__('messages.Reference')}}</th>
                        <th>{{__('messages.Libelle')}}</th>
                        <th>{{__('messages.Famille')}}</th>
                        <th>{{__('messages.PA')}}</th>
                        <th>{{__('messages.PV')}}</th>
                        <th>{{__('messages.Actions')}}</th>
                    </tr>
                    </thead>
                </table>
            </div>

            <!--Ajouter un produit -->
            <div id="produitModal" class="modal fade" role="dialog">
                <div class="modal-dialog modal-dialog-scrollable modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{__('messages.Creer un nouveau produit')}}</h4>
                        </div>
                        <div class="modal-body">
                            <span id="form_result"></span>
                            <form method="post" id="pdt_form" class="form-horizontal">
                                @csrf
                                <table class="responsive-table table">
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <label class="control-label col-md-12" >{{__('messages.Reference Produit')}} </label>
                                                <input type="text" name="reference" id="reference" class="form-control"/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <label class="control-label col-md-12" >{{__('messages.Libelle Produit')}} </label>
                                                <input type="text" name="nom_commercial" id="nom_commercial" class="form-control" required="required"/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <label class="control-label col-md-12" >{{__('messages.Famille therapeutique')}} : </label>
                                                <input type="text" name="famille_therapeutique" id="famille_therapeutique" class="form-control"/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <label class="control-label col-md-12" >{{__('messages.DCI')}} : </label>
                                                <input type="text" name="dci" id="dci" class="form-control" required="required"/>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <label class="control-label col-md-12" >{{__('messages.Prix achat')}} </label>
                                                <input type="text" name="prix_achat" id="prix_achat" class="form-control"/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <label class="control-label col-md-12" >{{__('messages.Prix de vente')}} </label>
                                                <input type="text" name="prix_vente" id="prix_vente" class="form-control" required="required"/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <label class="control-label col-md-12" >{{__('messages.Stock minimum')}} </label>
                                                <input type="text" name="stock_minimal" id="stock_minimal" class="form-control"/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group" id="pv_group">
                                                <label class="control-label col-md-12" >{{__('messages.Stock maximum')}} </label>
                                                <input type="text" name="stock_maximal" id="stock_maximal" class="form-control"/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <label class="control-label col-md-12" >{{__('messages.Unite achat')}}</label>
                                                <input type="text" name="unite_achat" id="unite_achat" class="form-control" value="1"/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <label class="control-label col-md-12" >{{__('messages.Unite vente')}}</label>
                                                <input type="text" name="unite_vente" id="unite_vente" class="form-control" value="1"/>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <label class="control-label col-md-12" >{{__('messages.Type de produit')}}</label>
                                                <select name="type" id="type" class="form-control">
                                                    <option id="Non_Perissable" value="Non_Perissable">{{__('messages.Non_Perissable')}}</option>
                                                    <option id="Perissable" value="Perissable">{{__('messages.Perissable')}}</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                {!! Form::label(__('messages.Selectionner la categorie')) !!}
                                                {!! Form::select('categorie_id',$categories,null,['class'=>'form-control','id'=>'categorie_id','onchange'=>'verifType()']) !!}
                                            </div>
                                        </td>
                                    </tr>


                                    <tr>
                                        <td>
                                            <div class="form-group" align="center">
                                                <input type="hidden" name="produit_id" id="produit_id" />
                                                <input type="submit" name="action_button" id="action_button" class="btn btn-success" value="{{__('messages.Enregistrer')}}" />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group" align="center">
                                                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Quitter')}}</button>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!--Infos un produit -->
            <div id="infoModal" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">{{__('messages.Details produit')}}</h4>
                        </div>
                        <div class="modal-body">
                        <table class="responsive-table table">
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <input type="text" name="ref" id="ref" class="form-control" readonly/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <input type="text" name="lib" id="lib" class="form-control" readonly/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <input type="text" name="famille" id="famille" class="form-control" readonly/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <input type="text" name="deno" id="deno" class="form-control" readonly/>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <input type="text" name="pa" id="pa" class="form-control" readonly/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <input type="text" name="pv" id="pv" class="form-control" readonly/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <input type="text" name="minimum" id="minimum" class="form-control" readonly/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group" id="pv_group">
                                                <input type="text" name="maximum" id="maximum" class="form-control" readonly/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <input type="text" name="uniteachat" id="uniteachat" class="form-control" readonly/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group" id="pv_group">
                                                <input type="text" name="unitevente" id="unitevente" class="form-control" readonly/>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="form-group">
                                                <input type="text" name="typepdt" id="typepdt" class="form-control" readonly/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-group">
                                                <input type="text" name="categorie" id="categorie" class="form-control" readonly/>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>

                                        </td>
                                        <td>
                                            <div class="form-group" align="center">
                                                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Quitter')}}</button>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
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
                            <h5 align="center" style="margin:0;">{{__('messages.Etes vous sure de supprimer ce produit')}}?</h5>
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
        function verifType(){
            var categorie_id = document.getElementById('categorie_id').value;
            $.ajax({
                url:"cat/"+categorie_id+"/edit",
                dataType:"json",
                success:function(data){
                    if(data.data.cat_type='Stockable'){
                        document.getElementById('pv_group').hidden=false;
                        document.getElementById('pdt_pv').hidden=false;
                    }else{
                        document.getElementById('pv_group').hidden=true;
                        document.getElementById('pdt_pv').hidden=true;
                    }
                }
            })
        }
        $(document).ready(function(){

            $('#liste_produit').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    url: "{{ route('pdt.index') }}",
                },
                columns:[
                    {
                        data: 'reference',
                        name: 'reference'
                    },
                    {
                        data: 'nom_commercial',
                        name: 'nom_commercial'
                    },
                    {
                        data: 'famille_therapeutique',
                        name: 'famille_therapeutique'
                    },
                    {
                        data: 'prix_achat',
                        name: 'prix_achat'
                    },
                    {
                        data: 'prix_vente',
                        name: 'prix_vente'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    }
                ]
            });

            $('#create_pdt').click(function(){
                $('.modal-title').text("{{__('messages.Creer un Produit')}}");
                $('#action_button').val("{{__('messages.Ajouter')}}");
                $('#reference').val("");
                $('#nom_commercial').val("");
                $('#prix_achat').val("");
                $('#prix_vente').val("");
                $('#unite').val("");
                $('#stock_minimal').val("5");
                $('#stock_maximal').val("200");
                //$('#type').val("");
                $('#produitModal').modal('show');
            });

            $('#pdt_form').on('submit', function(event){
                event.preventDefault();
                $.ajax({
                    url:"{{ route('pdt.store') }}",
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
                            $('#pdt_form')[0].reset();
                            $('#liste_produit').DataTable().ajax.reload();
                        }

                        $('#form_result').html(html);
                    }
                })
            });

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');
                $('#form_result').html('');
                $.ajax({
                    url:"pdt/"+id+"/edit",
                    dataType:"json",
                    success:function(data){
                        $('#reference').val(data.reference);
                        $('#nom_commercial').val(data.nom_commercial);
                        $('#dci').val(data.dci);
                        $('#famille_therapeutique').val(data.famille_therapeutique);
                        $('#prix_achat').val(data.prix_achat);
                        $('#prix_vente').val(data.prix_vente);
                        $('#unite_achat').val(data.unite_achat);
                        $('#unite_vente').val(data.unite_vente);
                        $('#type').val(data.type);
                        $('#stock_minimal').val(data.stock_minimal);
                        $('#stock_maximal').val(data.stock_maximal);
                        $('#type').val(data.type);
                        $('#categorie_id').val(data.categorie_id);
                        $('#produit_id').val(id);
                        $('.modal-title').text("{{__('messages.Editer un produit')}}");
                        $('#action_button').val("{{__('messages.Valider')}}");
                        $('#produitModal').modal('show');
                    }
                })
            });

            $(document).on('click', '.infos', function(){
                var id = $(this).attr('id');
                $('#form_result').html('');
                $.ajax({
                    url:"pdt/"+id+"/edit",
                    dataType:"json",
                    success:function(data){
                        $('#ref').val('Reference => '+data.reference);
                        $('#lib').val('Libelle => '+data.nom_commercial);
                        $('#deno').val('DCI => '+data.dci);
                        $('#famille').val('Famille => '+data.famille_therapeutique);
                        $('#pa').val('Prix Achat => '+data.prix_achat);
                        $('#pv').val('Prix de vente => '+data.prix_vente);
                        $('#minimum').val('Minumum => '+data.stock_minimal);
                        $('#maximum').val('Maximum => '+data.stock_maximal);
                        $('#uniteachat').val('Unite achat => '+data.unite_achat);
                        $('#unitevente').val('Unite de vente => '+data.unite_vente);
                        $('#typepdt').val('Type => '+data.type);
                        $('#categorie').val('Categorie => '+data.categorie_id);
                        $('#pdtnum').val(id);
                        $('.modal-title').text("{{__('messages.Infos produit')}}");
                        $('#infoModal').modal('show');
                    }
                })
            });


            var produit_id;
            $(document).on('click', '.delete', function(){
                produit_id = $(this).attr('id');
                $('.modal-title').text("{{__('messages.Confirmer')}}");
                $('#ok_button').text('{{__('messages.oui')}}');
                $('#confirmModal').modal('show');
            });

            $('#ok_button').click(function(){
                $.ajax({
                    url:"pdt.delete/"+produit_id,
                    beforeSend:function(){
                        $('#ok_button').text('{{__('messages.Suppression')}}...');
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
