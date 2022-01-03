<?php

namespace Controllers;

class ForumController extends Controller
{
    protected $modelName = \Models\ForumModel::class;
    protected string $title = 'forum';
    protected string $pageContent = 'forum/forum';
    protected array $variables = [];
    private object $modelCategory;

    public function __construct()
    {
        parent::__construct();
        // check if user is connected
        if (!isset($_SESSION['login'])) {
            header('Location: index.php?page=home');
            exit;
        }
        $this->modelCategory = new \Models\CategoryForumModel();
        // Init the array $variables with last message
        $this->variables = ['lastDate' => $this->lastMessages()];
    }

    /**
     * Find the last message of each chat
     * 
     * @return
     */

    public function lastMessages()
    {
        // find last messages by categories in database
        $result = $this->model->findLastMessage();
        return $result;
    }

    /**
     * Show chat page by categories id
     * 
     * @return void
     */
    public function chat(): void
    {
        // unset temporarry sessions
        $id = null;
        try {
            if (!empty($_GET['id'])) {
                $id = $_GET['id'];

                // find $_GET['id'] in the database table forum_categories
                $checkId = $this->modelCategory->findByItem(['categories_id'], 'categories_id', $id);
                // if $checkId is true

                if ($checkId) {
                    // save id in session
                    $_SESSION['chatId'] = $id;

                    // determine number of comments by id
                    $result = $this->model->countComments($id);
                    $nbComments = intval($result['nb_comments']);

                    // how many comments allowed per page
                    $nbPerPage = 10;

                    // total number of pages
                    $pageTotal = ceil($nbComments / $nbPerPage);

                    // determine which page we are on
                    $currentPage = null;

                    // if get[indexPage] exists, is not empty and is under or equal to total number of pages 
                    if (isset($_GET['indexPage']) && !empty($_GET['indexPage']) && $_GET['indexPage'] <= $pageTotal) {
                        $currentPage = intval($_GET['indexPage']);
                    } else {
                        $currentPage = 1;
                    }

                    // first comment of each page
                    $firstComment = ($currentPage * $nbPerPage) - $nbPerPage;

                    // find all messages in the database by category and is it's checked
                    $messages = $this->model->findCommentsById($id, $firstComment, $nbPerPage);

                    // find category in the database by id
                    $forumCategory = $this->modelCategory->findByItem(['category'], 'categories_id', $id);
                    $pageCategory = $forumCategory['category'];

                    // determine url without param indexPage
                    $urlArray = explode('&', $_SERVER["REQUEST_URI"]);
                    $url = $urlArray[0] . '&' . $urlArray[1] . '&' . $urlArray[2];

                    \Libraries\Renderer::render('Forum : ' . $pageCategory, 'forum/chat', compact('messages', 'pageCategory', 'currentPage', 'pageTotal', 'url'));
                } else {
                    throw new \Exception("Page introuvable");
                }
            } else {
                throw new \Exception("Page introuvable");
            }
        } catch (\Exception $e) {
            ErrorController::error404($e);
        }
    }

    /**
     * insert new comment in the database
     * 
     * @return void
     */
    public function insert()
    {
        $isAjax = isset($_GET['ajax']) && $_GET['ajax'] === 'true';
        try {
            if (isset($_POST) && !empty($_POST)) {
                // save user's message in session
                \Libraries\Session::init('forum', [
                    "title" => $_POST['title'],
                    "message"  => $_POST['message']
                ]);
                // check all inputs
                foreach ($_POST as $input => $post) {
                    // check $_POST values
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
                        'title', 'message', 'categories_id', 'user_id', 'created_at', 'is_checked'
                    ];
                $values =
                    [
                        ':title', ':message', ':categories_id', ':user_id', 'now()', '0'
                    ];
                $params =
                    [
                        ':title'            => $_POST['title'],
                        ':message'          => $_POST['message'],
                        ':categories_id'    => $_SESSION['chatId'],
                        ':user_id'          => $_SESSION['login']['user_id']
                    ];
                // insert in the database
                $this->model->insert($structure, $values, $params);
                // destroy session['chat'] & ['error'] & redirection
                \Libraries\Session::destroyByValue('forum');
                \Libraries\Session::destroyByValue('error-forum');
                if ($isAjax) {
                    echo 'success';
                } else {
                    \Libraries\Session::init('success-message', [
                        "message"  => "Votre message a bien été envoyé !"
                    ]);
                    header("Location: index.php?page=forum&task=chat&id={$_SESSION['chatId']}");
                    exit;
                }
            } else {
                throw new \ErrorException('Vous ne pouvez pas accéder à cette page');
            }
        } catch (\ErrorException $e) {
            ErrorController::error404($e);
        } catch (\DomainException $e) {
            if ($isAjax) {
                $data = unserialize($e->getMessage());
                header('Content-Type: application/json');
                echo json_encode($data);
            } else {
                ErrorController::errorForm('forum', unserialize($e->getMessage()));
            }
        }
    }
}
