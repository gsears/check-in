<template>
  <div class="wrapper">
    <div class="y-row" ref="rows" v-for="(y, row) in 4" :key="y">
      <XYQuestionRange
        class="x-range"
        v-for="(x, col) in 4"
        :key="x"
        :name="name"
        :disableCells="disableCells"
        :selectedArray="selected"
        :xMin="xMinVal(col)"
        :xMax="xMaxVal(col)"
        :yMin="yMinVal(row)"
        :yMax="yMaxVal(row)"
        @change="handleChange($event)"
      ></XYQuestionRange>
    </div>
  </div>
</template>

<script>
import XYQuestionRange from "@c/XYQuestionRange.vue";

export default {
  // Register used components
  components: {
    XYQuestionRange
  },
  props: {
    name: String,
    multiselect: {
        type: Boolean,
        default: false
    },
    disableCells: {
        default: false,
    },
    xRanges: {
      default: 4
    },
    yRanges: {
      default: 4
    },
    xRangeLength: {
      default: 5
    },
    yRangeLength: {
      default: 5
    },
    xLabelLow: {
      type: String,
      default: "xLow"
    },
    xLabelHigh: {
      type: String,
      default: "xHigh"
    },
    yLabelLow: {
      type: String,
      default: "yLow"
    },
    yLabelHigh: {
      type: String,
      default: "yHigh"
    }
  },
  data() {
    return {
      selected: [
        {
          x: 3,
          y: 4
        },
        {
          x: 3,
          y: 4
        },
        {
          x: -2,
          y: 0
        },
        {
          x: -10,
          y: -10
        }
      ]
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
      var selected = this.selected.filter(coordinates => {
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
            if(this.multiselect) {
                this.selected.push(e.coordinates);
            } else {
                this.selected = [e.coordinates];
            }

        } else {
            var index = this.selected.findIndex(obj => {
                return obj.x === e.coordinates.x &&
                    obj.y === e.coordinates.y;
            });

            console.log("match!", index);

            this.selected.splice(index, 1);
        }
    }
  }
};
</script>

<style scoped>
.wrapper {
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
