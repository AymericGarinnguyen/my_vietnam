<?php

namespace Controllers;

class UserController extends Controller
{
    protected $modelName = \Models\UserModel::class;
    protected string $title = 'connexion';
    protected string $pageContent = 'connection';
    protected array $variables = [];
    private array $selectAll =
    [
        'user_id',
        'firstname',
        'lastname',
        'email',
        'password',
        'phone',
        'address_line1',
        'address_line2', 'zipcode',
        'city',
        'is_admin',
        'created_at',
        'is_filled'
    ];

    /**
     * Create an array for session login
     * 
     * @param array $users
     * @return array
     */
    public static function sessionLogin(array $users): array
    {
        $result = [];
        foreach ($users as $user => $value) {
            if ($user !== 'password') {
                $result[$user] = $value;
            }
        }
        return $result;
    }

    /**
     * Manage user authentification, registration or update
     * 
     * @return void
     */
    public function manageUser():void
    {
        $isAjax = isset($_GET['ajax']) && $_GET['ajax'] === 'true';
        try {
            if (isset($_POST) && !empty($_POST)) {
                //save $_post in session
                $_SESSION['user'] = [];
                foreach ($_POST as $input => $post) {
                    $data = (isset($_POST['insert'])) ? "reg-{$input}" : $input;
                    $_SESSION['user'] += [$data => $post];
                }

                if(isset($_POST['auth'])) {
                    // find user by email
                    $user = $this->model->findByItem($this->selectAll, 'email', $_POST['email']);
                    // if user doesn't exist, throw an exception
                    if (empty($user)) {
                        throw new \DomainException(serialize([
                            'name' => 'email',
                            'message' => "Cet utilisateur n'existe pas"
                        ]));
                    } else {
                        // if password is verified
                        if (password_verify($_POST['password'], $user['password'])) {
                            // init the session[login]
                            \Libraries\Session::init('login', self::sessionLogin($user));
                            // destroy the session auth & redirection
                            \Libraries\Session::destroyByValue('user');
                            if ($isAjax) {
                                echo 'success';
                            } else {
                                header("Location: index.php?page=home");
                                exit;
                            }
                        } else {
                            throw new \DomainException(serialize([
                                'name' => 'password',
                                'message' => 'Le mot de passe de est erroné'
                            ]));
                        }
                    }
                } else {
                    foreach ($_POST as $input => $post) {
                        // check $_POST values
                        $_POST[$input] = \Libraries\CheckForm::checkSecurity($post);
                        $data = (isset($_POST['update'])) ? "check-{$input}" : $input;
                        
                        if ($input === "firstname") {
                            \Libraries\CheckForm::emptyInput($data, $post, 'Prénom');
                            \Libraries\CheckForm::fieldCheck($post, '[a-zA-Z\à\â\ä\é\è\ê\ë\î\ï\ô\ö\ù\û\ü\s]', 2, 40, $data, 'Prénom');
                        } elseif ($input === "lastname") {
                            \Libraries\CheckForm::emptyInput($data, $post, 'Nom');
                            \Libraries\CheckForm::fieldCheck($post, '[a-zA-Z\à\â\ä\é\è\ê\ë\î\ï\ô\ö\ù\û\ü\s]', 2, 40, $data, 'Nom');
                        } elseif ($input === "email") {
                            \Libraries\CheckForm::emptyInput($data, $post, 'Email');
                            \Libraries\CheckForm::emailCheck($post);
                        } elseif ($input === "password") {
                            \Libraries\CheckForm::emptyInput($data, $post, 'Mot de passe');
                            \Libraries\CheckForm::passwordCheck($post, '^\S*(?=\S{5,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$', 'password', 'Mot de passe');
                        } elseif ($input === "phone") {
                            \Libraries\CheckForm::emptyInput('check-phone', $post, 'Téléphone');
                            \Libraries\CheckForm::phoneCheck($post, 'check-phone', 'Téléphone');
                        } elseif ($input === "address_line1") {
                            \Libraries\CheckForm::emptyInput('check-address_line1', $post, 'Adresse');
                            \Libraries\CheckForm::lengthCheck($post, 2, 400, 'check-address_line1', 'Adresse');
                        } elseif ($input === "address_line2") {
                            if (!empty($post)) {
                                \Libraries\CheckForm::lengthCheck($post, 2, 400, 'check-address_line2', 'Compément');
                            }
                        } elseif ($input === "zipcode") {
                            \Libraries\CheckForm::emptyInput('check-zipcode', $post, 'Code postal');
                            \Libraries\CheckForm::fieldCheck($post, '[0-9]', 5, 7, 'check-zipcode', 'Code Postal');
                        } elseif ($input === "city") {
                            \Libraries\CheckForm::emptyInput('check-city', $post, 'Ville');
                            \Libraries\CheckForm::fieldCheck($post, '[a-zA-Z\à\â\ä\é\è\ê\ë\î\ï\ô\ö\ù\û\ü\s\-]', 2, 100, 'check-city', 'ville');
                        }
                    }
                    if(isset($_POST['insert'])) {
                        // if user already exist in the database
                        if ($this->model->findByItem(['email'], 'email', $_POST['email'])) {
                            throw new \DomainException(serialize([
                                'name' => 'register-email',
                                'message' => "Cet utilisateur existe déjà"
                            ]));
                        }
                        $structure =
                            [
                                'firstname',
                                'lastname',
                                'email',
                                'password',
                                'is_admin',
                                'created_at'
                            ];
                        $values =
                            [
                                ':firstname',
                                ':lastname',
                                ':email',
                                ':password',
                                0,
                                'now()'
                            ];
                        $params =
                            [
                                ':firstname'   => $_POST['firstname'],
                                ':lastname'    => $_POST['lastname'],
                                ':email'        => $_POST['email'],
                                ':password'     => password_hash($_POST['password'], PASSWORD_DEFAULT)
                            ];

                        // insert in the database
                        $this->model->insert($structure, $values, $params);
                        // find this new user in the database to fill session[login]
                        $user = $this->model->findByItem($this->selectAll, 'email', $_POST['email']);
                        // init the session[login]
                        \Libraries\Session::init('login', self::sessionLogin($user));
                        // destroy the session auth & redirection
                        \Libraries\Session::destroyByValue('user');
                        if ($isAjax) {
                            echo 'success';
                        } else {
                            header("Location: index.php?page=home");
                            exit;
                        }
                    } elseif (isset($_POST['update'])) {
                        $structure =
                        [
                            'firstname = :firstname',
                            'lastname = :lastname',
                            'email = :email',
                            'phone = :phone',
                            'address_line1 = :address_line1',
                            'address_line2 = :address_line2',
                            'zipcode = :zipcode',
                            'city = :city',
                            'is_filled = :is_filled'
                        ];
                        $params =
                            [
                                ':firstname'       => $_POST['firstname'],
                                ':lastname'        => $_POST['lastname'],
                                ':email'            => $_POST['email'],
                                ':phone'            => $_POST['phone'],
                                ':address_line1'    => $_POST['address_line1'],
                                ':address_line2'    => $_POST['address_line2'],
                                ':zipcode'          => $_POST['zipcode'],
                                ':city'             => $_POST['city'],
                                ':is_filled'        => 1,
                                ':user_id'          => $_SESSION['login']['user_id']
                            ];
                        $this->model->update($structure, 'user_id', $params);
                        // find this new user in the database to fill session[login]
                        $user = $this->model->findByItem($this->selectAll, 'email', $_POST['email']);
                        // init the session[login]
                        \Libraries\Session::init('login', self::sessionLogin($user));
                        // destroy the session auth & redirection
                        \Libraries\Session::destroyByValue('user');
                        if ($isAjax) {
                            echo 'success';
                        } else {
                            header('Location: index.php?page=cart&task=summary');
                            exit;
                        }
                    }
                }
            }
        } catch (\ErrorException $e) {
            ErrorController::error404($e);
        } catch (\DomainException $e) {
            if ($isAjax) {
                $data = unserialize($e->getMessage());
                header('Content-Type: application/json');
                echo json_encode($data);
            } else {
                ErrorController::errorForm('user', unserialize($e->getMessage()));
            }
        }
    }

    /**
     * Log out the current user, destroy all sessions
     * 
     * @return void
     */
    public function logout(): void
    {
        \Libraries\Session::destroyAll();
        header('Location: index.php?page=home');
    }
}
