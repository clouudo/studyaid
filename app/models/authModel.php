<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class AuthModel {

    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getUserByEmail($email){
        $conn = $this->db->connect();

        $query = "SELECT * FROM user WHERE email = :email";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function authenticate($email, $password) {
        $user = $this->getUserByEmail($email);
        if($user != null){
            error_log("User fetched: " . print_r($user, true));
            if(password_verify($password, $user['password'])) {
            error_log("Authentication successful for user: " . $user['email']);
            return [
                'id' => $user['userID'],
                'email' => $user['email']
            ];
        }else{
            error_log("Authentication failed for user: " . $user['email']);
            return false;
        }
        }
        else{
            error_log("No user found with email: " . $email);
            return false;
        }
    }

    public function registerUser($email, $hashedPassword, $username) {
        $conn = $this->db->connect();

        $query = "INSERT INTO user (email, password, username) VALUES (:email, :password, :username)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':username', $username);

        return $stmt->execute();
    }
}
