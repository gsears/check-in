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

/**
 * Binds two form inputs together so that their values are always equal.
 * Used to combine sliders and numberboxes.
 *
 * @param {HTMLElement} The first form input
 * @param {HTMLElement} The second form input
 * @param {HTMLElement} Optional: The element whose value is the initial value for both elements.
 */
global.bindInputs = (inputOne, inputTwo, primaryNode) => {
  primaryNode = primaryNode || inputOne;

  const copyFirstInputToSecond = function() {
    inputTwo.value = inputOne.value;
  };

  const copySecondInputToFirst = function() {
    inputOne.value = inputTwo.value;
  };

  inputOne.oninput = copyFirstInputToSecond;
  inputTwo.oninput = copySecondInputToFirst;

  // Initialise
  if (primaryNode === inputOne) {
    copyFirstInputToSecond();
  } else {
    copySecondInputToFirst();
  }
};

/**
 * Used to allow a checkbox to filter table rows using a particular predicate function.
 * @param {HTMLElement} tableElement
 * @param {HTMLElement} checkboxElement
 * @param {function} filterPredicate, in the form (tr) => {} where tr is the table row
 */
global.filterTableWithCheckbox = (
  tableElement,
  checkboxElement,
  filterPredicate
) => {
  const trs = tableElement.querySelectorAll("tbody tr");

  const filterFunction = () => {
    trs.forEach((tr) => {
      tr.style.display = filterPredicate(tr) ? "" : "none";
    });
  };

  checkboxElement.onchange = function() {
    if (this.checked) {
      filterFunction();
    } else {
      trs.forEach((tr) => {
        tr.style.display = "";
      });
    }
  };

  // Initialise
  filterFunction();
  checkboxElement.checked = true;
};
