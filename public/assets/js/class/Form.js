import FormError from "./FormError.js";

class Form {
  constructor() {
    this._fields = [];
    this._error = new FormError();
  }

  get fields() {
    return this._fields;
  }

  set fields(fields) {
    this._fields = fields;
  }

  get error() {
    return this._error;
  }

  /**
   * Validate form fields
   *
   * @return {boolean}
   */
  validate() {
    for (let field of this.fields) {
      // if empty fields => add error
      if (!field.value && !field.dataset.empty) {
        this._error.addError({
          name: field.id,
          message: "Le champ est vide",
        });
      } else {
        // foreach type of field, check if there are errors
        let regex;
        switch (field.dataset.type) {
          case "text":
            regex = /^[a-zA-Z\à\â\ä\é\è\ê\ë\î\ï\ô\ö\ù\û\ü\s\-]{2,40}$/;
            if (!regex.test(field.value)) {
              this._error.addError({
                name: field.id,
                message:
                  "Le champ doit contenir entre 2 et 40 caractères, et seulement des lettres",
              });
            }
            break;

          case "visa":
            regex = /^(\d{4}){4}$/;
            if (!regex.test(field.value)) {
              this._error.addError({
                name: field.id,
                message: "Le numéro de carte n'est pas valide",
              });
            }
            break;

          case "security":
            regex = /^\d{3}$/;
            if (!regex.test(field.value)) {
              this._error.addError({
                name: field.id,
                message: "Le code de sécurité n'est pas valide",
              });
            }
            break;

          case "phone":
            regex = /^((\+\d+(\s|-)?)|0)\d(\s|-|\.)?(\d{2}(\s|-|\.)?){4}$/;
            if (!regex.test(field.value)) {
              this._error.addError({
                name: field.id,
                message: "Le numéro de téléphone n'est pas valide",
              });
            }
            break;

          case "zipcode":
            regex = /^(\d){5}$/;
            if (!regex.test(field.value)) {
              this._error.addError({
                name: field.id,
                message: "Le code postal n'est pas valide",
              });
            }
            break;

          case "title":
            if (field.value.length < 2 || field.value.length > 200) {
              this._error.addError({
                name: field.id,
                message:
                  "Le champ doit contenir entre 2 et 200 caractères, et seulement des lettres",
              });
            }
            break;

          case "description":
            if (field.value.length < 5 || field.value.length > 1000) {
              this._error.addError({
                name: field.id,
                message:
                  "Le champ doit contenir entre 5 et 1000 caractères, et seulement des lettres",
              });
            }
            break;

          case "email":
            regex =
              /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            if (!regex.test(field.value)) {
              this._error.addError({
                name: field.id,
                message: "Le champ ne contient pas un email valide.",
              });
            }
            break;

          case "password":
            regex = /^\S*(?=\S{5,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/;
            if (!regex.test(field.value)) {
              this._error.addError({
                name: field.id,
                message:
                  "Le champ password doit contenir au moins 5 caractères, au moins 1 majuscule et au moins 1 chiffre.",
              });
            }
            break;

          case "date":
            // select the year select
            const selectYear = document.querySelector("#year");
            // set today's date
            const now = new Date();
            // set today year
            const nowYear = now.getFullYear();
            // set today month
            const nowMonth = now.getMonth() + 1;
            // set selected year
            const year = parseInt(selectYear.value);
            // set selected month
            const month = parseInt(field.value);
            // if the selected date is before today's date
            if (year === nowYear) {
              if (month < nowMonth) {
                // add error
                this._error.addError({
                  name: field.id,
                  message:
                    "La date sélectectionée est antérieure à la date actuelle",
                });
              }
            }
            break;
        }
      }
    }
    // if there is an error => return boolean false else return boolean true
    if (this._error.errors.length != 0) {
      return false;
    } else {
      return true;
    }
  }
}

export default Form;
