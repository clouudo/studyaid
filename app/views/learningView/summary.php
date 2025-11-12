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
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
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
                    <!-- Saved Summaries -->
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Generated Summaries</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" id="summaryList">
                            <?php if ($summaryList): ?>
                                <?php foreach ($summaryList as $summary): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="flex-grow-1">
                                                <strong><?= htmlspecialchars($summary['title']) ?></strong><br>
                                                <small class="text-muted">Updated: <?= htmlspecialchars($summary['createdAt']) ?></small>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownSummaryActions<?php echo $summary['summaryID']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownSummaryActions<?php echo $summary['summaryID']; ?>">
                                                    <li><a class="dropdown-item view-summary-btn" href="#" data-bs-toggle="collapse" data-bs-target="#summaryContent-<?php echo $summary['summaryID']; ?>">View</a></li>
                                                    <li><a class="dropdown-item export-summary-btn" href="#" data-export-type="pdf" data-summary-id="<?= htmlspecialchars($summary['summaryID']) ?>" data-file-id="<?= htmlspecialchars($file['fileID']) ?>">Export as PDF</a></li>
                                                    <li><a class="dropdown-item export-summary-btn" href="#" data-export-type="docx" data-summary-id="<?= htmlspecialchars($summary['summaryID']) ?>" data-file-id="<?= htmlspecialchars($file['fileID']) ?>">Export as DOCX</a></li>
                                                    <li><a class="dropdown-item export-summary-btn" href="#" data-export-type="txt" data-summary-id="<?= htmlspecialchars($summary['summaryID']) ?>" data-file-id="<?= htmlspecialchars($file['fileID']) ?>">Export as TXT</a></li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <form method="POST" action="<?= SAVE_SUMMARY_AS_FILE ?>" style="display: inline;">
                                                            <input type="hidden" name="summary_id" value="<?= htmlspecialchars($summary['summaryID']) ?>">
                                                            <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                            <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Save as File</button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" action="<?= DELETE_SUMMARY ?>" style="display: inline;">
                                                            <input type="hidden" name="summary_id" value="<?= htmlspecialchars($summary['summaryID']) ?>">
                                                            <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                            <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Delete</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="collapse mt-2" id="summaryContent-<?php echo $summary['summaryID']; ?>">
                                            <div class="summaryContent border-top pt-2" style="white-space: pre-wrap;"><?php echo htmlspecialchars($summary['content']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <div class="list-group-item text-muted text-center">No generated summaries</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <style>
        /* Prevent dropdowns from being clipped by list container */
        .list-group-item { 
            overflow: visible; 
        }
        .dropdown-menu { 
            z-index: 1060; 
        }
    </style>
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

            // Handle export summary buttons (mirror note.php behavior)
            document.querySelectorAll('.export-summary-btn').forEach(function(btn) {
                btn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const exportType = this.dataset.exportType;
                    const summaryId = this.dataset.summaryId;
                    const fileId = this.dataset.fileId;

                    let exportUrl = '';
                    if (exportType === 'pdf') {
                        exportUrl = '<?= EXPORT_SUMMARY_PDF ?>';
                    } else if (exportType === 'docx') {
                        exportUrl = '<?= EXPORT_SUMMARY_DOCX ?>';
                    } else if (exportType === 'txt') {
                        exportUrl = '<?= EXPORT_SUMMARY_TXT ?>';
                    }
                    if (!exportUrl) {
                        alert('Invalid export type');
                        return;
                    }

                    try {
                        const formData = new FormData();
                        formData.append('summary_id', summaryId);
                        formData.append('file_id', fileId);

                        const response = await fetch(exportUrl, {
                            method: 'POST',
                            body: formData
                        });

                        const contentType = response.headers.get('content-type') || '';
                        if (contentType.includes('text/html')) {
                            const text = await response.text();
                            alert('Export failed. Please check if the summary exists and try again.');
                            console.error('Export error response:', text);
                            return;
                        }
                        if (!response.ok) {
                            throw new Error('Export failed: ' + response.statusText);
                        }

                        const blob = await response.blob();
                        if (blob.size === 0) {
                            throw new Error('Empty file received from server');
                        }

                        let extension = exportType;
                        let filename = 'summary_' + summaryId;
                        if (exportType === 'pdf' && !blob.type.includes('pdf') && !contentType.includes('pdf')) {
                            throw new Error('Invalid PDF file received');
                        }

                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = filename + '.' + extension;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                    } catch (error) {
                        console.error('Export error:', error);
                        alert('Error exporting summary: ' + error.message);
                    }
                });
            });
        });
    </script>
</body>

</html>