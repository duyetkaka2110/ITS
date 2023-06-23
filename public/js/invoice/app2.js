
document.readyState === 'complete' ? init() : window.onload = init;
function init() {
    var headerCol = [], datacopy = null;
    // set header name, width, css
    for (let col = 0; col < headerkey.length; col++) {
        let key = headerkey[col];
        headerCol.push({
            binding: headerkey[col],
            header: headername[key]["name"],
            width: headername[key]["width"],
            wordWrap: true,
            cssClass: headername[key]["class"]
        })
    }
    // create Grid table
    var flex = new wijmo.grid.FlexGrid("#grid", {
        itemsSource: new wijmo.collections.ObservableArray(list),
        columns: headerCol,
        frozenColumns: 5,
        isReadOnly: true,
        allowResizing: 'BothAllCells',
        selectionMode: 'Row'
    })
    // set css style 
    flex.topLeftCells.setCellData(0, 0, "行No");
    flex.columnHeaders.rows[0].cssClass = "wj-align-center"
    flex.columnHeaders.rows[0].wordWrap = true;
    flex.columnHeaders.rows.defaultSize = 55;
    flex.rows.defaultSize = 59;
    setRowHeaderNumber(flex)

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
    let menu = new wijmo.input.Menu(document.createElement('div'), {
        displayMemberPath: 'header',
        selectedValuePath: 'cmd',
        dropDownCssClass: 'ctx-menu',
        itemsSource: [
            { header: '<span class="glyphicon glyphicon-plus"></span>挿入', cmd: 'cmdNew' },
            { header: '<span class="glyphicon glyphicon-copy"></span>コピー', cmd: 'cmdCopy' },
            { header: '<span class="glyphicon glyphicon-paste"></span>貼り付け', cmd: 'cmdPaste' },
            { header: '<span class="glyphicon glyphicon-share"></span>コピーした行の挿入', cmd: 'cmdPasteNew' },
            { header: '<span class="glyphicon glyphicon-trash"></span>削除', cmd: 'cmdDel' },
            { header: '<span class="wj-separator"></span>' },
            { header: '<span class="glyphicon glyphicon-remove"></span>閉じる', cmd: 'cmdExit' }
        ],
        itemClicked: () => {
            // 挿入
            if (menu.selectedValue == "cmdNew") {
                flex.itemsSource.insert(ht.row, null);
            }
            // 削除
            if (menu.selectedValue == "cmdDel") {
                $("#ConfirmModal").modal("show")
                // flex.itemsSource.removeAt(ht.row);
            }
            // コピー
            if (menu.selectedValue == "cmdCopy") {
                datacopy = flex.itemsSource[ht.row];
            }
            // 貼り付け
            if (menu.selectedValue == "cmdPaste") {
                setCopyData(datacopy, ht)
            }
            // コピーした行の挿入
            if (menu.selectedValue == "cmdPasteNew") {
                if (datacopy) {
                    flex.itemsSource.insert(ht.row, null);
                    setCopyData(datacopy, ht)
                }
            }
            setRowHeaderNumber(flex);
            setRowSelected(flex, ht)
        }
    });
    // disable menu when not have data copy
    menu.isDroppedDownChanging.addHandler((s, e) => {
        s.dropDown.childNodes.forEach(item => {
            if (!datacopy) {
                if (item.innerText == '貼り付け' || item.innerText == 'コピーした行の挿入') {
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
