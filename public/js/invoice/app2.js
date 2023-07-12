
document.readyState === 'complete' ? init() : window.onload = init;
function init() {
    var datacopy = null;
    // create Grid table
    var flex = new wijmo.grid.FlexGrid("#grid", {
        loadedRows: function (s, e) {
            for (var i = 0; i < s.rows.length; i++) {
                var row = s.rows[i];
                if (row.dataItem.FirstName == cmd.cmdTotal.text) {
                    row.cssClass = 'row-total';
                }
            }
        },
        itemsSource: new wijmo.collections.ObservableArray(list),
        columns: getheaderCol(header),
        autoGenerateColumns: false,
        frozenColumns: 6,
        isReadOnly: true,
        allowResizing: 'BothAllCells',
        selectionMode: 'RowRange',
        allowSorting: false,
        headersVisibility: "Column",
    })
    // set css style 
    flex.columnHeaders.rows[0].cssClass = "wj-align-center"
    flex.columnHeaders.rows[0].wordWrap = true;
    flex.columnHeaders.rows.defaultSize = 55;
    flex.rows.defaultSize = 59;
    console.info(flex)
    flex.hostElement.addEventListener('contextmenu', (e) => {
        ht = flex.hitTest(e);
        // set select rows style
        if (ht.cellType == 2) {
            // show on row header only
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true);
    // メニュー一覧表示
    let itemsSource = [];
    $.each(cmd, function (key, value) {
        if (value.cmd == cmd.cmdExit.cmd) {
            itemsSource.push({
                header: '<span class="wj-separator"></span>'
            })
        }
        itemsSource.push({
            header: '<i class="fa ' + value.icon + ' pr-2" aria-hidden="true"></i>' + value.name,
            cmd: value.cmd,
        })
    })
    let menu = new wijmo.input.Menu(document.createElement('div'), {
        displayMemberPath: 'header',
        selectedValuePath: 'cmd',
        dropDownCssClass: 'ctx-menu',
        itemsSource: itemsSource,
        _itemClicked: () => {
            let msg;
            // 確認メッセージ取得
            if (menu.selectedValue in cmd) {
                let totalRowsRun = flex.selection.rowSpan;

                if (menu.selectedValue == cmd.cmdPaste.cmd || menu.selectedValue == cmd.cmdPasteNew.cmd) {
                    totalRowsRun = datacopy.count;
                }
                if (menu.selectedValue == cmd.cmdTotal.cmd) {
                    totalRowsRun = 1;
                }
                let lineNo = "行No:" + (flex.selection.row2 + 1) + "から" + totalRowsRun + "行";

                if (cmd[menu.selectedValue].msg) {
                    msg = lineNo + cmd[menu.selectedValue].msg;
                } else {
                    if (menu.selectedValue == cmd.cmdCopy.cmd) {
                        datacopy = getSeletedItems(flex);
                    }
                }
            }
            if (msg) {
                // 確認メッセージ表示
                dispConfirmModal(msg + "ます。よろしいですか？", menu.selectedValue);
            }
        },
        get itemClicked() {
            return this._itemClicked;
        },
        set itemClicked(value) {
            this._itemClicked = value;
        },
    });
    // disable menu when not have data copy
    menu.isDroppedDownChanging.addHandler((s, e) => {
        s.dropDown.childNodes.forEach(item => {
            if (!datacopy) {
                if (item.innerText == cmd.cmdPaste.name || item.innerText == cmd.cmdPasteNew.name) {
                    wijmo.addClass(item, 'wj-state-disabled');
                }
            } else {
                wijmo.removeClass(item, 'wj-state-disabled');
            }
            // 小計行追加disabled
            if (item.innerText == cmd.cmdTotal.name) {
                if (flex.selection.topRow == 0) {
                    wijmo.addClass(item, 'wj-state-disabled');
                } else {
                    wijmo.removeClass(item, 'wj-state-disabled');
                }
            }
        });
    });
    // use it as a context menu for one or more elements
    let els = document.querySelectorAll('.has-ctx-menu');
    for (let i = 0; i < els.length; i++) {
        els[i].addEventListener('contextmenu', e => {
            menu.owner = wijmo.closest(e.target, '.has-ctx-menu');
            if (menu.owner) {
                e.preventDefault();
                menu.show(e);
            }
        }, true);
    }

    // 削除確認ボタン時
    $("#ConfirmModal .btnPopupOk").on("click", function () {
        let dataAction = false;
        var action = $(this).attr("data-action")
        var dataSelected = getSeletedItems(flex, action);
        let row = dataSelected.first;
        // 削除
        if (action == cmd.cmdDel.cmd) {
            row = dataSelected.last + 1;
        }

        // 貼り付け
        if (action == cmd.cmdPaste.cmd && datacopy) {
            row = dataSelected.last + 1;
            // 選択行後、id保存
            dataAction = datacopy;
        }
        // コピーした行の挿入
        if (action == cmd.cmdPasteNew.cmd && datacopy) {
            dataAction = datacopy;
        }

        // DB更新
        if (dataSelected.count) {
            $.ajax({
                type: "get",
                data: {
                    action: action,
                    dataCopy: dataAction,
                    dataSelected: dataSelected,
                    dataNoChange: getNextHaveNo(flex, row),// 選択一覧の下、No更新のidを取得
                },
                url: $(".route-invoice-action").val(),
                success: function (res) {
                    if (!res["status"]) {
                        // エラー表示
                        dispMessageModal(res["msg"])
                    } else {
                        // 一覧画面再表示
                        scrollPosition = flex.scrollPosition;
                        flex.itemsSource = new wijmo.collections.ObservableArray($.parseJSON(res["data"]));
                        flex.scrollPosition = scrollPosition
                        setRowSelected(flex, dataSelected)
                    }
                }
            });
        }
    })

    // 編集ポップアップ画面表表示
    flex.hostElement.addEventListener('dblclick', function (e) {
        if (e.returnValue) {
            e.preventDefault();
        }
        flex_selected = flex.selectedItems[0];
        console.info(flex_selected);
        modal = "#InvoiceModal";
        $(modal + " input[name=DetailNo]").val("No." + flex_selected["DetailNo"])
        $(modal + " input[name=FisrtName]").val(flex_selected["FisrtName"])
        $(modal + " input[name=StandDimen]").val(flex_selected["StandDimen"])
        $(modal + " input[name=MakerName]").val(flex_selected["MakerName"])
        $(modal + " select[name=UnitOrg_ID]").val(flex_selected["UnitOrg_ID"])
        $(modal + " input[name=Quantity]").val(flex_selected["Quantity"])
        $(modal + " input[name=UnitPrice]").val(flex_selected["UnitPrice"])
        $(modal + " input[name=Amount]").val(flex_selected["Amount"])
        $(modal + " input[name=Note]").val(flex_selected["Note"])

        $.ajax({
            type: "get",
            data: { Invoice_ID: flex_selected["id"] },
            url: $("input[name=route-getMitsumoreDetail]").val(),
            success: function (res) {
                if (res["status"])
                    shiyo_selected_flex.itemsSource = res["data"]//new wijmo.collections.ObservableArray(res["data"]),
            }
        });
        $(modal).modal();
    })

    const sortOnKey = (key, string, desc) => {
        const caseInsensitive = string && string === "CI";
        return (a, b) => {
            a = caseInsensitive ? a[key].toLowerCase() : a[key];
            b = caseInsensitive ? b[key].toLowerCase() : b[key];
            if (string) {
                return desc ? b.localeCompare(a) : a.localeCompare(b);
            }
            return desc ? b - a : a - b;
        }
    };
    function getSeletedItems(flex, actionCheck = null) {
        var selectedItems = flex.selectedItems;
        let i, first = flex.selection.topRow,
            last = flex.selection.bottomRow;
        if (actionCheck == cmd.cmdPaste.cmd || actionCheck == cmd.cmdPasteNew.cmd) {
            selectedItems = [];
            // from first row, get list items same copy clipboard items
            for (i = 0; i < datacopy.count; i++) {
                selectedItems.push(flex.itemsSource[first + i])
            }
            // set last row number
            last += (i - 1);
        }
        return getDataSelected(selectedItems, first, last);
    }

    // 選択行データ取得
    function getDataSelected(selectedItems, first, last) {
        selectedItems.sort(sortOnKey("DetailNo", false, false))
        var NoItems = selectedItems.map(el => el.No);
        var dataSelected = {
            count: selectedItems.length,
            id: selectedItems.map(el => el.id),
            DetailNo: selectedItems.map(el => el.DetailNo),
            No: NoItems,
            haveNoNull: NoItems.some(v => v === null) ? 1 : null,
            first: first,// first selected row
            last: last, // last selected row
            firstNo: selectedItems[0].No,
            lastNo: selectedItems[selectedItems.length - 1].No,
            prevItemNo: first > 0 ? flex.itemsSource[first - 1].No : 0,
            nextItemNo: last < flex.itemsSource.length ? flex.itemsSource[last + 1].No : 0,
        }
        return dataSelected;
    }
    // set selected row style
    function setRowSelected(flex, dataSelected) {
        flex.selection = new wijmo.grid.CellRange(dataSelected.first, 0, dataSelected.first, 1)
    }
    // get list items after selected rows, No can change
    function getNextHaveNo(flex, row) {
        let dataNoChange = [];
        while (flex.itemsSource[row].No) {
            dataNoChange.push(flex.itemsSource[row].id);
            row++;
        }
        return dataNoChange;
    }

    function getheaderCol(header) {
        var headerCol = [];
        // header row create
        $.each(header, function (key, value) {
            headerCol.push({
                binding: key,
                header: value["name"],
                width: value["width"] ? value["width"] : 100,
                wordWrap: true,
                cssClass: value["class"]
            })
        })
        return headerCol;
    }

    // 工事仕様の選択画面
    var headerColShiyo = [];
    var layoutDefinition = [];
    var headerLayoutDefinition = [];
    // header row create
    $.each(headerShiyo, function (key, value) {
        headerColShiyo.push({
            binding: key,
            header: value["name"],
            width: value["width"] ? value["width"] : 100,
            wordWrap: true,
            cssClass: value["class"]
        })
        width = value["width"] ? value["width"] : 100;

        layoutDefinition.push(
            {
                colspan: 1, header: value["line1"], align: 'center', width: width, cssClass: value["class"], dataMap: [1, 2, 3, 4], cells: [
                    { binding: key, header: value["name"], width: width, cssClass: value["class"] },
                ]
            });
        headerLayoutDefinition.push(
            {
                cells: [
                    { colspan: 1, header: value["line1"], align: 'center', width: width, cssClass: value["class"], dataMap: [1, 2, 3, 4] },//header line 1
                    { binding: key, header: value["name"], align: 'center', width: width, cssClass: value["class"] }, // header line 2
                ]
            });
    })
    console.info(headerShiyo)
    var headerColShiyoSelected = [];
    $.each(headerShiyoSelected, function (key, value) {
        headerColShiyoSelected.push({
            binding: key,
            header: value["name"],
            width: value["width"] ? value["width"] : 100,
            wordWrap: true,
            cssClass: value["class"]
        })
    })
    var shiyo_selected_flex = new wijmo.grid.FlexGrid("#shiyo_selected", {
        itemsSource: [],
        columns: headerColShiyoSelected,
        autoGenerateColumns: false,
        isReadOnly: true,
        selectionMode: 'Row',
        allowSorting: false,
        headersVisibility: "Column",
        imeEnabled: true,
    })
    let dataSearch = [];
    // create the MultiRow
    let shiyo_flex = new wijmo.grid.multirow.MultiRow('#shiyo', {
        layoutDefinition: layoutDefinition,
        headerLayoutDefinition: headerLayoutDefinition,
        itemsSource: [],//new wijmo.collections.ObservableArray(shiyo),
        alternatingRowStep: 0,
        formatItem: function (s, e) {
            /* show HTML in column headers */
            if (e.panel == s.columnHeaders) {
                e.cell.innerHTML = e.cell.textContent;
            }
        },
        autoGenerateColumns: false,
        isReadOnly: true,
        selectionMode: 'Row',
        allowSorting: false,
        headersVisibility: "Column",
        imeEnabled: true,
        updatedView: function () {
            if (dataSearch.length == 5) {
                // 検索条件保存
                $("select[name=Koshu_ID]").val(dataSearch[1]["value"]);
                $("select[name=Bui_ID]").val(dataSearch[2]["value"]);
                $("select[name=Shiyo_Shubetsu_ID]").val(dataSearch[3]["value"]);
                $("input[name=Shiyo_Nm]").val(dataSearch[4]["value"]);
            }
        }
    });
    headerRow1 = shiyo_flex.columnHeaders.rows[1];
    headerRow1.height = 45;
    headerRow1.cssClass = "header-red-bold"
    shiyo_flex.columnHeaders.rows[2].cssClass = "header-red-normal"

    // 赤画面行にクリック時,青画面に追加
    shiyo_flex.hostElement.addEventListener('dblclick', function (e) {
        if (e.returnValue) {
            e.preventDefault();
        }
        console.info(flex_selected)
        Shiyo_ID = shiyo_flex.selectedItems[0]["Shiyo_ID"];
        Invoice_ID = flex_selected["id"];
        $.ajax({
            type: "get",
            data: {
                Shiyo_ID: shiyo_flex.selectedItems[0]["Shiyo_ID"],
                Invoice_ID: flex_selected["id"]
            },
            url: $("input[name=route-setMitsumoreShiyo]").val(),
            success: function (res) {
                if(res["status"]){
                    shiyo_selected_flex.itemsSource = res["data"]
                }
            }
        });
    })
    // console.info(shiyo_flex);
    $(document).on("change", ".btn-search", function (e) {

        if (e.target.name == "Koshu_ID") {
            $("select[name=Bui_ID]").val("");
            $("select[name=Shiyo_Shubetsu_ID]").val("");
            $("input[name=Shiyo_Nm]").val("");
        }
        if (e.target.name == "Bui_ID") {
            $("select[name=Shiyo_Shubetsu_ID]").val("");
        }
        shiyoAjax(shiyo_flex);
    })
    $(document).on("click", "#shiyoPage .page-link", function (e) {
        e.preventDefault();
        page = getQueryStringValue($(this).attr('href'), "page")
        if (page) {
            $(".form-shiyo input[name=page]").val(page);
            shiyoAjax(shiyo_flex);

        }
    })
    $('.form-shiyo').bind("keypress", function (e) {
        if (e.keyCode == 13) {
            if (e.target.name == "Shiyo_Nm") {
                shiyoAjax(shiyo_flex);
            }
            e.preventDefault();
            return false;
        }
    }); shiyoAjax(shiyo_flex)
    function shiyoAjax(shiyo_flex) {
        dataSearch = $(".form-shiyo").serializeArray();
        $.ajax({
            type: "get",
            data: dataSearch,
            url: $("input[name=route-getListShiyo]").val(),
            success: function (res) {
                shiyo_flex.itemsSource = res["data"]//new wijmo.collections.ObservableArray(res["data"]),
                $("#shiyoPage").html(res["pagi"])
            }
        });
        // if (dataSearch.length == 5 && dataSearch[1]["value"] && dataSearch[2]["value"] && dataSearch[3]["value"]) {
        //     $.ajax({
        //         type: "get",
        //         data: dataSearch,
        //         url: $("input[name=route-getListShiyo]").val(),
        //         success: function (res) {
        //             shiyo_flex.itemsSource = res["data"]//new wijmo.collections.ObservableArray(res["data"]),
        //             $("#shiyoPage").html(res["pagi"])
        //         }
        //     });
        // } else {
        //     shiyo_flex.itemsSource = [];
        //     $("#shiyoPage").html("")
        // }
    }
}
