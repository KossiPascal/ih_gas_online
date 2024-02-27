@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')


@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <div class="col-12 col-sm-12 col-md-12">
            <div class="col-12 col-sm-8 col-md-8 float-left">
                <h3>{{__('messages.HISTORIQUES DES CONFECTION DE KIT')}}</h3>
            </div>
            <div class="col-12 col-md-4 float-right">
                <a href="{{route('ck.index')}}" class="btn btn-danger"><i class="fa fa-list"></i> {{__('messages.Nouvel conception de kit')}}</a>
            </div>
        </div>

        <div class="info-box mb-1">
            <div class="row input-daterange">
                <div class="col-12 col-md-3">
                    <input type="text" name="from_date" id="from_date" class="form-control" placeholder="{{__('messages.Date Debut')}}" readonly />
                </div>
                <div class="col-12 col-md-3">
                    <input type="text" name="to_date" id="to_date" class="form-control" placeholder="{{__('messages.Date Fin')}}" readonly />
                </div>

                <div class="col-12 col-md-6">
                    <button type="button" name="filter" id="filter" class="btn btn-primary">{{__('messages.Rechercher')}}</button>
                    <button type="button" name="refresh" id="reset" class="btn btn-danger">{{__('messages.Actualiser')}}</button>
                </div>
            </div>
            <!-- /.info-box-content -->
        </div>

        <div class="col-md-12">
            <div class="info-box">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="histo_prod">
                        <thead>
                        <tr class="cart_menu" style="background-color: #00b0e8">
                            <td class="description">{{__('messages.Date')}} </td>
                            <td class="price">{{__('messages.Code')}}</td>
                            <td class="price">{{__('messages.Source')}}</td>
                            <td class="price">{{__('messages.cout')}}</td>
                            <td class="total">{{__('messages.Utilisateur')}}</td>
                            <td>{{__('messages.Editer')}}</td>
                            <td>{{__('messages.Imprimer')}}</td>
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
        $(document).ready(function(){
            $('.input-daterange').datepicker({
                todayBtn:'linked',
                format:'yyyy-mm-dd',
                autoclose:true
            });

            load_data();

            function load_data(from_date = '', to_date = '')
            {
                $('#histo_prod').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url:'{{ route("ck.histo") }}',
                        data:{from_date:from_date, to_date:to_date}
                    },
                    columns: [
                        {
                            data:'ck_date',
                            name:'ck_date'
                        },
                        {
                            data:'ck_num',
                            name:'ck_num'
                        },
                        {
                            data:'mag_lib',
                            name:'mag_lib'
                        },
                        {
                            data:'ck_mont',
                            name:'ck_mont'
                        },
                        {
                            data:'name',
                            name:'name'
                        },
                        {
                            data:'ck_num',
                            name:'ck_num',
                            render:function (data, type, row) {
                                return "<a href='ck/"+row.ck_num+"/edit' class='btn btn-success'><i class='fa fa-edit'></i></a>"}
                        },
                        {
                            data:'ck_num',
                            name:'ck_num',
                            render:function (data, type, row) {
                                return "<a href='ck/"+row.ck_num+"' class='btn btn-info'><i class='fa fa-print'></i></a>"}
                        }

                    ]
                });
            }

            $('#filter').click(function(){
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                if(from_date != '' && to_date != '')
                {
                    $('#histo_prod').DataTable().destroy();
                    load_data(from_date, to_date);
                }
                else
                {
                    alert('{{__('messages.Selectionner la periode')}}');
                }
            });

            $('#reset').click(function(){
                $('#from_date').val('');
                $('#to_date').val('');
                $('#histo_prod').DataTable().destroy();
                load_data();
            });
        });
    </script>
@endsection
