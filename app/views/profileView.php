<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include VIEW_SIDEBAR; ?>
        <main class="flex-grow-1 p-3">
            <div class="container">
                <?php
                if (isset($_SESSION['message'])):
                ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php
                    unset($_SESSION['message']);
                endif;

                if (isset($_SESSION['error'])):
                ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php
                    unset($_SESSION['error']);
                endif;
                ?>

                <h3 class="mb-4" style="color: #A855F7;">Manage Profile</h3>

            <div class="row">
                    <!-- Profile Information Section -->
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header" style="background-color: #A855F7; color: white;">
                                <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Profile Information</h5>
                            </div>
                            <div class="card-body">
                                <form id="profileForm" action="<?= BASE_PATH ?>user/updateProfile" method="POST">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary" style="background-color: #A855F7; border: none;">
                                        <i class="bi bi-save me-2"></i>Update Profile
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Change Password Section -->
                        <div class="card">
                            <div class="card-header" style="background-color: #A855F7; color: white;">
                                <h5 class="mb-0"><i class="bi bi-key me-2"></i>Change Password</h5>
                            </div>
                            <div class="card-body">
                                <form id="passwordForm" action="<?= BASE_PATH ?>user/changePassword" method="POST">
                                    <div class="mb-3">
                                        <label for="currentPassword" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="newPassword" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                                        <div class="form-text">Password must be at least 8 characters long.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary" style="background-color: #A855F7; border: none;">
                                        <i class="bi bi-key-fill me-2"></i>Change Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Summary Sidebar -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header" style="background-color: #A855F7; color: white;">
                                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Account Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <h5><?= htmlspecialchars($user['username'] ?? 'Username') ?></h5>
                                    <p class="text-muted"><?= htmlspecialchars($user['email'] ?? 'email@example.com') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        // Password validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long.');
                return false;
            }

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New password and confirm password do not match.');
                return false;
            }
        });

        // Profile form validation
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const username = document.getElementById('username').value;

            if (!email || !username) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return false;
            }
        });
    </script>
</body>
</html>

