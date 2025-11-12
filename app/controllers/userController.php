<?php

namespace App\Controllers;

use App\Models\LmModel;
use App\Models\UserModel;
use App\Models\AuthModel;
use App\Config\Database;

class UserController {

    private $userModel;
    private $lmModel;
    private $authModel;
    private $db;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->lmModel = new LmModel();
        $this->authModel = new AuthModel();
        $this->db = new Database();
    }

    public function checkSession($isJsonResponse = false){
        if (!isset($_SESSION['user_id'])) {
            if ($isJsonResponse) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'User not logged in.']);
                exit();
            } else {
                header('Location: ' . BASE_PATH . 'auth/home');
                exit();
            }
        }
    }
    public function getUserInfo(){
        $userId = (int)$_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);
        return $user;
    }

    public function dashboard() {
        $this->checkSession();
        $userId = (int)$_SESSION['user_id'];
        $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
        $user = $this->getUserInfo();

        // Gather dashboard metrics
        $conn = $this->db->connect();
        // Documents
        $stmt = $conn->prepare("SELECT COUNT(*) FROM file WHERE userID = :userId");
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $documentsCount = (int)$stmt->fetchColumn();

        // Notes
        $stmt = $conn->prepare("SELECT COUNT(*) 
                                FROM note n 
                                INNER JOIN file f ON n.fileID = f.fileID 
                                WHERE f.userID = :userId");
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $notesCount = (int)$stmt->fetchColumn();

        // Summaries
        $stmt = $conn->prepare("SELECT COUNT(*) 
                                FROM summary s 
                                INNER JOIN file f ON s.fileID = f.fileID 
                                WHERE f.userID = :userId");
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $summariesCount = (int)$stmt->fetchColumn();

        // Flashcards
        $stmt = $conn->prepare("SELECT COUNT(*) 
                                FROM flashcard fc 
                                INNER JOIN file f ON fc.fileID = f.fileID 
                                WHERE f.userID = :userId");
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $flashcardsCount = (int)$stmt->fetchColumn();

        // Mindmaps
        $stmt = $conn->prepare("SELECT COUNT(*) 
                                FROM mindmap m 
                                INNER JOIN file f ON m.fileID = f.fileID 
                                WHERE f.userID = :userId");
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $mindmapsCount = (int)$stmt->fetchColumn();

        // Latest document
        $stmt = $conn->prepare("SELECT fileID, name FROM file WHERE userID = :userId ORDER BY fileID DESC LIMIT 1");
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $latestDocument = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        $latestDocumentId = $latestDocument ? (int)$latestDocument['fileID'] : 0;
        $latestDocumentName = $latestDocument ? $latestDocument['name'] : 'No documents yet';

        // Quiz history (line chart): use marked scores with timestamps
        $stmt = $conn->prepare("SELECT q.markAt, q.totalScore 
                                FROM quiz q 
                                INNER JOIN file f ON q.fileID = f.fileID 
                                WHERE f.userID = :userId AND q.totalScore IS NOT NULL 
                                ORDER BY q.markAt ASC, q.quizID ASC");
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $quizHistory = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        require_once 'app/views/dashboardView.php';
    }

    public function profile() {
        $this->checkSession();
        $user = $this->getUserInfo();
        $userId = (int)$_SESSION['user_id'];
        $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
        require_once 'app/views/profileView.php';
    }

    public function updateProfile() {
        $this->checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_PATH . 'user/profile');
            exit();
        }
        
        $userId = (int)$_SESSION['user_id'];
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        // Validate inputs
        if (empty($username) || empty($email)) {
            $_SESSION['error'] = "Username and email are required";
            header('Location: ' . BASE_PATH . 'user/profile');
            exit();
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Invalid email format";
            header('Location: ' . BASE_PATH . 'user/profile');
            exit();
        }
        
        // Check if email is already taken by another user
        $existingUser = $this->authModel->getUserByEmail($email);
        if ($existingUser && $existingUser['userID'] != $userId) {
            $_SESSION['error'] = "Email is already registered to another account";
            header('Location: ' . BASE_PATH . 'user/profile');
            exit();
        }
        
        try {
            if($this->userModel->updateUser($userId, $username, $email)){
                $_SESSION['message'] = "Profile updated successfully";
            } else {
                $_SESSION['error'] = "Failed to update profile";
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
        
        header('Location: ' . BASE_PATH . 'user/profile');
        exit();
    }

    public function changePassword(){
        $this->checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_PATH . 'user/profile');
            exit();
        }
        
        $userId = (int)$_SESSION['user_id'];
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        
        // Validate inputs
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error'] = "All password fields are required";
            header('Location: ' . BASE_PATH . 'user/profile');
            exit();
        }
        
        // Check password length
        if (strlen($newPassword) < 8) {
            $_SESSION['error'] = "New password must be at least 8 characters long";
            header('Location: ' . BASE_PATH . 'user/profile');
            exit();
        }
        
        // Check if passwords match
        if($newPassword !== $confirmPassword){
            $_SESSION['error'] = "New password and confirm password do not match";
            header('Location: ' . BASE_PATH . 'user/profile');
            exit();
        }
        
        // Verify current password
        $user = $this->getUserInfo();
        if(!password_verify($currentPassword, $user['password'])){
            $_SESSION['error'] = "Current password is incorrect";
            header('Location: ' . BASE_PATH . 'user/profile');
            exit();
        }
        
        // Hash new password and update
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        if($this->authModel->changePassword($userId, $hashedPassword)){
            $_SESSION['message'] = "Password changed successfully";
            header('Location: ' . BASE_PATH . 'auth/logout');
            exit();
        } else {
            $_SESSION['error'] = "Failed to change password";
            header('Location: ' . BASE_PATH . 'user/profile');
            exit();
        }
    }
}
