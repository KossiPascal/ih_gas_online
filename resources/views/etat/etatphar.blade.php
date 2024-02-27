@extends('layouts.pharmacielayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')


@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <div class="col-md-12">
            <div class="col-md-4 float-left">
                <h4 class="ml-5">{{__('messages.ETAT DES VENTES')}}</h4>
            </div>

            <div class="col-md-4 float-right">
                <a href="{{route('pdt.index')}}" class="btn btn-success">{{__('messages.FICHE DES PRODUITS')}}</a>
            </div>

        </div>
        <div class="row input-daterange col-md-12 info-box">
            <div class="col-12 col-md-3 float-left">
                <input type="text" name="debut" id="debut" value="{{ date('Y-m-d') }}" class="form-control" placeholder="{{__('messages.Date Debut')}}" readonly />
            </div>
            <div class="col-12 col-md-3 float-left">
                <input type="text" name="fin" id="fin" value="{{ date('Y-m-d') }}" class="form-control" placeholder="{{__('messages.Date Fin')}}" readonly />
            </div>

            <div class="col-12 col-md-6 float-right">
                <button type="button" name="filter" id="filter" class="btn btn-primary">{{__('messages.Rechercher')}}</button>
                <button type="submit" name="imprimer" id="imprimer" class="btn btn-warning imprimer">{{__('messages.Imprimer')}}</button>
                <button type="button" name="refresh" id="reset" class="btn btn-danger">{{__('messages.Actualiser')}}</button>
            </div>
        </div>
        <div class="col-md-12">
            <div class="col-12 col-md-6 float-left">
                <h3>{{__('messages.Utilisateurs selectionnes')}}</h3>
                    <div class="contour_table">
                        <table class="table table-striped table-bordered contour_table" id="user_selected">
                            <thead>
                            <tr class="cart_menu" style="background-color: rgba(202,217,52,0.48)">
                                <td class="description">{{__('messages.Nom')}}</td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($usercon as $user)
                                    <tr>
                                        <td>{{$user->name}}</td>
                                        <td><a href="{{route('vente.deleteuser',$user->id)}}" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a> </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
            </div>
            <div class="col-12 col-md-6 float-right">
                <div class="info-box">
                    <div class="table-responsive div_style">
                        <table id="liste_produit" class="display table table-striped table-bordered data-table">
                            <thead>
                            <tr>
                                <th>{{__('messages.Nom Caissier')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $count =1;?>
                            @foreach($users as $user)
                                <tr>
                                    <td width="90">
                                        <a href="{{route('vente.adduser',$user->id)}}" id="{{$user->id}}" class="select">{{$user->name}}</a>
                                    </td>
                                </tr>
                            @endforeach
                            <?php $count++;?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('extra-js')
    <script>
        $(document).ready(function(){
            $('.input-daterange').datepicker({
                todayBtn:'linked',
                format:'yyyy-mm-dd',
                autoclose:true
            });

            /*$('#user_selected').load('vente.rech_usercon');

            $(document).on('click', '.select', function(){
                var id = $(this).attr('id');
                var the_url = "vente.adduser/"+id;
                $.ajax({
                    url: the_url,
                    success: function(data){
                        console.log(data);
                        $('#user_selected').load('vente.rech_usercon');
                    },
                    error: function() {
                    }
                });

                sweetAlert('info','caissier clique');
            });*/

            $(document).on('click', '.imprimer', function(){
                var debut = document.getElementById('debut').value;
                var fin = document.getElementById('fin').value;

                console.log(debut, fin);
                var newWin = window.open();
                var the_url = "vente.printetatphar/"+debut+"/"+fin;
                $.ajax({
                    type: "GET", url: the_url, data: {},
                    success: function(data){
                        newWin.document.write(data.data);
                        window.location.reload();
                    }
                    ,error: function() {
                    }
                });
            });
        });
    </script>
@endsection
