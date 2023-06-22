<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Cache-Control" content="no-cache">
    <title>GrapeCity Wijmo MultiRow Row and Column Freezing</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="{{ URL::asset('node_modules/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ URL::asset('node_modules/@grapecity/wijmo.styles/wijmo.css') }}" rel="stylesheet" />
    <link href="{{ URL::asset('css/invoice.css') }}" rel="stylesheet" />

    <!-- SystemJS -->
    <script src="{{ URL::asset('node_modules/systemjs/dist/system.src.js') }}"></script>
    <script src="{{ URL::asset('js/systemjs.config.js') }}"></script>
    <script>
        var list = <?php echo $list ?>;
        var headerkey = <?php echo $headerkey ?>;
        var headername = <?php echo $headername ?>;
        var invoice = "{{ URL::asset('js/invoice/app2.js') }}";
        System.import(invoice);
    </script>
    <style>
        .wj-topleft .wj-header {
            white-space: inherit;
        }
    </style>
</head>

<body>
    duyetkaka
    <div id="app">
    </div>
    <div class="container-fluid">
        <div id="unboundSheet" class="has-ctx-menu"></div>
    </div>
    <div id="duyet"></div>
</body>

</html>