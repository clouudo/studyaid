<?php
function buildFolderTree($folders, $parentId = null) {
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
ob_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Documents - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
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

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASE_PATH ?>lm/displayLearningMaterials">Home</a></li>
                        <?php
                        if (isset($currentFolderPath) && is_array($currentFolderPath)) {
                            foreach ($currentFolderPath as $pathItem) {
                                echo '<li class="breadcrumb-item"><a href="' . BASE_PATH . 'lm/displayLearningMaterials?folder_id=' . htmlspecialchars($pathItem['id']) . '">' . htmlspecialchars($pathItem['name']) . '</a></li>';
                            }
                        }
                        ?>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($currentFolderName ?? 'Home'); ?></li>
                    </ol>
                </nav>
                <?php if (!empty($currentFolderName)): ?>
                    <h5 class="mb-4"><?php echo htmlspecialchars($currentFolderName); ?></h5>
                <?php endif; ?>

                <div class="mb-3">
                    <form action="index.php" method="get">
                        <div class="input-group">
                            <input type="hidden" name="url" value="lm/displayLearningMaterials">
                            <input type="text" class="form-control" placeholder="Search all documents..." name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            <button class="btn btn-outline-secondary" type="submit">Search</button>
                        </div>
                    </form>
                </div>

                <div class="list-group">
                    <?php if (!empty($fileList['folders']) || !empty($fileList['files'])): ?>
                        <?php foreach ($fileList['folders'] as $folder):
                        ?>
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <a class="text-decoration-none text-dark flex-grow-1" href="<?= BASE_PATH ?>lm/displayLearningMaterials?folder_id=<?php echo $folder['folderID'] ?>">
                                    <i class="bi bi-folder-fill me-2"></i>
                                    <strong><?php echo htmlspecialchars($folder['name']); ?></strong>
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownFolderActions<?php echo $folder['folderID']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownFolderActions<?php echo $folder['folderID']; ?>">
                                        <li><a class="dropdown-item rename-btn" href="#" data-bs-toggle="modal" data-bs-target="#renameModal" data-item-id="<?php echo $folder['folderID']; ?>" data-item-name="<?php echo htmlspecialchars($folder['name']); ?>" data-item-type="folder">Rename</a></li>
                                        <li><a class="dropdown-item move-btn" href="#" data-bs-toggle="modal" data-bs-target="#moveModal" data-item-id="<?php echo $folder['folderID']; ?>" data-item-type="folder">Move</a></li>
                                        <li><a class="dropdown-item" href="<?= BASE_PATH ?>lm/deleteFolder?folderID=<?php echo $folder['folderID'] ?>">Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach;
                        ?>

                        <?php foreach ($fileList['files'] as $file):
                        ?>
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <a class="text-decoration-none text-dark flex-grow-1" href="<?= BASE_PATH ?>lm/displayDocument?fileID=<?php echo $file['fileID'] ?>">
                                    <?php
                                    $fileIcon = 'bi-file-earmark';
                                    $fileTypeLower = strtolower($file['fileType']);
                                    if (in_array($fileTypeLower, ['pdf'])) $fileIcon = 'bi-file-earmark-pdf';
                                    elseif (in_array($fileTypeLower, ['doc', 'docx'])) $fileIcon = 'bi-file-earmark-word';
                                    elseif (in_array($fileTypeLower, ['jpg', 'jpeg', 'png', 'gif'])) $fileIcon = 'bi-file-earmark-image';
                                    ?>
                                    <i class="bi <?php echo $fileIcon; ?> me-2"></i>
                                    <strong><?php echo htmlspecialchars($file['name']); ?></strong>
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownFileActions<?php echo $file['fileID']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownFileActions<?php echo $file['fileID']; ?>">
                                        <li><a class="dropdown-item" href="<?= BASE_PATH ?>lm/displayDocument?fileID=<?php echo $file['fileID'] ?>">View</a></li>
                                        <li><a class="dropdown-item move-btn" href="#" data-bs-toggle="modal" data-bs-target="#moveModal" data-item-id="<?php echo $file['fileID']; ?>" data-item-type="file">Move</a></li>
                                        <li><a class="dropdown-item rename-btn" href="#" data-bs-toggle="modal" data-bs-target="#renameModal" data-item-id="<?php echo $file['fileID']; ?>" data-item-name="<?php echo htmlspecialchars($file['name']); ?>" data-item-type="file">Rename</a></li>
                                        <li><a class="dropdown-item" href="<?= BASE_PATH ?>lm/deleteDocument?fileID=<?php echo $file['fileID'] ?>">Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach;
                        ?>
                    <?php else:
                    ?>
                        <p>This folder is empty.</p>
                    <?php endif;
                    ?>
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
                    <form id="renameForm" onsubmit="return false;">
                        <input type="hidden" id="renameItemId">
                        <div class="mb-3">
                            <label for="newItemName" class="form-label" id="renameModalItemNameLabel">Name</label>
                            <input type="text" class="form-control" id="newItemName" required>
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

    <!-- Generic Move Modal -->
    <div class="modal fade" id="moveModal" tabindex="-1" aria-labelledby="moveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="moveModalLabel">Move Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5>Select Destination Folder</h5>
                    <ul>
                        <li>
                            <a href="#" class="folder-item" data-folder-id="0" data-folder-name="Root">Home</a>
                        </li>
                    </ul>
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
    $(document).ready(function() {
        var renameModal = $("#renameModal");
        var moveModal = $("#moveModal");
        var itemToMove = { id: null, type: null };

        // --- Rename Logic ---
        renameModal.on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var itemId = button.data('item-id');
            var itemName = button.data('item-name');
            var itemType = button.data('item-type');
            
            renameModal.find('.modal-title').text('Rename ' + itemType.charAt(0).toUpperCase() + itemType.slice(1));
            renameModal.find('#renameModalItemNameLabel').text(itemType.charAt(0).toUpperCase() + itemType.slice(1) + ' Name');
            renameModal.find('#renameItemId').val(itemId);
            renameModal.find('#newItemName').val(itemName);
            renameModal.find('#saveRenameBtn').data('item-type', itemType);
        });

        $('#saveRenameBtn').on('click', function() {
            var itemType = $(this).data('item-type');
            var itemId = $('#renameItemId').val();
            var newName = $('#newItemName').val();
            var url = itemType === 'folder' ? '<?= BASE_PATH ?>lm/renameFolder' : '<?= BASE_PATH ?>lm/renameFile';
            var data = itemType === 'folder' ? { folderId: itemId, newName: newName } : { fileId: itemId, newName: newName };

            $.ajax({ url: url, type: 'POST', data: data, dataType: 'json',
                success: function(response) { location.reload(); },
                error: function() { alert('An error occurred.'); }
            });
        });

        // --- Move Logic ---
        moveModal.on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            itemToMove.id = button.data('item-id');
            itemToMove.type = button.data('item-type');
        });

        moveModal.on('click', '.folder-item', function(e) {
            e.preventDefault();
            var targetFolderId = $(this).data('folder-id');
            var url = itemToMove.type === 'folder' ? '<?= BASE_PATH ?>lm/moveFolder' : '<?= BASE_PATH ?>lm/moveFile';
            var data = itemToMove.type === 'folder' ? { folderId: itemToMove.id, newFolderId: targetFolderId } : { fileId: itemToMove.id, newFolderId: targetFolderId };

            $.ajax({ url: url, type: 'POST', data: data, dataType: 'json',
                success: function(response) { location.reload(); },
                error: function() { alert('An error occurred during the move.'); }
            });
        });
    });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>