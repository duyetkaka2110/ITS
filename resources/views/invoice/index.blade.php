<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>GrapeCity Wijmo MultiRow Row and Column Freezing</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SystemJS -->
    <script src="{{ URL::asset('node_modules/jszip/dist/jszip.js') }}"></script>
    <script src="{{ URL::asset('node_modules/systemjs/dist/system.src.js') }}"></script>
    <script src="{{ URL::asset('js/systemjs.config.js') }}"></script>
    <script>
        var list = <?php echo $list ?>;
        var headerkey = <?php echo $headerkey ?>;
        var headername = <?php echo $headername ?>;
        var invoice = "{{ URL::asset('src/invoice.vue') }}";
        System.import(invoice);
    </script>
</head>

<body>
    duyetkaka
    <div id="app">
    </div>
    <div id="duyet"></div>
</body>

</html>