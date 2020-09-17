/*
app.js
Gareth Sears - 2493194S

This is the apps main javascript file. It uses webpack to build and inject it.
*/

import "../css/app.scss"; // The (SASS) CSS styles for the app.
import "./boostrap"; // Bootstrap import
import "./tour"; // Shepherd js tour library import
import "./tables"; // Tablesort library and methods.
import "./xy"; // Custom XY component and Vue libraries

/**
 * Add loading UI for lengthy serverside database operations / external api calls.
 */

const displayLoadingBar = () => {
  const loadingBar = document.getElementById("loading-bar");
  loadingBar.classList.remove("d-none");
};

// On a link click.
const links = Array.from(document.querySelectorAll("a"));
links.forEach((l) => {
  l.onclick = displayLoadingBar;
});

// On a submit click.
const submitButtons = Array.from(
  document.querySelectorAll('button[type="submit"]')
);
submitButtons.forEach((b) => {
  b.onclick = function() {
    submitButtons.forEach((b) => b.classList.add("disabled"));
    this.innerHTML =
      '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
    displayLoadingBar();
  };
});
