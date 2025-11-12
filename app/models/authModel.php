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

    public function createPasswordResetToken(int $userId, string $email, string $token, string $expiresAt) {
        $conn = $this->db->connect();
        $query = "INSERT INTO password_reset (userID, email, token, expiresAt, createdAt) VALUES (:userID, :email, :token, :expiresAt, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userID', $userId);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expiresAt', $expiresAt);
        return $stmt->execute();
    }

    public function getPasswordResetByToken(string $token) {
        $conn = $this->db->connect();
        $query = "SELECT * FROM password_reset WHERE token = :token LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function markPasswordResetUsed(int $resetId) {
        $conn = $this->db->connect();
        $query = "UPDATE password_reset SET usedAt = NOW() WHERE resetID = :resetID";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':resetID', $resetId);
        return $stmt->execute();
    }

    public function sendPasswordResetEmail(string $email, string $resetLink) {
        // Prefer EmailService if available (SMTP), fallback to mail()
        try {
            if (class_exists('\\App\\Services\\EmailService')) {
                $service = new \App\Services\EmailService();
                $subject = 'Password Reset - StudyAids';
                $html = '<p>We received a request to reset your password.</p>'
                    . '<p><a href="' . htmlspecialchars($resetLink) . '">Click here to reset your password</a></p>'
                    . '<p>This link expires in 1 hour. If you did not request this, you can ignore this email.</p>';
                $text = "We received a request to reset your password.\n"
                    . "Open this link to reset: " . $resetLink . "\n"
                    . "This link expires in 1 hour.";
                $ok = $service->send($email, $subject, $html, $text);
                error_log('[AuthModel] Password reset email ' . ($ok ? 'sent' : 'failed') . ' to=' . $email);
                return;
            }
        } catch (\Throwable $e) {
            error_log('EmailService error: ' . $e->getMessage());
        }
        // Fallback
        $subject = 'Password Reset - StudyAids';
        $message = "Click the link to reset your password: " . $resetLink . "\nThis link expires in 1 hour.";
        $headers = 'From: no-reply@studyaids' . "\r\n" .
                   'Content-Type: text/plain; charset=UTF-8';
        $ok = @mail($email, $subject, $message, $headers);
        error_log('[AuthModel] Fallback mail() ' . ($ok ? 'sent' : 'failed') . ' to=' . $email);
    }
}
