<template>
  <div class="wrapper">
    <input
      :name="name"
      type="checkbox"
      :checked="count"
      :disabled="disabled"
      @change="handleChange($event)"
      :value="value"
    />
    <label :for="value">{{ countDisplay }}</label>
  </div>
</template>

<script>
export default {
  props: {
    name: String,
    disabled: {
      type: Boolean,
      default: false,
    },
    xValue: Number,
    yValue: Number,
    count: {
      type: Number,
      default: null,
    },
    size: Number,
  },
  computed: {
    value() {
      return `{x:${this.xValue},y:${this.yValue}}`;
    },
    countDisplay() {
      return this.count > 1 ? this.count : "";
    },
  },
  methods: {
    handleChange(e) {
      this.$emit("change", {
        coordinates: {
          x: this.xValue,
          y: this.yValue,
        },

        checked: e.target.checked,
      });
    },
  },
};
</script>

<style scoped>
*,
*::before,
*::after {
  box-sizing: inherit;
}

/*style wrapper to give some space*/
.wrapper {
  display: inline-block;
  position: relative;
  height: 1em;
  width: 1em;
  font-size: 1em;
}

/*style and hide original checkbox*/
.wrapper input {
  height: 100%;
  width: 100%;
  left: 0;
  position: absolute;
  top: 0;
  cursor: pointer;
  opacity: 1;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  border: solid 1px #f2f2f2;
}

.wrapper input:disabled {
  cursor: initial;
}

.wrapper label {
  height: 100%;
  width: 100%;
  left: 0;
  opacity: 1;
  position: absolute;
  top: 0;
  margin: 0;
  pointer-events: none;
  font-size: 0.7em;
  text-align: center;
  color: transparent;
  line-height: initial;
}

/*reveal check for 'on' state*/
.wrapper input:checked + label {
  background-color: blue;
  color: white;
}

/*focus styles*/
.wrapper input:focus + label {
  box-shadow: 0 0 0 3px #ffbf47;
}
</style>
