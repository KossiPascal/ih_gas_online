@extends('layouts.caisselayout')
@section('title','PCSOFT V4: BON DE COMMANDE')

@section('extra-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">
        <div class="col-md-12">
            <div class="col-md-6 float-left">
                <h3 class="ml-5">{{__('messages.FICHE DE PRESCRIPTION')}}</h3>
            </div>

            <div class="col-md-6 float-right">
                <a href="{{route('asc.histo')}}" class="btn btn-warning"><i class="fa fa-list"></i> {{__('messages.HISTORIQUE DES PRESCRIPTIONS')}}</a>
            </div>
        </div>
        <span class="annuler_result" id="annuler_result"></span>
        <div class="col-md-12 float-left">
            <div class="col-md-4 float-left">
                <a>{{__('messages.PRODUITS SELECTIONNES')}}</a>
            </div>
            <div class="col-md-4 float-left">
                <a>Stock: {{$magasin->mag_lib}}</a>
                <a href="{{route('asc.select_mag')}}" class="btn btn-success">{{__('messages.Changer')}}</a>
            </div>
            <div class="col-md-4 float-right">
                <a> {{__('messages.LISTE DES PRODUITS')}}</a>
            </div>
        </div>

        <div class="col-md-12 float-left">
            <div class="col-md-7 float-left">
                <table class="table table-striped table-bordered" id="pdt_selected">
                    <thead>
                    <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                        <td class="description">{{__('messages.Produit')}}</td>
                        <td class="price">{{__('messages.Prix')}}</td>
                        <td class="quantity">{{__('messages.Qte')}}</td>
                        <td class="total">{{__('messages.Total')}}</td>
                        <td></td>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
                <div class="info-box">
                    @include('vente/form')
                </div>
            </div>

            <div class="col-md-5 float-right">
                <div class="form-group">
                    <label>{{__('messages.Selectionner assurance')}}</label>
                    <select name="mutnum" id="mutnum" class="form-control" onchange="rechmut()">
                        @foreach($mutuelles as $key=>$mutuelle)
                            <option value= "{!! $mutuelle !!}"> {!! $mutuelle !!} </option>
                        @endforeach
                    </select>
                </div>
                <div class="table-responsive">
                    <table id="liste_produit" class="display table table-striped table-bordered data-table">
                        <thead>
                        <tr>
                            <th>{{__('messages.Libelle')}}</th>
                            <th>{{__('messages.PU')}}</th>
                            <th>{{__('messages.Qte')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $count =1;?>
                        @foreach($produits as $produit)
                            <tr>
                                <td width="90">
                                    <a href="#" id="{{$produit->pdt_num}}" class="select">{{$produit->pdt_lib}}</a>
                                </td>
                                <td width="6" style="text-align: right">{{($produit->pv)}}</td>
                                <td width="5" style="text-align: right">{{($produit->qte)}}</td>
                            </tr>
                        @endforeach
                        <?php $count++;?>
                        </tbody>
                    </table>
                </div>

                <!--Ajouter un client -->
                <div id="addModal" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">{{__('messages.Ajouter un produit acte')}}</h4>
                            </div>
                            <div class="modal-body">
                                <span class="form_result" id="form_result"></span>
                                <form method="post" id="addForm" class="form-horizontal">
                                    @csrf
                                    <table>
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <label class="control-label col-md-12">{{__('messages.Denomination')}}: </label>
                                                    <input type="text" name="pdt_lib" id="pdt_lib" class="form-control" readonly/>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <label class="control-label col-md-12" >{{__('messages.Prix Unitaire')}} </label>
                                                    <input type="text" name="pu" id="pu" class="form-control" required="required"/>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <label class="control-label col-md-12" >{{__('messages.Base assurance')}} </label>
                                                    <input type="text" name="base" id="base" class="form-control" required="required"/>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <label class="control-label col-md-12" >{{__('messages.Marge')}} </label>
                                                    <input type="text" name="marge" id="marge" class="form-control" value="0" readonly/>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <label class="control-label col-md-12" >{{__('messages.Taux de prise en charge')}} </label>
                                                    <input type="text" name="taux" id="taux" class="form-control" required="required" onchange="verifTaux()"/>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <label class="control-label col-md-12" >{{__('messages.Quantite a vendre')}} </label>
                                                    <input type="text" name="qte" id="qte" class="form-control" required="required" onchange="verifQte()"/>
                                                    <input type="hidden" name="ini" id="ini" class="form-control" required="required"/>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>

                                    <div class="form-group" align="center">
                                        <input type="hidden" name="pdt_num" id="pdt_num" />
                                        <input type="hidden" name="cat_num" id="cat_num" />
                                        <input type="hidden" name="hidden_idcon" id="hidden_idcon" />
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

                <!--Annuler la vente -->
                <main id="annulerModal" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="modal-title">{{__('messages.Confirmation')}}</h2>
                            </div>
                            <div class="modal-body">
                                <h5 align="center" style="margin:0;">{{__('messages.annuler cette vente')}}</h5>
                            </div>
                            <div class="modal-footer">
                                <button type="button" name="okbutton" id="okbutton" class="btn btn-danger">{{__('messages.Oui')}}</button>
                                <button type="button" class="btn btn-primary" data-dismiss="modal">{{__('messages.Annuler')}}</button>
                            </div>
                        </div>
                    </div>
                </main>

                <!--Produit assurance-->
                <div id="listePdtAssur" class="modal fade" role="dialog" style="width: auto">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-body">
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <table id="pdt_assur" class="table table-responsive table-striped table-hover table-bordered">

                                        </table>
                                    </div>
                                </div>

                                <div class="form-group" align="center">
                                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Quitter')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('extra-js')
    <script>
        function mutuelle() {
            $.ajax({
                url:"asc.mutuelles",
                dataType:"json",
                success:function(data)
                {
                    $('#mutnum').empty();
                    $('#mutnum').append('<option id="11"  value="11">NEANT</option>');
                    for (var i = 0; i < data.length; i++) {
                        $('#mutnum').append('<option id=' + data[i].mut_num + ' value=' + data[i].mut_num + '>' + data[i].mut_lib +'</option>');
                    }
                    $('#mutnum').change();
                    rechmut();
                }
            })
        }

        function rechmont() {
            ven_num = document.getElementById("ven_num").value;
            $.ajax({
                url:"asc.rech_mont/"+ven_num,
                success:function(data)
                {
                    //console.log(data.mont);
                    document.getElementById('ven_mont').value = data.mont;
                    document.getElementById('ven_pec').value = data.pec;
                    document.getElementById('ven_net').value = data.net;
                    document.getElementById('ven_rem').value = data.net;
                    $("div.dataTables_filter input").focus();
                }
            });
        }

        function rech_code() {
            ven_date = document.getElementById("ven_date").value;
            $.ajax({
                url:"asc.rech_code/"+ven_date,
                success:function(data)
                {
                    document.getElementById('ven_num').value = data.ven_num;

                    ven_num = document.getElementById("ven_num").value;
                    $('#pdt_selected').load('asc.rech_pdtcon/'+ven_num);
                    $("div.dataTables_filter input").focus();
                }
            });
            rechmont();
        }

        function actualiser() {
            ven_num = document.getElementById("ven_num").value;
            $('#pdt_selected').load('asc.rech_pdtcon/'+ven_num);
            var mut_num = document.getElementById('mutnum').value;
            document.getElementById('mut_num').value = mut_num;
            rechmont();
            rech_code();
        }

        function rechmut() {
            mut_num = document.getElementById("mutnum").value;
            if (mut_num==null){
                document.getElementById('mut_taux').value = 0;
            }else {
                $.ajax({
                    url:"asc.rechtaux/"+mut_num,
                    dataType:"json",
                    success:function(data){
                        document.getElementById('mut_taux').value = data;
                        document.getElementById('mut_num').value = mut_num;
                    }
                })
            }
        }

        function verifTaux(){
            taux = document.getElementById('taux').value;
            if (taux<0){
                document.getElementById('taux').value = 0;
            }

            if (taux>100){
                document.getElementById('taux').value = 100;
            }
        }

        function verifQte(){
            qte = document.getElementById('qte').value;
            if (qte<0){
                document.getElementById('qte').value = 0;
            }

            if (qte>500){
                document.getElementById('qte').value = 100;
            }
        }

        $(document).ready(function(){
            $('#liste_produit').DataTable({
                language: {
                    searchS: "Recherche produit"
                }
            });
            $("div.dataTables_filter input").focus();
            actualiser();
            rechmont();
            mutuelle();

            $('#addForm').on('submit', function(event){
                event.preventDefault();
                var ven_num = document.getElementById('ven_num').value;
                $.ajax({
                    url:"{{ route('asc.add') }}",
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
                            rechmont();
                            html = '<div class="alert alert-success">' + data.success + '</div>';
                            $('#addForm')[0].reset();

                            $('#addModal').modal('hide');
                            $('#pdt_selected').load('asc.rech_pdtcon/'+ven_num);
                            $("div.dataTables_filter input").val('');
                            $("div.dataTables_filter input").focus();
                        }
                        $('#form_result').html(html);
                    }
                })
            });

            $(document).on('click', '.select', function(){
                var id = $(this).attr('id');
                var ven_num = document.getElementById('ven_num').value;
                var mut_num = document.getElementById('mutnum').value;
                document.getElementById('mut_num').value = mut_num;
                var mut_taux = document.getElementById('mut_taux').value;
                document.getElementById('ven_num').value = ven_num;
                $('#form_result').html('');
                $.ajax({
                    url:"asc.select/"+id,
                    dataType:"json",
                    success:function(data){
                        $('#pdt_lib').val(data.pdt_lib);
                        $('#pu').val(data.pv);
                        $('#base').val(data.pv);
                        $('#taux').val(mut_taux);
                        $('#ini').val(data.qte);
                        $('#pdt_num').val(data.pdt_num);
                        $('#cat_num').val(data.cat_num);
                        $('#hidden_idcon').val('');
                        $('#hidden_ven_num').val(ven_num);
                        $('#hidden_mut_num').val(mut_num);
                        $('#action_button').val("Ajouter");
                        $('#addModal').modal('show');
                        setTimeout(function() {$('#qte').focus();}, 200);
                    }
                })

                /*if (mut_num==11){
                    $.ajax({
                        url:"asc.select/"+id,
                        dataType:"json",
                        success:function(data){
                            $('#pdt_lib').val(data.pdt_lib);
                            $('#pu').val(data.pv);
                            $('#base').val(data.pv);
                            $('#taux').val(mut_taux);
                            $('#ini').val(data.qte);
                            $('#pdt_num').val(data.pdt_num);
                            $('#cat_num').val(data.cat_num);
                            $('#hidden_idcon').val('');
                            $('#hidden_ven_num').val(ven_num);
                            $('#hidden_mut_num').val(mut_num);
                            $('#action_button').val("Ajouter");
                            $('#addModal').modal('show');
                            setTimeout(function() {$('#qte').focus();}, 200);
                        }
                    })
                }else {
                    console.log('ASSURRANCE '+id+' - '+mut_num)
                    $.ajax({
                        url:"asc.rechPdtMut/"+id+"/"+mut_num,
                        dataType:"json",
                        success:function(data){
                            console.log(data)
                            if (data=='Vide'){
                                $.ajax({
                                    url:"asc.select/"+id,
                                    dataType:"json",
                                    success:function(data){
                                        $('#pdt_lib').val(data.pdt_lib);
                                        $('#pu').val(data.pv);
                                        $('#base').val(data.pv);
                                        $('#taux').val(mut_taux);
                                        $('#ini').val(data.qte);
                                        $('#pdt_num').val(data.pdt_num);
                                        $('#cat_num').val(data.cat_num);
                                        $('#hidden_idcon').val('');
                                        $('#hidden_ven_num').val(ven_num);
                                        $('#hidden_mut_num').val(mut_num);
                                        $('#action_button').val("Ajouter");
                                        $('#addModal').modal('show');
                                        setTimeout(function() {$('#qte').focus();}, 200);
                                    }
                                })
                            }else{
                                //var id = $(this).attr('id');
                                $('#pdt_assur').load(data);
                                $('#listPdtAssur').modal('show');
                            }
                        }
                    })
                }*/
            });

            $(document).on('click', '.edit', function(){
                var id = $(this).attr('id');
                var ven_num = document.getElementById('ven_num').value;
                var mut_num = document.getElementById('mutnum').value;
                document.getElementById('mut_num').value = mut_num;
                var mut_taux = document.getElementById('mut_taux').value;
                document.getElementById('ven_num').value = ven_num;
                $('#form_result').html('');
                $.ajax({
                    url:"asc.select_edit/"+id,
                    dataType:"json",
                    success:function(data){
                        $('#pdt_lib').val(data.pdt_lib);
                        $('#pu').val(data.pu);
                        $('#base').val(data.base);
                        $('#taux').val(mut_taux);
                        $('#ini').val(data.ini);
                        $('#qte').val(data.qte);
                        $('#pdt_num').val(data.pdt_num);
                        $('#cat_num').val(data.cat_num);
                        $('#hidden_idcon').val(id);
                        $('#hidden_ven_num').val(ven_num);
                        $('#hidden_mut_num').val(mut_num);
                        $('#action_button').val("Ajouter");
                        $('#addModal').modal('show');
                        setTimeout(function() {$('#qte').focus();}, 200);
                    }
                })
            });

            $(document).on('click', '.select_mt', function(){
                var id = $(this).attr('id');
                var ven_num = document.getElementById('ven_num').value;
                var mut_num = document.getElementById('mutnum').value;
                document.getElementById('mut_num').value = mut_num;
                var mut_taux = document.getElementById('mut_taux').value;
                var pdt_num = document.getElementById('pdt_num').value;
                document.getElementById('ven_num').value = ven_num;
                $('#form_result').html('');

                $.ajax({
                    url:"asc.select_mut/"+id+"/"+pdt_num,
                    dataType:"json",
                    success:function(data){
                        $('#pdt_lib').val(data.pdt_lib);
                        $('#pu').val(data.pv);
                        $('#base').val(data.base);
                        $('#taux').val(data.taux);
                        $('#ini').val(data.qte);
                        $('#pdt_num').val(data.pdt_num);
                        $('#cat_num').val(data.cat_num);
                        $('#hidden_idcon').val('');
                        $('#hidden_ven_num').val(ven_num);
                        $('#hidden_mut_num').val(mut_num);
                        $('#action_button').val("Ajouter");
                        $('#addModal').modal('show');
                        setTimeout(function() {$('#qte').focus();}, 200);
                    }
                })
            });



            pu.onchange=function () {
                pu = document.getElementById('pu').value;
                base = document.getElementById('base').value;
                document.getElementById('marge').value=pu-base;
            }

            base.onchange=function () {
                pu = document.getElementById('pu').value;
                base = document.getElementById('base').value;
                document.getElementById('marge').value=pu-base;
            }


            document.getElementById('ven_rem').onchange = function () {
                var ven_net = document.getElementById('ven_net').value;
                var ven_rem = document.getElementById('ven_rem').value;
                document.getElementById('ven_rel').value = ven_rem-ven_net;
            };

            $(document).on('click', '.delete', function(){
                var id = $(this).attr('id');
                pdt_num = $(this).attr('id');
                ven_num = document.getElementById('ven_num').value;
                rechmont();
                $.ajax({
                    url:"asc.delete/"+id,
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_client').DataTable().ajax.reload();
                        }, 100);
                        $('#pdt_selected').load('asc.rech_pdtcon/'+ven_num);
                        rechmont()
                        $("div.dataTables_filter input").focus();
                    }
                })
            });

            $('#annuler').click(function(){
                $('.modal-title').text("Confirmation");
                $('#okbutton').text('Oui');
                $('#annulerModal').modal('show');
            });

            var ven_num = document.getElementById('ven_num').value;
            $('#okbutton').click(function(){
                $.ajax({
                    url:"asc.annuler/"+ven_num,
                    beforeSend:function(){
                        $('#ok_button').text('Traitement...');
                    },
                    success:function(data)
                    {
                        if(data.error)
                        {
                            html = '<div class="alert alert-danger">' + data.error + '</div>';
                        }
                        if(data.success)
                        {
                            html = '<div class="alert alert-success">' + data.success + '</div>';
                        }
                        setTimeout(function(){
                            actualiser();
                            mutuelle();
                            $('#annulerModal').modal('hide');
                        }, 500);
                        //$('#annuler_result').html(html);
                    }
                })
            });

        });
    </script>
@endsection
