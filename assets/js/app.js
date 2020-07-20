/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.scss';

// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';

// console.log('Hello Webpack Encore! Edit me in assets/js/app.js');

import Vue from 'vue';
import XYQuestion from '@c/XYQuestion.vue';

global.XYQuestionWidgetFactory = (el, initialData, opts) => {

  const defaultOpts = {
    multiselect: false,
    disableCells: false,
    cellSizeInRem: 1.2
  };

  if(initialData) {
    if(!Array.isArray(initialData)) {
      initialData = [initialData];
    }
  } else {
    initialData = [];
  }

  return new Vue({
    el,
    render: h => h(XYQuestion, {
      props: {
        ...defaultOpts,
        ...opts,
        initialData
      }
    })
  });
}


