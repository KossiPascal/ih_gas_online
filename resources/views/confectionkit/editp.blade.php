@extends('layouts.pharmacielayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')

@section('extra-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">
        <div class="col-md-12"><h3 class="ml-5" style="text-align:center">{{__('messages.EDITER UN APRO DEPOT')}}</h3></div>
        <div class="col-md-12">
            <div class="col-md-6 float-left">
                <h3 class="ml-5"></h3>
            </div>

            <div class="col-md-6 float-right" style="text-align: right">
                <a href="{{route('approdep.histo')}}" class="btn btn-warning"><i class="fa fa-list"></i> {{__('messages.HISTORIQUE DES APPROVISIONNEMENT')}}</a>
            </div>
        </div>
        <div class="col-md-12 float-left">
            <div class="col-md-6 float-left">
                <h5 class="ml-5">{{__('messages.PRODUITS SELECTIONNES')}}</h5>
            </div>

            <div class="col-md-6 float-right">
                </i> {{__('messages.LISTE DES PRODUITS')}}</a>
            </div>
        </div>

        <div class="col-md-12 float-left">
            <div class="col-md-6 float-left">
                <div class="contour_div">
                    <div class="contour_table">
                        <table class="table table-striped table-bordered contour_table" id="pdt_selected">
                            <thead>
                            <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                                <td class="description">{{__('messages.Produit')}}</td>
                                <td class="price">{{__('messages.Prix')}}</td>
                                <td class="quantity">{{__('messages.Qte')}}</td>
                                <td class="total">{{__('messages.Total')}}</td>
                                <td></td>
                            </tr>
                            @if(session('status'))
                                <div class="alert alert-success">
                                    {{session('status')}}
                                </div>
                            @endif
                            @if(session('error'))

                                <div class="alert alert-danger">
                                    {{session('error')}}
                                </div>
                            @endif
                            </thead>
                            <tbody>
                            <?php $count =1;?>
                            @foreach($pdtcon as $produit)
                                <tr>
                                    <td class="cart_title">{{$produit->pdt_lib}}</td>
                                    <td class="cart_price">{{$produit->pdt_pv}}</td>
                                    <td class="cart_price">{{$produit->qte}}</td>
                                    <td class="cart_total">{{$produit->mont}}</td>
                                    <td class="cart_delete">
                                        <a href="{{route('approdep.delete',[$produit->ad_num,$produit->pdt_num])}}" class="btn btn-danger"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                            <?php $count++;?>
                            </tbody>
                        </table>
                    </div>
                    <div class="info-box">
                        @include('approdep/form')
                    </div>
                </div>

            </div>

            <div class="col-md-6 float-right">
                <div class="info-box">
                    <div class="table-responsive div_style">
                        <table id="liste_produit" class="display table table-striped table-bordered data-table">
                            <thead>
                            <tr>
                                <th>{{__('messages.Libelle')}}</th>
                                <th>{{__('messages.PU')}}</th>
                                <th>{{__('messages.Magasin')}}</th>
                                <th>{{__('messages.Depot')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $count =1;?>
                            @foreach($produits as $produit)
                                <tr>
                                    <td width="90">
                                        <a href="#" id="{{$produit->pdt_num}}" class="selectedit">{{$produit->pdt_lib}}</a>
                                    </td>
                                    <td width="6" style="text-align: right">{{getPrice3($produit->pdt_pv)}}</td>
                                    <td width="5" style="text-align: right">{{getPrice3($produit->pdt_mag)}}</td>
                                    <td width="5" style="text-align: right">{{getPrice3($produit->pdt_dep)}}</td>
                                </tr>
                            @endforeach
                            <?php $count++;?>
                            </tbody>
                        </table>
                    </div>

                    <!--Ajouter un client -->
                    <div id="clientModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">{{__('messages.Ajouter un nouveau produit')}}</h4>
                                </div>
                                <div class="modal-body">
                                    <form method="post" id="clt_form" class="form-horizontal">
                                        @csrf

                                        <div class="form-group">
                                            <label class="control-label col-md-12" id="pdt_lib">{{__('messages.Denomination')}} </label>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-md-12" >{{__('messages.Quantite recue')}} </label>
                                            <div class="col-md-12">
                                                <input type="text" name="qte" id="qte" class="form-control" required="required"/>
                                            </div>
                                        </div>
                                        <div class="form-group" align="center">
                                            <input type="hidden" name="action" id="action" />
                                            <input type="hidden" name="hidden_id" id="hidden_id" />
                                            <input type="hidden" name="hidden_ad_num" id="hidden_ad_num" />
                                            <input type="submit" name="action_button" id="action_button" class="btn btn-success" value="{{__('messages.Ajouter')}}" />
                                            <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Annuler')}}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Supprimer le client -->
                    <main id="confirmModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="modal-title">{{__('messages.Confirmation')}}</h2>
                                </div>
                                <div class="modal-body">
                                    <h5 align="center" style="margin:0;">{{__('messages.Retirer ce produit')}}</h5>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" name="ok_button" id="ok_button" class="btn btn-danger">{{__('messages.Oui')}}</button>
                                    <button type="button" class="btn btn-primary" data-dismiss="modal">{{__('messages.Annuler')}}</button>
                                </div>
                            </div>
                        </div>
                    </main>

                </div>
            </div>
        </div>
    </main>
@endsection

@section('extra-js')
    <script>
        $(document).ready(function(){

            $('#liste_produit').DataTable({
                language: {
                    searchS: "Recherche produit"
                }
            });
            $("div.dataTables_filter input").focus();

            $('#clt_form').on('submit', function(event){
                event.preventDefault();
                if($('#action').val() == 'Ajouter')
                {
                    $.ajax({
                        url:"{{ route('approdep.add') }}",
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
                            if(data.success)
                            {
                                html = '<div class="alert alert-success">' + data.success + '</div>';
                                $('#clt_form')[0].reset();
                                $('#clientModal').modal('hide');
                                window.location.reload();
                            }
                            $('#form_result').html(html);
                        }
                    })
                }
                window.location.reload();
                //$('#pdt_selected').DataTable().ajax.reload();
            });

            $(document).on('click', '.selectedit', function(){
                var id = $(this).attr('id');
                var ad_num = document.getElementById('ad_num').value;
                console.log(id,ad_num)
                document.getElementById('ad_num').value = ad_num;
                $('#form_result').html('');
                $.ajax({
                    url:"edit/selectedit/"+id,
                    dataType:"json",
                    success:function(html){
                        $('#qte').val(html.data.qte);
                        $('#pdt_lib').html('PRODUIT : '+html.data.pdt_lib+' / Prix Achat : '+html.data.pdt_pa);
                        $('#hidden_id').val(html.data.pdt_num);
                        $('#hidden_ad_num').val(ad_num);
                        $('.modal-title').text("Saisir la quantiter a virer");
                        $('#action_button').val("Ajouter");
                        $('#action').val("Ajouter");
                        $(this).find('#qte').focus();
                        $('#clientModal').modal('show');
                    }
                })
            });

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');
                $('#form_result').html('');
                console.log('Editer')
                $.ajax({
                    url:"approdep.select/"+id,
                    dataType:"json",
                    success:function(html){
                        $('#pdt_pu').val(html.data.pdt_pu);
                        $('#qtec').val(html.data.qtec);
                        $('#pdt_lib').html('PRODUIT : '+html.data.pdt_lib);
                        $('#hidden_id').val(html.data.pdt_num);
                        $('.modal-title').text("Modifier la quantiter a commander");
                        $('#action_button').val("Modifier");
                        $('#action').val("Modifier");
                        $('#clientModal').modal('show');
                    }
                })
            });


            $(document).on('click', '.delete', function(){
                clt_num = $(this).attr('id');
                $('.modal-title').text("Confirmation");
                $('#ok_button').text('Oui');
                $('#confirmModal').modal('show');
            });

            $('#ok_button').click(function(){
                $.ajax({
                    url:"approdep/destroy/"+clt_num,
                    beforeSend:function(){
                        $('#ok_button').text('Suppression...');
                    },
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_client').DataTable().ajax.reload();
                        }, 1000);
                    }
                })
            });

        });
    </script>
@endsection
