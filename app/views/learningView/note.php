<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extracted Text - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.7/dist/purify.min.js"></script>
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
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
                <h3 style="color: #212529; font-size: 1.5rem; font-weight: 600; margin-bottom: 30px;">Note</h3>
                <form method="POST" action="<?= DISPLAY_DOCUMENT ?>" style="display: inline;">
                    <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
                    <h4 style="color: #212529; font-size: 1.25rem; font-weight: 500; margin-bottom: 20px; cursor: pointer; display: inline-block;" onclick="this.closest('form').submit();"><?php echo htmlspecialchars($file['name']); ?></h4>
                </form>
                <?php require_once VIEW_NAVBAR; ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <form id="noteForm" action="<?= GENERATE_NOTES ?>" method="POST">
                            <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
                            <div class="mb-3">
                                <label class="form-label">Instructions (optional)</label>
                                <input type="text" class="form-control mb-3" id="instructions" name="instructions" placeholder="Describe your instructions">
                            </div>
                            <button type="submit" id="genNote" class="btn btn-primary">Generate Note</button>
                        </form>
                    </div>
                </div>
                <div class="card mb-3">
                    <form id="noteEditor" action="<?= SAVE_NOTE ?>" method="POST">
                        <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
                        <div class="card-header">
                            <label for="noteTitle" class="form-label">Add Note</label>
                            <div class="d-flex justify-content-between align-items-center">
                                <input type="text" class="form-control" id="noteTitle" name="noteTitle" placeholder="Enter note title">
                                <a class="btn btn-sm me-2 btn-toggle" data-bs-toggle="collapse" aria-expanded="true" data-bs-target="#noteEditorPanel"><i class="bi bi-chevron-down"></i></a>
                            </div>
                        </div>
                        <div class="card-body collapse" id="noteEditorPanel">
                            <div class="btn-toolbar mb-2" role="toolbar" id="toolbar">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"><i class="bi bi-type-bold"></i></button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"><i class="bi bi-type-italic"></i></button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"><i class="bi bi-type-h1"></i></button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i></button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ol"></i></button>
                                </div>
                            </div>
                            <textarea class="form-control mb-3" id="noteContent" name="noteContent" placeholder="Enter note content" style="min-height:120px; overflow:hidden; resize:none;"></textarea>
                            <div id="preview" class="bg-light border px-2 py-2 mb-3" style="min-height:120px"></div>
                            <button type="submit" class="btn btn-primary">Save Note</button>
                        </div>
                    </form>
                </div>
                <!-- Saved Notes -->
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Generated Notes</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" id="noteList">
                            <?php if ($noteList): ?>
                                <?php foreach ($noteList as $note): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="flex-grow-1">
                                                <strong><?= htmlspecialchars($note['title']) ?></strong><br>
                                                <small class="text-muted">Updated: <?= htmlspecialchars($note['createdAt']) ?></small>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownFileActions<?php echo $note['noteID']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownFileActions<?php echo $note['noteID']; ?>">
                                                    <li><a class="dropdown-item view-note-btn" href="#" data-bs-toggle="collapse" data-bs-target="#noteContent-<?php echo $note['noteID']; ?>">View</a></li>
                                                    <li><a class="dropdown-item export-note-btn" href="#" data-export-type="pdf" data-note-id="<?= htmlspecialchars($note['noteID']) ?>" data-file-id="<?= htmlspecialchars($file['fileID']) ?>">Export as PDF</a></li>
                                                    <li><a class="dropdown-item export-note-btn" href="#" data-export-type="docx" data-note-id="<?= htmlspecialchars($note['noteID']) ?>" data-file-id="<?= htmlspecialchars($file['fileID']) ?>">Export as DOCX</a></li>
                                                    <li><a class="dropdown-item export-note-btn" href="#" data-export-type="txt" data-note-id="<?= htmlspecialchars($note['noteID']) ?>" data-file-id="<?= htmlspecialchars($file['fileID']) ?>">Export as TXT</a></li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <form method="POST" action="<?= SAVE_NOTE_AS_FILE ?>" style="display: inline;">
                                                            <input type="hidden" name="note_id" value="<?= htmlspecialchars($note['noteID']) ?>">
                                                            <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                            <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Save as File</button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" action="<?= DELETE_NOTE ?>" style="display: inline;">
                                                            <input type="hidden" name="note_id" value="<?= htmlspecialchars($note['noteID']) ?>">
                                                            <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                            <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Delete</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="collapse mt-2" id="noteContent-<?php echo $note['noteID']; ?>">
                                            <div class="noteText border-top pt-2"><?php echo htmlspecialchars($note['content']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <div class="list-group-item text-muted text-center">No generated notes</div>
                            <?php endif; ?>
                        </div>
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

        document.getElementById('noteEditor').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;

            try {
                const data = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: data
                });
                const json = await res.json();
                if (json.success) {
                    showSnackbar('Note saved successfully!', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showSnackbar(json.message || 'Please provide both title and content for the note.', 'error');
                }
            } catch (error) {
                showSnackbar('An error occurred while saving the note. Please try again.', 'error');
                console.error('Error:', error);
            }
        });

        document.getElementById('noteForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            const form = e.target;
            const submitButton = form.querySelector('#genNote');
            const originalButtonText = submitButton.textContent;

            // Disable button and show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'Generating...';

            try {
                const data = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: data
                });
                const json = await res.json();
                if (json.success) {
                    showSnackbar('Note generated successfully!', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showSnackbar(json.message || 'Failed to generate note. Please try again.', 'error');
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                }
            } catch (error) {
                showSnackbar('An error occurred while generating the note. Please try again.', 'error');
                console.error('Error:', error);
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            }
        })

        // Auto-resize textarea function
        function autoResizeTextarea(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const noteContent = document.getElementById('noteContent');
            if (noteContent) {
                // Initial resize
                autoResizeTextarea(noteContent);

                // Resize on input
                noteContent.addEventListener('input', function() {
                    autoResizeTextarea(this);
                });
            }

            document.querySelectorAll('.noteText')
                .forEach(function(div) {
                    div.innerHTML = marked.parse(div.textContent);
                });

            // Handle export note buttons
            document.querySelectorAll('.export-note-btn').forEach(function(btn) {
                btn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const exportType = this.dataset.exportType;
                    const noteId = this.dataset.noteId;
                    const fileId = this.dataset.fileId;

                    // Determine the export URL based on type
                    let exportUrl = '';
                    if (exportType === 'pdf') {
                        exportUrl = '<?= EXPORT_NOTE_PDF ?>';
                    } else if (exportType === 'docx') {
                        exportUrl = '<?= EXPORT_NOTE_DOCX ?>';
                    } else if (exportType === 'txt') {
                        exportUrl = '<?= EXPORT_NOTE_TXT ?>';
                    }

                    if (!exportUrl) {
                        showSnackbar('Invalid export type selected.', 'error');
                        return;
                    }

                    try {
                        // Create form data
                        const formData = new FormData();
                        formData.append('note_id', noteId);
                        formData.append('file_id', fileId);

                        // Fetch the file
                        const response = await fetch(exportUrl, {
                            method: 'POST',
                            body: formData
                        });

                        // Check if response is ok and has correct content type
                        const contentType = response.headers.get('content-type') || '';
                        
                        // If it's an HTML response (error redirect), handle it
                        if (contentType.includes('text/html')) {
                            const text = await response.text();
                            showSnackbar('Export failed. Please check if the note exists and try again.', 'error');
                            console.error('Export error response:', text);
                            return;
                        }

                        if (!response.ok) {
                            throw new Error('Export failed: ' + response.statusText);
                        }

                        // Get the blob from response
                        const blob = await response.blob();
                        
                        // Verify blob is not empty
                        if (blob.size === 0) {
                            throw new Error('Empty file received from server');
                        }
                        
                        // Determine file extension and MIME type
                        let extension = '';
                        let filename = 'note_' + noteId;
                        if (exportType === 'pdf') {
                            extension = 'pdf';
                            // Verify it's actually a PDF by checking blob type
                            if (!blob.type.includes('pdf') && !contentType.includes('pdf')) {
                                throw new Error('Invalid PDF file received');
                            }
                        } else if (exportType === 'docx') {
                            extension = 'docx';
                        } else if (exportType === 'txt') {
                            extension = 'txt';
                        }

                        // Create download link
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = filename + '.' + extension;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                        showSnackbar('Note exported successfully!', 'success');
                    } catch (error) {
                        console.error('Export error:', error);
                        showSnackbar('Failed to export note. Please try again.', 'error');
                    }
                });
            });
        })
    </script>
</body>

</html>