// import files
import * as Ajax from "./ajax.js";
import * as Modal from "./modal.js";
import * as Generic from "./generic.js";
import * as Widget from "./widgetApi.js";
import Form from "./class/Form.js";

const formControl = new Form();

document.addEventListener("DOMContentLoaded", function () {
  /* *********************************************************************************************
                                        WIDGETS
  ********************************************************************************************* */
  const widget = document.querySelector(".widget-container");
  // on #widget-arrow click, toggle aria-hidden
  document
    .querySelector("#widget-arrow")
    .addEventListener("click", function () {
      if (widget.getAttribute("aria-hidden") === "true") {
        widget.setAttribute("aria-hidden", false);
      } else {
        widget.setAttribute("aria-hidden", true);
      }
    });

  /* WIDGET WEATHER */
  // on cities change
  document.querySelector("#cities").addEventListener("change", function (e) {
    // stop refresh
    e.preventDefault();
    // reset .forecast-weather
    document.querySelector(".forecast-weather").innerHTML = "";
    // call widget wetaher function with city value
    Widget.weather(this.value);
  });
  // init weather widget with hanoi city
  Widget.weather("hanoi");
  // init actual local time
  Widget.actualTime();

  /* WIDGET CURRENCY CHANGE */
  const rate = document.querySelector("#rate");
  const euro = document.querySelector("#euro");
  const dong = document.querySelector("#dong");
  // init widget currencyChange function
  Widget.currencyChange(rate, euro, dong);

  /* *********************************************************************************************
                                        MODAL
  ********************************************************************************************* */

  if (document.querySelector("#modal")) {
    // on #modal click
    document.querySelector("#modal").addEventListener("click", function (e) {
      // stop refresh
      e.preventDefault();
      // send attribute href to function openModal in modal.js
      Modal.openModal(e.target.getAttribute("href"));
    });
  }

  // escape touch presses
  window.addEventListener("keydown", function (e) {
    // if modal is open
    if (document.querySelector("#modal-user")) {
      if (e.key === "Escape" || e.key === "Esc") {
        // close modal
        Modal.closeModal();
      }
    }
  });

  /* *********************************************************************************************
                                        LOGOUT
  ********************************************************************************************* */

  // destroy localStrorage on logout
  if (document.querySelector("#logout")) {
    document.querySelector("#logout").addEventListener("click", function () {
      if (localStorage.getItem("products")) {
        localStorage.removeItem("products");
      }
    });
  }

  /* *********************************************************************************************
                                        FORMS
  ********************************************************************************************* */
  // call checkForm function
  document.querySelectorAll(".form").forEach(Generic.checkForm);

  // After error message, remove errors messages on input or textarea click
  Generic.resetErrors("input, textarea", "keydown");

  // After expiry error message, on select change, remove errors
  Generic.resetErrors(".expiry", "change");

  /* *********************************************************************************************
                                        SHOP
  ********************************************************************************************* */

  // select all button .shop
  document.querySelectorAll(".shop").forEach(function (button) {
    // on this button click
    button.addEventListener("click", function (e) {
      // stop refresh
      e.preventDefault();
      // if button data-action = add
      if (this.dataset.action === "add") {
        // send to Ajax.js function addProduct
        Ajax.addProduct(this.dataset.id);
      } else {
        // send to Ajax.js function deleteProduct
        Ajax.deleteProduct(this.dataset.id);
      }
    });
  });

  // select form in cart
  document.querySelectorAll(".form-quantity").forEach((quantity) => {
    // on form change
    quantity.addEventListener("change", function (e) {
      // stop refresh
      e.preventDefault();
      // select this select
      const select = document.querySelector(`#quantity${this.dataset.id}`);
      if (select.value === "0") {
        // delect product with ajax
        Ajax.deleteProduct(select.dataset.id);
      } else {
        // send form data to ajax.js
        const form = new FormData(this);
        Ajax.updateProduct(form, select.dataset.id);
      }
    });
  });


  document.querySelectorAll('.dish-figure').forEach(el => {
    el.addEventListener('touchstart', function(e) {
      e.preventDefault();
      const figcaption = el.getElementsByTagName('figcaption')[0];
      if(figcaption.style.opacity === "0") {
        figcaption.style.opacity = "1";
      } else {
        figcaption.style.opacity = "0";
      }
    });
  });
});
