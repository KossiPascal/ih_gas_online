@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')


@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;margin-bottom: 15px;">
        <div class="col-md-12">
            <div class="col-md-4 float-left">
                <h4 class="ml-5">{{__('messages.ETAT DES VENTES')}}</h4>
            </div>

            <div class="col-md-4 float-right">
                <a href="{{route('vente.etatassurance')}}" class="btn btn-success">{{__('messages.ETAT DES ASSURANCES')}}</a>
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
                <button type="button" name="refresh" class="btn btn-danger details">{{__('messages.Details des recus')}}</button>
            </div>
        </div>
        <div class="col-md-12" style="margin-bottom: 30px">
            <div class="col-md-6 float-left">
                <div class="contour_div">
                    <div class="contour_table">
                        <table class="table table-striped table-bordered contour_table" id="user_selected">

                        </table>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 float-right">
                <div class="info-box">
                    <div class="table-responsive div_style">
                        <span class="form_result" id="add_result"></span>
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
                                        <a href="#" id="{{$user->id}}" class="adduser">{{$user->name}}</a>
                                    </td>
                                </tr>
                            @endforeach
                            <?php $count++;?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <p></p>
            <p></p>
        </div>
    </main>
@endsection

@section('extra-js')
    <script>
        $(document).ready(function(){
            $('#liste_produit').DataTable({
                language: {
                    searchS: "{{__('messages.Recherche produit')}}"
                }
            });
            $("div.dataTables_filter input").focus();
            $('.input-daterange').datepicker({
                todayBtn:'linked',
                format:'yyyy-mm-dd',
                autoclose:true
            });

            $('#user_selected').load('vente.user_selected');
            $('#add_result').html('');

            $(document).on('click', '.adduser', function(){
                id = $(this).attr('id');
                $.ajax({
                    url:"vente.adduser/"+id,
                    success:function(data)
                    {
                        var html = '';
                        if(data.error)
                        {
                            html = '<div class="alert alert-warning">' + data.error + '</div>';
                        }
                        if(data.success)
                        {
                            html = '<div class="alert alert-success">' + data.success + '</div>';
                        }
                        $('#add_result').html(html);
                        $('#user_selected').load('vente.user_selected');
                        $("div.dataTables_filter input").val('');
                        $("div.dataTables_filter input").focus();
                    }
                })
            });

            $(document).on('click', '.delete', function(){
                id = $(this).attr('id');
                $.ajax({
                    url:"vente.deleteuser/"+id,
                    success:function(data)
                    {
                        var html = '';
                        html = '<div class="alert alert-danger">' + data + '</div>';
                        $('#add_result').html(html);
                        $('#user_selected').load('vente.user_selected');
                        $("div.dataTables_filter input").val('');
                        $("div.dataTables_filter input").focus();
                    }
                })
            });

            $(document).on('click', '.imprimer', function(){
                var debut = document.getElementById('debut').value;
                var fin = document.getElementById('fin').value;

                var newWin = window.open();
                var the_url = "vente.printetatcaisse/"+debut+"/"+fin;
                $.ajax({
                    type: "GET", url: the_url, data: {},
                    success: function(data){
                        newWin.document.write(data.data);
                    }
                    ,error: function() {
                    }
                });
            });

            $(document).on('click', '.details', function(){
                var debut = document.getElementById('debut').value;
                var fin = document.getElementById('fin').value;

                console.log(debut, fin);
                var newWin = window.open();
                var the_url = "vente.printdetailscaisse/"+debut+"/"+fin;
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
