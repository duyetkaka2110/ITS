
document.readyState === 'complete' ? init() : window.onload = init;
function init() {
    var headerCol = [], datacopy = null, key, col;
    // header row create
    $.each(header, function (key, value) {
        headerCol.push({
            binding: key,
            header: value["name"],
            width: value["width"],
            wordWrap: true,
            cssClass: value["class"]
        })
    })
    // create Grid table
    var flex = new wijmo.grid.FlexGrid("#grid", {
        loadedRows: function (s, e) {
            for (var i = 0; i < s.rows.length; i++) {
                var row = s.rows[i];
                var item = row.dataItem;
                if (item.FirstName == cmd.cmdTotal.text) {
                    row.cssClass = 'row-total';
                }
            }
        },
        itemsSource: new wijmo.collections.ObservableArray(list),
        columns: headerCol,
        autoGenerateColumns: false,
        frozenColumns: 6,
        isReadOnly: true,
        allowResizing: 'BothAllCells',
        selectionMode: 'RowRange',
        allowSorting: false,
        headersVisibility: "Column",
    })
    // set css style 
    flex.columnHeaders.rows.defaultSize = 55;
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
        $("#InvoiceModal").modal();
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
    // set data copy
    function setCopyData(datacopy, ht) {
        if (datacopy) {
            flex.itemsSource[ht.row] = datacopy;
            flex.collectionView.refresh()
        }
    }
    // set number line
    function setRowHeaderNumber(flex) {
        for (var r = 0; r < flex.rowHeaders.rows.length; r++) {
            flex.rowHeaders.setCellData(r, 0, r + 1);
        }
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
}
