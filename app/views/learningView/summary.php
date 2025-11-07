<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extracted Text - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app\views\sidebar.php'; ?>
        <main class="flex-grow-1 p-3">
            <div class="container">
                <h3 class="mb-4" style="color: #A855F7;">Summary</h3>
                <h4 class="mb-4"><?php echo $file['name']; ?></h4>
                <?php require_once 'app\views\learningView\navbar.php'; ?>
                <div class="card">
                    <div class="card-body">
                        <form action="<?= BASE_PATH ?>lm/generateSummary?fileID=<?php echo $_GET['fileID']; ?>" method="POST">
                            <label for="instructions" class="form-label">Instructions (optional)</label>
                            <input type="text" class="form-control mb-3" id="instructions" name="instructions" placeholder="Describe your instructions">
                            <button type="submit" id="genSummary" class="btn btn-primary" style="background-color: #A855F7; border: none;">Generate Summary</button>
                        </form>
                    </div>
                </div>
            <div class="mt-3">
                    <?php if ($summaryList): ?>
                        <?php foreach ($summaryList as $summary): ?>
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span><?php echo $summary['title'] . ' - ' . $summary['createdAt']; ?></span>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownSummaryActions<?php echo $summary['summaryID']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownSummaryActions<?php echo $summary['summaryID']; ?>">
                                                <li><a class="dropdown-item" href="<?= BASE_PATH ?>lm/exportSummaryAsPdf?summaryID=<?= htmlspecialchars($summary['summaryID']) ?>&fileID=<?= htmlspecialchars($file['fileID']) ?>">Export as PDF</a></li>
                                                <li><a class="dropdown-item" href="<?= BASE_PATH ?>lm/exportSummaryAsDocx?summaryID=<?= htmlspecialchars($summary['summaryID']) ?>&fileID=<?= htmlspecialchars($file['fileID']) ?>">Export as DOCX</a></li>
                                                <li><a class="dropdown-item" href="<?= BASE_PATH ?>lm/exportSummaryAsTxt?summaryID=<?= htmlspecialchars($summary['summaryID']) ?>&fileID=<?= htmlspecialchars($file['fileID']) ?>">Export as TXT</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item" href="<?= BASE_PATH ?>lm/deleteSummary?summaryID=<?= htmlspecialchars($summary['summaryID']) ?>&fileID=<?= htmlspecialchars($file['fileID']) ?>">Delete</a></li>
                                                <li><a class="dropdown-item" href="<?= BASE_PATH ?>lm/saveSummaryAsFile?summaryID=<?= htmlspecialchars($summary['summaryID']) ?>&fileID=<?= htmlspecialchars($file['fileID']) ?>">Save as File</a></li>
                                            </ul>
                                        </div>
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" aria-expanded="false" data-bs-target="#summaryContent-<?php echo $summary['summaryID']; ?>">
                                            <i class="bi bi-chevron-down"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body collapse" id="summaryContent-<?php echo $summary['summaryID']; ?>">
                                    <div class="summaryContent" style="white-space: pre-wrap;"><?php echo htmlspecialchars($summary['content']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        document.querySelector('form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form)
                });
                const json = await res.json();
                location.reload();
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.summaryContent').forEach(function(div) {
                div.innerHTML = marked.parse(div.textContent);
            });
        });
    </script>
</body>

</html>