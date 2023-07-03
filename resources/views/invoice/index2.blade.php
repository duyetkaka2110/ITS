<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>見積</title>
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
        wijmo.setLicenseKey("18.219.185.146,251837366827368#B0MYwpjIyNHZisnOiwmbBJye0ICRiwiI34TQvtSYvEmUHRmTzcHaT3UViJmZjlUNNFnZvw4ahJ6LrAjVIZkVw34VLBFcZt4dJdWV8F6YURke4J4doV5NxlUaodmcv2kauBDMHhjbopkYyh7VwJDSMtWOOZEN59kSxZ4QtV4badnV6REMl9GO6MzQNhEUBJjWZBXeF5GZtVXVXlzVPVnYFFVMVh4LwgzNTpkR8FUMVBFSKxWZ6QVSnhUaZZUblVFaxJ6dPVXMpZmRmVWeVBFStV5Vp9GTIZDODhzdi3kUzUEOVlmQ0FVe8hWVEdTQKBne8RVaStiSlhTYzoUUGRFU986Q8J4SI9WczBlcSlVTUR6TUpHTN3GcLZXaQhjSEJkcj3iThB7SNlEV6BlT9Y4V6QmYi56SRBDezhXTiFVarJkUwtCWuBHWCp4dlJja5IkaK5mcl9mMvd6RBJkRMFEUiF5baN4N9lkW8c4MQdUNXZlI0IyUiwiIzgzMFhzN5IjI0ICSiwCMzIDNwMjMwETM0IicfJye35XX3JSSwIjUiojIDJCLi86bpNnblRHeFBCI4VWZoNFelxmRg2Wbql6ViojIOJyes4nI5kkTRJiOiMkIsIibvl6cuVGd8VEIgIXZ7VWaWRncvBXZSBybtpWaXJiOi8kI1xSfis4N8gkI0IyQiwiIu3Waz9WZ4hXRgAydvJVa4xWdNBybtpWaXJiOi8kI1xSfiQjR6QkI0IyQiwiIu3Waz9WZ4hXRgACUBx4TgAybtpWaXJiOi8kI1xSfiMzQwIkI0IyQiwiIlJ7bDBybtpWaXJiOi8kI1xSfiUFO7EkI0IyQiwiIu3Waz9WZ4hXRgACdyFGaDxWYpNmbh9WaGBybtpWaXJiOi8kI1tlOiQmcQJCLiITMwUTMwAyMwcDMzIDMyIiOiQncDJCLiYDNx8SN8EjL9EjMugTMiojIz5GRiwiI2O88qO88iK88oK88+S09ayL9Pyb9qCq9iojIh94QiwiI8YzM7IDO6YzM7MDOxUjMiojIklkIs4XXbpjInxmZiwiIyY7MyAjMiojIyVmdiwZZsx");
    </script>
    <script>
        var list = <?php echo $list ?>;
        var header = <?php echo $header ?>;
        var cmd = <?php echo $cmd ?>;
    </script>

    <script src="{{ URL::asset('js/invoice/app2.js') }}" type="text/javascript"></script>
    <script> 
    $(document).ready(function(){
        $(".reload").on("click",function(){
            location.reload();
        })
        $(".reset").on("click",function(){
            $.ajax({
                type: "get",
                url: "/readCsv",
                success: function (res) {
                }
            });
            setTimeout(function() {
                location.reload();
            }, 2000);
        })
    })
    </script>
    <style>
        .bg-green,
        .wj-cell.bg-green{
           background: #b2ffd4;
        }
        .row-total{
            font-weight: bold;
        }
        .wj-cell.wj-frozen-col{
            border-right: 2px solid #a2a2a2;
        }
        .wj-topleft .wj-header {
            white-space: inherit;
        }
        .wj-cells .wj-cell.wj-state-selected{
            background: #80adbf;
        }
        .wj-flexgrid {
            height: 750px;
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
    <button class="reload w-17em" style="height: 100px;" >画面再表示</button>
    <button class="reset w-17em" style="height: 100px;" >データリセット</button>
    <input type="hidden" class="route-invoice-action" value="{{ route('invoice.action') }}" />
    <!-- 編集ポップアップ -->
    <div class="modal fade" id="InvoiceModal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static" aria-hidden="true">
        <div class="modal-dialog " role="document">
            <div class="modal-content w-100">
                <div class="modal-header bg-primary pt-2 pb-2">
                    <h6 class="modal-title text-white">見積編集</h6>
                </div>
                <div class="modal-body">
                見積編集画面
                見積編集画面
                見積編集画面
                見積編集画面
                見積編集画面
                見積編集画面
                見積編集画面
                見積編集画面
                見積編集画面
                見積編集画面
                見積編集画面
                見積編集画面
                見積編集画面
                見積編集画面
                見積編集画面
                見積編集画面
                見積編集画面
                見積編集画面
                見積編集画面
                </div>
                <div class="modal-footer justify-center">
                    <button type="button" class="btn btn-primary w-8em btnPopupOk" data-dismiss="modal">確認</button>
                    <button type="button" class="btn btn-danger w-8em" data-dismiss="modal">閉じる</button>
                </div>
            </div>
        </div>
    </div>
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