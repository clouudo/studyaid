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

    public function getUserIdByEmail($email){
        $conn = $this->db->connect();

        $query = "SELECT userID FROM user WHERE email = :email";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['userID'] : null;
    }

    public function addSession($email){
        $id = $this->getUserIdByEmail($email);
        if($id != null){
            $_SESSION['user_id'] = $id;
        }
    }

    public function authenticate($email, $password) {
        $user = $this->getUserByEmail($email);
        if($user != null){
            if(password_verify($password, $user['password'])) {
            $this-> addSession($email);
            return [
                'id' => $user['userID'],
                'email' => $user['email']
            ];
        }else{
            return false;
        }
        }
        else{
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

    public function changePassword($userId, $newPassword) {
        $conn = $this->db->connect();
        $query = "UPDATE user SET password = :password WHERE userID = :userID";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':password', $newPassword);
        $stmt->bindParam(':userID', $userId);
        return $stmt->execute();
    }
}
