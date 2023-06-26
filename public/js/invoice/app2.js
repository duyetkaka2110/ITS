
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
        itemsSource: new wijmo.collections.ObservableArray(list),
        columns: headerCol,
        frozenColumns: 5,
        isReadOnly: true,
        allowResizing: 'BothAllCells',
        selectionMode: 'Row',
        allowSorting: false
    })
    // set css style 
    flex.topLeftCells.setCellData(0, 0, "行No");
    flex.columnHeaders.rows[0].cssClass = "wj-align-center"
    flex.columnHeaders.rows[0].wordWrap = true;
    flex.columnHeaders.rows.defaultSize = 55;
    flex.rows.defaultSize = 59;
    setRowHeaderNumber(flex)
    console.info(flex)
    flex.hostElement.addEventListener('contextmenu', (e) => {
        ht = flex.hitTest(e);
        // set select rows style
        flex.selection = new wijmo.grid.CellRange(ht.row, 0, 1, 1)
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
            let lineNo = "行No:" + (ht.row + 1);
            // 確認メッセージ取得
            if (menu.selectedValue in cmd) {
                if (cmd[menu.selectedValue].msg) {
                    msg = lineNo + cmd[menu.selectedValue].msg;
                } else {
                    if (menu.selectedValue == cmd.cmdCopy.cmd) {
                        datacopy = flex.itemsSource[ht.row];
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
    $(".btnPopupOk").on("click", function () {
        let dataAction = false, dataActionOldId = false;
        let rowSelect = flex.itemsSource[ht.row];
        console.info(rowSelect)
        action = $(this).attr("data-action")

        // 挿入
        if (action == cmd.cmdNew.cmd) {
            dataAction = 1;
        }
        // 削除
        if (action == cmd.cmdDel.cmd) {
            dataAction = rowSelect;
        }
        // 貼り付け
        if (action == cmd.cmdPaste.cmd && datacopy) {
            // 選択行後、id保存
            dataActionOldId = rowSelect.id;
            dataAction = datacopy;
        }
        // コピーした行の挿入
        if (action == cmd.cmdPasteNew.cmd && datacopy) {
            datacopy.SortNo = rowSelect.SortNo - 1;
            dataAction = datacopy;
        }
        // DB更新
        if (dataAction) {
            $.ajax({
                type: "get",
                data: {
                    action: action,
                    data: dataAction,
                    dataOldId: dataActionOldId,
                },
                url: $(".route-invoice-action").val(),
                success: function (res) {
                    if (!res["status"]) {
                        // エラー表示
                        dispMessageModal(res["msg"])
                    } else {
                        // 挿入
                        if (action == cmd.cmdNew.cmd) {
                            flex.itemsSource.insert(ht.row, null);
                        }
                        // 削除
                        if (action == cmd.cmdDel.cmd) {
                            flex.itemsSource.removeAt(ht.row);
                        }
                        // 貼り付け
                        if (action == cmd.cmdPaste.cmd && datacopy) {
                            setCopyData(datacopy, ht)
                        }
                        // コピーした行の挿入
                        if (action == cmd.cmdPasteNew.cmd && datacopy) {
                            flex.itemsSource.insert(ht.row, null);
                            setCopyData(datacopy, ht)
                        }
                        setRowHeaderNumber(flex);
                        setRowSelected(flex, ht)
                    }

                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.info(jqXHR["responseText"]);
                }
            });
        }
    })
    // 編集ポップアップ画面表表示
    flex.hostElement.addEventListener('dblclick', function (e) {
        console.log('Double Click');
    })
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
    function setRowSelected(flex, ht) {
        flex.selection = new wijmo.grid.CellRange(ht.row, 0, 1, 1)
    }
}
