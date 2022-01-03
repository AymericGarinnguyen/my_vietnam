<?php

namespace Controllers;

class CartController extends Controller
{
    protected string $title = 'mon panier';
    protected string $pageContent = 'shop/cart';
    protected array $variables = [];
    private bool $is_empty;
    private $totalPrice;
    private $totalQuantity;

    public function __construct()
    {
        parent::__construct();
        // check if user is connected
        if (!isset($_SESSION['login'])) {
            header('Location: index.php?page=home');
            exit;
        }
        // if there isn't session "cart" => display cart page
        if (!isset($_SESSION['cart']) && $_GET['task'] !== 'display') {
            header('Location: index.php?page=cart&task=display');
            exit;
        }
        $this->productsInCart();
        $this->variables = ['is_empty' => $this->is_empty, 'totalPrice' => $this->totalPrice, 'totalQuantity' => $this->totalQuantity];
    }

    /**
     * Define if variable $is_empty is true or false, if session['cart'] exist define variable $totalPrice
     * 
     * @return void
     */
    public function productsInCart(): void
    {
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            $this->is_empty = false;
            foreach ($_SESSION['cart'] as $item) {
                $priceByProduct = $item['price'] * $item['quantity'];
                $this->totalPrice += $priceByProduct;
                $this->totalQuantity += $item['quantity'];
            }
        } else {
            $this->is_empty = true;
        }
    }

    /**
     * modify quantity of products in cart
     * 
     * @return void
     */
    public function update():void
    {
        $productId = $_GET['id'];
        if (isset($_POST) && !empty($_POST)) {
            $quantity = intval($_POST['quantity']);
            // if quantity === 0 => unset session "cart"
            if ($quantity === 0) {
                unset($_SESSION['cart'][$productId]);
            } else {
                // else => modify session "cart" quantity
                $_SESSION['cart'][$productId]['quantity'] = $quantity;
                
                if($quantity >= $_SESSION['cart'][$productId]['stock']) {
                    $_SESSION['cart'][$productId]['inStock'] = false;
                } else {
                    $_SESSION['cart'][$productId]['inStock'] = true;
                }
            }
            if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
                echo $quantity;
            } else {
                //redirection
                header('Location: index.php?page=cart');
            }
        }
    }

    /**
     * delete the product in cart
     * 
     * @return void
     */
    public function delete(): void
    {
        $productId = $_GET['id'];
        unset($_SESSION['cart'][$productId]);
        if (isset($_GET['ajax']) && $_GET['ajax'] === 'true'){
            echo 'success';
        }else {
            //redirection
            header('Location: index.php?page=cart');
        }
        
    }

    /**
     * show the summary page, if user's informations aren't complete => show the fill page
     * 
     * @return void
     */
    public function summary():void
    {
        if ($_SESSION['login']['is_filled'] !== '1') {
            header('Location: index.php?page=cart&task=fill');
            exit;
        }
        \Libraries\Renderer::render('Commmande', 'shop/summary');
    }

    /**
     * Display the fill page, define which session to use
     * 
     * @return void
     */
    public function fill(): void
    {
        $session = null;
        if(isset($_SESSION['user'])) {
            $session = $_SESSION['user'];
        }else {
            $session = $_SESSION['login'];
        }
        \Libraries\Renderer::render('Formulaire', 'shop/fill', compact('session'));
    }

    /**
     * Display the payment page
     * 
     * @return void
     */
    public function payment(): void
    {
        \Libraries\Renderer::render('Paiement', 'shop/payment');
    }

    public function checkPayment()
    {
        $isAjax = isset($_GET['ajax']) && $_GET['ajax'] === 'true';
        try {
            if (isset($_POST) && !empty($_POST)) {
                // save the user's auth
                \Libraries\Session::init('payment', [
                    "visa" => $_POST['visa'],
                    "name" => $_POST['name'],
                    "month" => $_POST['month'],
                    "year" => $_POST['year'],
                    "security" => $_POST['security']
                ]);

                // check all inputs sent, empty, length, characters
                foreach ($_POST as $input => $post) {
                    // we convert special characters to HTML entities
                    $_POST[$input] = \Libraries\CheckForm::checkSecurity($post);

                    if ($input === "visa") {
                        \Libraries\CheckForm::emptyInput('visa', $post, 'Numéro de carte');
                        \Libraries\CheckForm::visaCheck($post, 'visa', 'Numéro de carte');
                    } elseif ($input === "name") {
                        \Libraries\CheckForm::emptyInput('name', $post, 'Nom');
                        \Libraries\CheckForm::fieldCheck($post, '[a-zA-Z\à\â\ä\é\è\ê\ë\î\ï\ô\ö\ù\û\ü\s]', 2, 40, 'name', 'Nom');
                    } elseif ($input === "security") {
                        \Libraries\CheckForm::emptyInput('security', $post, 'Code de sécurité');
                        \Libraries\CheckForm::fieldCheck($post, '[0-9]', 3 ,3,'security', 'Code de sécurité');
                    } elseif ($input === "month") {
                        \Libraries\CheckForm::dateCheck($post, $_POST['year']);
                    }
                }
                // destroy the session auth & redirection
                \Libraries\Session::destroyByValue('payment');
                $productM = new ShopController();
                $productM->update();
                $orderM = new \Models\OrderModel();
                $orderM->insertOrder(intval($_SESSION['login']['user_id']));
                foreach($_SESSION['cart'] as $product) {
                    $orderM->insertDetails($product["quantity"], intval($product["id"]));
                }
                \Libraries\Session::destroyByValue('cart');
                if ($isAjax) {
                    echo 'success';
                } else {
                    header('Location: index.php?page=cart&task=display&success=true');
                    exit;
                }
            } else {
                throw new \ErrorException("Vous n'avez pas accès à cette page");
            }
        } catch (\ErrorException $e) {
            ErrorController::error404($e);
        } catch (\DomainException $e) {
            if ($isAjax) {
                $data = unserialize($e->getMessage());
                header('Content-Type: application/json');
                echo json_encode($data);
            } else {
                ErrorController::errorForm('payment', unserialize($e->getMessage()));
            }
        }
    }
}