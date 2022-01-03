<?php

namespace Controllers;

class ContactController extends Controller
{
    protected string $title = 'contact';
    protected string $pageContent = 'contact';
    protected array $variables = [];

    public function __construct()
    {
        parent::__construct();
        // give the active session to the variable
        $this->variables = ['session' => $this->checkSession()];
    }

    /**
     * Check if there is a session to fill the inputs
     * 
     * @return array||null
     */
    public function checkSession(): ?array
    {
        if (isset($_SESSION['contact'])) {
            return $_SESSION['contact'];
        } elseif (isset($_SESSION['login'])) {
            return $_SESSION['login'];
        } else {
            return null;
        }
    }

    /**
     * Send a message to the site administrator
     * 
     * @return void
     */
    public function send(): void
    {
        $isAjax = isset($_GET['ajax']) && $_GET['ajax'] === 'true';
        try {
            if (isset($_POST) && !empty($_POST)) {
                // save the user's message
                \Libraries\Session::init('contact', [
                    "firstname" => $_POST['firstname'],
                    "lastname"  => $_POST['lastname'],
                    "email"     => $_POST['email'],
                    "subject"   => $_POST['subject'],
                    "message"   => $_POST['message']
                ]);

                // check all inputs sent, empty, length, characters
                foreach ($_POST as $input => $post) {
                    // check $_post values
                    $_POST[$input] = \Libraries\CheckForm::checkSecurity($post);
                    
                    if ($input === "firstname") {
                        \Libraries\CheckForm::emptyInput('contact-firstname', $post, 'Prénom');
                        \Libraries\CheckForm::fieldCheck($post, '[a-zA-Z\à\â\ä\é\è\ê\ë\î\ï\ô\ö\ù\û\ü\s]', 2, 40, 'contact-firstname', 'Prénom');
                    } elseif ($input === "lastname") {
                        \Libraries\CheckForm::emptyInput('contact-name', $post, 'Nom');
                        \Libraries\CheckForm::fieldCheck($post, '[a-zA-Z\à\â\ä\é\è\ê\ë\î\ï\ô\ö\ù\û\ü\s]', 2, 40, 'contact-lastname', 'Nom');
                    } elseif ($input === "email") {
                        \Libraries\CheckForm::emptyInput('contact-email', $post, 'Email');
                        \Libraries\CheckForm::emailCheck($post);
                    } elseif ($input === "subject") {
                        \Libraries\CheckForm::emptyInput('contact-subject', $post, 'Sujet');
                        \Libraries\CheckForm::lengthCheck($post, 2, 100, 'contact-subject', 'Sujet');
                    } elseif ($input === "message") {
                        \Libraries\CheckForm::emptyInput('contact-message', $post, 'Message');
                        \Libraries\CheckForm::lengthCheck($post, 10, 1000,'contact-message', 'Message');
                    }
                }

                // localhost configuration
                if ($_SERVER['SERVER_NAME'] === "localhost") {
                    ini_set('SMTP', 'smtp.bbox.fr');
                    ini_set('smtp_port', 25);
                }

                $recipient = "aymeric.garin@yahoo.fr";
                $mail_body = $_POST['message'];
                $subject = $_POST['subject'];
                $header = "From: " . $_POST['firstname'] . " " . $_POST['lastname'] . " <" . $_POST['email'] . ">\r\n";

                // send the message
                mail($recipient, $subject, $mail_body, $header);

                // destroy the contact session and error session
                \Libraries\Session::destroyByValue('contact');
                \Libraries\Session::destroyByValue('error-contact');
                //redirection to the contact page with $_get success
                if($isAjax) {
                    echo 'success';
                } else {
                    header('Location:index.php?page=contact&success=true');
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
                ErrorController::errorForm('contact', unserialize($e->getMessage()));
            }
        }
    }
}
