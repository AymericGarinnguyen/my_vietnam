<?php

namespace Controllers;

abstract class Controller
{
    protected object $model;
    protected $modelName;
    protected string $title;
    protected string $pageContent;
    protected array $variables = [];

    public function __construct()
    {

        // if a modelName is instantiated, call the corresponding model class
        if($this->modelName !== null) {
            $this->model = new $this->modelName();
        }
        

        // destroy temporary sesions
        if(!isset($_GET['page']) || $_GET['page'] !== "forum") {
            \Libraries\Session::destroyByValue("chatId");
        }
        
        if(!isset($_GET['page']) || $_GET['page'] !== "back") {
            \Libraries\Session::destroyByValue("back");
        }
    }

    /**
     * Returns the rendering of the page called
     * @return void
     */
    public function display(): void
    {
        // render page content
        \Libraries\Renderer::render($this->title, $this->pageContent, $this->variables);
    }
}