@extends('layouts.adminlayout')
@section('title','PCSOFT V4: BON DE COMMANDE')

@section('extra-meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 20px">
        <div class="col-md-12">
            <div class="col-md-4 float-left">
                <h3 class="ml-5">{{__('messages.FICHE DE VENTE')}}</h3>
            </div>

            <div class="col-md-4 float-left">
                <a href="{{route('vente.histo')}}" class="btn btn-warning"><i class="fa fa-list"></i> {{__('messages.HISTORIQUE DES VENTES')}}</a>
            </div>

            <div class="col-md-4 float-right">
                <a href="{{route('vente.select_mag')}}" class="btn btn-danger"><i class="fa fa-list"></i> {{__('messages.Changer de magasin')}}</a>
            </div>
        </div>
        <span class="annuler_result" id="annuler_result"></span>
        <div class="col-md-12 float-left">
            <div class="col-md-7 float-left">
                <h5 class="ml-7">{{__('messages.PRODUITS SELECTIONNES')}}</h5>
            </div>

            <div class="col-md-5 float-right">
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
                    <select name="assuranceid" id="assuranceid" class="form-control" onchange="rechmut()">
                        @foreach($assurances as $key=>$assurance)
                            <option value= "{!! $assurance !!}"> {!! $assurance !!} </option>
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
                                    <a href="#" id="{{$produit->produit_id}}" class="select">{{$produit->libelle}}</a>
                                </td>
                                <td width="6" style="text-align: right">{{($produit->pv)}}</td>
                                <td width="5" style="text-align: right">{{($produit->qte)}}</td>
                            </tr>
                        @endforeach
                        <?php $count++;?>
                        </tbody>
                    </table>
                </div>

                <!--Ajouter un produit -->
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
                                                    <label class="control-label col-md-12">{{__('messages.Denomination')}} </label>
                                                    <input type="text" name="libelle" id="libelle" class="form-control" readonly/>
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
                                                    <input type="text" name="taux_pdt" id="taux_pdt" class="form-control" required="required" onchange="verifTaux()"/>
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
                                        <input type="hidden" name="produit_id" id="produit_id" />
                                        <input type="hidden" name="categorie_id" id="categorie_id" />
                                        <input type="hidden" name="produit_vente_id" id="produit_vente_id" />
                                        <input type="hidden" name="hidden_code" id="hidden_code" />
                                        <input type="hidden" name="hidden_assurance_id" id="hidden_assurance_id" />
                                        <input type="submit" name="action_button" id="action_button" class="btn btn-success" value="{{__('messages.Ajouter')}}" />
                                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Annuler')}}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Ajouter un patient -->
                <div id="addPatientModal" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">{{__('messages.Ajouter un patient')}}</h4>
                            </div>
                            <div class="modal-body">
                                <span class="form_result" id="form_result"></span>
                                <form method="post" id="addPatientForm" class="form-horizontal">
                                    @csrf
                                    <table width="100%">
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <label class="control-label col-md-12">{{__('messages.Code')}}: </label>
                                                    <input type="text" name="code_patient" id="code_patient" class="form-control" value="{{$code_patient}}" readonly/>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <label class="control-label col-md-12" >{{__('messages.Nom et Prenom Patient')}} </label>
                                                    <input type="text" name="nom_prenom" id="nom_prenom" class="form-control" required="required"/>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="form-group">
                                                    <label class="control-label col-md-12" >{{__('messages.Selectionner le sexe du patient')}} </label>
                                                    <select name="sexe" id="sexe" class="form-control">
                                                        <option value="Feminin">{{__('messages.Feminin')}}</option>
                                                        <option value="Masculin">{{__('messages.Masculin')}}</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-group">
                                                    <label class="control-label col-md-12" >{{__('messages.Age Patient')}}</label>
                                                    <input type="text" name="age" id="age" class="form-control" value="18"/>
                                                </div>
                                            </td>
                                        </tr>

                                    </table>

                                    <div class="form-group" align="center">
                                        <input type="hidden" name="produit_id" id="produit_id" />
                                        <input type="hidden" name="assurance_patient" id="assurance_patient" />
                                        <input type="submit" name="action_button" id="action_button" class="btn btn-success" value="{{__('messages.Ajouter')}}" />
                                        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-windows-close"></i>{{__('messages.Annuler')}}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!--Associer la vente -->
                <main id="annulerModal" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2 class="modal-title">{{__('messages.Confirmation')}}</h2>
                            </div>
                            <div class="modal-body">
                                <h5 align="center" style="margin:0;">{{__('messages.annuler vente?')}}</h5>
                            </div>
                            <div class="modal-footer">
                                <button type="button" name="okbutton" id="okbutton" class="btn btn-danger">{{__('messages.Oui')}}</button>
                                <button type="button" class="btn btn-primary" data-dismiss="modal">{{__('messages.Annuler')}}</button>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </main>
@endsection

@section('extra-js')
    <script>
        function assurance() {
            $.ajax({
                url:"vente.assurances",
                dataType:"json",
                success:function(data)
                {
                    $('#assuranceid').empty();
                    $('#assuranceid').append('<option id="1"  value="11">NEANT</option>');
                    for (var i = 0; i < data.length; i++) {
                        $('#assuranceid').append('<option id=' + data[i].assurance_id + ' value=' + data[i].assurance_id + '>' + data[i].nom +'</option>');
                    }
                    $('#assuranceid').change();
                    rechmut();
                }
            })
        }

        function rechmont() {
            code = document.getElementById("code").value;
            $.ajax({
                url:"vente.rech_mont/"+code,
                success:function(data)
                {
                    //console.log(data.mont);
                    document.getElementById('montant_total').value = data.mont;
                    document.getElementById('prise_en_charge').value = data.pec;
                    document.getElementById('net_apayer').value = data.net;
                    document.getElementById('montant_recu').value = data.net;
                    $("div.dataTables_filter input").focus();
                }
            });
        }

        function rech_code() {
            date_vente = document.getElementById("date_vente").value;
            $.ajax({
                url:"vente.rech_code/"+date_vente,
                success:function(data)
                {
                    document.getElementById('code').value = data.code;

                    code = document.getElementById("code").value;
                    $('#pdt_selected').load('vente.rech_pdtcon/'+code);
                    $("div.dataTables_filter input").focus();
                }
            });
            rechmont();
        }

        function actualiser() {
            code = document.getElementById("code").value;
            $('#pdt_selected').load('vente.rech_pdtcon/'+code);
            var assurance_id = document.getElementById('assuranceid').value;
            document.getElementById('assurance_id').value = assurance_id;
            rechmont();
            rech_code();
        }

        function rechmut() {
            assurance_id = document.getElementById("assuranceid").value;
            if (assurance_id==1){
                document.getElementById('taux').value = 0;
            }else {
                $.ajax({
                    url:"vente.rechtaux/"+assurance_id,
                    dataType:"json",
                    success:function(data){
                        document.getElementById('taux').value = data;
                        document.getElementById('assurance_id').value = assurance_id;
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

        $(document).ready(function(){
            $('#liste_produit').DataTable({
                language: {
                    searchS: "{{__('messages.Recherche produit')}}"
                }
            });
            $("div.dataTables_filter input").focus();
            actualiser();
            rechmont();
            assurance();
            rechmut();

            $('#addForm').on('submit', function(event){
                event.preventDefault();
                var code = document.getElementById('code').value;
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
                            $('#pdt_selected').load('vente.rech_pdtcon/'+code);
                            $("div.dataTables_filter input").val('');
                            $("div.dataTables_filter input").focus();
                        }
                        $('#form_result').html(html);
                    }
                })
            });

            $('#addPatientForm').on('submit', function(event){
                event.preventDefault();
                var code = document.getElementById('code').value;
                $.ajax({
                    url:"{{ route('patient.store') }}",
                    method:"POST",
                    data: new FormData(this),
                    contentType: false,
                    cache:false,
                    processData: false,
                    dataType:"json",
                    success:function(data)
                    {
                        document.getElementById('nom_prenom_patient').value = data.nom_prenom;
                        document.getElementById('patient_id').value = data.patient_id;
                        $('#addPatientModal').modal('hide');
                    }
                })
            });

            $(document).on('click', '.select', function(){
                var id = $(this).attr('id');
                var code = document.getElementById('code').value;
                var assurance_id = document.getElementById('assuranceid').value;
                document.getElementById('assurance_id').value = assurance_id;
                var taux = document.getElementById('taux').value;
                document.getElementById('code').value = code;
                console.log(taux);
                $('#form_result').html('');
                $.ajax({
                    url:"vente.select/"+id,
                    dataType:"json",
                    success:function(data){
                        $('#libelle').val(data.libelle);
                        $('#pu').val(data.pv);
                        $('#base').val(data.pv);
                        $('#taux_pdt').val(taux);
                        $('#ini').val(data.qte);
                        $('#qte').val('');
                        $('#produit_id').val(data.produit_id);
                        $('#categorie_id').val(data.categorie_id);
                        $('#produit_vente_id').val('');
                        $('#hidden_code').val(code);
                        $('#hidden_assurance_id').val(assurance_id);
                        $('#action_button').val("{{__('messages.Ajouter')}}");
                        $('#addModal').modal('show');
                        setTimeout(function() {$('#qte').focus();}, 200);
                    }
                })
            });

            $(document).on('click', '.edit', function(){
                var id = $(this).attr('id');
                var code = document.getElementById('code').value;
                var assurance_id = document.getElementById('assuranceid').value;
                document.getElementById('assurance_id').value = assurance_id;
                var taux = document.getElementById('taux').value;
                document.getElementById('code').value = code;
                $('#form_result').html('');
                $.ajax({
                    url:"vente.select_edit/"+id,
                    dataType:"json",
                    success:function(data){
                        $('#libelle').val(data.libelle);
                        $('#pu').val(data.pu);
                        $('#base').val(data.base);
                        $('#taux_pdt').val(taux_pdt);
                        $('#ini').val(data.ini);
                        $('#qte').val(data.qte);
                        $('#produit_id').val(data.produit_id);
                        $('#categorie_id').val(data.categorie_id);
                        $('#produit_vente_id').val(id);
                        $('#hidden_code').val(code);
                        $('#hidden_assurance_id').val(assurance_id);
                        $('#action_button').val("{{__('messages.Valider')}}");
                        $('#addModal').modal('show');
                        setTimeout(function() {$('#qte').focus();}, 200);
                    }
                })
            });

            $('#newPatient').click(function(){
                var assurance_patient = document.getElementById('assuranceid').value;
                $('#patient_id').val('');
                $('#nom_prenom').val('');
                $('#assurance_patient').val(assurance_patient);
                $('#addPatientModal').modal('show');
                $('#form_result').html('');
            });

            /*pu.onchange=function () {
                pu = document.getElementById('pu').value;
                base = document.getElementById('base').value;
                console.log(pu,base);
                if (base>pu){
                    document.getElementById('pu').value = base;
                    document.getElementById('marge').value=0;
                }else{
                    document.getElementById('marge').value=pu-base;
                }
            }

            base.onchange=function () {
                pu = document.getElementById('pu').value;
                base = document.getElementById('base').value;
                console.log(pu,base);
                if (base>pu){
                    document.getElementById('pu').value = base;
                    document.getElementById('marge').value=0;
                }else{
                    document.getElementById('marge').value=pu-base;
                }
            }*/

            $("#pu").on('change', function(){
                document.getElementById('marge').value=document.getElementById('pu').value-document.getElementById('base').value;
                /*var pu = document.getElementById('pu').value;
                var base = document.getElementById('base').value;
                if (base>pu){
                    document.getElementById('pu').value = base;
                    document.getElementById('marge').value=0;
                }else{
                    document.getElementById('marge').value=pu-base;
                }*/
            });

            $("#base").on('change', function(){
                document.getElementById('marge').value=document.getElementById('pu').value-document.getElementById('base').value;
            });

            $("#taux_pdt").on('change', function(){
                var taux_pdt = document.getElementById('taux_pdt').value;
                if (taux_pdt>100){
                    document.getElementById('taux_pdt').value = 100;
                }

                if (taux_pdt<0){
                    document.getElementById('taux_pdt').value = 0;
                }
            });


            document.getElementById('montant_recu').onchange = function () {
                var net_apayer = document.getElementById('net_apayer').value;
                var montant_recu = document.getElementById('montant_recu').value;
                document.getElementById('reliquat').value = montant_recu-net_apayer;
            };

            $(document).on('click', '.delete', function(){
                var id = $(this).attr('id');
                produit_id = $(this).attr('id');
                code = document.getElementById('code').value;
                rechmont();
                $.ajax({
                    url:"vente.delete/"+id,
                    success:function(data)
                    {
                        setTimeout(function(){
                            $('#confirmModal').modal('hide');
                            $('#liste_client').DataTable().ajax.reload();
                        }, 100);
                        $('#pdt_selected').load('vente.rech_pdtcon/'+code);
                        rechmont()
                        $("div.dataTables_filter input").focus();
                    }
                })
            });

            $('#annuler').click(function(){
                $('.modal-title').text("Confirmation");
                $('#okbutton').text('{{__('messages.Oui')}}');
                $('#annulerModal').modal('show');
            });

            var code = document.getElementById('code').value;
            $('#okbutton').click(function(){
                $.ajax({
                    url:"vente.annuler/"+code,
                    beforeSend:function(){
                        $('#ok_button').text('{{__('messages.Traitement')}}...');
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
                            assurance();
                            $('#annulerModal').modal('hide');
                        }, 500);
                        //$('#annuler_result').html(html);
                    }
                })
            });

        });
    </script>
@endsection
