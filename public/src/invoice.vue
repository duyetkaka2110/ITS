<template>
  <div class="container-fluid">
    <!-- the flexsheet -->
    <wj-flex-sheet :initialized="initializeFlexSheet">
      <wj-sheet :name="'unbound'" :loadedRows="autoSizeColumns"> </wj-sheet>
    </wj-flex-sheet>
  </div>
</template>

<script>
import "@grapecity/wijmo.styles/wijmo.css";
import "bootstrap.css";
import Vue from "vue";
// import * as wjcCore from "@grapecity/wijmo";
// import "@grapecity/wijmo.vue2.core";
import "@grapecity/wijmo.vue2.grid.sheet";

// wjcCore.setLicenseKey(
//   "477492474881697#B0XzzWYmpjIyNHZisnOiwmbBJye0ICRiwiI34TUuJFMStWbqFzZ8MGbHx6NhZ5ROlTdolGen9GUvx4KBRXNPVUW5Y5SaplcG3UbQVTOYp6atB5L8MzKodjQ7ImQINzRylVW4IWTlxWR0BVOORXRQRjMHNXO6l5K9BTd92EOrITTGt6K8AVZNtkdvlTYY3CTEpEdsVTc6QXQERVd7R7diR5Y6VmcjB7TJJzUSFESvIDOldEZyE4ZFpkZolWT6lFWZN5SGVVUy2mUOB5cyVXMBtCd8dzUXJkcW9kUrQkQFlkQ6cHMGR7NpF7boJWd5tkZwIWWDF4ZTl7b4ckbqRFe6llNBJWd5FzcBNUdsVVZRRTO584KYh7cnBFaUlncQd6SUxGRk9ka7cmepVESaF4KtZ5Q6gVc48kYHxWekJ7bmZTUzN4ZRhkR7Y4TKdWWOlFePZmMMdDcpVkdxNlcvQ6KNBjRGlVOstCeoR5ZhNVOYNmTQdkI0IyUiwiIyQURygTRwEjI0ICSiwyNyYDN8kDNxcTM0IicfJye&Qf35VfikEMyIlI0IyQiwiIu3Waz9WZ4hXRgACdlVGaThXZsZEIv5mapdlI0IiTisHL3JSNJ9UUiojIDJCLi86bpNnblRHeFBCIyV6dllmV4J7bwVmUg2Wbql6ViojIOJyes4nILdDOIJiOiMkIsIibvl6cuVGd8VEIgc7bSlGdsVXTg2Wbql6ViojIOJyes4nI4YkNEJiOiMkIsIibvl6cuVGd8VEIgAVQM3EIg2Wbql6ViojIOJyes4nIzMEMCJiOiMkIsISZy36Qg2Wbql6ViojIOJyes4nIVhzNBJiOiMkIsIibvl6cuVGd8VEIgQnchh6QsFWaj9WYulmRg2Wbql6ViojIOJyebpjIkJHUiwiI4IjNwcDMgATM5AzMyAjMiojI4J7QiwiIx8CMuAjL7ITMiojIz5GRiwiI+S09ayL9Pyb9qCq9jK88GO887K88XO882O88sO88wK88iojIh94QiwiI7kjNxgDO4cDNykDN7cDNiojIklkIs4XXbpjInxmZiwiIyY7MyAjMiojIyVmWiDS"
// );

let App = Vue.extend({
  name: "app",
  data: function () {
    return {
      isFrozen: false,
      flex: true,
    };
  },
  methods: {
    initializeFlexSheet: function (flex) {
      // remove header column, rows of sheet (A,B,C,D...)
      //   flex.columnHeaders.rows.removeAt(0);
      //   flex.rowHeaders.columns.removeAt(0);
      // console.info(flex)
      flex.frozenColumns = 5; //   freeze column

      flex.isTabHolderVisible = false; // not show sheet name
      //   flex.columnHeaders.columns.defaultSize = 50; // header width size
      flex.columnHeaders.rows.defaultSize = 50; // header height size
      let setHeaderName = false,
        colHeader,
        width;
      //   flex.deferUpdate(() => {
      for (let row = 0; row < list.length; row++) {
        for (let col = 0; col < headerkey.length; col++) {
          colHeader = flex.columnHeaders.columns[col];
          if (!setHeaderName) {
            // change header name from A, B , C....
            colHeader.header = headername[headerkey[col]]["name"];
            colHeader.wordWrap = true;
            colHeader.align = "center";
            console.info(colHeader.grid);

            if (headername[headerkey[col]]["class"])
              colHeader.cssClassAll = headerkey[col] //headername[headerkey[col]]["class"];

            if (headername[headerkey[col]]["width"]) {
              width = headername[headerkey[col]]["width"];
              // colHeader.maxWidth =
              //   headername[headerkey[col]]["width"];
              //   console.info( headername[headerkey[col]]["width"])
            }
          }
          // set cell data
          flex.setCellData(row, col, list[row][headerkey[col]]);
        }
        setHeaderName = true;
      }
      //   });
      // this.flex = flex;
    },
    autoSizeColumns: function (grid) {
      grid.autoSizeColumns();
    },
  },
});

for (let col = 0; col < headerkey.length; col++) {
    
}
new Vue({ render: (h) => h(App) }).$mount("#app");

</script>

<style>
.container-fluid .wj-flexsheet {
  height: 400px;
  margin: 6px 0;
}
</style>
