<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Cache-Control" content="no-cache">
    <title>GrapeCity Wijmo MultiRow Row and Column Freezing</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Wijmo styles and core (required) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="{{ URL::asset('css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ URL::asset('css/main.css') }}" rel="stylesheet" />
    <script src="{{ URL::asset('js/jquery.min.js') }}"></script>
    <script src="{{ URL::asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ URL::asset('js/main.js') }}"></script>

    <link href="{{ URL::asset('css/wijmo.min.css') }}" rel="stylesheet" />
    <link href="{{ URL::asset('css/invoice.css') }}" rel="stylesheet" />
    <script src="{{ URL::asset('js/wijmo.min.js') }}"></script>
    <script src="{{ URL::asset('js/wijmo.grid.min.js') }}"></script>
    <script src="{{ URL::asset('js/wijmo.input.min.js') }}"></script>
    <!-- apply your Wijmo licenseKey  (optional) -->
    <script>
        wijmo.setLicenseKey("477492474881697#B0XzzWYmpjIyNHZisnOiwmbBJye0ICRiwiI34TUuJFMStWbqFzZ8MGbHx6NhZ5ROlTdolGen9GUvx4KBRXNPVUW5Y5SaplcG3UbQVTOYp6atB5L8MzKodjQ7ImQINzRylVW4IWTlxWR0BVOORXRQRjMHNXO6l5K9BTd92EOrITTGt6K8AVZNtkdvlTYY3CTEpEdsVTc6QXQERVd7R7diR5Y6VmcjB7TJJzUSFESvIDOldEZyE4ZFpkZolWT6lFWZN5SGVVUy2mUOB5cyVXMBtCd8dzUXJkcW9kUrQkQFlkQ6cHMGR7NpF7boJWd5tkZwIWWDF4ZTl7b4ckbqRFe6llNBJWd5FzcBNUdsVVZRRTO584KYh7cnBFaUlncQd6SUxGRk9ka7cmepVESaF4KtZ5Q6gVc48kYHxWekJ7bmZTUzN4ZRhkR7Y4TKdWWOlFePZmMMdDcpVkdxNlcvQ6KNBjRGlVOstCeoR5ZhNVOYNmTQdkI0IyUiwiIyQURygTRwEjI0ICSiwyNyYDN8kDNxcTM0IicfJye&Qf35VfikEMyIlI0IyQiwiIu3Waz9WZ4hXRgACdlVGaThXZsZEIv5mapdlI0IiTisHL3JSNJ9UUiojIDJCLi86bpNnblRHeFBCIyV6dllmV4J7bwVmUg2Wbql6ViojIOJyes4nILdDOIJiOiMkIsIibvl6cuVGd8VEIgc7bSlGdsVXTg2Wbql6ViojIOJyes4nI4YkNEJiOiMkIsIibvl6cuVGd8VEIgAVQM3EIg2Wbql6ViojIOJyes4nIzMEMCJiOiMkIsISZy36Qg2Wbql6ViojIOJyes4nIVhzNBJiOiMkIsIibvl6cuVGd8VEIgQnchh6QsFWaj9WYulmRg2Wbql6ViojIOJyebpjIkJHUiwiI4IjNwcDMgATM5AzMyAjMiojI4J7QiwiIx8CMuAjL7ITMiojIz5GRiwiI+S09ayL9Pyb9qCq9jK88GO887K88XO882O88sO88wK88iojIh94QiwiI7kjNxgDO4cDNykDN7cDNiojIklkIs4XXbpjInxmZiwiIyY7MyAjMiojIyVmWiDS");
    </script>
    <script>
        var list = <?php echo $list ?>;
        var header = <?php echo $header ?>;
        var cmd = <?php echo $cmd ?>;
    </script>

    <script src="{{ URL::asset('js/invoice/app2.js') }}" type="text/javascript"></script>
    <style>
        .wj-cell.wj-frozen-col{
            border-right: 2px solid #a2a2a2;
        }
        .wj-topleft .wj-header {
            white-space: inherit;
        }

        .wj-flexgrid {
            height: 550px;
            /* position: absolute;
            inset: 0px; */
        }

        /* use flex display to center-align cells vertically */
        .wj-flexgrid .wj-cell {
            display: flex;
            align-items: center;
        }

        .wj-flexgrid .wj-cell.wj-align-right {
            justify-content: flex-end;
        }

        .wj-flexgrid .wj-cell.wj-align-center {
            justify-content: center;
        }

        .wj-flexgrid .wj-cell.note {
            display: inherit;
        }
        .wj-cells .wj-cell.wj-state-selected.text-danger,
        .wj-cells .wj-cell.wj-state-multi-selected.text-danger{
            color: #fff !important;
        }
    </style>
</head>

<body>
    <div class="page"></div>
    <div class="container-fluid">
        <div id="grid" class="has-ctx-menu"></div>
    </div>
    <input type="hidden" class="route-invoice-action" value="{{ route('invoice.action') }}" />
    <!-- 確認ポップアップ -->
    <div class="modal fade" id="ConfirmModal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mt-0 mb-0" role="document">
            <div class="modal-content w-100">
                <div class="modal-header bg-primary pt-2 pb-2">
                    <h6 class="modal-title text-white">確認メッセージ</h6>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer justify-center">
                    <button type="button" class="btn btn-primary w-8em btnPopupOk" data-dismiss="modal">確認</button>
                    <button type="button" class="btn btn-danger w-8em" data-dismiss="modal">閉じる</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- メッセージポップアップ -->
    <div class="modal fade" id="MessageModal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mt-0 mb-0" role="document">
            <div class="modal-content w-100">
                <div class="modal-header bg-danger pt-2 pb-2">
                    <h6 class="modal-title text-white">メッセージ</h6>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer justify-center">
                    <button type="button" class="btn btn-danger w-8em" data-dismiss="modal">閉じる</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>