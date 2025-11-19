<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Hub - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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

        .selection-panel {
            background-color: white;
            border-left: 1px solid var(--sa-card-border);
            height: 100vh;
            overflow-y: auto;
        }

        .content-panel {
            background-color: #f8f9fa;
            height: 100vh;
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
            background-color: var(--sa-accent);
        }

        .document-item.selected,
        .folder-item.selected {
            background-color: var(--sa-accent);
            border-left: 3px solid var(--sa-primary);
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

        .tool-card {
            border: 1px solid var(--sa-card-border);
            border-radius: 16px;
            transition: all 0.3s;
            cursor: pointer;
            height: 100%;
            box-shadow: 0 8px 24px rgba(111, 66, 193, 0.08);
        }

        .tool-card:hover {
            border-color: var(--sa-primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(111, 66, 193, 0.15);
        }

        .tool-card .card-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 200px;
        }

        .tool-icon-wrapper {
            background-color: var(--sa-accent);
            border-radius: 50%;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .tool-card:hover .tool-icon-wrapper {
            background-color: var(--sa-primary);
        }

        .tool-card:hover .tool-icon {
            color: white;
        }

        .tool-icon {
            font-size: 2.5rem;
            color: var(--sa-primary);
            transition: all 0.3s;
        }

        .selected-count {
            background-color: var(--sa-primary);
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
        .chat-container {
            height: 500px;
            overflow-y: auto;
            border: 1px solid var(--sa-card-border);
            border-radius: 1rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            box-shadow: inset 0 2px 8px rgba(111, 66, 193, 0.05);
        }

        .chat-container::-webkit-scrollbar {
            width: 8px;
        }

        .chat-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .chat-container::-webkit-scrollbar-thumb {
            background: var(--sa-primary);
            border-radius: 10px;
        }

        .chat-container::-webkit-scrollbar-thumb:hover {
            background: var(--sa-primary-dark);
        }

        .message {
            margin-bottom: 1.25rem;
            padding: 1rem 1.25rem;
            border-radius: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            animation: fadeIn 0.3s ease-in;
            max-width: 75%;
            word-wrap: break-word;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.user {
            background: linear-gradient(135deg, var(--sa-primary) 0%, var(--sa-primary-dark) 100%);
            color: #fff;
            margin-left: auto;
            border-bottom-right-radius: 0.25rem;
        }

        .message.bot {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid var(--sa-card-border);
            margin-right: auto;
            border-bottom-left-radius: 0.25rem;
            color: #212529;
        }

        .message-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .message-content {
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .message-time {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-top: 0.5rem;
            display: block;
        }

        .input-group {
            box-shadow: 0 2px 8px rgba(111, 66, 193, 0.1);
            border-radius: 0.75rem;
            overflow: hidden;
        }

        .input-group .form-control {
            border: 2px solid var(--sa-card-border);
            border-right: none;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
        }

        .input-group .form-control:focus {
            border-color: var(--sa-primary);
            box-shadow: none;
        }

        .input-group .btn-primary {
            border-left: none;
            padding: 0.75rem 1.5rem;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include VIEW_SIDEBAR; ?>

        <!-- Middle: Generation Tools -->
        <main class="content-panel flex-grow-1 p-4">
            <div class="container-fluid">
                <h3 class="mb-4" style="color: var(--sa-primary);">Document Hub</h3>
                <p class="text-muted mb-4">Select documents or folders from the right panel, then choose a tool to generate content.</p>

                <div class="row g-4">
                    <!-- Synthesize Document -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card tool-card h-100" id="reportTool">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="tool-icon-wrapper mb-3">
                                    <i class="bi bi-file-text tool-icon"></i>
                                </div>
                                <h5 class="card-title">Synthesize Document</h5>
                                <p class="card-text text-muted">Create a synthesized document from selected documents</p>
                            </div>
                        </div>
                    </div>

                    <!-- Document Hub Chatbot -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card tool-card h-100" id="chatbotTool">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="tool-icon-wrapper mb-3">
                                    <i class="bi bi-chat-dots tool-icon"></i>
                                </div>
                                <h5 class="card-title">Document Hub Chatbot</h5>
                                <p class="card-text text-muted">Ask questions across multiple selected documents</p>
                            </div>
                        </div>
                    </div>

                    <!-- Knowledge Base Search -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card tool-card h-100" id="knowledgeBaseTool">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <div class="tool-icon-wrapper mb-3">
                                    <i class="bi bi-search tool-icon"></i>
                                </div>
                                <h5 class="card-title">Knowledge Base Search</h5>
                                <p class="card-text text-muted">Search across all your uploaded documents</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chatbot Panel (will show chatbot interface) -->
                <div class="card mt-4" id="chatbotPanel" style="display: none;">
                <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-chat-dots me-3" style="font-size: 1.5rem;"></i>
                            <div class="flex-grow-1">
                                <h5 class="mb-0">Document Hub Chatbot</h5>
                                <small class="text-muted">Ask questions about your selected documents</small>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" id="closeChatbotBtn">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="card-body">
                    <div class="chat-container mb-3" id="chatContainer">
                            <div class="message bot">
                                <div class="message-header">
                                    <i class="bi bi-robot"></i>
                                    <span>StudyAid Bot</span>
                                </div>
                                <div class="message-content">Hello! I can answer questions using information from your selected documents. Ask me anything!</div>
                                <div class="message-time"><?= date('H:i') ?></div>
                            </div>
                        </div>
                        <form id="chatbotForm">
                        <div class="input-group">
                                <input type="text" class="form-control" id="chatbotQuestionInput" placeholder="Type your question here..." required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Knowledge Base Search Panel -->
                <div class="card mt-4" id="knowledgeBasePanel" style="display: none;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-search me-3" style="font-size: 1.5rem;"></i>
                            <div class="flex-grow-1">
                                <h5 class="mb-0">Knowledge Base Search</h5>
                                <small class="text-muted">Search across all your documents</small>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" id="closeKnowledgeBaseBtn">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <form id="knowledgeBaseForm">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="knowledgeBaseQueryInput" placeholder="Enter keywords to search..." required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-2"></i>Search
                                </button>
                            </div>
                        </form>
                        <div id="knowledgeBaseResults" style="max-height: 600px; overflow-y: auto;">
                            <!-- Search results will appear here -->
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
                        <h5 class="mb-0" style="color: var(--sa-primary);">Select Documents <small class="text-muted">(Max 3)</small></h5>
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
                <div class="modal-header" style="background-color: var(--sa-primary); color: white;">
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
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-play-fill"></i> Synthesize Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include VIEW_CONFIRM; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        // Pass checked documents from PHP to JavaScript
        const CHECKED_DOCUMENTS = <?= json_encode($checkedDocuments ?? []) ?>;
        const SAVE_CHECKED_DOCUMENTS_URL = '<?= SAVE_CHECKED_DOCUMENTS ?>';
        
        document.addEventListener('DOMContentLoaded', function() {
            let selectedFiles = new Set();
            let selectedFolders = new Set();
            const MAX_SELECTION = 3; // Maximum number of documents that can be selected

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
                    reportDescriptionElement.value = 'You are an expert tutor and researcher. Based on the provided material or topic, create a structured study guide with: 1. A short summary of key ideas, 2. Ten short-answer quiz questions (2–3 sentences each) with a matching answer key, 3. Five essay-style discussion questions (no answers required), 4. A glossary of 8–12 essential terms with concise definitions. Ensure clarity, educational value, and proper structure.';
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
                const countElement = document.getElementById('selectedCount');
                countElement.textContent = total;
                
                // Update count badge color based on limit
                if (total >= MAX_SELECTION) {
                    countElement.style.backgroundColor = '#dc3545'; // Red when at limit
                } else {
                    countElement.style.backgroundColor = '#6f42c1'; // Purple when under limit
                }
                
                // Enable/disable checkboxes based on limit
                const allCheckboxes = document.querySelectorAll('.document-checkbox, .folder-checkbox');
                allCheckboxes.forEach(checkbox => {
                    if (total >= MAX_SELECTION && !checkbox.checked) {
                        checkbox.disabled = true;
                        checkbox.style.opacity = '0.5';
                        checkbox.style.cursor = 'not-allowed';
                    } else {
                        checkbox.disabled = false;
                        checkbox.style.opacity = '1';
                        checkbox.style.cursor = 'pointer';
                    }
                });
            }



            // Function to save checked documents to session
            function saveCheckedDocumentsToSession() {
                const checkedFileIds = Array.from(selectedFiles).map(id => parseInt(id));
                
                fetch(SAVE_CHECKED_DOCUMENTS_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ fileIds: checkedFileIds })
                }).catch(error => {
                    console.error('Error saving checked documents:', error);
                });
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

            // Restore checked documents from session (after functions are defined)
            if (CHECKED_DOCUMENTS && CHECKED_DOCUMENTS.length > 0) {
                CHECKED_DOCUMENTS.forEach(fileId => {
                    const checkbox = document.querySelector(`.document-checkbox[data-file-id="${fileId}"]`);
                    if (checkbox) {
                        const fileItem = checkbox.closest('.document-item');
                        checkbox.checked = true;
                        selectedFiles.add(fileId.toString());
                        if (fileItem) {
                            fileItem.classList.add('selected');
                        }
                        
                        // Update parent folder checkbox state if this file is in a folder
                        const folderContainer = fileItem ? fileItem.closest('.folder-children') : null;
                        if (folderContainer) {
                            const folderId = folderContainer.id.replace('folder_children_', '');
                            updateFolderCheckboxState(folderId);
                        }
                    }
                });
                updateSelectedCount();
            }

            // Handle document checkbox
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('document-checkbox')) {
                    const fileId = e.target.dataset.fileId;
                    const item = e.target.closest('.document-item');
                    const folderContainer = item ? item.closest('.folder-children') : null;
                    const folderId = folderContainer ? folderContainer.id.replace('folder_children_', '') : null;

                    if (e.target.checked) {
                        // Check if adding this file would exceed the limit
                        if (selectedFiles.size >= MAX_SELECTION) {
                            e.target.checked = false;
                            // Hide cancel button for informational messages
                            document.getElementById('confirmCancelBtn').style.display = 'none';
                            showConfirmModal({
                                title: 'Selection Limit Reached',
                                message: `You can only select a maximum of ${MAX_SELECTION} documents.`,
                                confirmText: 'OK',
                                cancelText: '',
                                onConfirm: function() {
                                    // Modal will close automatically
                                }
                            });
                            return;
                        }
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
                    saveCheckedDocumentsToSession();
                }

                if (e.target.classList.contains('folder-checkbox')) {
                    const folderId = e.target.dataset.folderId;
                    const item = e.target.closest('.folder-item');
                    const folderChildren = document.getElementById('folder_children_' + folderId);

                    if (e.target.checked) {
                        // Count how many files are in this folder
                        let filesInFolder = 0;
                        if (folderChildren) {
                            filesInFolder = folderChildren.querySelectorAll('.document-checkbox').length;
                        }
                        
                        // Check if adding all files in this folder would exceed the limit
                        if (selectedFiles.size + filesInFolder > MAX_SELECTION) {
                            e.target.checked = false;
                            // Hide cancel button for informational messages
                            document.getElementById('confirmCancelBtn').style.display = 'none';
                            showConfirmModal({
                                title: 'Selection Limit Reached',
                                message: `Selecting this folder would exceed the maximum limit of ${MAX_SELECTION} documents. Please deselect some documents first.`,
                                confirmText: 'OK',
                                cancelText: '',
                                onConfirm: function() {
                                    // Modal will close automatically
                                }
                            });
                            return;
                        }
                        
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
                    saveCheckedDocumentsToSession();
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

            // Select All button (limited to MAX_SELECTION)
            document.getElementById('selectAllBtn').addEventListener('click', function() {
                // First, expand all folders so their files are visible
                document.querySelectorAll('.folder-children').forEach(children => {
                    children.classList.add('show');
                    const folderId = children.id.replace('folder_children_', '');
                    const folderItem = document.querySelector(`[data-folder-id="${folderId}"]`);
                    if (folderItem) {
                        const chevron = folderItem.querySelector('.folder-chevron');
                        if (chevron) {
                            chevron.classList.remove('bi-chevron-right');
                            chevron.classList.add('bi-chevron-down');
                        }
                    }
                });
                
                // Clear current selections
                selectedFiles.clear();
                selectedFolders.clear();
                
                // Collect all available files
                const allFiles = [];
                
                // Collect files from folders
                document.querySelectorAll('.folder-children').forEach(folderChildren => {
                    const fileCheckboxes = folderChildren.querySelectorAll('.document-checkbox');
                    fileCheckboxes.forEach(checkbox => {
                        const fileId = checkbox.dataset.fileId;
                        const fileItem = checkbox.closest('.document-item');
                        allFiles.push({ fileId, checkbox, fileItem, folderId: folderChildren.id.replace('folder_children_', '') });
                    });
                });
                
                // Collect root-level files
                document.querySelectorAll('.document-item').forEach(item => {
                    const folderContainer = item.closest('.folder-children');
                    if (!folderContainer) {
                        const checkbox = item.querySelector('.document-checkbox');
                        if (checkbox) {
                            const fileId = checkbox.dataset.fileId;
                            allFiles.push({ fileId, checkbox, fileItem: item, folderId: null });
                        }
                    }
                });
                
                // Select only up to MAX_SELECTION files
                let selectedCount = 0;
                const selectedFolderIds = new Set();
                
                for (const file of allFiles) {
                    if (selectedCount >= MAX_SELECTION) break;
                    
                    file.checkbox.checked = true;
                    selectedFiles.add(file.fileId);
                    if (file.fileItem) {
                        file.fileItem.classList.add('selected');
                    }
                    
                    if (file.folderId) {
                        selectedFolderIds.add(file.folderId);
                    }
                    
                    selectedCount++;
                }
                
                // Update folder checkboxes based on selected files
                selectedFolderIds.forEach(folderId => {
                    updateFolderCheckboxState(folderId);
                });
                
                // Ensure count is updated
                updateSelectedCount();
                saveCheckedDocumentsToSession();
                
                if (selectedCount < allFiles.length) {
                    // Hide cancel button for informational messages
                    document.getElementById('confirmCancelBtn').style.display = 'none';
                    showConfirmModal({
                        title: 'Selection Limit',
                        message: `Only ${selectedCount} document(s) selected (maximum ${MAX_SELECTION}).`,
                        confirmText: 'OK',
                        cancelText: '',
                        onConfirm: function() {
                            // Modal will close automatically
                        }
                    });
                }
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
                saveCheckedDocumentsToSession();
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
                fetch('<?= BASE_PATH ?>lm/synthesizeDocument', {
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
                        
                        // Redirect to view the synthesized document
                        if (data.fileId) {
                            // Create a form to submit via POST (as displayDocument expects POST)
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '<?= DISPLAY_DOCUMENT ?>';
                            
                            const fileIdInput = document.createElement('input');
                            fileIdInput.type = 'hidden';
                            fileIdInput.name = 'file_id';
                            fileIdInput.value = data.fileId;
                            
                            form.appendChild(fileIdInput);
                            document.body.appendChild(form);
                            form.submit();
                        } else {
                            console.error('No fileId returned from synthesis');
                            // Hide cancel button for informational messages
                            document.getElementById('confirmCancelBtn').style.display = 'none';
                            showConfirmModal({
                                title: 'Redirect Error',
                                message: 'Document synthesized but could not redirect to view it.',
                                confirmText: 'OK',
                                cancelText: '',
                                onConfirm: function() {
                                    // Modal will close automatically
                                }
                            });
                        }
                    } else {
                        console.error('Document synthesis failed:', data.message || 'Failed to synthesize document');
                        // Hide cancel button for informational messages
                        document.getElementById('confirmCancelBtn').style.display = 'none';
                        showConfirmModal({
                            title: 'Synthesis Failed',
                            message: 'Failed to synthesize document: ' + (data.message || 'Unknown error'),
                            confirmText: 'OK',
                            cancelText: '',
                            onConfirm: function() {
                                // Modal will close automatically
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error synthesizing document:', error);
                    // Hide cancel button for informational messages
                    document.getElementById('confirmCancelBtn').style.display = 'none';
                    showConfirmModal({
                        title: 'Error',
                        message: 'An error occurred while synthesizing the document. Please try again.',
                        confirmText: 'OK',
                        cancelText: '',
                        onConfirm: function() {
                            // Modal will close automatically
                        }
                    });
                });
            });

            // Close results panel
            document.getElementById('closeResultsBtn').addEventListener('click', function() {
                document.getElementById('resultsPanel').style.display = 'none';
            });

            // Document Hub Chatbot Handler
            document.getElementById('chatbotTool').addEventListener('click', function(e) {
                const fileIds = Array.from(selectedFiles);
                
                if (fileIds.length === 0) {
                    // Hide cancel button for informational messages
                    document.getElementById('confirmCancelBtn').style.display = 'none';
                    showConfirmModal({
                        title: 'No Documents Selected',
                        message: 'Please select at least one document to use the chatbot.',
                        confirmText: 'OK',
                        cancelText: '',
                        onConfirm: function() {
                            // Modal will close automatically
                        }
                    });
                    return;
                }

                document.getElementById('chatbotPanel').style.display = 'block';
                document.getElementById('resultsPanel').style.display = 'none';
                
                const chatContainer = document.getElementById('chatContainer');
                chatContainer.scrollTop = chatContainer.scrollHeight;
            });

            // Close chatbot panel
            document.getElementById('closeChatbotBtn').addEventListener('click', function() {
                document.getElementById('chatbotPanel').style.display = 'none';
            });

            // Chatbot form submission
            const chatbotForm = document.getElementById('chatbotForm');
            const chatbotQuestionInput = document.getElementById('chatbotQuestionInput');
            const chatContainer = document.getElementById('chatContainer');

            function addChatMessage(text, isUser = false) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `message ${isUser ? 'user' : 'bot'}`;

                const sender = isUser ? 'You' : 'StudyAid Bot';
                const icon = isUser ? 'bi-person-fill' : 'bi-robot';
                const time = new Date().toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                let parsedContent;
                if (isUser) {
                    // Escape HTML for user messages
                    const tempDiv = document.createElement('div');
                    tempDiv.textContent = text;
                    parsedContent = tempDiv.innerHTML;
                } else {
                    // Parse markdown for bot messages
                    parsedContent = marked.parse(text || '');
                }

                messageDiv.innerHTML = `
                    <div class="message-header">
                        <i class="bi ${icon}"></i>
                        <span>${sender}</span>
                    </div>
                    <div class="message-content">${parsedContent}</div>
                    <div class="message-time">${time}</div>
                `;

                chatContainer.appendChild(messageDiv);
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }

            chatbotForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const question = chatbotQuestionInput.value.trim();
                if (!question) return;

                const fileIds = Array.from(selectedFiles);
                if (fileIds.length === 0) {
                    // Hide cancel button for informational messages
                    document.getElementById('confirmCancelBtn').style.display = 'none';
                    showConfirmModal({
                        title: 'No Documents Selected',
                        message: 'Please select at least one document.',
                        confirmText: 'OK',
                        cancelText: '',
                        onConfirm: function() {
                            // Modal will close automatically
                        }
                    });
                    return;
                }

                addChatMessage(question, true);
                chatbotQuestionInput.value = '';

                // Show loading indicator
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'message bot';
                loadingDiv.innerHTML = '<div class="message-header"><i class="bi bi-robot"></i><span>StudyAid Bot</span></div><div class="message-content"><i class="bi bi-hourglass-split me-2"></i>Thinking...</div>';
                chatContainer.appendChild(loadingDiv);
                chatContainer.scrollTop = chatContainer.scrollHeight;

                try {
                    const response = await fetch('<?= BASE_PATH ?>lm/sendDocumentHubChat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            question: question,
                            fileIds: fileIds
                        })
                    });

                    const data = await response.json();

                    // Remove loading indicator
                    if (chatContainer.contains(loadingDiv)) {
                        chatContainer.removeChild(loadingDiv);
                    }

                    if (data.success) {
                        addChatMessage(data.response || data.message, false);
                    } else {
                        addChatMessage('Sorry, I encountered an error: ' + (data.message || 'Unknown error'), false);
                    }
                } catch (error) {
                    if (chatContainer.contains(loadingDiv)) {
                        chatContainer.removeChild(loadingDiv);
                    }
                    addChatMessage('Sorry, there was a network error. Please try again.', false);
                }
            });

            // Restore cancel button visibility when modal is hidden
            document.getElementById('confirmModal').addEventListener('hidden.bs.modal', function() {
                document.getElementById('confirmCancelBtn').style.display = '';
            });

            // Knowledge Base Search Handler
            document.getElementById('knowledgeBaseTool').addEventListener('click', function(e) {
                document.getElementById('knowledgeBasePanel').style.display = 'block';
                document.getElementById('chatbotPanel').style.display = 'none';
                document.getElementById('resultsPanel').style.display = 'none';
            });

            // Close knowledge base panel
            document.getElementById('closeKnowledgeBaseBtn').addEventListener('click', function() {
                document.getElementById('knowledgeBasePanel').style.display = 'none';
            });

            // Knowledge Base Search form submission
            const knowledgeBaseForm = document.getElementById('knowledgeBaseForm');
            const knowledgeBaseQueryInput = document.getElementById('knowledgeBaseQueryInput');
            const knowledgeBaseResults = document.getElementById('knowledgeBaseResults');

            function displaySearchResults(results, totalMatches, returned) {
                if (results.length === 0) {
                    knowledgeBaseResults.innerHTML = `
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No matching results found. Try different keywords.
                        </div>
                    `;
                    return;
                }

                let html = `<div class="mb-3"><small class="text-muted">Found ${totalMatches} match(es), showing top ${returned}</small></div>`;
                
                results.forEach((result, index) => {
                    const similarityPercent = (result.similarity * 100).toFixed(1);
                    const chunkText = result.chunkText || '';
                    const snippet = chunkText.length > 300 
                        ? chunkText.substring(0, 300) + '...' 
                        : chunkText;
                    
                    // Parse markdown for the snippet
                    const formattedSnippet = marked.parse(snippet);
                    
                    html += `
                        <div class="card mb-3" style="border-left: 3px solid var(--sa-primary);">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">
                                        <i class="bi bi-file-text me-2"></i>${result.fileName || 'Unknown'}
                                    </h6>
                                    <span class="badge bg-primary">${similarityPercent}% match</span>
                                </div>
                                <div class="card-text text-muted" style="font-size: 0.9rem; line-height: 1.6;">
                                    ${formattedSnippet}
                                </div>
                                <form method="POST" action="<?= DISPLAY_DOCUMENT ?>" style="display: inline;">
                                    <input type="hidden" name="file_id" value="${result.fileID}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="bi bi-arrow-right me-1"></i>Open Document
                                    </button>
                                </form>
                            </div>
                        </div>
                    `;
                });

                knowledgeBaseResults.innerHTML = html;
            }

            knowledgeBaseForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const query = knowledgeBaseQueryInput.value.trim();
                if (!query) return;

                // Show loading state
                knowledgeBaseResults.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Searching your knowledge base...</p>
                    </div>
                `;

                try {
                    const response = await fetch('<?= SEARCH_KNOWLEDGE_BASE ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            query: query
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        displaySearchResults(
                            data.results || [],
                            data.totalMatches || 0,
                            data.returned || 0
                        );
                    } else {
                        knowledgeBaseResults.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                ${data.message || 'An error occurred while searching.'}
                            </div>
                        `;
                    }
                } catch (error) {
                    console.error('Knowledge base search error:', error);
                    knowledgeBaseResults.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Sorry, there was a network error. Please try again.
                        </div>
                    `;
                }
            });

            // Initialize
            updateSelectedCount();
        });
    </script>
</body>

</html>