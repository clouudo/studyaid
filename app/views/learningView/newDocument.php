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
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app\\views\\sidebar.php'; ?>
        <main class="flex-grow-1 p-3">
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
                <h3 class="mb-4">Upload Document</h3>
                <form action="<?= BASE_PATH ?>lm/uploadDocument" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="folderSelect" id="folderSelect">
                    <div class="mb-3">
                        <label for="documentName" class="form-label">Document Name</label>
                        <input type="text" class="form-control" id="documentName" name="documentName" placeholder="Enter document name">
                    </div>
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
                        <label for="dragDropArea" class="form-label">Drag and Drop Document</label>
                        <div id="dragDropArea" class="border rounded p-5 text-center bg-light d-flex flex-column justify-content-center align-items-center" style="min-height: 550px; cursor: pointer;" onclick="document.getElementById('documentFile').click();">
                            <span id="fileNameDisplay">Drag and drop your files here or click to upload</span>
                            <input type="file" id="documentFile" name="document" style="display: none;" accept="image/*,.pdf,.txt,.doc,.docx">
                            <p class="mt-3">Or</p>
                            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('documentFile').click();">Browse Files</button>
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
        document.getElementById('documentFile').addEventListener('change', function() {
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            if (this.files && this.files.length > 0) {
                fileNameDisplay.textContent = this.files[0].name;
            } else {
                fileNameDisplay.textContent = 'Drag and drop your files here or click to upload';
            }
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