@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')


@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <div class="col-md-12">
            <div class="col-md-4 float-left">
                <h4 class="ml-5">{{__('messages.ETAT FINANCIER')}}</h4>
            </div>

            <div class="col-md-4 float-right">
                <a href="{{route('vente.histo')}}" class="btn btn-default">{{__('messages.HISTORIQUE')}}</a>
            </div>
            <div class="col-md-4 float-right">
                <a href="{{route('vente.index')}}" class="btn btn-warning">{{__('messages.NOUVELLE VENTE')}}</a>
            </div>

        </div>

        <div class="info-box mb-1">
            <div class="row input-daterange">
                    <div class="col-12 col-md-3 float-left">
                        <input type="text" name="debut" id="debut" value="{{ date('Y-m-d') }}" class="form-control" placeholder="{{__('messages.Date Debut')}}" readonly />
                    </div>
                    <div class="col-12 col-md-3 float-left">
                        <input type="text" name="fin" id="fin" value="{{ date('Y-m-d') }}" class="form-control" placeholder="{{__('messages.Date Fin')}}" readonly />
                    </div>

                    <div class="col-12 col-md-6 float-right">
                        <button type="button" name="filter" id="filter" class="btn btn-primary">{{__('messages.Rechercher')}}</button>
                        <button type="submit" name="imprimer" id="imprimer" class="btn btn-success imprimer">{{__('messages.Imprimer')}}</button>
                        <button type="button" name="refresh" id="reset" class="btn btn-danger">{{__('messages.Actualiser')}}</button>
                    </div>
            </div>
            <!-- /.info-box-content -->
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

            $(document).on('click', '.imprimer', function(){
                var debut = document.getElementById('debut').value;
                var fin = document.getElementById('fin').value;

                var newWin = window.open();
                var the_url = "vente.print_ef/"+debut+"/"+fin;
                $.ajax({
                    type: "GET", url: the_url, data: {},
                    success: function(data){
                        newWin.document.write(data);;
                    }
                    ,error: function() {
                    }
                });
            });
        });
    </script>
@endsection
