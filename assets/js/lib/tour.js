/*
tour.js
Gareth Sears - 2493194S

Components for the shepherd 'tour' library
https://shepherdjs.dev/
*/
import Shepherd from "shepherd.js";

// Make global for use in templates that need it (most of them!)
global.Shepherd = Shepherd;

// Helper functions to create tour steps
global.firstTourStep = (tour, opts) => {
  tour.addStep({
    buttons: [
      {
        text: "Next",
        action: tour.next,
        classes: "btn btn-small btn-primary",
      },
    ],
    ...opts,
  });
};

global.tourStep = (tour, opts) => {
  tour.addStep({
    buttons: [
      {
        text: "Previous",
        action: tour.back,
        classes: "btn btn-small btn-secondary",
      },
      {
        text: "Next",
        action: tour.next,
        classes: "btn btn-small btn-primary",
      },
    ],
    ...opts,
  });
};

global.finalTourStep = (tour, opts) => {
  tour.addStep({
    buttons: [
      {
        text: "Previous",
        action: tour.back,
        classes: "btn btn-small btn-secondary",
      },
      {
        text: "Finish",
        action: tour.next,
        classes: "btn btn-small btn-success",
      },
    ],
    ...opts,
  });
};
