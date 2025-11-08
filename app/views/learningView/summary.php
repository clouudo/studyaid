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
        <?php include VIEW_SIDEBAR; ?>
        <main class="flex-grow-1 p-3">
            <div class="container">
                <h3 class="mb-4" style="color: #A855F7;">Summary</h3>
                <h4 class="mb-4"><?php echo $file['name']; ?></h4>
                <?php require_once VIEW_NAVBAR; ?>
                <div class="card">
                    <div class="card-body">
                        <form id="generateSummaryForm" action="#" method="POST" data-action="<?= GENERATE_SUMMARY ?>">
                            <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
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
                                                <li>
                                                    <form method="POST" action="<?= EXPORT_SUMMARY_PDF ?>" style="display: inline;">
                                                        <input type="hidden" name="summary_id" value="<?= htmlspecialchars($summary['summaryID']) ?>">
                                                        <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                        <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Export as PDF</button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" action="<?= EXPORT_SUMMARY_DOCX ?>" style="display: inline;">
                                                        <input type="hidden" name="summary_id" value="<?= htmlspecialchars($summary['summaryID']) ?>">
                                                        <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                        <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Export as DOCX</button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" action="<?= EXPORT_SUMMARY_TXT ?>" style="display: inline;">
                                                        <input type="hidden" name="summary_id" value="<?= htmlspecialchars($summary['summaryID']) ?>">
                                                        <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                        <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Export as TXT</button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="<?= DELETE_SUMMARY ?>" style="display: inline;">
                                                        <input type="hidden" name="summary_id" value="<?= htmlspecialchars($summary['summaryID']) ?>">
                                                        <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                        <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Delete</button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" action="<?= SAVE_SUMMARY_AS_FILE ?>" style="display: inline;">
                                                        <input type="hidden" name="summary_id" value="<?= htmlspecialchars($summary['summaryID']) ?>">
                                                        <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                        <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Save as File</button>
                                                    </form>
                                                </li>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Handle generate summary form submission
            const generateSummaryForm = document.getElementById('generateSummaryForm');
            if (generateSummaryForm) {
                generateSummaryForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const form = e.target;
                    const submitButton = form.querySelector('#genSummary');
                    const originalButtonText = submitButton.textContent;
                    
                    // Disable button and show loading state
                    submitButton.disabled = true;
                    submitButton.textContent = 'Generating...';
                    
                    try {
                        const actionUrl = form.getAttribute('data-action') || form.action;
                        const res = await fetch(actionUrl, {
                            method: 'POST',
                            body: new FormData(form)
                        });
                        
                        if (!res.ok) {
                            throw new Error('Network response was not ok');
                        }
                        
                        const json = await res.json();
                        
                        if (json.success) {
                            // Reload page to show new summary
                            location.reload();
                        } else {
                            alert('Error: ' + (json.message || 'Failed to generate summary'));
                            submitButton.disabled = false;
                            submitButton.textContent = originalButtonText;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error: ' + error.message);
                        submitButton.disabled = false;
                        submitButton.textContent = originalButtonText;
                    }
                });
            }
            
            // Parse markdown for summary content
            document.querySelectorAll('.summaryContent').forEach(function(div) {
                div.innerHTML = marked.parse(div.textContent);
            });
        });
    </script>
</body>

</html>