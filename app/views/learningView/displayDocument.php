<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Document - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include VIEW_SIDEBAR; ?>
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
            <div class="container">
                <?php if (isset($documentData)): ?>
                    <h3 class="mb-4"><?php echo $file['name'] ?></h3>
                    <?php require_once VIEW_NAVBAR; ?>

                    <?php if ($documentData['type'] === 'image'): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <img src="<?php echo htmlspecialchars($documentData['content']); ?>" class="img-fluid" alt="Document Image">
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($documentData['extracted_text'])): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <p><?php echo htmlspecialchars($documentData['extracted_text']); ?></p>
                            </div>
                        </div>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('p').forEach(function(p) {
            p.innerHTML = marked.parse(p.textContent);
        });
    });
</script>

</html>