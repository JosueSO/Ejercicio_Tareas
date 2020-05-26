<?php 

class DB {
    private static $connection;

    public static function getConnection() {
        if(self::$connection === null) {
            self::$connection = new PDO('mysql:host=localhost;dbname=lista_tareas;charset=utf8', 'root', '');
            //self::$connection = new PDO('mysql:host=localhost;dbname=id13815927_lista_tareas;charset=utf8', 'id13815927_root', 'Fundamentos2020?');
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }

        return self::$connection;
    }
}

?>