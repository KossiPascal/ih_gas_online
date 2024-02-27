@extends('layouts.personnelplayout')
@section('title','PCSOFT V4: Gestion Produit')
@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">

        <div class="col-12 col-sm-12 col-md-12">
            <h3 class="ml-5">{{__('messages.PRODUITS, LOTS, ET DATE DE PEREMPTION')}}</h3>
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('inv.etatglobal')}}" class="btn btn-success"> {{__('messages.Etat du stock global')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('inv.magasin')}}" class="btn btn-warning">{{__('messages.Etat du stock par magasin')}}</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-right">
                <a href="#" class="btn btn-danger">{{__('messages.Controle des dates de peremption')}}</a>
            </div>
        </div>
        <br>
        <div class="info-box">
            <div class="table-responsive">
                <table id="liste_produit" class="table table-striped table-bordered data-table">
                    <thead>
                    <tr>
                        <th>{{__('messages.Lot')}}</th>
                        <th>{{__('messages.Libelle')}}</th>
                        <th>{{__('messages.Prix de vente')}}</th>
                        <th>{{__('messages.Qte reste')}}</th>
                        <th>{{__('messages.Expire')}}</th>
                        <th>{{__('messages.Actions')}}</th>
                    </tr>
                    </thead>
                </table>
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
        $(document).ready(function(){

            $('#liste_produit').DataTable({
                processing: true,
                serverSide: true,
                ajax:{
                    url: "{{ route('inv.date_per') }}",
                },
                columns:[
                    {
                        data: 'lot',
                        name: 'lot'
                    },
                    {
                        data: 'libelle',
                        name: 'libelle'
                    },
                    {
                        data: 'pv',
                        name: 'pv'
                    },
                    {
                        data: 'qte',
                        name: 'qte'
                    },
                    {
                        data: 'date_peremption',
                        name: 'date_peremption'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    }
                ],
                'rowCallback': function(row, data, index){
					if(data.date_peremption == '0'){
						$(row).find('td:eq(4)').css('background-color', 'red').css('color', 'white');
					}
            }
            });

            $(document).on('click', '.editer', function(){
                var id = $(this).attr('id');
                $('#form_result').html('');
                $.ajax({
                    url:"produit/"+id+"/edit",
                    dataType:"json",
                    success:function(html){
                        $('#pdt_ref').val(html.data.pdt_ref);
                        $('#pdt_lib').val(html.data.pdt_lib);
                        $('#pdt_pa').val(html.data.pdt_pa);
                        $('#pdt_pv').val(html.data.pdt_pv);
                        $('#cat_num').val(html.data.cat_num);
                        $('#hidden_id').val(id);
                        $('.modal-title').text("{{__('messages.Editer un produit')}}");
                        $('#action_button').val("{{__('messages.Editer')}}");
                        $('#action').val("{{__('messages.Editer')}}");
                        $('#produitModal').modal('show');
                    }
                })
            });


            var pdt_num;
            $(document).on('click', '.delete', function(){
                pdt_num = $(this).attr('id');
                $('.modal-title').text("{{__('messages.Confirmation')}}");
                $('#ok_button').text('{{__('messages.Oui')}}');
                $('#confirmModal').modal('show');
            });

            $('#ok_button').click(function(){
                $.ajax({
                    url:"produit/destroy/"+pdt_num,
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
