<?php

namespace Controllers;

class NewsletterController extends Controller
{
    protected $modelName = \Models\NewsletterModel::class;

    /**
     * Insert a new email in the database => table newsletter
     * 
     * @return void
     */
    public function insert(): void
    {
        $isAjax = isset($_GET['ajax']) && $_GET['ajax'] === 'true';
        try {
            if (isset($_POST) && !empty($_POST)) {
                // check $_POST values
                $_POST['email'] = \Libraries\CheckForm::checkSecurity($_POST['email']);

                \Libraries\CheckForm::emptyInput('newsletter', $_POST['email'], 'Email');
                \Libraries\CheckForm::emailCheck($_POST['email']);
                // if user already exist in the database
                if ($this->model->findByItem(['email'], 'email', $_POST['email'])) {
                    throw new \DomainException(serialize([
                        'name' => 'newsletter',
                        'message' => "Cet email est déjà abonné"
                    ]));
                }

                $structure = ['email'];
                $values = [':email'];
                $params = [':email' => $_POST['email']];

                // insert in the database
                $this->model->insert($structure, $values, $params);
                
                if ($isAjax) {
                    echo 'success';
                } else {
                    header('Location: ' . $_SERVER['HTTP_REFERER'].'&newsletter=success');
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
                ErrorController::errorForm('newsletter', unserialize($e->getMessage()));
            }
        }
    }
}
