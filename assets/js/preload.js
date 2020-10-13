/*
preload.js
Gareth Sears - 2493194S
*/

/*
    A tiny javascript function that we need ready when the
    document loads in order to register our Vue XY component
    as a form element.

    This is done because the javascript in custom_types.html.twig
    is injected inline, where our vue component hasn't yet been registered.
*/

addLoadEvent = (func) => {
  var oldonload = window.onload;
  if (typeof window.onload != "function") {
    window.onload = func;
  } else {
    window.onload = function () {
      if (oldonload) {
        oldonload();
      }
      func();
    };
  }
};
