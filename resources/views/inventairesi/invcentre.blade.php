@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')


@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <div class="col-12 col-sm-12 col-md-12">
            <h5 class="ml-5">{{__('messages.Inventaire par centre')}}</h5>
            <div class="col-12 col-md-4 float-left">
                <a href="{{route('invsi.invglobal')}}" class="btn btn-success"><i class="fa fa-globe"></i> {{__('messages.Inventaire Global')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('invsi.invcentre')}}" class="btn btn-primary"><i class="fa fa-database"></i>{{__('messages.Inventaire par centre')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-right">
                <a href="{{route('invsi.invproduit')}}" class="btn btn-warning"><i class="fa fa-info"></i>{{__('messages.Fiche de Stock')}}</a>
            </div>
        </div>
        <br>

        <div class="info-box mb-1">
            <div class="row input-daterange">
                <div class="col-12 col-md-2">
                    <input type="text" name="from_date" id="from_date" class="form-control" placeholder="{{__('messages.Date Debut')}}" readonly />
                </div>
                <div class="col-12 col-md-2">
                    <input type="text" name="to_date" id="to_date" class="form-control" placeholder="{{__('messages.Date Fin')}}" readonly />
                </div>
                <div class="col-md-3 float-left">
                    <select name="centre_id" id="centre_id" class="form-control" onchange="actualiser()">
                        @foreach($centres as $key=>$centre)
                            <option value= "{!! $centre !!}"> {!! $centre !!} </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-5">
                    <button type="button" name="filter" id="filter" class="btn btn-danger">{{__('messages.Rechercher')}}</button>
                    <button type="button" name="imprimer" id="imprimer" class="btn btn-primary">{{__('messages.Imprimer')}}</button>
                </div>

            </div>
            <!-- /.info-box-content -->
        </div>

        <div class="col-md-12">
            <div class="info-box">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="pdt_mag">
                        <thead>
                        <tr class="cart_menu" >
                            <td class="price">{{__('messages.Produit')}}</td>
                            <td class="price">{{__('messages.Initial')}}</td>
                            <td class="price">{{__('messages.Achat')}}</td>
                            <td class="price">{{__('messages.Sortie')}}</td>
                            <td class="total">{{__('messages.Solde')}}</td>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

    </main>
@endsection

@section('extra-js')
    <script>
        function centre() {
            $.ajax({
                url:"invsi.centres",
                dataType:"json",
                success:function(data)
                {
                    $('#centre_id').empty();
                    $('#centre_id').append('<option id="0"  value="0">- {{__('messages.Choisir un centre')}} -</option>');
                    for (var i = 0; i < data.length; i++) {
                        $('#centre_id').append('<option id=' + data[i].centre_id + ' value=' + data[i].centre_id + '>'+ data[i].nom_centre +'</option>');
                    }
                    $('#centre_id').change();
                }
            })
        }

        function actualiser() {
            centre_id = document.getElementById("centre_id").value;
        }

        $(document).ready(function(){
            $('.input-daterange').datepicker({
                todayBtn:'linked',
                format:'yyyy-mm-dd',
                autoclose:true
            });
            centre()

            load_data();

            function load_data(from_date = '', to_date = '', centre_id=0) {
                $('#pdt_mag').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url:'{{ route("invsi.invcentre") }}',
                        data:{from_date:from_date, to_date:to_date, centre_id:centre_id}
                    },
                    columns: [
                        {
                            data:'pdt_lib',
                            name:'pdt_lib'
                        },
                        {
                            data:'pdt_ini',
                            name:'pdt_ini'
                        },
                        {
                            data:'pdt_ent',
                            name:'pdt_ent'
                        },
                        {
                            data:'pdt_sor',
                            name:'pdt_sor'
                        },
                        {
                            data:'pdt_act',
                            name:'pdt_act'
                        }

                    ]
                });
            }

            $('#filter').click(function(){
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                var centre_id = $('#centre_id').val();

                if(from_date != '' && to_date != '')
                {
                    $('#pdt_mag').DataTable().destroy();
                    load_data(from_date, to_date,centre_id);
                }
                else
                {
                    alert('{{__('messages.Selectionner la periode')}}');
                }
            });

            $('#reset').click(function(){
                $('#from_date').val('');
                $('#to_date').val('');
                $('#inventaires').DataTable().destroy();
                load_data();
            });

            $('#imprimer').click(function(){
                var debut = document.getElementById('from_date').value;
                var fin = document.getElementById('to_date').value;
                var centre_id = document.getElementById('centre_id').value;

                var newWin = window.open();
                var the_url = "invsi.print_invcentre/"+debut+"/"+fin+"/"+centre_id;
                $.ajax({
                    type: "GET", url: the_url, data: {},
                    success: function(data){
                        newWin.document.write(data);;
                    }
                    ,error: function() {
                    }
                });
            });

            $(document).on('click', '.details', function(){
                var id = $(this).attr('id');
                var debut = document.getElementById('from_date').value;
                var fin = document.getElementById('to_date').value;

                var newWin = window.open();
                var the_url = "invsi.details/"+debut+"/"+fin+"/"+id;
                $.ajax({
                    type: "GET", url: the_url, data: {},
                    success: function(data){
                        //newWin.document.write(data);;
                    }
                    ,error: function() {
                    }
                });
            });
        });
    </script>
@endsection
