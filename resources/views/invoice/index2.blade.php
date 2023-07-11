@extends('layouts.layout')
@section("title","見積詳細")
@section("css")
<link href="{{ URL::asset('css/wijmo.min.css') }}" rel="stylesheet" />
<link href="{{ URL::asset('css/invoice.css') }}" rel="stylesheet" />
@endsection
@section("js")
<!-- Wijmo styles and core (required) -->

<script src="{{ URL::asset('js/wijmo.min.js') }}"></script>
<script src="{{ URL::asset('js/wijmo.input.min.js') }}"></script>
<script src="{{ URL::asset('js/wijmo.grid.min.js') }}"></script>
<script src="{{ URL::asset('js/wijmo.grid.multirow.min.js') }}"></script>
<!-- apply your Wijmo licenseKey  (optional) -->
<script>
    wijmo.setLicenseKey("18.219.185.146,251837366827368#B0MYwpjIyNHZisnOiwmbBJye0ICRiwiI34TQvtSYvEmUHRmTzcHaT3UViJmZjlUNNFnZvw4ahJ6LrAjVIZkVw34VLBFcZt4dJdWV8F6YURke4J4doV5NxlUaodmcv2kauBDMHhjbopkYyh7VwJDSMtWOOZEN59kSxZ4QtV4badnV6REMl9GO6MzQNhEUBJjWZBXeF5GZtVXVXlzVPVnYFFVMVh4LwgzNTpkR8FUMVBFSKxWZ6QVSnhUaZZUblVFaxJ6dPVXMpZmRmVWeVBFStV5Vp9GTIZDODhzdi3kUzUEOVlmQ0FVe8hWVEdTQKBne8RVaStiSlhTYzoUUGRFU986Q8J4SI9WczBlcSlVTUR6TUpHTN3GcLZXaQhjSEJkcj3iThB7SNlEV6BlT9Y4V6QmYi56SRBDezhXTiFVarJkUwtCWuBHWCp4dlJja5IkaK5mcl9mMvd6RBJkRMFEUiF5baN4N9lkW8c4MQdUNXZlI0IyUiwiIzgzMFhzN5IjI0ICSiwCMzIDNwMjMwETM0IicfJye35XX3JSSwIjUiojIDJCLi86bpNnblRHeFBCI4VWZoNFelxmRg2Wbql6ViojIOJyes4nI5kkTRJiOiMkIsIibvl6cuVGd8VEIgIXZ7VWaWRncvBXZSBybtpWaXJiOi8kI1xSfis4N8gkI0IyQiwiIu3Waz9WZ4hXRgAydvJVa4xWdNBybtpWaXJiOi8kI1xSfiQjR6QkI0IyQiwiIu3Waz9WZ4hXRgACUBx4TgAybtpWaXJiOi8kI1xSfiMzQwIkI0IyQiwiIlJ7bDBybtpWaXJiOi8kI1xSfiUFO7EkI0IyQiwiIu3Waz9WZ4hXRgACdyFGaDxWYpNmbh9WaGBybtpWaXJiOi8kI1tlOiQmcQJCLiITMwUTMwAyMwcDMzIDMyIiOiQncDJCLiYDNx8SN8EjL9EjMugTMiojIz5GRiwiI2O88qO88iK88oK88+S09ayL9Pyb9qCq9iojIh94QiwiI8YzM7IDO6YzM7MDOxUjMiojIklkIs4XXbpjInxmZiwiIyY7MyAjMiojIyVmdiwZZsx");
</script>
<script>
    var list = <?php echo $list ?>;
    var header = <?php echo $header ?>;
    var cmd = <?php echo $cmd ?>;
    var shiyo = <?php echo $shiyo ?>;
    var headerShiyo = <?php echo $headerShiyo ?>;
</script>

<script src="{{ URL::asset('js/invoice/app2.js') }}" type="text/javascript"></script>
<script>
    $(document).ready(function() {



        $(".reload").on("click", function() {
            location.reload();
        })
        $(".reset").on("click", function() {
            $.ajax({
                type: "get",
                url: "/readCsvInvoice",
                success: function(res) {}
            });
            setTimeout(function() {
                location.reload();
            }, 2000);
        })
        $("#InvoiceModal").modal();

    })
</script>

@endsection
@section('content')
<div class="page"></div>
<div class="container-fluid">
    <div id="grid" class="has-ctx-menu"></div>
</div>
<button class="reload w-17em" style="height: 100px;">画面再表示</button>
<button class="reset w-17em" style="height: 100px;">データリセット</button>
<input type="hidden" class="route-invoice-action" value="{{ route('invoice.action') }}" />

@endsection
@section('modal')
<!-- 編集ポップアップ -->
<div class="modal fade bd-example-modal-lg" id="InvoiceModal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content w-100">
            <div class="modal-header bg-primary pt-2 pb-2">
                <h6 class="modal-title text-white">工事仕様の選択</h6>
                <button type="button" class="close text-white pt-2 pr-2" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-1">
                <div class=" float-right mr-2">
                    <button type="button" class="btn btn-primary btn-custom ml-2 mt-1">選択行に登録</button>
                    <button type="button" class="btn btn-primary btn-custom ml-2 mt-1">同名行に反映(シート)</button>
                    <button type="button" class="btn btn-primary btn-custom ml-2 mt-1">同名行に反映(全体)</button>
                    <button type="button" class="btn btn-primary btn-custom ml-2 mt-1">新規行として追加</button>
                    <div class="input-group align-bottom mg-ig-row ml-2 mt-1 d-inline-flex">
                        <input type="text" class="form-control p-1">
                        <div class="input-group-append">
                            <span class="input-group-text p-1">行</span>
                        </div>
                    </div>
                </div>
                <div class="clear-both"></div>
                <div class="row float-right mr-2 mt-2">
                    <button type="button" class="btn btn-primary btn-custom ml-2 mt-1">入力項目クリア</button>
                    <button type="button" class="btn btn-primary btn-custom ml-2 mt-1">初期値に戻す</button>
                    <button type="button" class="btn btn-primary btn-custom ml-2 mt-1">全て初期値に戻す</button>
                </div>
                <div class="clear-both"></div>
                <div class="">
                    <div class="form-group row mb-1">
                        <label class="col-sm-2 col-form-label text-right">明細書</label>
                        <div class="col-sm-10 pl-0 ">
                            <input type="text" name="DetailNo" readonly class="form-control w-5em   p-1 h-30" value="">
                        </div>
                    </div>
                    <div class="form-group row mb-1">
                        <label class="col-sm-2 col-form-label text-right">名称</label>
                        <div class="col-sm-10 pl-0">
                            <input type="text" class="form-control p-1 h-30" name="FisrtName">
                        </div>
                    </div>
                    <div class="form-group row mb-1">
                        <label class="col-sm-2 col-form-label text-right">規格・寸法</label>
                        <div class="col-sm-10 pl-0">
                            <input type="text" class="form-control p-1 h-30" name="StandDimen">
                        </div>
                    </div>
                    <div class="form-group row mb-1">
                        <label class="col-sm-2 col-form-label text-right">メーカ名</label>
                        <div class="col-sm-10 pl-0">
                            <input type="text" class="form-control p-1 h-30 col-5" name="MakerName">
                        </div>
                    </div>
                    <div class="form-group row mb-1">
                        <label class="col-sm-2 col-form-label text-right">単位</label>
                        <div class="col-sm-10 pl-0">
                            <select name="Unit" class="form-control p-1 h-30 col-3 Unit">
                                <option></option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row mb-1">
                        <label class="col-sm-2 col-form-label text-right">数量</label>
                        <div class="col-sm-10 pl-0">
                            <input type="number" class="form-control p-1 h-30 col-3 d-inline text-right" name="Quantity">
                            <span class="ml-2 pr-1">単価</span><input type="number" name="UnitPrice" class="form-control p-1 h-30 col-3 d-inline text-right">
                            <span class="ml-2 pr-1">金額</span><input type="text" name="Amount" class="form-control p-1 h-30 col-3 d-inline text-right">
                        </div>
                    </div>
                    <div class="form-group row mb-1">
                        <label class="col-sm-2 col-form-label text-right">備考</label>
                        <div class="col-sm-10 pl-0">
                            <input type="text" class="form-control p-1 h-30" name="Note">
                        </div>
                    </div>
                </div>
                <div id="shiyo_selected" class="wijmo-custom">

                </div>
                <div id="shiyo" class="wijmo-custom mt-2">

                </div>

                <div id="shiyoPage"></div>
            </div>
        </div>
    </div>
</div>
{{ Form::hidden('route-getListShiyo', route('getListShiyo')) }}
@endsection