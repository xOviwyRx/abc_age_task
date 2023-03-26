<?php
namespace classes;

use mysqli;
class DBConnection
{
    private $connection;

    public function __construct()
    {
        $this->connect();
    }

    public function getConnection(){
        return $this->connection;
    }

    private function connect(): void
    {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->connection->connect_errno) {
            $msg = "Database connection failed: ";
            $msg .= $this->connection->connect_error;
            $msg .= " (" . $this->connection->connect_errno . ")";
            die($msg);
        }
    }

    public function __destruct()
    {
        $this->connection->close();
    }
}