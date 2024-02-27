<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="{{asset('plugins/fontawesome-free/css/all.min.css')}}">
    <!-- Ionicons
    <link rel="stylesheet" href="{{asset('css/ionicons.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css')}}">

    <link rel="stylesheet" href="{{asset('dist/css/bootstrap.min.css')}}" />-->
</head>
<body onload="mafonction()">

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper mb-7">
    @yield('content')
<!-- /.content -->
</div>
</body>
@yield('extra-js')
</html>