<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extracted Text - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.7/dist/purify.min.js"></script>
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <style>
        .upload-container {
            background-color: #f8f9fa;
            padding: 30px;
        }

        /* Split Screen Layout for Note Editor */
        .note-split-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            min-height: 500px;
        }

        .note-split-container.single-view {
            grid-template-columns: 1fr;
        }

        .note-editor-panel {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
        }

        .note-preview-panel {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            background-color: #ffffff;
            overflow-y: auto;
            padding: 1rem;
        }

        #noteSplitEditor {
            flex: 1;
            resize: none;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            border: none;
            padding: 1rem;
            outline: none;
        }

        .note-editor-header {
            padding: 0.75rem 1rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .note-editor-footer {
            padding: 0.5rem 1rem;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            font-size: 0.875rem;
            color: #6c757d;
        }

        .view-toggle-active {
            background-color: #A855F7 !important;
            color: white !important;
            border-color: #A855F7 !important;
        }

        .note-preview-panel h1, .note-preview-panel h2, .note-preview-panel h3,
        .note-preview-panel h4, .note-preview-panel h5, .note-preview-panel h6 {
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }

        .note-preview-panel p {
            margin-bottom: 0.75rem;
        }

        .note-preview-panel ul, .note-preview-panel ol {
            margin-bottom: 0.75rem;
            padding-left: 2rem;
        }

        .note-preview-panel code {
            background-color: #f8f9fa;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
            font-size: 0.875em;
        }

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

        .note-preview-panel pre {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.375rem;
            overflow-x: auto;
            margin-bottom: 0.75rem;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app/views/sidebar.php'; ?>
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
            <div class="container-fluid upload-container">
                <h3 style="color: #212529; font-size: 1.5rem; font-weight: 600; margin-bottom: 30px;">Note</h3>
                <h4 style="color: #212529; font-size: 1.25rem; font-weight: 500; margin-bottom: 20px;"><?php echo htmlspecialchars($file['name']); ?></h4>
                <?php require_once VIEW_NAVBAR; ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <form id="noteForm" action="<?= GENERATE_NOTES ?>" method="POST">
                            <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
                            <div class="mb-3">
                                <label class="form-label">Instructions (optional)</label>
                                <input type="text" class="form-control mb-3" id="instructions" name="instructions" placeholder="Describe your instructions">
                            </div>
                            <button type="submit" id="genNote" class="btn btn-primary" style="background-color: #A855F7; border: none;">Generate Note</button>
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
                            <button type="submit" class="btn btn-primary" style="background-color: #A855F7; border: none;">Save Note</button>
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
                                    <?php
                                        $encodedTitle = htmlspecialchars($note['title'], ENT_QUOTES, 'UTF-8');
                                        $encodedContent = htmlspecialchars(json_encode($note['content'], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <div class="list-group-item note-item"
                                        data-note-id="<?= htmlspecialchars($note['noteID']) ?>"
                                        data-note-title="<?= $encodedTitle ?>"
                                        data-note-content='<?= $encodedContent ?>'>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="flex-grow-1">
                                                <strong class="note-title-text"><?= htmlspecialchars($note['title']) ?></strong><br>
                                                <small class="text-muted note-updated-at">Created: <?= htmlspecialchars($note['createdAt'] ?? '') ?></small>
                                            </div>
                                            <div class="dropdown">
                                                <button class="action-btn"
                                                    type="button"
                                                    id="dropdownFileActions<?php echo $note['noteID']; ?>"
                                                    data-bs-toggle="dropdown"
                                                    data-bs-display="static"
                                                    aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownFileActions<?php echo $note['noteID']; ?>">
                                                    <li><a class="dropdown-item view-note-btn" href="#" data-bs-toggle="collapse" data-bs-target="#noteContent-<?php echo $note['noteID']; ?>">View</a></li>
                                                    <li><button type="button" class="dropdown-item edit-note-btn" data-note-id="<?= htmlspecialchars($note['noteID']) ?>">Edit inline</button></li>
                                                    <li><a class="dropdown-item audio-note-btn" href="#" data-note-id="<?= htmlspecialchars($note['noteID']) ?>">
                                                        <i class="bi bi-volume-up me-2"></i>Listen to Audio
                                                    </a></li>
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
                                            <div class="note-preview border-top pt-2" data-note-id="<?= htmlspecialchars($note['noteID']) ?>">
                                                <?php echo nl2br(htmlspecialchars($note['content'])); ?>
                                            </div>
                                            <div class="note-inline-editor d-none pt-3" data-note-id="<?= htmlspecialchars($note['noteID']) ?>">
                                                <!-- Split Screen Editor -->
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <button type="button" class="btn btn-outline-secondary" id="toggleNoteViewBtn-<?= htmlspecialchars($note['noteID']) ?>">Split View</button>
                                                            </div>
                                                            <div class="ms-auto d-flex gap-2 align-items-center">
                                                                <button type="button" class="btn btn-sm cancel-note-edit" style="background-color: #6c757d; border: none; color: white;">Cancel</button>
                                                                <button type="button" class="btn btn-sm save-note-edit" data-note-id="<?= htmlspecialchars($note['noteID']) ?>" style="background-color: #A855F7; border: none; color: white;">Save Changes</button>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label form-label-sm">Title</label>
                                                            <input type="text" class="form-control form-control-sm note-edit-title" value="<?= htmlspecialchars($note['title']) ?>">
                                                        </div>
                                                        <div id="note-split-container-<?= htmlspecialchars($note['noteID']) ?>" class="note-split-container">
                                                            <!-- Editor Panel -->
                                                            <div class="note-editor-panel">
                                                                <div class="note-editor-header">
                                                                    <h6 class="mb-0">Markdown Editor</h6>
                                                                </div>
                                                                <div class="btn-toolbar mb-2 px-2 pt-2" role="toolbar" id="toolbar-<?= htmlspecialchars($note['noteID']) ?>">
                                                                    <div class="btn-group">
                                                                        <button type="button" class="btn btn-outline-secondary btn-sm toolbar-undo" data-note-id="<?= htmlspecialchars($note['noteID']) ?>" title="Undo"><i class="bi bi-arrow-counterclockwise"></i></button>
                                                                        <button type="button" class="btn btn-outline-secondary btn-sm toolbar-redo" data-note-id="<?= htmlspecialchars($note['noteID']) ?>" title="Redo"><i class="bi bi-arrow-clockwise"></i></button>
                                                                        <button type="button" class="btn btn-outline-secondary btn-sm toolbar-bold" data-note-id="<?= htmlspecialchars($note['noteID']) ?>" title="Bold"><i class="bi bi-type-bold"></i></button>
                                                                        <button type="button" class="btn btn-outline-secondary btn-sm toolbar-italic" data-note-id="<?= htmlspecialchars($note['noteID']) ?>" title="Italic"><i class="bi bi-type-italic"></i></button>
                                                                        <button type="button" class="btn btn-outline-secondary btn-sm toolbar-heading" data-note-id="<?= htmlspecialchars($note['noteID']) ?>" title="Heading"><i class="bi bi-type-h1"></i></button>
                                                                        <button type="button" class="btn btn-outline-secondary btn-sm toolbar-ul" data-note-id="<?= htmlspecialchars($note['noteID']) ?>" title="Unordered List"><i class="bi bi-list-ul"></i></button>
                                                                        <button type="button" class="btn btn-outline-secondary btn-sm toolbar-ol" data-note-id="<?= htmlspecialchars($note['noteID']) ?>" title="Ordered List"><i class="bi bi-list-ol"></i></button>
                                                                    </div>
                                                                </div>
                                                                <textarea 
                                                                    id="noteSplitEditor-<?= htmlspecialchars($note['noteID']) ?>" 
                                                                    class="note-edit-content"
                                                                    placeholder="Enter your note content in markdown..."></textarea>
                                                                <div class="note-editor-footer">
                                                                    <small>Edit markdown to see real-time preview. Changes are saved when you click "Save Changes".</small>
                                                                </div>
                                                            </div>
                                                            <!-- Preview Panel -->
                                                            <div class="note-preview-panel" id="notePreviewPanel-<?= htmlspecialchars($note['noteID']) ?>">
                                                                <div class="text-muted">Preview will appear here...</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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
        const CURRENT_FILE_ID = '<?= htmlspecialchars($file['fileID']) ?>';

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
                    location.reload();
                } else {
                    alert("Title or content missing!");
                }
            } catch (error) {
                alert('Error: ' + error.message);
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
                    location.reload();
                } else {
                    alert('Error: ' + (json.message || 'Unknown error'));
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                }
            } catch (error) {
                alert('Error: ' + error.message);
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
            const manualNoteContent = document.getElementById('noteContent');
            if (manualNoteContent) {
                autoResizeTextarea(manualNoteContent);
                manualNoteContent.addEventListener('input', function() {
                    autoResizeTextarea(this);
                });
            }

            hydrateNotePreviews();
            bindInlineEditorInputs();

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
                        alert('Invalid export type');
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
                            alert('Export failed. Please check if the note exists and try again.');
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
                    } catch (error) {
                        console.error('Export error:', error);
                        alert('Error exporting note: ' + error.message);
                    }
                });
            });
        });

        function bindInlineEditorInputs() {
            document.querySelectorAll('.note-edit-content').forEach(function(textarea) {
                autoResizeTextarea(textarea);
                textarea.addEventListener('input', function() {
                    autoResizeTextarea(this);
                });
            });
        }

        function hydrateNotePreviews() {
            document.querySelectorAll('.note-item').forEach(function(item) {
                const preview = item.querySelector('.note-preview');
                if (!preview) return;
                const rawContent = getNoteDatasetContent(item);
                preview.innerHTML = marked.parse(rawContent || '');
            });
        }

        function getNoteDatasetContent(item) {
            if (!item || !item.dataset.noteContent) {
                return '';
            }
            try {
                return JSON.parse(item.dataset.noteContent);
            } catch (error) {
                return item.dataset.noteContent;
            }
        }

        function openNoteEditor(noteId) {
            const item = document.querySelector(`.note-item[data-note-id="${noteId}"]`);
            if (!item) return;

            const collapseTarget = document.getElementById(`noteContent-${noteId}`);
            if (collapseTarget && !collapseTarget.classList.contains('show') && window.bootstrap) {
                new bootstrap.Collapse(collapseTarget, {
                    toggle: false
                }).show();
            }

            const editor = item.querySelector('.note-inline-editor');
            const preview = item.querySelector('.note-preview');
            if (!editor) return;

            editor.classList.remove('d-none');
            if (preview) {
                preview.classList.add('d-none');
            }
            item.classList.add('editing');

            const titleInput = editor.querySelector('.note-edit-title');
            const contentInput = editor.querySelector(`#noteSplitEditor-${noteId}`);
            const previewPanel = document.getElementById(`notePreviewPanel-${noteId}`);
            const toggleBtn = document.getElementById(`toggleNoteViewBtn-${noteId}`);
            const splitContainer = document.getElementById(`note-split-container-${noteId}`);

            if (titleInput) {
                titleInput.value = item.dataset.noteTitle || '';
            }
            if (contentInput) {
                const content = getNoteDatasetContent(item);
                contentInput.value = content;
                autoResizeTextarea(contentInput);
                
                // Initial preview render
                if (previewPanel && typeof marked !== 'undefined') {
                    previewPanel.innerHTML = DOMPurify.sanitize(marked.parse(content || ''));
                }

                // Real-time preview update with debouncing
                let previewTimeout = null;
                contentInput.addEventListener('input', function() {
                    clearTimeout(previewTimeout);
                    previewTimeout = setTimeout(() => {
                        if (previewPanel && typeof marked !== 'undefined') {
                            const markdownText = contentInput.value || '';
                            previewPanel.innerHTML = DOMPurify.sanitize(marked.parse(markdownText));
                        }
                    }, 300); // 300ms debounce
                });
            }

            // Toggle split view (only add listener once)
            if (toggleBtn && splitContainer && previewPanel && !toggleBtn.dataset.listenerAdded) {
                toggleBtn.dataset.listenerAdded = 'true';
                let isSplitView = true;
                toggleBtn.addEventListener('click', function() {
                    isSplitView = !isSplitView;
                    if (isSplitView) {
                        splitContainer.classList.remove('single-view');
                        previewPanel.style.display = 'block';
                        toggleBtn.textContent = 'Editor Only';
                        toggleBtn.classList.add('view-toggle-active');
                    } else {
                        splitContainer.classList.add('single-view');
                        previewPanel.style.display = 'none';
                        toggleBtn.textContent = 'Split View';
                        toggleBtn.classList.remove('view-toggle-active');
                    }
                });
                // Initialize as split view
                splitContainer.classList.remove('single-view');
                previewPanel.style.display = 'block';
                toggleBtn.textContent = 'Editor Only';
                toggleBtn.classList.add('view-toggle-active');
            }

            if (contentInput) {
                contentInput.focus();
            }

            // Initialize toolbar functionality
            initializeNoteToolbar(noteId, contentInput);
        }

        // Function to initialize toolbar for note editor
        function initializeNoteToolbar(noteId, textarea) {
            if (!textarea) return;

            // Undo/Redo functionality (using browser's built-in undo/redo)
            const undoBtn = document.querySelector(`.toolbar-undo[data-note-id="${noteId}"]`);
            const redoBtn = document.querySelector(`.toolbar-redo[data-note-id="${noteId}"]`);

            if (undoBtn) {
                undoBtn.addEventListener('click', function() {
                    document.execCommand('undo', false, null);
                    textarea.focus();
                });
            }

            if (redoBtn) {
                redoBtn.addEventListener('click', function() {
                    document.execCommand('redo', false, null);
                    textarea.focus();
                });
            }

            // Bold functionality
            const boldBtn = document.querySelector(`.toolbar-bold[data-note-id="${noteId}"]`);
            if (boldBtn) {
                boldBtn.addEventListener('click', function() {
                    insertMarkdown(textarea, '**', '**', 'bold text');
                });
            }

            // Italic functionality
            const italicBtn = document.querySelector(`.toolbar-italic[data-note-id="${noteId}"]`);
            if (italicBtn) {
                italicBtn.addEventListener('click', function() {
                    insertMarkdown(textarea, '*', '*', 'italic text');
                });
            }

            // Heading functionality
            const headingBtn = document.querySelector(`.toolbar-heading[data-note-id="${noteId}"]`);
            if (headingBtn) {
                headingBtn.addEventListener('click', function() {
                    insertMarkdown(textarea, '## ', '', 'Heading');
                });
            }

            // Unordered list functionality
            const ulBtn = document.querySelector(`.toolbar-ul[data-note-id="${noteId}"]`);
            if (ulBtn) {
                ulBtn.addEventListener('click', function() {
                    insertMarkdown(textarea, '- ', '', 'List item');
                });
            }

            // Ordered list functionality
            const olBtn = document.querySelector(`.toolbar-ol[data-note-id="${noteId}"]`);
            if (olBtn) {
                olBtn.addEventListener('click', function() {
                    insertMarkdown(textarea, '1. ', '', 'List item');
                });
            }
        }

        // Function to insert markdown at cursor position
        function insertMarkdown(textarea, prefix, suffix, placeholder) {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            const textToInsert = selectedText || placeholder;
            
            const newText = textarea.value.substring(0, start) + 
                          prefix + textToInsert + suffix + 
                          textarea.value.substring(end);
            
            textarea.value = newText;
            
            // Set cursor position after inserted text
            const newCursorPos = start + prefix.length + textToInsert.length + suffix.length;
            textarea.setSelectionRange(newCursorPos, newCursorPos);
            textarea.focus();
            
            // Trigger input event to update preview
            textarea.dispatchEvent(new Event('input'));
        }

        function closeNoteEditor(item) {
            if (!item) return;
            const editor = item.querySelector('.note-inline-editor');
            const preview = item.querySelector('.note-preview');
            if (editor) {
                editor.classList.add('d-none');
            }
            if (preview) {
                preview.classList.remove('d-none');
            }
            item.classList.remove('editing');
        }

        async function handleSaveNote(button) {
            const item = button.closest('.note-item');
            if (!item) return;

            const noteId = button.dataset.noteId;
            const editor = item.querySelector('.note-inline-editor');
            const titleInput = editor.querySelector('.note-edit-title');
            // Try to get the split editor first, fallback to regular editor
            const contentInput = editor.querySelector(`#noteSplitEditor-${noteId}`) || editor.querySelector('.note-edit-content');

            const title = titleInput.value.trim();
            const content = contentInput.value.trim();

            if (!title || !content) {
                alert('Title and content are required.');
                return;
            }

            button.disabled = true;
            const originalText = button.textContent;
            button.textContent = 'Saving...';

            const formData = new FormData();
            formData.append('note_id', button.dataset.noteId);
            formData.append('file_id', CURRENT_FILE_ID);
            formData.append('noteTitle', title);
            formData.append('noteContent', content);

            try {
                const response = await fetch('<?= UPDATE_NOTE ?>', {
                    method: 'POST',
                    body: formData
                });
                const json = await response.json();
                if (!json.success) {
                    throw new Error(json.message || 'Failed to update note');
                }

                const updatedNote = json.note || {};
                const preview = item.querySelector('.note-preview');
                if (preview && typeof marked !== 'undefined') {
                    preview.innerHTML = DOMPurify.sanitize(marked.parse(updatedNote.content || content));
                }

                const titleText = item.querySelector('.note-title-text');
                if (titleText) {
                    titleText.textContent = updatedNote.title || title;
                }

                const updatedLabel = item.querySelector('.note-updated-at');
                if (updatedLabel && updatedNote.createdAt) {
                    updatedLabel.textContent = 'Created: ' + updatedNote.createdAt;
                }

                item.dataset.noteTitle = updatedNote.title || title;
                item.dataset.noteContent = JSON.stringify(updatedNote.content || content);

                button.textContent = originalText;
                button.disabled = false;
                closeNoteEditor(item);
            } catch (error) {
                alert('Error updating note: ' + error.message);
                button.textContent = originalText;
                button.disabled = false;
            }
        }

        document.addEventListener('click', function(e) {
            const editBtn = e.target.closest('.edit-note-btn');
            if (editBtn) {
                e.preventDefault();
                openNoteEditor(editBtn.dataset.noteId);
                return;
            }

            const cancelBtn = e.target.closest('.cancel-note-edit');
            if (cancelBtn) {
                const item = cancelBtn.closest('.note-item');
                closeNoteEditor(item);
                return;
            }

            const saveBtn = e.target.closest('.save-note-edit');
            if (saveBtn) {
                handleSaveNote(saveBtn);
            }
        });

        // Handle audio note buttons
        document.querySelectorAll('.audio-note-btn').forEach(function(btn) {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();

                const noteId = this.dataset.noteId;
                const originalText = this.innerHTML;
                
                // Show loading state
                this.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Generating...';
                this.style.pointerEvents = 'none';

                try {
                    const formData = new FormData();
                    formData.append('note_id', noteId);
                    formData.append('file_id', CURRENT_FILE_ID);

                    const response = await fetch('<?= AUDIO_NOTE ?>', {
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
                    console.error('Error:', error);
                    alert('Error: ' + error.message);
                    this.innerHTML = originalText;
                    this.style.pointerEvents = 'auto';
                }
            });
        });
    </script>
</body>

</html>