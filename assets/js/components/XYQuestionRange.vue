<template>
  <div class="range">
    <div class="y-row" v-for="row in yMax - yMin + 1" :key="row">
      <XYQuestionCell
        v-for="col in xMax - xMin + 1"
        :key="col"
        :name="name"
        :size=1.5
        :count="getCellCount(xVal(col), yVal(row))"
        :x-value="xVal(col)" :y-value="yVal(row)"
        @change="handleChange($event)">
      </XYQuestionCell>
    </div>
  </div>
</template>

<script>
import XYQuestionCell from "@c/XYQuestionCell.vue";

export default {
  // Register used components
  components: {
    XYQuestionCell
  },
  props: {
    name: String,
    selectedArray: Array,
    xMin: Number,
    xMax: Number,
    yMin: Number,
    yMax: Number,
  },
  data() {
    return {
      message: "Hello"
    };
  },
  methods: {
    xVal(col) {
      return this.xMin + col - 1;
    },
    yVal(row) {
      return this.yMax-row + 1;
    },
    getCellCount(x, y) {
      return this.selectedArray.filter(coordinates => {
        return coordinates.x === x && coordinates.y === y;
      }).length;
    },
    handleChange(e) {
      this.$emit('change', e);
    }
  }
};
</script>

<style scoped>
.range {
  display: inline-block;
}

.row {
  margin: 0
}
</style>
