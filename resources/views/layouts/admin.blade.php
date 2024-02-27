@extends('layouts.adminlayout')
@section('content')
    <div class="contentfluid py-4">
    <div class="col-md-12"><h3 style="text-align: center">{{__('messages.TABLEAU DE BORD')}}</h3></div>
    <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div
                        class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                        <i class="btn" style="background-color: #08588d;color:#fff">{{__('messages.Recette du mois en cours')}}</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">&nbsp;</p>
                        <h5 class="mb-0">{{number_format($recette,0,'.',' ')}} Franc cfa</h5><p>&nbsp;</p>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div
                        class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                        <i class="btn" style="background-color: #e92676;color:#fff ">{{__('messages.Commande en attente')}}</i>
                    </div>
                    <div class="alert alert-danger mt-3">
                        <h5>{{$commande}} {{__('messages.commande(s)')}}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div
                        class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                        <i class="btn" style="background-color: #d1d73f;color:#fff">{{__('messages.Produit en peremption')}}</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">&nbsp;</p>
                        <h5 class="mb-0">{{$produit_perime}} {{__('messages.produit(s)')}}</h5><p>&nbsp;</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div
                        class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                        <i class="btn" style="background-color: #000000;color:#fff">{{__('messages.Stock de securite atteint')}}</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">&nbsp;</p>
                        <h5 class="mb-0">{{$stock_alerte}} {{__('messages.produit(s)')}}</h5><p>&nbsp;</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
        <div class="container-fluid col-md-12">
                <div id="lesventes" style="height: 400px; width: 100%;" ></div>
        </div>
        <div class="container-fluid col-md-12">
            <div id="stock" style="height: 400px;" ></div>
        </div>
        <br>
    </div>


@endsection

@section('extra-js')

    <script>
        $(document).ready(function() {
            // Javascript method's body can be found in assets/js/demos.js
            //md.initDashboardPageCharts();
        });
    </script>
    <script type="text/javascript" src="{{asset('js/googlePie.js')}}"></script>
    <script src="{{asset('js/materialize.min.js')}}"></script>
    <script type="text/javascript">

        google.charts.load('current', {'packages':['bar']});
        google.charts.setOnLoadCallback(drawChart);
        google.charts.setOnLoadCallback(stock);

        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(perimes);

        function drawChart() {

            var data = google.visualization.arrayToDataTable([
                ['Date', 'Ventes'],
                @foreach ($ventes as $vente) // On parcourt les catégories
                [ "{{ $vente->date_vente }}", {{ $vente->montant_total }} ], // Proportion des produits de la catégorie
                @endforeach
            ]);

            var options = {
                chart: {
                    title: '{{__('messages.Les Ventes de l utilisateur du mois en cours')}}',
                },
                bars: 'vertical' // Direction "verticale" pour les bars
            };

            var chart = new google.charts.Bar(document.getElementById('lesventes'));

            chart.draw(data, google.charts.Bar.convertOptions(options));
        }

        function stock() {

            var data = google.visualization.arrayToDataTable([
                ['Produit', 'stock'],
                @foreach ($stock as $pdt) // On parcourt les catégories
                [ "{{ $pdt->libelle }}", {{ $pdt->qte }} ], // Proportion des produits de la catégorie
                @endforeach
            ]);
            var options = {
                chart: {
                    title: '{{__('messages.Les produits en fin de stock')}}',
                },
                bars: 'vertical' // Direction "verticale" pour les bars
            };

            var chart = new google.charts.Bar(document.getElementById('stock'));

            chart.draw(data, google.charts.Bar.convertOptions(options));
        }
    </script>
@endsection
