<?php
function buildFolderTree($folders, $parentId = null)
{
    $html = '<ul>';
    foreach ($folders as $folder) {
        if ($folder['parentFolderId'] == $parentId) {
            $html .= '<li>';
            $html .= '<a href="#" class="folder-item" data-folder-id="' . $folder['folderID'] . '" data-folder-name="' . htmlspecialchars($folder['name']) . '">' . htmlspecialchars($folder['name']) . '</a>';
            $html .= buildFolderTree($folders, $folder['folderID']);
            $html .= '</li>';
        }
    }
    $html .= '</ul>';
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
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <style>
        .folder-item {
            text-decoration: none;
        }
        .file-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
        }
        .file-info {
            flex: 1;
            min-width: 0;
        }
        .file-name {
            font-weight: 500;
            word-break: break-word;
            margin-bottom: 0.25rem;
        }
        .file-size {
            font-size: 0.875rem;
            color: #6c757d;
        }
        #dropZone {
            transition: background-color 0.2s;
            position: relative;
        }
        #dropZone:not(.has-files) {
            min-height: 200px;
        }
        .drop-zone-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        #dropZone.has-files {
            min-height: 150px;
            height: auto;
            justify-content: flex-start;
            padding-top: 1rem;
        }
        #dropZone.has-files .drop-zone-content {
            justify-content: flex-start;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app/views/sidebar.php'; ?>
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
            <div class="container">
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
                <h3 class="mb-4">Upload Documents</h3>
                <form action="<?= BASE_PATH ?>lm/uploadDocument" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="folderSelect" id="folderSelect">
                    <div class="mb-3">
                        <label class="form-label">Add to Folder</label>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#selectFolderModal" style="background-color: #A855F7;">
                                <i class="bi bi-folder-fill"></i>
                                Select Folder
                            </button>
                            <span id="selectedFolderName" class="ms-2"></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="dragDropArea" class="form-label">Drag and Drop Documents</label>
                        <div id="dragDropArea" class="border rounded p-5" style="background-color: #d4b5ff;">
                            <div id="dropZone" class="text-center d-flex flex-column justify-content-center align-items-center" style="min-height: 200px; cursor: pointer;" onclick="document.getElementById('documentFile').click();">
                                <div class="drop-zone-content">
                                    <span id="dropZoneText" class="d-block">Drag and drop your files here or click to upload (Multiple files supported)</span>
                                    <p class="mt-3 mb-0">Or</p>
                                    <button type="button" class="btn btn-outline-primary mt-2" onclick="event.stopPropagation(); document.getElementById('documentFile').click();">Browse Files</button>
                                </div>
                                <input type="file" id="documentFile" name="document[]" style="display: none;" accept="image/*,.pdf,.txt,.doc,.docx" multiple>
                            </div>
                            <div id="fileListContainer" class="mt-4" style="display: none;">
                                <h6 class="mb-3">Selected Files:</h6>
                                <div id="fileList" class="list-group"></div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="background-color: #A855F7; border: none;">Upload</button>
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
                </form>
            </div>
        </main>
    </div>

    <!-- Folder Select Modal -->
    <div class="modal fade" id="selectFolderModal" tabindex="-1" aria-labelledby="selectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectModalLabel">Select Folder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo buildFolderTree($allUserFolders); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        function updateFileList(files) {
            const fileListContainer = document.getElementById('fileListContainer');
            const fileList = document.getElementById('fileList');
            const dropZone = document.getElementById('dropZone');
            const dropZoneText = document.getElementById('dropZoneText');

            if (files && files.length > 0) {
                fileList.innerHTML = '';
                
                Array.from(files).forEach((file, index) => {
                    const listItem = document.createElement('div');
                    listItem.className = 'list-group-item file-list-item';
                    listItem.setAttribute('data-file-index', index);
                    listItem.innerHTML = `
                        <div class="file-info">
                            <div class="file-name">${file.name}</div>
                            <div class="file-size">${formatFileSize(file.size)}</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger ms-2 remove-file-btn" data-file-index="${index}" title="Remove file">
                            <i class="bi bi-x"></i>
                        </button>
                    `;
                    fileList.appendChild(listItem);
                });

                fileListContainer.style.display = 'block';
                dropZone.classList.add('has-files');
                dropZone.style.minHeight = '150px';
                dropZoneText.textContent = `${files.length} file(s) selected. Click to add more files.`;
            } else {
                fileListContainer.style.display = 'none';
                dropZone.classList.remove('has-files');
                dropZone.style.minHeight = '200px';
                dropZoneText.textContent = 'Drag and drop your files here or click to upload (Multiple files supported)';
            }
        }

        function removeFile(index) {
            const fileInput = document.getElementById('documentFile');
            const files = Array.from(fileInput.files);
            
            if (index >= 0 && index < files.length) {
                files.splice(index, 1);
                
                // Create a new FileList-like object
                const dt = new DataTransfer();
                files.forEach(file => dt.items.add(file));
                fileInput.files = dt.files;
                
                // Update display
                updateFileList(fileInput.files);
            }
        }

        // Use event delegation for remove buttons
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-file-btn')) {
                const btn = e.target.closest('.remove-file-btn');
                const index = parseInt(btn.getAttribute('data-file-index'));
                removeFile(index);
            }
        });

        document.getElementById('documentFile').addEventListener('change', function() {
            updateFileList(this.files);
        });

        // Handle drag and drop
        const dragDropArea = document.getElementById('dragDropArea');
        const dropZone = document.getElementById('dropZone');
        
        dragDropArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.style.backgroundColor = '#e7d5ff';
        });

        dragDropArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (!dragDropArea.contains(e.relatedTarget)) {
                dropZone.style.backgroundColor = '';
            }
        });

        dragDropArea.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropZone.style.backgroundColor = '';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const fileInput = document.getElementById('documentFile');
                
                // Merge with existing files
                const existingFiles = Array.from(fileInput.files);
                const newFiles = Array.from(files);
                const allFiles = [...existingFiles, ...newFiles];
                
                // Create a new FileList-like object
                const dt = new DataTransfer();
                allFiles.forEach(file => dt.items.add(file));
                fileInput.files = dt.files;
                
                // Update display
                updateFileList(fileInput.files);
            }
        });

        // Prevent default drag behaviors on the entire area
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dragDropArea.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        $(document).ready(function() {
            var selectFolderModal = new bootstrap.Modal(document.getElementById('selectFolderModal'));
            $('.folder-item').on('click', function(e) {
                e.preventDefault();
                var folderId = $(this).data('folder-id');
                var folderName = $(this).data('folder-name');
                $('#folderSelect').val(folderId);
                $('#selectedFolderName').text(folderName);
                selectFolderModal.hide();
            });
        });
    </script>
</body>

</html>