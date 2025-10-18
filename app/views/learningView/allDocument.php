<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Documents - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="/studyaid/app/public/css/style.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app/views/sidebar.php'; ?>
        <main class="flex-grow-1 p-3">
            <div class="container">
                <?php
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }

                if (isset($_SESSION['message'])):
                ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $_SESSION['message']; ?>
                    </div>
                <?php 
                    unset($_SESSION['message']);
                endif;

                if (isset($_SESSION['error'])):
                ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $_SESSION['error']; ?>
                    </div>
                <?php
                    unset($_SESSION['error']);
                endif;
                ?>
                <h3 class="mb-4">All Documents</h3>
                <div class="list-group">
                    <?php if (!empty($fileList)): ?>
                        <?php foreach ($fileList as $file): ?>
                            <div class="list-group-item list-group-item-action w-75">
                                <div class="row">
                                    <a class="col" href="index.php?url=lm/displayDocument&fileID=<?php echo $file['fileID'] ?>" style="text-decoration: none; color: inherit;">
                                        <strong><?php echo htmlspecialchars($file['name']); ?></strong><br>
                                        <small class="text-muted"><?php echo 'Uploaded on ' . date('Y-m-d', strtotime($file['uploadDate'])); ?></small>
                                    </a>
                                    <div class="col align-self-center text-end">
                                        <a href="index.php?url=lm/deleteDocument&fileID=<?php echo $file['fileID'] ?>" class="btn btn-outline-primary">Delete</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No documents found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

</html>