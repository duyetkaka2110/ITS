<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') </title>
    <script src="{{ URL::asset('js/jquery.min.js') }}"></script>
    <script src="{{ URL::asset('js/bootstrap.min.js') }}"></script>
    <link href="{{ URL::asset('css/bootstrap.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="{{ URL::asset('css/main.css') }}" rel="stylesheet" />
    <script src="{{ URL::asset('js/main.js') }}"></script>
    @yield("css")
    @yield("js")
</head>

<body>
    <main class="">
        @if(session('success'))
        <div class="alert alert-success" role="alert">
            {{session('success')}}
        </div>
        <script>
            $(".alert-success").show().delay(1500).fadeOut(100);
        </script>
        @endif
        @yield("content")
        <div class="clear-both"></div>
    </main>

    @include("layouts.modal")
    @yield("modal")
</body>

</html>