<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to StudyAid</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center" style="height: 100vh;">
            <div class="col-md-8 text-center">
                <h1 class="display-4">Welcome to StudyAid!</h1>
                <p class="lead">Your personal learning management system.</p>
                <hr class="my-4">
                <p>
                    <a class="btn btn-primary btn-lg" href="<?php echo LOGIN; ?>" role="button">Login</a>
                    <a class="btn btn-success btn-lg" href="<?php echo REGISTER; ?>" role="button">Sign Up</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>