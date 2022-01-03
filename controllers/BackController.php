<?php

namespace Controllers;

class BackController
{
    private string $title = 'Back';
    private object $userModel;
    private object $forumModel;
    private object $categoryModel;
    private object $shopModel;

    public function __construct()
    {
        // check if user is connected
        if (!isset($_SESSION['login'])) {
            header('Location: index.php?page=home');
            exit;
        }
        // select model
        $user = ['backUser', 'updateUser', 'deleteUser'];
        $forum = ['backForum', 'checkMessage', 'manageMessage'];
        $shop = ['backShop', 'newProduct', 'checkProduct', 'manageProduct'];
        if (in_array($_GET['task'], $user)) {
            $this->userModel = new \Models\UserModel();
        } elseif (in_array($_GET['task'], $forum)) {
            $this->forumModel = new \Models\ForumModel();
            $this->categoryModel = new \Models\CategoryForumModel();
        } elseif (in_array($_GET['task'], $shop)) {
            $this->shopModel = new \Models\ShopModel();
        } elseif ('toto') {
            $this->orderModel = new \Models\OrderModel();
        }

        // destroy temporary sesions
        \Libraries\Session::destroyByValue("chatId");
    }

    /* **************************************************************
                            BACK USER
    ************************************************************** */

    /**
     * Show all users in the database
     * 
     * @return void
     */
    public function backUser(): void
    {
        try {
            $select =
                [
                    'user_id',
                    'firstname',
                    'lastname',
                    'email',
                    'is_admin',
                    'created_at'
                ];
            // find all users in database    
            $users = $this->userModel->findAll($select, 'user_id');
            \Libraries\Renderer::render($this->title, 'back/backUsers', compact('users'));
        } catch (\PDOException $e) {
            ErrorController::error404($e);
        }
    }

    /**
     * Update user if is admin or not
     * 
     * @return void
     */
    public function updateUser(): void
    {
        try {
            if (isset($_POST) && !empty($_POST)) {
                foreach ($_POST['is-admin'] as $id => $value) {
                    // update this user admin or user
                    $this->userModel->update(
                        ['is_admin = :is_admin'],
                        'user_id',
                        [
                            ':is_admin' => $value,
                            ':user_id'  => $id
                        ]
                    );
                }
                // redirection
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            } else {
                throw new \Exception("Vous n'avez pas accès à cette page");
            }
        } catch (\Exception $e) {
            ErrorController::error404($e);
        }
    }

    /**
     * Delete user in the database
     * 
     * @return void
     */
    public function deleteUser(): void
    {
        try {
            // if there is $_GET['id'] and it's not empty
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                // looking for this $_GET['id'] exists in database
                $user = $this->userModel->findByItem(['user_id'], 'user_id', $_GET['id']);
                // if $user exists and if $_GET['id'] is not the user connected
                if ($user && $_SESSION['login']['id'] !== $_GET['id']) {
                    // delete the user and redirection
                    $this->userModel->delete($_GET['id'], 'user_id');
                    header('Location: index.php?page=back&task=backUser');
                    exit;
                } else {
                    throw new \Exception('Impossible de supprimer cet utilisateur');
                }
            } else {
                throw new \Exception('Page introuvable');
            }
        } catch (\Exception $e) {
            ErrorController::error404($e);
        }
    }

    /* **************************************************************
                            BACK FORUM
    ************************************************************** */

    /**
     * Show all messages to check in the database
     * 
     * @return void
     */
    public function backForum(): void
    {
        try {
            $select =
                [
                    'forum_id',
                    'title',
                    'forum.created_at',
                    'is_checked',
                    'email'
                ];
            // find all comments checked in database
            $messages = $this->forumModel->findAllByItem($select, 'is_checked', 0, 'created_at DESC');
            \Libraries\Renderer::render($this->title, 'back/backForum', compact('messages'));
        } catch (\PDOException $e) {
            ErrorController::error404($e);
        }
    }

    /**
     * Show message to check, find by $_GET['id]
     * 
     * @return void
     */
    public function checkMessage(): void
    {
        try {
            // if there is $_GET['id'] and it's not empty
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                // looking for this $_GET['id'] exists in database
                $message = $this->forumModel->findByItem(['forum_id'], 'forum_id', $_GET['id']);
                // if $comment exists
                if ($message) {
                    $selectForum =
                        [
                            'title',
                            'message',
                            'category'
                        ];
                    // save the id in session['back]
                    \Libraries\Session::init('back', ['id' => $_GET['id']]);
                    // find informations for this comment
                    $forum = null;
                    if(isset($_SESSION['back-message'])) {
                        $forum = $_SESSION['back-message'];
                    } else {
                        $forum = $this->forumModel->findByItem($selectForum, 'forum_id', $_GET['id']);
                    }
                    
                    // find all forum's categories
                    $categoriesMessage = $this->categoryModel->findAll(['categories_id, category']);
                    \Libraries\Renderer::render('Valider le message', 'back/checkMessage', compact('forum', 'categoriesMessage'));
                } else {
                    throw new \Exception('Message introuvable');
                }
            } else {
                throw new \Exception('Page introuvable');
            }
        } catch (\Exception $e) {
            ErrorController::error404($e);
        }
    }

    /**
     * Manage the current comment
     * 
     * @return void
     */
    public function manageMessage(): void
    {
        try {
            if (isset($_POST) && !empty($_POST)) {
                // save session id
                $id = $_SESSION['back']['id'];
                // check which post it receive
                if (isset($_POST['update']) || isset($_POST['validate'])) {
                    // save user's message in session
                    \Libraries\Session::init('back-message', [
                        "title" => $_POST['title'],
                        "message"  => $_POST['message'],
                        "category" => $_POST['category']
                    ]);
                    // check all inputs
                    foreach ($_POST as $input => $post) {

                        $_POST[$input] = \Libraries\CheckForm::checkSecurity($post);

                        if ($input === "title") {
                            \Libraries\CheckForm::emptyInput('title', $post, 'Titre');
                            \Libraries\CheckForm::lengthCheck($post, 2, 100, 'title', 'Titre');
                        }
                        if ($input === "message") {
                            \Libraries\CheckForm::emptyInput('message', $post, 'Message');
                            \Libraries\CheckForm::lengthCheck($post, 10, 1000, 'message', 'Message');
                        }
                    };
                    $structure =
                        [
                            'title = :title',
                            'message = :message',
                            'categories_id = :categories_id'
                        ];
                    if (isset($_POST['validate'])) {
                        array_push($structure, 'is_checked = 1');
                    }
                    $params = [
                        ':title' => $_POST['title'],
                        ':message' => $_POST['message'],
                        ':categories_id' => $_POST['category'],
                        'forum_id' => $id
                    ];
                    // modify all informations of this comment
                    $this->forumModel->update($structure, 'forum_id', $params);
                } elseif (isset($_POST['delete'])) {
                    // delete this comment
                    $this->forumModel->delete($id, 'forum_id');
                } elseif (isset($_POST['manage'])) {
                    foreach ($_POST['check'] as $key => $value) {
                        // check which value it receive
                        if ($value === "val") {
                            // modify checked status
                            $this->forumModel->update(['is_checked = 1'], "forum_id", [':forum_id' => $key]);
                        } elseif ($value === "del") {
                            // delete comments
                            $this->forumModel->delete($key, 'forum_id');
                        }
                    }
                }
                \Libraries\Session::destroyByValue('back-message');
                // redirection
                header('Location: index.php?page=back&task=backForum');
                exit;
            } else {
                throw new \ErrorException("Vous n'avez pas accès à cette page");
            }
        } catch (\ErrorException $e) {
            ErrorController::error404($e);
        } catch (\DomainException $e) {
            ErrorController::errorForm('check', unserialize($e->getMessage()));
        }
    }

    /* **************************************************************
                            BACK SHOP
    ************************************************************** */

    /**
     * Show all products in the database
     * 
     * @return void
     */
    public function backShop(): void
    {
        try {
            $select =
                [
                    'product_id',
                    'name',
                    'stock',
                    'category'
                ];
            //find all products in database
            $products = $this->shopModel->findAll($select);
            \Libraries\Renderer::render($this->title, 'back/backShop', compact('products'));
        } catch (\PDOException $e) {
            ErrorController::error404($e);
        }
    }

    /**
     * Show the page to create a new product
     * 
     * @return void
     */
    public function newProduct(): void
    {
        \Libraries\Renderer::render($this->title, 'back/addProduct');
    }

    /**
     * Show product to check, find by $_GET['id]
     * 
     * @return void
     */
    public function checkProduct(): void
    {
        $session = null;
        try {
            // if there is $_GET['id'] and it's not empty
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                // looking for this $_GET['id'] exists in database
                $product = $this->shopModel->findByItem(['product_id'], 'product_id', $_GET['id']);
                // if $comment exists
                if ($product) {
                    $select =
                        [
                            'name',
                            'description',
                            'picture',
                            'price',
                            'stock',
                            'category'
                        ];
                    // init session['back']
                    \Libraries\Session::init('back', ['id' => $_GET['id']]);
                    // find product by id
                    $shop = $this->shopModel->findByItem($select, 'product_id', $_GET['id']);
                    // define which session to pass in the renderer
                    if (isset($_SESSION['back-product'])) {
                        $session = $_SESSION['back-product'];
                    } else {
                        $session = $shop;
                    }
                    \Libraries\Renderer::render('Modifier le produit', 'back/checkProduct', compact('session'));
                } else {
                    throw new \Exception('Produit introuvable');
                }
            } else {
                throw new \Exception('Page introuvable');
            }
        } catch (\Exception $e) {
            ErrorController::error404($e);
        }
    }

    /**
     * Manage the current product
     * 
     * @return void
     */
    public function manageProduct(): void
    {
        try {
            if (isset($_POST) && !empty($_POST)) {

                // check which post it receive
                if (isset($_POST['delete'])) {
                    // delete this product
                    $this->shopModel->delete($_POST['id'], 'product_id');
                } else {
                    $_SESSION['back-product'] = [];
                    foreach ($_POST as $input => $post) {
                        if ($input === "current-picture") {
                            $_SESSION['back-product'] += ["picture" => $post];
                        } else {
                            $_SESSION['back-product'] += [$input => $post];
                        }
                    }
                    $picture = null;
                    // check all inputs sent, empty, length, characters
                    foreach ($_POST as $input => $post) {
                        // we convert special characters to HTML entities
                        $_POST[$input] = \Libraries\CheckForm::checkSecurity($post);
                        if ($input === "name") {
                            \Libraries\CheckForm::emptyInput('name', $post, 'Nom');
                            \Libraries\CheckForm::fieldCheck($post, '[0-9A-Za-zÀ-ÖØ-öø-ÿ\s]', 2, 40, 'name', 'Nom');
                        } elseif ($input === "description") {
                            \Libraries\CheckForm::emptyInput('description', $post, 'Description');
                            \Libraries\CheckForm::lengthCheck($post, 10, 1000, 'description', 'Description');
                        } elseif ($input === "price") {
                            \Libraries\CheckForm::emptyInput('price', $post, 'Prix');
                            \Libraries\CheckForm::fieldCheck($post, '[0-9]', 1, 5, 'price', 'Prix');
                        } elseif ($input === "stock") {
                            \Libraries\CheckForm::emptyInput('stock', $post, 'Stock');
                            \Libraries\CheckForm::fieldCheck($post, '[0-9]', 1, 5, 'stock', 'Stock');
                        }
                    }

                    if (isset($_POST['update'])) {
                        if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
                            $picture = \Libraries\CheckForm::pictureCheck($_FILES['picture']);
                        } else {
                            $picture = $_POST['current-picture'];
                        }
                        $structure =
                            [
                                'name = :name',
                                'description = :description',
                                'picture = :picture',
                                'price = :price',
                                'stock = :stock',
                                'category = :category'
                            ];
                        $params = [
                            ':name' => $_POST['name'],
                            ':description' => $_POST['description'],
                            ':picture' => $picture,
                            ':price' => $_POST['price'],
                            ':stock' => $_POST['stock'],
                            ':category' => $_POST['category'],
                            'product_id' => $_POST['id']
                        ];
                        // update this product
                        $this->shopModel->update($structure, 'product_id', $params);
                    } elseif (isset($_POST['insert'])) {
                        // image is choosen
                        if ($_FILES['picture']['error'] === UPLOAD_ERR_OK) {
                            $picture = \Libraries\CheckForm::pictureCheck($_FILES['picture']);
                        } else {
                            throw new \DomainException("Vous devez choisir une image");
                        }
                        $select = [
                            'name',
                            'description',
                            'picture',
                            'price',
                            'stock',
                            'category'
                        ];
                        $values = [
                            ':name',
                            ':description',
                            ':picture',
                            ':price',
                            ':stock',
                            ':category'
                        ];
                        $params = [
                            ':name' => $_POST['name'],
                            ':description' => $_POST['description'],
                            ':picture' => $picture,
                            ':price' => $_POST['price'],
                            ':stock' => $_POST['stock'],
                            ':category' => $_POST['category']
                        ];
                        // insert in the database
                        $this->shopModel->insert($select, $values, $params);
                    }
                }
                // destroy session['error] and redirection
                header('Location: index.php?page=back&task=backShop');
                exit;
            } else {
                throw new \ErrorException("Vous n'avez pas accès à cette page");
            }
        } catch (\ErrorException $e) {
            ErrorController::error404($e);
        } catch (\DomainException $e) {
            ErrorController::errorForm('product', unserialize($e->getMessage()));
        }
    }

    /* **************************************************************
                            BACK ORDER
    ************************************************************** */
    /**
     * Show all orders in the database
     * 
     * @return void
     */
    public function backOrder(): void
    {
        try {
            $select =
                [
                    'orders.order_id',
                    'orders.created_at',
                    'users.lastname'
                ];
            //find all products in database
            $orders = $this->orderModel->findAll($select, "orders.created_at");
            \Libraries\Renderer::render($this->title, 'back/backOrder', compact('orders'));
        } catch (\PDOException $e) {
            ErrorController::error404($e);
        }
    }

    /**
     * Show order to check, find by $_GET['id]
     * 
     * @return void
     */
    public function checkOrder(): void
    {
        try {
            // if there is $_GET['id'] and it's not empty
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                // looking for this $_GET['id'] exists in database
                $order = $this->orderModel->findByItem(['order_id'], 'order_id', $_GET['id']);
                // if $comment exists
                if ($order) {
                    // find product by id
                    $order = $this->orderModel->findOrder($_GET['id']);
                    \Libraries\Renderer::render('Valider la commande', 'back/checkOrder', compact('order'));
                } else {
                    throw new \Exception('Commande introuvable');
                }
            } else {
                throw new \Exception('Page introuvable');
            }
        } catch (\Exception $e) {
            ErrorController::error404($e);
        }
    }

    /**
     * Delete order after validation
     */
    public function deleteOrder()
    {
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $this->orderModel->delete($_GET['id'], "order_id");
            header("Location: index.php?page=back&task=backOrder");
            exit;
        }
    }
}
