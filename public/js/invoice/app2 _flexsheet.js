import * as input from '@grapecity/wijmo.input';
import * as wjFlexSheet from '@grapecity/wijmo.grid.sheet';
//
document.readyState === 'complete' ? init() : window.onload = init;
//
function init() {
    wijmo.setLicenseKey("477492474881697#B0XzzWYmpjIyNHZisnOiwmbBJye0ICRiwiI34TUuJFMStWbqFzZ8MGbHx6NhZ5ROlTdolGen9GUvx4KBRXNPVUW5Y5SaplcG3UbQVTOYp6atB5L8MzKodjQ7ImQINzRylVW4IWTlxWR0BVOORXRQRjMHNXO6l5K9BTd92EOrITTGt6K8AVZNtkdvlTYY3CTEpEdsVTc6QXQERVd7R7diR5Y6VmcjB7TJJzUSFESvIDOldEZyE4ZFpkZolWT6lFWZN5SGVVUy2mUOB5cyVXMBtCd8dzUXJkcW9kUrQkQFlkQ6cHMGR7NpF7boJWd5tkZwIWWDF4ZTl7b4ckbqRFe6llNBJWd5FzcBNUdsVVZRRTO584KYh7cnBFaUlncQd6SUxGRk9ka7cmepVESaF4KtZ5Q6gVc48kYHxWekJ7bmZTUzN4ZRhkR7Y4TKdWWOlFePZmMMdDcpVkdxNlcvQ6KNBjRGlVOstCeoR5ZhNVOYNmTQdkI0IyUiwiIyQURygTRwEjI0ICSiwyNyYDN8kDNxcTM0IicfJye&Qf35VfikEMyIlI0IyQiwiIu3Waz9WZ4hXRgACdlVGaThXZsZEIv5mapdlI0IiTisHL3JSNJ9UUiojIDJCLi86bpNnblRHeFBCIyV6dllmV4J7bwVmUg2Wbql6ViojIOJyes4nILdDOIJiOiMkIsIibvl6cuVGd8VEIgc7bSlGdsVXTg2Wbql6ViojIOJyes4nI4YkNEJiOiMkIsIibvl6cuVGd8VEIgAVQM3EIg2Wbql6ViojIOJyes4nIzMEMCJiOiMkIsISZy36Qg2Wbql6ViojIOJyes4nIVhzNBJiOiMkIsIibvl6cuVGd8VEIgQnchh6QsFWaj9WYulmRg2Wbql6ViojIOJyebpjIkJHUiwiI4IjNwcDMgATM5AzMyAjMiojI4J7QiwiIx8CMuAjL7ITMiojIz5GRiwiI+S09ayL9Pyb9qCq9jK88GO887K88XO882O88sO88wK88iojIh94QiwiI7kjNxgDO4cDNykDN7cDNiojIklkIs4XXbpjInxmZiwiIyY7MyAjMiojIyVmWiDS");


    let flex = new wjFlexSheet.FlexSheet('#unboundSheet');
    flex.addUnboundSheet('unbound', list.length, headerkey.length);
    console.info(flex);
    flex.frozenColumns = 5; //   freeze column
    flex.isReadOnly = true;
    flex.isTabHolderVisible = false; // not show sheet name
    //   flex.columnHeaders.columns.defaultSize = 50; // header width size
    flex.columnHeaders.rows.defaultSize = 50; // header height size
    flex.showFilterIcons = false; // hide filter icon
    let setHeaderName = false,
        colHeader, key,
        width, ht, sourceRow, fmt;
    flex.topLeftCells.setCellData(0, 0, "行No");
    flex.topLeftCells.columns.defaultSize = 40;
    flex.topLeftCells.hostElement.lastChild.lastChild.wordWrap = true;
    for (let row = 0; row < list.length; row++) {
        for (let col = 0; col < headerkey.length; col++) {
            colHeader = flex.columnHeaders.columns[col];
            key = headerkey[col];
            if (!setHeaderName) {
                // change header name from A, B , C....
                colHeader.header = headername[key]["name"];
                colHeader.wordWrap = true;
                // colHeader.align = "center";

                if (headername[key]["class"])
                    colHeader.cssClass = headername[key]["class"];

                width = parseInt(headername[key]["width"]);
                if (width) {
                    colHeader.width = width;
                }
            }
            // set cell data
            if(key != "Quantity"){
                fmt = "n0";
            }else{
                fmt = "n2";
            }
            // set cell number format
            flex.setCellData(row, col, wijmo.Globalize.format(list[row][key], fmt));
        }
        setHeaderName = true;
    }
    flex.hostElement.addEventListener('contextmenu', (e) => {
        ht = flex.hitTest(e);
        console.info(menu)

        if (ht.cellType == 1) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    }, true);
    let menu = new input.Menu(document.createElement('div'), {
        displayMemberPath: 'header',
        selectedValuePath: 'cmd',
        dropDownCssClass: 'ctx-menu',
        itemsSource: [
            { header: '<span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp;挿入', cmd: 'cmdNew' },
            { header: '<span class="glyphicon glyphicon-copy"></span>&nbsp;&nbsp;コピー', cmd: 'cmdCopy' },
            { header: '<span class="glyphicon glyphicon-paste"></span>&nbsp;&nbsp;貼り付け', cmd: 'cmdPaste' },
            { header: '<span class="glyphicon glyphicon-share"></span>&nbsp;&nbsp;コピーした行の挿入', cmd: 'cmdPasteNew' },
            { header: '<span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;削除', cmd: 'cmdDel' },
            { header: '<span class="wj-separator"></span>' },
            { header: '<span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;閉じる', cmd: 'cmdExit' }
        ],
        itemClicked: () => {
            console.info(ht)
            // 挿入
            if (menu.selectedValue == "cmdNew") {
                flex.insertRows(ht.row, 1)
            }
            // 削除
            if (menu.selectedValue == "cmdDel") {
                flex.rows.removeAt(ht.row)
            }
            // コピー
            if (menu.selectedValue == "cmdCopy") {
                sourceRow = flex.rows[ht.row];
            }
            // 貼り付け
            if (menu.selectedValue == "cmdPaste") {
                setCopyData(flex, ht, sourceRow)
            }
            // コピーした行の挿入
            if (menu.selectedValue == "cmdPasteNew") {
                flex.insertRows(ht.row, 1)
                setCopyData(flex, ht, sourceRow)
            }
        }
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
    function setCopyData(flex, ht, sourceRow) {
        if (sourceRow) {
            for (let i = 0; i < flex.columns.length; i++) {
                //copy data,optionally update formula
                flex.setCellData(ht.row, i, flex.getCellData(sourceRow.index, i, false), false, true);
            }
            sourceRow = null;
        }
    }
}
