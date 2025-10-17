<?php

namespace App\Config;

use PDO;
use PDOException;

class Database {
    private $host = 'localhost';
    private $db_name = 'studyaid';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            error_log("Database connection successful.");
        } catch(PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            echo 'Connection Error: ' . $e->getMessage();
        }

        return $this->conn;
    }
}
