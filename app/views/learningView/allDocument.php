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
                <h3 class="mb-4">All Documents</h3>
                <div class="list-group">
                    <?php if (!empty($fileList)): ?>
                        <?php foreach ($fileList as $file): ?>
                            <a href="index.php?url=lm/displayDocument&fileID=<?php echo $file['fileID'] ?>" class="list-group-item list-group-item-action">
                                <strong><?php echo htmlspecialchars($file['name']); ?></strong><br>
                                <small class="text-muted"><?php echo 'Uploaded on ' . date('Y-m-d H:i:s', strtotime($file['uploadDate'])); ?></small>
                            </a>
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