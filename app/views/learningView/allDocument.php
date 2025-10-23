<?php ob_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Documents - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app\views\sidebar.php'; ?>
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

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?url=lm/displayLearningMaterials">Home</a></li>
                        <?php
                        if (isset($currentFolderPath) && is_array($currentFolderPath)) {
                            foreach ($currentFolderPath as $pathItem) {
                                echo '<li class="breadcrumb-item"><a href="index.php?url=lm/displayLearningMaterials&folder_id=' . htmlspecialchars($pathItem['id']) . '">' . htmlspecialchars($pathItem['name']) . '</a></li>';
                            }
                        }
                        ?>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($currentFolderName ?? 'Home'); ?></li>
                    </ol>
                </nav>
                <?php if (!empty($currentFolderName)): ?>
                    <h5 class="mb-4"><?php echo htmlspecialchars($currentFolderName); ?></h5>
                <?php endif; ?>

                <div class="list-group">
                    <?php if (!empty($fileList['folders']) || !empty($fileList['files'])): ?>
                        <?php foreach ($fileList['folders'] as $folder): ?>
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <a class="text-decoration-none text-dark flex-grow-1" href="index.php?url=lm/displayLearningMaterials&folder_id=<?php echo $folder['folderID'] ?>">
                                    <i class="bi bi-folder-fill me-2"></i>
                                    <strong><?php echo htmlspecialchars($folder['name']); ?></strong>
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownFolderActions<?php echo $folder['folderID']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownFolderActions<?php echo $folder['folderID']; ?>">
                                        <li><a class="dropdown-item rename-btn" href="#" data-bs-toggle="modal" data-bs-target="#renameModal" data-item-id="<?php echo $folder['folderID']; ?>" data-item-name="<?php echo htmlspecialchars($folder['name']); ?>" data-item-type="folder">Rename</a></li>
                                        <li><a class="dropdown-item" href="index.php?url=lm/deleteFolder&folderID=<?php echo $folder['folderID'] ?>">Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php foreach ($fileList['files'] as $file): ?>
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <a class="text-decoration-none text-dark flex-grow-1" href="index.php?url=lm/displayDocument&fileID=<?php echo $file['fileID'] ?>">
                                    <?php
                                    $fileIcon = 'bi-file-earmark'; // Default icon
                                    $fileTypeLower = strtolower($file['fileType']);
                                    if ($fileTypeLower == 'pdf') {
                                        $fileIcon = 'bi-file-earmark-pdf';
                                    } elseif ($fileTypeLower == 'doc' || $fileTypeLower == 'docx') {
                                        $fileIcon = 'bi-file-earmark-word';
                                    } elseif ($fileTypeLower == 'xls' || $fileTypeLower == 'xlsx') {
                                        $fileIcon = 'bi-file-earmark-excel';
                                    } elseif ($fileTypeLower == 'ppt' || $fileTypeLower == 'pptx') {
                                        $fileIcon = 'bi-file-earmark-ppt';
                                    } elseif ($fileTypeLower == 'jpg' || $fileTypeLower == 'jpeg' || $fileTypeLower == 'png' || $fileTypeLower == 'gif') {
                                        $fileIcon = 'bi-file-earmark-image';
                                    } elseif ($fileTypeLower == 'txt') {
                                        $fileIcon = 'bi-file-earmark-text';
                                    } elseif ($fileTypeLower == 'zip') {
                                        $fileIcon = 'bi-file-earmark-zip';
                                    }
                                    ?>
                                    <i class="bi <?php echo $fileIcon; ?> me-2"></i>
                                    <strong><?php echo htmlspecialchars($file['name']); ?></strong>
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownFileActions<?php echo $file['fileID']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownFileActions<?php echo $file['fileID']; ?>">
                                        <li><a class="dropdown-item" href="index.php?url=lm/displayDocument&fileID=<?php echo $file['fileID'] ?>">View</a></li>
                                        <li><a class="dropdown-item rename-btn" href="#" data-bs-toggle="modal" data-bs-target="#renameModal" data-item-id="<?php echo $file['fileID']; ?>" data-item-name="<?php echo htmlspecialchars($file['name']); ?>" data-item-type="file">Rename</a></li>
                                        <li><a class="dropdown-item" href="index.php?url=lm/deleteDocument&fileID=<?php echo $file['fileID'] ?>">Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>This folder is empty.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Generic Rename Modal -->
    <div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="renameModalLabel">Rename</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="renameForm">
                        <input type="hidden" id="renameItemId" name="itemId">
                        <div class="mb-3">
                            <label for="newItemName" class="form-label" id="renameModalItemNameLabel">Name</label>
                            <input type="text" class="form-control" id="newItemName" name="newName" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveRenameBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            var renameModal = document.getElementById('renameModal');
            var saveRenameBtn = document.getElementById('saveRenameBtn');

            renameModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var itemId = button.getAttribute('data-item-id');
                var itemName = button.getAttribute('data-item-name');
                var itemType = button.getAttribute('data-item-type');

                var modalTitle = renameModal.querySelector('.modal-title');
                var modalBodyInputName = renameModal.querySelector('.modal-body #newItemName');
                var modalBodyInputId = renameModal.querySelector('.modal-body #renameItemId');
                var modalBodyNameLabel = renameModal.querySelector('.modal-body #renameModalItemNameLabel');

                modalBodyInputId.value = itemId;
                modalBodyInputName.value = itemName;
                saveRenameBtn.setAttribute('data-item-type', itemType);

                if (itemType === 'folder') {
                    modalTitle.textContent = 'Rename Folder';
                    modalBodyNameLabel.textContent = 'Folder Name';
                } else if (itemType === 'file') {
                    modalTitle.textContent = 'Rename File';
                    modalBodyNameLabel.textContent = 'File Name';
                }
            });

                    $(saveRenameBtn).on('click', function() {

                        var itemId = $('#renameItemId').val();

                        var newName = $('#newItemName').val();

                        var itemType = this.getAttribute('data-item-type');

            

                        if (newName.trim() === '') {

                            alert('Name cannot be empty.');

                            return;

                        }

            

                        var url = '';

                        var data = {};

            

                        if (itemType === 'folder') {

                            url = 'index.php?url=lm/renameFolder';

                            data = { folderId: itemId, newName: newName };

                        } else if (itemType === 'file') {

                            url = 'index.php?url=lm/renameFile';

                            data = { fileId: itemId, newName: newName };

                        }

            

                        if (url === '') return;

            

                        $.ajax({

                            url: url,

                            type: 'POST',

                            data: data,

                            dataType: 'json',

                            success: function(response) {

                                if (response.success) {

                                    location.reload();

                                } else {

                                    alert('Error renaming ' + itemType + ': ' + response.message);

                                }

                            },

                            error: function() {

                                alert('An error occurred while communicating with the server.');

                            }

                        });

                    });

            

                    $('#renameForm').on('submit', function(event) {

                        event.preventDefault();

                        $('#saveRenameBtn').click();

                    });
        });
    </script>
</body>

</html>
<?php ob_end_flush(); ?>