<?php

namespace Libraries;

class Renderer
{
    /**
     * Show the page call in $path, pass all the variables and call the layout
     * 
     * @param string $title
     * @param string $path
     * @param array $variables
     * @return void
     */
    public static function render(string $title, string $path, array $variables = []): void
    {
        // extract $variables
        extract($variables);
        // Capitalize $title
        $pageTitle = ucfirst($title);
        // save content html path
        $pageContent = $path.".phtml";
        // call layout.phtml
        require '../views/layout.phtml';
    }
}