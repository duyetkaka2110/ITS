<template>
  <div class="container-fluid">
    <!-- the flexsheet -->
    <wj-flex-sheet :initialized="initializeFlexSheet">
      <wj-sheet
        :name="'unbound'"
        :rowCount="30"
        :columnCount="12"
        :itemsSource="data"
      >
      </wj-sheet>
    </wj-flex-sheet>
  </div>
</template>

<script>
import "@grapecity/wijmo.styles/wijmo.css";
import "bootstrap.css";
import Vue from "vue";
import "@grapecity/wijmo.vue2.core";
import "@grapecity/wijmo.vue2.grid.sheet";

let App = Vue.extend({
  name: "app",
  data: function () {
    return {
      isFrozen: false,
      flex: null,
      data: list,
    };
  },
  methods: {
    initializeFlexSheet: function (flex) {
      // remove header column, rows of sheet (A,B,C,D...)
      flex.columnHeaders.rows.removeAt(0);
      flex.rowHeaders.columns.removeAt(0);
      //   freeze
      flex.frozenRows = 1;
      flex.frozenColumns = 4;
      this.flex = flex;
    },
  },
});

new Vue({ render: (h) => h(App) }).$mount("#app");
</script>

<style>
.container-fluid .wj-flexsheet {
  height: 400px;
  margin: 6px 0;
}
</style>
