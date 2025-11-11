<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StudyAids</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
       .logo-box {
            background-color: #00000000;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 5px;
        }
        .logo-box img {
            width: 100px;
            height: 100px;
            object-fit: contain;
        }
        .brand-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #212529;
        }
        .auth-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 20px;}

        .auth-title {
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 8px;
        }
        .auth-subtitle {
            color: #6c757d;
            font-size: 0.95rem;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 16px;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #6f42c1;
            box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
        }
        .btn-login {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background-color: #5a32a3;
            border-color: #5a32a3;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(111, 66, 193, 0.3);
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            border-radius: 8px;
            padding: 12px 16px;
        }
        .register-link {
            color: #6f42c1;
            text-decoration: none;
            font-weight: 600;
        }
        .register-link:hover {
            color: #5a32a3;
            text-decoration: underline;
        }
        .password-toggle {
            position: relative;
        }
        .password-toggle-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6C757D;
            cursor: pointer;
            padding: 0;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .password-toggle-btn:hover {
            color: #6f42c1;
        }
        .btn-back {
            background-color: transparent;
            color: #6f42c1;
            padding: 8px 20px;
            border-radius: 8px;          
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            margin-left:365px;
            font-size: 30px;
        }
        .btn-back:hover {
            background-color: #6f42c1;
            color: white;
        }
        .alert-success {
            background-color: #d1e7dd;
            border-color: #badbcc;
            color: #0f5132;
            border-radius: 8px;
            padding: 12px 16px;
        }
        /* Snackbar Styles */
        .snackbar {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background-color: #333;
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 9999;
            min-width: 300px;
            max-width: 500px;
            display: flex;
            align-items: center;
            gap: 12px;
            opacity: 0;
            transition: all 0.3s ease-in-out;
        }
        .snackbar.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        .snackbar.success {
            background-color: #28a745;
        }
        .snackbar.error {
            background-color: #dc3545;
        }
        .snackbar-icon {
            font-size: 1.2rem;
        }
        .snackbar-message {
            flex: 1;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="auth-container">
                    <a href="<?= HOME ?>" class="btn-back">
                        <i class="bi bi-x"></i>
                    </a>
                    
                    <div class="auth-header">
                        <img src="<?= IMG_LOGO ?>" alt="StudyAids Logo" style="width: 200px; height: 200px; object-fit: contain;">
                        <h1 class="auth-title">Welcome Back</h1>
                        <p class="auth-subtitle">Login to continue your learning journey</p>
                    </div>
                    
                    <!-- Snackbar Container -->
                    <div id="snackbar" class="snackbar">
                        <i class="snackbar-icon" id="snackbarIcon"></i>
                        <span class="snackbar-message" id="snackbarMessage"></span>
                    </div>
                    
                    <?php 
                    $successMessage = null;
                    if (isset($_SESSION['success_message'])) {
                        $successMessage = $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                    }
                    $errorMessage = isset($error) ? $error : null;
                    ?>
                    
                    <form action="<?= BASE_PATH ?>auth/login" method="POST" id="loginForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   placeholder="Enter your email" autocomplete="email" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="password-toggle">
                                <input type="password" class="form-control" id="password" name="password" required 
                                       placeholder="Enter your password" autocomplete="current-password">
                                <button type="button" class="password-toggle-btn" onclick="togglePassword()">
                                    <i class="bi bi-eye-fill" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-login">Login</button>
                    </form>
                    
                    <p class="text-center mt-3 mb-0">
                        <a href="<?= BASE_PATH ?>auth/forgotPassword" class="register-link">Forgot your password?</a>
                    </p>
                    
                    <p class="text-center mt-4 mb-0">
                        Don't have an account? 
                        <a href="<?= BASE_PATH ?>auth/register" class="register-link">Register here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'bi bi-eye-slash-fill';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'bi bi-eye-fill';
            }
        }
        
        // Snackbar function
        function showSnackbar(message, type) {
            const snackbar = document.getElementById('snackbar');
            const snackbarMessage = document.getElementById('snackbarMessage');
            const snackbarIcon = document.getElementById('snackbarIcon');
            
            snackbarMessage.textContent = message;
            snackbar.className = 'snackbar ' + type;
            
            if (type === 'success') {
                snackbarIcon.className = 'snackbar-icon bi bi-check-circle-fill';
            } else if (type === 'error') {
                snackbarIcon.className = 'snackbar-icon bi bi-x-circle-fill';
            }
            
            snackbar.classList.add('show');
            
            setTimeout(function() {
                snackbar.classList.remove('show');
            }, 3000);
        }
        
        // Show messages on page load
        <?php if ($successMessage): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showSnackbar('<?php echo addslashes($successMessage); ?>', 'success');
        });
        <?php endif; ?>
        
        <?php if ($errorMessage): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showSnackbar('<?php echo addslashes($errorMessage); ?>', 'error');
        });
        <?php endif; ?>
        
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (!email) {
                e.preventDefault();
                showSnackbar('Please enter your email address', 'error');
                return false;
            }
            
            if (!password) {
                e.preventDefault();
                showSnackbar('Please enter your password', 'error');
                return false;
            }
            
            // Email format validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                showSnackbar('Please enter a valid email address', 'error');
                return false;
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>