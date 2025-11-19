<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --sa-primary: #6f42c1;
            --sa-primary-dark: #593093;
            --sa-accent: #e7d5ff;
            --sa-accent-strong: #d4b5ff;
            --sa-muted: #6c757d;
            --sa-card-border: #ede1ff;
        }

        .card {
            border: 1px solid var(--sa-card-border);
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(111, 66, 193, 0.08);
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
        .upload-container {
            background-color: #f8f9fa;
            padding: 30px;
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
            z-index: 10;
        }
        .password-toggle-btn:hover {
            color: #6f42c1;
        }
        .password-toggle .form-control {
            padding-right: 40px;
        }
        .form-control-lg {
            border: 2px solid var(--sa-card-border);
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }

        .form-control-lg:focus {
            border-color: var(--sa-primary);
            box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
        }
        .modal-close-btn {
            background-color: transparent;
            color: #6f42c1;
            border: none;
            font-size: 1.5rem;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .modal-close-btn:hover {
            background-color: #6f42c1;
            color: white;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include VIEW_SIDEBAR; ?>
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
            <div class="container-fluid upload-container">
                <!-- Snackbar Container -->
                <div id="snackbar" class="snackbar">
                    <i class="snackbar-icon" id="snackbarIcon"></i>
                    <span class="snackbar-message" id="snackbarMessage"></span>
                    </div>
                
                <?php
                $successMessage = null;
                $errorMessage = null;
                if (isset($_SESSION['message'])) {
                    $successMessage = $_SESSION['message'];
                    unset($_SESSION['message']);
                }
                if (isset($_SESSION['error'])) {
                    $errorMessage = $_SESSION['error'];
                    unset($_SESSION['error']);
                }
                ?>

                <h3 style="color: #212529; font-size: 1.5rem; font-weight: 600; margin-bottom: 30px;">Manage Profile</h3>

            <div class="row g-4">
                    <!-- Profile Information Section -->
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-header d-flex align-items-center" style="background: linear-gradient(135deg, #f6efff, #ffffff); border-bottom: 1px solid var(--sa-card-border);">
                                <i class="bi bi-person-circle me-3" style="font-size: 1.5rem; color: #6f42c1;"></i>
                                <div class="flex-grow-1">
                                    <h5 class="mb-0" style="color: #6f42c1; font-weight: 600;">Profile Information</h5>
                                    <small class="text-muted">Update your personal details</small>
                                </div>
                            </div>
                            <div class="card-body">
                                <form id="profileForm" action="<?= BASE_PATH ?>user/updateProfile" method="POST">
                                    <div class="mb-4">
                                        <label for="username" class="form-label fw-semibold">Username</label>
                                        <input type="text" class="form-control form-control-lg" id="username" name="username" 
                                               value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-4">
                                        <label for="email" class="form-label fw-semibold">Email</label>
                                        <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                               value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary" style="background-color: #6f42c1; border: none; padding: 10px 24px; font-weight: 600;">Update Profile</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Change Password Section -->
                        <div class="card mb-4">
                            <div class="card-header d-flex align-items-center" style="background: linear-gradient(135deg, #f6efff, #ffffff); border-bottom: 1px solid var(--sa-card-border);">
                                <i class="bi bi-key me-3" style="font-size: 1.5rem; color: #6f42c1;"></i>
                                <div class="flex-grow-1">
                                    <h5 class="mb-0" style="color: #6f42c1; font-weight: 600;">Change Password</h5>
                                    <small class="text-muted">Update your account password</small>
                                </div>
                            </div>
                            <div class="card-body">
                                <form id="passwordForm" action="<?= BASE_PATH ?>user/changePassword" method="POST">
                                    <div class="mb-4">
                                        <label for="currentPassword" class="form-label fw-semibold">Current Password</label>
                                        <div class="password-toggle">
                                        <input type="password" class="form-control form-control-lg" id="currentPassword" name="currentPassword" required>
                                            <button type="button" class="password-toggle-btn" onclick="toggleCurrentPassword()">
                                                <i class="bi bi-eye-fill" id="toggleCurrentPasswordIcon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="newPassword" class="form-label fw-semibold">New Password</label>
                                        <div class="password-toggle">
                                        <input type="password" class="form-control form-control-lg" id="newPassword" name="newPassword" required>
                                            <button type="button" class="password-toggle-btn" onclick="toggleNewPassword()">
                                                <i class="bi bi-eye-fill" id="toggleNewPasswordIcon"></i>
                                            </button>
                                        </div>
                                        <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>Password must be at least 8 characters long.</div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="confirmPassword" class="form-label fw-semibold">Confirm New Password</label>
                                        <div class="password-toggle">
                                        <input type="password" class="form-control form-control-lg" id="confirmPassword" name="confirmPassword" required>
                                            <button type="button" class="password-toggle-btn" onclick="toggleConfirmPassword()">
                                                <i class="bi bi-eye-fill" id="toggleConfirmPasswordIcon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary" style="background-color: #6f42c1; border: none; padding: 10px 24px; font-weight: 600;">Change Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Delete Account Section -->
                        <div class="card">
                            <div class="card-header d-flex align-items-center" style="background: linear-gradient(135deg, #fff5f5, #ffffff); border-bottom: 1px solid #f8d7da;">
                                <i class="bi bi-exclamation-triangle me-3" style="font-size: 1.5rem; color: #dc3545;"></i>
                                <div class="flex-grow-1">
                                    <h5 class="mb-0" style="color: #dc3545; font-weight: 600;">Delete Account</h5>
                                    <small class="text-muted">Permanently delete your account</small>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-4">Once you delete your account, you will not be able to recover it. This action cannot be undone.</p>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                        <i class="bi bi-trash me-2"></i>Delete Account
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Summary Sidebar -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header d-flex align-items-center" style="background: linear-gradient(135deg, #f6efff, #ffffff); border-bottom: 1px solid var(--sa-card-border);">
                                <i class="bi bi-info-circle me-3" style="font-size: 1.5rem; color: #6f42c1;"></i>
                                <div class="flex-grow-1">
                                    <h5 class="mb-0" style="color: #6f42c1; font-weight: 600;">Account Summary</h5>
                                </div>
                            </div>
                            <div class="card-body text-center">
                                <div class="mb-4">
                                    <div class="mb-3" style="width: 80px; height: 80px; margin: 0 auto; background: linear-gradient(135deg, #6f42c1, #5a32a3); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; font-weight: 600;">
                                        <?= strtoupper(substr(htmlspecialchars($user['username'] ?? 'U'), 0, 1)) ?>
                                    </div>
                                    <h5 class="mb-1"><?= htmlspecialchars($user['username'] ?? 'Username') ?></h5>
                                    <p class="text-muted mb-0"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($user['email'] ?? 'email@example.com') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
                <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h5 class="modal-title" id="deleteAccountModalLabel" style="font-weight: 600; color: #212529; font-size: 1.25rem;">Delete Account</h5>
                    <button type="button" class="modal-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="modal-body" style="padding: 20px 24px;">
                    <p class="mb-3">Are you sure you want to delete your account? This action cannot be undone.</p>
                    <p class="text-danger mb-0"><strong>Warning:</strong> All your data will be permanently deleted.</p>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 20px 24px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background-color: #e7d5ff; color: #6f42c1; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600;">Cancel</button>
                    <form action="<?= BASE_PATH ?>user/deleteAccount" method="POST" style="display: inline;">
                        <button type="submit" class="btn btn-danger">Delete Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        // Password toggle functions
        function toggleCurrentPassword() {
            const passwordInput = document.getElementById('currentPassword');
            const toggleIcon = document.getElementById('toggleCurrentPasswordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'bi bi-eye-slash-fill';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'bi bi-eye-fill';
            }
        }
        
        function toggleNewPassword() {
            const passwordInput = document.getElementById('newPassword');
            const toggleIcon = document.getElementById('toggleNewPasswordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'bi bi-eye-slash-fill';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'bi bi-eye-fill';
            }
        }
        
        function toggleConfirmPassword() {
            const passwordInput = document.getElementById('confirmPassword');
            const toggleIcon = document.getElementById('toggleConfirmPasswordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'bi bi-eye-slash-fill';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'bi bi-eye-fill';
            }
        }
        
        // Password validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword.length < 8) {
                e.preventDefault();
                showSnackbar('Password must be at least 8 characters long.', 'error');
                return false;
            }

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                showSnackbar('New password and confirm password do not match.', 'error');
                return false;
            }
        });

        // Profile form validation
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const username = document.getElementById('username').value;

            if (!email || !username) {
                e.preventDefault();
                showSnackbar('Please fill in all required fields.', 'error');
                return false;
            }

            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                showSnackbar('Please enter a valid email address.', 'error');
                return false;
            }
        });
        
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
    </script>
</body>
</html>

