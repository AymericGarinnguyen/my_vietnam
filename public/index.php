<?php

require '../router/autoload.php';
\Libraries\Session::start();
\Router\Router::process();