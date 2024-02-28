<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    @yield('extra-meta')

    <title>GAS V1</title>
        <!-- Tell the browser to be responsive to screen width -->
    <link rel="icon" href="{{asset('images/pcsoft.png')}}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{asset('plugins/fontawesome-free/css/all.min.css')}}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="{{asset('css/ionicons.css')}}">
    <link rel="stylesheet" href="{{asset('css/charte_graphique.css')}}" type="text/css">
    <link rel="stylesheet" href="{{asset('css/sweetalert2.css')}}" type="text/css">
    <!-- JQVMap -->
    <!-- Theme style -->
    <link rel="stylesheet" href="{{asset('dist/css/adminlte.min.css')}}">
    <link rel="stylesheet" href="{{asset('dist/css/adminlte.core.min.css')}}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css')}}">
    <!-- Google Font: Source Sans Pro
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">-->

    <link rel="stylesheet" href="{{asset('dist/css/bootstrap.min.css')}}" />


    <link rel="stylesheet" href="{{asset('dist/css/jquery.dataTables.min.css')}}" />
    <link rel="stylesheet" href="{{asset('dist/css/dataTables.bootstrap4.min.css')}}" />
	<!-- ... (Votre code HTML existant) ... -->

	<style>
		body {
			background-color: #ffffff; /* Couleur de fond */
		}

		nav.main-header {
			background-color: #08588d; /* Bleu foncé pour la barre de navigation */
		}

		.navbar-nav {
			background-color: #ffffff; /* Fond blanc pour les éléments de navigation */
		}

		.navbar-light .navbar-nav .nav-link {
			color: #08588d; /* Couleur du texte de navigation en bleu foncé */
		}

		.navbar-light .navbar-nav .nav-link:hover {
			color: #d1d73f; /* Couleur du texte de navigation en jaune au survol */
		}

		/* ... (Autres styles CSS à mettre à jour selon la charte graphique) ... */

		.nav-link.active {
			background-color: #27a5de; /* Bleu clair pour la navigation active */
			color: #ffffff; /* Texte en blanc pour la navigation active */
		}

		.main-sidebar {
			background-color: #08588d; /* Bleu fonce pour la barre latérale */
		}

		.wrapper {
			border-left: 3px solid #27a5de; /* Bordure gauche en bleu clair pour le wrapper */
		}

		/* ... (Autres styles CSS à mettre à jour selon la charte graphique) ... */

		footer.main-footer {
			background-color: #000000; /* Noir pour le pied de page */
			color: #ffffff; /* Texte en blanc pour le pied de page */
		}
	</style>

	<!-- ... (Votre code HTML existant) ... -->


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

    @php
    $defaultLocale = app()->getLocale();
    @endphp

    <div class="container" style="margin-left: 80%">
        <div class="row" style="justify-content: space-around; align-items:center">
            <div class="col-md-3">
                <i class="fa fa-globe"></i>
            </div>
            <div>
                <select class="form-control" onchange="window.location.href=this.value" style="border: none">
                    @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        <option value="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                            {{ $localeCode === $defaultLocale ? 'selected' : '' }}>
                            {{ $properties['native'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</nav>


<!-- /.navbar -->

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-04">
    <!-- Brand Logo -->
    <a href="{{route('app.home')}}" class="brand-link" style="background-color: #08588d;">
        <img src="/images/logo.png" alt="" class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text font-weight-light">GAS V1</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                @if(Auth::check() && Auth::user()->profile_picture)
                    <img src="{{ asset(Auth::user()->profile_picture) }}" class="img-circle elevation-2" alt="User Picture">
                @else
                    <!-- Default image if user doesn't have a profile picture -->
                    <img src="{{ asset('images/default_icon.png') }}" class="img-circle elevation-2" alt="User Picture">
                @endif
            </div>
            <div class="info">
                <a href="{{ route('user.moncompte',\Illuminate\Support\Facades\Auth::user()->id) }}">{{\Illuminate\Support\Facades\Auth::user()->name}}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        @include('sweetalert::alert')

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
                     with font-awesome or any other icon font library -->
                <li class="info">
                    <a href="#" class="nav-link active" style="background-color: #27a5de;">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                        {{__('messages.MENU GENERAL')}}
                        </p>
                    </a>
                </li><br>
                @can('manage-action', ['menu','donnee'])
                <li class="nav-item has-treeview">
                    <a href="{{route('cat.index')}}" class="nav-link">
                        <i class="nav-icon fas fa-copy ml-2"></i>
                        <p>
                        {{__('messages.DONNEES DE BASE')}}
                        </p>
                    </a>
                </li>
                @endcan
                <li class="nav-item has-treeview menu-open">
                    <a href="#" class="nav-link active" style="background-color: #08588d;">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                        {{__('messages.GESTION DE STOCK')}}
                        </p>
                    </a>
                </li>
                @can('manage-action', ['menu','stock'])
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fab fa-buy-n-large ml-2"></i>
                        {{__('messages.FORMATION SANITAIRE')}}
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item ml-4">
                            @can('manage-action', ['commande','lister'])
                            <a href="{{route('cmde.index')}}" class="nav-link">
                                <i class="fas fa-shopping-cart nav-icon"></i>
                                <p>{{__('messages.Gestion des commandes')}}</p>
                            </a>
                            @endcan
                        </li>
                        <li class="nav-item ml-4">
                            @can('manage-action', ['reception','lister'])
                            <a href="{{route('rec.index')}}" class="nav-link">
                                <i class="fas fa-plus nav-icon"></i>
                                <p>{{__('messages.Reception commandes')}}</p>
                            </a>
                            @endcan
                        </li>

                        <li class="nav-item ml-4">
                            @can('manage-action', ['transfert','lister'])
                            <a href="{{route('tr.index')}}" class="nav-link">
                                <i class="fas fa-backspace nav-icon"></i>
                                <p>{{__('messages.Transfert de produit')}}</p>
                            </a>
                            @endcan
                        </li>
                        <li class="nav-item ml-4">
                            @can('manage-action', ['confection_kit','lister'])
                            <a href="#" class="nav-link">
                                <i class="fas fa-backspace nav-icon"></i>
                                <p>{{__('messages.Confection des Kits')}}</p>
                            </a>
                            @endcan
                        </li>
                        <li class="nav-item ml-4">
                            @can('manage-action', ['correction','lister'])
                            <a href="{{route('cs.index')}}" class="nav-link">
                                <i class="fas fa-store nav-icon"></i>
                                <p>{{__('messages.Corection du stock')}}</p>
                            </a>
                            @endcan
                        </li>

                        <li class="nav-item ml-4">
                            @can('manage-action', ['stock','lister'])
                            <a href="{{route('inv.etatglobal')}}" class="nav-link">
                                <i class="fas fa-home nav-icon"></i>
                                <p>{{__('messages.Etat du stock')}}</p>
                            </a>
                            @endcan
                        </li>
                        <li class="nav-item ml-4">
                            @can('manage-action', ['stock','lister'])
                            <a href="{{route('inv.invglobal')}}" class="nav-link">
                                <i class="fas fa-store  nav-icon"></i>
                                <p>{{__('messages.Inventaires')}}</p>
                            </a>
                            @endcan
                        </li>
                    </ul>
                </li>
                @endcan

                @can('manage-action', ['menu','dps'])
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-copy ml-2"></i>
                        <p>
                        {{__('messages.DPS')}}
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item ml-4">
                            <a href="{{route('val.index')}}" class="nav-link">
                                <i class="fas fa-copy nav-icon"></i>
                                <p>{{__('messages.Valider une commande')}}</p>
                            </a>
                        </li>
                        <li class="nav-item ml-4">
                            <a href="{{route('recdps.index')}}" class="nav-link">
                                <i class="fas fa-plus nav-icon"></i>
                                <p>{{__('messages.Reception Commande')}}</p>
                            </a>
                        </li>
                        <li class="nav-item ml-4">
                            <a href="{{route('trdps.index')}}" class="nav-link">
                                <i class="fas fa-plus nav-icon"></i>
                                <p>{{__('messages.Transfert vers FS')}}</p>
                            </a>
                        </li>
                        <li class="nav-item ml-4">
                            <a href="{{route('eg.stockglobaldps')}}" class="nav-link">
                                <i class="fas fa-home nav-icon"></i>
                                <p>{{__('messages.Etat du stock')}}</p>
                            </a>
                        </li>
                        <li class="nav-item ml-4">
                            <a href="{{route('eg.stockglobaldps')}}" class="nav-link">
                                <i class="fas fa-store  nav-icon"></i>
                                <p>Etat des recettes</p>
                            </a>
                        </li>
                        <li class="nav-item ml-4">
                            <a href="{{route('eg.stockglobaldps')}}" class="nav-link">
                                <i class="fas fa-store  nav-icon"></i>
                                <p>{{__('messages.Inventaires')}}</p>
                            </a>
                        </li>
                        @can('manage-action', ['menu','userdps'])
                        <li class="nav-item ml-4">
                            <a href="{{route('user.userdps')}}" class="nav-link">
                                <i class="fas fa-home nav-icon"></i>
                                <p>{{__('messages.Utilisateur DPS')}}</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcan

                <!-- @can('manage-action', ['menu','si']) -->
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fab fa-buy-n-large ml-2"></i>
                        <p>
                        {{__('messages.SANTE INTEGREE')}}
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item ml-4">
                            <a href="{{route('recsi.index')}}" class="nav-link">
                                <i class="fas fa-plus nav-icon"></i>
                                <p>{{__('messages.Reception Grossiste')}}</p>
                            </a>
                        </li>
                        <li class="nav-item ml-4">
                            <a href="{{route('trsi.index')}}" class="nav-link">
                                <i class="fas fa-plus nav-icon"></i>
                                <p>{{__('messages.Transfert vers FS')}}</p>
                            </a>
                        </li>
                        <li class="nav-item ml-4">
                            <a href="{{route('cmde.histo')}}" class="nav-link">
                                <i class="fas fa-backspace nav-icon"></i>
                                <p>{{__('messages.Suivi des commandes')}}</p>
                            </a>
                        </li>

                        <li class="nav-item ml-4">
                            <a href="{{route('eg.stockglobal')}}" class="nav-link">
                                <i class="fas fa-home nav-icon"></i>
                                <p>{{__('messages.Etat du stock')}}</p>
                            </a>
                        </li>
                        <li class="nav-item ml-4">
                            <a href="{{route('invsi.invglobal')}}" class="nav-link">
                                <i class="fas fa-store  nav-icon"></i>
                                <p>{{__('messages.Inventaires')}}</p>
                            </a>
                        </li>
                        <li class="nav-item ml-4">
                            <a href="{{route('eg.etatcaissesi')}}" class="nav-link">
                                <i class="fas fa-store  nav-icon"></i>
                                <p>{{__('messages.Etat des recettes')}}</p>
                            </a>
                        </li>
                        @can('manage-action', ['menu','usersi'])
                        <li class="nav-item ml-4">
                            <a href="{{route('user.usersi')}}" class="nav-link">
                                <i class="fas fa-backspace nav-icon"></i>
                                <p>{{__('messages.Utilisateur SI')}}</p>
                            </a>
                        </li>
                        @endcan
                    </ul>
                </li>
                <!-- @endcan -->

                @can('manage-action', ['menu','caisse'])
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link" style="background-color: #27a5de;">
                        <i class="nav-icon fas fa-copy"></i>
                        <p>
                        {{ __('messages.CAISSE/COMPTABILITE') }}
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item ml-4">
                            @can('manage-action', ['vente','lister'])
                            <a href="{{route('vente.index')}}" class="nav-link">
                                <i class="fas fa-box nav-icon"></i>
                                <p>{{__('messages.Fiche de Vente')}}</p>
                            </a>
                            @endcan
                        </li>
                        <li class="nav-item ml-4">
                            @can('manage-action', ['vente','encaisser'])
                            <a href="{{route('vente.index')}}" class="nav-link">
                                <i class="nav-icon fas fa-oil-can ml-2"></i>
                                {{__('messages.PHARMACIE / VENTE')}}
                            </a>
                            @endcan
                        </li>
                        <li class="nav-item ml-4">
                            @can('manage-action', ['vente','etat_recette'])
                            <a href="{{route('vente.etatcaisse')}}" class="nav-link">
                                <i class="fas fa-calculator nav-icon"></i>
                                <p>{{__('messages.Etat des recettes')}}</p>
                            </a>
                            @endcan
                        </li>
                        <li class="nav-item ml-4">
                            @can('manage-action', ['vente','etat_assurance'])
                            <a href="{{route('vente.etatassurance')}}" class="nav-link">
                                <i class="fas fa-asterisk nav-icon"></i>
                                <p>{{__('messages.Facturer les assurances')}}</p>
                            </a>
                            @endcan
                        </li>
                        <li class="nav-item ml-4">
                            @can('manage-action', ['operation','creer'])
                            <a href="{{route('op.index')}}" class="nav-link">
                                <i class="fas fa-credit-card nav-icon"></i>
                                <p>{{__('messages.Operation Bancaire')}}</p>
                            </a>
                            @endcan
                        </li>
                    </ul>
                </li>
                @endcan
                <li class="nav-item has-treeview menu-open">
                    <a href="#" class="nav-link" style="background-color: #d1d73f;">
                        <i class="nav-icon fas fa-digital-tachograph"></i>
                        <p>
                            {{__('messages.ADMINISTRATION')}}
                        </p>
                    </a>
                </li>
                <li class="nav-item has-treeview">
                    <!-- @can('manage-action', ['user','lister'])-->
                    <a href="{{route('user.index')}}" class="nav-link">
                        <i class="nav-icon fa fa-file-invoice"></i>
                        <p>
                            {{__('messages.ESPACE ADMINISTRATION')}}
                        </p>
                    </a>
                    <!--@endcan-->
                </li>
                <li class="nav-item">
                @can('manage-action', ['utilisateur','lister'])
                    <a href="{{route('user.index')}}" class="nav-link">
                        <i class="nav-icon fa fa-file-invoice"></i>
                        <p class="text">{{__('messages.ESPACE ADMINISTRATION')}}</p>
                    </a>
                @endcan
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fa fa-user"></i>
                        <p class="text">{{__('messages.MON COMPTE')}}</p>
                    </a>
                </li>
                <li class="nav-item has-treeview menu-open" style="background-color: #e92676">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        @method('POSt')
                        <button type="submit" class="btn"><i class="nav-icon fas fa-sign-out-alt"></i>
                        <span class="text">{{__('messages.Deconnexion')}}</span>
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
    <strong>Copyright  {{__('messages.SANTE INTEGREE')}} &copy; 2023 GAS V1.</strong>
    {{__('messages.Tous droits reserves')}}
    <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 5.0.1
    </div>
</footer>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="{{asset('plugins/jquery/jquery.min.js')}}"></script>
<!-- jQuery UI 1.11.4 -->

<!-- AdminLTE App -->
<script src="{{asset('dist/js/adminlte.js')}}"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!-- AdminLTE for demo purposes -->

<script src="{{asset('dist/js/jquery.validate.js')}}"></script>
<script src="{{asset('dist/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('dist/js/bootstrap.min.js')}}"></script>
<script src="{{asset('dist/js/bootstrap-datepicker.js')}}"></script>
<script src="{{asset('dist/js/sum().js')}}"></script>
<script src="{{asset('js/sweetalert.all.js')}}"></script>




</body>
@yield('extra-js')
</html>
