<?php

namespace Database;

/**
 * Class which exposes a MySQL connection, using singleton design pattern.
 */
class DB
{
    private \mysqli $db;

    private static DB $instance;

    public static function getDB(): \mysqli
    {
        if (!isset(self::$instance)) {
            self::$instance = new DB();
        }
        return self::$instance->db;
    }

    private function __construct()
    {
        $servername = "localhost";
        $username = "secret";
        $password = "secret";
        $dbname = "secret";
        $this->db = new \mysqli($servername, $username, $password, $dbname);
        if ($this->db->connect_error) {
            die("Connection failed:{$this->db->connect_error}");
        }
    }
}
