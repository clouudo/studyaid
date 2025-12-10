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

        .file-folder-link:hover .file-upload-date {
            color: #6c757d;
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
        /* Only apply to dropdowns in the main content, not sidebar */
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
        
        /* Exclude sidebar dropdowns from the above rules - let Bootstrap handle it */
        .sidebar-wrapper .dropdown-menu,
        .sidebar-wrapper .dropup .dropdown-menu {
            position: absolute !important;
            bottom: calc(100% + 8px) !important;
            top: auto !important;
            right: 0 !important;
            left: auto !important;
            margin: 0 !important;
            margin-bottom: 8px !important;
            border-radius: 8px !important;
            border: 1px solid #e9ecef !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
            background-color: white !important;
            min-width: 150px !important;
            width: auto !important;
            max-width: none !important;
            padding: 0.5rem 0 !important;
            transform: none !important;
        }
        .dropdown-menu li {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .dropdown-menu li + li {
            border-top: 1px solid #f0e6ff;
        }

        /* Action dropdowns (Rename, Move, Delete) should be in front */
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

        /* Only apply to dropdowns in the main content */
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
        
        /* Sidebar dropup should go up - override any conflicting styles */
        .sidebar-wrapper .dropup.show .dropdown-menu,
        .sidebar-wrapper .dropup[data-bs-popper-placement] .dropdown-menu {
            z-index: 1050 !important;
            display: block !important;
            position: absolute !important;
            bottom: calc(100% + 8px) !important;
            top: auto !important;
            right: 0 !important;
            left: auto !important;
            transform: none !important;
            margin-bottom: 8px !important;
        }

        .list-group-item {
            position: relative;
            z-index: 1;
            overflow: visible;
            isolation: isolate;
        }

        .list-group-item .dropdown {
            z-index: 2147483646;
            position: relative;
            overflow: visible;
            isolation: auto;
        }

        .list-group-item .dropdown.show {
            z-index: 2147483646 !important;
            position: relative;
            isolation: auto;
        }

        .list-group-item .dropdown.show .dropdown-menu {
            z-index: 2147483647 !important;
            display: block !important;
            position: absolute !important;
            top: calc(100% + 8px) !important;
            right: 0 !important;
            left: auto !important;
            margin: 0 !important;
            transform: none !important;
            width: 180px !important;
            min-width: 180px !important;
            max-width: 180px !important;
            isolation: auto;
        }
        
        /* Ensure list-group-item doesn't clip dropdown when it's open */
        .list-group-item.dropdown-open {
            z-index: 2147483645 !important;
            overflow: visible !important;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            font-weight: 500;
            transition: all 0.2s;
            color: #212529;
            background-color: transparent;
            cursor: pointer;
            border: none;
            margin: 0 !important;
        }

        .dropdown-item:hover {
            background-color: #e7d5ff !important;
            color: #5a32a3 !important;
        }

        .dropdown-item:active,
        .dropdown-item.active,
        .dropdown-item:focus {
            background-color: #d4b5ff !important;
            color: #212529 !important;
            outline: none;
        }

        .list-group {
            overflow: visible;
        }

        .upload-container {
            overflow: visible;
        }

        .search-container .dropdown {
            position: relative;
            z-index: 2147483646;
        }

        .search-container .dropdown.show {
            z-index: 2147483646 !important;
        }

        .search-container .dropdown.show .dropdown-menu {
            z-index: 2147483647 !important;
            width: 180px !important;
            min-width: 180px !important;
            max-width: 180px !important;
        }

        /* Remove any blue colors from dropdowns */
        .dropdown-item:focus,
        .dropdown-item:focus-visible {
            background-color: #d4b5ff !important;
            color: #212529 !important;
            outline: none !important;
            box-shadow: none !important;
        }

        /* Ensure dropdown menu doesn't have blue borders or backgrounds */
        .dropdown-menu * {
            border-color: transparent !important;
        }

        /* Override Bootstrap default blue colors */
        .dropdown-item:not(:disabled):not(.disabled):active,
        .dropdown-item:not(:disabled):not(.disabled).active {
            background-color: #d4b5ff !important;
            color: #212529 !important;
        }

        /* Ensure dropdown stays within container */
        .list-group-item .dropdown-menu {
            max-width: calc(100vw - 20px);
        }
        @media (min-width: 768px) {
            .list-group-item .dropdown-menu {
                max-width: 250px;
            }
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

        .file-upload-date {
            font-size: 0.85rem;
            color: #6c757d;
            margin-left: 12px;
            font-weight: normal;
        }

        .file-name-wrapper {
            display: flex;
            align-items: center;
            width: 100%;
        }

        /* Content Navigation Navbar Styles */
        .content-navbar {
            background-color: white;
            border-radius: 12px;
            padding: 8px;
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.08);
            border: 1px solid var(--sa-card-border);
            margin-bottom: 20px;
        }

        .content-navbar .nav {
            gap: 4px;
        }

        .content-navbar .nav-link {
            color: #6c757d;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 8px;
            transition: all 0.2s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .content-navbar .nav-link:hover {
            background-color: var(--sa-accent);
            color: var(--sa-primary);
        }

        .content-navbar .nav-link.active {
            background-color: var(--sa-primary);
            color: white;
        }

        .content-navbar .nav-link i {
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .content-navbar .nav-link {
                font-size: 0.85rem;
                padding: 8px 12px;
            }
            
            .content-navbar .nav-link i {
                font-size: 1rem;
            }
        }

        /* Content List Styles */
        .content-list-container {
            display: none;
            margin-top: 20px;
        }

        .content-list-container.active {
            display: block;
        }

        .content-list-item {
            background: white;
            border: 1px solid var(--sa-card-border);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content-list-item:hover {
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.1);
            transform: translateY(-2px);
            border-color: var(--sa-primary);
        }

        .content-list-item-info {
            flex: 1;
        }

        .content-list-item-title {
            font-weight: 600;
            color: #212529;
            margin-bottom: 4px;
            font-size: 1rem;
        }

        .content-list-item-meta {
            font-size: 0.85rem;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .content-list-item-icon {
            color: var(--sa-primary);
            font-size: 1.2rem;
        }

        .content-list-empty {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .content-list-empty i {
            font-size: 3rem;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        /* Badge styles for quiz status and exam mode */
        .content-list-item-title .badge {
            font-size: 0.75rem;
            font-weight: 500;
            padding: 4px 8px;
            margin-top: 4px;
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

                <h3 style="color: #212529; font-size: 1.5rem; font-weight: 600; margin-bottom: 20px;">All Documents</h3>

                <!-- Navigation Tabs for Generated Content -->
                <div class="content-navbar mb-4">
                    <nav class="nav nav-pills nav-justified">
                        <button class="nav-link active" data-tab="documents" type="button">
                            <i class="bi bi-folder me-2"></i>Documents
                        </button>
                        <button class="nav-link" data-tab="summaries" type="button">
                            <i class="bi bi-file-text me-2"></i>Summaries
                        </button>
                        <button class="nav-link" data-tab="notes" type="button">
                            <i class="bi bi-journal-text me-2"></i>Notes
                        </button>
                        <button class="nav-link" data-tab="mindmaps" type="button">
                            <i class="bi bi-diagram-3 me-2"></i>Mindmaps
                        </button>
                        <button class="nav-link" data-tab="flashcards" type="button">
                            <i class="bi bi-card-text me-2"></i>Flashcards
                        </button>
                        <button class="nav-link" data-tab="quizzes" type="button">
                            <i class="bi bi-question-circle me-2"></i>Quizzes
                        </button>
                    </nav>
                </div>

                <!-- Content Lists Container -->
                <div id="contentListsContainer" style="display: none;">
                    <!-- Summaries List -->
                    <div id="summariesList" class="content-list-container"></div>
                    
                    <!-- Notes List -->
                    <div id="notesList" class="content-list-container"></div>
                    
                    <!-- Mindmaps List -->
                    <div id="mindmapsList" class="content-list-container"></div>
                    
                    <!-- Flashcards List -->
                    <div id="flashcardsList" class="content-list-container"></div>
                    
                    <!-- Quizzes List -->
                    <div id="quizzesList" class="content-list-container"></div>
                </div>

                <!-- Documents Section (Breadcrumb, Search, and Document List) -->
                <div id="documentsSection">
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
                        <button class="btn-icon" id="sortDropdownBtn" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" title="Sort documents">
                            <i class="bi bi-funnel-fill" style="font-size: 1.1rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortDropdownBtn">
                            <li><a class="dropdown-item sort-option" href="#" data-sort="asc">A to Z</a></li>
                            <li><a class="dropdown-item sort-option" href="#" data-sort="desc">Z to A</a></li>
                            <li><a class="dropdown-item sort-option" href="#" data-sort="latest">Latest to Oldest</a></li>
                            <li><a class="dropdown-item sort-option" href="#" data-sort="oldest">Oldest to Latest</a></li>
                        </ul>
                    </div>
                    <div class="dropdown" id="bulkActionsDropdown" style="display: none;">
                        <button class="btn-icon" id="bulkActionsBtn" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" title="Bulk actions">
                            <i class="bi bi-check2-square" style="font-size: 1.1rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bulkActionsBtn">
                            <li><a class="dropdown-item" href="#" id="bulkMoveBtn">Move Selected</a></li>
                            <li><a class="dropdown-item text-danger" href="#" id="bulkDeleteBtn">Delete Selected</a></li>
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
                                data-item-name="<?php echo htmlspecialchars(strtolower($folder['name'])); ?>"
                                data-item-date="<?php echo isset($folder['createdAt']) ? strtotime($folder['createdAt']) : (isset($folder['created_at']) ? strtotime($folder['created_at']) : (isset($folder['folderID']) ? (int)$folder['folderID'] * 1000 : 0)); ?>">
                                <div class="d-flex align-items-center" style="flex-grow: 1;">
                                    <input type="checkbox" class="form-check-input bulk-select-checkbox me-3" 
                                           data-item-id="<?php echo $folder['folderID']; ?>" 
                                           data-item-type="folder"
                                           style="width: 18px; height: 18px; cursor: pointer;"
                                           onclick="event.stopPropagation();">
                                    <a class="file-folder-link" href="<?= BASE_PATH ?>lm/displayLearningMaterials?folder_id=<?php echo $folder['folderID'] ?>" onclick="event.stopPropagation();">
                                        <i class="bi bi-folder-fill"></i>
                                        <strong><?php echo htmlspecialchars($folder['name']); ?></strong>
                                    </a>
                                </div>
                                <div class="dropdown">
                                    <button class="action-btn"
                                        type="button"
                                        id="dropdownFolderActions<?php echo $folder['folderID']; ?>"
                                        data-bs-toggle="dropdown"
                                        data-bs-display="static"
                                        aria-expanded="false">
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
                                data-item-name="<?php echo htmlspecialchars(strtolower($file['name'])); ?>"
                                data-item-date="<?php echo isset($file['uploadDate']) ? strtotime($file['uploadDate']) : 0; ?>">
                                <div class="d-flex align-items-center" style="flex-grow: 1;">
                                    <input type="checkbox" class="form-check-input bulk-select-checkbox me-3" 
                                           data-item-id="<?php echo $file['fileID']; ?>" 
                                           data-item-type="file"
                                           style="width: 18px; height: 18px; cursor: pointer;"
                                           onclick="event.stopPropagation();">
                                    <form method="POST" action="<?= DISPLAY_DOCUMENT ?>" style="display: inline; flex-grow: 1; margin: 0;" onclick="event.stopPropagation();">
                                        <input type="hidden" name="file_id" value="<?php echo $file['fileID']; ?>">
                                        <button type="submit" class="file-folder-link" style="border: none; background: none; width: 100%; text-align: left; padding: 0;">
                                            <div class="file-name-wrapper">
                                                <?php
                                                $fileIcon = 'bi-file-earmark';
                                                $fileTypeLower = strtolower($file['fileType']);
                                                if (in_array($fileTypeLower, ['pdf'])) $fileIcon = 'bi-file-earmark-pdf';
                                                elseif (in_array($fileTypeLower, ['doc', 'docx'])) $fileIcon = 'bi-file-earmark-word';
                                                elseif (in_array($fileTypeLower, ['jpg', 'jpeg', 'png', 'gif'])) $fileIcon = 'bi-file-earmark-image';
                                                
                                                // Format upload date
                                                $uploadDate = null;
                                                if (isset($file['uploadDate']) && !empty($file['uploadDate'])) {
                                                    $uploadDate = date('M d, Y', strtotime($file['uploadDate']));
                                                }
                                                ?>
                                                <i class="bi <?php echo $fileIcon; ?>"></i>
                                                <strong><?php echo htmlspecialchars($file['name']); ?></strong>
                                                <?php if ($uploadDate): ?>
                                                    <span class="file-upload-date"><?php echo htmlspecialchars($uploadDate); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </button>
                                    </form>
                                </div>
                                <div class="dropdown">
                                    <button class="action-btn" type="button" id="dropdownFileActions<?php echo $file['fileID']; ?>" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
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
                                            <form method="POST" action="<?= DELETE_DOCUMENT ?>" style="display: inline;" class="delete-file-form" data-file-id="<?php echo $file['fileID']; ?>" data-file-name="<?php echo htmlspecialchars($file['name']); ?>">
                                                <input type="hidden" name="file_id" value="<?php echo $file['fileID']; ?>">
                                                <button type="button" class="dropdown-item delete-file-btn" style="border: none; background: none; width: 100%; text-align: left;">Delete</button>
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
                <!-- End Documents Section -->
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

    <!-- Bulk Move Modal -->
    <div class="modal fade" id="bulkMoveModal" tabindex="-1" aria-labelledby="bulkMoveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
                <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e9ecef; padding: 20px 24px;">
                    <h5 class="modal-title" id="bulkMoveModalLabel" style="font-weight: 600; color: #212529; font-size: 1.25rem;">Move Selected Items</h5>
                    <button type="button" class="modal-close-btn" data-bs-dismiss="modal" aria-label="Close" style="background-color: transparent; border: none; color: #6f42c1; padding: 8px 12px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s; font-size: 1.5rem; cursor: pointer;">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="modal-body" style="padding: 20px 24px; max-height: 400px; overflow-y: auto;">
                    <h5 style="margin-bottom: 16px; color: #212529;">Select Destination Folder</h5>
                    <?php if (!empty($allUserFolders)): ?>
                        <ul class="folder-list" style="list-style: none; padding: 0; margin: 0;">
                            <li>
                                <a href="#" class="bulk-folder-item" data-folder-id="0" data-folder-name="Root" style="display: block; padding: 12px 16px; color: #6f42c1; text-decoration: none; border-radius: 8px; transition: all 0.2s; margin-bottom: 4px; font-weight: 500;">
                                    <i class="bi bi-house-fill me-2"></i>Home
                                </a>
                            </li>
                            <?php 
                            // Build folder tree with bulk-folder-item class
                            $bulkFolderTree = buildFolderTree($allUserFolders);
                            $bulkFolderTree = str_replace('folder-item', 'bulk-folder-item', $bulkFolderTree);
                            echo $bulkFolderTree;
                            ?>
                        </ul>
                    <?php else: ?>
                        <ul class="folder-list" style="list-style: none; padding: 0; margin: 0;">
                            <li>
                                <a href="#" class="bulk-folder-item" data-folder-id="0" data-folder-name="Root" style="display: block; padding: 12px 16px; color: #6f42c1; text-decoration: none; border-radius: 8px; transition: all 0.2s; margin-bottom: 4px; font-weight: 500;">
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
    /**
     * Document ready handler - initializes all document management functionality
     * 
     * Behavior: Sets up modals, dropdowns, drag-and-drop, sorting, and AJAX handlers
     * for renaming, moving, deleting folders and files.
     */
    $(document).ready(function() {
        var renameModal = $("#renameModal");
        var moveModal = $("#moveModal");
            var itemToMove = {
                id: null,
                type: null
            };

            /**
             * Applies consistent dropdown styling to override Bootstrap defaults
             * 
             * Behavior: Removes conflicting Bootstrap styles and applies custom theme
             * styles with maximum z-index to ensure dropdowns appear above all content.
             * 
             * @param {jQuery} $menu Dropdown menu element
             * @param {jQuery} $dropdown Dropdown container element
             * @param {boolean} isActionDropdown Whether this is an action dropdown (Rename/Move/Delete)
             */
            function applyDropdownStyles($menu, $dropdown, isActionDropdown) {
                // Use maximum z-index to ensure dropdowns are always on top
                const zIndex = '2147483647';
                const zIndexDropdown = '2147483646';
                $dropdown.css('z-index', zIndexDropdown);

                // Get current style attribute and modify it
                let currentStyle = $menu.attr('style') || '';

                // Remove existing position, sizing, and z-index declarations that conflict with theme
                currentStyle = currentStyle.replace(/width\s*:\s*[^;]+;?/gi, '');
                currentStyle = currentStyle.replace(/min-width\s*:\s*[^;]+;?/gi, '');
                currentStyle = currentStyle.replace(/max-width\s*:\s*[^;]+;?/gi, '');
                currentStyle = currentStyle.replace(/z-index\s*:\s*[^;]+;?/gi, '');
                currentStyle = currentStyle.replace(/top\s*:\s*[^;]+;?/gi, '');
                currentStyle = currentStyle.replace(/right\s*:\s*[^;]+;?/gi, '');
                currentStyle = currentStyle.replace(/left\s*:\s*[^;]+;?/gi, '');
                currentStyle = currentStyle.replace(/inset\s*:\s*[^;]+;?/gi, '');
                currentStyle = currentStyle.replace(/transform\s*:\s*[^;]+;?/gi, '');
                currentStyle = currentStyle.replace(/position\s*:\s*[^;]+;?/gi, '');
                currentStyle = currentStyle.replace(/margin(?:-top|-right|-bottom|-left)?\s*:\s*[^;]+;?/gi, '');
                currentStyle = currentStyle.replace(/padding(?:-top|-right|-bottom|-left)?\s*:\s*[^;]+;?/gi, '');
                currentStyle = currentStyle.replace(/border(?:-radius)?\s*:\s*[^;]+;?/gi, '');
                currentStyle = currentStyle.replace(/box-shadow\s*:\s*[^;]+;?/gi, '');
                currentStyle = currentStyle.replace(/background-color\s*:\s*[^;]+;?/gi, '');

                // Add our styles with !important - use maximum z-index
                const newStyles = [
                    'position: absolute !important',
                    'top: calc(100% + 8px) !important',
                    'right: 0 !important',
                    'left: auto !important',
                    'margin: 0 !important',
                    'width: 180px !important',
                    'min-width: 180px !important',
                    'max-width: 180px !important',
                    'padding: 8px 0 !important',
                    'border-radius: 12px !important',
                    'border: 1px solid #d4b5ff !important',
                    'box-shadow: 0 10px 24px rgba(90, 50, 163, 0.12) !important',
                    'background-color: #ffffff !important',
                    'overflow: hidden !important',
                    'transform: none !important',
                    'z-index: 2147483647 !important'
                ].join('; ') + ';';

                // Combine styles
                const finalStyle = (currentStyle.trim() ? currentStyle.trim() + '; ' : '') + newStyles;
                $menu.attr('style', finalStyle);
            }

            /**
             * Dropdown show event handler
             * 
             * Behavior: Applies custom styling when dropdown opens, ensuring proper
             * z-index and positioning. Excludes sidebar dropdowns from custom styling.
             */
            $(document).on('show.bs.dropdown', '.dropdown', function(e) {
                const $dropdown = $(this);
                const $menu = $dropdown.find('.dropdown-menu');
                const $listItem = $dropdown.closest('.list-group-item');
                const $searchContainer = $dropdown.closest('.search-container');
                const $sidebar = $dropdown.closest('.sidebar-wrapper');

                // Skip sidebar dropdowns - let them use their own styling
                if ($sidebar.length > 0) {
                    return;
                }

                // Action dropdowns (in list items) get higher z-index than sort dropdown
                if ($listItem.length > 0) {
                    // This is an action dropdown (Rename, Move, Delete)
                    // Add class to list item to raise its z-index
                    $listItem.addClass('dropdown-open').css({
                        'overflow': 'visible',
                        'z-index': '2147483645'
                    });
                    applyDropdownStyles($menu, $dropdown, true);
                } else if ($searchContainer.length > 0) {
                    // This is the sort dropdown
                    applyDropdownStyles($menu, $dropdown, false);
                } else {
                    // Other dropdowns
                    applyDropdownStyles($menu, $dropdown, false);
                }
            });

            /**
             * Dropdown shown event handler
             * 
             * Behavior: Reapplies styles after Bootstrap positioning, uses MutationObserver
             * to maintain styles if Bootstrap/Popper.js modifies them. Ensures dropdowns
             * remain visible and properly styled.
             */
            $(document).on('shown.bs.dropdown', '.dropdown', function() {
                const $dropdown = $(this);
                const $menu = $dropdown.find('.dropdown-menu');
                const $listItem = $dropdown.closest('.list-group-item');
                const $searchContainer = $dropdown.closest('.search-container');
                const $sidebar = $dropdown.closest('.sidebar-wrapper');
                const isActionDropdown = $listItem.length > 0;

                // Skip sidebar dropdowns - let them use their own styling
                if ($sidebar.length > 0) {
                    return;
                }

                // Apply styles immediately
                if (isActionDropdown) {
                    // Ensure list item doesn't interfere with dropdown
                    $listItem.css({
                        'overflow': 'visible',
                        'z-index': '2147483645'
                    });
                    applyDropdownStyles($menu, $dropdown, true);
                    
                    // Calculate position relative to viewport to ensure it's on top
                    const buttonOffset = $dropdown.find('button').offset();
                    const buttonWidth = $dropdown.find('button').outerWidth();
                    const menuHeight = $menu.outerHeight() || 200;
                    
                    // Position dropdown absolutely but ensure it's visible
                    $menu.css({
                        'position': 'absolute',
                        'top': 'calc(100% + 8px)',
                        'right': '0',
                        'left': 'auto',
                        'z-index': '2147483647'
                    });
                } else if ($searchContainer.length > 0) {
                    applyDropdownStyles($menu, $dropdown, false);
                } else {
                    applyDropdownStyles($menu, $dropdown, false);
                }

                // Use setTimeout to ensure styles are applied after Popper.js positioning
                setTimeout(function() {
                    if (isActionDropdown) {
                        $listItem.css({
                            'overflow': 'visible',
                            'z-index': '2147483645'
                        });
                        applyDropdownStyles($menu, $dropdown, true);
                    } else {
                        applyDropdownStyles($menu, $dropdown, false);
                    }
                }, 10);

                // Use MutationObserver to watch for style changes and reapply
                if ($menu.length > 0) {
                    const observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                                setTimeout(function() {
                                    applyDropdownStyles($menu, $dropdown, isActionDropdown);
                                }, 5);
                            }
                        });
                    });

                    observer.observe($menu[0], {
                        attributes: true,
                        attributeFilter: ['style']
                    });

                // Disconnect observer when dropdown is hidden
                $dropdown.one('hide.bs.dropdown', function() {
                        observer.disconnect();
                    });
                }
            });

            /**
             * Dropdown hide event handler
             * 
             * Behavior: Resets list item styles when dropdown closes to restore
             * normal overflow and z-index behavior.
             */
            $(document).on('hide.bs.dropdown', '.dropdown', function() {
                // Reset styles when closing
                const $listItem = $(this).closest('.list-group-item');
                if ($listItem.length > 0) {
                    $listItem.removeClass('dropdown-open').css({
                        'overflow': '',
                        'z-index': ''
                    });
                }
            });

        /**
         * Rename modal initialization
         * 
         * Behavior: Populates rename modal with item details when opened.
         * Sets modal title, label, and input value based on item type and name.
         */
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

        /**
         * Save rename button handler
         * 
         * Behavior: Sends AJAX request to rename folder or file. Reloads page on success,
         * shows error snackbar on failure.
         */
        $('#saveRenameBtn').on('click', function() {
            var itemType = $(this).data('item-type');
            var itemId = $('#renameItemId').val();
            var newName = $('#newItemName').val();
            var url = itemType === 'folder' ? '<?= BASE_PATH ?>lm/renameFolder' : '<?= BASE_PATH ?>lm/renameFile';
                var data = itemType === 'folder' ? {
                    folderId: itemId,
                    newName: newName
                } : {
                    fileId: itemId,
                    newName: newName
                };

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        location.reload();
                    },
                    error: function() {
                        showSnackbar('An error occurred while renaming.', 'error');
                    }
            });
        });

        /**
         * Move modal initialization
         * 
         * Behavior: Stores item ID and type when move modal opens for later use
         * when user selects destination folder.
         */
        moveModal.on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            itemToMove.id = button.data('item-id');
            itemToMove.type = button.data('item-type');
        });

        /**
         * Folder selection handler in move modal
         * 
         * Behavior: Moves item to selected folder via AJAX. Reloads page on success,
         * shows error snackbar on failure.
         */
        moveModal.on('click', '.folder-item', function(e) {
            e.preventDefault();
            var targetFolderId = $(this).data('folder-id');
            var url = itemToMove.type === 'folder' ? '<?= BASE_PATH ?>lm/moveFolder' : '<?= BASE_PATH ?>lm/moveFile';
                var data = itemToMove.type === 'folder' ? {
                    folderId: itemToMove.id,
                    newFolderId: targetFolderId
                } : {
                    fileId: itemToMove.id,
                    newFolderId: targetFolderId
                };

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        location.reload();
                    },
                    error: function() {
                        showSnackbar('An error occurred during the move.', 'error');
                    }
                });
            });

            /**
             * Sort functionality - Client-side sorting
             * 
             * Behavior: Sorts document list items client-side based on selected sort type.
             * Always keeps folders first, then files. Supports A-Z, Z-A, latest-oldest,
             * and oldest-latest sorting.
             */
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

                    switch (sortType) {
                        case 'asc':
                            return aName.localeCompare(bName);
                        case 'desc':
                            return bName.localeCompare(aName);
                        case 'latest':
                            // Latest to oldest - items with higher date first
                            const aDate = parseInt($a.data('item-date')) || 0;
                            const bDate = parseInt($b.data('item-date')) || 0;
                            // If dates are equal, sort by name
                            if (aDate === bDate) {
                                return aName.localeCompare(bName);
                            }
                            return bDate - aDate;
                        case 'oldest':
                            // Oldest to latest - items with lower date first
                            const aDateOld = parseInt($a.data('item-date')) || 0;
                            const bDateOld = parseInt($b.data('item-date')) || 0;
                            // If dates are equal, sort by name
                            if (aDateOld === bDateOld) {
                                return aName.localeCompare(bName);
                            }
                            return aDateOld - bDateOld;
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

        /**
         * Delete file handler
         * 
         * Behavior: Shows confirmation modal before deleting file. On confirmation,
         * submits delete form. Uses form submission instead of AJAX for consistency.
         */
        $(document).on('click', '.delete-file-btn', function(e) {
            e.preventDefault();
            var $form = $(this).closest('.delete-file-form');
            var fileId = $form.data('file-id');
            var fileName = $form.data('file-name');
            
            showConfirmModal({
                message: 'Are you sure you want to delete the file "' + fileName + '"? This action cannot be undone.',
                title: 'Delete File',
                confirmText: 'Delete',
                cancelText: 'Cancel',
                danger: true,
                onConfirm: function() {
                    $form.submit();
                }
            });
        });

        /**
         * Delete folder handler
         * 
         * Behavior: Shows confirmation modal before deleting folder. On confirmation,
         * sends AJAX request to delete folder and reloads page. Shows error snackbar on failure.
         */
        $(document).on('click', '.delete-folder-btn', function(e) {
            e.preventDefault();
            var folderId = $(this).data('folder-id');
            var folderName = $(this).closest('.list-group-item').find('strong').text();
            
            showConfirmModal({
                message: 'Are you sure you want to delete the folder "' + folderName + '"? This action cannot be undone.',
                title: 'Delete Folder',
                confirmText: 'Delete',
                cancelText: 'Cancel',
                danger: true,
                onConfirm: function() {
                    var url = '<?= BASE_PATH ?>lm/deleteFolder';
                    var data = {
                        folder_id: folderId
                    };
                    $.ajax({ 
                        url: url, 
                        type: 'POST', 
                        data: data,
                        dataType: 'json',
                        success: function(response) { 
                            if (response.success) {
                                showSnackbar(response.message, 'success');
                                setTimeout(function() {
                                    location.reload();
                                }, 500);
                            } else {
                                showSnackbar(response.message || 'Failed to delete folder.', 'error');
                            }
                        },
                        error: function() { 
                            showSnackbar('An error occurred during the deletion.', 'error');
                        }
                    });
                }
            });
        });

            /**
             * Drag and Drop functionality
             * 
             * Behavior: Enables drag-and-drop to move files and folders between folders.
             * Provides visual feedback during drag and handles drop with validation.
             */
            let draggedElement = null;
            let currentFolderId = <?php echo isset($_GET['folder_id']) ? (int)$_GET['folder_id'] : 0; ?>;

            /**
             * Drag start handler
             * 
             * Behavior: Marks element as being dragged, adds visual class, and sets
             * drag data transfer effect and HTML data.
             */
            $(document).on('dragstart', '.list-group-item[draggable="true"]', function(e) {
                draggedElement = this;
                $(this).addClass('dragging');
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                e.originalEvent.dataTransfer.setData('text/html', this.outerHTML);
            });

            /**
             * Drag end handler
             * 
             * Behavior: Removes dragging visual class and clears drag-over highlights
             * from all items when drag operation ends.
             */
            $(document).on('dragend', '.list-group-item[draggable="true"]', function(e) {
                $(this).removeClass('dragging');
                $('.list-group-item').removeClass('drag-over');
            });

            /**
             * Drag over handler
             * 
             * Behavior: Prevents default behavior, sets drop effect, and adds visual
             * highlight to valid drop targets (excluding dragged element itself).
             */
            $(document).on('dragover', '.list-group-item[draggable="true"]', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.originalEvent.dataTransfer.dropEffect = 'move';

                // Don't highlight if dragging over itself
                if (this !== draggedElement) {
                    $(this).addClass('drag-over');
                }
            });

            /**
             * Drag leave handler
             * 
             * Behavior: Removes drag-over highlight when drag leaves a drop target.
             */
            $(document).on('dragleave', '.list-group-item[draggable="true"]', function(e) {
                $(this).removeClass('drag-over');
            });

            /**
             * Drop handler
             * 
             * Behavior: Handles file/folder drop operation. Validates drop target,
             * prevents invalid moves (non-folder targets, self-move), shows confirmation
             * modal, and performs move via AJAX. Shows error snackbar on validation failure.
             */
            $(document).on('drop', '.list-group-item[draggable="true"]', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (draggedElement === null || this === draggedElement) {
                    return;
                }

                $(this).removeClass('drag-over');

                const draggedItemId = $(draggedElement).data('item-id');
                const draggedItemType = $(draggedElement).data('item-type');
                const draggedItemName = $(draggedElement).find('strong').text();
                const targetItemId = $(this).data('item-id');
                const targetItemType = $(this).data('item-type');
                const targetItemName = $(this).find('strong').text();

                // Only allow dropping on folders
                if (targetItemType !== 'folder') {
                    showSnackbar('You can only move items into folders.', 'error');
                    return;
                }

                // Prevent moving folder into itself
                if (draggedItemType === 'folder' && draggedItemId === targetItemId) {
                    showSnackbar('Cannot move folder into itself.', 'error');
                    return;
                }

                // Show confirmation modal before moving
                showConfirmModal({
                    message: 'Move "' + draggedItemName + '" to "' + targetItemName + '"?',
                    title: 'Move ' + (draggedItemType === 'folder' ? 'Folder' : 'File'),
                    confirmText: 'Move',
                    cancelText: 'Cancel',
                    onConfirm: function() {
                        // Perform move via AJAX
                        const url = draggedItemType === 'folder' ?
                            '<?= BASE_PATH ?>lm/moveFolder' :
                            '<?= BASE_PATH ?>lm/moveFile';
                        const data = draggedItemType === 'folder' ?
                            {
                                folderId: draggedItemId,
                                newFolderId: targetItemId
                            } :
                            {
                                fileId: draggedItemId,
                                newFolderId: targetItemId
                            };

                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: data,
                            dataType: 'json',
                            success: function(response) {
                                location.reload();
                            },
                            error: function() {
                                showSnackbar('An error occurred during the move.', 'error');
                            }
                        });
                    }
                });
            });

            /**
             * Bulk actions functionality
             * 
             * Behavior: Shows/hides bulk actions dropdown based on checkbox selection,
             * handles bulk delete and bulk move operations.
             */
            
            // Show/hide bulk actions dropdown based on checkbox selection
            $(document).on('change', '.bulk-select-checkbox', function() {
                const checkedCount = $('.bulk-select-checkbox:checked').length;
                if (checkedCount > 0) {
                    $('#bulkActionsDropdown').show();
                } else {
                    $('#bulkActionsDropdown').hide();
                }
            });

            // Select all checkbox functionality (if needed in future)
            // For now, we'll just handle individual checkboxes

            /**
             * Get selected items for bulk operations
             * 
             * Behavior: Collects all checked items and returns them as arrays
             * separated by type (folders and files).
             */
            function getSelectedItems() {
                const folders = [];
                const files = [];
                
                $('.bulk-select-checkbox:checked').each(function() {
                    const itemId = $(this).data('item-id');
                    const itemType = $(this).data('item-type');
                    
                    if (itemType === 'folder') {
                        folders.push(itemId);
                    } else if (itemType === 'file') {
                        files.push(itemId);
                    }
                });
                
                return { folders: folders, files: files };
            }

            /**
             * Bulk delete handler
             * 
             * Behavior: Shows confirmation modal, then deletes all selected items
             * via AJAX. Reloads page on success.
             */
            $('#bulkDeleteBtn').on('click', function(e) {
                e.preventDefault();
                const selected = getSelectedItems();
                const totalCount = selected.folders.length + selected.files.length;
                
                if (totalCount === 0) {
                    showSnackbar('Please select at least one item to delete.', 'error');
                    return;
                }
                
                const message = 'Are you sure you want to delete ' + totalCount + 
                    (totalCount === 1 ? ' item' : ' items') + 
                    '? This action cannot be undone.';
                
                showConfirmModal({
                    message: message,
                    title: 'Delete Selected Items',
                    confirmText: 'Delete',
                    cancelText: 'Cancel',
                    danger: true,
                    onConfirm: function() {
                        // Delete folders first
                        const folderPromises = selected.folders.map(function(folderId) {
                            return $.ajax({
                                url: '<?= BASE_PATH ?>lm/deleteFolder',
                                type: 'POST',
                                data: { folder_id: folderId },
                                dataType: 'json'
                            });
                        });
                        
                        // Delete files
                        const filePromises = selected.files.map(function(fileId) {
                            return $.ajax({
                                url: '<?= BASE_PATH ?>lm/bulkDeleteFile',
                                type: 'POST',
                                data: { fileId: fileId },
                                dataType: 'json'
                            });
                        });
                        
                        // Wait for all deletions to complete
                        Promise.all([...folderPromises, ...filePromises])
                            .then(function() {
                                showSnackbar(totalCount + ' item(s) deleted successfully.', 'success');
                                setTimeout(function() {
                                    location.reload();
                                }, 500);
                            })
                            .catch(function() {
                                showSnackbar('Some items could not be deleted.', 'error');
                                setTimeout(function() {
                                    location.reload();
                                }, 1000);
                            });
                    }
                });
            });

            /**
             * Bulk move handler
             * 
             * Behavior: Opens bulk move modal to select destination folder,
             * then moves all selected items via AJAX.
             */
            $('#bulkMoveBtn').on('click', function(e) {
                e.preventDefault();
                const selected = getSelectedItems();
                const totalCount = selected.folders.length + selected.files.length;
                
                if (totalCount === 0) {
                    showSnackbar('Please select at least one item to move.', 'error');
                    return;
                }
                
                // Open bulk move modal
                $('#bulkMoveModal').modal('show');
            });

            /**
             * Bulk move folder selection handler
             * 
             * Behavior: Moves all selected items to the chosen destination folder.
             */
            $('#bulkMoveModal').on('click', '.bulk-folder-item', function(e) {
                e.preventDefault();
                const targetFolderId = $(this).data('folder-id');
                const selected = getSelectedItems();
                const totalCount = selected.folders.length + selected.files.length;
                
                // Close modal
                $('#bulkMoveModal').modal('hide');
                
                // Move folders
                const folderPromises = selected.folders.map(function(folderId) {
                    return $.ajax({
                        url: '<?= BASE_PATH ?>lm/moveFolder',
                        type: 'POST',
                        data: {
                            folderId: folderId,
                            newFolderId: targetFolderId
                        },
                        dataType: 'json'
                    });
                });
                
                // Move files
                const filePromises = selected.files.map(function(fileId) {
                    return $.ajax({
                        url: '<?= BASE_PATH ?>lm/moveFile',
                        type: 'POST',
                        data: {
                            fileId: fileId,
                            newFolderId: targetFolderId
                        },
                        dataType: 'json'
                    });
                });
                
                // Wait for all moves to complete
                Promise.all([...folderPromises, ...filePromises])
                    .then(function() {
                        showSnackbar(totalCount + ' item(s) moved successfully.', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    })
                    .catch(function() {
                        showSnackbar('Some items could not be moved.', 'error');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    });
            });

    });
    
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
    </script>

    <!-- Content Navigation Tabs Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabButtons = document.querySelectorAll('.content-navbar .nav-link[data-tab]');
            const documentsSection = document.getElementById('documentsSection');
            const contentListsContainer = document.getElementById('contentListsContainer');
            
            // Ensure documents section is visible initially
            if (documentsSection) {
                documentsSection.style.display = 'block';
            }

            // Tab click handlers
            tabButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');
                    
                    // Update active tab
                    tabButtons.forEach(function(btn) {
                        btn.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    // Show/hide documents section
                    if (tabName === 'documents') {
                        // Show documents section
                        if (documentsSection) {
                            documentsSection.style.display = 'block';
                        }
                        // Hide content lists container
                        if (contentListsContainer) {
                            contentListsContainer.style.display = 'none';
                        }
                        // Hide all individual content lists
                        document.querySelectorAll('.content-list-container').forEach(function(list) {
                            list.classList.remove('active');
                        });
                    } else {
                        // Hide documents section completely
                        if (documentsSection) {
                            documentsSection.style.display = 'none';
                        }
                        // Show content lists container
                        if (contentListsContainer) {
                            contentListsContainer.style.display = 'block';
                        }
                        
                        // Hide all lists first
                        document.querySelectorAll('.content-list-container').forEach(function(list) {
                            list.classList.remove('active');
                        });
                        
                        // Show and load selected list
                        loadContentList(tabName);
                    }
                });
            });

            function loadContentList(type) {
                const listContainer = document.getElementById(type + 'List');
                if (!listContainer) return;
                
                // Show loading state
                listContainer.classList.add('active');
                listContainer.innerHTML = '<div class="content-list-empty"><i class="bi bi-hourglass-split"></i><p>Loading...</p></div>';
                
                // Determine endpoint and route
                let endpoint = '';
                let route = '';
                
                switch(type) {
                    case 'summaries':
                        endpoint = '<?= BASE_PATH ?>index.php?url=lm/getAllSummaries';
                        route = '<?= BASE_PATH ?>index.php?url=lm/summary';
                        break;
                    case 'notes':
                        endpoint = '<?= BASE_PATH ?>index.php?url=lm/getAllNotes';
                        route = '<?= BASE_PATH ?>index.php?url=lm/note';
                        break;
                    case 'mindmaps':
                        endpoint = '<?= BASE_PATH ?>index.php?url=lm/getAllMindmaps';
                        route = '<?= BASE_PATH ?>index.php?url=lm/mindmap';
                        break;
                    case 'flashcards':
                        endpoint = '<?= BASE_PATH ?>index.php?url=lm/getAllFlashcards';
                        route = '<?= BASE_PATH ?>index.php?url=lm/flashcard';
                        break;
                    case 'quizzes':
                        endpoint = '<?= BASE_PATH ?>index.php?url=lm/getAllQuizzes';
                        route = '<?= BASE_PATH ?>index.php?url=lm/quiz';
                        break;
                }
                
                // Fetch data
                fetch(endpoint)
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success && data.data && data.data.length > 0) {
                            renderContentList(listContainer, data.data, type, route);
                        } else {
                            listContainer.innerHTML = '<div class="content-list-empty"><i class="bi bi-inbox"></i><p>No ' + type + ' found</p></div>';
                        }
                    })
                    .catch(function(error) {
                        console.error('Error loading ' + type + ':', error);
                        listContainer.innerHTML = '<div class="content-list-empty"><i class="bi bi-exclamation-triangle"></i><p>Error loading ' + type + '</p></div>';
                    });
            }

            function renderContentList(container, items, type, route) {
                let html = '';
                
                items.forEach(function(item) {
                    const fileId = item.fileID || item.file_id;
                    const fileName = item.fileName || 'Unknown Document';
                    let title = '';
                    let meta = '';
                    let icon = '';
                    
                    switch(type) {
                        case 'summaries':
                            title = item.title || 'Untitled Summary';
                            meta = fileName;
                            icon = 'bi-file-text';
                            break;
                        case 'notes':
                            title = item.title || 'Untitled Note';
                            meta = fileName;
                            icon = 'bi-journal-text';
                            break;
                        case 'mindmaps':
                            title = item.title || 'Untitled Mindmap';
                            meta = fileName;
                            icon = 'bi-diagram-3';
                            break;
                        case 'flashcards':
                            title = item.title || 'Untitled Flashcard';
                            meta = fileName + (item.cardCount ? ' • ' + item.cardCount + ' cards' : '');
                            icon = 'bi-card-text';
                            break;
                        case 'quizzes':
                            title = item.title || 'Untitled Quiz';
                            meta = fileName;
                            icon = 'bi-question-circle';
                            break;
                    }
                    
                    const createdAt = item.createdAt ? new Date(item.createdAt).toLocaleDateString() : '';
                    
                    html += '<div class="content-list-item" data-file-id="' + fileId + '">';
                    html += '<div class="content-list-item-info">';
                    html += '<div class="content-list-item-title">';
                    html += escapeHtml(title);
                    
                    // Add badges for quizzes (status and exam mode)
                    if (type === 'quizzes') {
                        const status = item.status || 'pending';
                        const examMode = item.examMode || 0;
                        const isCompleted = status === 'completed';
                        const isExamMode = examMode === 1 || examMode === '1';
                        
                        html += '<div class="d-flex align-items-center gap-2 mt-2 flex-wrap">';
                        if (isExamMode) {
                            html += '<span class="badge rounded-pill bg-warning text-dark">Exam Mode</span>';
                        }
                        html += '<span class="badge rounded-pill ' + (isCompleted ? 'bg-success' : 'bg-secondary') + '">';
                        html += isCompleted ? 'Completed' : 'Pending';
                        html += '</span>';
                        html += '</div>';
                    }
                    
                    html += '</div>';
                    html += '<div class="content-list-item-meta">';
                    html += '<span><i class="bi bi-file-earmark me-1"></i>' + escapeHtml(meta) + '</span>';
                    if (createdAt) {
                        html += '<span><i class="bi bi-calendar me-1"></i>' + createdAt + '</span>';
                    }
                    html += '</div>';
                    html += '</div>';
                    html += '<div class="content-list-item-icon"><i class="bi ' + icon + '"></i></div>';
                    html += '</div>';
                });
                
                container.innerHTML = html;
                
                // Add click handlers
                container.querySelectorAll('.content-list-item').forEach(function(item) {
                    item.addEventListener('click', function() {
                        const fileId = this.getAttribute('data-file-id');
                        if (fileId) {
                            // Navigate to the specific page with file_id
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = route;
                            
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'file_id';
                            input.value = fileId;
                            
                            form.appendChild(input);
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        });
    </script>
    <?php include VIEW_CONFIRM; ?>
</body>

</html>
<?php ob_end_flush(); ?>