/*
boostrap.js
Gareth Sears - 2493194S

Import bootstrap and jquery dependencies
*/

import $ from "jquery";
import "bootstrap";
import "@fortawesome/fontawesome-free/js/fontawesome";
import "@fortawesome/fontawesome-free/js/solid";
import "@fortawesome/fontawesome-free/js/regular";
import "@fortawesome/fontawesome-free/js/brands";

// Enable popovers everywhere
$(function() {
  $('[data-toggle="popover"]').popover();
});
