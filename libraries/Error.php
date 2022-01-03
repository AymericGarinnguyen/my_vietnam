<?php

namespace Libraries;

class Error
{
    /**
     * Check if session error exists => display error message
     * 
     * @param string $name
     * @return void
     */
    public static function display($name): void
    {
        if (isset($_SESSION[$name])) {
            echo    "<div class='no-js-error'>
                        <p>" . $_SESSION[$name]['message'] . "</p>
                    </div>";
        }
        // unset error sesion
        unset($_SESSION[$name]);
    }
}
