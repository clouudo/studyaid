<?php
function buildFolderTree($folders, $parentId = null, $level = 0)
{
    $html = '';
    $hasItems = false;
    
    foreach ($folders as $folder) {
        if ($folder['parentFolderId'] == $parentId) {
            $hasItems = true;
            $html .= '<li>';
            $html .= '<a href="#" class="folder-item" data-folder-id="' . $folder['folderID'] . '" data-folder-name="' . htmlspecialchars($folder['name']) . '" style="display: block; padding: 12px 16px; color: #6f42c1; text-decoration: none; border-radius: 8px; transition: all 0.2s; margin-bottom: 4px; font-weight: 500;">';
            $html .= '<i class="bi bi-folder-fill me-2"></i>';
            $html .= htmlspecialchars($folder['name']);
            $html .= '</a>';
            $children = buildFolderTree($folders, $folder['folderID'], $level + 1);
            if ($children) {
                $html .= '<ul class="folder-list" style="list-style: none; padding-left: 24px; margin: 8px 0 0 0;">' . $children . '</ul>';
            }
            $html .= '</li>';
        }
    }
    
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .upload-container {
            background-color: #f8f9fa;
            padding: 30px;
        }
        .page-header {
            color: #212529;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .breadcrumb-container {
            margin-bottom: 20px;
        }
        .breadcrumb {
            background-color: #d4b5ff;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 0;
        }
        .breadcrumb-item a {
            color: #6f42c1;
            text-decoration: none;
        }
        .breadcrumb-item a:hover {
            color: #5a32a3;
            text-decoration: underline;
        }
        .breadcrumb-item.active {
            color: #495057;
        }
        .search-container {
            margin-bottom: 20px;
        }
        .form-control-search {
            background-color: #e7d5ff;
            border: none;
            border-radius: 12px;
            padding: 12px 16px;
            color: #212529;
        }
        .form-control-search:focus {
            background-color: #e7d5ff;
            border: 2px solid #6f42c1;
            box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
            color: #212529;
        }
        .btn-search {
            background-color: #6f42c1;
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
        }
        .btn-search:hover {
            background-color: #5a32a3;
            color: white;
        }
        .list-group-item {
            background-color: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            margin-bottom: 8px;
            padding: 16px;
            transition: all 0.2s;
        }
        .list-group-item:hover {
            background-color: #f8f9fa;
            border-color: #d4b5ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(111, 66, 193, 0.1);
        }
        .list-group-item.dragging {
            opacity: 0.5;
            border: 2px dashed #6f42c1;
        }
        .list-group-item.drag-over {
            background-color: #e7d5ff;
            border: 2px solid #6f42c1;
        }
        .file-folder-link {
            color: #212529;
            text-decoration: none;
            display: flex;
            align-items: center;
            flex-grow: 1;
        }
        .file-folder-link:hover {
            color: #6f42c1;
        }
        .file-folder-link i {
            color: #6f42c1;
            margin-right: 12px;
            font-size: 1.25rem;
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
        .btn-icon {
            background: transparent;
            border: none;
            color: #6c757d;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-icon:hover {
            color: #6f42c1;
            background-color: #e7d5ff;
        }
        .dropdown-menu {
            border-radius: 12px;
            border: 1px solid #e9ecef;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            z-index: 1050;
        }
        .dropdown {
            position: relative;
            z-index: 1000;
        }
        .dropdown.show {
            z-index: 1051;
        }
        .list-group-item {
            position: relative;
            z-index: 1;
        }
        .list-group-item .dropdown.show {
            z-index: 1052;
        }
        .dropdown-item {
            padding: 10px 16px;
            transition: all 0.2s;
        }
        .dropdown-item:hover {
            color: #6f42c1;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            color: #d4b5ff;
            margin-bottom: 16px;
        }
        .modal-close-btn:hover {
            background-color: #6f42c1 !important;
            color: white !important;
        }
        .btn-cancel:hover {
            background-color: #d4b5ff !important;
            color: #5a32a3 !important;
        }
        .btn-create:hover {
            background-color: #6f42c1 !important;
            color: white !important;
        }
        .folder-item:hover {
            background-color: #e7d5ff !important;
            color: #5a32a3 !important;
            text-decoration: none !important;
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

                <h3 class="page-header">All Documents</h3>
                
                <div class="breadcrumb-container">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= BASE_PATH ?>lm/displayLearningMaterials"><i class="bi bi-house-fill"></i></a></li>
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
                </div>

                <div class="search-container d-flex align-items-center gap-3">
                    <form action="index.php" method="get" class="flex-grow-1">
                        <div class="input-group">
                            <input type="hidden" name="url" value="lm/displayLearningMaterials">
                            <input type="text" class="form-control form-control-search" placeholder="Search all documents..." name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            <button class="btn btn-search" type="submit">
                                <i class="bi bi-search me-1"></i>Search
                            </button>
                        </div>
                    </form>
                    <div class="dropdown">
                        <button class="btn-icon" id="sortDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false" title="Sort documents">
                            <i class="bi bi-funnel-fill" style="font-size: 1.1rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortDropdownBtn">
                            <li><a class="dropdown-item sort-option" href="#" data-sort="asc">A to Z</a></li>
                            <li><a class="dropdown-item sort-option" href="#" data-sort="desc">Z to A</a></li>
                        </ul>
                    </div>
                </div>

                <div class="list-group" id="documentList">
                    <?php if (!empty($fileList['folders']) || !empty($fileList['files'])): ?>
                        <?php foreach ($fileList['folders'] as $folder):
                        ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center" 
                                 draggable="true" 
                                 data-item-id="<?php echo $folder['folderID']; ?>" 
                                 data-item-type="folder"
                                 data-item-name="<?php echo htmlspecialchars(strtolower($folder['name'])); ?>">
                                <a class="file-folder-link" href="<?= BASE_PATH ?>lm/displayLearningMaterials?folder_id=<?php echo $folder['folderID'] ?>">
                                    <i class="bi bi-folder-fill"></i>
                                    <strong><?php echo htmlspecialchars($folder['name']); ?></strong>
                                </a>
                                <div class="dropdown">
                                    <button class="action-btn" type="button" id="dropdownFolderActions<?php echo $folder['folderID']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownFolderActions<?php echo $folder['folderID']; ?>">
                                        <li><a class="dropdown-item rename-btn" href="#" data-bs-toggle="modal" data-bs-target="#renameModal" data-item-id="<?php echo $folder['folderID']; ?>" data-item-name="<?php echo htmlspecialchars($folder['name']); ?>" data-item-type="folder">Rename</a></li>
                                        <li><a class="dropdown-item move-btn" href="#" data-bs-toggle="modal" data-bs-target="#moveModal" data-item-id="<?php echo $folder['folderID']; ?>" data-item-type="folder">Move</a></li>
                                        <li><a class="dropdown-item delete-folder-btn" href="#" data-folder-id="<?php echo $folder['folderID']; ?>">Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach;
                        ?>

                        <?php foreach ($fileList['files'] as $file):
                        ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center" 
                                 draggable="true" 
                                 data-item-id="<?php echo $file['fileID']; ?>" 
                                 data-item-type="file"
                                 data-item-name="<?php echo htmlspecialchars(strtolower($file['name'])); ?>">
                                <form method="POST" action="<?= DISPLAY_DOCUMENT ?>" style="display: inline; flex-grow: 1; margin: 0;">
                                    <input type="hidden" name="file_id" value="<?php echo $file['fileID']; ?>">
                                    <button type="submit" class="file-folder-link" style="border: none; background: none; width: 100%; text-align: left; padding: 0;">
                                        <?php
                                        $fileIcon = 'bi-file-earmark';
                                        $fileTypeLower = strtolower($file['fileType']);
                                        if (in_array($fileTypeLower, ['pdf'])) $fileIcon = 'bi-file-earmark-pdf';
                                        elseif (in_array($fileTypeLower, ['doc', 'docx'])) $fileIcon = 'bi-file-earmark-word';
                                        elseif (in_array($fileTypeLower, ['jpg', 'jpeg', 'png', 'gif'])) $fileIcon = 'bi-file-earmark-image';
                                        ?>
                                        <i class="bi <?php echo $fileIcon; ?>"></i>
                                        <strong><?php echo htmlspecialchars($file['name']); ?></strong>
                                    </button>
                                </form>
                                <div class="dropdown">
                                    <button class="action-btn" type="button" id="dropdownFileActions<?php echo $file['fileID']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownFileActions<?php echo $file['fileID']; ?>">
                                        <li>
                                            <form method="POST" action="<?= DISPLAY_DOCUMENT ?>" style="display: inline;">
                                                <input type="hidden" name="file_id" value="<?php echo $file['fileID']; ?>">
                                                <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">View</button>
                                            </form>
                                        </li>
                                        <li><a class="dropdown-item move-btn" href="#" data-bs-toggle="modal" data-bs-target="#moveModal" data-item-id="<?php echo $file['fileID']; ?>" data-item-type="file">Move</a></li>
                                        <li><a class="dropdown-item rename-btn" href="#" data-bs-toggle="modal" data-bs-target="#renameModal" data-item-id="<?php echo $file['fileID']; ?>" data-item-name="<?php echo htmlspecialchars($file['name']); ?>" data-item-type="file">Rename</a></li>
                                        <li>
                                            <form method="POST" action="<?= DELETE_DOCUMENT ?>" style="display: inline;">
                                                <input type="hidden" name="file_id" value="<?php echo $file['fileID']; ?>">
                                                <button type="submit" id="deleteFileBtn" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Delete</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach;
                        ?>
                    <?php else:
                    ?>
                        <div class="empty-state">
                            <i class="bi bi-folder-x"></i>
                            <p>This folder is empty.</p>
                        </div>
                    <?php endif;
                    ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Generic Rename Modal -->
    <div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
                <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e9ecef; padding: 20px 24px;">
                    <h5 class="modal-title" id="renameModalLabel" style="font-weight: 600; color: #212529; font-size: 1.25rem;">Rename</h5>
                    <button type="button" class="modal-close-btn" data-bs-dismiss="modal" aria-label="Close" style="background-color: transparent; border: none; color: #6f42c1; padding: 8px 12px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s; font-size: 1.5rem; cursor: pointer;">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="modal-body" style="padding: 20px 24px;">
                    <form id="renameForm" onsubmit="return false;">
                        <input type="hidden" id="renameItemId">
                        <div class="mb-3">
                            <label for="newItemName" class="form-label fw-semibold" id="renameModalItemNameLabel">Name</label>
                            <input type="text" class="form-control form-input-theme" id="newItemName" required style="background-color: #e7d5ff; border: none; border-radius: 12px; padding: 12px 16px; color: #212529;">
                        </div>
                    </form>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 20px 24px;">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal" style="background-color: #e7d5ff; border: none; color: #6f42c1; padding: 10px 24px; border-radius: 8px; font-weight: 600;">Cancel</button>
                    <button type="button" class="btn btn-create" id="saveRenameBtn" style="background-color: #e7d5ff; border: none; color: #6f42c1; padding: 10px 24px; border-radius: 8px; font-weight: 600;">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Generic Move Modal -->
    <div class="modal fade" id="moveModal" tabindex="-1" aria-labelledby="moveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
                <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e9ecef; padding: 20px 24px;">
                    <h5 class="modal-title" id="moveModalLabel" style="font-weight: 600; color: #212529; font-size: 1.25rem;">Move Item</h5>
                    <button type="button" class="modal-close-btn" data-bs-dismiss="modal" aria-label="Close" style="background-color: transparent; border: none; color: #6f42c1; padding: 8px 12px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s; font-size: 1.5rem; cursor: pointer;">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="modal-body" style="padding: 20px 24px; max-height: 400px; overflow-y: auto;">
                    <h5 style="margin-bottom: 16px; color: #212529;">Select Destination Folder</h5>
                    <?php if (!empty($allUserFolders)): ?>
                        <ul class="folder-list" style="list-style: none; padding: 0; margin: 0;">
                            <li>
                                <a href="#" class="folder-item" data-folder-id="0" data-folder-name="Root" style="display: block; padding: 12px 16px; color: #6f42c1; text-decoration: none; border-radius: 8px; transition: all 0.2s; margin-bottom: 4px; font-weight: 500;">
                                    <i class="bi bi-house-fill me-2"></i>Home
                                </a>
                            </li>
                            <?php echo buildFolderTree($allUserFolders); ?>
                        </ul>
                    <?php else: ?>
                        <ul class="folder-list" style="list-style: none; padding: 0; margin: 0;">
                            <li>
                                <a href="#" class="folder-item" data-folder-id="0" data-folder-name="Root" style="display: block; padding: 12px 16px; color: #6f42c1; text-decoration: none; border-radius: 8px; transition: all 0.2s; margin-bottom: 4px; font-weight: 500;">
                                    <i class="bi bi-house-fill me-2"></i>Home
                                </a>
                            </li>
                        </ul>
                        <p class="text-muted text-center py-4">No folders available. Create a folder first.</p>
                    <?php endif; ?>
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

        // Ensure dropdowns appear in front when opened
        $(document).on('show.bs.dropdown', '.dropdown', function() {
            $(this).css('z-index', '1052');
        });
        
        $(document).on('hide.bs.dropdown', '.dropdown', function() {
            $(this).css('z-index', '1000');
        });

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

        // Sort functionality - Client-side sorting
        $(document).on('click', '.sort-option', function(e) {
            e.preventDefault();
            const sortType = $(this).data('sort');
            const $listGroup = $('#documentList');
            const $items = $listGroup.find('.list-group-item').toArray();
            
            $items.sort(function(a, b) {
                const $a = $(a);
                const $b = $(b);
                const aName = $a.data('item-name') || $a.find('strong').text().toLowerCase();
                const bName = $b.data('item-name') || $b.find('strong').text().toLowerCase();
                const aType = $a.data('item-type');
                const bType = $b.data('item-type');
                
                // Always keep folders first, then files
                if (aType !== bType) {
                    return aType === 'folder' ? -1 : 1;
                }
                
                switch(sortType) {
                    case 'asc':
                        return aName.localeCompare(bName);
                    case 'desc':
                        return bName.localeCompare(aName);
                    default:
                        return 0;
                }
            });
            
            // Re-append sorted items
            $listGroup.empty();
            $items.forEach(function(item) {
                $listGroup.append(item);
            });
            
            // Close dropdown
            const $dropdown = $(this).closest('.dropdown');
            const dropdownInstance = bootstrap.Dropdown.getInstance($dropdown.find('button')[0]);
            if (dropdownInstance) {
                dropdownInstance.hide();
            }
        });

        // Delete folder handler
        $(document).on('click', '.delete-folder-btn', function(e) {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this folder? This action cannot be undone.')) {
                return;
            }
            var folderId = $(this).data('folder-id');
            var url = '<?= BASE_PATH ?>lm/deleteFolder';
            var data = { folder_id: folderId };
            $.ajax({ 
                url: url, 
                type: 'POST', 
                data: data,
                success: function(response) { 
                    location.reload(); 
                },
                error: function() { 
                    alert('An error occurred during the deletion.'); 
                }
            });
        });

        // Drag and Drop functionality
        let draggedElement = null;
        let currentFolderId = <?php echo isset($_GET['folder_id']) ? (int)$_GET['folder_id'] : 0; ?>;

        // Drag start
        $(document).on('dragstart', '.list-group-item[draggable="true"]', function(e) {
            draggedElement = this;
            $(this).addClass('dragging');
            e.originalEvent.dataTransfer.effectAllowed = 'move';
            e.originalEvent.dataTransfer.setData('text/html', this.outerHTML);
        });

        // Drag end
        $(document).on('dragend', '.list-group-item[draggable="true"]', function(e) {
            $(this).removeClass('dragging');
            $('.list-group-item').removeClass('drag-over');
        });

        // Drag over
        $(document).on('dragover', '.list-group-item[draggable="true"]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.originalEvent.dataTransfer.dropEffect = 'move';
            
            // Don't highlight if dragging over itself
            if (this !== draggedElement) {
                $(this).addClass('drag-over');
            }
        });

        // Drag leave
        $(document).on('dragleave', '.list-group-item[draggable="true"]', function(e) {
            $(this).removeClass('drag-over');
        });

        // Drop
        $(document).on('drop', '.list-group-item[draggable="true"]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (draggedElement === null || this === draggedElement) {
                return;
            }

            $(this).removeClass('drag-over');
            
            const draggedItemId = $(draggedElement).data('item-id');
            const draggedItemType = $(draggedElement).data('item-type');
            const targetItemId = $(this).data('item-id');
            const targetItemType = $(this).data('item-type');

            // Only allow dropping on folders
            if (targetItemType !== 'folder') {
                alert('You can only move items into folders.');
                return;
            }

            // Prevent moving folder into itself or its children
            if (draggedItemType === 'folder' && draggedItemId === targetItemId) {
                alert('Cannot move folder into itself.');
                return;
            }

            // Confirm move
            if (!confirm('Move ' + draggedItemType + ' to this folder?')) {
                return;
            }

            // Perform move via AJAX
            const url = draggedItemType === 'folder' 
                ? '<?= BASE_PATH ?>lm/moveFolder' 
                : '<?= BASE_PATH ?>lm/moveFile';
            const data = draggedItemType === 'folder'
                ? { folderId: draggedItemId, newFolderId: targetItemId }
                : { fileId: draggedItemId, newFolderId: targetItemId };

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    location.reload();
                },
                error: function() {
                    alert('An error occurred during the move.');
                }
            });
        });
    });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>