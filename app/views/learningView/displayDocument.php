<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Content - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="/studyaid/app/public/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app/views/sidebar.php'; ?>
        <main class="flex-grow-1 p-3">
            <div class="container">
                <h3 class="mb-4">Document Content</h3>
                <?php if (isset($documentData)): ?>
                    <div class="card">
                        <div class="card-body">
                            <?php if ($documentData['type'] === 'image'): ?>
                                <img src="<?php echo htmlspecialchars($documentData['content']); ?>" class="img-fluid" alt="Document Image">
                            <?php elseif ($documentData['type'] === 'text'): ?>
                                <pre><?php echo htmlspecialchars($documentData['content']); ?></pre>
                            <?php else: ?>
                                <p>Unsupported document type.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <p>No document content to display.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>