<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Document - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        :root {
            --sa-primary: #6f42c1;
            --sa-primary-dark: #593093;
            --sa-accent: #e7d5ff;
            --sa-accent-strong: #d4b5ff;
            --sa-muted: #6c757d;
            --sa-card-border: #ede1ff;
        }

        .upload-container {
            background-color: #f8f9fa;
            padding: 30px;
        }

        h4[onclick] {
            transition: color 0.2s;
        }

        h4[onclick]:hover {
            color: var(--sa-primary) !important;
            text-decoration: underline;
        }

        .card {
            border: 1px solid var(--sa-card-border);
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(111, 66, 193, 0.08);
        }

        .card-header {
            background: linear-gradient(135deg, #f6efff, #ffffff);
            border-bottom: 1px solid var(--sa-card-border);
            color: var(--sa-primary);
            font-weight: 600;
        }

        .card-header h5 {
            color: inherit;
            font-weight: 600;
        }

        .btn-primary {
            background-color: var(--sa-primary) !important;
            border-color: var(--sa-primary) !important;
            box-shadow: 0 8px 18px rgba(111, 66, 193, 0.2);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--sa-primary-dark) !important;
            border-color: var(--sa-primary-dark) !important;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app/views/sidebar.php'; ?>
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
            <div class="container-fluid upload-container">
                <?php if (isset($documentData)): ?>
                    <?php require_once VIEW_NAVBAR; ?>

                    <div class="card mb-3 mt-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><?php echo htmlspecialchars($file['name'] ?? 'Document'); ?></h5>
                        </div>
                        <div class="card-body" style="padding: 3rem 5rem;">
                            <?php if ($documentData['type'] === 'image'): ?>
                                <img src="<?php echo htmlspecialchars($documentData['content']); ?>" class="img-fluid" alt="Document Image">
                            <?php endif; ?>

                            <?php if (!empty($documentData['extracted_text'])): ?>
                                <p id="document-content"><?php echo htmlspecialchars($documentData['extracted_text']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
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
        const content = document.getElementById('document-content');
        if (content) {
            content.innerHTML = marked.parse(content.textContent);
        }
    });
</script>

</html>
