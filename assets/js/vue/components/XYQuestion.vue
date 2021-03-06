<!-- XYQuestion.vue  -->
<!-- Gareth Sears - 2493194S -->

<!-- A javascript component in Vue.js that is used for displaying XY Questions. -->
<!-- It allows region selecting (danger zones) and point selection, as well as -->
<!-- displaying multiple points (with counts where points overlap.) -->

<template>
  <div class="d-flex align-items-center">
    <div class>
      <h2 class="label text-right mr-2 xlabel-left">{{ xLabelLow }}</h2>
    </div>
    <div class="text-center">
      <h2 class="label">{{ yLabelHigh }}</h2>
      <div class="grid-wrapper">
        <!-- Note that i, j are indexed from 0, row and col from 1 -->
        <!-- id is used to index XYQuestionRanges in this.regions -->
        <div class="y-row" ref="rows" v-for="(row, j) in yRanges" :key="row">
          <XYQuestionRange
            class="x-range"
            v-for="(col, i) in xRanges"
            :key="col"
            :name="name"
            :mode="mode"
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
        <h2 class="label">{{ yLabelLow }}</h2>
      </div>
    </div>
    <div class>
      <h2 class="label text-left ml-2 xlabel-right">{{ xLabelHigh }}</h2>
    </div>
  </div>
</template>

<script>
import XYQuestionRange from "./XYQuestionRange.vue";

export default {
  // Register used components
  components: {
    XYQuestionRange,
  },
  props: {
    name: String,
    // An array of point coordinates in the form {x: val, y: val}
    points: {
      type: Object,
      default: function () {
        return {
          data: [],
          onChange: () => {},
          multiselect: false,
        };
      },
    },
    // An array of regions, in the form {xMin: val, xMax: val, yMin: val, yMax: val}
    regions: {
      type: Object,
      default: function () {
        return {
          data: [],
          onChange: () => {},
          multiselect: false,
        };
      },
    },
    // Used to choose between read only as well as selecting points or regions
    mode: {
      type: String,
      default: "point",
      validator: (value) => {
        return (
          ["readonly-danger", "readonly", "point", "region"].indexOf(value) !==
          -1
        );
      },
    },
    // The component is extendable, and can be used for greater / smaller grids
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
    // Axis labels
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
  // Reactive data
  data() {
    return {
      dataPoints: [...this.points.data],
      dataRegions: [...this.regions.data],
    };
  },
  methods: {
    // Maths for calculating grids
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
    // Update the points in a region
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
    // When a point is clicked
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
          const index = this.dataPoints.findIndex((obj) => {
            return obj.x === e.coordinates.x && obj.y === e.coordinates.y;
          });
          this.dataPoints.splice(index, 1);
        }

        // Run the callback for a point change
        this.points.onChange([...this.dataPoints]);
      }
    },

    // When a region is clicked
    handleRegionChange(e) {
      if (this.mode === "region") {
        const region = e.data;
        // Find in array, otherwise push
        const index = this.dataRegions.findIndex((obj) => {
          return (
            obj.xMin === region.xMin &&
            obj.xMax === region.xMax &&
            obj.yMin === region.yMin &&
            obj.yMax === region.yMax
          );
        });

        if (index < 0) {
          // Add if missing
          this.dataRegions.push(region);
        } else {
          // Update if found
          this.$set(this.dataRegions, index, region);
        }

        // Provide just the regions with risk levels in the callback.
        const selectedRegions = [
          ...this.dataRegions.filter((region) => {
            return region.riskLevel > 0;
          }),
        ];

        this.regions.onChange(selectedRegions);
      }
    },
  },
};
</script>

<style scoped>
.label {
  font-weight: bold;
  font-size: 1rem;
}
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

.xlabel-left {
  writing-mode: vertical-rl;
  height: max-content;
  transform: rotate(180deg);
}

.xlabel-right {
  writing-mode: vertical-rl;
  height: max-content;
}
</style>
