document.readyState === 'complete' ? init() : window.onload = init;
function init() {
    let UnitPrice = $('input[name="UnitPrice"]');
    let Amount = $('input[name="Amount"]');
    let Quantity = $('input[name="Quantity"]');
    let MitsumoriModal = "#MitsumoriModal";
    let ShiyoEditModal = "#ShiyoEditModal";
    let firstUnitPrice;
    let formZairyo = ".form-zairyo";

    var listModalSavePosition = ["MitsumoriModal", "ShiyoEditModal"];
    // 移動した位置」「変更したサイズ」を記憶しておき、次に同じポップアップウィンドウを開いた際、その位置、サイズを再現する
    $.each(listModalSavePosition, function (key, modal) {
        if (Cookies.get(modal + 'Header') !== undefined) {
            $("#" + modal).attr("style", Cookies.get(modal + 'Header'));
        }
        if (Cookies.get(modal + 'Dialog') !== undefined) {
            $("#" + modal + " .modal-dialog").attr("style", Cookies.get(modal + 'Dialog'));
            $("#" + modal + " .modal-content").attr("style", Cookies.get(modal + 'Content'));
        }
        var windowX = $(window).width(); //determines the width of the browser window
        var windowY = $(window).height();//determines the height of the browser window
        var imageX = 2000; //put the width of your image here. my image was 2000x1000
        var imageY = 1000;//image height
        // ポップアップウィンドウを全て,移動可,サイズ変更可にする。
        $("#" + modal).draggable({
            handle: ".modal-header-" + modal,
            stop: function (e) {
                Cookies.set($(this).attr("id") + "Header", e.target.getAttribute("style").replace("display: block;", ""));
            }
        });
        $("#" + modal + " .modal-content").resizable({
            alsoResize: ".modal-dialog-" + modal,
            minHeight: 150,
            stop: function (e) {
                Cookies.set($(this).parent().parent().attr("id") + "Dialog", $(this).parent().attr("style"));
                Cookies.set($(this).parent().parent().attr("id") + "Content", $(this).attr("style"));
            }
        });
    })
    $(document).keydown(function (e) {
        if (e.keyCode == 116 && e.ctrlKey) {
            // ctrl + f5: ポップアップウィンドウを全て,移動可,サイズ保存をクリア
            Object.keys(Cookies.get()).forEach(function (cookieName) {
                Cookies.remove(cookieName);
            });
        }
    });
    // 数量がNullであれば、デフォルト値として「1」をセットし、金額も更新する。
    $("input[name=UnitPrice]").on("change", function () {
        if ($("input[name=Quantity]").val() == 0 && shiyo_selected_flex.rows.length == 0 && this.value != 0) {
            $("input[name=Quantity]").val(1)
        }
    })
    $(".numeric").on("change", function () {
        $(this).val(parseInt(!this.value ? 0 : this.value).toLocaleString())
    })
    $(".floatic").on("change", function () {
        this.value = parseFloat(!this.value ? 0.0 : this.value).toFixed(1)
    })
    $(document).on("input", ".numeric", function () {
        this.value = this.value.replace(/\D/g, '');
    });
    $('.floatic').keypress(function (event) {
        if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
            event.preventDefault();
        }
    });
    resize();
    $(window).on("resize", function () {
        resize();
    });
    function resize() {
        $("#grid").css("height", window.innerHeight - 90);
    }
    var ajaxMethod = "get";
    var datacopy = null;
    // create Grid table
    var flex = new wijmo.grid.FlexGrid("#grid", {
        loadedRows: function (s, e) {
            for (var i = 0; i < s.rows.length; i++) {
                var row = s.rows[i];
                var FirstName = row.dataItem.FirstName;
                if (FirstName == cmd.cmdTotal.text || FirstName == cmd.cmdTotalKe.text || FirstName == cmd.cmdTotalGo.text) {
                    row.cssClass = 'row-total';
                }
            }
        },
        selectionChanged: function (s, e) {
            if (s.selection.topRow == 0) {
                $(".mg-menu .cmdTotal").prop("disabled", true);
                $(".mg-menu .cmdTotalKe").prop("disabled", true);
                $(".mg-menu .cmdTotalGo").prop("disabled", true);
            } else {
                $(".mg-menu .cmdTotal").prop("disabled", false);
                $(".mg-menu .cmdTotalKe").prop("disabled", false);
                $(".mg-menu .cmdTotalGo").prop("disabled", false);
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
        autoRowHeights: true,
    })
    // set css style 
    flex.columnHeaders.rows[0].cssClass = "wj-align-center"
    flex.columnHeaders.rows[0].wordWrap = true;
    flex.columnHeaders.rows.defaultSize = 55;
    flex.hostElement.addEventListener('contextmenu', (e) => {
        ht = flex.hitTest(e);
        setRowSelected(flex, { first: ht.row })
        // set select rows style
        if (ht.cellType == 2) {
            // show on row header only
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true);
    // メニュー一覧表示
    let itemsSource = [];
    let htmlMenu = "";
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
        if (value.cmd != cmd.cmdExit.cmd) {
            let disable = (value.cmd == cmd.cmdPaste.cmd || value.cmd == cmd.cmdPasteNew.cmd
                || value.cmd == cmd.cmdTotal.cmd || value.cmd == cmd.cmdTotalKe.cmd || value.cmd == cmd.cmdTotalGo.cmd) ? "disabled" : "";
            htmlMenu += '<button class="btn btn-menu-item ' + value.cmd + '" ' + disable + ' data-cmd="' + value.cmd + '"><i class="fa ' + value.icon + ' pr-2" aria-hidden="true"></i>' + value.name + "</button>"
        }
    })
    $(".mg-menu").html(htmlMenu)
    $(document).on("click", ".btn-menu-item", function () {
        selectedValue = $(this).attr("data-cmd");
        MenuMsg(selectedValue)
    })
    function MenuMsg(selectedValue) {
        let msg;
        // 確認メッセージ取得
        if (selectedValue in cmd) {
            let totalRowsRun = flex.selection.rowSpan;

            if (selectedValue == cmd.cmdPaste.cmd || selectedValue == cmd.cmdPasteNew.cmd) {
                totalRowsRun = datacopy.count;
            }
            if (selectedValue == cmd.cmdTotal.cmd || selectedValue == cmd.cmdTotalKe.cmd || selectedValue == cmd.cmdTotalGo.cmd) {
                totalRowsRun = 1;
            }
            let lineNo = "行No:" + (flex.selection.row2 + 1) + "から" + totalRowsRun + "行";

            if (cmd[selectedValue].msg) {
                msg = lineNo + cmd[selectedValue].msg;
            } else {
                if (selectedValue == cmd.cmdCopy.cmd) {
                    $(".mg-menu ." + cmd.cmdPaste.cmd).prop("disabled", false)
                    $(".mg-menu ." + cmd.cmdPasteNew.cmd).prop("disabled", false)
                    datacopy = getSeletedItems(flex);
                }
                if (selectedValue == cmd.cmdNewEdit.cmd) {
                    runAction(selectedValue);
                }
            }
        }
        if (msg) {
            // 確認メッセージ表示
            dispConfirmModal(msg + "ます。よろしいですか？", selectedValue);
        }
    }
    let menu = new wijmo.input.Menu(document.createElement('div'), {
        displayMemberPath: 'header',
        selectedValuePath: 'cmd',
        dropDownCssClass: 'ctx-menu',
        itemsSource: itemsSource,
        _itemClicked: () => {
            MenuMsg(menu.selectedValue)
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
            if (item.innerText == cmd.cmdTotal.name || item.innerText == cmd.cmdTotalKe.name || item.innerText == cmd.cmdTotalGo.name) {
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
        runAction($(this).attr("data-action"));
    })
    function runAction(action) {
        let dataAction = false;
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
                    url: $(".route-mitsumore-action").val(),
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
                            if (action == cmd.cmdNewEdit.cmd) {
                                // 追加した行の「工事仕様の選択」画面を開く
                                flexSelectModal();
                            }
                        }
                    }
                });
            }
        } else {
            // 工事仕様の選択画面閉じる
            if (action == "close") {
                $(MitsumoriModal).modal("hide")
            }
            // 仕様の構成の編集画面閉じる
            if (action == "closeEdit") {
                $(ShiyoEditModal).modal("hide")
            }
        }
    }
    // 編集ポップアップ画面表表示
    flex.hostElement.addEventListener('dblclick', function (e) {
        if (e.returnValue) {
            e.preventDefault();
        }
        if (flex.selectedRows[0].cssClass != "row-total") {
            flexSelectModal()
        }
    })
    function flexSelectModal() {
        flex_selected = flex.selectedItems[0];
        $(MitsumoriModal + " input[name=RowAdd]").val(1)
        $(MitsumoriModal + " input[name=id]").val(flex_selected["id"])
        $(MitsumoriModal + " input[name=DetailNo]").val("No." + flex_selected["DetailNo"])
        $(MitsumoriModal + " input[name=FirstName]").val(flex_selected["FirstName"])
        $(MitsumoriModal + " input[name=StandDimen]").val(flex_selected["StandDimen"])
        $(MitsumoriModal + " input[name=MakerName]").val(flex_selected["MakerName"])
        $(MitsumoriModal + " select[name=UnitOrg_ID]").val(flex_selected["UnitOrg_ID"])
        $(MitsumoriModal + " input[name=Quantity]").val(numberFormat(flex_selected["Quantity"]))
        $(MitsumoriModal + " input[name=UnitPrice]").val(numberFormat(flex_selected["UnitPrice"]))
        $(MitsumoriModal + " input[name=Amount]").val(numberFormat(flex_selected["Amount"]))
        $(MitsumoriModal + " input[name=Note]").val(flex_selected["Note"])
        $(MitsumoriModal + " input[name=Type]").val(flex_selected["Koshu_ID"])
        $(MitsumoriModal + " input[name=PartName]").val(flex_selected["Bui_ID"])
        dataSearch = [];
        dataSearch.push({ name: "page", value: 1 })
        dataSearch.push({ name: "Koshu_ID", value: flex_selected["Koshu_ID"] })
        dataSearch.push({ name: "Bui_ID", value: flex_selected["Bui_ID"] })
        dataSearch.push({ name: "Shiyo_Shubetsu_ID", value: flex_selected["Shiyo_Shubetsu_ID"] ? flex_selected["Koshu_ID"] + '_' + flex_selected["Shiyo_Shubetsu_ID"] : '' })
        dataSearch.push({ name: "Shiyo_Nm", value: null })
        dataSearch.push({ name: "Invoice_ID", value: flex_selected["id"] });
        // check form has changed
        form_shiyo = getFormSelected();
        shiyo_selected_ajax()
        $(MitsumoriModal).modal();
    }
    var form_shiyo_flex;
    function shiyo_selected_ajax() {
        $.ajax({
            type: ajaxMethod,
            data: dataSearch,
            url: $("input[name=route-getMitsumoreMeisai]").val(),
            success: function (res) {
                if (res["status"]) {
                    shiyo_selected_flex.itemsSource = res["data"]
                    shiyo_flex.itemsSource = new wijmo.collections.ObservableArray(res["dataShiyo"]["data"]);
                    $("#shiyoPage").html(res["dataShiyo"]["pagi"])
                    form_shiyo_flex = JSON.stringify(shiyo_selected_flex.itemsSource);
                    firstUnitPrice = wijmo.getAggregate("Sum", shiyo_selected_flex.itemsSource, "M_Tanka_IPN2");
                }
            }
        });
    }
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

    function getheaderCol(header, flgZairyo = false) {
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
                if (flgZairyo) {
                    temp["format"] = "";
                }
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
        itemFormatter: function (panel, r, c, cell) {
            if (panel.cellType == wijmo.grid.CellType.Cell) {
                if (c == 0) {
                    cell.innerHTML = '<i title="材料構成編集画面" class="fa fa-wrench cursor-point btnShiyoEdit pl-2 pr-2  " aria-hidden="true"></i>'
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
            })
            total = wijmo.getAggregate("Sum", shiyo_selected_flex.itemsSource, "M_Tanka_IPN2");
            if (s.rows.length > 0 && total != firstUnitPrice) {
                firstUnitPrice = total;
                UnitPrice.val(numberFormat(total, "n0"))
                Amount.val(numberFormat(total * Quantity.val()))
            }
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
                        value = wijmo.clamp(parseFloat(value).toFixed(1), 0, 100000);
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
                        $(MitsumoriModal).modal("hide")
                        setRowSelected(flex, dataSelected)
                    }
                }
            });
        }
    })
    function numberFormat(number, str = null) {
        if (!number) return 0;
        return wijmo.Globalize.format(number, str ? str : "n0")
    }
    function getNumberData(number) {
        return wijmo.isNumber(number) ? number : number.replace(/\,/g, '');
    }
    // 数量/単価更新時
    $('.amount-change').on("change", function () {
        Amount.val(numberFormat(Quantity.val() * wijmo.Globalize.parseFloat(UnitPrice.val())))
    })
    // 削除ボタンをクリック時
    $(document).on("click", "#shiyo_selected .btnDel", function (e) {
        let viewSelected = shiyo_selected_flex.collectionView;
        viewSelected.remove(viewSelected.currentItem);
        shiyo_selected_flex.collectionView.refresh()
    })
    let dataSearch = [];
    let dataSearch2 = [];
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
                    colspan: 1, header: value["line1"], align: 'center', width: width, cssClass: value["class"], cells: [
                        { binding: key, header: value["name"], width: width, cssClass: value["class"] },
                    ]
                });
            headerLayoutDefinition.push(
                {
                    cells: [
                        { colspan: 1, header: value["line1"], align: 'center', width: width, cssClass: value["class"] },//header line 1
                        { binding: key, header: value["name"], align: 'center', width: width, cssClass: value["class"] }, // header line 2
                    ]
                });
        })
    }
    let formshiyo = ".form-shiyo";
    let shiyo_flex = new wijmo.grid.multirow.MultiRow('#shiyo', {
        layoutDefinition: layoutDefinition,
        headerLayoutDefinition: headerLayoutDefinition,
        itemsSource: [],
        alternatingRowStep: 0,
        itemFormatter: function (panel, r, c, cell) {
            if (panel.cellType == wijmo.grid.CellType.Cell) {
                if (c == 0) {
                    cell.innerHTML = "<button type='button' class='btn btn-select btn-primary btn-custom-2'>選択</button>"
                }
            }
        },
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
                var tag = $(formshiyo + " select[name=Koshu_ID] option[value='" + dataSearch[1].value + "']").attr("data-bui");
                $(formshiyo + " select[name=Koshu_ID]").val(dataSearch[1].value);
                $(formshiyo + " select[name=Bui_ID] option.a").hide();
                $(formshiyo + " select[name=Bui_ID] .a" + tag).show();
                $(formshiyo + " select[name=Shiyo_Shubetsu_ID] option.a").hide();
                $(formshiyo + " select[name=Shiyo_Shubetsu_ID] .a" + dataSearch[1].value).show();
            }
            if (dataSearch[2] != undefined) {
                $(formshiyo + " select[name=Bui_ID]").val(dataSearch[2].value);
                if (dataSearch[1] != undefined) {
                }
            }
            if (dataSearch[3] != undefined) $(formshiyo + " select[name=Shiyo_Shubetsu_ID]").val(dataSearch[3].value);
            if (dataSearch[4] != undefined) $(formshiyo + " input[name=Shiyo_Nm]").val(dataSearch[4].value);

        }
    });
    headerRow1 = shiyo_flex.columnHeaders.rows[1];
    headerRow1.height = 45;
    shiyo_flex.rows.defaultSize = 32;
    headerRow1.cssClass = "header-red-bold"
    shiyo_flex.columnHeaders.rows[2].cssClass = "header-red-normal"

    // 赤画面行にクリック時,青画面に追加
    shiyo_flex.hostElement.addEventListener('dblclick', function (e) {
        addItemInShiyoSelected(e)
    })
    $(document).on("click", "#shiyo .btn-select", function (e) {
        addItemInShiyoSelected(e)
    })
    function addItemInShiyoSelected(e) {
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
    }
    // 検索条件更新の時
    $(document).on("change", "#shiyo .btn-search", function (e) {
        // 種別
        if (e.target.name == "Koshu_ID") {
            $(formshiyo + " select[name=Bui_ID]").val('');
            $(formshiyo + " select[name=Shiyo_Shubetsu_ID]").val('');
            $(formshiyo + " input[name=Shiyo_Nm]").val('');
        }
        $(formshiyo + " input[name=page]").val(1);
        shiyoAjax(shiyo_flex);
    })

    $(document).on("click", formshiyo + " .btn-search-run", function () {
        $(formshiyo + " input[name=page]").val(1);
        shiyoAjax(shiyo_flex);
    })
    $(document).on("click", formshiyo + " .btn-search-clear", function () {
        $(formshiyo + " select[name=Koshu_ID]").val('');
        $(formshiyo + " select[name=Bui_ID]").val('');
        $(formshiyo + " select[name=Shiyo_Shubetsu_ID]").val('');
        $(formshiyo + " input[name=Shiyo_Nm]").val('');
    })
    var form_shiyo = [];
    var form_shiyo_changed_flag = false;
    var formSelected = $(".form-selected");
    // 工事仕様の選択画面閉じるのポップアップ表示
    $(MitsumoriModal + " .close").on("click", function () {
        if (form_shiyo !== getFormSelected() || form_shiyo_flex !== JSON.stringify(shiyo_selected_flex.itemsSource)) {
            dispConfirmModal("編集中のデータはまだ保存していません。<br>編集中の内容を破棄し、見積明細画面に戻りますか？", "close");
        } else {
            $(MitsumoriModal).modal("hide");
        }
    })

    function getFormSelected() {
        let form = formSelected.serializeArray();
        delete form[0];
        delete form[10];
        delete form[11];
        return JSON.stringify(form);
    }
    // ページング
    $(document).on("click", "#shiyoPage .page-link", function (e) {
        e.preventDefault();
        page = getQueryStringValue($(this).attr('href'), "page")
        if (page) {
            $(formshiyo + " input[name=page]").val(page);
            shiyoAjax(shiyo_flex);
        }
    })
    $("form").bind("keypress", function (e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });
    function shiyoAjax(shiyo_flex) {
        dataSearch = $(formshiyo).serializeArray();
        $.ajax({
            type: ajaxMethod,
            data: dataSearch,
            url: $("input[name=route-getListShiyo]").val(),
            beforeSend: function () {
                $('.loading').addClass('d-none');
                $(formshiyo + " .shiyo-loading").removeClass("d-none");
            },
            complete: function () {
                $(formshiyo + " .shiyo-loading").addClass('d-none');
            },
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

    // 仕様の構成の編集画面表示
    $(document).on("click", ".btnShiyoEdit", function (e) {
        $(ShiyoEditModal).modal();
        shiyo_selected_edit = shiyo_selected_flex.selectedItems[0];
        $(ShiyoEditModal + " .Koshu_Nmtxt").html(shiyo_selected_edit["Koshu_Nm"])
        $(ShiyoEditModal + " .Bui_NMtxt").html(shiyo_selected_edit["Bui_NM"])
        $(ShiyoEditModal + " input[name=Shiyo_Nm]").val(shiyo_selected_edit["Shiyo_Nm"])
        $(ShiyoEditModal + " input[name=Maker_Nm]").val(shiyo_selected_edit["Maker_Nm"])
        $(ShiyoEditModal + " select[name=Tani_ID]").val(shiyo_selected_edit["Tani_ID"])
        // 材料リストデータ取得
        ajaxZairyoSelected(shiyo_selected_edit["Shiyo_ID"])
    })

    // 材料リストの戻すボタン
    $(document).on("click", ".btnZairyoRestore", function (e) {
        // 材料リストデータ取得
        ajaxZairyoSelected(shiyo_selected_flex.selectedItems[0]["Shiyo_ID"])
    });

    var form_zairyo_selected_flex;
    // 材料リストデータ取得
    function ajaxZairyoSelected(Shiyo_ID) {
        $.ajax({
            type: ajaxMethod,
            data: "Shiyo_ID=" + Shiyo_ID,
            url: $("input[name=route-getListZairyoSelected]").val(),
            success: function (res) {
                if (res["status"]) {
                    zairyo_selected_flex.itemsSource = res["data"];
                    zairyo_flex.itemsSource = new wijmo.collections.ObservableArray(res["dataZairyo"]["data"]);
                    $("#zairyoPage").html(res["dataZairyo"]["pagi"])
                    form_zairyo_selected_flex = JSON.stringify(zairyo_selected_flex.itemsSource)
                }
            }
        });
    }

    let dataSearchZairyo = [];
    var layoutDefinitionZairyo = [];
    var headerLayoutDefinitionZairyo = [];
    getHeaderColZairyo(headerZairyo);
    function getHeaderColZairyo(headerZairyo) {
        // create the MultiRow
        // header row create
        $.each(headerZairyo, function (key, value) {
            width = value["width"] ? value["width"] : 100;

            layoutDefinitionZairyo.push(
                {
                    colspan: 1, header: value["line1"], align: 'center', width: width, cssClass: value["class"], cells: [
                        { binding: key, header: value["name"], width: width, cssClass: value["class"] },
                    ]
                });
            headerLayoutDefinitionZairyo.push(
                {
                    cells: [
                        { colspan: 1, header: value["line1"], align: 'center', width: width, cssClass: value["class"] },//header line 1
                        { binding: key, header: value["name"], align: 'center', width: width, cssClass: value["class"] }, // header line 2
                    ]
                });

        })
    }
    let zairyo_flex = new wijmo.grid.multirow.MultiRow('#zairyo', {
        layoutDefinition: layoutDefinitionZairyo,
        headerLayoutDefinition: headerLayoutDefinitionZairyo,
        itemsSource: [],
        alternatingRowStep: 0,
        formatItem: function (s, e) {
            /* show HTML in column headers */
            if (e.panel == s.columnHeaders) {
                e.cell.innerHTML = e.cell.textContent;
            }
        },
        itemFormatter: function (panel, r, c, cell) {
            if (panel.cellType == wijmo.grid.CellType.Cell) {
                if (c == 0) {
                    cell.innerHTML = "<button type='button' class='btn btn-select btn-primary btn-custom-2'>選択</button>"
                }
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
            if (dataSearchZairyo[1] != undefined) $("select[name=Zairyo_Shubetsu_ID]").val(dataSearchZairyo[1].value);
            if (dataSearchZairyo[2] != undefined) $("input[name=Zairyo_Nm]").val(dataSearchZairyo[2].value);

        }
    });
    headerRow1 = zairyo_flex.columnHeaders.rows[1];
    headerRow1.height = 45;
    headerRow1.cssClass = "header-red-bold"
    zairyo_flex.columnHeaders.rows[2].cssClass = "header-red-normal"

    // 検索条件更新の時
    $(document).on("change", "#zairyo .btn-search-zairyo", function (e) {
        $(formZairyo + " input[name=page]").val(1);
        zairyoAjax(zairyo_flex);
    })
    $(document).on("click", "#zairyo .btn-search-clear", function (e) {
        $(formZairyo + " .btn-search-zairyo").val("");

    })

    $(document).on("click", "#zairyo .btn-search-run", function (e) {
        $(formZairyo + " input[name=page]").val(1);
        zairyoAjax(zairyo_flex);
    })

    // ページング
    $(document).on("click", "#zairyoPage .page-link", function (e) {
        e.preventDefault();
        page = getQueryStringValue($(this).attr('href'), "page")
        if (page) {
            $(formZairyo + " input[name=page]").val(page);
            zairyoAjax(zairyo_flex);
        }
    })

    // 材料/仕様リスト画面
    var zairyo_selected_flex = new wijmo.grid.FlexGrid("#zairyo_selected", {
        itemsSource: new wijmo.collections.ObservableArray([]),
        columns: getheaderCol(headerZairyoSelected, true),
        autoGenerateColumns: false,
        selectionMode: 'Row',
        allowSorting: false,
        allowDragging: wijmo.grid.AllowDragging.Both,
        itemFormatter: function (panel, r, c, cell) {
            if (panel.cellType == wijmo.grid.CellType.Cell) {
                if (c == 0) {
                    if (panel.rows[r].dataItem["Old_Flg"] != 1)
                        cell.innerHTML = '<i class="fa fa-trash cursor-point btnDel  pl-2 pr-2 " title="行削除" aria-hidden="true"></i>';
                }
                if (c == 1) {
                    cell.innerHTML = r + 1;
                }
            }
        },
        updatedView: function (s, e) {
            $.each(s.rows, function (r, value) {
                s.rows[r].dataItem["Sort_No"] = r + 1;
                if (value.dataItem["Old_Flg"] == 1) {
                    s.rows[r].isReadOnly = true;
                }
                if (value.dataItem["Zairyo_Shiyo_Type"] == "材料") {
                    s.rows[r].format = "n4";
                } else {
                    s.rows[r].format = "n1";
                }
            })
        },
        _cellEditEnding: (s, e) => {
            let col = s.columns[e.col];
            if (col.binding == 'AtariSuryo') {
                let format = s.rows[e.row].format;
                if (s.activeEditor) {
                    let value = wijmo.changeType(s.activeEditor.value, wijmo.DataType.Number, format);

                    let flag = true;
                    if (format == "n1" && value < 0) flag = false;
                    if (isNaN(value) || !wijmo.isNumber(value) || !flag) {
                        e.cancel = true;
                        console.info('Please enter a positive ');
                    } else {
                        // マイナス数量になったらエラー
                        if (checkNegative(s.itemsSource, s.rows[e.row].dataItem["Zairyo_Shiyo_ID"], s.rows[e.row].dataItem["Sort_No"], value)) {
                            s.activeEditor.value = value;
                        } else {
                            e.cancel = true;
                            dispMessageModal("マイナス数量になりました。再入力をお願い致します。")
                        }
                    }
                } else {
                    value = 0;
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

    // 赤画面行にクリック時,青画面に追加
    zairyo_flex.hostElement.addEventListener('dblclick', function (e) {
        addItemInZairyoSelected(e)
    })
    $(document).on("click", "#zairyo .btn-select", function (e) {
        addItemInZairyoSelected(e)
    })
    function addItemInZairyoSelected(e) {
        ht = zairyo_flex.hitTest(e);
        if (ht.cellType == 1) {
            selectedItem = JSON.parse(JSON.stringify(zairyo_flex.itemsSource[ht.row]));
            zairyo_selected_flex.itemsSource.push(selectedItem);
            zairyo_selected_flex.collectionView.refresh();
            zairyo_selected_flex.select(zairyo_selected_flex.itemsSource.length, -1)
            if (e.returnValue) {
                e.preventDefault();
            }
        }
    }
    // マイナス数量になったらエラー
    function checkNegative(source, Zairyo_Shiyo_ID, Sort_No, value) {
        subtotal = 0;
        $.each(source, function (index, val) {
            if (val["Zairyo_Shiyo_ID"] == Zairyo_Shiyo_ID) {
                if (val["Sort_No"] == Sort_No) {
                    subtotal += value;
                } else {
                    subtotal += val["AtariSuryo"];
                }
            }
        });
        return subtotal < 0 ? false : true;
    }

    // 削除ボタンをクリック時
    $(document).on("click", "#zairyo_selected .btnDel", function (e) {
        let viewSelected = zairyo_selected_flex.collectionView;
        viewSelected.remove(viewSelected.currentItem);
        zairyo_selected_flex.collectionView.refresh()
    })

    // 仕様の構成の編集画面閉じるのポップアップ表示
    $(ShiyoEditModal + " .close").on("click", function () {
        if (form_zairyo_selected_flex !== JSON.stringify(zairyo_selected_flex.itemsSource)) {
            dispConfirmModal("編集中のデータはまだ保存していません。<br>編集中の内容を破棄し、工事仕様の選択に戻りますか？", "closeEdit");
        } else {
            $(ShiyoEditModal).modal("hide")
        }
    })

    $(".btnSaveZairyo").on("click", function () {
        let btnClick = $(this).attr("data-btn");
        var zairyo_selected_form = [];
        zairyo_selected_form.push({ name: "btn", value: btnClick });
        zairyo_selected_form.push({ name: "Shiyo_ID", value: shiyo_selected_flex.selectedItems[0]["Shiyo_ID"] });
        $.each(zairyo_selected_flex.itemsSource, function (key, value) {
            zairyo_selected_form.push({ name: "Zairyo_Shiyo_Type[]", value: value["Zairyo_Shiyo_Type"] });
            zairyo_selected_form.push({ name: "Zairyo_Shiyo_ID[]", value: value["Zairyo_Shiyo_ID"] });
            zairyo_selected_form.push({ name: "Old_Flg[]", value: value["Old_Flg"] });
            zairyo_selected_form.push({ name: "AtariSuryo[]", value: value["AtariSuryo"] != undefined ? value["AtariSuryo"] : null });
            zairyo_selected_form.push({ name: "Sort_No[]", value: value["Sort_No"] });
            zairyo_selected_form.push({ name: "Shubetsu_ID[]", value: value["Shubetsu_ID"] });
            zairyo_selected_form.push({ name: "Tani_ID[]", value: value["Tani_ID"] });
        });
        $.ajax({
            type: ajaxMethod,
            data: zairyo_selected_form,
            url: $("input[name=route-zstore]").val(),
            success: function (res) {
                if (res["status"]) {
                    zairyo_selected_flex.itemsSource = res["data"]["data"];
                    form_zairyo_selected_flex = JSON.stringify(zairyo_selected_flex.itemsSource)
                    shiyo_selected_ajax();
                    dispSuccessMsg(res["msg"])
                } else {
                    dispMessageModal(res["msg"])
                }
            }
        });
    })
    function zairyoAjax(zairyo_flex) {
        dataSearchZairyo = $(formZairyo).serializeArray();
        $.ajax({
            type: ajaxMethod,
            data: dataSearchZairyo,
            url: $("input[name=route-getListZairyo]").val(),
            beforeSend: function () {
                $('.loading').addClass('d-none');
                $(".zairyo-loading").removeClass("d-none");
            },
            complete: function () {
                $('.zairyo-loading').addClass('d-none');
            },
            success: function (res) {
                if (res["status"]) {
                    zairyo_flex.itemsSource = new wijmo.collections.ObservableArray(res["data"]);
                    $("#zairyoPage").html(res["pagi"])
                } else {
                    zairyo_flex.itemsSource = [];
                    $("#zairyoPage").html("")
                }
            }
        });
    }

    // 材料構成編集の仕様検索画面
    let formshiyo2 = ".form-shiyo2";
    let shiyo_flex2 = new wijmo.grid.multirow.MultiRow('#shiyo2', {
        layoutDefinition: layoutDefinition,
        headerLayoutDefinition: headerLayoutDefinition,
        itemsSource: [],
        alternatingRowStep: 0,
        formatItem: function (s, e) {
            /* show HTML in column headers */
            if (e.panel == s.columnHeaders) {
                e.cell.innerHTML = e.cell.textContent;
            }
        },
        itemFormatter: function (panel, r, c, cell) {
            if (panel.cellType == wijmo.grid.CellType.Cell) {
                if (c == 0) {
                    cell.innerHTML = "<button type='button' class='btn btn-select btn-primary btn-custom-2'>選択</button>"
                }
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
            if (dataSearch2[1] != undefined) {
                var tag = $(formshiyo2 + " select[name=Koshu_ID] option[value='" + dataSearch2[1].value + "']").attr("data-bui");
                $(formshiyo2 + " select[name=Koshu_ID]").val(dataSearch2[1].value);
                $(formshiyo2 + " select[name=Bui_ID] option.a").hide();
                $(formshiyo2 + " select[name=Bui_ID] .a" + tag).show();
                $(formshiyo2 + " select[name=Shiyo_Shubetsu_ID] option.a").hide();
                $(formshiyo2 + " select[name=Shiyo_Shubetsu_ID] .a" + dataSearch2[1].value).show();
            }
            if (dataSearch2[2] != undefined) {
                $(formshiyo2 + " select[name=Bui_ID]").val(dataSearch2[2].value);
                if (dataSearch2[1] != undefined) {
                }
            }
            if (dataSearch2[3] != undefined) $(formshiyo2 + " select[name=Shiyo_Shubetsu_ID]").val(dataSearch2[3].value);
            if (dataSearch2[4] != undefined) $(formshiyo2 + " input[name=Shiyo_Nm]").val(dataSearch2[4].value);

        }
    });
    headerRow1 = shiyo_flex2.columnHeaders.rows[1];
    headerRow1.height = 45;
    headerRow1.cssClass = "header-red-bold"
    shiyo_flex2.columnHeaders.rows[2].cssClass = "header-red-normal"

    $(document).on("click", "#shiyo2 .btn-select", function (e) {
        addItemInShiyoSelected2(e)
    })
    function addItemInShiyoSelected2(e) {
        ht = shiyo_flex2.hitTest(e);
        if (ht.cellType == 1) {
            selectedItem = JSON.parse(JSON.stringify(shiyo_flex2.itemsSource[ht.row]));
            var selectedItemNew = {
                AtariSuryo: selectedItem.AtariSuryo,
                Name: selectedItem.Shiyo_Nm,
                Old_Flg: selectedItem.Old_Flg,
                Sort_No: 0,
                Tani_Nm: selectedItem.Tani_Nm,
                Tani_ID: selectedItem.Tani_ID,
                Zairyo_Shiyo_Type: selectedItem.Zairyo_Shiyo_Type,
                Zairyo_Shiyo_ID: selectedItem.Shiyo_ID,
                Shubetsu_Nm: selectedItem.Koshu_Nm,
                Shubetsu_ID: selectedItem.Koshu_ID
            }
            zairyo_selected_flex.itemsSource.push(selectedItemNew);
            zairyo_selected_flex.collectionView.refresh();
            zairyo_selected_flex.select(zairyo_selected_flex.itemsSource.length, -1)
        }
    }
    // 赤画面行にクリック時,青画面に追加
    shiyo_flex2.hostElement.addEventListener('dblclick', function (e) {
        addItemInShiyoSelected2(e)
        if (e.returnValue) {
            e.preventDefault();
        }
    })
    // 検索条件更新の時
    $(document).on("change", formshiyo2 + " .btn-search", function (e) {
        // 種別
        if (e.target.name == "Koshu_ID") {
            $(formshiyo2 + " select[name=Bui_ID]").val('');
            $(formshiyo2 + " select[name=Shiyo_Shubetsu_ID]").val('');
            $(formshiyo2 + " input[name=Shiyo_Nm]").val('');
        }
        $(formshiyo2 + " input[name=page]").val(1);
        shiyoAjax2(shiyo_flex2);
    })

    $(document).on("click", formshiyo2 + " .btn-search-run", function () {
        $(formshiyo2 + " input[name=page]").val(1);
        shiyoAjax2(shiyo_flex2);
    })
    $(document).on("click", formshiyo2 + " .btn-search-clear", function () {
        $(formshiyo2 + " select[name=Koshu_ID]").val('');
        $(formshiyo2 + " select[name=Bui_ID]").val('');
        $(formshiyo2 + " select[name=Shiyo_Shubetsu_ID]").val('');
        $(formshiyo2 + " input[name=Shiyo_Nm]").val('');
    })
    // ページング
    $(document).on("click", "#shiyoPage2 .page-link", function (e) {
        e.preventDefault();
        page = getQueryStringValue($(this).attr('href'), "page")
        if (page) {
            $(formshiyo2 + " input[name=page]").val(page);
            shiyoAjax2(shiyo_flex2);
        }
    })
    $(formshiyo2).bind("keypress", function (e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });
    $("input[name=radioSearch]").on("change", function () {
        $(".form-kosei").toggleClass("d-none");
    })
    function shiyoAjax2(shiyo_flex2) {
        dataSearch2 = $(formshiyo2).serializeArray();
        dataSearch2.push({ name: "Shiyo_ID", value: shiyo_selected_flex.selectedItems[0]["Shiyo_ID"] });
        $.ajax({
            type: ajaxMethod,
            data: dataSearch2,
            url: $("input[name=route-getListShiyo]").val(),
            beforeSend: function () {
                $('.loading').addClass('d-none');
                $(formshiyo2 + " .shiyo-loading").removeClass("d-none");
            },
            complete: function () {
                $(formshiyo2 + " .shiyo-loading").addClass('d-none');
            },
            success: function (res) {
                if (res["status"]) {
                    shiyo_flex2.itemsSource = new wijmo.collections.ObservableArray(res["data"]);
                    $("#shiyoPage2").html(res["pagi"])
                } else {
                    shiyo_flex2.itemsSource = [];
                    $("#shiyoPage2").html("")
                }
            }
        });
    }
}
