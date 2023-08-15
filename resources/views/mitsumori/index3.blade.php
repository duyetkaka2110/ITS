@extends('layouts.layout')
@section("title","見積詳細")
@section("css")
<link href="{{ URL::asset('css/jquery-ui.css') }}" rel="stylesheet" />
<link href="https://unpkg.com/tabulator-tables@5.5.0/dist/css/tabulator.min.css" rel="stylesheet">
<link href="{{ URL::asset('css/mitsumori.css') }}" rel="stylesheet" />
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet" />
<link href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css" rel="stylesheet" />
<style>
    .container-fluid {
        max-width: 2133px;
    }

    .tabulator-editing input {
        text-align: right;
    }

    .tabulator .tabulator-header .tabulator-col {
        justify-content: center !important;
        text-align: center;
    }

    #grid .tabulator-row .tabulator-cell {
        white-space: break-spaces;
    }

    .wj-align-right {
        justify-content: right !important;
    }

    .wj-align-left {
        justify-content: left !important;
    }

    .wj-align-center {
        justify-content: center !important;
        text-align: center !important;
    }

    .tabulator-menu-item:last-child {
        border-top: 1px solid #ccc;
    }

    .align-center-im {
        justify-content: center !important;
    }

    .tabulator .tabulator-header .tabulator-col.text-danger {
        color: inherit !important;
    }

    @media (hover: hover) and (pointer: fine) {
        .tabulator-row.tabulator-selectable:hover {
            background-color: #ebebeb;
            cursor: pointer;
        }
    }

    .tabulator-row {
        border-bottom: 1px solid #ccc;
    }

    .tabulator-row.tabulator-row-even {
        background-color: #f7f7f7;
    }

    .tabulator-row.tabulator-selected .bg-green,
    .tabulator-row.tabulator-selected {
        background-color: #80adbf !important;
        color: #fff !important;
    }

    .tabulator-row.tabulator-selected .text-danger {
        color: #fff !important;
    }

    #container {
        height: 500px;
        overflow: scroll;
    }

    .table-custom {
        height: 500px;
        overflow: scroll;
    }
</style>
@endsection
@section("js")
<script type="text/javascript" src="{{ URL::asset('js/js.cookie.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/jquery-ui.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('js/jquery.floatThead.js') }}"></script>
<script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.5.0/dist/js/tabulator.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>

<script>
    var list = <?php echo $list ?>;
    var header = <?php echo $header ?>;
    var cmd = <?php echo $cmd ?>;
    var headerShiyo = <?php echo $headerShiyo ?>;
    var headerShiyoSelected = <?php echo $headerShiyoSelected ?>;
    var headerZairyo = <?php echo $headerZairyo ?>;
    var headerZairyoSelected = <?php echo $headerZairyoSelected ?>;
</script>

<style>
    :root {
        --dt-row-selected: 128, 173, 191;
        --dt-row-selected-text: 255, 255, 255;
        --dt-row-selected-link: 9, 10, 11;
        --dt-row-stripe: 0, 0, 0;
        --dt-row-hover: 0, 0, 0;
        --dt-column-ordering: 0, 0, 0;
        --dt-html-background: white
    }
</style>
<script>
    $(document).ready(function() {
        // Get the keys (column names) of the first object in the JSON data
        let cols = [];
        $.each(header, function(key, val) {
            cols.push({
                title: val["name"],
                data: key,
                width: 149,
                // height: 65

            })
        })

        var table = new DataTable('#example', {
            autoWidth: false,
            columns: cols,
            data: list,
            sort: false,
            info: false,
            paging: false,
            searching: false,
            responsive: false,
            fixedColumns: {
                left: 7
            },
            // dom: 'Plfrtip'
        });
        $('#example').floatThead({
            position: 'absolute',
            scrollContainer: true
        });


        table.on('click', 'tbody tr', function(e) {
            let classList = e.currentTarget.classList;
            if (!classList.contains('selected')) {
                table.rows('.selected').nodes().each((row) => row.classList.remove('selected'));
                classList.add('selected');
            }
        });

        $('.not-mousedown').mousedown(function(event) {
            event.preventDefault();
        });
        //initialize table
        // var flex = new Tabulator("#grid", {
        //     height: "1011px",
        //     columnHeaderSortMulti: false,
        //     data: list,
        //     movableRows: false, //enable user movable rows
        //     selectable: true, //make rows selectable 
        //     selectableRangeMode: "click",
        //     layout: "fitColumns",
        //     rowHeight: 70,
        //     rowContextMenu: function(e, row) {
        //         flex.deselectRow();
        //         row.select();
        //         //add context menu to rows
        //         return rowMenu;
        //         // console.info(table.getSelectedData())
        //     },
        //     columns: getheaderCol(header)
        // });
        // flex.on("rowDblClick", function(e, row) {
        //     e.preventDefault();
        //     // show popup
        //     console.info(flex)
        // });


        // メニュー一覧表示
        let rowMenu = [];
        let htmlMenu = "";
        $.each(cmd, function(key, value) {
            rowMenu.push({
                label: '<i class="fa ' + value.icon + ' pr-2" aria-hidden="true"></i>' + value.name,
                action: function(e, row) {

                }
            })
            if (value.cmd != cmd.cmdExit.cmd) {
                let disable = (value.cmd == cmd.cmdPaste.cmd || value.cmd == cmd.cmdPasteNew.cmd ||
                    value.cmd == cmd.cmdTotal.cmd || value.cmd == cmd.cmdTotalKe.cmd || value.cmd == cmd.cmdTotalGo.cmd) ? "disabled" : "";
                htmlMenu += '<button class="btn btn-menu-item ' + value.cmd + '" ' + disable + ' data-cmd="' + value.cmd + '"><i class="fa ' + value.icon + ' pr-2" aria-hidden="true"></i>' + value.name + "</button>"
            }
        })
        $(".mg-menu").html(htmlMenu)
        $(document).on("click", ".btn-menu-item", function() {
            selectedValue = $(this).attr("data-cmd");
            MenuMsg(selectedValue)
        })
    })

    function getheaderCol(header, flagDrag = false) {
        var headerCol = [{
            rowHandle: true,
            formatter: "handle",
            headerSort: false,
            frozen: true,
            width: 30,
            minWidth: 30
        }];
        if (!flagDrag) headerCol = [];
        let flagFrozen = false;
        // header row create
        $.each(header, function(key, value) {
            let cssClass = ("class" in value ? value["class"] : "");
            temp = {
                field: key,
                title: value["name"],
                width: value["width"] ? value["width"] : 100,
                headerSort: false,
                vertAlign: "middle",
                headerWordWrap: true,
                hozAlign: "hozAlign" in value ? value["hozAlign"] : "left",
                editor: "editor" in value ? value["editor"] : false,
                editor: "editor" in value ? "input" : false,
                editor: "validator" in value ? value["validator"] : [],
                formatter: "money",
                formatterParams: {
                    negativeSign: true,
                    precision: false,
                }
            };
            if ("class" in value) {
                temp["cssClass"] = key + " " + $.trim(value["class"]);
            }
            if (!flagFrozen) temp["frozen"] = true
            if (key == "SpecName3") {
                flagFrozen = true;
            }
            headerCol.push(temp)
        })
        return headerCol;
    }
</script>

@endsection
@section('content')
<div class="page"></div>
<div class="container-fluid">
    <div class="mg-menu"></div>
    <div id="grid" class="not-"></div>
</div>
<div id="container" class="display">
    <table id="example" class="display bg-white nowrap" style="width:100%">
    </table>
</div>
<input type="hidden" class="route-mitsumore-action" value="{{ route('mitsumore.action') }}" />

@endsection