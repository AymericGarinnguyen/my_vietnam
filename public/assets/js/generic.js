import * as Ajax from "./ajax.js";
import Form from "./class/Form.js";

const formControl = new Form();

/**
 * Create a new element with class name and text
 *
 * @param {string} el
 * @param {array} classes
 * @param {string} text
 * @return {object}
 */
function addElement(el, classes, text) {
  const element = document.createElement(el);
  for (const className of classes) {
    element.classList.add(className);
  }
  element.setAttribute("aria-hidden", false);
  element.innerText = text;

  return element;
}

/**
 * Reset form
 *
 * @param {string} id
 */
function resetForm(id) {
  document.querySelector(id).reset();
}

/**
 * Reset all elements errors
 *
 * @param {string} element
 * @param {string} keyEvent
 */
function resetErrors(element, keyEvent) {
  document.querySelectorAll(element).forEach((el) => {
    el.addEventListener(keyEvent, formControl.error.removeErrors);
  });
}

/**
 * Check form elements, if validate => call ajax
 *
 * @param {object} form
 */
function checkForm(form) {
  form.addEventListener("submit", function (e) {
    // stop refresh
    e.preventDefault();
    // get form id
    const formId = form.id;
    // define page & task with dataset
    const page = form.dataset.page;
    const task = form.dataset.task;
    // get all input, textarea & select to push it in array allTags
    const inputs = document.querySelectorAll(`#${formId} input`);
    const textareas = document.querySelectorAll(`#${formId} textarea`);
    const selects = document.querySelectorAll(`#${formId} select`);
    const allTags = [];
    allTags.push.apply(allTags, inputs);
    allTags.push.apply(allTags, textareas);
    allTags.push.apply(allTags, selects);
    // insert all tags values in formControl
    formControl.fields = allTags;
    // remove old errors
    formControl.error.errors = [];
    // checks for errors in the submit forms
    if (!formControl.validate()) {
      formControl.error.displayErrors();
    } else {
      // send form datas in Ajax.js
      const formData = new FormData(form);
      Ajax.form(formData, page, task);
    }
  });
}

export { addElement, resetForm, resetErrors, checkForm };
