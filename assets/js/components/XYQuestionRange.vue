<template>
  <div id="app">
    {{ message }}
    <div class="row" v-for="y in yMax - yMin + 1" :key="y">
      <XYQuestionCell
        v-for="x in xMax - xMin + 1"
        :key="x"
        :size=1.5
        :count=20
        :x-value="xMin + x - 1" :y-value="yMax-y + 1"
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
    xMin: Number,
    xMax: Number,
    yMin: Number,
    yMax: Number,
    xLabelLow: String,
    xLabelHigh: String,
    yLabelLow: String,
    yLabelHigh: String,
  },
  data() {
    return {
      message: "Hello"
    };
  },
  methods: {
    handleChange(e) {
            console.log('onChange', e);
        }
  }
};
</script>

<style>
#app {
  font-size: 18px;
  font-family: "Roboto", sans-serif;
  color: blue;
  min-width: max-content;
  white-space: nowrap;
}

.row {
  margin: 0
}
</style>
