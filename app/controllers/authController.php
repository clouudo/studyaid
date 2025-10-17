<?php

namespace App\Controllers;

use App\Models\AuthModel;

class AuthController {

    public function showLoginForm() {
        require_once 'app/views/authView/login.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $authModel = new AuthModel();
            $user = $authModel->authenticate($email, $password);

            if ($user) {
                // Start a session and redirect to the dashboard
                session_start();
                $_SESSION['user_id'] = $user['id'];
                header('Location: index.php?url=user/dashboard');
            } else {
                // Show an error message
                $error = "Invalid email or password";
                require_once 'app/views/authView/login.php';
            }
        } else {
            $this->showLoginForm();
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: index.php?url=auth/login');
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            if(empty($username)){
                $error = "Username is required";
                require_once 'app/views/authView/register.php';
                return;
            }

            if ($password !== $confirmPassword) {
                $error = "Passwords do not match";
                require_once 'app/views/authView/register.php';
                return;
            }

            $authModel = new AuthModel();
            if ($authModel->getUserByEmail($email)) {
                $error = "Email already registered";
                require_once 'app/views/authView/register.php';
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            if ($authModel->registerUser($email, $hashedPassword, $username)) {
                header('Location: index.php?url=auth/login');
            } else {
                $error = "Registration failed. Please try again.";
                require_once 'app/views/authView/register.php';
            }
        } else {
            require_once 'app/views/authView/register.php';
        }
    }
}
