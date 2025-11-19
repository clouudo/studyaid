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
        :root {
            --sa-primary: #6f42c1;
            --sa-primary-dark: #593093;
            --sa-accent: #e7d5ff;
            --sa-accent-strong: #d4b5ff;
            --sa-muted: #6c757d;
            --sa-card-border: #ede1ff;
        }

        body {
            background-color: #f8f9fa;
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

        .card-header h5 {
            color: inherit;
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
            background-color: var(--sa-accent-strong);
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 0;
        }

        .breadcrumb-item a {
            color: var(--sa-primary);
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: var(--sa-primary-dark);
            text-decoration: underline;
        }

        .breadcrumb-item.active {
            color: #495057;
        }

        .search-container {
            margin-bottom: 20px;
        }

        .form-control-search {
            background-color: var(--sa-accent);
            border: none;
            border-radius: 12px;
            padding: 12px 16px;
            color: #212529;
        }

        .form-control-search:focus {
            background-color: var(--sa-accent);
            border: 2px solid var(--sa-primary);
            box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
            color: #212529;
        }

        .btn-search {
            background-color: var(--sa-primary);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 8px 18px rgba(111, 66, 193, 0.2);
        }

        .btn-search:hover {
            background-color: var(--sa-primary-dark);
            color: white;
        }

        .list-group-item {
            background-color: white;
            border: 1px solid var(--sa-card-border);
            border-radius: 12px;
            margin-bottom: 8px;
            padding: 16px;
            transition: all 0.2s;
        }

        .list-group-item:hover {
            background-color: #f8f9fa;
            border-color: var(--sa-accent-strong);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.1);
        }

        .list-group-item.dragging {
            opacity: 0.5;
            border: 2px dashed var(--sa-primary);
        }

        .list-group-item.drag-over {
            background-color: var(--sa-accent);
            border: 2px solid var(--sa-primary);
        }

        .file-folder-link {
            color: #212529;
            text-decoration: none;
            display: flex;
            align-items: center;
            flex-grow: 1;
        }

        .file-folder-link:hover {
            color: var(--sa-primary);
        }

        .file-folder-link i {
            color: var(--sa-primary);
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
            background-color: var(--sa-accent);
            color: var(--sa-primary);
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
            color: var(--sa-primary);
            background-color: var(--sa-accent);
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
            border: 1px solid var(--sa-accent-strong) !important;
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
            background-color: var(--sa-accent) !important;
            color: var(--sa-primary-dark) !important;
        }

        .dropdown-item:active,
        .dropdown-item.active,
        .dropdown-item:focus {
            background-color: var(--sa-accent-strong) !important;
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
            background-color: var(--sa-accent-strong) !important;
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
            background-color: var(--sa-accent-strong) !important;
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
            color: var(--sa-accent-strong);
            margin-bottom: 16px;
        }

        .modal-close-btn:hover {
            background-color: var(--sa-primary) !important;
            color: white !important;
        }

        .btn-cancel:hover {
            background-color: var(--sa-accent-strong) !important;
            color: var(--sa-primary-dark) !important;
        }

        .btn-create:hover {
            background-color: var(--sa-primary) !important;
            color: white !important;
        }

        .folder-item:hover {
            background-color: var(--sa-accent) !important;
            color: var(--sa-primary-dark) !important;
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
                    <h4 style="color: #212529; font-size: 1.25rem; font-weight: 500; margin-bottom: 20px; cursor: pointer; display: inline-block;" onclick="this.closest('form').submit();"><?php echo htmlspecialchars($file['name'] ?? 'Document'); ?></h4>
                </form>
                <?php require_once VIEW_NAVBAR; ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Generate Note with AI</h5>
                    </div>
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
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="toolbar-image"><i class="bi bi-image"></i></button>
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
                                                        <form method="POST" action="<?= SAVE_NOTE_AS_FILE ?>" style="display: inline;" class="save-note-as-file-form" data-note-id="<?= htmlspecialchars($note['noteID']) ?>" data-note-title="<?= htmlspecialchars($note['title']) ?>">
                                                            <input type="hidden" name="note_id" value="<?= htmlspecialchars($note['noteID']) ?>">
                                                            <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                            <button type="button" class="dropdown-item save-note-as-file-btn" style="border: none; background: none; width: 100%; text-align: left;">Save as File</button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" action="<?= DELETE_NOTE ?>" style="display: inline;" class="delete-note-form" data-note-id="<?= htmlspecialchars($note['noteID']) ?>" data-file-id="<?= htmlspecialchars($file['fileID']) ?>">
                                                            <input type="hidden" name="note_id" value="<?= htmlspecialchars($note['noteID']) ?>">
                                                            <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                            <button type="button" class="dropdown-item delete-note-btn" style="border: none; background: none; width: 100%; text-align: left;">Delete</button>
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
                                                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="toolbar-image" data-note-id="<?= htmlspecialchars($note['noteID']) ?>" title="Image"><i class="bi bi-image"></i></button>
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

    <!-- Image Upload Modal -->
    <div class="modal fade" id="imageUploadModal" tabindex="-1" aria-labelledby="imageUploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #A855F7; color: white;">
                    <h5 class="modal-title" id="imageUploadModalLabel">
                        <i class="bi bi-image me-2"></i>Upload Image
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="imageUploadForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="imageFileInput" class="form-label">Select Image</label>
                            <input type="file" class="form-control" id="imageFileInput" name="image" accept="image/*" required>
                            <small class="form-text text-muted">Supported formats: JPG, PNG, GIF, WEBP, BMP (Max 10MB)</small>
                        </div>
                        <div class="mb-3">
                            <label for="imageAltText" class="form-label">Alt Text (optional)</label>
                            <input type="text" class="form-control" id="imageAltText" placeholder="Describe the image">
                            <small class="form-text text-muted">This will be used as the image alt text in markdown</small>
                        </div>
                        <div id="imagePreviewContainer" class="mb-3" style="display: none;">
                            <label class="form-label">Preview</label>
                            <div class="border rounded p-2 text-center">
                                <img id="imagePreview" src="" alt="Preview">
                            </div>
                        </div>
                        <div id="imageUploadError" class="alert alert-danger" style="display: none;"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="uploadImageBtn" style="background-color: #A855F7; border: none;">
                        <i class="bi bi-upload me-2"></i>Upload & Insert
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        // Main note editor toolbar and preview functionality
        document.addEventListener('DOMContentLoaded', function() {
            const editor = document.getElementById('noteContent');
            const preview = document.getElementById('preview');
            const toolbar = document.getElementById('toolbar');

            // Only initialize if all elements exist (main editor, not inline editors)
            if (!editor || !preview || !toolbar) return;

            function debounce(fn, ms) {
                let id;
                return (...args) => {
                    clearTimeout(id);
                    id = setTimeout(() => fn(...args), ms);
                }
            }

            function render() {
                const raw = editor.value || '';
                const html = marked.parse(raw, { gfm: true, breaks: true });
                preview.innerHTML = DOMPurify.sanitize(html);
            }

            const debounceRender = debounce(render, 100);
            editor.addEventListener('input', debounceRender);
            render();

            function getSelection() {
                return {
                    start: editor.selectionStart,
                    end: editor.selectionEnd,
                    value: editor.value
                };
            }

            function setSelection(start, end) {
                editor.selectionStart = start;
                editor.selectionEnd = end;
                editor.focus();
            }

            function replaceSelection(transform) {
                const { start, end, value } = getSelection();
                const before = value.slice(0, start);
                const selected = value.slice(start, end);
                const after = value.slice(end);

                const { text, cursorStart, cursorEnd } = transform(selected);
                editor.value = before + text + after;
                setSelection(before.length + cursorStart, before.length + cursorEnd);
                render();
            }

            function wrapInline(wrapperLeft, wrapperRight = wrapperLeft) {
                return (selected) => {
                    const text = (selected && selected.length) ? selected : 'text';
                    const wrapped = `${wrapperLeft}${text}${wrapperRight}`;
                    return {
                        text: wrapped, cursorStart: wrapperLeft.length, cursorEnd: wrapperLeft.length + text.length
                    };
                };
            }

            function toggleHeading(level = 1) {
                const prefix = '#'.repeat(level) + ' ';
                return (selected) => {
                    const text = selected || 'Heading';
                    const lines = text.split('\n').map(line => {
                        const clean = line.replace(/^(#{1,6}\s+)?/, '');
                        return prefix + clean;
                    });
                    const out = lines.join('\n');
                    return { text: out, cursorStart: 0, cursorEnd: out.length };
                };
            }

            function prefixLines(prefix, defaultText = 'List item'){
                return (selected) => {
                    const text = selected || defaultText;
                    const out = text.split('\n').map(l => ( l ? `${prefix} ${l}` : `${prefix} `)).join('\n');
                    return { text: out, cursorStart: 0, cursorEnd: out.length };
                };
            }

            function orderedList(defaultText = 'First item\nSecond item'){
                return (selected) =>{
                    const text = selected || defaultText;
                    const out = text.split('\n').map((l, i) => `${i + 1}. ${l || ''}`).join('\n');
                    return { text: out, cursorStart: 0, cursorEnd: out.length };
                }
            }

            const undoStack = [];
            const redoStack = [];
            let lastSnapshot = editor.value;

            function snapshot(){
                if (editor.value !== lastSnapshot) {
                    undoStack.push(lastSnapshot);
                    lastSnapshot = editor.value;
                    redoStack.length = 0;
                }
            }

            editor.addEventListener('input', snapshot);

            function undo(){
                if (!undoStack.length) return;
                redoStack.push(lastSnapshot);
                editor.value = undoStack.pop();
                lastSnapshot = editor.value;
                render();
            }

            function redo(){
                if (!redoStack.length) return;
                undoStack.push(lastSnapshot);
                editor.value = redoStack.pop();
                lastSnapshot = editor.value;
                render();
            }

            const buttons = toolbar.querySelectorAll('button');
            if (buttons.length >= 7) {
                buttons[0].addEventListener('click', undo);
                buttons[1].addEventListener('click', redo);
                buttons[2].addEventListener('click', () => replaceSelection(wrapInline('**', '**')));
                buttons[3].addEventListener('click', () => replaceSelection(wrapInline('*', '*')));
                buttons[4].addEventListener('click', () => replaceSelection(toggleHeading(1)));
                buttons[5].addEventListener('click', () => replaceSelection(prefixLines('-')));
                buttons[6].addEventListener('click', () => replaceSelection(orderedList()));
                // Image button (index 7) is handled separately in the image upload handler below
            }

            editor.addEventListener('keydown', (e) => {
                const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
                const ctrl = isMac ? e.metaKey : e.ctrlKey;

                if (ctrl && e.key.toLowerCase() === 'b') {
                    e.preventDefault();
                    replaceSelection(wrapInline('**', '**'));
                } else if (ctrl && e.key.toLowerCase() === 'i') {
                    e.preventDefault();
                    replaceSelection(wrapInline('*', '*'));
                }
            });
        });
    </script>
    <style>
        /* Prevent dropdowns from being clipped by list container */
        .list-group-item { 
            overflow: visible; 
        }
        .dropdown-menu { 
            z-index: 1060; 
        }

        /* Split-view editor styles */
        .note-split-container {
            display: flex;
            flex-direction: row;
            gap: 0;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            min-height: 400px;
            max-height: 600px;
        }

        .note-split-container.single-view .note-preview-panel {
            display: none !important;
        }

        .note-split-container.single-view .note-editor-panel {
            width: 100% !important;
            border-right: none !important;
        }

        .note-editor-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            width: 50%;
            border-right: 1px solid #dee2e6;
            background-color: #ffffff;
            min-height: 400px;
        }

        .note-preview-panel {
            flex: 1;
            width: 50%;
            background-color: #f8f9fa;
            padding: 16px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .note-preview-panel .text-muted {
            color: #adb5bd;
            font-style: italic;
            text-align: center;
            padding: 2rem 0;
        }

        .note-editor-header {
            padding: 12px 16px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            font-size: 0.875rem;
            color: #495057;
        }

        .note-editor-footer {
            padding: 8px 16px;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 0.75rem;
        }

        .note-editor-panel .btn-toolbar {
            padding: 8px 16px;
            background-color: #ffffff;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 0;
        }

        .note-editor-panel textarea.note-edit-content {
            flex: 1;
            border: none;
            border-radius: 0;
            padding: 16px;
            resize: none;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.6;
            min-height: 300px;
            height: auto;
            overflow-y: auto;
        }

        .note-editor-panel textarea.note-edit-content:focus {
            outline: none;
            box-shadow: none;
        }

        .note-preview-panel h1,
        .note-preview-panel h2,
        .note-preview-panel h3,
        .note-preview-panel h4,
        .note-preview-panel h5,
        .note-preview-panel h6 {
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .note-preview-panel p {
            margin-bottom: 1rem;
        }

        .note-preview-panel ul,
        .note-preview-panel ol {
            margin-bottom: 1rem;
            padding-left: 2rem;
        }

        .note-preview-panel code {
            background-color: #e9ecef;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }

        .note-preview-panel pre {
            background-color: #f8f9fa;
            padding: 12px;
            border-radius: 4px;
            overflow-x: auto;
            margin-bottom: 1rem;
        }

        .note-preview-panel pre code {
            background-color: transparent;
            padding: 0;
        }

        .note-preview-panel img {
            max-width: 50%;
            max-height: 300px;
            height: auto;
            border-radius: 4px;
            margin: 1rem 0;
        }

        /* Shrink images in main editor preview */
        #preview img {
            max-width: 50%;
            max-height: 300px;
            height: auto;
            border-radius: 4px;
            margin: 0.5rem 0;
        }

        /* Shrink images in saved note preview */
        .note-preview img {
            max-width: 50%;
            max-height: 300px;
            height: auto;
            border-radius: 4px;
            margin: 0.5rem 0;
        }

        /* Shrink image in upload modal preview */
        #imagePreview {
            max-width: 100%;
            max-height: 300px;
            width: auto;
            height: auto;
            border-radius: 4px;
            object-fit: contain;
        }

        .note-preview-panel blockquote {
            border-left: 4px solid #6f42c1;
            padding-left: 1rem;
            margin-left: 0;
            color: #6c757d;
            font-style: italic;
        }

        .view-toggle-active {
            background-color: #6f42c1 !important;
            color: white !important;
            border-color: #6f42c1 !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .note-split-container {
                flex-direction: column;
                min-height: auto;
                max-height: none;
            }

            .note-editor-panel,
            .note-preview-panel {
                width: 100% !important;
                border-right: none !important;
                border-bottom: 1px solid #dee2e6;
            }

            .note-preview-panel {
                max-height: 400px;
            }
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
            
            // Ensure image button has note-id attribute
            const imageBtn = editor.querySelector(`#toolbar-image[data-note-id="${noteId}"], .toolbar-image[data-note-id="${noteId}"]`);
            if (imageBtn && !imageBtn.dataset.noteId) {
                imageBtn.dataset.noteId = noteId;
            }
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

        // Image upload modal state
        let currentImageUploadNoteId = null;
        let currentImageUploadTextarea = null;
        let imageUploadModal = null;

        // Initialize image upload modal after DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const modalElement = document.getElementById('imageUploadModal');
            if (modalElement) {
                imageUploadModal = new bootstrap.Modal(modalElement);
            }
        });

        const imageFileInput = document.getElementById('imageFileInput');
        const imageAltTextInput = document.getElementById('imageAltText');
        const imagePreview = document.getElementById('imagePreview');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        const imageUploadError = document.getElementById('imageUploadError');
        const uploadImageBtn = document.getElementById('uploadImageBtn');

        // Handle image file selection preview
        imageFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp'];
                if (!validTypes.includes(file.type)) {
                    showImageError('Invalid file type. Please select an image file.');
                    return;
                }

                // Validate file size (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    showImageError('File size exceeds 10MB limit.');
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreviewContainer.style.display = 'block';
                    imageUploadError.style.display = 'none';
                    
                    // Auto-fill alt text if empty
                    if (!imageAltTextInput.value.trim()) {
                        imageAltTextInput.value = file.name.replace(/\.[^/.]+$/, '');
                    }
                };
                reader.readAsDataURL(file);
            } else {
                imagePreviewContainer.style.display = 'none';
            }
        });

        function showImageError(message) {
            imageUploadError.textContent = message;
            imageUploadError.style.display = 'block';
            imagePreviewContainer.style.display = 'none';
        }

        // Handle image button clicks
        document.addEventListener('click', function(e) {
            const imageBtn = e.target.closest('#toolbar-image, .toolbar-image');
            if (imageBtn) {
                e.preventDefault();
                e.stopPropagation();
                
                // Determine which note/editor we're working with
                const noteId = imageBtn.dataset.noteId;
                let textarea = null;
                
                if (noteId) {
                    // Inline editor for existing note
                    textarea = document.getElementById(`noteSplitEditor-${noteId}`);
                } else {
                    // Manual note creation editor
                    textarea = document.getElementById('noteContent');
                }
                
                if (!textarea) {
                    alert('Please open a note editor first.');
                    return;
                }
                
                currentImageUploadNoteId = noteId;
                currentImageUploadTextarea = textarea;
                
                // Reset form
                document.getElementById('imageUploadForm').reset();
                imagePreviewContainer.style.display = 'none';
                imageUploadError.style.display = 'none';
                
                // Open modal (ensure it's initialized)
                if (!imageUploadModal) {
                    const modalElement = document.getElementById('imageUploadModal');
                    if (modalElement) {
                        imageUploadModal = new bootstrap.Modal(modalElement);
                    }
                }
                if (imageUploadModal) {
                    imageUploadModal.show();
                }
            }
        });

        // Handle upload button click
        uploadImageBtn.addEventListener('click', async function() {
            const file = imageFileInput.files[0];
            if (!file) {
                showImageError('Please select an image file.');
                return;
            }

            if (!currentImageUploadTextarea) {
                showImageError('No editor found. Please try again.');
                return;
            }

            // Disable button and show loading
            uploadImageBtn.disabled = true;
            const originalText = uploadImageBtn.innerHTML;
            uploadImageBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';

            try {
                const formData = new FormData();
                formData.append('image', file);
                formData.append('file_id', CURRENT_FILE_ID);
                if (currentImageUploadNoteId) {
                    formData.append('note_id', currentImageUploadNoteId);
                }

                const response = await fetch('<?= UPLOAD_NOTE_IMAGE ?>', {
                    method: 'POST',
                    body: formData
                });

                const json = await response.json();

                if (!json.success) {
                    throw new Error(json.message || 'Failed to upload image');
                }

                // Insert markdown image syntax at cursor position
                const altText = imageAltTextInput.value.trim() || json.altText || 'Image';
                const imageMarkdown = `![${altText}](${json.imageUrl})`;
                
                // Insert at cursor position
                const start = currentImageUploadTextarea.selectionStart;
                const end = currentImageUploadTextarea.selectionEnd;
                const textBefore = currentImageUploadTextarea.value.substring(0, start);
                const textAfter = currentImageUploadTextarea.value.substring(end);
                const newText = textBefore + imageMarkdown + textAfter;
                
                currentImageUploadTextarea.value = newText;
                const newCursorPos = start + imageMarkdown.length;
                currentImageUploadTextarea.setSelectionRange(newCursorPos, newCursorPos);
                currentImageUploadTextarea.focus();
                
                // Trigger input event to update preview
                currentImageUploadTextarea.dispatchEvent(new Event('input'));

                // Close modal and reset
                imageUploadModal.hide();
                document.getElementById('imageUploadForm').reset();
                imagePreviewContainer.style.display = 'none';
                imageUploadError.style.display = 'none';
                currentImageUploadNoteId = null;
                currentImageUploadTextarea = null;

            } catch (error) {
                console.error('Image upload error:', error);
                showImageError('Error uploading image: ' + error.message);
            } finally {
                uploadImageBtn.disabled = false;
                uploadImageBtn.innerHTML = originalText;
            }
        });

        // Reset modal state when closed
        document.getElementById('imageUploadModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('imageUploadForm').reset();
            imagePreviewContainer.style.display = 'none';
            imageUploadError.style.display = 'none';
            currentImageUploadNoteId = null;
            currentImageUploadTextarea = null;
        });

        /**
         * Delete note handler
         * 
         * Behavior: Shows confirmation modal before deleting note. On confirmation,
         * submits delete form.
         */
        $(document).on('click', '.delete-note-btn', function(e){
            e.preventDefault();
            var $form = $(this).closest('.delete-note-form');
            var noteId = $form.data('note-id');
            var noteTitle = $form.closest('.note-item').find('.note-title-text').text();

            showConfirmModal({
                message: 'Are you sure you want to delete the note "' + noteTitle + '"? This action cannot be undone.',
                title: 'Delete Note',
                confirmText: 'Delete',
                cancelText: 'Cancel',
                danger: true,
                onConfirm: function() {
                    $form.submit();
                }
            });
        });

        /**
         * Save note as file handler
         * 
         * Behavior: Shows confirmation modal before saving note as file. On confirmation,
         * submits the form to save the note as a new file.
         */
        $(document).on('click', '.save-note-as-file-btn', function(e){
            e.preventDefault();
            e.stopPropagation();
            var $form = $(this).closest('.save-note-as-file-form');
            var noteTitle = $form.data('note-title');

            showConfirmModal({
                message: 'Are you sure you want to save the note "' + noteTitle + '" as a new file?',
                title: 'Save Note as File',
                confirmText: 'Save',
                cancelText: 'Cancel',
                danger: false,
                onConfirm: function() {
                    $form.submit();
                }
            });
        });
    </script>
    <?php include VIEW_CONFIRM; ?>
</body>

</html>