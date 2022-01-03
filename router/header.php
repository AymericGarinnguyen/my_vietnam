<?php

// find all forum categories in the database
$categoriesM = new \Models\CategoryForumModel();
$select =
[
    'categories_id', 
    'category',
    'description',
    'icon'
];
$categories = $categoriesM->findAll($select);

// manage the cart in the header
$nbProductsInCart = null;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $add = null;
    //open a span
    $nbProductsInCart = '<span class="pop aria-hidden="false"">';
    // for each products in the session['cart]
    foreach ($_SESSION['cart'] as $key => $value) {
        // add the quantity in the variable $add
        $add += $value['quantity'];
    }
    // add the variable to the span and close it
    $nbProductsInCart .= $add . '</span>';
}

// check user's status
$isUser = null;
$isAdmin = null;
if (\Libraries\Session::isConnected()) {
    $isUser = true;
}
if (\Libraries\Session::isAdmin()) {
    $isAdmin = true;
}
require '../views/header.phtml';