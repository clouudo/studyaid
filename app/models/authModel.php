<?php

class AuthModel {

    private $db;

    public function __construct() {
        require_once 'app/config/database.php';
        $this->db = new Database();
    }

    public function authenticate($email, $password) {
        $conn = $this->db->connect();

        $query = "SELECT id, email, password FROM users WHERE email = :email";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }

        return false;
    }
}
