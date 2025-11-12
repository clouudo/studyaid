<?php

namespace App\Controllers;

use App\Models\AuthModel;

class AuthController
{
    private $authModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
    }

    public function showLoginForm()
    {
        require_once 'app/views/authView/login.php';
    }

    public function home(){
        require_once 'app/views/authView/home.php';
    }

    public function login()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . DASHBOARD);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize and validate input
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Please enter a valid email address";
                require_once 'app/views/authView/login.php';
                return;
            }

            // Check if email and password are provided
            if (empty($email) || empty($password)) {
                $error = "Please enter both email and password";
                require_once 'app/views/authView/login.php';
                return;
            }

            // Authenticate user (Model handles password verification)
            $user = $this->authModel->authenticate($email, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['success_message'] = "Login successful! Welcome back.";
                header('Location: ' . DASHBOARD);
                exit;
            } else {
                $error = "Invalid email or password. Please check your credentials and try again.";
                require_once 'app/views/authView/login.php';
            }
        } else {
            $this->showLoginForm();
        }
    }

    public function logout()
    {
        session_destroy();
        header('Location: ' . LOGIN);
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitize and validate input
            $username = trim($_POST['username'] ?? '');
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validate all fields are provided
            if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
                $error = "All fields are required";
                require_once 'app/views/authView/register.php';
                return;
            }

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Please enter a valid email address";
                require_once 'app/views/authView/register.php';
                return;
            }

            // Validate username length
            if (strlen($username) < 3) {
                $error = "Username must be at least 3 characters long";
                require_once 'app/views/authView/register.php';
                return;
            }

            // Validate password length
            if (strlen($password) < 8) {
                $error = "Password must be at least 8 characters long";
                require_once 'app/views/authView/register.php';
                return;
            }

            // Check if passwords match
            if ($password !== $confirmPassword) {
                $error = "Passwords do not match";
                require_once 'app/views/authView/register.php';
                return;
            }

            // Check if email already exists
            if ($this->authModel->getUserByEmail($email)) {
                $error = "Email already registered";
                require_once 'app/views/authView/register.php';
                return;
            }

            // Hash password and register user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            if ($this->authModel->registerUser($email, $hashedPassword, $username)) {
                $_SESSION['success_message'] = "Registration successful! Please login with your credentials.";
                header('Location: ' . LOGIN);
                exit;
            } else {
                $error = "Registration failed. Please try again.";
                require_once 'app/views/authView/register.php';
            }
        } else {
            require_once 'app/views/authView/register.php';
        }
    }

    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Please enter a valid email address";
                require_once 'app/views/authView/forgotPassword.php';
                return;
            }

            // Always show success to prevent email enumeration
            $message = "If an account exists for that email, a reset link has been sent.";

            $user = $this->authModel->getUserByEmail($email);
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $this->authModel->createPasswordResetToken((int)$user['userID'], $email, $token, date('Y-m-d H:i:s', time() + 3600));

                // Build absolute URL for email clients
                $resetLink = APP_URL . 'auth/resetPasswordForm/' . $token;
                // Send email (service may use SMTP if configured)
                $this->authModel->sendPasswordResetEmail($email, $resetLink);
            }

            require_once 'app/views/authView/forgotPassword.php';
            return;
        }

        require_once 'app/views/authView/forgotPassword.php';
    }

    public function resetPasswordForm($token = '')
    {
        $token = trim($token ?? '');
        if ($token === '') {
            $error = "Invalid reset link.";
            require_once 'app/views/authView/resetPassword.php';
            return;
        }
        $reset = $this->authModel->getPasswordResetByToken($token);
        if (!$reset || strtotime($reset['expiresAt']) <= time() || !empty($reset['usedAt'])) {
            $error = "This reset link is invalid or has expired.";
        }
        require_once 'app/views/authView/resetPassword.php';
    }

    public function resetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_PATH . 'auth/login');
            return;
        }
        $token = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($password !== $confirm) {
            $error = "Passwords do not match.";
            require_once 'app/views/authView/resetPassword.php';
            return;
        }
        if (strlen($password) < 8) {
            $error = "Password must be at least 8 characters.";
            require_once 'app/views/authView/resetPassword.php';
            return;
        }

        $reset = $this->authModel->getPasswordResetByToken($token);
        if (!$reset || strtotime($reset['expiresAt']) <= time() || !empty($reset['usedAt'])) {
            $error = "This reset link is invalid or has expired.";
            require_once 'app/views/authView/resetPassword.php';
            return;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $this->authModel->changePassword((int)$reset['userID'], $hashed);
        $this->authModel->markPasswordResetUsed((int)$reset['resetID']);

        $_SESSION['success_message'] = "Your password has been reset. Please login.";
        header('Location: ' . BASE_PATH . 'auth/login');
    }
}
