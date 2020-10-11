// Tablesort library
// https://github.com/tristen/tablesort
import Tablesort from "tablesort";
import { check as dateCheck, sort as dateSort } from "./tablesort/date";
import { check as numberCheck, sort as numberSort } from "./tablesort/number";

Tablesort.extend("datesort", dateCheck, dateSort);
Tablesort.extend("numbersort", numberCheck, numberSort);

/**
 * Make all tables sortable
 */
[...document.querySelectorAll("table")].forEach((table) => {
  new Tablesort(table);
});
