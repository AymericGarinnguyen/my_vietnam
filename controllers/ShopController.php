<?php

namespace Controllers;

class ShopController extends Controller
{
    protected $modelName = \Models\ShopModel::class;
    protected string $title = 'le shop';
    protected string $pageContent = 'shop/shop';
    protected array $variables = [];

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['login'])) {
            header('Location: index.php?page=home');
            exit;
        }
        $this->variables = ['products' => $this->findAllProducts()];
    }

    /**
     * find all products in the database
     * 
     * @return array
     */
    public function findAllProducts(): array
    {
        $select =
            [
                'product_id',
                'name',
                'description',
                'picture',
                'price',
                'stock',
                'category'
            ];
        return $this->model->findAll($select);
    }

    /**
     * find & add a product in cart
     * 
     * @return void
     */
    public function add(): void
    {
        $select =
            [
                'product_id',
                'name',
                'description',
                'picture',
                'price',
                'stock',
                'category'
            ];
        // find product in the database
        $product = $this->model->findByItem($select, 'product_id', $_GET['id']);
        $quantity = 1;
        $stock =intval($product['stock']);
        $inStock = true;
        // if product_id already exist in session "cart" => add +1 to quantity
        if (isset($_SESSION['cart'][$product['product_id']])) {
            $quantity = $_SESSION['cart'][$product['product_id']]['quantity'] + 1;
        }
        
        // if quantity === stock in database => $instock become false
        if ($quantity === $stock) {
            $inStock = false;
        }
        // add product in session['cart]
        $_SESSION['cart'][$product['product_id']] =
            [
                'id' => $product['product_id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'picture' => $product['picture'],
                'stock' => $stock,
                'quantity' => $quantity,
                'inStock' => $inStock
            ];

        if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
            header('Content-Type: application/json');
            echo json_encode($product);
        } else {
            // redirection
            header('Location: index.php?page=shop');
            exit;
        }
    }

    /**
     * if payment is success => update stock in database 
     */
    public function update()
    {
        foreach ($_SESSION['cart'] as $product) {
            $structure = ['stock = :stock'];
            $params =
                [
                    ':stock'       => $product['stock'] - $product['quantity'],
                    ':product_id'  => $product['id']
                ];
            $this->model->update($structure, 'product_id', $params);
        }
    }
}