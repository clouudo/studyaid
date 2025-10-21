<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extracted Text - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="/studyaid/app/public/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app\views\sidebar.php'; ?>
        <main class="flex-grow-1 p-3">
            <div class="container">
                <?php if (isset($documentData)): ?>
                    <?php if ($documentData['type'] === 'image'): ?>
                        <h3 class="mb-4">Image Preview</h3>
                        <div class="card">
                            <div class="card-body">
                                <img src="<?php echo htmlspecialchars($documentData['content']); ?>" class="img-fluid" alt="Document Image">
                            </div>
                        </div>
                    <?php else: ?>
                        <h3 class="mb-4">Extracted Text</h3>
                        <?php if (!empty($documentData['extracted_text'])): ?>
                            <div class="card">
                                <div class="card-body">
                                    <pre><?php echo htmlspecialchars($documentData['extracted_text']); ?></pre>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <p>No text could be extracted from this document, or the document is empty.</p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <p>No document content to display.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>