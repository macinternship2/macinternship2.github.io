<!doctype html>
<html>
<head>
    @include('includes.head')
    @yield('head-content')
</head>
<body>
<div class="container">

    <header class="row">
        @include('includes.header')
    </header>

    <div id="main" class="row">

            @yield('content')

    </div>

    <footer>
        @include('includes.footer')
    </footer>

</div>
    @yield('footer-content')
</body>
</html>