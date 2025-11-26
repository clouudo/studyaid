<?php

/**
 * Recursively builds a hierarchical folder tree HTML structure
 * 
 * Behavior: Traverses folders array and generates nested <ul><li> structure
 * for folders matching the parent ID. Recursively processes child folders
 * to create multi-level folder hierarchy.
 * 
 * @param array $folders Array of folder data with folderID and parentFolderId
 * @param int|null $parentId Parent folder ID to filter children (null for root)
 * @param int $level Current nesting level (for indentation, currently unused)
 * @return string HTML string containing nested folder list structure
 */
function buildFolderTree($folders, $parentId = null, $level = 0)
{
    $html = '';
    $hasItems = false;

    foreach ($folders as $folder) {
        if ($folder['parentFolderId'] == $parentId) {
            $hasItems = true;
            $html .= '<li>';
            $html .= '<a href="#" class="folder-item" data-folder-id="' . $folder['folderID'] . '" data-folder-name="' . htmlspecialchars($folder['name']) . '">';
            $html .= '<i class="bi bi-folder-fill me-2"></i>';
            $html .= htmlspecialchars($folder['name']);
            $html .= '</a>';
            $children = buildFolderTree($folders, $folder['folderID'], $level + 1);
            if ($children) {
                $html .= '<ul class="folder-list">' . $children . '</ul>';
            }
            $html .= '</li>';
        }
    }

    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Document - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .folder-item {
            text-decoration: none;
        }

        .upload-container {
            background-color: #f8f9fa;
            padding: 30px;
        }

        .form-input-theme {
            background-color: #e7d5ff;
            border: none;
            border-radius: 12px;
            padding: 12px 16px;
            color: #212529;
        }

        .form-input-theme:focus {
            background-color: #e7d5ff;
            border: 2px solid #6f42c1;
            box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
            color: #212529;
        }

        .form-input-theme::placeholder {
            color: #6c757d;
        }

        .folder-select-wrapper {
            position: relative;
        }

        .folder-select-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            pointer-events: none;
        }

        .drag-drop-area {
            background-color: #e7d5ff;
            border: 2px dashed #d4b5ff;
            border-radius: 16px;
            padding: 60px 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            min-height: 300px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .drag-drop-area:hover {
            background-color: #d4b5ff;
            border-color: #6f42c1;
        }

        .drag-drop-area.dragover {
            background-color: #d4b5ff;
            border-color: #6f42c1;
            border-style: solid;
        }

        .upload-icon {
            font-size: 4rem;
            color: #212529;
            margin-bottom: 20px;
        }

        .upload-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 10px;
        }

        .upload-formats {
            font-size: 0.9rem;
            color: #495057;
            margin-top: 10px;
        }

        .uploaded-file-item {
            background-color: #e7d5ff;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s;
        }

        .uploaded-file-item:hover {
            background-color: #d4b5ff;
        }

        .uploaded-file-item .file-info {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .uploaded-file-item .file-icon {
            font-size: 1.5rem;
            color: #212529;
        }

        .uploaded-file-item .file-name {
            color: #212529;
            font-weight: 500;
            word-break: break-word;
        }

        .remove-file-btn {
            background: none;
            border: none;
            color: #212529;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 4px 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            z-index: 10;
        }

        .remove-file-btn:hover {
            color: #dc3545;
        }

        /* Preview Modal Styles */
        .preview-modal .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .preview-modal .modal-header {
            border-bottom: 1px solid #e9ecef;
            padding: 20px 24px;
        }

        .preview-modal .modal-title {
            font-weight: 600;
            color: #212529;
            font-size: 1.25rem;
        }

        .preview-modal .modal-body {
            padding: 20px 24px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .preview-modal .preview-content {
            width: 100%;
            min-height: 400px;
        }

        .preview-modal .preview-content iframe {
            width: 100%;
            min-height: 600px;
            border: none;
        }

        .preview-modal .preview-content img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .preview-modal .preview-content pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            background-color: #f8f9fa;
            padding: 16px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn-cancel {
            background-color: #e7d5ff;
            border: none;
            color: #6f42c1;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-cancel:hover {
            background-color: #d4b5ff;
            color: #5a32a3;
        }

        .btn-create {
            background-color: #e7d5ff;
            border: none;
            color: #6f42c1;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-create:hover {
            background-color: #6f42c1;
            color: white;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .modal-close-btn {
            background-color: transparent;
            border: none;
            color: #6f42c1;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .modal-close-btn:hover {
            background-color: #6f42c1;
            color: white;
        }

        .folder-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .folder-list ul {
            list-style: none;
            padding-left: 24px;
            margin: 8px 0 0 0;
        }

        .folder-list li {
            margin-bottom: 2px;
        }

        .folder-item {
            display: block;
            padding: 12px 16px;
            color: #6f42c1;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .folder-item:hover {
            background-color: #e7d5ff;
            color: #5a32a3;
            text-decoration: none;
        }

        .folder-item:active {
            background-color: #d4b5ff;
        }

        .modal-header {
            border-bottom: 1px solid #e9ecef;
            padding: 20px 24px;
        }

        .modal-title {
            font-weight: 600;
            color: #212529;
            font-size: 1.25rem;
        }

        .modal-body {
            padding: 20px 24px;
            max-height: 400px;
            overflow-y: auto;
        }

        #fileList {
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 100%;
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
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 9999;
            min-width: 300px;
            max-width: 500px;
            display: flex;
            align-items: center;
            gap: 12px;
            opacity: 0;
            transition: all 0.3s ease-in-out;
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
    <div class="d-flex flex-grow-1">
        <?php include 'app/views/sidebar.php'; ?>
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
            <div class="container-fluid upload-container">
                <!-- Snackbar Container -->
                <div id="snackbar" class="snackbar">
                    <i class="snackbar-icon" id="snackbarIcon"></i>
                    <span class="snackbar-message" id="snackbarMessage"></span>
                </div>

                <?php
                // Extract and clear session messages for display
                // Behavior: Retrieves success/error messages from session, clears them
                // to prevent re-display on page refresh, and stores in local variables
                $successMessage = null;
                $errorMessage = null;
                if (isset($_SESSION['message'])) {
                    $successMessage = $_SESSION['message'];
                    unset($_SESSION['message']);
                }
                if (isset($_SESSION['error'])) {
                    $errorMessage = $_SESSION['error'];
                    unset($_SESSION['error']);
                }
                ?>
                <h3 style="color: #212529; font-size: 1.5rem; font-weight: 600; margin-bottom: 30px;">Upload Document</h3>
                <form action="<?= BASE_PATH ?>lm/uploadDocument" method="POST" enctype="multipart/form-data" id="uploadDocumentForm">
                    <input type="hidden" name="folderSelect" id="folderSelect">

                    <div class="form-row">
                        <div>
                            <label for="documentName" class="form-label fw-semibold">Document Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-input-theme" id="documentName" name="documentName" placeholder="Enter your document name here..." required>
                        </div>
                        <div>
                            <label for="folderSelectInput" class="form-label fw-semibold">Add to Folder</label>
                            <div class="folder-select-wrapper">
                                <input type="text" class="form-control form-input-theme" id="folderSelectInput" placeholder="Choose folder to add" readonly onclick="openFolderModal()">
                                <i class="bi bi-chevron-expand folder-select-icon"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div id="dragDropArea" class="drag-drop-area" onclick="document.getElementById('documentFile').click();">
                            <div id="dropZone" class="drop-zone-content">
                                <div class="upload-icon">📄</div>
                                <div class="upload-title">Click or drag to upload document</div>
                                <div class="upload-formats">Supported formats: PDF, DOCS, TXT, Images (JPG, PNG, GIF, BMP, WEBP, TIFF)</div>
                            </div>
                            <input type="file" id="documentFile" name="document[]" style="display: none;" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif,.bmp,.webp,.tiff,.tif" multiple>
                        </div>
                        <div id="fileListContainer" style="display: none; margin-top: 20px;">
                            <div id="fileList"></div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="button" class="btn btn-cancel" onclick="resetForm()">Reset</button>
                        <button type="submit" class="btn btn-create">Create</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- Folder Select Modal -->
    <div class="modal fade" id="selectFolderModal" tabindex="-1" aria-labelledby="selectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
                <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h5 class="modal-title" id="selectModalLabel">Select Folder</h5>
                    <button type="button" class="modal-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($allUserFolders)): ?>
                        <ul class="folder-list">
                            <?php echo buildFolderTree($allUserFolders); ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">No folders available. Create a folder first.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- File Preview Modal -->
    <div class="modal fade preview-modal" id="filePreviewModal" tabindex="-1" aria-labelledby="filePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filePreviewModalLabel">File Preview</h5>
                    <button type="button" class="modal-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="previewContent" class="preview-content">
                        <p class="text-center text-muted">Loading preview...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        /**
         * Formats file size in bytes to human-readable format
         * 
         * Behavior: Converts bytes to appropriate unit (Bytes, KB, MB, GB)
         * using base-1024 calculation. Returns formatted string with 2 decimal places.
         * 
         * @param {number} bytes File size in bytes
         * @returns {string} Formatted file size (e.g., "1.5 MB")
         */
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        /**
         * Updates the file list display with selected files
         * 
         * Behavior: Creates visual list items for each file with appropriate icon
         * based on file extension. Stores file metadata in data attributes for preview.
         * Shows/hides file list container based on file count. Each file item is
         * clickable for preview and has a remove button.
         * 
         * @param {FileList} files FileList object containing selected files
         */
        function updateFileList(files) {
            const fileListContainer = document.getElementById('fileListContainer');
            const fileList = document.getElementById('fileList');
            const dropZone = document.getElementById('dropZone');

            if (files && files.length > 0) {
                fileList.innerHTML = '';

                Array.from(files).forEach((file, index) => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'uploaded-file-item';
                    fileItem.setAttribute('data-file-index', index);

                    // Determine file icon based on extension
                    let fileIcon = '📄';
                    const ext = file.name.split('.').pop().toLowerCase();
                    if (ext === 'pdf') fileIcon = '📄';
                    else if (['doc', 'docx'].includes(ext)) fileIcon = '📝';
                    else if (ext === 'txt') fileIcon = '📄';
                    else if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'tif'].includes(ext)) fileIcon = '🖼️';

                    fileItem.innerHTML = `
                        <div class="file-info" data-file-index="${index}">
                            <span class="file-icon">${fileIcon}</span>
                            <span class="file-name">${file.name}</span>
                        </div>
                        <button type="button" class="remove-file-btn" data-file-index="${index}" title="Remove file">
                            <i class="bi bi-x"></i>
                        </button>
                    `;
                    // Store file metadata for preview functionality
                    fileItem.dataset.fileName = file.name;
                    fileItem.dataset.fileType = file.type;
                    fileItem.dataset.fileSize = file.size;
                    fileList.appendChild(fileItem);
                });

                fileListContainer.style.display = 'block';
                if (getDocumentSelection().mode === 'multiple') {
                    document.getElementById('documentName').disabled = true;
                }
            } else {
                fileListContainer.style.display = 'none';
            }
        }

        /**
         * Removes a file from the file input by index
         * 
         * Behavior: Removes file at specified index from FileList, creates new
         * DataTransfer object to rebuild FileList (since FileList is read-only),
         * and updates the visual file list display.
         * 
         * @param {number} index Zero-based index of file to remove
         */
        function removeFile(index) {
            const fileInput = document.getElementById('documentFile');
            const files = Array.from(fileInput.files);

            if (index >= 0 && index < files.length) {
                files.splice(index, 1);

                // Rebuild FileList using DataTransfer API (FileList is immutable)
                const dt = new DataTransfer();
                files.forEach(file => dt.items.add(file));
                fileInput.files = dt.files;

                // Refresh file list display
                updateFileList(fileInput.files);
            }
        }

        /**
         * Object URL management for file previews
         * 
         * Behavior: Stores current preview URL to prevent memory leaks.
         * Automatically revokes object URL when preview modal closes to free
         * browser memory. Clears preview content on modal close.
         */
        let currentPreviewUrl = null;

        // Cleanup: Revoke object URLs when preview modal closes to prevent memory leaks
        const previewModalElement = document.getElementById('filePreviewModal');
        if (previewModalElement) {
            previewModalElement.addEventListener('hidden.bs.modal', function() {
                if (currentPreviewUrl) {
                    URL.revokeObjectURL(currentPreviewUrl);
                    currentPreviewUrl = null;
                }
                // Clear preview content
                document.getElementById('previewContent').innerHTML = '';
            });
        }

        /**
         * Event delegation for file list interactions
         * 
         * Behavior: Handles clicks on dynamically created file items and remove buttons.
         * Uses event delegation to work with dynamically added elements. Prevents
         * preview from opening when clicking remove button.
         */
        document.addEventListener('click', function(e) {
            // Handle remove button clicks
            if (e.target.closest('.remove-file-btn')) {
                e.preventDefault();
                e.stopPropagation();
                const btn = e.target.closest('.remove-file-btn');
                const index = parseInt(btn.getAttribute('data-file-index'));
                removeFile(index);
                return;
            }

            // Handle file item clicks for preview (exclude remove button clicks)
            if (e.target.closest('.uploaded-file-item')) {
                const fileItem = e.target.closest('.uploaded-file-item');
                // Don't trigger preview if clicking the remove button
                if (!e.target.closest('.remove-file-btn')) {
                    const index = parseInt(fileItem.getAttribute('data-file-index'));
                    previewFile(index);
                }
            }
        });

        /**
         * Displays file preview in modal based on file type
         * 
         * Behavior: Opens modal and renders preview based on file extension:
         * - PDF: Uses object URL in iframe for native PDF viewer
         * - Images: Reads as data URL and displays as <img>
         * - Text: Reads as text and displays in <pre> with escaped HTML
         * - Other: Shows file info message (no preview available)
         * 
         * Automatically cleans up previous object URLs to prevent memory leaks.
         * 
         * @param {number} index Zero-based index of file to preview
         */
        function previewFile(index) {
            const fileInput = document.getElementById('documentFile');
            const files = fileInput.files;

            if (index >= 0 && index < files.length) {
                const file = files[index];
                const previewModal = new bootstrap.Modal(document.getElementById('filePreviewModal'));
                const previewContent = document.getElementById('previewContent');
                const modalTitle = document.getElementById('filePreviewModalLabel');

                modalTitle.textContent = file.name;
                previewContent.innerHTML = '<p class="text-center text-muted">Loading preview...</p>';
                previewModal.show();

                const reader = new FileReader();
                const fileExt = file.name.split('.').pop().toLowerCase();

                if (fileExt === 'pdf') {
                    // Preview PDF using object URL in iframe
                    // Clean up previous URL to prevent memory leaks
                    if (currentPreviewUrl) {
                        URL.revokeObjectURL(currentPreviewUrl);
                    }
                    currentPreviewUrl = URL.createObjectURL(file);
                    previewContent.innerHTML = `<iframe src="${currentPreviewUrl}"></iframe>`;
                } else if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExt)) {
                    // Preview images as data URL
                    reader.onload = function(e) {
                        previewContent.innerHTML = `<img src="${e.target.result}" alt="${file.name}">`;
                    };
                    reader.readAsDataURL(file);
                } else if (fileExt === 'txt') {
                    // Preview text files with HTML escaping
                    reader.onload = function(e) {
                        previewContent.innerHTML = `<pre>${escapeHtml(e.target.result)}</pre>`;
                    };
                    reader.readAsText(file);
                } else {
                    // Show file info for unsupported preview types
                    previewContent.innerHTML = `
                        <div class="text-center py-5">
                            <i class="bi bi-file-earmark" style="font-size: 4rem; color: #d4b5ff;"></i>
                            <p class="mt-3 text-muted">Preview not available for this file type.</p>
                            <p class="text-muted">File: ${escapeHtml(file.name)}</p>
                            <p class="text-muted">Size: ${formatFileSize(file.size)}</p>
                        </div>
                    `;
                }
            }
        }

        /**
         * Escapes HTML special characters to prevent XSS attacks
         * 
         * Behavior: Uses DOM textContent property to safely escape HTML characters,
         * then returns innerHTML which contains the escaped version.
         * 
         * @param {string} text Text to escape
         * @returns {string} HTML-escaped text
         */
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        /**
         * Displays temporary notification snackbar message
         * 
         * Behavior: Shows animated notification at bottom of screen with
         * appropriate icon based on type (success/error). Automatically hides
         * after 3 seconds. Updates icon and styling based on message type.
         * 
         * @param {string} message Message text to display
         * @param {string} type Message type: 'success' or 'error'
         */
        function showSnackbar(message, type) {
            const snackbar = document.getElementById('snackbar');
            const snackbarMessage = document.getElementById('snackbarMessage');
            const snackbarIcon = document.getElementById('snackbarIcon');

            snackbarMessage.textContent = message;
            snackbar.className = 'snackbar ' + type;

            // Set appropriate icon based on message type
            if (type === 'success') {
                snackbarIcon.className = 'snackbar-icon bi bi-check-circle-fill';
            } else if (type === 'error') {
                snackbarIcon.className = 'snackbar-icon bi bi-x-circle-fill';
            }

            snackbar.classList.add('show');

            // Auto-hide after 3 seconds
            setTimeout(function() {
                snackbar.classList.remove('show');
            }, 3000);
        }

        /**
         * Display session messages on page load
         * 
         * Behavior: Checks for success/error messages from PHP session and
         * displays them as snackbar notifications when DOM is ready.
         */
        <?php if ($successMessage): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showSnackbar('<?php echo addslashes($successMessage); ?>', 'success');
            });
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showSnackbar('<?php echo addslashes($errorMessage); ?>', 'error');
            });
        <?php endif; ?>

        /**
         * Form submission validation
         * 
         * Behavior: Validates form before submission:
         * - Document name is required and non-empty
         * - At least one file must be selected
         * - Document name length between 3-255 characters
         * 
         * Prevents form submission if validation fails and shows error snackbar.
         * Focuses on first invalid field for better UX.
         */
        document.getElementById('uploadDocumentForm').addEventListener('submit', function(e) {
            const documentName = document.getElementById('documentName').value.trim();
            const fileInput = document.getElementById('documentFile');
            const files = fileInput.files;

            if (getDocumentSelection().mode === 'single') {
                // Validate document name exists
                if (!documentName) {
                    e.preventDefault();
                    showSnackbar('Please enter a document name.', 'error');
                    document.getElementById('documentName').focus();
                    return false;
                }
            }

            // Validate at least one file selected
            if (!files || files.length === 0) {
                e.preventDefault();
                showSnackbar('Please select at least one file to upload.', 'error');
                return false;
            }

            // Validate no PPTX files are present
            const pptxFiles = [];
            Array.from(files).forEach(file => {
                const ext = file.name.split('.').pop().toLowerCase();
                if (ext === 'pptx') {
                    pptxFiles.push(file.name);
                }
            });
            
            if (pptxFiles.length > 0) {
                e.preventDefault();
                showSnackbar(`PPTX files are not supported. Please remove: ${pptxFiles.join(', ')}`, 'error');
                return false;
            }

            if (documentName.length > 255) {
                e.preventDefault();
                showSnackbar('Document name must be less than 255 characters.', 'error');
                document.getElementById('documentName').focus();
                return false;
            }
        });

        /**
         * Resets the entire upload form to initial state
         * 
         * Behavior: Clears all form fields including document name, folder selection,
         * and file input. Updates file list display to reflect empty state.
         */
        function resetForm() {
            // Clear document name field
            document.getElementById('documentName').value = '';

            // Clear folder selection (both hidden input and display field)
            document.getElementById('folderSelect').value = '';
            document.getElementById('folderSelectInput').value = '';

            // Clear file input and update visual file list
            const fileInput = document.getElementById('documentFile');
            fileInput.value = '';
            updateFileList(fileInput.files);
        }

        /**
         * Validates file type and rejects PPTX files
         * 
         * Behavior: Checks if any file has PPTX extension and removes it
         * from the file list, showing an error message to the user.
         * 
         * @param {FileList} files FileList object containing selected files
         * @returns {FileList} Filtered FileList without PPTX files
         */
        function validateFileTypes(files) {
            const fileInput = document.getElementById('documentFile');
            const validFiles = [];
            const pptxFiles = [];
            
            Array.from(files).forEach(file => {
                const ext = file.name.split('.').pop().toLowerCase();
                if (ext === 'pptx') {
                    pptxFiles.push(file.name);
                } else {
                    validFiles.push(file);
                }
            });
            
            if (pptxFiles.length > 0) {
                showSnackbar(`PPTX files are not supported. Removed: ${pptxFiles.join(', ')}`, 'error');
            }
            
            // Rebuild FileList with only valid files
            const dt = new DataTransfer();
            validFiles.forEach(file => dt.items.add(file));
            fileInput.files = dt.files;
            
            return fileInput.files;
        }

        /**
         * File input change handler
         * 
         * Behavior: Updates file list display when files are selected via
         * file input dialog (not drag-and-drop). Validates file types first.
         */
        document.getElementById('documentFile').addEventListener('change', function() {
            const validatedFiles = validateFileTypes(this.files);
            updateFileList(validatedFiles);
        });

        /**
         * Drag and drop file upload functionality
         * 
         * Behavior: Enables drag-and-drop file upload with visual feedback.
         * - dragover: Adds visual highlight class when files are dragged over
         * - dragleave: Removes highlight when drag leaves area (with boundary check)
         * - drop: Merges dropped files with existing selection and updates display
         * 
         * Prevents default browser drag behaviors to enable custom handling.
         */
        const dragDropArea = document.getElementById('dragDropArea');
        const dropZone = document.getElementById('dropZone');

        // Visual feedback when dragging over drop area
        dragDropArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dragDropArea.classList.add('dragover');
        });

        // Remove visual feedback when drag leaves area
        dragDropArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            // Only remove highlight if actually leaving the drop area (not entering child)
            if (!dragDropArea.contains(e.relatedTarget)) {
                dragDropArea.classList.remove('dragover');
            }
        });

        // Handle file drop: merge with existing files and update display
        dragDropArea.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dragDropArea.classList.remove('dragover');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const fileInput = document.getElementById('documentFile');

                // Merge dropped files with existing file selection
                const existingFiles = Array.from(fileInput.files);
                const newFiles = Array.from(files);
                const allFiles = [...existingFiles, ...newFiles];

                // Rebuild FileList using DataTransfer API
                const dt = new DataTransfer();
                allFiles.forEach(file => dt.items.add(file));
                fileInput.files = dt.files;

                // Validate file types and refresh file list display
                const validatedFiles = validateFileTypes(fileInput.files);
                updateFileList(validatedFiles);
            }
        });

        // Prevent default browser drag behaviors to enable custom handling
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dragDropArea.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        /**
         * Opens folder selection modal
         * 
         * Behavior: Displays modal with hierarchical folder tree for user
         * to select destination folder for uploaded documents.
         */
        function openFolderModal() {
            const modal = new bootstrap.Modal(document.getElementById('selectFolderModal'));
            modal.show();
        }

        /**
         * Folder selection handler
         * 
         * Behavior: When user clicks a folder in the modal, stores folder ID
         * in hidden input and displays folder name in visible input field.
         * Closes modal after selection. Uses jQuery for event handling.
         */
        $(document).ready(function() {
            var selectFolderModal = new bootstrap.Modal(document.getElementById('selectFolderModal'));
            $('.folder-item').on('click', function(e) {
                e.preventDefault();
                var folderId = $(this).data('folder-id');
                var folderName = $(this).data('folder-name');
                // Store selected folder ID in hidden input for form submission
                $('#folderSelect').val(folderId);
                // Display selected folder name in visible input field
                $('#folderSelectInput').val(folderName);
                selectFolderModal.hide();
            });
        });

        function getDocumentSelection() {
            const fileInput = document.getElementById('documentFile');
            const files = fileInput.files;

            if (!files || files.length === 0) {
                return {
                    count: 0,
                    mode: 'none'
                };
            }
            if (files.length === 1) {
                return {
                    count: 1,
                    mode: 'single'
                };
            }
            return {
                count: files.length,
                mode: 'multiple'
            };
        }
    </script>
</body>

</html>