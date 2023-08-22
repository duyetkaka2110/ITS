@extends('layouts.layout')
@section("title","見積詳細")
@section("css")
<link href="{{ URL::asset('css/jquery-ui.css') }}" rel="stylesheet" />
<link href="{{ URL::asset('css/wijmo.min.css') }}" rel="stylesheet" />
<link href="{{ URL::asset('css/mitsumori.css') }}" rel="stylesheet" />
<link href="{{ URL::asset('jstree/themes/default/style.min.css') }}" rel="stylesheet" />
<link href="{{ URL::asset('css/category.css') }}" rel="stylesheet" />
@endsection
@section("js")
<script src="{{ URL::asset('js/js.cookie.min.js') }}"></script>
<script src="{{ URL::asset('js/jquery-ui.min.js') }}"></script>
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
    var headerShiyo = <?php echo $headerShiyo ?>;
    var headerShiyoSelected = <?php echo $headerShiyoSelected ?>;
    var headerZairyo = <?php echo $headerZairyo ?>;
    var headerZairyoSelected = <?php echo $headerZairyoSelected ?>;
    var categories = <?php echo $categories ?>
</script>

<script src="{{ URL::asset('js/mitsumori.js') }}" type="text/javascript"></script>
<script type="text/javascript" src="{{ URL::asset('jstree/jstree.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/category.js') }}"></script>
<script>
    $(document).ready(function() {

        $(".btn-secondary").on("click", function() {
            dispMessageModal("工事中です")
        })

        $(".reload").on("click", function() {
            location.reload();
        })
        $(".reset").on("click", function() {
            $.ajax({
                type: "get",
                url: "/readCsvMitsumori",
                success: function(res) {}
            });
            setTimeout(function() {
                location.reload();
            }, 2000);
        })
        // $("#MitsumoriModal").modal()
    })
</script>
<style>
</style>
@endsection
@section('content')
<div class="page"></div>
<div class="mg-all row m-0 ">
    <div class="mg-jstree col-sm-3">
        <label class="pt-2 pb-2 m-0 btn-collapse bold cursor-point" data-toggle="collapse" >
            <i class="fa fa-minus-square-o" aria-hidden="true"></i>
            <strong>カテゴリ</strong>
        </label>
        <div id="jstree" class=""></div>
    </div>

    <div class="mg-grid  col-sm-9 p-0">
        <div class="mg-menu"></div>
        <div id="grid" class="has-ctx-menu"></div>
    </div>
    {{ Form::hidden('route-cstore', route('c.store')) }}
</div>
<a class="reload btn btn-primary" href="#" >画面再表示</a>
<a class="reset btn btn-primary" href="#" >データリセット</a>
<a href="{{ route('c.reset') }}" class=" btn btn-primary">カテゴリリセット</a>
<input type="hidden" class="route-mitsumore-action" value="{{ route('mitsumore.action') }}" />

@endsection
@section('modal')
<!-- 編集ポップアップ -->
<div class="modal fade modal-drag bd-example-modal-lg " id="MitsumoriModal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-MitsumoriModal modal-lg" role="document">
        <div class="modal-content w-100">
            <div class="modal-header modal-header-MitsumoriModal bg-primary pt-2 pb-2">
                <h6 class="modal-title text-white">工事仕様選択</h6>
                <button type="button" class="close text-white ">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-1">
                <form class="form-selected">
                    <div class=" float-right mr-2 mb-3">
                        <button type="button" class="btn btn-secondary btn-custom ml-2 mt-1">入力項目クリア</button>
                        <button type="button" class="btn btn-secondary btn-custom ml-2 mt-1">初期値に戻す</button>
                        <button type="button" class="btn btn-secondary btn-custom ml-2 mt-1">全て初期値に戻す</button>
                        <button type="button" class="btn btn-primary btn-custom ml-2 mt-1 btnSave" data-btn="btnSave">選択行に登録</button>
                        <button type="button" class="btn btn-secondary btn-custom ml-2 mt-1">同名行に反映(シート)</button>
                        <button type="button" class="btn btn-secondary btn-custom ml-2 mt-1">同名行に反映(全体)</button>
                        <button type="button" class="btn btn-primary btn-custom ml-2 mt-1 btnSave" data-btn="btnSaveNew">新規行として追加</button>
                        <div class="input-group align-bottom mg-ig-row ml-2 mt-1 d-inline-flex">
                            <input type="number" min=1 max=99 class="form-control p-1" name="RowAdd">
                            <div class="input-group-append">
                                <span class="input-group-text p-1">行</span>
                            </div>
                        </div>
                    </div>
                    <div class="clear-both"></div>
                    <div class="">
                        <div class="form-group row mb-1">
                            <label class="col-sm-2 col-form-label text-right">明細書</label>
                            <div class="col-sm-10 pl-0 ">
                                {{ Form::hidden("id",null)}}
                                {{ Form::hidden("Type",null)}}
                                {{ Form::hidden("PartName",null)}}
                                <input type="text" name="DetailNo" readonly class="form-control w-5em d-inline  p-1 h-30" value="">
                                <span class="cursor-point p-2 d-inline btn-collapse" title="表示/非表示" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="true" aria-controls="collapseExample">
                                    <i class="fa fa-minus-square-o" aria-hidden="true"></i>
                                </span>
                            </div>
                        </div>
                        <div id="collapseExample" class="collapse show">
                            <div class="form-group row mb-1">
                                <label class="col-sm-2 col-form-label text-right">名称</label>
                                <div class="col-sm-10 pl-0">
                                    <input type="text" class="form-control p-1 h-30  text-danger" name="FirstName">
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label class="col-sm-2 col-form-label text-right">規格・寸法</label>
                                <div class="col-sm-10 pl-0">
                                    <input type="text" class="form-control p-1 h-30 text-danger" name="StandDimen">
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label class="col-sm-2 col-form-label text-right">メーカ名</label>
                                <div class="col-sm-10 pl-0">
                                    <input type="text" class="form-control p-1 h-30 col-5 text-danger" name="MakerName">
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label class="col-sm-2 col-form-label text-right">単位</label>
                                <div class="col-sm-10 pl-0">
                                    {!! Form::select('UnitOrg_ID', ["" => ""] + $tanis, null, ["class" => "form-control p-1 h-30 col-3 Unit"]) !!}
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label class="col-sm-2 col-form-label text-right">数量</label>
                                <div class="col-sm-10 pl-0">
                                    <input type="url" title="数量" class="form-control p-1 h-30 col-3 d-inline text-right imeoff amount-change floatic" inputmode="number" name="Quantity">
                                    <span class="ml-2 pr-1">単価</span>
                                    <div class="input-group align-bottom pl-0 col-3 mt-1 d-inline-flex">
                                        <input type="url" title="単価" class="form-control p-1 h-30  d-inline text-right imeoff numeric amount-change" inputmode="number" name="UnitPrice">
                                        <div class="input-group-append">
                                            <span class="input-group-text p-1 font-s-83">円</span>
                                        </div>
                                    </div>
                                    <span class="ml-2 pr-1">金額</span>
                                    <div class="input-group align-bottom pl-0 col-3 mt-1 d-inline-flex">
                                        <input type="text" title="金額" class="form-control p-1 h-30  d-inline text-right" name="Amount" readonly>
                                        <div class="input-group-append">
                                            <span class="input-group-text p-1 font-s-83">円</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row mb-1">
                                <label class="col-sm-2 col-form-label text-right">備考</label>
                                <div class="col-sm-10 pl-0">
                                    <input type="text" class="form-control p-1 h-30" name="Note">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="shiyo_selected" class="wijmo-custom wijmo-height-220 wijmo-blue"></div>
                </form>
                <form class="form-shiyo position-relative">
                    {{ Form::hidden("page",0)}}
                    <div class="shiyo-loading d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only"></span>
                        </div>
                    </div>
                    <div id="shiyo" class="wijmo-custom mt-2 wijmo-red wijmo-height-220"></div>
                </form>
                <div id="shiyoPage" class="mt-2"></div>
            </div>
        </div>
    </div>
</div>
<!-- 仕様の構成の編集画面ポップアップ -->
<div class="modal fade modal-drag  bd-example-modal-lg " id="ShiyoEditModal" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-ShiyoEditModal modal-lg" role="document">
        <div class="modal-content w-100">
            <div class="modal-header modal-header-ShiyoEditModal bg-primary pt-2 pb-2">
                <h6 class="modal-title text-white">材料構成編集</h6>
                <button type="button" class="close text-white ">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-1">
                <div class="mg-shiyo mg-title-top p-2 position-relative">
                    <div class="mg-title position-absolute">
                        <span>仕様 </span>
                        <span class="cursor-point p-2 d-inline btn-collapse" title="表示/非表示" data-toggle="collapse" href="#mgShiyo" role="button" aria-expanded="true" aria-controls="collapseExample">
                            <i class="fa fa-minus-square-o" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div id="mgShiyo" class="collapse show">
                        <div class="mt-3 pl-3"><span class="Koshu_Nmtxt"></span> ＞ <span class="Bui_NMtxt"></span> ＞ </div>

                        <div class="form-group row mb-1">
                            <label class="col-sm-2 col-form-label text-right">仕様名称</label>
                            <div class="col-sm-10 pl-0">
                                <input type="text" class="form-control p-1 h-30" disabled name="Shiyo_Nm">
                            </div>
                        </div>

                        <div class="form-group row mb-1">
                            <label class="col-sm-2 col-form-label text-right">メーカ名</label>
                            <div class="col-sm-10 pl-0">
                                <input type="text" class="form-control p-1 h-30" disabled name="Maker_Nm">
                            </div>
                        </div>

                        <div class="form-group row mb-1">
                            <label class="col-sm-2 col-form-label text-right">単位</label>
                            <div class="col-sm-10 pl-0">
                                {!! Form::select('Tani_ID', ["" => ""] + $tanis, null, ["class" => "form-control p-1 h-30 col-3 Unit"," disabled"]) !!}
                            </div>
                        </div>
                        <div class="form-group row mb-1">
                            <label class="col-sm-2 col-form-label text-right">単価表</label>
                            <div class="col-sm-10 pl-0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mg-zairyo mg-title-top p-2 position-relative">
                    <div class="mg-title position-absolute">
                        <span>材料</span>
                        <span class="cursor-point p-2 d-inline btn-collapse" title="表示/非表示" data-toggle="collapse" href="#mgZairyo" role="button" aria-expanded="true" aria-controls="collapseExample">
                            <i class="fa fa-minus-square-o" aria-hidden="true"></i>
                        </span>
                    </div>
                    <div id="mgZairyo" class="collapse show pl-3">
                        <div class="mt-4 position-relative">
                            <div class="position-absolute mg-zairyo-button">
                                <button type="button" class="btn btn-primary btnSaveZairyo">反映</button>
                                <button type="button" class="btn btn-primary btnZairyoRestore">戻す</button>
                            </div>
                            <div class="">材料/仕様リスト</div>
                            <div id="zairyo_selected" class="wijmo-custom wijmo-blue wijmo-height-220"></div>

                            <div class="mt-2">
                                <label class="m-0 cursor-point"><input type="radio" checked name="radioSearch" value="form-zairyo"><span class="pl-1">材料検索</span></label>
                                <label class="m-0 cursor-point"><input type="radio" class="ml-2" name="radioSearch" value="form-shiyo2"><span class="pl-1">仕様検索</span></label>
                            </div>
                            <form class="form-kosei form-zairyo position-relative">
                                {{ Form::hidden("page",0)}}
                                <div class="zairyo-loading d-none">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only"></span>
                                    </div>
                                </div>
                                <div id="zairyo" class="wijmo-red wijmo-custom wijmo-height-220"></div>
                                <div id="zairyoPage" class="mt-2"></div>
                            </form>
                            <form class="form-kosei form-shiyo2 position-relative d-none">
                                {{ Form::hidden("page",0)}}
                                <div class="shiyo-loading d-none">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only"></span>
                                    </div>
                                </div>
                                <div id="shiyo2" class="shiyo2 wijmo-custom wijmo-red wijmo-height-250"></div>
                                <div id="shiyoPage2" class="shiyoPage2 mt-2"></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{ Form::hidden('route-getListShiyo', route('getListShiyo')) }}
{{ Form::hidden('route-setMitsumoreShiyo', route('setMitsumoreShiyo')) }}
{{ Form::hidden('route-getMitsumoreMeisai', route('getMitsumoreMeisai')) }}
{{ Form::hidden('route-istore', route('m.store')) }}
{{ Form::hidden('route-mlist', route('m.list')) }}
{{ Form::hidden('route-getListZairyo', route('getListZairyo')) }}
{{ Form::hidden('route-getListZairyoSelected', route('getListZairyoSelected')) }}
{{ Form::hidden('route-zstore', route('z.store')) }}
@endsection