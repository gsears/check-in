/*
app.js
Gareth Sears - 2493194S
*/

/*
 * This is the apps main javascript file. It uses webpack to build and inject it.
 */

// The (SASS) CSS styles for the app.
import "../css/app.scss";

// Bootstrap javascript (for tooltips etc.) with its jquery dependency
import "bootstrap";
import $ from "jquery";

// Font awesome fonts
import "@fortawesome/fontawesome-free/js/fontawesome";
import "@fortawesome/fontawesome-free/js/solid";
import "@fortawesome/fontawesome-free/js/regular";
import "@fortawesome/fontawesome-free/js/brands";

// Shepherd 'tour' library
// https://shepherdjs.dev/
import Shepherd from "shepherd.js";
// Make global for use in templates that need it (most of them!)
global.Shepherd = Shepherd;
// Mixin functions to create tour step buttons
global.firstTourStep = (tour, opts) => {
  tour.addStep({
    buttons: [{
      text: 'Next',
      action: tour.next,
      classes: 'btn btn-small btn-primary',
    }],
    ...opts, 
  })
}

global.tourStep = (tour, opts) => {
  tour.addStep({
    buttons: [{
      text: 'Previous',
      action: tour.back,
      classes: 'btn btn-small btn-secondary',
    },
    {
      text: 'Next',
      action: tour.next,
      classes: 'btn btn-small btn-primary',
    }],
    ...opts, 
  })
}

global.finalTourStep = (tour, opts) => {
  tour.addStep({
    buttons: [{
      text: 'Previous',
      action: tour.back,
      classes: 'btn btn-small btn-secondary',
    },
    {
      text: 'Finish',
      action: tour.next,
      classes: 'btn btn-small btn-success',
    }],
    ...opts, 
  })
}

// Tablesort library
// https://github.com/tristen/tablesort
import Tablesort from "tablesort";

// Vue, for the XYQuestion component. @c is a shorthand for the components folder
import Vue from "vue";
import XYQuestion from "@c/XYQuestion.vue";

// Enable popovers everywhere
$(function() {
  $('[data-toggle="popover"]').popover();
});

/**
 * Add custom sort function for tablesort
 */

const parseDateTime = (dateString) => {
  dateString = dateString.trim();

  const date = new Date();

  // If a date exists, set it on the date object.
  const dateMatch = dateString.match(/(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})/);
  if (dateMatch) {
    date.setDate(parseInt(dateMatch[1]));
    date.setMonth(parseInt(dateMatch[2] - 1));

    let yearString = dateMatch[3];

    if (yearString.length == 2) {
      yearString = "20" + yearString;
    }
    date.setYear(parseInt(yearString));
  }

  // If a time exists, set it on the date object.
  const timeMatch = dateString.match(/(\d{1,2})[\:](\d{2})/);
  if (timeMatch) {
    date.setHours(parseInt(timeMatch[1]));
    date.setMinutes(parseInt(timeMatch[2]));
  }

  return dateMatch || timeMatch ? date.getTime() : -1;
};

Tablesort.extend(
  "datesort",
  (item) => true,
  (a, b) => {
    return parseDateTime(b) - parseDateTime(a);
  }
);

/**
 * Make all tables sortable
 */
[...document.querySelectorAll("table")].forEach((table) => {
  new Tablesort(table);
});

/**
 * GLOBALLY AVAILABLE FUNCTIONS
 */

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

