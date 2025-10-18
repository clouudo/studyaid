<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container">
        <header class="d-flex justify-content-between align-items-center">
        <div class="d-flex jusitfy-content-start">
            <h1>StudyAid</h1>
        </div>
        <div class="d-flex justify-content-end">
            <a class="btn btn-primary btn-lg mx-3" href="<?php echo LOGIN; ?>" role="button">Login</a>
            <a class="btn btn-success btn-lg" href="<?php echo REGISTER; ?>" role="button">Sign Up</a>
        </div>
        </header>
    </div>
    <div class="container">
        <div class="row justify-content-center align-items-center" style="height: 100vh;">
            <div class="col-md-8 text-center">
                <h1 class="display-4">Welcome to StudyAid!</h1>
                <p class="lead">Click or drag to upload document.</p>
            </div>
        </div>
    </div>
</body>
</html>