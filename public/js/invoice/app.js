// onload = function(){
//     var grid = new wijmo.grid.FlexGrid("#grid",{
//         itemsSource: getData()
//     })
//     function getData(){
//         var countries ="アメリカ,日本,韓国,英語, いたり".split(","),
//         data = []
//         for(var i = 0; i < 200; i++){
//             data.push({
//                 id: i,
//                 date: wijmo.DateTime.addDays(new Date(), -Math.random()*365),
//                 country: countries[i%countries.length],
//                 active: i%5 != 0,
//                 downloads: Math.round(Math.random()*200000),
//                 sales: Math.random()*1000000,
//                 expenses: Math.random()*500000
//             })
//         }
//         return data;
//     }
// }

// document.readyState === 'complete' ? init() : window.onload = init;
// //
// function init() {
//     let unboundSheet = new wijmo.grid.sheet.FlexSheet('#grid');
    
// var sheet = new wijmo.grid.sheet.Sheet();
// sheet.name = "New Sheet";
// sheet.itemsSource = data;
// unboundSheet.sheets.push(sheet);
//     // unboundSheet.addUnboundSheet('unbound', 20, 10);
//     // unboundSheet.deferUpdate(() => {
//     //     for (let row = 0; row < unboundSheet.rows.length; row++) {
//     //         for (let col = 0; col < unboundSheet.columns.length; col++) {
//     //             unboundSheet.setCellData(row, col, row + col);
//     //         }
//     //     }
//     // });
// }

var countries = 'US,Germany,UK,Japan,Italy,Greece'.split(','),
    data = [];
for (var i = 0; i < countries.length; i++) {
    data.push({
        country: countries[i],
        downloads: Math.round(Math.random() * 20000),
        sales: Math.round(Math.random() * 10000),
        expenses: Math.round(Math.random() * 5000)
    });
}
// create the FlexSheet control
let flexSheet = new wijmo.grid.sheet.FlexSheet('#grid');

// create and add a new sheet the control
var sheet = new wijmo.grid.sheet.Sheet();
sheet.name = "New Sheet";
sheet.itemsSource = data;
flexSheet.sheets.push(sheet);
//flexSheet.columns[1].isReadOnly = true;
// flexSheet.getColumn('downloads').isReadOnly = true;

// flexSheet.beginningEdit.addHandler(function (s, e) {
//   var col = s.columns[e.col];
//   if (col.binding == 'downloads') {
//   //   e.cancel = true;
//   }
// });