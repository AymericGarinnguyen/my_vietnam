<?php

namespace Router;

class Router
{
    /**
     * processes the data sent in get to call the correct controllers and their methods
     * 
     * @return void
     */
    public static function process(): void
    {
        // default controller & method 
        $controllerName = "HomeController";
        $taskName = "display";
        //if page is get in url
        if (!empty($_GET['page'])) {
            $controllerName = ucfirst($_GET['page']) . "Controller";
        }
        //if task is get in url
        if (!empty($_GET['task'])) {
            $taskName = $_GET['task'];
        }
        // add \Controllers\ to $controllerName variable
        $controllerPath = "controllers/" . $controllerName;
        $controllerName = "\Controllers\\" . $controllerName;

        try {
            // if the controller exist, call it
            if (file_exists("../" . $controllerPath . ".php")) {
                $controller = new $controllerName();
                //if the method exist, call it
                if (method_exists($controllerName, $taskName)) {
                    $controller->$taskName();
                } else {
                    throw new \DomainException('Page introuvable');
                }
            } else {
                throw new \DomainException('Page introuvable');
            }
        } catch (\DomainException $e) {
            \Controllers\ErrorController::error404($e);
        }
    }
}
