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
          <!-- Note that i, j are indexed from 0, row and col from 1 -->
          <!-- id is used to index XYQuestionRanges in this.regions -->
          <div class="y-row" ref="rows" v-for="(row, j) in yRanges" :key="row">
            <XYQuestionRange
              class="x-range"
              v-for="(col, i) in xRanges"
              :id="j * xRanges + i"
              :key="col"
              :name="name"
              :mode="mode"
              :cellSizeInRem="cellSizeInRem"
              :dataPoints="dataPoints"
              :dataRegions="dataRegions"
              :xMin="xMinVal(i)"
              :xMax="xMaxVal(i)"
              :yMin="yMinVal(j)"
              :yMax="yMaxVal(j)"
              @pointChange="handlePointChange($event)"
              @regionClick="handleRegionChange($event)"
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
    points: {
      type: Object,
      default: function() {
        return {
          data: [],
          onChange: () => {},
          multiselect: false,
        };
      },
    },
    regions: {
      type: Object,
      default: function() {
        return {
          data: [],
          onChange: () => {},
          multiselect: false,
        };
      },
    },
    mode: {
      type: String,
      default: "region",
      validator: (value) => {
        return (
          ["readonly-danger", "readonly", "point", "region"].indexOf(value) !==
          -1
        );
      },
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
    return {
      dataPoints: this.points.data,
      dataRegions: this.regions.data,
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
    setPoints(xMin, xMax, yMin, yMax) {
      var points = this.points.filter((coordinates) => {
        return (
          coordinates.x >= xMin &&
          coordinates.x <= xMax &&
          coordinates.y >= yMin &&
          coordinates.y <= yMax
        );
      });
      return points;
    },
    handlePointChange(e) {
      if (this.mode === "point") {
        // If point is checked
        if (e.checked) {
          if (this.points.multiselect) {
            this.dataPoints.push(e.coordinates);
          } else {
            this.dataPoints = [e.coordinates];
          }
          // Remove (first found) from data array
        } else {
          var index = this.dataPoints.findIndex((obj) => {
            return obj.x === e.coordinates.x && obj.y === e.coordinates.y;
          });
          this.dataPoints.splice(index, 1);
        }

        // Run the callback for a point change
        this.points.onChange([...this.dataPoints]);
      }
    },
    handleRegionChange(e) {
      if (this.mode === "region") {
        const region = e.data;
        // Use set to trigger redraw on array change
        // https://stackoverflow.com/questions/44800470/vue-js-updated-array-item-value-doesnt-update-in-page
        this.$set(this.dataRegions, region.id, region);
        // Provide just populated regions to the callback.
        this.regions.onChange(this.dataRegions.filter((region) => region));
      }
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
