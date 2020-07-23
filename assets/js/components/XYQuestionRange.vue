<template>
  <div class="range">
    <!-- Overlay region selector -->
    <div
      :class="{
        selectable: !disabledRegions,
        warning: riskLevel() === 1 && !disabledColors,
        danger: riskLevel() === 2 && !disabledColors,
      }"
      @click="handleRegionClick($event)"
    ></div>
    <!-- Cell Range -->
    <div>
      <div class="y-row" v-for="row in yMax - yMin + 1" :key="row">
        <XYQuestionCell
          v-for="col in xMax - xMin + 1"
          :key="col"
          :name="name"
          :disabled="disabledPoints"
          :size="cellSizeInRem"
          :count="getCellCount(xVal(col), yVal(row))"
          :x-value="xVal(col)"
          :y-value="yVal(row)"
          @change="handlePointChange($event)"
        >
        </XYQuestionCell>
      </div>
    </div>
  </div>
</template>

<script>
import XYQuestionCell from "@c/XYQuestionCell.vue";

export default {
  // Register used components
  components: {
    XYQuestionCell,
  },
  props: {
    id: Number,
    name: String,
    mode: String,
    cellSizeInRem: Number,
    dataPoints: Array,
    dataRegions: Array,
    xMin: Number,
    xMax: Number,
    yMin: Number,
    yMax: Number,
  },
  computed: {
    disabledColors() {
      return ["readonly", "point"].indexOf(this.mode) >= 0;
    },
    disabledRegions() {
      return ["readonly-danger", "readonly", "point"].indexOf(this.mode) >= 0;
    },
    disabledPoints() {
      return ["readonly-danger", "readonly", "region"].indexOf(this.mode) >= 0;
    },
  },
  methods: {
    riskLevel() {
      const region = this.dataRegions.filter((region) => {
        return this.id == region.id;
      });
      const riskLevel = region[0] ? region[0].riskLevel : 0;
      return riskLevel;
    },
    xVal(col) {
      return this.xMin + col - 1;
    },
    yVal(row) {
      return this.yMax - row + 1;
    },
    getCellCount(x, y) {
      // Sums all the data points matching the x, y values
      return this.dataPoints.filter((coordinates) => {
        return coordinates.x === x && coordinates.y === y;
      }).length;
    },
    handlePointChange(e) {
      this.$emit("pointChange", e);
    },
    handleRegionClick(e) {
      this.$emit("regionClick", {
        event: e,
        data: {
          id: this.id,
          riskLevel: (this.riskLevel() + 1) % 3,
          xMin: this.xMin,
          xMax: this.xMax,
          yMin: this.yMin,
          yMax: this.yMax,
        },
      });
    },
  },
};
</script>

<style scoped>
.range {
  display: inline-block;
  position: relative;
}

.selectable {
  position: absolute;
  height: 100%;
  width: 100%;
  cursor: pointer;
  z-index: 10;
  opacity: 0.3;
}

.warning {
  background-color: #f0e68c;
}

.danger {
  background-color: #f08080;
}

.row {
  margin: 0;
}
</style>
