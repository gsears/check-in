/*
number.js
Gareth Sears - 2493194S

Refactor of https://github.com/tristen/tablesort custom sort method
so it can be used as an import. Most code taken from:
https://github.com/tristen/tablesort/tree/gh-pages/src/sorts
*/

var cleanNumber = function(i) {
    return i.replace(/[^\-?0-9.]/g, "");
  },
  compareNumber = function(a, b) {
    a = parseFloat(a);
    b = parseFloat(b);

    a = isNaN(a) ? 0 : a;
    b = isNaN(b) ? 0 : b;

    return a - b;
  };

function check(item) {
  return (
    item.match(/^[-+]?[£\x24Û¢´€]?\d+\s*([,\.]\d{0,2})/) || // Prefixed currency
    item.match(/^[-+]?\d+\s*([,\.]\d{0,2})?[£\x24Û¢´€]/) || // Suffixed currency
    item.match(/^[-+]?(\d)*-?([,\.]){0,1}-?(\d)+([E,e][\-+][\d]+)?%?$/) // Number
  );
}

function sort(a, b) {
  a = cleanNumber(a);
  b = cleanNumber(b);

  return compareNumber(b, a);
}

export { check, sort };
