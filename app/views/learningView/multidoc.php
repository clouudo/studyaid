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
            border-left: 1px solid #dee2e6;
            height: calc(100vh - 60px);
            overflow-y: auto;
        }

        .content-panel {
            background-color: #f8f9fa;
            height: calc(100vh - 60px);
            overflow-y: auto;
            width: 100%;
            flex: 1 1 auto;
        }

        .document-item,
        .folder-item {
            padding: 0.5rem;
            margin: 0.25rem 0;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .document-item:hover,
        .folder-item:hover {
            background-color: #f8f9fa;
        }

        .document-item.selected,
        .folder-item.selected {
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

        /* Modal Styles */
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include VIEW_SIDEBAR; ?>

        <!-- Middle: Generation Tools -->
        <main class="content-panel flex-grow-1 p-4">
            <div class="container-fluid">
                <h3 class="mb-4" style="color: #A855F7;">Multi-Document Tools</h3>
                <p class="text-muted mb-4">Select documents or folders from the right panel, then choose a tool to generate content.</p>

                <div class="row g-4">
                    <!-- Synthesize Document -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card tool-card" id="reportTool">
                            <div class="card-body text-center">
                                <i class="bi bi-file-text tool-icon"></i>
                                <h5 class="card-title">Synthesize Document</h5>
                                <p class="card-text text-muted">Create a synthesized document from selected documents</p>
                            </div>
                        </div>
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

        <!-- Right Side: Document & Folder Selection -->
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
                                            <?php foreach ($fileList as $file): ?>
                                                <?php if ($file['folderID'] == $folder['folderID']): ?>
                                                    <div class="document-item d-flex align-items-center" data-file-id="<?= $file['fileID'] ?>">
                                                        <input type="checkbox" class="form-check-input me-2 document-checkbox"
                                                            id="file_<?= $file['fileID'] ?>"
                                                            data-file-id="<?= $file['fileID'] ?>">
                                                        <i class="bi bi-file-text me-2"></i>
                                                        <span><?= htmlspecialchars($file['name']) ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Documents (root level) -->
                        <?php if (!empty($fileList)): ?>
                            <?php foreach ($fileList as $file): ?>
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
    </div>

    <!-- Synthesize Document Form Modal -->
    <div class="modal fade" id="reportFormModal" tabindex="-1" aria-labelledby="reportFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #A855F7; color: white;">
                    <h5 class="modal-title" id="reportFormModalLabel">
                        <i class="bi bi-file-text"></i> Synthesize Document
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="reportGenerationForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="reportType" class="form-label">Document Type</label>
                            <select class="form-select" id="reportType" name="reportType">
                                <option value="studyGuide">Study Guide</option>
                                <option value="briefDocument">Brief Document</option>
                                <option value="keyPoints">Key Points</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="reportDescription" class="form-label">Describe the document</label>
                            <textarea class="form-control" id="reportDescription" name="reportDescription" rows="3" placeholder="Describe the document"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Selected Documents</label>
                            <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto; background-color: #f8f9fa;">
                                <div id="selectedDocumentsList">
                                    <span class="text-muted">No documents selected</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Documents that will be included in the synthesized document</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background-color: #A855F7; border: none;">
                            <i class="bi bi-play-fill"></i> Synthesize Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selectedFiles = new Set();
            let selectedFolders = new Set();

            // Initialize Bootstrap modal
            const reportFormModal = new bootstrap.Modal(document.getElementById('reportFormModal'));

            // Function to populate selected documents list in form modal
            function populateSelectedDocumentsList() {
                const listContainer = document.getElementById('selectedDocumentsList');
                const fileIds = Array.from(selectedFiles);
                
                if (fileIds.length === 0) {
                    listContainer.innerHTML = '<span class="text-muted">No documents selected</span>';
                    return;
                }

                let html = '<ul class="list-unstyled mb-0">';
                fileIds.forEach(fileId => {
                    const fileItem = document.querySelector(`[data-file-id="${fileId}"]`);
                    if (fileItem) {
                        const fileName = fileItem.querySelector('span').textContent.trim();
                        html += `<li class="mb-1"><i class="bi bi-file-text text-primary"></i> ${fileName}</li>`;
                    }
                });
                html += '</ul>';
                listContainer.innerHTML = html;
            }

            // Function to update description based on document type
            function updateDescriptionByReportType() {
                const reportType = document.getElementById('reportType').value;
                const reportDescriptionElement = document.getElementById('reportDescription');
                
                if (reportType === 'briefDocument') {
                    reportDescriptionElement.value = 'Create a comprehensive briefing document that synthesizes the main themes and ideas from the sources. Start with a concise Executive Summary that presents the most critical takeaways upfront. The body of the document must provide a detailed and thorough examination of the main themes, evidence, and conclusions found in the sources. This analysis should be structured logically with headings and bullet points to ensure clarity. The tone must be objective and incisive.';
                } else if (reportType === 'studyGuide') {
                    reportDescriptionElement.value = 'You are a highly capable research assistant and tutor. Create a detailed study guide designed to review understanding of the sources. Create a quiz with ten short-answer questions (2-3 sentences each) and include a separate answer key. Suggest five essay format questions, but do not supply answers. Also conclude with a comprehensive glossary of key terms with definitions.';
                } else if (reportType === 'keyPoints') {
                    reportDescriptionElement.value = 'Generate key points from the selected documents.';
                }
            }

                // Update selected count
            function updateSelectedCount() {
                // Only count files since folders now select their files
                const total = selectedFiles.size;
                document.getElementById('selectedCount').textContent = total;
            }



            // Function to update folder checkbox state based on child files
            function updateFolderCheckboxState(folderId) {
                const folderChildren = document.getElementById('folder_children_' + folderId);
                if (!folderChildren) return;
                
                const folderCheckbox = document.querySelector(`.folder-checkbox[data-folder-id="${folderId}"]`);
                const folderItem = folderCheckbox ? folderCheckbox.closest('.folder-item') : null;
                const fileCheckboxes = folderChildren.querySelectorAll('.document-checkbox');
                
                if (fileCheckboxes.length === 0) return;
                
                const checkedCount = Array.from(fileCheckboxes).filter(cb => cb.checked).length;
                const allChecked = checkedCount === fileCheckboxes.length;
                const someChecked = checkedCount > 0 && checkedCount < fileCheckboxes.length;
                
                if (folderCheckbox) {
                    if (allChecked) {
                        folderCheckbox.checked = true;
                        folderCheckbox.indeterminate = false;
                        if (folderItem) folderItem.classList.add('selected');
                        selectedFolders.add(folderId);
                    } else if (someChecked) {
                        folderCheckbox.checked = false;
                        folderCheckbox.indeterminate = true;
                        if (folderItem) folderItem.classList.add('selected');
                        selectedFolders.delete(folderId);
                    } else {
                        folderCheckbox.checked = false;
                        folderCheckbox.indeterminate = false;
                        if (folderItem) folderItem.classList.remove('selected');
                        selectedFolders.delete(folderId);
                    }
                }
            }

            // Handle document checkbox
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('document-checkbox')) {
                    const fileId = e.target.dataset.fileId;
                    const item = e.target.closest('.document-item');
                    const folderContainer = item ? item.closest('.folder-children') : null;
                    const folderId = folderContainer ? folderContainer.id.replace('folder_children_', '') : null;

                    if (e.target.checked) {
                        selectedFiles.add(fileId);
                        item.classList.add('selected');
                    } else {
                        selectedFiles.delete(fileId);
                        item.classList.remove('selected');
                    }
                    
                    // Update parent folder checkbox state if this file is in a folder
                    if (folderId) {
                        updateFolderCheckboxState(folderId);
                    }
                    
                    updateSelectedCount();
                }

                if (e.target.classList.contains('folder-checkbox')) {
                    const folderId = e.target.dataset.folderId;
                    const item = e.target.closest('.folder-item');
                    const folderChildren = document.getElementById('folder_children_' + folderId);

                    if (e.target.checked) {
                        selectedFolders.add(folderId);
                        item.classList.add('selected');
                        
                        // Select all files within this folder
                        if (folderChildren) {
                            const fileCheckboxes = folderChildren.querySelectorAll('.document-checkbox');
                            fileCheckboxes.forEach(checkbox => {
                                const fileId = checkbox.dataset.fileId;
                                const fileItem = checkbox.closest('.document-item');
                                
                                checkbox.checked = true;
                                selectedFiles.add(fileId);
                                if (fileItem) {
                                    fileItem.classList.add('selected');
                                }
                            });
                        }
                    } else {
                        selectedFolders.delete(folderId);
                        item.classList.remove('selected');
                        
                        // Deselect all files within this folder
                        if (folderChildren) {
                            const fileCheckboxes = folderChildren.querySelectorAll('.document-checkbox');
                            fileCheckboxes.forEach(checkbox => {
                                const fileId = checkbox.dataset.fileId;
                                const fileItem = checkbox.closest('.document-item');
                                
                                checkbox.checked = false;
                                selectedFiles.delete(fileId);
                                if (fileItem) {
                                    fileItem.classList.remove('selected');
                                }
                            });
                        }
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

            // Update document list and description when form modal is shown
            document.getElementById('reportFormModal').addEventListener('show.bs.modal', function() {
                populateSelectedDocumentsList();
                
                // Set initial description based on selected document type
                updateDescriptionByReportType();
            });

            // Update description when document type changes
            document.getElementById('reportType').addEventListener('change', function() {
                updateDescriptionByReportType();
            });

            // Synthesize Document Handler - Opens Form Modal when card is clicked
            document.getElementById('reportTool').addEventListener('click', function(e) {
                // Check if files are selected - do nothing if no documents selected
                const fileIds = Array.from(selectedFiles);
                
                if (fileIds.length === 0) {
                    return; // Exit silently without showing any modal
                }

                // Reset form
                document.getElementById('reportGenerationForm').reset();
                
                // Show the form modal (populateSelectedDocumentsList and description update will be called by the show.bs.modal event)
                reportFormModal.show();
            });

            // Form Submission Handler
            document.getElementById('reportGenerationForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form values
                const reportDescription = document.getElementById('reportDescription').value.trim();
                const reportType = document.getElementById('reportType').value;
                const fileIds = Array.from(selectedFiles);

                // Close form modal
                reportFormModal.hide();

                // Prepare request data
                const requestData = {
                    fileIds: fileIds,
                    description: reportDescription,
                    reportType: reportType
                };

                // Submit document synthesis request
                fetch('<?= BASE_PATH ?>lm/generateMultiReport', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        console.log('Document synthesized successfully');
                    } else {
                        console.error('Document synthesis failed:', data.message || 'Failed to synthesize document');
                    }
                })
                .catch(error => {
                    console.error('Error synthesizing document:', error);
                });
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