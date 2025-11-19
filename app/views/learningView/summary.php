<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extracted Text - StudyAid</title>
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

        .list-group-item {
            border-color: rgba(237, 225, 255, 0.8);
        }

        .list-group-item strong {
            color: #212529;
        }

        /* Snackbar Styles */
        .snackbar {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background-color: #333;
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            opacity: 0;
            transition: all 0.3s ease;
            min-width: 300px;
            max-width: 500px;
        }
        .snackbar.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        .snackbar.success {
            background-color: #28a745;
        }
        .snackbar.error {
            background-color: #dc3545;
        }
        .snackbar-icon {
            font-size: 1.2rem;
        }
        .snackbar-message {
            flex: 1;
            font-size: 0.95rem;
        .action-btn {
            background-color: transparent;
            border: none;
            color: #6c757d;
            padding: 8px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-btn:hover {
            background-color: #e7d5ff;
            color: #6f42c1;
        }

        /* Dropdown menu styling */
        main .dropdown-menu,
        .upload-container .dropdown-menu {
            position: absolute !important;
            inset: auto auto auto auto !important;
            top: calc(100% + 8px) !important;
            right: 0 !important;
            left: auto !important;
            margin: 0 !important;
            border-radius: 12px !important;
            border: 1px solid #d4b5ff !important;
            box-shadow: 0 10px 24px rgba(90, 50, 163, 0.12) !important;
            background-color: #ffffff !important;
            min-width: 180px !important;
            width: 180px !important;
            max-width: 180px !important;
            padding: 8px 0 !important;
            overflow: hidden !important;
            transform: none !important;
            z-index: 2147483647 !important;
        }

        .dropdown-menu li {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .dropdown-menu li + li {
            border-top: 1px solid #f0e6ff;
        }

        .list-group-item .dropdown.show .dropdown-menu {
            z-index: 2147483647 !important;
            width: 180px !important;
            min-width: 180px !important;
            max-width: 180px !important;
        }

        .dropdown {
            position: relative;
            z-index: 2147483646;
        }

        .dropdown.show {
            z-index: 2147483646 !important;
        }

        main .dropdown.show .dropdown-menu,
        .upload-container .dropdown.show .dropdown-menu {
            z-index: 2147483647 !important;
            display: block !important;
            position: absolute !important;
            top: calc(100% + 8px) !important;
            right: 0 !important;
            left: auto !important;
            transform: none !important;
            width: 180px !important;
            min-width: 180px !important;
            max-width: 180px !important;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <!-- Snackbar Container -->
    <div id="snackbar" class="snackbar">
        <i class="snackbar-icon" id="snackbarIcon"></i>
        <span class="snackbar-message" id="snackbarMessage"></span>
    </div>
    <div class="d-flex flex-grow-1">
        <?php include 'app/views/sidebar.php'; ?>
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
            <div class="container-fluid upload-container">
                <h3 style="color: #212529; font-size: 1.5rem; font-weight: 600; margin-bottom: 30px;">Summary</h3>
                <form method="POST" action="<?= DISPLAY_DOCUMENT ?>" style="display: inline;">
                    <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
                    <h4 style="color: #212529; font-size: 1.25rem; font-weight: 500; margin-bottom: 20px; cursor: pointer; display: inline-block;" onclick="this.closest('form').submit();"><?php echo htmlspecialchars($file['name']); ?></h4>
                </form>
                <?php require_once VIEW_NAVBAR; ?>
                <div class="card">
                    <div class="card-body">
                        <form id="generateSummaryForm" action="<?= GENERATE_SUMMARY ?>" method="POST" data-action="<?= GENERATE_SUMMARY ?>">
                            <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
                            <label for="instructions" class="form-label">Instructions (optional)</label>
                            <input type="text" class="form-control mb-3" id="instructions" name="instructions" placeholder="Describe your instructions">
                            <button type="submit" id="genSummary" class="btn btn-primary">Generate Summary</button>
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
                                                <small class="text-muted">Created: <?= htmlspecialchars($summary['createdAt'] ?? '') ?></small>
                                            </div>
                                            <div class="dropdown">
                                                <button class="action-btn"
                                                    type="button"
                                                    id="dropdownSummaryActions<?php echo $summary['summaryID']; ?>"
                                                    data-bs-toggle="dropdown"
                                                    data-bs-display="static"
                                                    aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownSummaryActions<?php echo $summary['summaryID']; ?>">
                                                    <li><a class="dropdown-item view-summary-btn" href="#" data-bs-toggle="collapse" data-bs-target="#summaryContent-<?php echo $summary['summaryID']; ?>">View</a></li>
                                                    <li><a class="dropdown-item audio-summary-btn" href="#" data-summary-id="<?= htmlspecialchars($summary['summaryID']) ?>">
                                                        <i class="bi bi-volume-up me-2"></i>Listen to Audio
                                                    </a></li>
                                                    <li><a class="dropdown-item export-summary-btn" href="#" data-export-type="pdf" data-summary-id="<?= htmlspecialchars($summary['summaryID']) ?>" data-file-id="<?= htmlspecialchars($file['fileID']) ?>">Export as PDF</a></li>
                                                    <li><a class="dropdown-item export-summary-btn" href="#" data-export-type="docx" data-summary-id="<?= htmlspecialchars($summary['summaryID']) ?>" data-file-id="<?= htmlspecialchars($file['fileID']) ?>">Export as DOCX</a></li>
                                                    <li><a class="dropdown-item export-summary-btn" href="#" data-export-type="txt" data-summary-id="<?= htmlspecialchars($summary['summaryID']) ?>" data-file-id="<?= htmlspecialchars($file['fileID']) ?>">Export as TXT</a></li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <form method="POST" action="<?= SAVE_SUMMARY_AS_FILE ?>" style="display: inline;" class="save-summary-as-file-form" data-summary-id="<?= htmlspecialchars($summary['summaryID']) ?>" data-summary-title="<?= htmlspecialchars($summary['title']) ?>">
                                                            <input type="hidden" name="summary_id" value="<?= htmlspecialchars($summary['summaryID']) ?>">
                                                            <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                            <button type="button" class="dropdown-item save-summary-as-file-btn" style="border: none; background: none; width: 100%; text-align: left;">Save as File</button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" action="<?= DELETE_SUMMARY ?>" style="display: inline;" class="delete-summary-form" data-summary-id="<?= htmlspecialchars($summary['summaryID']) ?>" data-file-id="<?= htmlspecialchars($file['fileID']) ?>">
                                                            <input type="hidden" name="summary_id" value="<?= htmlspecialchars($summary['summaryID']) ?>">
                                                            <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                            <button type="button" class="dropdown-item delete-summary-btn" style="border: none; background: none; width: 100%; text-align: left;">Delete</button>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        // Snackbar function
        function showSnackbar(message, type) {
            const snackbar = document.getElementById('snackbar');
            const snackbarMessage = document.getElementById('snackbarMessage');
            const snackbarIcon = document.getElementById('snackbarIcon');
            
            snackbarMessage.textContent = message;
            snackbar.className = 'snackbar ' + type;
            
            if (type === 'success') {
                snackbarIcon.className = 'snackbar-icon bi bi-check-circle-fill';
            } else if (type === 'error') {
                snackbarIcon.className = 'snackbar-icon bi bi-x-circle-fill';
            }
            
            snackbar.classList.add('show');
            
            setTimeout(function() {
                snackbar.classList.remove('show');
            }, 3000);
        }

        /**
         * Delete summary handler
         * 
         * Behavior: Shows confirmation modal before deleting summary. On confirmation,
         * submits delete form.
         */
        $(document).on('click', '.delete-summary-btn', function(e){
            e.preventDefault();
            var $form = $(this).closest('.delete-summary-form');
            var summaryId = $form.data('summary-id');
            var summaryTitle = $form.closest('.list-group-item').find('strong').text();

            showConfirmModal({
                message: 'Are you sure you want to delete the summary "' + summaryTitle + '"? This action cannot be undone.',
                title: 'Delete Summary',
                confirmText: 'Delete',
                cancelText: 'Cancel',
                danger: true,
                onConfirm: function() {
                    $form.submit();
                }
            });
        });

        /**
         * Save summary as file handler
         * 
         * Behavior: Shows confirmation modal before saving summary as file. On confirmation,
         * submits the form to save the summary as a new file.
         */
        $(document).on('click', '.save-summary-as-file-btn', function(e){
            e.preventDefault();
            e.stopPropagation();
            var $form = $(this).closest('.save-summary-as-file-form');
            var summaryTitle = $form.data('summary-title');

            showConfirmModal({
                message: 'Are you sure you want to save the summary "' + summaryTitle + '" as a new file?',
                title: 'Save Summary as File',
                confirmText: 'Save',
                cancelText: 'Cancel',
                danger: false,
                onConfirm: function() {
                    $form.submit();
                }
            });
        });


        /**
         * Document ready handler - initializes summary page functionality
         * 
         * Behavior: Sets up form submission handlers, markdown parsing, and
         * event listeners for audio generation and export functionality.
         */
        document.addEventListener('DOMContentLoaded', function() {
            /**
             * Generate summary form submission handler
             * 
             * Behavior: Intercepts form submission, sends AJAX request to generateSummary
             * endpoint, shows loading state, and reloads page on success or shows error.
             */
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
                        // Get the action URL - use data-action attribute or form action
                        let actionUrl = form.getAttribute('data-action') || form.action;
                        
                        // Convert routing URL to index.php format for fetch
                        // GENERATE_SUMMARY outputs: /studyaid/lm/generateSummary
                        // Need to convert to: /studyaid/index.php?url=lm/generateSummary
                        if (actionUrl.includes('/lm/')) {
                            // Extract the route part (lm/generateSummary)
                            const routeMatch = actionUrl.match(/\/lm\/(.+)$/);
                            if (routeMatch) {
                                actionUrl = '<?= BASE_PATH ?>index.php?url=lm/' + routeMatch[1];
                            }
                        }
                        
                        const formData = new FormData(form);
                        
                        const res = await fetch(actionUrl, {
                            method: 'POST',
                            body: formData
                        });

                        if (!res.ok) {
                            const errorText = await res.text();
                            throw new Error('Network response was not ok: ' + res.status + ' - ' + errorText.substring(0, 100));
                        }

                        const json = await res.json();

                        if (json.success) {
                            showSnackbar('Summary generated successfully!', 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            showSnackbar(json.message || 'Failed to generate summary. Please try again.', 'error');
                            submitButton.disabled = false;
                            submitButton.textContent = originalButtonText;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showSnackbar('An error occurred while generating the summary. Please try again.', 'error');
                        submitButton.disabled = false;
                        submitButton.textContent = originalButtonText;
                    }
                });
            }

            /**
             * Parse markdown for summary content
             * 
             * Behavior: Converts markdown text in summary content divs to HTML
             * using the marked.js library.
             */
            document.querySelectorAll('.summaryContent').forEach(function(div) {
                div.innerHTML = marked.parse(div.textContent);
            });

            /**
             * Audio summary button handler
             * 
             * Behavior: Generates audio for summary via AJAX, creates Audio element,
             * and plays it. Shows loading state during generation.
             */
            // Handle audio summary buttons
            document.querySelectorAll('.audio-summary-btn').forEach(function(btn) {
                btn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const summaryId = this.dataset.summaryId;
                    const originalText = this.innerHTML;
                    
                    // Show loading state
                    this.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Generating...';
                    this.style.pointerEvents = 'none';

                    try {
                        const formData = new FormData();
                        formData.append('summary_id', summaryId);
                        formData.append('file_id', '<?= htmlspecialchars($file['fileID']) ?>');

                        const response = await fetch('<?= AUDIO_SUMMARY ?>', {
                            method: 'POST',
                            body: formData
                        });

                        const json = await response.json();

                        if (!json.success) {
                            throw new Error(json.message || 'Failed to generate audio');
                        }

                        // Create audio element and play
                        const audio = new Audio(json.audioUrl);
                        audio.play().catch(err => {
                            console.error('Error playing audio:', err);
                            alert('Error playing audio. Please check your browser settings.');
                        });

                        // Update button state
                        this.innerHTML = '<i class="bi bi-volume-up-fill me-2"></i>Playing...';
                        
                        // Reset button when audio ends
                        audio.addEventListener('ended', () => {
                            this.innerHTML = originalText;
                            this.style.pointerEvents = 'auto';
                        });

                        audio.addEventListener('error', () => {
                            this.innerHTML = originalText;
                            this.style.pointerEvents = 'auto';
                            alert('Error loading audio file.');
                        });

                    } catch (error) {
                        console.error('Audio error:', error);
                        alert('Error generating audio: ' + error.message);
                        this.innerHTML = originalText;
                        this.style.pointerEvents = 'auto';
                    }
                });
            });

            /**
             * Export summary button handler
             * 
             * Behavior: Exports summary as PDF, DOCX, or TXT file. Downloads file
             * via blob URL. Handles errors and validates response content type.
             */
            // Handle export summary buttons
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
                        showSnackbar('Invalid export type selected.', 'error');
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
                            showSnackbar('Export failed. Please check if the summary exists and try again.', 'error');
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
                        showSnackbar('Summary exported successfully!', 'success');
                    } catch (error) {
                        console.error('Export error:', error);
                        showSnackbar('Failed to export summary. Please try again.', 'error');
                    }
                });
            });
        });
    </script>
    <?php include VIEW_CONFIRM; ?>
</body>

</html>