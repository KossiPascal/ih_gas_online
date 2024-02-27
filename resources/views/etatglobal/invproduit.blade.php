@extends('layouts.adminlayout')
@section('title','CARRISOFT V2: BON DE COMMANDE')


@section('content')
    <main class="col-sm-12 ml-sm-auto col-md-12 pt-0" style="text-decoration: none; margin-top: 5px;">
        <div class="col-12 col-sm-12 col-md-12">
            <h5 class="ml-5">FICHE DE STOCK</h5>
            <div class="col-12 col-md-4 float-left">
                <a href="{{route('inv.invglobal')}}" class="btn btn-success"><i class="fa fa-globe"></i> Inventaire Global</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-left">
                <a href="{{route('inv.invmagasin')}}" class="btn btn-primary"><i class="fa fa-database"></i>Inventaire par Magasin</a>
            </div>
            <div class="col-12 col-sm-4 col-md-4 float-right">
                <a href="{{route('inv.invproduit')}}" class="btn btn-warning"><i class="fa fa-info"></i>Fiche de Stock</a>
            </div>
        </div>
        <br>

        <div class="info-box mb-1">
            <div class="row input-daterange">
                <div class="col-12 col-md-3">
                    <input type="text" name="from_date" id="from_date" class="form-control" placeholder="Date Debut" readonly />
                </div>
                <div class="col-12 col-md-3">
                    <input type="text" name="to_date" id="to_date" class="form-control" placeholder="Date Fin" readonly />
                </div>
                <div class="cool-md-3 float-left">
                    <select name="produit_id" id="produit_id" class="form-control">
                        @foreach($produits as $key=>$produit)
                            <option value= "{!! $produit !!}"> {!! $produit !!} </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <button type="button" name="filter" id="filter" class="btn btn-danger">Rechercher</button>
                    <button type="button" name="imprimer" id="imprimer" class="btn btn-primary">Imprimer</button>
                </div>

            </div>
            <!-- /.info-box-content -->
        </div>

        <div class="col-md-12">
            <div class="info-box">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="fiche_produit">
                        <thead>
                        <tr class="cart_menu" style="background-color: #00b0e8">
                            <td class="price">Date</td>
                            <td class="price">Libelle</td>
                            <td class="price">Initial</td>
                            <td class="price">Achat</td>
                            <td class="price">Sortie</td>
                            <td class="total">Solde</td>
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
        function produit() {
            $.ajax({
                url:"inv.produits",
                dataType:"json",
                success:function(data)
                {
                    $('#produit_id').empty();
                    $('#produit_id').append('<option id="0"  value="0">- Choisir un produit -</option>');
                    for (var i = 0; i < data.length; i++) {
                        $('#produit_id').append('<option id=' + data[i].produit_id + ' value=' + data[i].produit_id + '>'+ data[i].libelle +'</option>');
                    }
                    $('#produit_id').change();
                }
            })
        }

        function actualiser() {
            produit_id = document.getElementById("produit_id").value;
        }

        $(document).ready(function(){
            $('.input-daterange').datepicker({
                todayBtn:'linked',
                format:'yyyy-mm-dd',
                autoclose:true
            });
            produit()

            load_data();

            function load_data(from_date = '', to_date = '', produit_id=0) {
                $('#fiche_produit').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url:'{{ route("inv.invproduit") }}',
                        data:{from_date:from_date, to_date:to_date, produit_id:produit_id}
                    },
                    columns: [
                        {
                            data:'date',
                            name:'date'
                        },
                        {
                            data:'motif',
                            name:'motif'
                        },
                        {
                            data:'qte_initiale',
                            name:'qte_initiale'
                        },
                        {
                            data:'qte_entree',
                            name:'qte_entree'
                        },
                        {
                            data:'qte_sortie',
                            name:'qte_sortie'
                        },
                        {
                            data:'qte_reelle',
                            name:'qte_reelle'
                        }

                    ]
                });
            }

            $('#filter').click(function(){
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val();
                var produit_id = $('#produit_id').val();
                if(from_date != '' && to_date != '')
                {
                    $('#fiche_produit').DataTable().destroy();
                    load_data(from_date, to_date,produit_id);
                }
                else
                {
                    alert('Selectionner la periode');
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
                var produit_id = document.getElementById('produit_id').value;
                console.log(debut,fin,produit_id)

                var newWin = window.open();
                var the_url = "inv.print_invproduit/"+debut+"/"+fin+"/"+produit_id;
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
