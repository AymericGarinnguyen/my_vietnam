<?php

// require the classes called
spl_autoload_register(function ($className) {
    $className = str_replace("\\", "/", $className);
    $className = lcfirst($className );

    require "../" . $className . ".php";
});
