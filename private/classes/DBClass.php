<?php
namespace classes;

use classes\DBConnection;

class DBClass {

    static protected $db;

    static public function setDatabase(DBConnection $db): void {
        self::$db = $db->getConnection();
    }
}