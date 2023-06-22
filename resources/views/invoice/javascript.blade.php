<html>

<head>
    <meta />
    <title>duyet</title>
    <!-- Wijmo styles and core (required) -->
    <link href="{{ URL::asset('css/wijmo.min.css') }}" rel="stylesheet" />
    <script src="{{ URL::asset('js/wijmo.min.js') }}"></script>

    <script src="{{ URL::asset('js/wijmo.grid.sheet.min.js') }}"></script>
    <!-- Wijmo controls (optional, include the controls you need) -->
    <script src="{{ URL::asset('js/wijmo.grid.min.js') }}"></script>
    <script src="{{ URL::asset('js/wijmo.grid.filter.min.js') }}"></script>
    <script src="{{ URL::asset('js/wijmo.input.min.js') }}"></script>
    <script src="{{ URL::asset('js/wijmo.xlsx.min.js') }}"></script>
    <script src="{{ URL::asset('js/wijmo.grid.xlsx.min.js') }}"></script>
    
    <!-- <script src="{{ URL::asset('node_modules/@grapecity/wijmo.grid/index.js') }}"></script>
    <script src="{{ URL::asset('node_modules/@grapecity/wijmo.input/index.js') }}"></script>
    <script src="{{ URL::asset('node_modules/@grapecity/wijmo.grid.filter/index.js') }}"></script> -->
    <!-- <script src="{{ URL::asset('node_modules/@grapecity/wijmo.grid.sheet/index.js') }}"></script> -->

    <!-- apply your Wijmo licenseKey  (optional) -->
    <script>
        wijmo.setLicenseKey("477492474881697#B0XzzWYmpjIyNHZisnOiwmbBJye0ICRiwiI34TUuJFMStWbqFzZ8MGbHx6NhZ5ROlTdolGen9GUvx4KBRXNPVUW5Y5SaplcG3UbQVTOYp6atB5L8MzKodjQ7ImQINzRylVW4IWTlxWR0BVOORXRQRjMHNXO6l5K9BTd92EOrITTGt6K8AVZNtkdvlTYY3CTEpEdsVTc6QXQERVd7R7diR5Y6VmcjB7TJJzUSFESvIDOldEZyE4ZFpkZolWT6lFWZN5SGVVUy2mUOB5cyVXMBtCd8dzUXJkcW9kUrQkQFlkQ6cHMGR7NpF7boJWd5tkZwIWWDF4ZTl7b4ckbqRFe6llNBJWd5FzcBNUdsVVZRRTO584KYh7cnBFaUlncQd6SUxGRk9ka7cmepVESaF4KtZ5Q6gVc48kYHxWekJ7bmZTUzN4ZRhkR7Y4TKdWWOlFePZmMMdDcpVkdxNlcvQ6KNBjRGlVOstCeoR5ZhNVOYNmTQdkI0IyUiwiIyQURygTRwEjI0ICSiwyNyYDN8kDNxcTM0IicfJye&Qf35VfikEMyIlI0IyQiwiIu3Waz9WZ4hXRgACdlVGaThXZsZEIv5mapdlI0IiTisHL3JSNJ9UUiojIDJCLi86bpNnblRHeFBCIyV6dllmV4J7bwVmUg2Wbql6ViojIOJyes4nILdDOIJiOiMkIsIibvl6cuVGd8VEIgc7bSlGdsVXTg2Wbql6ViojIOJyes4nI4YkNEJiOiMkIsIibvl6cuVGd8VEIgAVQM3EIg2Wbql6ViojIOJyes4nIzMEMCJiOiMkIsISZy36Qg2Wbql6ViojIOJyes4nIVhzNBJiOiMkIsIibvl6cuVGd8VEIgQnchh6QsFWaj9WYulmRg2Wbql6ViojIOJyebpjIkJHUiwiI4IjNwcDMgATM5AzMyAjMiojI4J7QiwiIx8CMuAjL7ITMiojIz5GRiwiI+S09ayL9Pyb9qCq9jK88GO887K88XO882O88sO88wK88iojIh94QiwiI7kjNxgDO4cDNykDN7cDNiojIklkIs4XXbpjInxmZiwiIyY7MyAjMiojIyVmWiDS");
    </script>
    <script>
        var list = <?php echo $list ?>;
        var headerkey = <?php echo $headerkey ?>;
        var headername = <?php echo $headername ?>;
    </script>
    <script src="{{ URL::asset('js/invoice/app.js') }}" type="text/javascript"></script>

</head>

<body>
    <div>
        <div id="grid"></div>
    </div>
</body>

</html>