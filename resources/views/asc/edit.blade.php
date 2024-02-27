@extends('layouts.caisselayout')
@section('title','PCSOFT V4: BON DE COMMANDE')

@section('extra-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">
        <div class="col-md-12"><h3 class="ml-5" style="text-align:center">{{__('messages.MODIFIER UNE VENTE')}}</h3></div>
        <div class="col-md-12">
            <div class="col-md-4 float-left">
                <h3 class="ml-5"></h3>
            </div>

            <div class="col-md-4 float-right" style="text-align: right">
                <a href="{{route('vente.histo')}}" class="btn btn-warning"><i class="fa fa-list"></i> {{__('messages.HISTORIQUE DES VENTES')}}</a>
            </div>

            <div class="col-md-4 float-right" style="text-align: right">
                <a href="{{route('vente.etat')}}" class="btn btn-success"><i class="fa fa-list"></i>{{__('messages.ETAT FINANCIER')}}</a>
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
                                <td class="quantity">{{(__'messages.Qte')}}</td>
                                <td class="total">{{__('messages.Total')}}</td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                    <div class="info-box">
                        @include('vente/form')
                    </div>
                </div>

            </div>

            <div class="col-md-6 float-right">
                <div class="info-box">
                    <div class="table-responsive div_style">
                        <table id="liste_produit" class="display table table-striped table-bordered data-table">
                            <thead>
                            <tr>
                                <th>{{__('message.Libelle')}}</th>
                                <th>{{__('messages.PU')}}</th>
                                <th>{{__('messages.Qte')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $count =1;?>
                            @foreach($produits as $produit)
                                <tr>
                                    <td width="90">
                                        <a href="#" id="{{$produit->pdt_num}}" class="selection">{{$produit->pdt_lib}}</a>
                                    </td>
                                    <td width="6" style="text-align: right">{{getPrice3($produit->pdt_pv)}}</td>
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
                                            <label class="control-label col-md-12" >{{__('messages.Prix Unitaire')}} </label>
                                            <div class="col-md-12">
                                                <input type="text" name="pu" id="pu" class="form-control" required="required" onchange="rechpu(this.id)"/>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-12" >{{__('message.Base assurance')}} </label>
                                            <div class="col-md-12">
                                                <input type="text" name="base" id="base" class="form-control" required="required" onchange="rechbase()"/>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-12" >{{__('messages.Marge')}} </label>
                                            <div class="col-md-12">
                                                <input type="text" name="marge" id="marge" class="form-control" value="0" readonly/>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-12" >{{__('messages.Taux de prise en charge')}} </label>
                                            <div class="col-md-12">
                                                <input type="text" name="taux" id="taux" class="form-control" required="required"/>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-md-12" >{{__('messages.Quantite a vendre')}} </label>
                                            <div class="col-md-12">
                                                <input type="text" name="qte" id="qte" class="form-control" value="1" required="required"/>
                                            </div>
                                        </div>
                                        <div class="form-group" align="center">
                                            <input type="hidden" name="action" id="action" />
                                            <input type="hidden" name="hidden_id" id="hidden_id" />
                                            <input type="hidden" name="hidden_ven_num" id="hidden_ven_num" />
                                            <input type="hidden" name="hidden_mut_num" id="hidden_mut_num" />
                                            <input type="submit" name="action_button" id="action_button" class="btn btn-success" value="{{__('messages.Ajouter')}}" />
                                            <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Annuler')}}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--Supprimer le client
                    <main id="confirmModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="modal-title">Confirmation</h2>
                                </div>
                                <div class="modal-body">
                                    <h5 align="center" style="margin:0;">Etes vou sure de Retirer ce produit ?</h5>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" name="ok_button" id="ok_button" class="btn btn-danger">Oui</button>
                                    <button type="button" class="btn btn-primary" data-dismiss="modal">Annuler</button>
                                </div>
                            </div>
                        </div>
                    </main> -->

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
            actualiser();
            rechmont();

            $('#clt_form').on('submit', function(event){
                event.preventDefault();
                var ven_num = document.getElementById('ven_num').value;
                if($('#action').val() == 'Ajouter')
                {
                    $.ajax({
                        url:"{{ route('vente.add') }}",
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
                                rechmont();
                                html = '<div class="alert alert-success">' + data.success + '</div>';
                                $('#clt_form')[0].reset();
                                $('#clientModal').modal('hide');
                                $('#pdt_selected').load('vente.rech_pdtcon/'+ven_num);
                                $("div.dataTables_filter input").val('');
                                $("div.dataTables_filter input").focus();
                            }
                            $('#form_result').html(html);

                        }
                        //window.location.reload();
                    })
                    rechmont();
                }
                //window.location.reload();
                //$('#pdt_selected').DataTable().ajax.reload();
            });

            $(document).on('click', '.selection', function(){
                var id = $(this).attr('id');
                var ven_num = document.getElementById('ven_num').value;
                var mut_num = document.getElementById('mut_num').value;
                var mut_taux = document.getElementById('mut_taux').value;
                document.getElementById('ven_num').value = ven_num;
                $('#form_result').html('');
                $.ajax({
                    url:"vente.select/"+id,
                    dataType:"json",
                    success:function(html){
                        $('#pdt_lib').html('PRODUIT : '+html.data.pdt_lib);
                        $('#pu').val(html.data.pdt_pv);
                        $('#base').val(html.data.pdt_pv);
                        $('#taux').val(mut_taux);
                        $('#hidden_id').val(html.data.pdt_num);
                        $('#hidden_ven_num').val(ven_num);
                        $('#hidden_mut_num').val(mut_num);
                        $('.modal-title').text("Saisir la quantiter a vendre");
                        $('#action_button').val("Ajouter");
                        $('#action').val("Ajouter");
                        $('#clientModal').modal('show');
                        setTimeout(function() {$('#qte').focus();}, 200);
                    }
                })
            });



            pu.onchange=function () {
                document.getElementById('marge').value=document.getElementById('pu').value-document.getElementById('base').value;
            }

            base.onchange=function () {
                document.getElementById('marge').value=document.getElementById('pu').value-document.getElementById('base').value;
            }


            document.getElementById('ven_rem').onchange = function () {
                var ven_net = document.getElementById('ven_net').value;
                var ven_rem = document.getElementById('ven_rem').value;
                document.getElementById('ven_rel').value = ven_rem-ven_net;
            };

            $(document).on('click', '.delete', function(){
                pdt_num = $(this).attr('id');
                ven_num = document.getElementById('ven_num').value;
                rechmont();
                $.ajax({
                    url:"vente.delete/"+ven_num+"/"+pdt_num,
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_client').DataTable().ajax.reload();
                        }, 100);
                        $('#pdt_selected').load('vente.rech_pdtcon/'+ven_num);
                        $('#ven_mont').load('vente.rech_mont/'+ven_num);
                        $("div.dataTables_filter input").focus();
                    }
                })
            });

            /*$('#ok_button').click(function(){
             $.ajax({
             url:"vente.delete/"+ven_num+"/"+pdt_num,
             success:function(data)
             {
             setTimeout(function(){
             $('#confirmModal').modal('hide');
             $('#liste_client').DataTable().ajax.reload();
             }, 100);
             $('#pdt_selected').load('vente.rech_pdtcon/'+ven_num);
             $("div.dataTables_filter input").focus();
             }
             })
             });*/

        });
    </script>
@endsection
