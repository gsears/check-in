<template>
<div class="wrapper" :style="sizeStyle()">
        <input :name="xyDisplay" type="checkbox" @change="handleChange($event)" :value="xyDisplay">
        <label :for="xyDisplay">
            {{count}}
        </label>
</div>
</template>

<script>
export default {
    props: {
        xValue: Number,
        yValue: Number,
        count: Number,
        size: Number,
    },
    computed: {
        xyDisplay() {
            return this.xValue + ' - ' + this.yValue;
        }
    },
    methods: {
        sizeStyle() {
            return {
                height: this.size + 'rem',
                width: this.size + 'rem',
                fontSize: this.size + 'rem',
            }
        },
        handleChange(e) {
            this.$emit('change', {
                x: this.xValue,
                y: this.yValue,
            });
        }
    },
    data() {
        return {
            selected: false
        };
    },
};
</script>

<style>
*,
*::before,
*::after {
    box-sizing: inherit;
}

/*style wrapper to give some space*/
.wrapper {
    display: inline-block;
    position: relative;
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
