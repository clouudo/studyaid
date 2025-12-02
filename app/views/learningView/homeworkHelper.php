<?php
// Ensure homeworkEntries is defined
if (!isset($homeworkEntries)) {
    $homeworkEntries = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homework Helper - StudyAid</title>
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

        .card {
            border: 1px solid var(--sa-card-border);
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(111, 66, 193, 0.08);
            overflow: visible;
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

        /* Upload Area Styles */
        .upload-area {
            border: 2px dashed var(--sa-card-border);
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            background-color: #f8f9fa;
            transition: all 0.3s;
            cursor: pointer;
        }

        .upload-area:hover {
            border-color: var(--sa-primary);
            background-color: var(--sa-accent);
        }

        .upload-area.dragover {
            border-color: var(--sa-primary);
            background-color: var(--sa-accent-strong);
        }

        /* List Group Styles (Consolidated with Flashcards) */
        .list-group-item {
            border-left: none;
            border-right: none;
            padding: 1.25rem;
            transition: background-color 0.2s;
        }

        .list-group-item:first-child {
            border-top: none;
        }
        
        .list-group-item:last-child {
            border-bottom: none;
        }

        /* Action Button (Three dots) */
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

        /* Dropdown Menu */
        .dropdown-menu {
            border-radius: 12px !important;
            border: 1px solid #d4b5ff !important;
            box-shadow: 0 10px 24px rgba(90, 50, 163, 0.12) !important;
            padding: 8px 0 !important;
            min-width: 200px;
            z-index: 1050;
        }

        .dropdown-item {
            padding: 8px 16px;
            cursor: pointer;
        }
        
        .dropdown-item:hover, .dropdown-item:focus {
            background-color: var(--sa-accent);
            color: var(--sa-primary);
        }
        
        .dropdown-item:active {
            background-color: var(--sa-primary);
            color: white;
        }

        .dropdown-header {
            color: var(--sa-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            padding: 8px 16px 4px;
        }

        /* Status Badges - Removed custom class to use Bootstrap default */
        
        /* Answer Content */
        .answer-content {
            background-color: #f8f9fa;
            border-left: 4px solid var(--sa-primary);
            padding: 20px;
            border-radius: 8px;
            margin-top: 15px;
        }

        /* Snackbar */
        .snackbar {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background-color: #333;
            color: white;
            padding: 16px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            opacity: 0;
            transition: all 0.3s ease;
            min-width: 300px;
            max-width: 500px;
        }
        
        .snackbar.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        
        .snackbar.success { background-color: #28a745; }
        .snackbar.error { background-color: #dc3545; }
        
        .snackbar-icon { font-size: 1.2rem; }
        .snackbar-message { flex: 1; font-size: 0.95rem; }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
        
        .upload-container {
            background-color: #f8f9fa;
            padding: 30px;
            min-height: 100%;
        }
        
        .view-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--sa-muted);
            margin-bottom: 0.5rem;
            display: block;
        }

        /* Collapsible Homework Header */
        .homework-header {
            user-select: none;
            padding: 4px;
            border-radius: 8px;
            transition: background-color 0.2s;
        }

        .homework-header:hover {
            background-color: rgba(111, 66, 193, 0.05);
        }

        .collapse-icon {
            display: inline-block;
            transition: transform 0.3s ease;
            color: var(--sa-primary);
        }

        .homework-header[aria-expanded="true"] .collapse-icon {
            transform: rotate(90deg);
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
                <h3 style="color: #212529; font-size: 1.5rem; font-weight: 600; margin-bottom: 30px;">Homework Helper</h3>

                <!-- Upload Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Generate with AI</h5>
                        <small class="text-muted">Upload PDF or image to get AI answers.</small>
                    </div>
                    <div class="card-body">
                        <form id="homeworkForm" enctype="multipart/form-data">
                            <div class="upload-area" id="uploadArea">
                                <i class="bi bi-cloud-upload" style="font-size: 3rem; color: var(--sa-primary);"></i>
                                <h5 class="mt-3 mb-2">Drag & Drop or Click to Upload</h5>
                                <p class="text-muted mb-3">Supports PDF and image files (JPG, PNG, WEBP)</p>
                                <input type="file" id="homeworkFile" name="homework_file" accept=".pdf,.jpg,.jpeg,.png,.gif,.bmp,.webp" style="display: none;" required>
                                <div id="fileName" class="mt-3" style="display: none;">
                                    <p class="mb-0"><strong>Selected:</strong> <span id="selectedFileName"></span></p>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <label for="homeworkInstruction" class="form-label">Instructions (Optional)</label>
                                <textarea class="form-control" id="homeworkInstruction" name="instruction" rows="2" placeholder="Describe your instruction"></textarea>
                            </div>

                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-primary" id="submitBtn">Process Homework</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Homework History -->
                <div class="card">
                    <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Homework History</h5>
                            <small class="text-muted d-block">Your past questions and answers</small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" id="homeworkList">
                            <?php if (empty($homeworkEntries)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: var(--sa-muted);"></i>
                                    <p class="text-muted mt-3">No homework processed yet. Upload a file to get started!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($homeworkEntries as $entry): ?>
                                    <div class="list-group-item">
                                        <!-- Header Row (Clickable) -->
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1 homework-header" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#homework-content-<?= $entry['homeworkID'] ?>" aria-expanded="false" aria-controls="homework-content-<?= $entry['homeworkID'] ?>">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <i class="bi bi-chevron-right collapse-icon" style="transition: transform 0.3s ease;"></i>
                                                    <strong class="h6 mb-0"><?= htmlspecialchars($entry['fileName']) ?></strong>
                                                    <?php
                                                    $statusClass = 'bg-secondary';
                                                    if ($entry['status'] === 'completed') $statusClass = 'bg-success';
                                                    elseif ($entry['status'] === 'processing') $statusClass = 'bg-primary';
                                                    elseif ($entry['status'] === 'no_question') $statusClass = 'bg-danger';
                                                    elseif ($entry['status'] === 'pending') $statusClass = 'bg-warning text-dark';
                                                    ?>
                                                    <span class="badge rounded-pill <?= $statusClass ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $entry['status'])) ?>
                                                    </span>
                                                </div>
                                                <small class="text-muted d-block ms-4">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    <?= date('Y-m-d H:i:s', strtotime($entry['createdAt'])) ?>
                                                </small>
                                                <?php if (!empty($entry['instruction'])): ?>
                                                    <div class="mt-1 text-muted small ms-4">
                                                        <i class="bi bi-info-circle me-1"></i>
                                                        Instruction: <?= htmlspecialchars($entry['instruction']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Actions Dropdown -->
                                            <div class="dropdown">
                                                <button class="action-btn" type="button" id="dropdownAction<?= $entry['homeworkID'] ?>" data-bs-toggle="dropdown" aria-expanded="false" onclick="event.stopPropagation();">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownAction<?= $entry['homeworkID'] ?>">
                                                    <li><a class="dropdown-item" href="<?= VIEW_HOMEWORK_FILE ?>&id=<?= $entry['homeworkID'] ?>" target="_blank" onclick="event.stopPropagation();"><i class="bi bi-file-earmark me-2"></i>View Original File</a></li>
                                                    <li>
                                                        <button type="button" class="dropdown-item text-danger delete-homework-btn" 
                                                           data-homework-id="<?= $entry['homeworkID'] ?>"
                                                           data-homework-name="<?= htmlspecialchars($entry['fileName']) ?>">
                                                            <i class="bi bi-trash me-2"></i>Delete
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                        <!-- Content Display Area (Collapsible) -->
                                        <div class="collapse" id="homework-content-<?= $entry['homeworkID'] ?>">
                                            <div class="mt-3">
                                                <?php if ($entry['status'] === 'completed' || $entry['status'] === 'no_question'): ?>
                                                    <div id="content-wrapper-<?= $entry['homeworkID'] ?>">
                                                        
                                                        <!-- Answer View -->
                                                        <div id="answer-content-<?= $entry['homeworkID'] ?>" class="view-section">
                                                            <span class="view-label">Answer</span>
                                                            <?php if ($entry['status'] === 'no_question'): ?>
                                                                <div class="alert alert-warning mb-0">
                                                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                                                    <?= htmlspecialchars($entry['answer'] ?? 'No question found in the uploaded document. Please upload a document that contains questions or problems to solve.') ?>
                                                                </div>
                                                            <?php else: ?>
                                                                <div class="answer-content mt-0">
                                                                    <div id="answer-text-<?= $entry['homeworkID'] ?>">
                                                                        <?= $entry['answer'] ?>
                                                                    </div>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Extracted Text View -->
                                                        <div id="text-content-<?= $entry['homeworkID'] ?>" class="view-section d-none">
                                                            <span class="view-label">Extracted Text</span>
                                                            <div class="p-3 bg-light rounded border" style="max-height: 300px; overflow-y: auto;">
                                                                <pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;"><?= htmlspecialchars($entry['extractedText'] ?? 'No text extracted.') ?></pre>
                                                            </div>
                                                        </div>
                                                    </div>

                                                <?php elseif ($entry['status'] === 'processing'): ?>
                                                    <div class="text-center py-3">
                                                        <div class="loading-spinner" style="border-color: var(--sa-primary); border-top-color: transparent;"></div>
                                                        <p class="text-muted mt-2 mb-0">Processing your file...</p>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-secondary mb-0">
                                                        Status: <?= htmlspecialchars($entry['status']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        // Snackbar function
        function showSnackbar(message, type = 'success') {
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
            setTimeout(() => {
                snackbar.classList.remove('show');
            }, 5000);
        }


        // File input handling
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('homeworkFile');
        const fileName = document.getElementById('fileName');
        const selectedFileName = document.getElementById('selectedFileName');

        uploadArea.addEventListener('click', (e) => {
             if(e.target.tagName !== 'BUTTON' && e.target.tagName !== 'I') {
                 fileInput.click();
             }
        });

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect();
            }
        });

        fileInput.addEventListener('change', handleFileSelect);

        function handleFileSelect() {
            if (fileInput.files.length > 0) {
                selectedFileName.textContent = fileInput.files[0].name;
                fileName.style.display = 'block';
            } else {
                fileName.style.display = 'none';
            }
        }

        // Form submission
        const homeworkForm = document.getElementById('homeworkForm');
        const submitBtn = document.getElementById('submitBtn');

        homeworkForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!fileInput.files.length) {
                showSnackbar('Please select a file to upload', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('homework_file', fileInput.files[0]);
            const instruction = document.getElementById('homeworkInstruction').value;
            if (instruction) {
                formData.append('instruction', instruction);
            }

            // Disable submit button and show loading
            submitBtn.disabled = true;
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="loading-spinner"></span> Processing...';

            try {
                const response = await fetch('<?= PROCESS_HOMEWORK ?>', {
                    method: 'POST',
                    body: formData
                });

                let data;
                try {
                    const responseText = await response.text();
                    if (!responseText) {
                        throw new Error('Empty response from server');
                    }
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    throw new Error('Invalid response format from server');
                }

                if (data.success) {
                    showSnackbar('Homework processed successfully!', 'success');
                    
                    // Reset form
                    homeworkForm.reset();
                    fileName.style.display = 'none';
                    
                    // Reload page to show new entry
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showSnackbar(data.message || 'Failed to process homework', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showSnackbar('Network error. Please check your connection and try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });

        // Parse markdown in answer content
        document.addEventListener('DOMContentLoaded', () => {
            const answerElements = document.querySelectorAll('[id^="answer-text-"]');
            answerElements.forEach(element => {
                // Skip if element is empty or already contains HTML tags (already processed)
                if (!element.textContent.trim() || element.children.length > 0) {
                    return;
                }
                
                try {
                    // Get the raw text content
                    let markdownText = element.textContent;
                    
                    // Trim leading and trailing whitespace/newlines
                    markdownText = markdownText.trim();
                    
                    // Fix lines that start with markdown syntax but have leading whitespace
                    // This ensures headings, lists, etc. are recognized even with leading spaces
                    const lines = markdownText.split('\n');
                    const cleanedLines = lines.map(line => {
                        const trimmed = line.trimStart();
                        // If line starts with markdown syntax (#, -, *, >, etc.) after whitespace,
                        // remove the leading whitespace to ensure proper parsing
                        if (trimmed.match(/^(#{1,6}\s|[-*+]\s|>\s|\d+\.\s)/)) {
                            return trimmed;
                        }
                        // Preserve intentional indentation for code blocks and nested content
                        return line;
                    });
                    
                    markdownText = cleanedLines.join('\n');
                    
                    // Parse markdown with options
                    const html = marked.parse(markdownText, {
                        gfm: true,
                        breaks: false,
                        headerIds: true,
                        mangle: false
                    });
                    
                    element.innerHTML = html;
                } catch (e) {
                    console.error('Markdown parsing error:', e);
                }
            });

            // Handle collapse icon rotation for homework entries
            const collapseElements = document.querySelectorAll('[id^="homework-content-"]');
            collapseElements.forEach(collapseElement => {
                collapseElement.addEventListener('show.bs.collapse', function() {
                    const header = document.querySelector(`[data-bs-target="#${this.id}"]`);
                    if (header) {
                        header.setAttribute('aria-expanded', 'true');
                    }
                });
                
                collapseElement.addEventListener('hide.bs.collapse', function() {
                    const header = document.querySelector(`[data-bs-target="#${this.id}"]`);
                    if (header) {
                        header.setAttribute('aria-expanded', 'false');
                    }
                });
            });
        });

        // Delete homework handler - attach directly to buttons
        function initDeleteHandlers() {
            document.querySelectorAll('.delete-homework-btn').forEach(deleteBtn => {
                deleteBtn.addEventListener('click', async (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    const homeworkId = deleteBtn.dataset.homeworkId;
                    const homeworkName = deleteBtn.dataset.homeworkName;
                    
                    // Confirmation dialog
                    const confirmed = confirm(`Are you sure you want to delete "${homeworkName}"?\n\nThis action cannot be undone.`);
                    
                    if (!confirmed) {
                        return;
                    }
                    
                    // Disable button during deletion
                    deleteBtn.disabled = true;
                    const originalText = deleteBtn.innerHTML;
                    deleteBtn.innerHTML = '<span class="loading-spinner" style="width:16px;height:16px;border-width:2px;"></span> Deleting...';
                    
                    try {
                        const formData = new FormData();
                        formData.append('homework_id', homeworkId);
                        
                        const response = await fetch('<?= DELETE_HOMEWORK ?>', {
                            method: 'POST',
                            body: formData
                        });
                        
                        let data;
                        try {
                            const responseText = await response.text();
                            if (!responseText) {
                                throw new Error('Empty response from server');
                            }
                            data = JSON.parse(responseText);
                        } catch (parseError) {
                            console.error('JSON Parse Error:', parseError);
                            throw new Error('Invalid response format from server');
                        }
                        
                        if (data.success) {
                            showSnackbar(data.message || 'Homework entry deleted successfully!', 'success');
                            
                            // Remove the list item from DOM with animation
                            const listItem = deleteBtn.closest('.list-group-item');
                            if (listItem) {
                                listItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                                listItem.style.opacity = '0';
                                listItem.style.transform = 'translateX(-20px)';
                                
                                setTimeout(() => {
                                    listItem.remove();
                                    
                                    // Check if list is now empty
                                    const remainingItems = document.querySelectorAll('#homeworkList .list-group-item');
                                    if (remainingItems.length === 0) {
                                        document.getElementById('homeworkList').innerHTML = `
                                            <div class="text-center py-5">
                                                <i class="bi bi-inbox" style="font-size: 3rem; color: var(--sa-muted);"></i>
                                                <p class="text-muted mt-3">No homework processed yet. Upload a file to get started!</p>
                                            </div>
                                        `;
                                    }
                                }, 300);
                            }
                        } else {
                            showSnackbar(data.message || 'Failed to delete homework entry.', 'error');
                            // Restore button
                            deleteBtn.disabled = false;
                            deleteBtn.innerHTML = originalText;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showSnackbar('An error occurred while deleting. Please try again.', 'error');
                        // Restore button
                        deleteBtn.disabled = false;
                        deleteBtn.innerHTML = originalText;
                    }
                });
            });
        }
        
        // Initialize delete handlers when DOM is ready
        document.addEventListener('DOMContentLoaded', initDeleteHandlers);
    </script>
</body>
</html>