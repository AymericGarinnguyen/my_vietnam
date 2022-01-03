import * as Generic from "./../generic.js";

class FormError {
  constructor() {
    this._errors = [];
  }

  /**
   * add error this._errors
   *
   * @param {object} error
   */
  addError(error) {
    this._errors.push(error);
  }

  get errors() {
    return this._errors;
  }

  set errors(error) {
    this._errors = error;
  }

  /**
   * Display errors
   */
  displayErrors() {
    for (const error of this._errors) {
      // create a new span with class "form-error" & error message
      const addSpan = Generic.addElement(
        "span",
        ["form-error"],
        error.message
      );
      // select input
      let input = document.querySelector(`#${error.name}`);
      // add class "error" to this input
      input.classList.add("error");
      if (error.name === "month") {
        // select input "month" parent node
        input = input.parentNode;
        // add class "error" to select #year
        document.querySelector("#year").classList.add("error");
      }
      // if span ".form-error" doesn't exists
      if (!input.nextElementSibling.classList.contains("form-error")) {
        // insert span after input
        input.parentNode.insertBefore(addSpan, input.nextSibling);
      }
    }
  }

  /**
   * Remove this input error
   */
  removeErrors() {
    // if input is a select
    if (this.tagName === "SELECT") {
      if (this.parentNode.nextElementSibling.classList.contains("form-error")) {
        // remove the span after the select
        this.parentNode.nextElementSibling.remove();
      }
      // remove class ".error" to both select #month & #year
      document.querySelector("#month").classList.remove("error");
      document.querySelector("#year").classList.remove("error");
    } else {
      if (this.nextElementSibling !== null) {
        if (this.nextElementSibling.classList.contains("form-error")) {
          // remove span after this input
          this.nextElementSibling.remove();
          // remove class ".error" to this input
          this.classList.remove("error");
        }
      }
    }
  }
}

export default FormError;
