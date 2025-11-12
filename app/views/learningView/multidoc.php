<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi-Document Tools - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <style>
        .selection-panel {
            background-color: white;
            border-right: 1px solid #dee2e6;
            height: calc(100vh - 60px);
            overflow-y: auto;
        }

        .content-panel {
            background-color: #f8f9fa;
            height: calc(100vh - 60px);
            overflow-y: auto;
        }

        .document-item, .folder-item {
            padding: 0.5rem;
            margin: 0.25rem 0;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .document-item:hover, .folder-item:hover {
            background-color: #f8f9fa;
        }

        .document-item.selected, .folder-item.selected {
            background-color: #e7d5ff;
            border-left: 3px solid #A855F7;
        }

        .tool-card {
            border: 2px solid #dee2e6;
            border-radius: 0.5rem;
            transition: all 0.3s;
            cursor: pointer;
            height: 100%;
        }

        .tool-card:hover {
            border-color: #A855F7;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(168, 85, 247, 0.2);
        }

        .tool-card .card-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 200px;
        }

        .tool-icon {
            font-size: 3rem;
            color: #A855F7;
            margin-bottom: 1rem;
        }

        .selected-count {
            background-color: #A855F7;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }

        .folder-toggle {
            cursor: pointer;
            user-select: none;
        }

        .folder-children {
            margin-left: 1.5rem;
            display: none;
        }

        .folder-children.show {
            display: block;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include VIEW_SIDEBAR; ?>
        
        <!-- Left Side: Document & Folder Selection -->
        <aside class="selection-panel p-3" style="width: 350px;">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" style="color: #A855F7;">Select Documents</h5>
                        <span class="badge bg-primary selected-count" id="selectedCount">0</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="searchDocuments" placeholder="Search documents...">
                    </div>

                    <div class="mb-3">
                        <button class="btn btn-sm btn-outline-primary w-100" id="selectAllBtn">Select All</button>
                        <button class="btn btn-sm btn-outline-secondary w-100 mt-2" id="clearSelectionBtn">Clear Selection</button>
                    </div>

                    <hr>

                    <div id="documentList" style="max-height: calc(100vh - 300px); overflow-y: auto;">
                        <!-- Folders will be rendered here -->
                        <?php if (!empty($allUserFolders)): ?>
                            <?php foreach ($allUserFolders as $folder): ?>
                                <?php if ($folder['parentFolderId'] == null): ?>
                                    <div class="folder-container mb-2">
                                        <div class="folder-item d-flex align-items-center" data-folder-id="<?= $folder['folderID'] ?>">
                                            <input type="checkbox" class="form-check-input me-2 folder-checkbox" 
                                                   id="folder_<?= $folder['folderID'] ?>" 
                                                   data-folder-id="<?= $folder['folderID'] ?>">
                                            <i class="bi bi-folder me-2"></i>
                                            <span class="folder-toggle flex-grow-1"><?= htmlspecialchars($folder['name']) ?></span>
                                            <i class="bi bi-chevron-right folder-chevron"></i>
                                        </div>
                                        <div class="folder-children" id="folder_children_<?= $folder['folderID'] ?>">
                                            <!-- Child folders and documents will be loaded here -->
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Documents (root level) -->
                        <?php if (!empty($fileList['files'])): ?>
                            <?php foreach ($fileList['files'] as $file): ?>
                                <?php if ($file['folderID'] == null): ?>
                                    <div class="document-item d-flex align-items-center" data-file-id="<?= $file['fileID'] ?>">
                                        <input type="checkbox" class="form-check-input me-2 document-checkbox" 
                                               id="file_<?= $file['fileID'] ?>" 
                                               data-file-id="<?= $file['fileID'] ?>">
                                        <i class="bi bi-file-text me-2"></i>
                                        <span><?= htmlspecialchars($file['name']) ?></span>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2">No documents available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Right Side: Generation Tools -->
        <main class="content-panel flex-grow-1 p-4">
            <div class="container">
                <h3 class="mb-4" style="color: #A855F7;">Multi-Document Tools</h3>
                <p class="text-muted mb-4">Select documents or folders from the left panel, then choose a tool to generate content.</p>

                <div class="row g-4">
                    <!-- Generate Summary -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card tool-card" id="summaryTool">
                            <div class="card-body text-center">
                                <i class="bi bi-file-text tool-icon"></i>
                                <h5 class="card-title">Generate Summary</h5>
                                <p class="card-text text-muted">Create a summary from selected documents</p>
                                <button class="btn btn-primary" style="background-color: #A855F7; border: none;" disabled>
                                    Generate Summary
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Generate Notes -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card tool-card" id="notesTool">
                            <div class="card-body text-center">
                                <i class="bi bi-journal-text tool-icon"></i>
                                <h5 class="card-title">Generate Notes</h5>
                                <p class="card-text text-muted">Create study notes from selected documents</p>
                                <button class="btn btn-primary" style="background-color: #A855F7; border: none;" disabled>
                                    Generate Notes
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Generate Flashcards -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card tool-card" id="flashcardsTool">
                            <div class="card-body text-center">
                                <i class="bi bi-card-checklist tool-icon"></i>
                                <h5 class="card-title">Generate Flashcards</h5>
                                <p class="card-text text-muted">Create flashcards from selected documents</p>
                                <button class="btn btn-primary" style="background-color: #A855F7; border: none;" disabled>
                                    Generate Flashcards
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Generate Quiz -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card tool-card" id="quizTool">
                            <div class="card-body text-center">
                                <i class="bi bi-question-circle tool-icon"></i>
                                <h5 class="card-title">Generate Quiz</h5>
                                <p class="card-text text-muted">Create quiz questions from selected documents</p>
                                <button class="btn btn-primary" style="background-color: #A855F7; border: none;" disabled>
                                    Generate Quiz
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Generate Mindmap -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card tool-card" id="mindmapTool">
                            <div class="card-body text-center">
                                <i class="bi bi-diagram-3 tool-icon"></i>
                                <h5 class="card-title">Generate Mindmap</h5>
                                <p class="card-text text-muted">Create a mindmap from selected documents</p>
                                <button class="btn btn-primary" style="background-color: #A855F7; border: none;" disabled>
                                    Generate Mindmap
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instructions Panel (will show when documents are selected) -->
                <div class="card mt-4" id="instructionsPanel" style="display: none;">
                    <div class="card-header">
                        <h5 class="mb-0">Instructions (Optional)</h5>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" id="instructionsText" rows="3" 
                                  placeholder="Add any specific instructions for generation..."></textarea>
                    </div>
                </div>

                <!-- Results Panel (will show generated content) -->
                <div class="card mt-4" id="resultsPanel" style="display: none;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Generated Content</h5>
                        <button class="btn btn-sm btn-outline-secondary" id="closeResultsBtn">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="card-body" id="resultsContent">
                        <!-- Generated content will appear here -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selectedFiles = new Set();
            let selectedFolders = new Set();

            // Update selected count
            function updateSelectedCount() {
                const total = selectedFiles.size + selectedFolders.size;
                document.getElementById('selectedCount').textContent = total;
                
                // Enable/disable tool buttons
                const buttons = document.querySelectorAll('.tool-card button');
                buttons.forEach(btn => {
                    btn.disabled = total === 0;
                });

                // Show/hide instructions panel
                document.getElementById('instructionsPanel').style.display = total > 0 ? 'block' : 'none';
            }

            // Handle document checkbox
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('document-checkbox')) {
                    const fileId = e.target.dataset.fileId;
                    const item = e.target.closest('.document-item');
                    
                    if (e.target.checked) {
                        selectedFiles.add(fileId);
                        item.classList.add('selected');
                    } else {
                        selectedFiles.delete(fileId);
                        item.classList.remove('selected');
                    }
                    updateSelectedCount();
                }

                if (e.target.classList.contains('folder-checkbox')) {
                    const folderId = e.target.dataset.folderId;
                    const item = e.target.closest('.folder-item');
                    
                    if (e.target.checked) {
                        selectedFolders.add(folderId);
                        item.classList.add('selected');
                    } else {
                        selectedFolders.delete(folderId);
                        item.classList.remove('selected');
                    }
                    updateSelectedCount();
                }
            });

            // Handle folder toggle
            document.querySelectorAll('.folder-toggle').forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const folderItem = this.closest('.folder-item');
                    const folderId = folderItem.dataset.folderId;
                    const children = document.getElementById('folder_children_' + folderId);
                    const chevron = folderItem.querySelector('.folder-chevron');
                    
                    if (children) {
                        children.classList.toggle('show');
                        chevron.classList.toggle('bi-chevron-down');
                        chevron.classList.toggle('bi-chevron-right');
                    }
                });
            });

            // Select All button
            document.getElementById('selectAllBtn').addEventListener('click', function() {
                document.querySelectorAll('.document-checkbox, .folder-checkbox').forEach(checkbox => {
                    checkbox.checked = true;
                    checkbox.dispatchEvent(new Event('change'));
                });
            });

            // Clear Selection button
            document.getElementById('clearSelectionBtn').addEventListener('click', function() {
                selectedFiles.clear();
                selectedFolders.clear();
                document.querySelectorAll('.document-checkbox, .folder-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                document.querySelectorAll('.document-item, .folder-item').forEach(item => {
                    item.classList.remove('selected');
                });
                updateSelectedCount();
            });

            // Search functionality
            document.getElementById('searchDocuments').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                document.querySelectorAll('.document-item, .folder-item').forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(searchTerm) ? 'block' : 'none';
                });
            });

            // Tool card click handlers (placeholder)
            document.getElementById('summaryTool').addEventListener('click', function() {
                if (selectedFiles.size > 0 || selectedFolders.size > 0) {
                    console.log('Generate Summary for:', Array.from(selectedFiles), Array.from(selectedFolders));
                    // TODO: Implement summary generation
                }
            });

            document.getElementById('notesTool').addEventListener('click', function() {
                if (selectedFiles.size > 0 || selectedFolders.size > 0) {
                    console.log('Generate Notes for:', Array.from(selectedFiles), Array.from(selectedFolders));
                    // TODO: Implement notes generation
                }
            });

            document.getElementById('flashcardsTool').addEventListener('click', function() {
                if (selectedFiles.size > 0 || selectedFolders.size > 0) {
                    console.log('Generate Flashcards for:', Array.from(selectedFiles), Array.from(selectedFolders));
                    // TODO: Implement flashcards generation
                }
            });

            document.getElementById('quizTool').addEventListener('click', function() {
                if (selectedFiles.size > 0 || selectedFolders.size > 0) {
                    console.log('Generate Quiz for:', Array.from(selectedFiles), Array.from(selectedFolders));
                    // TODO: Implement quiz generation
                }
            });

            document.getElementById('mindmapTool').addEventListener('click', function() {
                if (selectedFiles.size > 0 || selectedFolders.size > 0) {
                    console.log('Generate Mindmap for:', Array.from(selectedFiles), Array.from(selectedFolders));
                    // TODO: Implement mindmap generation
                }
            });

            // Close results panel
            document.getElementById('closeResultsBtn').addEventListener('click', function() {
                document.getElementById('resultsPanel').style.display = 'none';
            });

            // Initialize
            updateSelectedCount();
        });
    </script>
</body>

</html>

