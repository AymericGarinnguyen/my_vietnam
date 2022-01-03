import * as Generic from "./generic.js";

// create variable modal
let modal = null;

/**
 * Open the modal
 *
 * @param {string} target
 */
async function openModal(target) {
  // load modal in variable "modal"
  modal = await ajaxModal(target);
  // "modal" display flex
  modal.style.display = "flex";
  // define "modal" attibutes
  modal.setAttribute("aria-hidden", false);
  modal.setAttribute("aria-modal", true);
  // on click, close modal and avoid click on div ".stop-propagation"
  // on button .close click, close modal
  modal.querySelector(".close").addEventListener("click", closeModal);
  modal.addEventListener("click", closeModal);
  modal
    .querySelector(".stop-propagation")
    .addEventListener("click", stopPropagation);
  // on .choice-form click
  document.querySelectorAll(".choice-form").forEach((button) => {
    button.addEventListener("click", (e) => {
      e.preventDefault();
      formSelection(button);
    });
  });
  // call checkForm function
  document.querySelectorAll("form").forEach(Generic.checkForm);

  // After error message, remove errors messages on input keydown
  Generic.resetErrors("input", "keydown");

  document.querySelector("body").style.overflow = "hidden";
}

/**
 * Close the modal
 */
function closeModal() {
  // define "modal" attibutes
  modal.setAttribute("aria-hidden", true);
  modal.setAttribute("aria-modal", false);
  modal.style.display=null;
  // remove events listenner
  modal.removeEventListener("click", closeModal);
  modal.querySelector(".close").removeEventListener("click", closeModal);
  modal
    .querySelector(".stop-propagation")
    .removeEventListener("click", stopPropagation);
  document.querySelectorAll(".formChoice").forEach((button) => {
    button.removeEventListener("click", formSelection);
  });
  document.querySelector("body").style.overflow = "initial";
}

/**
 * Defines which form to choose
 *
 * @param {object} button
 */
function formSelection(button) {
  // remove class "hide" from buttons
  [].forEach.call(document.querySelectorAll(".choice-form"), function (el) {
    el.classList.remove("hide");
  });
  // add class "hide" to this button
  button.classList.add("hide");
  // remove class "hide" to this href form
  document.querySelector(button.getAttribute("href")).classList.remove("hide");
  // add class "hide" to the dataset form
  document.querySelector(button.dataset.form).classList.add("hide");
}

/**
 * Stop propagation
 *
 * @param {object} e
 */
function stopPropagation(e) {
  e.stopPropagation();
}

/**
 * Define if modal already existe, else load it
 *
 * @param {string} url
 * @return {object}
 */
async function ajaxModal(url) {
  // define modal id target
  const target = "#" + url.split("#")[1];
  // select the target modal
  const exist = document.querySelector("#" + url.split("#")[1]);
  // if this modal already existe, retun it
  if (exist !== null) {
    return exist;
  }
  // else load the modal page in the constante "modalHtml"
  const modalHtml = await fetch(url).then((response) => response.text());
  // create an empty range, load modalHtml inside with createContextualFragment and select the targetof this fragment in constante "fragment"
  const fragment = document
    .createRange()
    .createContextualFragment(modalHtml)
    .querySelector(target);
  // fragment is inserted before header
  const header = document.querySelector("header");
  header.parentNode.insertBefore(fragment, header.previousSibling);
  return fragment;
}

export { openModal, closeModal };
