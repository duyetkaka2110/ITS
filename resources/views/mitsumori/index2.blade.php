@extends('layouts.layout')
@section("title","見積詳細")
@section("css")
<link href="{{ URL::asset('css/jquery-ui.css') }}" rel="stylesheet" />
<link href="https://unpkg.com/tabulator-tables@5.5.0/dist/css/tabulator.min.css" rel="stylesheet">
<link href="{{ URL::asset('css/mitsumori.css') }}" rel="stylesheet" />
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet" />
<link href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.dataTables.min.css" rel="stylesheet" />
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
<script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.5.0/dist/js/tabulator.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>

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

.dataTables_info{
    display: none;
}
    </style>
<script>
    $(document).ready(function() {
        // Get the keys (column names) of the first object in the JSON data
        console.info(list)
        let cols = [];
        $.each(list[0], function(key, val) {
            cols.push({
                title: key,
                data: key
            })
        })

        new DataTable('#example', {
            columns: cols,
            data: list,
            fixedHeader: true,
            paging: false,
            searching: false,
            select: {
                style: "single",
                className: "row-selected"
            },
            responsive: true,
            searchPanes: {
                controls: false
            },
            dom: 'Plfrtip'
        });
        $('.dataTables_info:eq(1)').hide();
        // Function to convert JSON data to HTML table
        function convert() {

            // Sample JSON data
            let jsonData = list;

            // Get the container element where the table will be inserted
            let container = $("#table");

            // Create the table element
            let table = $("<table class='table table-striped table-bordered table-custom'>");

            // Get the keys (column names) of the first object in the JSON data
            let cols = Object.keys(jsonData[0]);

            // Create the header element
            let thead = $("<thead>");
            let tr = $("<tr>");

            // Loop through the column names and create header cells
            $.each(cols, function(i, item) {
                let th = $("<th>");
                th.text(item); // Set the column name as the text of the header cell
                tr.append(th); // Append the header cell to the header row
            });
            thead.append(tr); // Append the header row to the header
            table.append(tr) // Append the header to the table

            // Loop through the JSON data and create table rows
            $.each(jsonData, function(i, item) {
                let tr = $("<tr>");

                // Get the values of the current object in the JSON data
                let vals = Object.values(item);

                // Loop through the values and create table cells
                $.each(vals, (i, elem) => {
                    let td = $("<td>");
                    td.text(elem); // Set the value as the text of the table cell
                    tr.append(td); // Append the table cell to the table row
                });
                table.append(tr); // Append the table row to the table
            });
            container.append(table) // Append the table to the container element
        }
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
<div id="container" class="display">
    <table id="example" class="display" style="width:100%">
    </table>
</div>
<div class="page"></div>
<div class="container-fluid">
    <div class="mg-menu"></div>
    <div id="grid" class="not-"></div>
</div>
<input type="hidden" class="route-mitsumore-action" value="{{ route('mitsumore.action') }}" />

@endsection