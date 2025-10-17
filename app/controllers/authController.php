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
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = $this->authModel->authenticate($email, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                header('Location: ' . DASHBOARD);
            } else {
                $error = "Invalid email or password";
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
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            if ($password !== $confirmPassword) {
                $error = "Passwords do not match";
                require_once 'app/views/authView/register.php';
                return;
            }

            if ($this->authModel->getUserByEmail($email)) {
                $error = "Email already registered";
                require_once 'app/views/authView/register.php';
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            if ($this->authModel->registerUser($email, $hashedPassword, $username)) {
                header('Location: ' . LOGIN);
            } else {
                $error = "Registration failed. Please try again.";
                require_once 'app/views/authView/register.php';
            }
        } else {
            require_once 'app/views/authView/register.php';
        }
    }
}
