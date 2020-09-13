/*
xy.js
Gareth Sears - 2493194S

Make the XYQuestion component available within the app.
Uses VueJS
https://vuejs.org/
*/
import Vue from "vue";
import XYQuestion from "@c/XYQuestion.vue"; //@c is a shorthand for the components folder

/**
 * A factory function used to bind the XYQuestionWidget to a named
 * html element (usually a div by id).
 *
 * @param {HTMLElement} el
 * @param {Object} props to pass to the XYQuestion
 */
global.XYQuestionWidgetFactory = (el, props) => {
  return new Vue({
    el,
    render: (h) =>
      h(XYQuestion, {
        props: {
          ...props,
        },
      }),
  });
};
