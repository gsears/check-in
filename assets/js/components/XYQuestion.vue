<template>
  <div class="container">
    <div class="row">
      <div class="col"></div>
      <div class="col-xs-6">
        <h2 class="h5">{{ yLabelHigh }}</h2>
      </div>
      <div class="col"></div>
    </div>
    <div class="row align-items-center">
      <div class="col">
        <h2 class="h5 text-right">{{ xLabelLow }}</h2>
      </div>
      <div class="col-xs-6">
        <div class="grid-wrapper">
          <div class="y-row" ref="rows" v-for="(y, row) in 4" :key="y">
            <XYQuestionRange
              class="x-range"
              v-for="(x, col) in 4"
              :key="x"
              :name="name"
              :disableCells="disableCells"
              :cellSizeInRem="cellSizeInRem"
              :selectedArray="selected"
              :xMin="xMinVal(col)"
              :xMax="xMaxVal(col)"
              :yMin="yMinVal(row)"
              :yMax="yMaxVal(row)"
              @change="handleChange($event)"
            ></XYQuestionRange>
          </div>
        </div>
      </div>
      <div class="col">
        <h2 class="h5 text-left">{{ xLabelHigh }}</h2>
      </div>
    </div>
    <div class="row">
      <div class="col"></div>
      <div class="col-xs-6">
        <h2 class="h5">{{ yLabelLow }}</h2>
      </div>
      <div class="col"></div>
    </div>
  </div>
</template>

<script>
import XYQuestionRange from "@c/XYQuestionRange.vue";

export default {
  // Register used components
  components: {
    XYQuestionRange,
  },
  props: {
    name: String,
    onChange: Function,
    initialData: Array, // Data array
    multiselect: {
      type: Boolean,
      default: false,
    },
    disableCells: {
      default: false,
    },
    cellSizeInRem: {
      type: Number,
      default: 1.5,
    },
    xRanges: {
      default: 4,
    },
    yRanges: {
      default: 4,
    },
    xRangeLength: {
      default: 5,
    },
    yRangeLength: {
      default: 5,
    },
    xLabelLow: {
      type: String,
      default: "xLow",
    },
    xLabelHigh: {
      type: String,
      default: "xHigh",
    },
    yLabelLow: {
      type: String,
      default: "yLow",
    },
    yLabelHigh: {
      type: String,
      default: "yHigh",
    },
  },
  data() {
    console.log(this.initialData);
    return {
      selected: this.initialData, // Copy
    };
  },
  methods: {
    xMinVal(col) {
      return (
        (this.xRanges / 2) * (this.xRangeLength * -1) + col * this.xRangeLength
      );
    },
    xMaxVal(col) {
      return this.xMinVal(col) + this.xRangeLength - 1;
    },
    yMinVal(row) {
      return this.yMaxVal(row) - this.yRangeLength + 1;
    },
    yMaxVal(row) {
      return (
        (this.yRanges / 2) * this.yRangeLength - row * this.yRangeLength - 1
      );
    },
    setSelected(xMin, xMax, yMin, yMax) {
      var selected = this.selected.filter((coordinates) => {
        return (
          coordinates.x >= xMin &&
          coordinates.x <= xMax &&
          coordinates.y >= yMin &&
          coordinates.y <= yMax
        );
      });
      return selected;
    },
    handleChange(e) {
      if (e.checked) {
        if (this.multiselect) {
          this.selected.push(e.coordinates);
        } else {
          this.selected = [e.coordinates];
        }
      } else {
        var index = this.selected.findIndex((obj) => {
          return obj.x === e.coordinates.x && obj.y === e.coordinates.y;
        });
        this.selected.splice(index, 1);
      }

      // Run the callback with the updated selection.
      this.onChange([...this.selected]);
    },
  },
};
</script>

<style scoped>
.grid-wrapper {
  line-height: 0%;
}

.y-row {
  margin: 0;
  white-space: nowrap;
}
.x-range {
  border: solid 1px gray;
}
.x-divider {
  display: inline-block;
  height: 100%;
  min-width: 2px;
  background-color: black;
}
</style>
