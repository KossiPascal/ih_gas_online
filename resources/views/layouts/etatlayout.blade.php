<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    @yield('extra-meta')

    <title>CARRISOFT V2</title>
        <!-- Tell the browser to be responsive to screen width -->
    <link rel="icon" href="{{asset('images/ceco.png')}}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{asset('plugins/fontawesome-free/css/all.min.css')}}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="{{asset('css/ionicons.css')}}">
    <link rel="stylesheet" href="{{asset('css/main_style.css')}}">
    <!-- JQVMap -->
    <!-- Theme style -->
    <link rel="stylesheet" href="{{asset('dist/css/adminlte.min.css')}}">
    <link rel="stylesheet" href="{{asset('dist/css/adminlte.core.min.css')}}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css')}}">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

    <link rel="stylesheet" href="{{asset('dist/css/bootstrap.min.css')}}" />


    <link rel="stylesheet" href="{{asset('dist/css/jquery.dataTables.min.css')}}" />
    <link rel="stylesheet" href="{{asset('dist/css/dataTables.bootstrap4.min.css')}}" />
    <link rel="stylesheet" href="{{asset('dist/css/bootstrap-datetimepicker.css')}}" />
    <link rel="stylesheet" href="{{asset('css/buttons.dataTables.min.css')}}" />
    <link rel="stylesheet" href="{{asset('css/jquery.dataTables.min.css')}}" />

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.23/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.dataTables.min.css">


</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">

<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav" style="background-color: #fff">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>
</nav>
<!-- /.navbar -->

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-04">
    <!-- Brand Logo -->
    <a href="{{route('app.home')}}" class="brand-link">
        <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text font-weight-light">CARRISOFT V2</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                {{Auth::user()->name}}
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
                     with font-awesome or any other icon font library -->
                <li class="nav-item has-treeview menu-open">
                    <a href="#" class="nav-link active">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            MENU GENERAL
                        </p>
                    </a>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-copy ml-2"></i>
                        <p>
                            DONNEES DE BASE
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item ml-4">
                            <a href="{{route('categorie.index')}}" class="nav-link">
                                <i class="fab fa-product-hunt nav-icon"></i>
                                <p>Les Categories</p>
                            </a>
                        </li>

                        <li class="nav-item ml-4">
                            <a href="{{route('produit.index')}}" class="nav-link">
                                <i class="fab fa-product-hunt nav-icon"></i>
                                <p>Les Produits</p>
                            </a>
                        </li>

                        <li class="nav-item ml-4">
                            <a href="{{route('matiere.index')}}" class="nav-link">
                                <i class="fa fa-golf-ball nav-icon"></i>
                                <p>Les Matieres</p>
                            </a>
                        </li>

                        <li class="nav-item ml-4">
                            <a href="{{route('client.index')}}" class="nav-link">
                                <i class="fa fa-user nav-icon"></i>
                                <p>Les Clients</p>
                            </a>
                        </li>

                        <li class="nav-item ml-4">
                            <a href="{{route('fournisseur.index')}}" class="nav-link">
                                <i class="fa fa-user-circle nav-icon"></i>
                                <p>Les Fournisseur</p>
                            </a>
                        </li>

                        <li class="nav-item ml-4">
                            <a href="{{route('machine.index')}}" class="nav-link">
                                <i class="fas fa-subway nav-icon"></i>
                                <p>Parc d engins</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fab fa-buy-n-large ml-2"></i>
                        <p>
                            GESTION DES COMMANDES
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item ml-4">
                            <a href="{{route('bc.index')}}" class="nav-link">
                                <i class="fas fa-terminal nav-icon"></i>
                                <p>Bon de Commande</p>
                            </a>
                        </li>
                        <li class="nav-item ml-4">
                            <a href="{{route('br.bc')}}" class="nav-link">
                                <i class="fas fa-backspace nav-icon"></i>
                                <p>Bon de requisition</p>
                            </a>
                        </li>
                        <li class="nav-item ml-4">
                            <a href="{{route('bs.index')}}" class="nav-link">
                                <i class="fas fa-backspace nav-icon"></i>
                                <p>Bon de Sortie</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fab fa-buy-n-large ml-2"></i>
                        <p>
                            GESTION DE STOCK
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item ml-4">
                            <a href="{{route('bc.index')}}" class="nav-link">
                                <i class="fas fa-terminal nav-icon"></i>
                                <p>Matiere</p>
                            </a>
                        </li>
                        <li class="nav-item ml-4">
                            <a href="{{route('bc.index')}}" class="nav-link">
                                <i class="fas fa-backspace nav-icon"></i>
                                <p>Produit</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-oil-can ml-2"></i>
                        <p>
                            CONSO GAZ OIL
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item ml-4">
                            <a href="{{route('cg.livraison')}}" class="nav-link">
                                <i class="fas fa-crutch nav-icon"></i>
                                <p>Livraison Gaz Oil</p>
                            </a>
                        </li>
                        <li class="nav-item ml-4">
                            <a href="{{route('cg.index')}}" class="nav-link">
                                <i class="fas fa-truck-pickup nav-icon"></i>
                                <p>Saisie Consommation</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-bomb ml-2"></i>
                        <p>
                            GESTION DES TIRS
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item ml-4">
                            <a href="{{route('pdttir.index')}}" class="nav-link">
                                <i class="fas fa-bomb nav-icon"></i>
                                <p>Explosif et autre</p>
                            </a>
                        </li>
                        <li class="nav-item ml-4">
                            <a href="{{route('tir.index')}}" class="nav-link">
                                <i class="fas fa-calendar-check nav-icon"></i>
                                <p>Commande et rapport de tir</p>
                            </a>
                        </li>
                        <li class="nav-item ml-4">
                    </ul>
                </li>

                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-project-diagram ml-2"></i>
                        <p>
                            PRODUCTION
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item ml-4">
                            <a href="{{route('prod.index')}}" class="nav-link">
                                <i class="fas fa-atom nav-icon"></i>
                                <p>Fiche  de saisie</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fab fa-paypal ml-2"></i>
                        <p>
                            GESTION DES VENTES
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item ml-4">
                            <a href="{{route('pf.index')}}" class="nav-link">
                                <i class="fas fa-money-check nav-icon"></i>
                                <p>PRO FORMA </p>
                            </a>
                        </li>

                        <li class="nav-item ml-4">
                            <a href="{{route('vente.index')}}" class="nav-link">
                                <i class="fas fa-money-check nav-icon"></i>
                                <p>FICHE DE VENTE</p>
                            </a>
                        </li>

                        <li class="nav-item ml-4">
                            <a href="{{route('vente.stock')}}" class="nav-link">
                                <i class="fas fa-cubes nav-icon"></i>
                                <p>STOCK DE MATIERE</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item has-treeview menu-open">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            @method('POSt')
                            <button type="submit" class="btn btn-danger"><i class="nav-icon fas fa-sign-out-alt"></i>
                                    Deconnexion
                            </button>
                        </form>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper mb-7">
    <!-- Main content -->
    @if(session('success'))
        <div class="alert alert-success col-8 ml-5">
            {{session('success')}}
        </div>
    @endif

    @if(count($errors)>0)
        <div class="alert alert-danger col-8 ml-5">
            <ul class="mt-0 mb-0">
                @foreach($errors->all() as $error)
                    <li>{{$error}}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @yield('content')
    @include('sweetalert::alert')

<!-- /.content -->
</div>
<!-- /.content-wrapper -->
<footer class="main-footer col-12 col-sm-12 col-md-12 mt">
    <strong>Copyright &copy; 2021 CARRISOFT V2.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 2.0.1
    </div>
</footer>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="{{asset('plugins/jquery/jquery.min.js')}}"></script>
<!-- jQuery UI 1.11.4 -->
<script src="{{asset('plugins/jquery-ui/jquery-ui.js')}}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="{{asset('plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

<!-- AdminLTE App -->
<script src="{{asset('dist/js/adminlte.js')}}"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="{{asset('dist/js/pages/dashboard.js')}}"></script>
<!-- AdminLTE for demo purposes -->

<script src="{{asset('dist/js/jquery.validate.js')}}"></script>
<script src="{{asset('dist/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('dist/js/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{asset('dist/js/bootstrap.min.js')}}"></script>
<script src="{{asset('dist/js/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('dist/js/sum().js')}}"></script>
<script src="{{asset('js/sweetalert.js')}}"></script>






</body>
@yield('extra-js')
</html>