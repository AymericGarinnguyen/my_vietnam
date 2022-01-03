<?php

namespace Controllers;

class ErrorController
{
    /**
     * Show page 404
     * 
     * @return void
     */
    public function display(): void
    {
        require '../views/404.phtml';
    }

    /**
     * Redirection to this class->display()
     * 
     * @return void
     */
    public static function error404(object $e): void
    {
        $data = [
            'error' => $e->getMessage()
        ];
        \Libraries\Session::setError('404', $data);
        header('Location: index.php?page=error');
        exit;
    }

    /**
     * Redirection to the previous page
     * 
     * @return void
     */
    public static function errorForm(string $name, array $error): void
    {
        \Libraries\Session::setError($name, $error);
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
}