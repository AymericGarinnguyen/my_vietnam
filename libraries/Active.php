<?php

namespace Libraries;

class Active
{
    /**
     * check if this link is for the current page
     * 
     * @param string $get
     * @param string $variable
     * @return void
     */
    public static function activeTab(string $get, string $variable): void
    {
        if (isset($_GET[$get]) && $_GET[$get] === $variable) {
            // add 'active' classname
            echo 'active';
        }
    }
}