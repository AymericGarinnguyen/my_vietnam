import * as Modal from "./modal.js";
import * as Generic from "./generic.js";
import FormError from "./class/FormError.js";

const formError = new FormError();
// define initial path
const PATH = "index.php?page=";

/* *********************************************************************************************
                                        FORMS AJAX
  ********************************************************************************************* */
/**
 * Query the controller of each form
 *
 * @param {object} form
 * @param {string} page
 * @param {string} task
 */
function form(form, page, task) {
  // fetch in the controller give by "page"
  fetch(`${PATH}${page}&task=${task}&ajax=true`, {
    method: "post",
    body: form,
  })
    .then((response) => {
      // check if response is text or json
      if (response.headers.get("content-type") === "application/json") {
        return response.json();
      } else {
        return response.text();
      }
    })
    .then((data) => {
      if (data === "success") {
        // if the modal authentification form or the modal registration form is validated
        if (page === "user" && task === "manageUser") {
          // close modal & reload page
          Modal.closeModal();
          document.location.reload();
        }
        // if the contact form is validated
        else if (page === "contact" && task === "send") {
          // reload page with get success
          document.location.replace("index.php?page=contact&success=true");
        }
        // if the newsletter form is validated
        else if (page === "newsletter" && task === "insert") {
          // reset form
          Generic.resetForm("#form-newsletter");
          // create a span with class "success" & message
          const addSpan = Generic.addElement(
            "span",
            ["success", "box-container"],
            "Vous êtes abonnés à la newsletter"
          );
          // select input #newsletter
          const input = document.querySelector("#newsletter");
          // insert span after input
          input.parentNode.insertBefore(addSpan, input.nextSibling);
          // remove span after 3s
          setTimeout(function () {
            addSpan.setAttribute("aria-hidden", true);
            addSpan.addEventListener("animationend", function () {
              this.parentNode.removeChild(this);
            });
          }, 3000);
        }
        // if the forum form is validated
        else if (page === "forum" && task === "insert") {
          // reset form
          Generic.resetForm("#insert-message");
          // create a div with class "success" & message
          const addDiv = Generic.addElement(
            "span",
            ["success", "box-container"],
            "Votre message a bien été envoyé !"
          );
          // select form #insert-message
          const form = document.querySelector("#insert-message");
          // insert div before form
          form.parentNode.insertBefore(addDiv, form.previousSibling);
          // remove div after 3s
          setTimeout(function () {
            addDiv.setAttribute("aria-hidden", true);
            addDiv.addEventListener("animationend", function () {
              this.parentNode.removeChild(this);
            });
          }, 3000);
        }
        // if the update user form is validated
        else if (page === "user" && task === "update") {
          // load summary page
          document.location.replace("index.php?page=cart&task=summary");
        }
        // if the payment form is validated
        else if (page === "cart" && task === "checkPayment") {
          // remove localStorage "products"
          localStorage.removeItem("products");
          if (!localStorage.getItem("products")) {
            // load cart page with get "success"
            document.location.replace(
              "index.php?page=cart&task=display&success=true"
            );
          }
        }
      } else {
        console.log(data);
        // display errors
        formError.errors = [];
        formError.addError(data);
        formError.displayErrors();
      }
    });
}

/* *********************************************************************************************
                                        SHOP AJAX
  ********************************************************************************************* */
/**
 * Query the controller to add a product
 *
 * @param {string} id
 */
function addProduct(id) {
  // fetch controller shop
  fetch(`${PATH}shop&task=add&id=${id}&ajax=true`)
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      // if localStorage "products" doesnt exist => create it
      if (!localStorage.getItem("products")) {
        // add quantity to data
        data.quantity = 1;
        // create an array
        const listProducts = [];
        // push data in array
        listProducts.push(data);
        // set localStorage with array
        localStorage.setItem("products", JSON.stringify(listProducts));
        // check if quantity === stock
        checkStock(data);
      } else {
        // parse localStorage in constante
        const datas = JSON.parse(localStorage.getItem("products"));
        console.log(datas);
        // create variable to check data exists in localStorage
        let checkEntry = false;
        datas.forEach((entry) => {
          // if this entry matches the id of data
          if (data.product_id === entry.product_id) {
            // add +1 to quantity entry
            entry.quantity++;
            // data is already in localstorage
            checkEntry = true;
            // check if quantity === stock
            checkStock(entry);
          }
        });
        // if data is not in localstorage
        if (!checkEntry) {
          // add quantity to data
          data.quantity = 1;
          // pussh data in array "datas"
          datas.push(data);
          // check if quantity === stock
          checkStock(data);
        }
        // save the localstorage
        localStorage.setItem("products", JSON.stringify(datas));
      }
      // parse localStorage "products" in constante
      const products = JSON.parse(localStorage.getItem("products"));
      let cartQuantity = 0;
      products.forEach((product) => {
        cartQuantity += product.quantity;
      });
      if (!document.querySelector(".pop")) {
        // create a span with class "pop" & cart quantity
        const addSpan = Generic.addElement("span", ["pop"], cartQuantity);
        // select .cart
        const cart = document.querySelector(".cart a");
        // span after .cart
        cart.parentNode.insertBefore(addSpan, cart.previousSibling);
      } else {
        document.querySelector(".pop").innerHTML = cartQuantity;
      }
    });
}

/**
 * check if quantity === stock => replace button add by sold out
 *
 * @param {object} data
 */
function checkStock(data) {
  if (data.quantity === parseInt(data.stock)) {
    // target the button
    const button = document.querySelector(
      `.add-product[data-id="${data.product_id}"]`
    );
    // create a paragraph
    const addParagraph = Generic.addElement(
      "p",
      ["sold-out"],
      "Rupture de stock"
    );
    // target the previous element
    const previousElement = button.previousElementSibling;
    // insert paragraph
    button.previousElementSibling.parentNode.insertBefore(
      addParagraph,
      previousElement.nextElementSibling
    );
    // remove button .add-product
    button.remove();
  }
}

/**
 * Delete a product selected by id
 *
 * @param {string} id
 */
function deleteProduct(id) {
  // fetch controller cart
  fetch(`${PATH}cart&task=delete&id=${id}&ajax=true`)
    .then((response) => {
      return response.text();
    })
    .then((data) => {
      if (data === "success") {
        // parse localStorage "products" in constante
        const products = JSON.parse(localStorage.getItem("products"));
        //
        const index = selectIndex(id, products);
        let totalQuantity = 0;
        let totalPrice = 0;
        // delete this product by index from array products
        products.splice(index, 1);
        // save array in localStorage "products"
        localStorage.setItem("products", JSON.stringify(products));
        // check new total quantity & new total price
        const totals = calculateTotal(products);
        // if cart have products
        if (totalQuantity > 0) {
          // remove this product
          document.querySelector(`#card${id}`).remove();
          // refresh cart
          refreshCart(totals.totalQuantity, totals.totalPrice);
        } else {
          // reload page
          document.location.reload();
        }
      }
    });
}

/**
 * Update quantity of product
 *
 * @param {object} form
 * @param {string} id
 */
function updateProduct(form, id) {
  // fetch controller cart
  fetch(`${PATH}cart&task=update&id=${id}&ajax=true`, {
    method: "post",
    body: form,
  })
    .then((response) => {
      return response.json();
    })
    .then((data) => {
      // parse localStorage "products" in constante
      const products = JSON.parse(localStorage.getItem("products"));
      const index = selectIndex(id, products);
      products[index].quantity = parseInt(data);
      localStorage.setItem("products", JSON.stringify(products));
      // save object new total quantity & new total price in constante
      const totals = calculateTotal(products);
      // refresh cart
      refreshCart(totals.totalQuantity, totals.totalPrice);
    });
}

/**
 * Select index in products array
 *
 * @param {string} id
 * @param {object} products
 * @return {number}
 */
function selectIndex(id, products) {
  let index;
  // save this product in variable index
  products.forEach((product) => {
    if (product.product_id === id) {
      index = products.indexOf(product);
    }
  });
  return index;
}

/**
 * Calculates the total price and the total quantity of products in the cart
 *
 * @param {object} products
 * @returns {object}
 */
function calculateTotal(products) {
  let quantity = 0;
  let price = 0;
  // check new total quantity & new total price
  products.forEach((product) => {
    quantity += product.quantity;
    price += parseInt(product.price) * product.quantity;
  });
  return { totalQuantity: quantity, totalPrice: price };
}

/**
 * Refresh cart informations: total price & total quantity
 *
 * @param {number} totalQuantity
 * @param {number} totalPrice
 */
function refreshCart(totalQuantity, totalPrice) {
  // select .pop, #total-quantity & #total-price
  const popValue = document.querySelector(".pop");
  const quantityValue = document.querySelector("#total-quantity");
  const priceValue = document.querySelector("#total-price");
  // write new values
  popValue.innerHTML = totalQuantity.toString();
  quantityValue.innerHTML = totalQuantity.toString();
  priceValue.innerHTML = totalPrice.toString() + " €";
}

export { addProduct, deleteProduct, updateProduct, form };
