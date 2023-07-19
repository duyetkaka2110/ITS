
document.readyState === 'complete' ? init() : window.onload = init;
function init() {
    var ajaxMethod = "get";
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
        frozenColumns: 7,
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
        if (action in cmd) {
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
                    type: ajaxMethod,
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
        } else {
            // 工事仕様の選択画面閉じる
            if (action == "close") {
                $("#InvoiceModal").modal("hide")
            }
        }
    })

    // 編集ポップアップ画面表表示
    flex.hostElement.addEventListener('dblclick', function (e) {
        if (e.returnValue) {
            e.preventDefault();
        }
        flex_selected = flex.selectedItems[0];
        modal = "#InvoiceModal";
        $(modal + " input[name=id]").val(flex_selected["id"])
        $(modal + " input[name=DetailNo]").val("No." + flex_selected["DetailNo"])
        $(modal + " input[name=FirstName]").val(flex_selected["FirstName"])
        $(modal + " input[name=StandDimen]").val(flex_selected["StandDimen"])
        $(modal + " input[name=MakerName]").val(flex_selected["MakerName"])
        $(modal + " select[name=UnitOrg_ID]").val(flex_selected["UnitOrg_ID"])
        $(modal + " input[name=Quantity]").val(flex_selected["Quantity"])
        $(modal + " input[name=UnitPrice]").val(flex_selected["UnitPrice"])
        $(modal + " input[name=Amount]").val(flex_selected["Amount"])
        $(modal + " input[name=Note]").val(flex_selected["Note"])
        $(modal + " input[name=Type]").val(flex_selected["Koshu_ID"])
        $(modal + " input[name=PartName]").val(flex_selected["Bui_ID"])
        dataSearch = [];
        dataSearch.push({ name: "page", value: 1 })
        dataSearch.push({ name: "Koshu_ID", value: flex_selected["Koshu_ID"] })
        dataSearch.push({ name: "Bui_ID", value: flex_selected["Bui_ID"] })
        dataSearch.push({ name: "Shiyo_Shubetsu_ID", value: flex_selected["Shiyo_Shubetsu_ID"] ? flex_selected["Koshu_ID"] + '_' + flex_selected["Shiyo_Shubetsu_ID"] : '' })
        dataSearch.push({ name: "Shiyo_Nm", value: null })
        dataSearch.push({ name: "Invoice_ID", value: flex_selected["id"] })
        $.ajax({
            type: ajaxMethod,
            data: dataSearch,
            url: $("input[name=route-getMitsumoreDetail]").val(),
            success: function (res) {
                if (res["status"]) {
                    shiyo_selected_flex.itemsSource = res["data"]
                    shiyo_flex.itemsSource = new wijmo.collections.ObservableArray(res["dataShiyo"]["data"]);
                    $("#shiyoPage").html(res["dataShiyo"]["pagi"])
                }
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
            nextItemNo: last < flex.itemsSource.length - 1 ? flex.itemsSource[last].No : 0,
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
        if (row < flex.itemsSource.length - 1)
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
            temp = {
                binding: key,
                header: value["name"],
                width: value["width"] ? value["width"] : 100,
                wordWrap: true,
                cssClass: value["class"],
                isReadOnly: key == "AtariSuryo" ? false : true,
                format: "n0"
            };
            if (key == 'AtariSuryo') {
                temp["format"] = "n1";
            }
            if (key == "M_Tanka_IPN2") {
                temp["aggregate"] = "Sum";
            }
            headerCol.push(temp)
        })
        return headerCol;
    }

    // 工事仕様の選択画面
    var shiyo_selected_flex = new wijmo.grid.FlexGrid("#shiyo_selected", {
        itemsSource: new wijmo.collections.ObservableArray([]),
        columns: getheaderCol(headerShiyoSelected),
        autoGenerateColumns: false,
        selectionMode: 'Row',
        allowSorting: false,
        allowDragging: wijmo.grid.AllowDragging.Both,
        // headersVisibility: "Column",
        itemFormatter: function (panel, r, c, cell) {
            if (panel.cellType == wijmo.grid.CellType.Cell) {
                if (c == 0) {
                    cell.innerHTML = '<a href=""  title="材料構成編集画面"><i class="fa fa-wrench cursor-point  pl-2 pr-2  " aria-hidden="true"></i></a>'
                        + '<a href="" title="組物仕様編集画面"><i class="fa fa-file-text cursor-point btnd pl-2 pr-2"  aria-hidden="true"></i></a>'
                        + '<i class="fa fa-trash cursor-point btnDel  pl-2 pr-2 " title="行削除" aria-hidden="true"></i>';
                }
                if (c == 2) {
                    panel.rows[r].dataItem["Sort_No"] = r + 1;
                    cell.innerHTML = r + 1;
                }
                if (c == 8) {
                    dataItem = panel.rows[r].dataItem;
                    dataItem["M_Tanka_IPN2"] = dataItem["M_Tanka_IPN"] * getNumberData(dataItem["AtariSuryo"])
                    dataItem["Z_Tanka_IPN2"] = dataItem["Z_Tanka_IPN"] * getNumberData(dataItem["AtariSuryo"])
                    dataItem["R_Tanka_IPN2"] = dataItem["R_Tanka_IPN"] * getNumberData(dataItem["AtariSuryo"])
                }
            }
        },
        updatedView: function (s, e) {
            $.each(s.rows, function (r, value) {
                s.rows[r].dataItem["Sort_No"] = r + 1;
                // shiyo_selected_flex.itemsSource[r]["Sort_No"] = r + 1;
            })
            total = wijmo.getAggregate("Sum", shiyo_selected_flex.itemsSource, "M_Tanka_IPN2");
            $('input[name="UnitPrice"]').val(numberFormat(total, "n0"))
            $('input[name="Amount"]').val(numberFormat(total * $('input[name="Quantity"]').val()))
        },
        _cellEditEnding: (s, e) => {
            let col = s.columns[e.col];
            if (col.binding == 'AtariSuryo') {
                if (s.activeEditor) {
                    let value = wijmo.changeType(s.activeEditor.value, wijmo.DataType.Number, col.format);
                    if (isNaN(value) || !wijmo.isNumber(value) || value < 0) { // prevent negative sales/expenses
                        e.cancel = true;
                        console.info('Please enter a positive amount');
                    } else {
                        value = parseFloat(value).toFixed(1);
                        s.activeEditor.value = value;
                        changedItem = shiyo_selected_flex.itemsSource[e.row];
                        changedItem["M_Tanka_IPN2"] = changedItem["M_Tanka_IPN"] * value;
                        changedItem["R_Tanka_IPN2"] = changedItem["R_Tanka_IPN"] * value;
                        changedItem["Z_Tanka_IPN2"] = changedItem["Z_Tanka_IPN"] * value;
                        shiyo_selected_flex.collectionView.refresh();
                    }
                } else {
                    value = 0;
                    changedItem = shiyo_selected_flex.itemsSource[e.row];
                    changedItem["M_Tanka_IPN2"] = 0;
                    changedItem["R_Tanka_IPN2"] = 0;
                    changedItem["Z_Tanka_IPN2"] = 0;
                    changedItem["AtariSuryo"] = value;
                    shiyo_selected_flex.collectionView.refresh();
                }
            }
        },
        get cellEditEnding() {
            return this._cellEditEnding;
        },
        set cellEditEnding(value) {
            this._cellEditEnding = value;
        },
    })

    $(".btnSave").on("click", function () {
        let btnClick = $(this).attr("data-btn");
        if (btnClick == "btnSaveNew" && !$("input[name=RowAdd]").val()) {
            dispMessageModal("追加行数を入力してください。")
        } else {
            var shiyo_selected_form = [];
            shiyo_selected_form.push({ name: "btn", value: btnClick });
            $.each(shiyo_selected_flex.itemsSource, function (key, value) {
                shiyo_selected_form.push({ name: "Shiyo_ID[]", value: value["Shiyo_ID"] });
                shiyo_selected_form.push({ name: "AtariSuryo[]", value: value["AtariSuryo"] != undefined ? value["AtariSuryo"] : null });
                shiyo_selected_form.push({ name: "Sort_No[]", value: value["Sort_No"] });
            });
            $.ajax({
                type: ajaxMethod,
                data: $.merge($(".form-selected").serializeArray(), shiyo_selected_form),
                url: $("input[name=route-istore]").val(),
                success: function (res) {
                    if (res["status"]) {
                        dataSelected = { first: flex.selectedItems[0]["DetailNo"] - 1 };
                        scrollPosition = flex.scrollPosition;
                        flex.itemsSource = new wijmo.collections.ObservableArray($.parseJSON(res["data"]));
                        if (btnClick == "btnSaveNew") {
                            dataSelected = { first: flex.itemsSource.length - 1 };
                            scrollPosition.y = -flex.scrollSize.height;
                        }
                        flex.scrollPosition = scrollPosition;
                        $("#InvoiceModal").modal("hide")
                        setRowSelected(flex, dataSelected)
                    }
                }
            });
        }
    })
    function numberFormat(number, str = null) {
        return wijmo.Globalize.format(number, str ? str : "n0")
    }
    function getNumberData($number) {
        return $number ? $number : 0;
    }
    // 数量更新の時
    $('input[name="Quantity"]').on("change", function () {
        $('input[name="Amount"]').val(numberFormat($(this).val() * wijmo.Globalize.parseFloat($('input[name="UnitPrice"]').val())))
    })
    // 削除ボタンをクリック時
    $(document).on("click", ".btnDel", function (e) {
        let viewSelected = shiyo_selected_flex.collectionView;
        viewSelected.remove(viewSelected.currentItem);
        shiyo_selected_flex.collectionView.refresh()
    })
    let dataSearch = [];
    var layoutDefinition = [];
    var headerLayoutDefinition = [];
    getHeaderColShiyo(headerShiyo);
    function getHeaderColShiyo(headerShiyo) {
        // create the MultiRow
        // header row create
        $.each(headerShiyo, function (key, value) {
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
    }
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
            // 検索条件保存
            if (dataSearch[1] != undefined) {
                var tag = $("select[name=Koshu_ID] option[value='" + dataSearch[1].value + "']").attr("data-bui");
                $("select[name=Koshu_ID]").val(dataSearch[1].value);
                $("select[name=Bui_ID] option.a").hide();
                $("select[name=Bui_ID] .a" + tag).show();
                $("select[name=Shiyo_Shubetsu_ID] option.a").hide();
                $("select[name=Shiyo_Shubetsu_ID] .a" + dataSearch[1].value).show();
            }
            if (dataSearch[2] != undefined) {
                $("select[name=Bui_ID]").val(dataSearch[2].value);
                if (dataSearch[1] != undefined) {
                }
            }
            if (dataSearch[3] != undefined) $("select[name=Shiyo_Shubetsu_ID]").val(dataSearch[3].value);
            if (dataSearch[4] != undefined) $("input[name=Shiyo_Nm]").val(dataSearch[4].value);

        }
    });
    headerRow1 = shiyo_flex.columnHeaders.rows[1];
    headerRow1.height = 45;
    headerRow1.cssClass = "header-red-bold"
    shiyo_flex.columnHeaders.rows[2].cssClass = "header-red-normal"

    // 赤画面行にクリック時,青画面に追加
    shiyo_flex.hostElement.addEventListener('dblclick', function (e) {
        ht = shiyo_flex.hitTest(e);
        if (ht.cellType == 1) {
            selectedItem = JSON.parse(JSON.stringify(shiyo_flex.itemsSource[ht.row]));
            shiyo_selected_flex.itemsSource.push(selectedItem);
            shiyo_selected_flex.collectionView.refresh();
            shiyo_selected_flex.select(shiyo_selected_flex.itemsSource.length, -1)
            if (e.returnValue) {
                e.preventDefault();
            }
        }
    })
    // 検索条件更新の時
    $(document).on("change", ".btn-search", function (e) {
        // 種別
        if (e.target.name == "Koshu_ID") {
            $("select[name=Bui_ID]").val('');
            $("select[name=Shiyo_Shubetsu_ID]").val('');
            $("input[name=Shiyo_Nm]").val('');
        }
        $(".form-shiyo input[name=page]").val(1);
        shiyoAjax(shiyo_flex);
    })

    // 工事仕様の選択画面閉じるのポップアップ表示
    $("#InvoiceModal .close").on("click", function () {
        dispConfirmModal("編集中のデータはまだ保存していません。<br>編集中の内容を破棄し、見積明細画面に戻りますか？", "close");
    })

    // ページング
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
    });
    function shiyoAjax(shiyo_flex) {
        dataSearch = $(".form-shiyo").serializeArray();
        $.ajax({
            type: ajaxMethod,
            data: dataSearch,
            url: $("input[name=route-getListShiyo]").val(),
            success: function (res) {
                if (res["status"]) {
                    shiyo_flex.itemsSource = new wijmo.collections.ObservableArray(res["data"]);
                    $("#shiyoPage").html(res["pagi"])
                } else {
                    shiyo_flex.itemsSource = [];
                    $("#shiyoPage").html("")
                }
            }
        });
    }
    // 表示/非表示アイコン
    $(".btn-collapse").on("click", function () {
        $(this).find('i').toggleClass('fa-plus-square-o ');
    })
}
