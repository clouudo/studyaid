<?php
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
            min-height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
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
            font-size: 5rem;
            color: #6f42c1;
            margin-bottom: 20px;
        }
        .upload-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 10px;
        }
        .upload-formats {
            font-size: 0.9rem;
            color: #495057;
            margin-top: 10px;
        }
        .uploaded-file-display {
            background-color: #e7d5ff;
            border-radius: 12px;
            padding: 12px 16px;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 400px;
        }
        .uploaded-file-display .file-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .uploaded-file-display .file-icon {
            font-size: 1.5rem;
            color: #6f42c1;
        }
        .uploaded-file-display .file-name {
            color: #212529;
            font-weight: 500;
        }
        .remove-file-btn {
            background: none;
            border: none;
            color: #dc3545;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .remove-file-btn:hover {
            color: #c82333;
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
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app/views/sidebar.php'; ?>
        <main class="flex-grow-1 p-3">
            <div class="container-fluid upload-container">
                <?php
                if (isset($_SESSION['message'])):
                ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $_SESSION['message']; ?>
                    </div>
                <?php
                    unset($_SESSION['message']);
                endif;

                if (isset($_SESSION['error'])):
                ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $_SESSION['error']; ?>
                    </div>
                <?php
                    unset($_SESSION['error']);
                endif;
                ?>
                <h3 class="mb-4" style="color: #212529;">Upload Document</h3>
                <form action="<?= BASE_PATH ?>lm/uploadDocument" method="POST" enctype="multipart/form-data" id="uploadForm">
                    <input type="hidden" name="folderSelect" id="folderSelect">
                    
                    <div class="form-row">
                        <div>
                            <label for="documentName" class="form-label fw-semibold">Document Name</label>
                            <input type="text" class="form-control form-input-theme" id="documentName" name="documentName" placeholder="Enter your document name here...">
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
                            <div class="upload-icon">📄</div>
                            <div class="upload-title" id="uploadTitle">Click or drag to upload document</div>
                            <div class="upload-formats">Supported formats: PDF, DOCS, PPTX, TXT</div>
                            <input type="file" id="documentFile" name="document" style="display: none;" accept=".pdf,.doc,.docx,.pptx,.txt">
                        </div>
                        
                        <div id="uploadedFileDisplay" class="uploaded-file-display" style="display: none;">
                            <div class="file-info">
                                <i class="bi bi-file-earmark-pdf file-icon"></i>
                                <span class="file-name" id="displayFileName"></span>
                            </div>
                            <button type="button" class="remove-file-btn" id="removeFileBtn" onclick="removeFile()">
                                <i class="bi bi-x-circle"></i>
                            </button>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        const dragDropArea = document.getElementById('dragDropArea');
        const documentFile = document.getElementById('documentFile');
        const uploadTitle = document.getElementById('uploadTitle');
        const uploadedFileDisplay = document.getElementById('uploadedFileDisplay');
        const displayFileName = document.getElementById('displayFileName');

        // Handle file selection
        documentFile.addEventListener('change', function() {
            handleFileSelect(this.files);
        });

        // Drag and drop functionality
        dragDropArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dragDropArea.classList.add('dragover');
        });

        dragDropArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dragDropArea.classList.remove('dragover');
        });

        dragDropArea.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dragDropArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                // Create a new FileList-like object and assign to input
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(files[0]);
                documentFile.files = dataTransfer.files;
                handleFileSelect(files);
            }
        });

        function handleFileSelect(files) {
            if (files && files.length > 0) {
                const fileName = files[0].name;
                uploadTitle.textContent = fileName;
                displayFileName.textContent = fileName;
                uploadedFileDisplay.style.display = 'flex';
                
                // Change icon based on file type
                const fileIcon = uploadedFileDisplay.querySelector('.file-icon');
                const extension = fileName.split('.').pop().toLowerCase();
                fileIcon.className = 'bi file-icon';
                if (extension === 'pdf') {
                    fileIcon.classList.add('bi-file-earmark-pdf');
                } else if (['doc', 'docx'].includes(extension)) {
                    fileIcon.classList.add('bi-file-earmark-word');
                } else if (['ppt', 'pptx'].includes(extension)) {
                    fileIcon.classList.add('bi-file-earmark-ppt');
                } else {
                    fileIcon.classList.add('bi-file-earmark-text');
                }
            } else {
                uploadTitle.textContent = 'Click or drag to upload document';
                uploadedFileDisplay.style.display = 'none';
            }
        }

        function removeFile() {
            documentFile.value = '';
            uploadTitle.textContent = 'Click or drag to upload document';
            uploadedFileDisplay.style.display = 'none';
        }

        function resetForm() {
            // Reset document name
            document.getElementById('documentName').value = '';
            
            // Reset folder selection
            document.getElementById('folderSelect').value = '';
            document.getElementById('folderSelectInput').value = '';
            
            // Reset file upload
            documentFile.value = '';
            uploadTitle.textContent = 'Click or drag to upload document';
            uploadedFileDisplay.style.display = 'none';
        }

        // Folder selection
        function openFolderModal() {
            const modal = new bootstrap.Modal(document.getElementById('selectFolderModal'));
            modal.show();
        }

        $(document).ready(function() {
            var selectFolderModal = new bootstrap.Modal(document.getElementById('selectFolderModal'));
            $('.folder-item').on('click', function(e) {
                e.preventDefault();
                var folderId = $(this).data('folder-id');
                var folderName = $(this).data('folder-name');
                $('#folderSelect').val(folderId);
                $('#folderSelectInput').val(folderName);
                selectFolderModal.hide();
            });
        });
    </script>
</body>

</html>
