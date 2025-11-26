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

        .homework-item {
            border: 1px solid var(--sa-card-border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: white;
            transition: all 0.3s;
        }

        .homework-item:hover {
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.15);
        }

        .answer-content {
            background-color: #f8f9fa;
            border-left: 4px solid var(--sa-primary);
            padding: 20px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background-color: #cfe2ff;
            color: #084298;
        }

        .status-completed {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .status-no_question {
            background-color: #f8d7da;
            color: #842029;
        }

        .snackbar {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s;
        }

        .snackbar.show {
            transform: translateY(0);
            opacity: 1;
        }

        .snackbar.success {
            background-color: #28a745;
        }

        .snackbar.error {
            background-color: #dc3545;
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <?php require_once VIEW_SIDEBAR; ?>
        <main class="flex-grow-1 p-4">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="mb-0">
                            <i class="bi bi-journal-text me-2" style="color: var(--sa-primary);"></i>
                            Homework Helper
                        </h2>
                        <p class="text-muted">Upload your homework questions in PDF or image format and get AI-powered answers</p>
                    </div>
                </div>

                <!-- Upload Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-cloud-upload me-2"></i>
                            Upload Homework Question
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="homeworkForm" enctype="multipart/form-data">
                            <div class="upload-area" id="uploadArea">
                                <i class="bi bi-file-earmark-pdf" style="font-size: 3rem; color: var(--sa-primary);"></i>
                                <h5 class="mt-3 mb-2">Drag & Drop or Click to Upload</h5>
                                <p class="text-muted mb-3">Supports PDF and image files (JPG, PNG, GIF, BMP, WEBP)</p>
                                <input type="file" id="homeworkFile" name="homework_file" accept=".pdf,.jpg,.jpeg,.png,.gif,.bmp,.webp" style="display: none;" required>
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('homeworkFile').click()">
                                    <i class="bi bi-upload me-2"></i>Choose File
                                </button>
                                <div id="fileName" class="mt-3" style="display: none;">
                                    <p class="mb-0"><strong>Selected:</strong> <span id="selectedFileName"></span></p>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="bi bi-send me-2"></i>Process Homework
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Homework History -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Homework History
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="homeworkList">
                            <?php if (empty($homeworkEntries)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: var(--sa-muted);"></i>
                                    <p class="text-muted mt-3">No homework processed yet. Upload a file to get started!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($homeworkEntries as $entry): ?>
                                    <div class="homework-item" data-homework-id="<?= $entry['homeworkID'] ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <i class="bi bi-file-earmark me-2"></i>
                                                    <?= htmlspecialchars($entry['fileName']) ?>
                                                </h6>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    <?= date('Y-m-d H:i:s', strtotime($entry['createdAt'])) ?>
                                                </small>
                                            </div>
                                            <span class="status-badge status-<?= $entry['status'] ?>">
                                                <?= ucfirst(str_replace('_', ' ', $entry['status'])) ?>
                                            </span>
                                        </div>
                                        
                                        <?php if (!empty($entry['question'])): ?>
                                            <div class="mb-3">
                                                <strong><i class="bi bi-question-circle me-2"></i>Question:</strong>
                                                <p class="mb-0 mt-2" style="white-space: pre-wrap;"><?= htmlspecialchars($entry['question']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($entry['answer'])): ?>
                                            <div class="answer-content">
                                                <strong><i class="bi bi-lightbulb me-2"></i>Answer:</strong>
                                                <div class="mt-2" id="answer-<?= $entry['homeworkID'] ?>">
                                                    <?= $entry['answer'] ?>
                                                </div>
                                            </div>
                                        <?php elseif ($entry['status'] === 'processing'): ?>
                                            <div class="text-center py-3">
                                                <div class="loading-spinner"></div>
                                                <p class="text-muted mt-2 mb-0">Processing...</p>
                                            </div>
                                        <?php elseif ($entry['status'] === 'no_question'): ?>
                                            <div class="alert alert-warning mb-0">
                                                <i class="bi bi-exclamation-triangle me-2"></i>
                                                No question found.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Snackbar -->
    <div id="snackbar" class="snackbar"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        // Snackbar function
        function showSnackbar(message, type = 'success') {
            const snackbar = document.getElementById('snackbar');
            snackbar.textContent = message;
            snackbar.className = `snackbar ${type} show`;
            setTimeout(() => {
                snackbar.classList.remove('show');
            }, 5000);
        }

        // File input handling
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('homeworkFile');
        const fileName = document.getElementById('fileName');
        const selectedFileName = document.getElementById('selectedFileName');

        uploadArea.addEventListener('click', () => fileInput.click());

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
            const answerElements = document.querySelectorAll('[id^="answer-"]');
            answerElements.forEach(element => {
                if (element.textContent.trim()) {
                    try {
                        element.innerHTML = marked.parse(element.textContent);
                    } catch (e) {
                        console.error('Markdown parsing error:', e);
                    }
                }
            });
        });
    </script>
</body>

</html>


