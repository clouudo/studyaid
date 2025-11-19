<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mindmap - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css" />
    <style>
        #mindmap-container {
            width: 100%;
            min-height: 50px;
            border: 1px solid #ccc;
        }

        .markmap {
            width: 100%;
            height: 600px;
        }
        .upload-container {
            background-color: #f8f9fa;
            padding: 30px;
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

        /* Dropdown menu styling */
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

        .dropdown-menu li {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .dropdown-menu li + li {
            border-top: 1px solid #f0e6ff;
        }

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

        /* Split Screen Layout */
        .mindmap-split-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            min-height: 600px;
        }

        .mindmap-split-container.single-view {
            grid-template-columns: 1fr;
        }

        .mindmap-visual-panel {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            background-color: #ffffff;
            overflow: hidden;
        }

        .markdown-editor-panel {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
        }

        #markdownEditor {
            flex: 1;
            resize: none;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            border: none;
            padding: 1rem;
            outline: none;
        }

        .editor-header {
            padding: 0.75rem 1rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .editor-footer {
            padding: 0.5rem 1rem;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            font-size: 0.875rem;
            color: #6c757d;
        }

        .view-toggle-active {
            background-color: #A855F7 !important;
            color: white !important;
            border-color: #A855F7 !important;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app/views/sidebar.php'; ?>
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
            <div class="container-fluid upload-container">
                <h3 style="color: #212529; font-size: 1.5rem; font-weight: 600; margin-bottom: 30px;">Mindmap</h3>
                <h4 style="color: #212529; font-size: 1.25rem; font-weight: 500; margin-bottom: 20px;"><?php echo htmlspecialchars($file['name']); ?></h4>
                <?php require_once VIEW_NAVBAR; ?>

                <!-- Generate Mindmap Form -->
                <div class="card">
                    <div class="card-body">
                        <form id="mindmapForm" action="<?= GENERATE_MINDMAP ?>" method="POST">
                            <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">

                            <button type="submit" id="genMindmap" class="btn btn-primary" style="background-color: #A855F7; border: none;">Generate Mindmap</button>
                        </form>
                    </div>
                </div>

                <!-- Mindmap Display with Split Screen Editor -->
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3" id="mindmapToolbar" style="display: none;">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary" id="toggleViewBtn">Edit View</button>
                            </div>
                            <div class="ms-auto d-flex gap-2 align-items-center">
                                <button type="button" class="btn btn-sm" id="saveMindmapBtn" disabled style="background-color: #A855F7; border: none; color: white;">Save Changes</button>
                            </div>
                        </div>
                        <div id="mindmap-split-container" class="mindmap-split-container single-view" style="display: none;">
                            <!-- Visual Mindmap Panel -->
                            <div class="mindmap-visual-panel">
                                <div id="mindmap-container" style="height: 100%; min-height: 600px;">
                                    <!-- Mindmap will be injected here -->
                                </div>
                            </div>
                            <!-- Markdown Editor Panel -->
                            <div class="markdown-editor-panel" id="markdownEditorPanel" style="display: none;">
                                <div class="editor-header">
                                    <h6 class="mb-0">Markdown Editor</h6>
                                    <button type="button" class="btn btn-sm btn-outline-info" id="markdownInfoBtn" data-bs-toggle="modal" data-bs-target="#markdownInfoModal" title="Markdown Syntax Guide">
                                        <i class="bi bi-info-circle"></i> Info
                                    </button>
                                </div>
                                <textarea 
                                    id="markdownEditor" 
                                    placeholder="# Main Topic&#10;## Branch 1&#10;### Sub-branch 1.1&#10;## Branch 2"></textarea>
                                <div class="editor-footer">
                                    <small>Edit the markdown to see real-time updates in the visual. Click "Save Changes" to persist to database.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Saved Mindmaps -->
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Generated Mindmaps</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" id="mindmapList">
                            <?php if (!empty($mindmapList)) : ?>
                                <?php foreach ($mindmapList as $mindmap) : ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <strong><?= htmlspecialchars($mindmap['title']) ?></strong><br>
                                            <small class="text-muted">Updated: <?= htmlspecialchars($mindmap['createdAt']) ?></small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="action-btn"
                                                type="button"
                                                id="dropdownFileActions<?php echo $mindmap['mindmapID']; ?>"
                                                data-bs-toggle="dropdown"
                                                data-bs-display="static"
                                                aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownFileActions<?php echo $mindmap['mindmapID']; ?>">
                                                <li><a class="dropdown-item view-btn" href="#" data-id="<?= htmlspecialchars($mindmap['mindmapID']) ?>">View</a></li>
                                                <li><a class="dropdown-item export-mindmap-image-btn" href="#" data-id="<?= htmlspecialchars($mindmap['mindmapID']) ?>">Export as Image</a></li>
                                                <li><a class="dropdown-item export-mindmap-pdf-btn" href="#" data-id="<?= htmlspecialchars($mindmap['mindmapID']) ?>">Export as PDF</a></li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <form method="POST" action="<?= DELETE_MINDMAP ?>" style="display: inline;" class="delete-mindmap-form" data-mindmap-id="<?= htmlspecialchars($mindmap['mindmapID']) ?>" data-file-id="<?= htmlspecialchars($file['fileID']) ?>">
                                                        <input type="hidden" name="mindmap_id" value="<?= htmlspecialchars($mindmap['mindmapID']) ?>">
                                                        <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                        <button type="button" class="dropdown-item delete-mindmap-btn" style="border: none; background: none; width: 100%; text-align: left;">Delete</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <div class="list-group-item text-muted text-center">No generated mindmaps</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Markdown Syntax Info Modal -->
    <div class="modal fade" id="markdownInfoModal" tabindex="-1" aria-labelledby="markdownInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #A855F7; color: white;">
                    <h5 class="modal-title" id="markdownInfoModalLabel">
                        <i class="bi bi-info-circle me-2"></i>Markdown Syntax Guide for Mindmaps
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="lead">Use markdown headings to create your mindmap structure. The number of <code>#</code> symbols determines the level in the hierarchy.</p>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead style="background-color: #f8f9fa;">
                                <tr>
                                    <th>Symbol</th>
                                    <th>Level</th>
                                    <th>Description</th>
                                    <th>Example</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>#</code></td>
                                    <td><span class="badge bg-primary">Level 1</span></td>
                                    <td>Main topic (root node)</td>
                                    <td><code># Main Topic</code></td>
                                </tr>
                                <tr>
                                    <td><code>##</code></td>
                                    <td><span class="badge bg-info">Level 2</span></td>
                                    <td>Primary branches</td>
                                    <td><code>## Branch 1</code></td>
                                </tr>
                                <tr>
                                    <td><code>###</code></td>
                                    <td><span class="badge bg-success">Level 3</span></td>
                                    <td>Sub-branches</td>
                                    <td><code>### Sub-branch 1.1</code></td>
                                </tr>
                                <tr>
                                    <td><code>####</code></td>
                                    <td><span class="badge bg-warning">Level 4</span></td>
                                    <td>Deeper sub-branches</td>
                                    <td><code>#### Detail 1.1.1</code></td>
                                </tr>
                                <tr>
                                    <td><code>#####</code></td>
                                    <td><span class="badge bg-secondary">Level 5</span></td>
                                    <td>Even deeper levels</td>
                                    <td><code>##### Sub-detail</code></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3">
                        <h6><i class="bi bi-lightbulb me-2"></i>Tips:</h6>
                        <ul class="mb-0">
                            <li>Always start with a single <code>#</code> for the main topic</li>
                            <li>Use <strong>bold</strong> text with <code>**text**</code> for emphasis</li>
                            <li>Use <em>italic</em> text with <code>*text*</code> for subtle emphasis</li>
                            <li>Don't skip heading levels (e.g., don't go from <code>##</code> to <code>####</code>)</li>
                            <li>Each heading becomes a node in the mindmap</li>
                            <li>Changes update the visual in real-time as you type</li>
                        </ul>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-code-square me-2"></i>Example Structure</h6>
                        </div>
                        <div class="card-body">
                            <pre class="mb-0" style="background-color: #f8f9fa; padding: 1rem; border-radius: 0.375rem;"><code># Study Guide
## Chapter 1: Introduction
### Overview
### Key Concepts
#### Concept A
#### Concept B
## Chapter 2: Advanced Topics
### Topic 1
### Topic 2
#### Subtopic 2.1
#### Subtopic 2.2</code></pre>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/markmap-autoloader@0.18"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
        let currentViewedMindmapId = null; // Track which mindmap is currently being viewed
        let currentMarkdown = ''; // Track current markdown content
        let isSplitView = false; // Track split view state
        let hasUnsavedChanges = false; // Track if there are unsaved changes
        let updateTimeout = null; // Debounce timer for real-time updates

        // Get DOM elements
        const markdownEditor = document.getElementById('markdownEditor');
        const markdownEditorPanel = document.getElementById('markdownEditorPanel');
        const mindmapSplitContainer = document.getElementById('mindmap-split-container');
        const toggleViewBtn = document.getElementById('toggleViewBtn');
        const saveMindmapBtn = document.getElementById('saveMindmapBtn');
        const mindmapToolbar = document.getElementById('mindmapToolbar');

        // Function to update export button states
        function updateExportButtonStates(viewedMindmapId) {
            // Disable all export buttons
            document.querySelectorAll('.export-mindmap-image-btn, .export-mindmap-pdf-btn').forEach(btn => {
                btn.style.pointerEvents = 'none';
                btn.style.opacity = '0.5';
                btn.style.cursor = 'not-allowed';
            });

            // Enable export buttons only for the currently viewed mindmap
            if (viewedMindmapId) {
                document.querySelectorAll(`.export-mindmap-image-btn[data-id="${viewedMindmapId}"], .export-mindmap-pdf-btn[data-id="${viewedMindmapId}"]`).forEach(btn => {
                    btn.style.pointerEvents = 'auto';
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                });
            }
        }

        // Initialize: disable all export buttons on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateExportButtonStates(null);
            
            const selectedMindmap = document.getElementById('mindmap-container');
            if (selectedMindmap.innerHTML) {
                selectedMindmap.innerHTML = '<p class="text-center p-3"> Select a mindmap</p>';
            }

            // Initialize markdown editor event listeners
            if (markdownEditor) {
                markdownEditor.addEventListener('input', function() {
                    hasUnsavedChanges = true;
                    updateSaveButtonState();
                    
                    // Real-time visual update with debouncing
                    clearTimeout(updateTimeout);
                    updateTimeout = setTimeout(() => {
                        updateVisualFromMarkdown(true); // true = silent update (no alerts)
                    }, 500); // 500ms debounce delay
                });
            }

            if (toggleViewBtn) {
                toggleViewBtn.addEventListener('click', toggleSplitView);
            }

            if (saveMindmapBtn) {
                saveMindmapBtn.addEventListener('click', saveMindmapToDatabase);
            }
        });

        // Function to update visual mindmap from markdown editor
        // silent: if true, don't show alerts for empty markdown
        function updateVisualFromMarkdown(silent = false) {
            if (!markdownEditor) {
                if (!silent) alert('No mindmap loaded.');
                return;
            }

            const markdownText = markdownEditor.value.trim();
            if (!markdownText) {
                if (!silent) alert('Markdown cannot be empty.');
                return;
            }

            try {
                currentMarkdown = markdownText;
                renderAutoloadMindmap(markdownText);
            } catch (error) {
                console.error('Error updating visual:', error);
                if (!silent) alert('Error updating mindmap: ' + error.message);
            }
        }

        // Function to toggle split view
        function toggleSplitView() {
            if (!mindmapSplitContainer) return;

            isSplitView = !isSplitView;

            if (isSplitView) {
                // Show split view
                mindmapSplitContainer.classList.remove('single-view');
                if (markdownEditorPanel) {
                    markdownEditorPanel.style.display = 'flex';
                }
                toggleViewBtn.textContent = 'Visual Only';
                toggleViewBtn.classList.add('view-toggle-active');
            } else {
                // Show only visual
                mindmapSplitContainer.classList.add('single-view');
                if (markdownEditorPanel) {
                    markdownEditorPanel.style.display = 'none';
                }
                toggleViewBtn.textContent = 'Split View';
                toggleViewBtn.classList.remove('view-toggle-active');
            }
        }

        // Function to update save button state
        function updateSaveButtonState() {
            if (!saveMindmapBtn) return;

            if (hasUnsavedChanges && currentViewedMindmapId) {
                saveMindmapBtn.disabled = false;
            } else {
                saveMindmapBtn.disabled = true;
            }
        }

        // Function to save mindmap to database
        async function saveMindmapToDatabase() {
            if (!currentViewedMindmapId) {
                alert('No mindmap loaded.');
                return;
            }

            if (!markdownEditor || !markdownEditor.value.trim()) {
                alert('Markdown cannot be empty.');
                return;
            }

            try {
                const originalText = saveMindmapBtn.textContent;
                saveMindmapBtn.disabled = true;
                saveMindmapBtn.textContent = 'Saving...';

                const markdownToSave = markdownEditor.value.trim();
                const fileId = '<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>';

                const response = await fetch('<?= UPDATE_MINDMAP_STRUCTURE ?>?file_id=' + encodeURIComponent(fileId), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        mindmap_id: currentViewedMindmapId,
                        file_id: fileId,
                        markdown: markdownToSave
                    })
                });

                const json = await response.json();

                if (!json.success) {
                    throw new Error(json.message || 'Failed to save mindmap');
                }

                currentMarkdown = markdownToSave;
                hasUnsavedChanges = false;
                updateSaveButtonState();
                
                // Show success feedback
                saveMindmapBtn.textContent = 'Saved!';
                setTimeout(() => {
                    saveMindmapBtn.textContent = originalText;
                }, 2000);
            } catch (error) {
                console.error('Error saving mindmap:', error);
                alert('Error saving mindmap: ' + error.message);
                saveMindmapBtn.disabled = false;
                updateSaveButtonState();
            }
        }

        // Handle generating new mindmap
        document.getElementById('mindmapForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            const form = e.target;
            const submitButton = form.querySelector('#genMindmap');
            const originalButtonText = submitButton.textContent;
            const container = document.getElementById('mindmap-container');

            // Disable button and show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'Generating...';
            container.innerHTML = '<p class="text-center p-3">Generating mindmap...</p>';

            try {
                const data = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: data
                });
                const json = await res.json();

                if (json.success && json.markdown) {
                    // Show split container and toolbar
                    if (mindmapSplitContainer) {
                        mindmapSplitContainer.style.display = 'grid';
                    }
                    if (mindmapToolbar) {
                        mindmapToolbar.style.display = 'flex';
                    }
                    
                    // Enable split view by default
                    isSplitView = true;
                    if (mindmapSplitContainer) {
                        mindmapSplitContainer.classList.remove('single-view');
                    }
                    if (markdownEditorPanel) {
                        markdownEditorPanel.style.display = 'flex';
                    }
                    if (toggleViewBtn) {
                        toggleViewBtn.textContent = 'Visual Only';
                        toggleViewBtn.classList.add('view-toggle-active');
                    }

                    // Populate markdown editor
                    if (markdownEditor) {
                        markdownEditor.value = json.markdown;
                        currentMarkdown = json.markdown;
                    }

                    // Render visual mindmap
                    container.style.display = 'block';
                    renderAutoloadMindmap(json.markdown);

                    // Set current viewed mindmap
                    currentViewedMindmapId = json.mindmapId;
                    hasUnsavedChanges = false;
                    updateSaveButtonState();
                    updateExportButtonStates(json.mindmapId);
                } else {
                    container.innerHTML = `<div class="alert alert-danger">Error: ${json.message || 'Failed to generate mindmap'}</div>`;
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                }
            } catch (err) {
                container.innerHTML = `<div class="alert alert-danger">Error: ${err.message}</div>`;
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            }
        });


        // Handle viewing saved mindmaps
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.view-btn');
            if (!btn) return;

            const id = btn.dataset.id;
            const container = document.getElementById('mindmap-container');
            container.innerHTML = '<p class="text-center p-3">Loading mindmap...</p>';

            try {
                const formData = new FormData();
                formData.append('mindmap_id', id);
                formData.append('file_id', '<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>');

                const res = await fetch('<?= VIEW_MINDMAP_ROUTE ?>', {
                    method: 'POST',
                    body: formData
                });
                const json = await res.json();

                if (json.success && json.markdown) {
                    // Show split container and toolbar
                    if (mindmapSplitContainer) {
                        mindmapSplitContainer.style.display = 'grid';
                    }
                    if (mindmapToolbar) {
                        mindmapToolbar.style.display = 'flex';
                    }
                    
                    // Enable split view by default
                    isSplitView = true;
                    if (mindmapSplitContainer) {
                        mindmapSplitContainer.classList.remove('single-view');
                    }
                    if (markdownEditorPanel) {
                        markdownEditorPanel.style.display = 'flex';
                    }
                    if (toggleViewBtn) {
                        toggleViewBtn.textContent = 'Visual Only';
                        toggleViewBtn.classList.add('view-toggle-active');
                    }

                    // Populate markdown editor
                    if (markdownEditor) {
                        markdownEditor.value = json.markdown;
                        currentMarkdown = json.markdown;
                    }

                    // Render visual mindmap
                    const container = document.getElementById('mindmap-container');
                    container.style.display = 'block';
                    renderAutoloadMindmap(json.markdown);

                    // Set current viewed mindmap and update button states
                    currentViewedMindmapId = id;
                    hasUnsavedChanges = false;
                    updateSaveButtonState();
                    updateExportButtonStates(id);
                } else {
                    const container = document.getElementById('mindmap-container');
                    container.innerHTML = `<div class="alert alert-danger">Error: ${json.message || 'Failed to load mindmap'}</div>`;
                    currentViewedMindmapId = null;
                    hasUnsavedChanges = false;
                    updateSaveButtonState();
                    updateExportButtonStates(null);
                }
            } catch (err) {
                const container = document.getElementById('mindmap-container');
                container.innerHTML = `<div class="alert alert-danger">Error: ${err.message}</div>`;
                currentViewedMindmapId = null;
                hasUnsavedChanges = false;
                updateSaveButtonState();
                updateExportButtonStates(null);
            }
        });

        // Handle exporting mindmap as image
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.export-mindmap-image-btn');
            if (!btn) return;

            const id = btn.dataset.id;

            // Check if this mindmap is currently being viewed
            if (currentViewedMindmapId !== id) {
                alert('Please view this mindmap first before exporting.');
                return;
            }

            const container = document.getElementById('mindmap-container');
            const markmapDiv = container.querySelector('.markmap');

            // Verify mindmap is displayed
            if (!markmapDiv || markmapDiv.children.length === 0) {
                alert('Mindmap is not fully loaded. Please wait and try again.');
                return;
            }

            // Export the currently displayed mindmap
            exportMindmapAsImage(id);
        });

        // Handle exporting mindmap as PDF
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.export-mindmap-pdf-btn');
            if (!btn) return;

            const id = btn.dataset.id;

            // Check if this mindmap is currently being viewed
            if (currentViewedMindmapId !== id) {
                alert('Please view this mindmap first before exporting.');
                return;
            }

            const container = document.getElementById('mindmap-container');
            const markmapDiv = container.querySelector('.markmap');

            // Verify mindmap is displayed
            if (!markmapDiv || markmapDiv.children.length === 0) {
                alert('Mindmap is not fully loaded. Please wait and try again.');
                return;
            }

            // Export the currently displayed mindmap
            exportMindmapAsPdf(id);
        });

        // Helper function to download canvas as image using data URL
        function downloadCanvasAsImage(canvas, mindmapId) {
            try {
                const dataURL = canvas.toDataURL('image/png');
                const link = document.createElement('a');
                link.href = dataURL;
                link.download = `mindmap_${mindmapId}_${new Date().getTime()}.png`;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                setTimeout(() => {
                    if (document.body.contains(link)) {
                        document.body.removeChild(link);
                    }
                }, 200);
            } catch (err) {
                console.error('Data URL download error:', err);
                alert('Error downloading image: ' + err.message);
            }
        }

        // Function to export mindmap as image
        function exportMindmapAsImage(mindmapId) {
            const container = document.getElementById('mindmap-container');
            const markmapDiv = container.querySelector('.markmap');

            if (!markmapDiv) {
                alert('Please view the mindmap first before exporting.');
                return;
            }

            // Check if mindmap has content
            if (!markmapDiv.children || markmapDiv.children.length === 0) {
                alert('Mindmap is not fully loaded. Please wait and try again.');
                return;
            }

            // Show loading indicator
            const originalDisplay = container.style.display;
            container.style.display = 'block';

            // Use html2canvas to capture the mindmap
            html2canvas(markmapDiv, {
                backgroundColor: '#ffffff',
                scale: 2,
                logging: false,
                useCORS: true,
                allowTaint: true,
                width: markmapDiv.scrollWidth,
                height: markmapDiv.scrollHeight
            }).then(canvas => {
                try {
                    // Convert canvas to blob and download
                    if (canvas.toBlob) {
                        canvas.toBlob(function(blob) {
                            if (!blob) {
                                // Fallback to data URL method
                                downloadCanvasAsImage(canvas, mindmapId);
                                return;
                            }
                            
                            try {
                                const url = URL.createObjectURL(blob);
                                const link = document.createElement('a');
                                link.href = url;
                                link.download = `mindmap_${mindmapId}_${new Date().getTime()}.png`;
                                link.style.display = 'none';
                                link.setAttribute('download', link.download);
                                
                                document.body.appendChild(link);
                                
                                // Trigger download
                                link.click();
                                
                                // Cleanup after a short delay
                                setTimeout(() => {
                                    if (document.body.contains(link)) {
                                        document.body.removeChild(link);
                                    }
                                    URL.revokeObjectURL(url);
                                }, 200);
                            } catch (err) {
                                console.error('Download link error:', err);
                                // Fallback to data URL method
                                downloadCanvasAsImage(canvas, mindmapId);
                            }
                        }, 'image/png', 1.0);
                    } else {
                        // Fallback if toBlob is not supported
                        downloadCanvasAsImage(canvas, mindmapId);
                    }
                } catch (err) {
                    console.error('Export error:', err);
                    alert('Error creating download: ' + err.message);
                }
            }).catch(err => {
                console.error('html2canvas error:', err);
                alert('Error exporting mindmap: ' + err.message);
            });
        }

        // Function to export mindmap as PDF
        function exportMindmapAsPdf(mindmapId) {
            const container = document.getElementById('mindmap-container');
            const markmapDiv = container.querySelector('.markmap');

            if (!markmapDiv) {
                alert('Please view the mindmap first before exporting.');
                return;
            }

            // Check if jsPDF is available
            if (typeof window.jspdf === 'undefined') {
                alert('PDF library not loaded. Please refresh the page and try again.');
                return;
            }

            // Use html2canvas to capture the mindmap with higher quality
            html2canvas(markmapDiv, {
                backgroundColor: '#ffffff',
                scale: 3, // Higher scale for better quality
                logging: false,
                useCORS: true,
                width: markmapDiv.scrollWidth,
                height: markmapDiv.scrollHeight
            }).then(canvas => {
                try {
                    const {
                        jsPDF
                    } = window.jspdf;
                    const imgData = canvas.toDataURL('image/png', 1.0);

                    // PDF dimensions (A4 size in mm)
                    const pdfWidth = 210; // A4 width in mm
                    const pdfHeight = 297; // A4 height in mm

                    // Reduced margins for bigger mindmap (5mm on each side = 10mm total)
                    const margin = 5;
                    const availableWidth = pdfWidth - (margin * 2);
                    const availableHeight = pdfHeight - (margin * 2);

                    // Calculate image dimensions maintaining aspect ratio
                    const imgWidth = canvas.width;
                    const imgHeight = canvas.height;
                    const ratio = imgWidth / imgHeight;

                    // Determine orientation based on mindmap aspect ratio
                    let finalWidth, finalHeight, orientation, pageWidth, pageHeight;

                    if (ratio > 1) {
                        // Landscape mindmap - use landscape orientation
                        orientation = 'landscape';
                        pageWidth = pdfHeight; // Swap for landscape
                        pageHeight = pdfWidth;
                        const landscapeAvailableWidth = pageWidth - (margin * 2);
                        const landscapeAvailableHeight = pageHeight - (margin * 2);

                        finalWidth = landscapeAvailableWidth;
                        finalHeight = finalWidth / ratio;

                        // If height exceeds page, scale down
                        if (finalHeight > landscapeAvailableHeight) {
                            finalHeight = landscapeAvailableHeight;
                            finalWidth = finalHeight * ratio;
                        }
                    } else {
                        // Portrait mindmap - use portrait orientation
                        orientation = 'portrait';
                        pageWidth = pdfWidth;
                        pageHeight = pdfHeight;

                        finalWidth = availableWidth;
                        finalHeight = finalWidth / ratio;

                        // If height exceeds page, scale down
                        if (finalHeight > availableHeight) {
                            finalHeight = availableHeight;
                            finalWidth = finalHeight * ratio;
                        }
                    }

                    // Center the image
                    const xOffset = (pageWidth - finalWidth) / 2;
                    const yOffset = (pageHeight - finalHeight) / 2;

                    // Create PDF with appropriate orientation
                    const pdf = new jsPDF(orientation, 'mm', 'a4');
                    pdf.addImage(imgData, 'PNG', xOffset, yOffset, finalWidth, finalHeight);

                    // Save PDF
                    pdf.save(`mindmap_${mindmapId}_${new Date().getTime()}.pdf`);
                } catch (err) {
                    alert('Error exporting mindmap as PDF: ' + err.message);
                }
            }).catch(err => {
                alert('Error capturing mindmap: ' + err.message);
            });
        }

        //Render using autoloader 
        function renderAutoloadMindmap(markdown) {
            const container = document.getElementById('mindmap-container');

            // Clear container
            container.innerHTML = '';

            // Create markmap div using DOM methods to avoid template literal issues
            const markmapDiv = document.createElement('div');
            markmapDiv.className = 'markmap';

            // Create script element for template
            const scriptEl = document.createElement('script');
            scriptEl.type = 'text/template';
            scriptEl.textContent = markdown;

            markmapDiv.appendChild(scriptEl);
            container.appendChild(markmapDiv);

            // Re-run autoloader to render the new block
            if (window.markmap && window.markmap.autoLoader) {
                window.markmap.autoLoader.renderAll();
            }
        }

        /**
         * Delete mindmap handler
         * 
         * Behavior: Shows confirmation modal before deleting mindmap. On confirmation,
         * submits delete form.
         */
        $(document).on('click', '.delete-mindmap-btn', function(e){
            e.preventDefault();
            var $form = $(this).closest('.delete-mindmap-form');
            var mindmapId = $form.data('mindmap-id');
            var mindmapTitle = $form.closest('.list-group-item').find('strong').text();

            showConfirmModal({
                message: 'Are you sure you want to delete the mindmap "' + mindmapTitle + '"? This action cannot be undone.',
                title: 'Delete Mindmap',
                confirmText: 'Delete',
                cancelText: 'Cancel',
                danger: true,
                onConfirm: function() {
                    $form.submit();
                }
            });
        });
    </script>
    <?php include VIEW_CONFIRM; ?>
</body>

</html>