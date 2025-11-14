<?php

namespace App\Models;

use App\Config\Database;

class UserModel {

    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getUserById($userId){
        $conn = $this->db->connect();
        $query = "SELECT * FROM user WHERE userID = :userId";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateUser($userId, $username, $email){
        $conn = $this->db->connect();
        $query = "UPDATE user SET username = :username, email = :email WHERE userID = :userId";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function deactivateUser($userId){
        $conn = $this->db->connect();
        $query = "UPDATE user SET isActive = 'FALSE' WHERE userID = :userId";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        return $stmt->rowCount();
    }

}
