<?php

class AuthController {

    public function showLoginForm() {
        require_once 'app/views/authView.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            require_once 'app/models/authModel.php';
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
                require_once 'app/views/authView.php';
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
}
