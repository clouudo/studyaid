<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extracted Text - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
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
                <h3 style="color: #212529; font-size: 1.5rem; font-weight: 600; margin-bottom: 30px;">Summary</h3>
                <form method="POST" action="<?= DISPLAY_DOCUMENT ?>" style="display: inline;">
                    <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
                    <h4 style="color: #212529; font-size: 1.25rem; font-weight: 500; margin-bottom: 20px; cursor: pointer; display: inline-block;" onclick="this.closest('form').submit();"><?php echo htmlspecialchars($file['name'] ?? 'Document'); ?></h4>
                </form>
                <?php require_once VIEW_NAVBAR; ?>
                <?php
                $hasExtractedText = isset($file['extracted_text']) && !empty(trim($file['extracted_text'] ?? ''));
                ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Generate Summary with AI</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!$hasExtractedText): ?>
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-exclamation-triangle"></i> This document has no extracted text. AI tools are not available.
                            </div>
                        <?php endif; ?>
                        <form id="generateSummaryForm" action="<?= GENERATE_SUMMARY ?>" method="POST" data-action="<?= GENERATE_SUMMARY ?>">
                            <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
                            <label for="instructions" class="form-label">Instructions (optional)</label>
                            <input type="text" class="form-control mb-3" id="instructions" name="instructions" placeholder="Describe your instructions" <?php echo !$hasExtractedText ? 'disabled' : ''; ?>>
                            <button type="submit" id="genSummary" class="btn btn-primary" <?php echo !$hasExtractedText ? 'disabled' : ''; ?> style="<?php echo !$hasExtractedText ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>">Generate Summary</button>
                        </form>
                    </div>
                </div>
                    <!-- Saved Summaries -->
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Generated Summaries</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" id="summaryList">
                            <?php if ($summaryList): ?>
                                <?php foreach ($summaryList as $summary): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="flex-grow-1">
                                                <strong><?= htmlspecialchars($summary['title']) ?></strong><br>
                                                <small class="text-muted">Created: <?= htmlspecialchars($summary['createdAt'] ?? '') ?></small>
                                            </div>
                                            <div class="dropdown">
                                                <button class="action-btn"
                                                    type="button"
                                                    id="dropdownSummaryActions<?php echo $summary['summaryID']; ?>"
                                                    data-bs-toggle="dropdown"
                                                    data-bs-display="static"
                                                    aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownSummaryActions<?php echo $summary['summaryID']; ?>">
                                                    <li><a class="dropdown-item view-summary-btn" href="#" data-bs-toggle="collapse" data-bs-target="#summaryContent-<?php echo $summary['summaryID']; ?>">View</a></li>
                                                    <li><a class="dropdown-item audio-summary-btn" href="#" data-summary-id="<?= htmlspecialchars($summary['summaryID']) ?>">
                                                        <i class="bi bi-volume-up me-2"></i>Listen to Audio
                                                    </a></li>
                                                    <li><a class="dropdown-item export-summary-btn" href="#" data-export-type="pdf" data-summary-id="<?= htmlspecialchars($summary['summaryID']) ?>" data-file-id="<?= htmlspecialchars($file['fileID']) ?>">Export as PDF</a></li>
                                                    <li><a class="dropdown-item export-summary-btn" href="#" data-export-type="txt" data-summary-id="<?= htmlspecialchars($summary['summaryID']) ?>" data-file-id="<?= htmlspecialchars($file['fileID']) ?>">Export as TXT</a></li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <form method="POST" action="<?= SAVE_SUMMARY_AS_FILE ?>" style="display: inline;" class="save-summary-as-file-form" data-summary-id="<?= htmlspecialchars($summary['summaryID']) ?>" data-summary-title="<?= htmlspecialchars($summary['title']) ?>">
                                                            <input type="hidden" name="summary_id" value="<?= htmlspecialchars($summary['summaryID']) ?>">
                                                            <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                            <button type="button" class="dropdown-item save-summary-as-file-btn" style="border: none; background: none; width: 100%; text-align: left;">Save as File</button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" action="<?= DELETE_SUMMARY ?>" style="display: inline;" class="delete-summary-form" data-summary-id="<?= htmlspecialchars($summary['summaryID']) ?>" data-file-id="<?= htmlspecialchars($file['fileID']) ?>">
                                                            <input type="hidden" name="summary_id" value="<?= htmlspecialchars($summary['summaryID']) ?>">
                                                            <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                            <button type="button" class="dropdown-item delete-summary-btn" style="border: none; background: none; width: 100%; text-align: left;">Delete</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="collapse mt-2" id="summaryContent-<?php echo $summary['summaryID']; ?>">
                                            <div class="summaryContent border-top pt-2" style="white-space: pre-wrap;"><?php echo htmlspecialchars($summary['content']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <div class="list-group-item text-muted text-center">No generated summaries</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <style>
        /* Prevent dropdowns from being clipped by list container */
        .list-group-item { 
            overflow: visible; 
        }
        .dropdown-menu { 
            z-index: 1060; 
        }
        
        /* Audio highlighting styles */
        .summaryContent .audio-word {
            transition: background-color 0.2s ease, color 0.2s ease;
            padding: 2px 1px;
            border-radius: 3px;
            display: inline;
            line-height: inherit;
        }
        
        .summaryContent .audio-word.highlighted {
            background-color: #fff3cd;
            color: #856404;
            font-weight: 600;
        }
        
        .summaryContent .audio-word.current {
            background-color: #ffc107;
            color: #000;
            font-weight: 700;
            box-shadow: 0 0 8px rgba(255, 193, 7, 0.5);
        }
    </style>
    <script>
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

        /**
         * Delete summary handler
         * 
         * Behavior: Shows confirmation modal before deleting summary. On confirmation,
         * submits delete form.
         */
        $(document).on('click', '.delete-summary-btn', function(e){
            e.preventDefault();
            var $form = $(this).closest('.delete-summary-form');
            var summaryId = $form.data('summary-id');
            var summaryTitle = $form.closest('.list-group-item').find('strong').text();

            showConfirmModal({
                message: 'Are you sure you want to delete the summary "' + summaryTitle + '"? This action cannot be undone.',
                title: 'Delete Summary',
                confirmText: 'Delete',
                cancelText: 'Cancel',
                danger: true,
                onConfirm: function() {
                    $form.submit();
                }
            });
        });

        /**
         * Save summary as file handler
         * 
         * Behavior: Shows confirmation modal before saving summary as file. On confirmation,
         * submits the form to save the summary as a new file.
         */
        $(document).on('click', '.save-summary-as-file-btn', function(e){
            e.preventDefault();
            e.stopPropagation();
            var $form = $(this).closest('.save-summary-as-file-form');
            var summaryTitle = $form.data('summary-title');

            showConfirmModal({
                message: 'Are you sure you want to save the summary "' + summaryTitle + '" as a new file?',
                title: 'Save Summary as File',
                confirmText: 'Save',
                cancelText: 'Cancel',
                danger: false,
                onConfirm: function() {
                    $form.submit();
                }
            });
        });


        /**
         * Document ready handler - initializes summary page functionality
         * 
         * Behavior: Sets up form submission handlers, markdown parsing, and
         * event listeners for audio generation and export functionality.
         */
        document.addEventListener('DOMContentLoaded', function() {
            /**
             * Generate summary form submission handler
             * 
             * Behavior: Intercepts form submission, sends AJAX request to generateSummary
             * endpoint, shows loading state, and reloads page on success or shows error.
             */
            const generateSummaryForm = document.getElementById('generateSummaryForm');
            if (generateSummaryForm) {
                generateSummaryForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    const form = e.target;
                    const submitButton = form.querySelector('#genSummary');
                    const originalButtonText = submitButton.textContent;

                    // Disable button and show loading state
                    submitButton.disabled = true;
                    submitButton.textContent = 'Generating...';

                    try {
                        // Get the action URL - use data-action attribute or form action
                        let actionUrl = form.getAttribute('data-action') || form.action;
                        
                        // Convert routing URL to index.php format for fetch
                        // GENERATE_SUMMARY outputs: /studyaid/lm/generateSummary
                        // Need to convert to: /studyaid/index.php?url=lm/generateSummary
                        if (actionUrl.includes('/lm/')) {
                            // Extract the route part (lm/generateSummary)
                            const routeMatch = actionUrl.match(/\/lm\/(.+)$/);
                            if (routeMatch) {
                                actionUrl = '<?= BASE_PATH ?>index.php?url=lm/' + routeMatch[1];
                            }
                        }
                        
                        const formData = new FormData(form);
                        
                        const res = await fetch(actionUrl, {
                            method: 'POST',
                            body: formData
                        });

                        if (!res.ok) {
                            const errorText = await res.text();
                            throw new Error('Network response was not ok: ' + res.status + ' - ' + errorText.substring(0, 100));
                        }

                        const json = await res.json();

                        if (json.success) {
                            showSnackbar('Summary generated successfully!', 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        } else {
                            showSnackbar(json.message || 'Failed to generate summary. Please try again.', 'error');
                            submitButton.disabled = false;
                            submitButton.textContent = originalButtonText;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showSnackbar('An error occurred while generating the summary. Please try again.', 'error');
                        submitButton.disabled = false;
                        submitButton.textContent = originalButtonText;
                    }
                });
            }

            /**
             * Parse markdown for summary content
             * 
             * Behavior: Converts markdown text in summary content divs to HTML
             * using the marked.js library.
             */
            document.querySelectorAll('.summaryContent').forEach(function(div) {
                div.innerHTML = marked.parse(div.textContent);
            });

            /**
             * Audio summary button handler with text highlighting
             * 
             * Behavior: Generates audio for summary via AJAX, creates Audio element,
             * wraps text in spans for highlighting, and highlights words as audio plays.
             */
            // Handle audio summary buttons
            document.querySelectorAll('.audio-summary-btn').forEach(function(btn) {
                let audioElement = null;
                let isPlaying = false;
                
                btn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const summaryId = this.dataset.summaryId;
                    const originalText = this.innerHTML;
                    
                    // If audio is already created, toggle pause/play
                    if (audioElement) {
                        if (audioElement.paused) {
                            audioElement.play().catch(err => {
                                console.error('Error playing audio:', err);
                                alert('Error playing audio. Please check your browser settings.');
                            });
                        } else {
                            audioElement.pause();
                        }
                        return;
                    }
                    
                    // Find the summary content element
                    const summaryContentDiv = document.querySelector(`#summaryContent-${summaryId} .summaryContent`);
                    if (!summaryContentDiv) {
                        alert('Summary content not found. Please view the summary first.');
                        return;
                    }
                    
                    // Ensure summary is visible (expand collapse)
                    const collapseElement = document.getElementById(`summaryContent-${summaryId}`);
                    if (collapseElement && !collapseElement.classList.contains('show')) {
                        const bsCollapse = new bootstrap.Collapse(collapseElement, { toggle: true });
                    }
                    
                    // Show loading state
                    this.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Generating...';
                    this.style.pointerEvents = 'none';

                    try {
                        const formData = new FormData();
                        formData.append('summary_id', summaryId);
                        formData.append('file_id', '<?= htmlspecialchars($file['fileID']) ?>');

                        const response = await fetch('<?= AUDIO_SUMMARY ?>', {
                            method: 'POST',
                            body: formData
                        });

                        const json = await response.json();

                        if (!json.success) {
                            throw new Error(json.message || 'Failed to generate audio');
                        }

                        // Store original HTML if not already wrapped
                        if (!summaryContentDiv.dataset.originalHtml) {
                            summaryContentDiv.dataset.originalHtml = summaryContentDiv.innerHTML;
                        }
                        
                        // Wrap words in spans for highlighting (only if not already wrapped)
                        if (!summaryContentDiv.querySelector('.audio-word')) {
                            wrapWordsForHighlighting(summaryContentDiv);
                        }

                        // Create audio element
                        const audio = new Audio(json.audioUrl);
                        audioElement = audio; // Store reference
                        
                        // Set up highlighting
                        let wordSpans = summaryContentDiv.querySelectorAll('.audio-word');
                        let currentWordIndex = -1;
                        
                        // Function to highlight word based on audio time
                        function updateHighlight() {
                            // Don't update if audio is paused
                            if (audio.paused || !audio.duration || wordSpans.length === 0) return;
                            
                            // Delay highlighting by 1.25 seconds to better sync with audio stream
                            const delayedTime = Math.max(0, audio.currentTime - 1.25);
                            const progress = delayedTime / audio.duration;
                            const targetIndex = Math.floor(progress * wordSpans.length);
                            
                            if (targetIndex !== currentWordIndex && targetIndex < wordSpans.length) {
                                // Remove previous highlights
                                wordSpans.forEach(span => {
                                    span.classList.remove('current', 'highlighted');
                                });
                                
                                // Highlight current word
                                if (targetIndex >= 0) {
                                    wordSpans[targetIndex].classList.add('current');
                                    
                                    // Highlight previous words (fade effect)
                                    for (let i = Math.max(0, targetIndex - 5); i < targetIndex; i++) {
                                        wordSpans[i].classList.add('highlighted');
                                    }
                                    
                                    // Scroll to current word
                                    wordSpans[targetIndex].scrollIntoView({
                                        behavior: 'smooth',
                                        block: 'center'
                                    });
                                }
                                
                                currentWordIndex = targetIndex;
                            }
                        }
                        
                        // Update highlight on timeupdate
                        audio.addEventListener('timeupdate', updateHighlight);
                        
                        // Handle playback state changes
                        audio.addEventListener('play', () => {
                            this.innerHTML = '<i class="bi bi-pause-fill me-2"></i>Pause';
                            this.style.pointerEvents = 'auto';
                        });
                        
                        audio.addEventListener('pause', () => {
                            this.innerHTML = '<i class="bi bi-play-fill me-2"></i>Resume';
                            // Stop highlighting when paused
                            wordSpans.forEach(span => {
                                span.classList.remove('current', 'highlighted');
                            });
                        });
                        
                        // Reset highlights when audio ends
                        audio.addEventListener('ended', () => {
                            wordSpans.forEach(span => {
                                span.classList.remove('current', 'highlighted');
                            });
                            this.innerHTML = originalText;
                            this.style.pointerEvents = 'auto';
                            currentWordIndex = -1;
                            audioElement = null;
                            isPlaying = false;
                        });
                        
                        // Start playing
                        audio.play().catch(err => {
                            console.error('Error playing audio:', err);
                            alert('Error playing audio. Please check your browser settings.');
                            this.innerHTML = originalText;
                            this.style.pointerEvents = 'auto';
                            audioElement = null;
                        });
                        
                        // Clean up on error
                        audio.addEventListener('error', () => {
                            this.innerHTML = originalText;
                            this.style.pointerEvents = 'auto';
                            wordSpans.forEach(span => {
                                span.classList.remove('current', 'highlighted');
                            });
                            audioElement = null;
                            isPlaying = false;
                            alert('Error loading audio file.');
                        });

                    } catch (error) {
                        console.error('Audio error:', error);
                        alert('Error generating audio: ' + error.message);
                        this.innerHTML = originalText;
                        this.style.pointerEvents = 'auto';
                    }
                });
            });
            
            /**
             * Wraps words in spans for audio highlighting
             * Preserves HTML structure and markdown formatting
             */
            function wrapWordsForHighlighting(element) {
                let wordIndex = 0;
                
                // Recursively process all text nodes
                function processTextNode(textNode) {
                    const text = textNode.textContent;
                    if (!text.trim()) {
                        return; // Skip empty text nodes
                    }
                    
                    // Split text into words while preserving whitespace
                    const words = text.split(/(\s+)/);
                    const fragment = document.createDocumentFragment();
                    
                    words.forEach(word => {
                        if (word.trim() === '') {
                            // Preserve whitespace as text node
                            fragment.appendChild(document.createTextNode(word));
                        } else {
                            // Wrap word in span
                            const span = document.createElement('span');
                            span.className = 'audio-word';
                            span.setAttribute('data-word-index', wordIndex++);
                            span.textContent = word;
                            fragment.appendChild(span);
                        }
                    });
                    
                    // Replace the text node with the fragment
                    if (textNode.parentNode) {
                        textNode.parentNode.replaceChild(fragment, textNode);
                    }
                }
                
                // Traverse DOM tree and process all text nodes
                function traverse(node) {
                    const children = Array.from(node.childNodes);
                    
                    children.forEach(child => {
                        if (child.nodeType === Node.TEXT_NODE) {
                            // Process text node
                            processTextNode(child);
                        } else if (child.nodeType === Node.ELEMENT_NODE) {
                            // Skip script, style, and code/pre elements
                            const tagName = child.tagName.toLowerCase();
                            if (tagName === 'script' || tagName === 'style' || 
                                tagName === 'code' || tagName === 'pre') {
                                return;
                            }
                            // Recursively process child elements
                            traverse(child);
                        }
                    });
                }
                
                traverse(element);
            }

            /**
             * Export summary button handler
             * 
             * Behavior: Exports summary as PDF or TXT file. Downloads file
             * via blob URL. Handles errors and validates response content type.
             */
            // Handle export summary buttons
            document.querySelectorAll('.export-summary-btn').forEach(function(btn) {
                btn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const exportType = this.dataset.exportType;
                    const summaryId = this.dataset.summaryId;
                    const fileId = this.dataset.fileId;

                    let exportUrl = '';
                    if (exportType === 'pdf') {
                        exportUrl = '<?= EXPORT_SUMMARY_PDF ?>';
                    } else if (exportType === 'txt') {
                        exportUrl = '<?= EXPORT_SUMMARY_TXT ?>';
                    }
                    if (!exportUrl) {
                        showSnackbar('Invalid export type selected.', 'error');
                        return;
                    }

                    try {
                        const formData = new FormData();
                        formData.append('summary_id', summaryId);
                        formData.append('file_id', fileId);

                        const response = await fetch(exportUrl, {
                            method: 'POST',
                            body: formData
                        });

                        const contentType = response.headers.get('content-type') || '';
                        if (contentType.includes('text/html')) {
                            const text = await response.text();
                            showSnackbar('Export failed. Please check if the summary exists and try again.', 'error');
                            console.error('Export error response:', text);
                            return;
                        }
                        if (!response.ok) {
                            throw new Error('Export failed: ' + response.statusText);
                        }

                        const blob = await response.blob();
                        if (blob.size === 0) {
                            throw new Error('Empty file received from server');
                        }

                        let extension = exportType;
                        let filename = 'summary_' + summaryId;
                        if (exportType === 'pdf' && !blob.type.includes('pdf') && !contentType.includes('pdf')) {
                            throw new Error('Invalid PDF file received');
                        }

                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = filename + '.' + extension;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                        showSnackbar('Summary exported successfully!', 'success');
                    } catch (error) {
                        console.error('Export error:', error);
                        showSnackbar('Failed to export summary. Please try again.', 'error');
                    }
                });
            });
        });
    </script>
    <?php include VIEW_CONFIRM; ?>
</body>

</html>