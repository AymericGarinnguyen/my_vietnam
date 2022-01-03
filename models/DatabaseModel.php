<?php

namespace Models;

class DatabaseModel
{

    private static $instance = null;

    /**
     * Returns a connection to the database
     * Use the singleton pattern to avoid recalling several times the connection to the database, technique see from Lior Chamla's video
     * 
     * @return PDO
     */
    public static function getPdo(): \PDO
    {
        try {
            if (self::$instance === null) {

                self::$instance = new \PDO('mysql:host=localhost;dbname=my_vietnam;charset=utf8', 'root', '', [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ]);
            }
            return self::$instance;
        } catch (\PDOException $e) {
            \Controllers\ErrorController::error404($e);
        }
    }
}
