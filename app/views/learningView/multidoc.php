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

        /* Themed Checkboxes */
        .form-check-input:checked {
            background-color: var(--sa-primary);
            border-color: var(--sa-primary);
        }
        
        .form-check-input:focus {
            border-color: var(--sa-primary);
            box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.25);
        }
        
        /* Modal checklist styles */
        #modalDocumentChecklist {
            font-size: 0.9rem;
        }
        
        #modalDocumentChecklist .form-check {
            padding: 0.25rem 0;
        }
        
        #modalDocumentChecklist .form-check-label {
            cursor: pointer;
            user-select: none;
        }
        
        #modalDocumentChecklist .modal-folder-children {
            margin-top: 0.5rem;
            border-left: 2px solid var(--sa-accent);
            padding-left: 1rem;
        }
        
        #modalDocumentChecklist .modal-document-checkbox:checked + label {
            color: var(--sa-primary);
            font-weight: 500;
        }
        
        #modalDocumentChecklist .modal-folder-checkbox:checked + label {
            color: var(--sa-primary-dark);
        }
        
        /* Fix checkbox z-index and pointer events */
        #modalDocumentChecklist .form-check-input {
            position: relative;
            z-index: 10;
            pointer-events: auto;
            cursor: pointer;
            margin-top: 0.25rem;
        }
        
        #modalDocumentChecklist .form-check-label {
            position: relative;
            z-index: 1;
            pointer-events: none;
            cursor: default;
        }
        
        #modalDocumentChecklist .form-check-label .expand-folder-btn {
            pointer-events: auto;
            cursor: pointer;
            user-select: none;
        }
        
        #modalDocumentChecklist .expand-folder-btn:hover {
            opacity: 0.7;
        }
        
        /* Ensure checkbox is always clickable and visible */
        #modalDocumentChecklist .form-check-input:hover {
            z-index: 11;
        }
        
        #modalDocumentChecklist .form-check-input:focus {
            z-index: 11;
            box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.25);
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
                <p class="text-muted mb-4">Choose a tool to generate content.</p>

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
                                <option value="customize">Customize</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="reportDescription" class="form-label">Describe the document</label>
                            <textarea class="form-control" id="reportDescription" name="reportDescription" rows="3" placeholder="Describe the document"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="modalDocumentChecklist">
                                Selected Documents 
                                <small class="selected-count-badge text-muted ms-2">(0/3)</small>
                            </label>
                            <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto; background-color: #f8f9fa;">
                                <div id="modalDocumentChecklist">
                                    <!-- Hierarchical checklist will be populated here -->
                                </div>
                            </div>
                            <small class="form-text text-muted">Select documents from the checklist above</small>
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

    <!-- Loading Modal for Document Synthesis -->
    <div class="modal fade" id="loadingModal" tabindex="-1" aria-labelledby="loadingModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content loading-modal-content">
                <div class="modal-body loading-modal-body">
                    <div class="loading-icon-wrapper">
                        <div class="spinner-border text-primary loading-spinner" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <p class="loading-message" id="loadingMessage">Generating your document...</p>
                    <p class="loading-submessage text-muted">This may take a few moments. Please wait.</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Loading Modal Styles */
        .loading-modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .loading-modal-body {
            padding: 40px 24px;
            text-align: center;
        }

        .loading-icon-wrapper {
            margin-bottom: 24px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .loading-spinner {
            width: 4rem;
            height: 4rem;
            border-width: 0.4rem;
        }

        .loading-message {
            color: #212529;
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0 0 8px 0;
            line-height: 1.6;
        }

        .loading-submessage {
            font-size: 0.95rem;
            margin: 0;
        }

        /* Prevent closing loading modal by clicking backdrop */
        #loadingModal {
            pointer-events: auto;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        // Pass data from PHP to JavaScript
        const ALL_FOLDERS = <?= json_encode($allUserFolders ?? []) ?>;
        const ALL_FILES = <?= json_encode($fileList ?? []) ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            let selectedFiles = new Set();
            let selectedFolders = new Set();
            const MAX_SELECTION = 5; // Maximum number of documents that can be selected

            // Initialize Bootstrap modals
            const reportFormModal = new bootstrap.Modal(document.getElementById('reportFormModal'));
            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'), {
                backdrop: 'static',
                keyboard: false
            });
            
            // Function to show loading modal
            function showLoadingModal(message = 'Generating your document...') {
                document.getElementById('loadingMessage').textContent = message;
                loadingModal.show();
            }
            
            // Function to hide loading modal
            function hideLoadingModal() {
                loadingModal.hide();
            }
            
            // Set up folder expansion listener using event delegation on document
            // This works for dynamically created elements
            document.addEventListener('click', function(e) {
                // Only handle clicks within the modal checklist
                const checklistContainer = document.getElementById('modalDocumentChecklist');
                if (!checklistContainer || !checklistContainer.contains(e.target)) {
                    return;
                }
                
                // Check if clicking on expand button (chevron or folder name)
                const expandBtn = e.target.closest('.expand-folder-btn');
                if (!expandBtn) return;
                
                // Don't trigger if clicking checkbox
                if (e.target.type === 'checkbox' || e.target.closest('input[type="checkbox"]')) {
                    return;
                }
                
                e.preventDefault();
                e.stopPropagation();
                
                const label = expandBtn.closest('.modal-folder-label');
                if (!label) return;
                
                // Find the checkbox by the label's 'for' attribute
                const checkboxId = label.getAttribute('for');
                const checkbox = document.getElementById(checkboxId);
                if (!checkbox) return;
                
                const folderId = checkbox.dataset.folderId;
                const folderChildren = document.getElementById(`modal_folder_children_${folderId}`);
                const chevron = label.querySelector('.modal-folder-chevron');
                
                if (folderChildren) {
                    const currentDisplay = window.getComputedStyle(folderChildren).display;
                    const isVisible = currentDisplay !== 'none';
                    folderChildren.style.display = isVisible ? 'none' : 'block';
                    
                    // Update chevron icon
                    if (chevron) {
                        if (isVisible) {
                            chevron.classList.remove('bi-chevron-down');
                            chevron.classList.add('bi-chevron-right');
                        } else {
                            chevron.classList.remove('bi-chevron-right');
                            chevron.classList.add('bi-chevron-down');
                        }
                    }
                }
            });

            // Function to build hierarchical folder/file structure for modal checklist
            function buildModalChecklist() {
                const checklistContainer = document.getElementById('modalDocumentChecklist');
                
                if (!ALL_FOLDERS || ALL_FOLDERS.length === 0 && (!ALL_FILES || ALL_FILES.length === 0)) {
                    checklistContainer.innerHTML = '<span class="text-muted">No documents available</span>';
                    return;
                }

                let html = '';
                
                // Helper function to build folder tree recursively
                function buildFolderTree(parentId = null, level = 0) {
                    let folderHtml = '';
                    const paddingLeft = level * 20;
                    
                    ALL_FOLDERS.forEach(folder => {
                        if (folder.parentFolderId == parentId) {
                            const folderId = folder.folderID;
                            const folderFiles = ALL_FILES.filter(f => f.folderID == folderId);
                            
                            folderHtml += `
                                <div class="mb-2" style="padding-left: ${paddingLeft}px;">
                                    <div class="d-flex align-items-center">
                                        <input class="form-check-input modal-folder-checkbox" type="checkbox" 
                                            id="modal_folder_${folderId}" 
                                            data-folder-id="${folderId}"
                                            data-level="${level}"
                                            style="flex-shrink: 0; margin-right: 0.5rem;">
                                        <div class="flex-grow-1">
                                            <label class="form-check-label d-flex align-items-center modal-folder-label" 
                                                for="modal_folder_${folderId}"
                                                style="margin-left: 0;">
                                                <i class="bi bi-chevron-right modal-folder-chevron me-1 expand-folder-btn" style="font-size: 0.8rem; cursor: pointer;"></i>
                                                <i class="bi bi-folder-fill me-2 text-warning"></i>
                                                <strong class="expand-folder-btn" style="cursor: pointer;" >${escapeHtml(folder.name)}</strong>
                                                <small class="text-muted ms-2">(${folderFiles.length} file${folderFiles.length !== 1 ? 's' : ''})</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="modal-folder-children ms-4" id="modal_folder_children_${folderId}" style="display: none;">
                            `;
                            
                            // Add files in this folder
                            folderFiles.forEach(file => {
                                const isChecked = selectedFiles.has(file.fileID.toString());
                                folderHtml += `
                                    <div class="d-flex align-items-center mt-1">
                                        <input class="form-check-input modal-document-checkbox" type="checkbox" 
                                            id="modal_file_${file.fileID}" 
                                            data-file-id="${file.fileID}"
                                            ${isChecked ? 'checked' : ''}
                                            style="flex-shrink: 0; margin-right: 0.5rem;">
                                        <label class="form-check-label d-flex align-items-center" for="modal_file_${file.fileID}" style="margin-left: 0;">
                                            <i class="bi bi-file-text me-2 text-primary"></i>
                                            ${escapeHtml(file.name)}
                                        </label>
                                    </div>
                                `;
                            });
                            
                            // Recursively add child folders
                            folderHtml += buildFolderTree(folderId, level + 1);
                            
                            folderHtml += '</div></div>';
                        }
                    });
                    
                    return folderHtml;
                }
                
                // Build root folders
                html += buildFolderTree(null, 0);
                
                // Add root-level files (files without folders)
                const rootFiles = ALL_FILES.filter(f => !f.folderID);
                if (rootFiles.length > 0) {
                    html += '<div class="mt-2"><strong>Home Documents</strong></div>';
                    rootFiles.forEach(file => {
                        const isChecked = selectedFiles.has(file.fileID.toString());
                        html += `
                            <div class="d-flex align-items-center mt-1">
                                <input class="form-check-input modal-document-checkbox" type="checkbox" 
                                    id="modal_file_${file.fileID}" 
                                    data-file-id="${file.fileID}"
                                    ${isChecked ? 'checked' : ''}
                                    style="flex-shrink: 0; margin-right: 0.5rem;">
                                <label class="form-check-label d-flex align-items-center" for="modal_file_${file.fileID}" style="margin-left: 0;">
                                    <i class="bi bi-file-text me-2 text-primary"></i>
                                    ${escapeHtml(file.name)}
                                </label>
                            </div>
                        `;
                    });
                }
                
                checklistContainer.innerHTML = html || '<span class="text-muted">No documents available</span>';
                
                // Attach event listeners to new checkboxes
                attachModalChecklistListeners();
            }
            
            // Helper function to escape HTML
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
            
            // Function to attach event listeners to modal checklist
            function attachModalChecklistListeners() {
                // Handle folder checkbox clicks (for selecting all files)
                document.querySelectorAll('.modal-folder-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', function(e) {
                        e.stopPropagation();
                        const folderId = this.dataset.folderId;
                        const folderChildren = document.getElementById(`modal_folder_children_${folderId}`);
                        const isChecked = this.checked;
                        
                        // Select/deselect all files in folder
                        if (folderChildren) {
                            const fileCheckboxes = folderChildren.querySelectorAll('.modal-document-checkbox');
                            const filesToSelect = Array.from(fileCheckboxes);
                            
                            if (isChecked) {
                                // Check if selecting all files in folder would exceed limit
                                const currentCount = selectedFiles.size;
                                const folderFileCount = filesToSelect.length;
                                
                                if (currentCount + folderFileCount > MAX_SELECTION) {
                                    this.checked = false;
                                    document.getElementById('confirmCancelBtn').style.display = 'none';
                                    showConfirmModal({
                                        title: 'Selection Limit Reached',
                                        message: `Selecting all files in this folder would exceed the maximum limit of ${MAX_SELECTION} documents. Please select individual files instead.`,
                                        confirmText: 'OK',
                                        onConfirm: function() {}
                                    });
                                    return;
                                }
                                
                                // Select all files in folder
                                filesToSelect.forEach(fileCb => {
                                    fileCb.checked = true;
                                    selectedFiles.add(fileCb.dataset.fileId);
                                });
                            } else {
                                // Deselect all files in folder
                                filesToSelect.forEach(fileCb => {
                                    fileCb.checked = false;
                                    selectedFiles.delete(fileCb.dataset.fileId);
                                });
                            }
                        }
                        
                        updateModalSelectedCount();
                    });
                });
                
                // Handle document checkbox clicks
                document.querySelectorAll('.modal-document-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', function(e) {
                        const fileId = this.dataset.fileId;
                        
                        if (this.checked) {
                            if (selectedFiles.size >= MAX_SELECTION) {
                                this.checked = false;
                                document.getElementById('confirmCancelBtn').style.display = 'none';
                                showConfirmModal({
                                    title: 'Selection Limit Reached',
                                    message: `You can only select a maximum of ${MAX_SELECTION} documents.`,
                                    confirmText: 'OK',
                                    onConfirm: function() {}
                                });
                                return;
                            }
                            selectedFiles.add(fileId);
                        } else {
                            selectedFiles.delete(fileId);
                        }
                        
                        updateModalSelectedCount();
                    });
                });
            }
            
            // Function to update selected count in modal
            function updateModalSelectedCount() {
                const count = selectedFiles.size;
                const countSpan = document.querySelector('label[for="modalDocumentChecklist"] .selected-count-badge');
                if (countSpan) {
                    countSpan.textContent = `(${count}/${MAX_SELECTION})`;
                    // Update color based on count
                    if (count >= MAX_SELECTION) {
                        countSpan.classList.remove('text-muted', 'text-primary');
                        countSpan.classList.add('text-danger');
                    } else if (count > 0) {
                        countSpan.classList.remove('text-muted', 'text-danger');
                        countSpan.classList.add('text-primary');
                    } else {
                        countSpan.classList.remove('text-primary', 'text-danger');
                        countSpan.classList.add('text-muted');
                    }
                }
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
                } else if (reportType === 'customize') {
                    reportDescriptionElement.value = '';
                }
            }


            // Update description when document type changes
            document.getElementById('reportType').addEventListener('change', function() {
                updateDescriptionByReportType();
            });

            // Synthesize Document Handler - Opens Form Modal when card is clicked
            document.getElementById('reportTool').addEventListener('click', function(e) {
                // Reset form and clear selections
                document.getElementById('reportGenerationForm').reset();
                selectedFiles.clear();
                
                // Build the checklist in the modal
                buildModalChecklist();
                updateModalSelectedCount();
                updateDescriptionByReportType();
                
                // Show the form modal
                reportFormModal.show();
            });
            
            // When modal is shown, ensure checklist is built
            document.getElementById('reportFormModal').addEventListener('show.bs.modal', function() {
                buildModalChecklist();
                updateModalSelectedCount();
                updateDescriptionByReportType();
            });

            // Form Submission Handler
            document.getElementById('reportGenerationForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form values
                const reportDescription = document.getElementById('reportDescription').value.trim();
                const reportType = document.getElementById('reportType').value;
                const fileIds = Array.from(selectedFiles);
                
                // Validate that at least 2 documents are selected
                if (fileIds.length < 2) {
                    document.getElementById('confirmCancelBtn').style.display = 'none';
                    showConfirmModal({
                        title: 'Insufficient Documents Selected',
                        message: 'Please select at least 2 documents to synthesize.',
                        confirmText: 'OK',
                        onConfirm: function() {}
                    });
                    return;
                }

                // Close form modal
                reportFormModal.hide();
                
                // Show loading modal
                showLoadingModal('Generating your document...');

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
                    // Hide loading modal
                    hideLoadingModal();
                    
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
                    // Hide loading modal
                    hideLoadingModal();
                    
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
                // No document selection required - chatbot uses RAG to search all user documents
                document.getElementById('chatbotPanel').style.display = 'block';
                document.getElementById('resultsPanel').style.display = 'none';
                document.getElementById('knowledgeBasePanel').style.display = 'none';
                
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

                // No document selection required - RAG will search all user documents automatically
                addChatMessage(question, true);
                chatbotQuestionInput.value = '';

                // Show loading indicator
                const loadingDiv = document.createElement('div');
                loadingDiv.className = 'message bot';
                loadingDiv.innerHTML = '<div class="message-header"><i class="bi bi-robot"></i><span>StudyAid Bot</span></div><div class="message-content"><i class="bi bi-hourglass-split me-2"></i>Searching your documents...</div>';
                chatContainer.appendChild(loadingDiv);
                chatContainer.scrollTop = chatContainer.scrollHeight;

                try {
                    const response = await fetch('<?= BASE_PATH ?>lm/sendDocumentHubChat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            question: question
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
                const query = knowledgeBaseQueryInput.value.trim();
                
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
                
                // Escape special characters for regex
                const escapeRegExp = (string) => {
                    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                };

                const keywords = query.split(/\s+/).filter(k => k.length > 0).map(escapeRegExp);
                
                results.forEach((result, index) => {
                    const similarityPercent = (result.similarity * 100).toFixed(1);
                    const chunkText = result.chunkText || '';
                    let snippet = chunkText.length > 100 
                        ? chunkText.substring(0, 1000) + '...' 
                        : chunkText;
                    
                    // Highlight keywords in the snippet (markdown source) using <mark> tags
                    // We do this before markdown parsing so it persists
                    if (keywords.length > 0) {
                        const pattern = new RegExp(`(${keywords.join('|')})`, 'gi');
                        snippet = snippet.replace(pattern, '<mark style="background-color: #fff3cd; padding: 0 2px; border-radius: 2px;">$1</mark>');
                    }

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