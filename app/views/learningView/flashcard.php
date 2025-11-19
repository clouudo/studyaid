<?php
$flashcardSets = [];
if (isset($flashcards) && is_array($flashcards)) {
    foreach ($flashcards as $flashcard) {
        $flashcardSets[$flashcard['title']][] = $flashcard;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flashcards - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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

        .card-header h5 {
            color: inherit;
            font-weight: 600;
        }

        .nav-item.active .nav-link {
            background-color: #e7d5ff !important;
            color: #6f42e1 !important;
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

        .flashcard-container {
            perspective: 1000px;
            width: 100%;
            max-width: 600px;
            height: 400px;
            margin: 0 auto;
        }

        #manualFlashcardTitle.form-control {
            margin-bottom: 30px !important;
        }

        .form-range {
            accent-color: #6f42c1;
        }

        .form-range::-webkit-slider-thumb {
            width: 20px;
            height: 20px;
            background-color: #6f42c1;
            border: 3px solid #ffffff;
            box-shadow: 0 3px 6px rgba(111, 66, 193, 0.35);
        }

        .form-range::-moz-range-thumb {
            width: 20px;
            height: 20px;
            background-color: #6f42c1;
            border: 3px solid #ffffff;
            box-shadow: 0 3px 6px rgba(111, 66, 193, 0.35);
        }

        .form-range::-webkit-slider-runnable-track,
        .form-range::-moz-range-track {
            background: linear-gradient(90deg, #e7d5ff 0%, #cdb0ff 100%);
            height: 0.35rem;
            border-radius: 999px;
        }

        .flashcard-pairs .flashcard-pair {
            border: 1px solid #e7d5ff;
            border-radius: 12px;
            padding: 16px;
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.08);
        }

        .flashcard-pairs .btn-remove-pair {
            color: #dc3545;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            background-color: #ffeef2;
            border: 1px solid #f8d7da;
            transition: all 0.2s;
        }

        .flashcard-pairs .btn-remove-pair:hover {
            background-color: #dc3545;
            color: #fff;
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

        .dropdown-item {
            margin: 0px !important;
        }

        .btn-back {
            background-color: transparent;
            border: none;
            color: #6f42c1;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .btn-back:hover {
            background-color: #6f42c1;
            color: white;
        }

        .flashcard {
            width: 100%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.6s;
        }

        .flashcard.flipped {
            transform: rotateY(180deg);
        }


        .flashcard-front,
        .flashcard-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            text-align: center;
        }

        .flashcard-front {
            background-color: #6f42c1;
            color: #fff;
        }

        .flashcard-back {
            background-color: #fff;
            color: #333;
            border: 2px solid #6f42c1;
            transform: rotateY(180deg);
        }

        .flashcard-content {
            font-size: 1.25rem;
            font-weight: 500;
        }

        

        .flashcard-counter {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background-color: rgba(0, 0, 0, 0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
        }

        .btn-check:checked+.btn-outline-secondary {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: #fff;
        }

        .btn-check:checked+.btn-outline-secondary:hover {
            background-color: #593093;
            border-color: #593093;
        }

        /* Ensure dropdowns can render outside of list/card without being clipped */
        .card {
            overflow: visible;
        }

        .card-body {
            overflow: visible;
        }

        #flashcardList {
            overflow: visible;
        }

        #flashcardList .list-group-item {
            min-width: 0;
            overflow: visible;
            position: relative;
            z-index: 1;
        }

        #flashcardList .list-group-item:hover {
            z-index: 10;
        }

        #flashcardList .list-group-item .flex-grow-1 {
            min-width: 0;
            overflow: hidden;
            margin-right: 0.75rem;
        }

        #flashcardList .list-group-item .flex-grow-1 strong {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }

        #flashcardList .list-group-item .dropdown {
            flex-shrink: 0;
            position: relative;
            z-index: 2;
        }

        #flashcardList .list-group-item:hover .dropdown {
            z-index: 11;
        }

        #flashcardList .list-group-item .dropdown-menu {
            z-index: 1050 !important;
        }

        .upload-container {
            background-color: #f8f9fa;
            padding: 30px;
        }

        h4[onclick] {
            transition: color 0.2s;
        }

        h4[onclick]:hover {
            color: #6f42c1 !important;
            text-decoration: underline;
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
    <?php
    $current_url = $_GET['url'] ?? 'lm/flashcard';
    $currentFileId = isset($file['fileID']) ? htmlspecialchars($file['fileID']) : '';
    ?>
    <div class="d-flex flex-grow-1">
        <?php include 'app/views/sidebar.php'; ?>
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
            <div class="container-fluid upload-container">
                <h3 style="color: #212529; font-size: 1.5rem; font-weight: 600; margin-bottom: 30px;">Flashcards</h3>
                <form method="POST" action="<?= DISPLAY_DOCUMENT ?>" style="display: inline;">
                    <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
                    <h4 style="color: #212529; font-size: 1.25rem; font-weight: 500; margin-bottom: 20px; cursor: pointer; display: inline-block;" onclick="this.closest('form').submit();"><?php echo htmlspecialchars($file['name'] ?? 'Document'); ?></h4>
                </form>
                <?php require_once VIEW_NAVBAR; ?>

                <!-- Creation Row -->
                <div class="row g-4">
                    <div class="col-12 col-xl-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">Generate with AI</h5>
                                <small class="text-muted">Let StudyAid create flashcards from your document.</small>
                            </div>
                            <div class="card-body">
                                <form id="generateFlashcardForm" action="<?= GENERATE_FLASHCARDS ?>" method="POST">
                                    <input type="hidden" name="file_id" value="<?php echo $currentFileId; ?>">
                                    <label for="flashcardAmountRange" class="form-label">Number of Flashcards</label>
                                    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center mb-2">
                                        <input type="range" class="form-range" min="1" max="25" value="15" name="flashcardAmount" id="flashcardAmountRange">
                                        <div>
                                            <span class="badge rounded-pill" style="background-color: #e7d5ff; color: #5a32a3; font-size: 1rem;">
                                                <span id="flashcardAmountValue">15</span> cards
                                            </span>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mb-3">Drag the slider to choose anywhere between 1 and 25 flashcards.</small>
                                    <label for="flashcardType" class="form-label d-block">Level of Difficulty</label>
                            <div class="btn-group mb-3" role="group">
                                <input type="radio" class="btn-check" name="flashcardType" autcomplete="off" value="easy" id="easy">
                                <label class="btn btn-outline-secondary" for="easy">Easy</label>
                                <input type="radio" class="btn-check" name="flashcardType" autcomplete="off" checked value="medium" id="medium">
                                <label class="btn btn-outline-secondary" for="medium">Medium (Default)</label>
                                <input type="radio" class="btn-check" name="flashcardType" autcomplete="off" value="hard" id="hard">
                                <label class="btn btn-outline-secondary" for="hard">Hard</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Instructions (optional)</label>
                                        <input type="text" name="instructions" class="form-control" placeholder="Describe your instruction">
                                    </div>
                                    <button type="submit" id="genFlashcards" class="btn btn-primary">Generate Flashcard</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-6">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h5 class="mb-0">Create Flashcards Manually</h5>
                                    <small class="text-muted d-block">Add your own terms and definitions, then save them as a set.</small>
                    </div>
                </div>
                        <div class="card-body">
                                <form id="manualFlashcardForm">
                                    <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Flashcard Title</label>
                                        <input type="text" id="manualFlashcardTitle" class="form-control" placeholder="Enter flashcard title">
                                    </div>
                                    <div id="manualPairsContainer" class="flashcard-pairs d-flex flex-column gap-3"></div>
                                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-3">
                                        <button type="button" class="btn btn-primary" id="addManualPairBtn">
                                            <i class="bi bi-plus-lg me-2"></i>Add Card
                                        </button>
                                        
                                        <button type="submit" class="btn btn-primary" id="saveManualFlashcards">Save Flashcard</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Flashcard Library -->
                <div class="card mt-4">
                    <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Flashcard Library</h5>
                            <small class="text-muted d-block" id="currentFlashcardSortLabel">Sorted by: A to Z</small>
                        </div>
                        <div class="dropdown">
                            <button class="btn-icon" type="button" id="flashcardSortDropdown" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" title="Sort flashcards">
                                <i class="bi bi-funnel-fill" style="font-size: 1.1rem;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="flashcardSortDropdown">
                                <li><a class="dropdown-item flashcard-sort-option" href="#" data-sort="asc">A to Z</a></li>
                                <li><a class="dropdown-item flashcard-sort-option" href="#" data-sort="desc">Z to A</a></li>
                                <li><a class="dropdown-item flashcard-sort-option" href="#" data-sort="latest">Latest to Oldest</a></li>
                                <li><a class="dropdown-item flashcard-sort-option" href="#" data-sort="oldest">Oldest to Latest</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" id="flashcardList">
                            <?php if ($flashcards): ?>
                                <?php foreach ($flashcards as $flashcard): ?>
                                    <?php
                                    $normalizedTitle = strtolower($flashcard['title'] ?? '');
                                    $updatedTimestamp = strtotime($flashcard['createdAt'] ?? '') ?: 0;
                                    ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center flashcard-item"
                                        data-title="<?= htmlspecialchars($normalizedTitle) ?>"
                                        data-updated="<?= $updatedTimestamp ?>">
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <strong title="<?php echo htmlspecialchars($flashcard['title']); ?>"><?php echo htmlspecialchars($flashcard['title']); ?></strong>
                                            <small class="text-muted d-block">Updated: <?php echo htmlspecialchars($flashcard['createdAt']); ?></small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="action-btn" type="button" id="dropdownFileActions<?php echo $flashcard['flashcardID']; ?>" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" title="Flashcard actions">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownFileActions<?php echo $flashcard['flashcardID']; ?>">
                                                <li><a class="dropdown-item view-btn" href="#" data-id="<?= htmlspecialchars($flashcard['flashcardID']) ?>">View</a></li>
                                                <li><a class="dropdown-item edit-btn" href="#" data-id="<?= htmlspecialchars($flashcard['flashcardID']) ?>">Edit</a></li>
                                                <li>
                                                    <form method="POST" action="<?= DELETE_FLASHCARD ?>" class="delete-flashcard-form" style="display: inline;">
                                                        <input type="hidden" name="flashcard_id" value="<?= htmlspecialchars($flashcard['flashcardID']) ?>">
                                                        <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                        <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Delete</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="list-group-item text-muted text-center">No flashcards yet. Generate or create your first set above.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Flashcards Viewer Modal -->
                <div class="modal fade preview-modal" id="flashcardsModal" tabindex="-1" aria-labelledby="flashcardsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="flashcardsModalLabel">Flashcards Viewer</h5>
                                <button type="button" class="btn-back" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div id="flashcardsSection">
                                    <div class="flashcard-container">
                                        <div class="flashcard" id="flashcard">
                                            <div class="flashcard-front">
                                                <div class="flashcard-counter" id="flashcardCounter">1 / 0</div>
                                                <div class="flashcard-content" id="flashcardFront">
                                                    Select a flashcard set to begin viewing.
                                                </div>
                                            </div>
                                            <div class="flashcard-back">
                                                <div class="flashcard-content" id="flashcardBack">
                                                    Back side
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Navigation Controls -->
                                    <div class="d-flex justify-content-between align-items-center mt-4">
                                        <button class="btn btn-outline-secondary" id="prevBtn">
                                            <i class="bi bi-chevron-left me-2"></i>Previous
                                        </button>
                                        <button class="btn btn-primary" id="flipBtn" style="background-color: #6f42c1; border: none;">
                                            <i class="bi bi-arrow-repeat me-2"></i>Flip Card
                                        </button>
                                        <button class="btn btn-outline-secondary" id="nextBtn">
                                            Next<i class="bi bi-chevron-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Flashcard Modal -->
                <div class="modal fade" id="editFlashcardModal" tabindex="-1" aria-labelledby="editFlashcardModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editFlashcardModalLabel">Edit Flashcard</h5>
                                <button type="button" class="btn-back" data-bs-dismiss="modal" aria-label="Close">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="editFlashcardForm">
                                    <input type="hidden" id="editFlashcardId" name="flashcard_id">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Flashcard Title</label>
                                        <input type="text" id="editFlashcardTitle" class="form-control">
                                    </div>
                                    <div id="editPairsContainer" class="flashcard-pairs d-flex flex-column gap-3"></div>
                                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-3">
                                        <button type="button" class="btn btn-primary" id="addEditPairBtn">
                                            <i class="bi bi-plus-lg me-2"></i>Add Card
                                        </button>
                                        <button type="submit" class="btn btn-primary">Update Flashcards</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
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

        document.addEventListener('DOMContentLoaded', () => {
        let terms = [];
        let definitions = [];
        let currentIndex = 0;

        const flashcard = document.getElementById('flashcard');
        const flashcardFront = document.getElementById('flashcardFront');
        const flashcardBack = document.getElementById('flashcardBack');
        const flashcardCounter = document.getElementById('flashcardCounter');
        const flipBtn = document.getElementById('flipBtn');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const flashcardsSection = document.getElementById('flashcardsSection');
            const flashcardsModalEl = document.getElementById('flashcardsModal');
            const flashcardsModal = new bootstrap.Modal(flashcardsModalEl);
        const generateForm = document.getElementById('generateFlashcardForm');
            const flashcardAmountRange = document.getElementById('flashcardAmountRange');
            const flashcardAmountValue = document.getElementById('flashcardAmountValue');
            const manualFlashcardForm = document.getElementById('manualFlashcardForm');
            const manualPairsContainer = document.getElementById('manualPairsContainer');
            const addManualPairBtn = document.getElementById('addManualPairBtn');
            const manualFlashcardTitle = document.getElementById('manualFlashcardTitle');
            const editFlashcardForm = document.getElementById('editFlashcardForm');
            const editPairsContainer = document.getElementById('editPairsContainer');
            const addEditPairBtn = document.getElementById('addEditPairBtn');
            const editFlashcardModalEl = document.getElementById('editFlashcardModal');
            const editFlashcardModal = editFlashcardModalEl ? new bootstrap.Modal(editFlashcardModalEl) : null;
            const editFlashcardIdInput = document.getElementById('editFlashcardId');
            const editFlashcardTitleInput = document.getElementById('editFlashcardTitle');
            const flashcardSortOptions = document.querySelectorAll('.flashcard-sort-option');
            const flashcardList = document.getElementById('flashcardList');
            const currentFlashcardSortLabel = document.getElementById('currentFlashcardSortLabel');
            let activeFlashcardId = null;
            let modalVisible = false;

            if (flashcardAmountRange && flashcardAmountValue) {
                const updateAmountDisplay = () => {
                    flashcardAmountValue.textContent = flashcardAmountRange.value;
                };
                updateAmountDisplay();
                flashcardAmountRange.addEventListener('input', updateAmountDisplay);
            }

            const MAX_FLASHCARD_PAIRS = 25;

            function autoResizeTextarea(textarea) {
                if (!textarea) return;
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            }

            function addPairRow(container, termValue = '', definitionValue = '', options = {}) {
                if (!container) return;
                if (container.children.length >= MAX_FLASHCARD_PAIRS && termValue === '' && definitionValue === '') {
                    return;
                }

                const row = document.createElement('div');
                row.className = 'flashcard-pair row g-3 align-items-center';
                row.innerHTML = `
                    <div class="col-12 col-lg-4">
                        <label class="form-label small text-muted mb-1">Term</label>
                        <input type="text" class="form-control term-input" placeholder="Term">
                    </div>
                    <div class="col-12 col-lg-6">
                        <label class="form-label small text-muted mb-1">Definition</label>
                        <textarea class="form-control definition-input" rows="1" placeholder="Definition"></textarea>
                    </div>
                    <div class="col-12 col-lg-2 d-flex align-items-center justify-content-lg-end">
                        <button type="button" class="btn btn-remove-pair" aria-label="Remove flashcard">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;
                container.appendChild(row);

                const termInput = row.querySelector('.term-input');
                const definitionInput = row.querySelector('.definition-input');
                const removeBtn = row.querySelector('.btn-remove-pair');

                termInput.value = termValue;
                definitionInput.value = definitionValue;
                autoResizeTextarea(definitionInput);

                definitionInput.addEventListener('input', () => autoResizeTextarea(definitionInput));

                removeBtn.addEventListener('click', () => {
                    if (container.children.length > 1) {
                        container.removeChild(row);
                        if (options.autoAppend) {
                            ensureEmptyRow(container);
                        }
                    } else {
                        termInput.value = '';
                        definitionInput.value = '';
                    }
                });

                [termInput, definitionInput].forEach((input) => {
                    input.addEventListener('input', () => {
                        if (options.autoAppend) {
                            ensureEmptyRow(container);
                        }
                    });
                });
            }

            function ensureEmptyRow(container) {
                if (!container) return;
                if (!container.children.length) {
                    addPairRow(container, '', '', {
                        autoAppend: true
                    });
                    return;
                }
                const rows = container.querySelectorAll('.flashcard-pair');
                const lastRow = rows[rows.length - 1];
                const term = lastRow.querySelector('.term-input').value.trim();
                const def = lastRow.querySelector('.definition-input').value.trim();
                if (term !== '' && def !== '' && rows.length < MAX_FLASHCARD_PAIRS) {
                    addPairRow(container, '', '', {
                        autoAppend: true
                    });
                }
            }

            function collectPairs(container) {
                if (!container) {
                    return {
                        terms: [],
                        definitions: []
                    };
                }
                const termsCollected = [];
                const definitionsCollected = [];
                const termInputs = container.querySelectorAll('.term-input');
                const definitionInputs = container.querySelectorAll('.definition-input');

                termInputs.forEach((input, index) => {
                    const termValue = input.value.trim();
                    const defValue = definitionInputs[index]?.value.trim() ?? '';

                    if (!termValue && !defValue) {
                        return;
                    }
                    if (!termValue || !defValue) {
                        throw new Error('Please complete both the term and definition for each card.');
                    }
                    termsCollected.push(termValue);
                    definitionsCollected.push(defValue);
                });

                return {
                    terms: termsCollected,
                    definitions: definitionsCollected
                };
            }

            function populatePairs(container, termsArr = [], definitionsArr = [], options = {}) {
                if (!container) return;
                container.innerHTML = '';
                const length = Math.max(termsArr.length, definitionsArr.length);
                if (length === 0) {
                    addPairRow(container, '', '', options);
                    if (options.autoAppend) {
                        ensureEmptyRow(container);
                    }
                    return;
                }
                for (let i = 0; i < length; i++) {
                    addPairRow(container, termsArr[i] || '', definitionsArr[i] || '', options);
                }
                if (options.autoAppend) {
                    ensureEmptyRow(container);
                }
            }

            function parseStoredField(value) {
                if (!value) return '';
                let parsed = value;
                try {
                    parsed = JSON.parse(value);
                } catch (error) {}
                return parsed;
            }

            function parseFlashcardEntries(flashcardData) {
                const termText = parseStoredField(flashcardData.term);
                const definitionText = parseStoredField(flashcardData.definition);
                const termsList = termText ?
                    termText.split('\n').map((item) => item.trim()).filter((item) => item !== '') :
                    [];
                const definitionsListRaw = definitionText ?
                    definitionText.split('\n').map((item) => item.trim()) :
                    [];

                while (definitionsListRaw.length < termsList.length) {
                    definitionsListRaw.push('');
                }

                return {
                    terms: termsList,
                    definitions: definitionsListRaw
                };
            }

            if (manualPairsContainer) {
                addPairRow(manualPairsContainer, '', '', {
                    autoAppend: true
                });
            }

            addManualPairBtn?.addEventListener('click', () => {
                addPairRow(manualPairsContainer, '', '', {
                    autoAppend: true
                });
            });

            addEditPairBtn?.addEventListener('click', () => {
                addPairRow(editPairsContainer, '', '', {
                    autoAppend: false
                });
            });

            manualFlashcardForm?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const title = manualFlashcardTitle?.value.trim() || '';
                if (!title) {
                    showSnackbar('Please enter a title for your flashcards.', 'error');
                    return;
                }

                let collectedPairs;
                try {
                    collectedPairs = collectPairs(manualPairsContainer);
                } catch (error) {
                    showSnackbar(error.message, 'error');
                    return;
                }

                if (!collectedPairs.terms.length) {
                    showSnackbar('Add at least one flashcard before saving.', 'error');
                    return;
                }

                const submitButton = document.getElementById('saveManualFlashcards');
                if (submitButton) submitButton.disabled = true;

                const manualFormData = new FormData();
                manualFormData.append('file_id', manualFlashcardForm.querySelector('input[name="file_id"]').value);
                manualFormData.append('title', title);
                collectedPairs.terms.forEach((term, index) => {
                    manualFormData.append('terms[]', term);
                    manualFormData.append('definitions[]', collectedPairs.definitions[index]);
                });

                try {
                    const response = await fetch('<?= CREATE_FLASHCARD_MANUAL ?>', {
                        method: 'POST',
                        body: manualFormData
                    });
                    const data = await response.json();
                    if (data.success) {
                        showSnackbar(data.message || 'Flashcards saved successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showSnackbar(data.message || 'Failed to save flashcards. Please try again.', 'error');
                    }
                } catch (error) {
                    showSnackbar('An error occurred while saving flashcards. Please try again.', 'error');
                    console.error('Error:', error);
                } finally {
                    if (submitButton) submitButton.disabled = false;
                }
            });

        //View flashcards
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.view-btn');
            if (!btn) return;

            const id = btn.dataset.id;
                if (activeFlashcardId === id && modalVisible) {
                    flashcardsModal.hide();
                    return;
                }

            const formData = new FormData();
            formData.append('flashcard_id', id);
            formData.append('file_id', '<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>');

            const res = await fetch('<?= VIEW_FLASHCARD_ROUTE ?>', {
                method: 'POST',
                body: formData
            });

            const json = await res.json();
            if (json.success && json.flashcard) {
                    activeFlashcardId = id;
                renderFlashcard(json.flashcard);
                    flashcardsModal.show();
                } else {
                    showSnackbar(json.message || 'Failed to load flashcards. Please try again.', 'error');
                }
            });

            document.addEventListener('click', async (e) => {
                const editBtn = e.target.closest('.edit-btn');
                if (!editBtn) return;
                e.preventDefault();
                if (!editFlashcardModal) return;

                const id = editBtn.dataset.id;
                const formData = new FormData();
                formData.append('flashcard_id', id);

                try {
                    const res = await fetch('<?= VIEW_FLASHCARD_ROUTE ?>', {
                        method: 'POST',
                        body: formData
                    });
                    const json = await res.json();
                    if (json.success && json.flashcard) {
                        const parsed = parseFlashcardEntries(json.flashcard);
                        editFlashcardIdInput.value = id;
                        editFlashcardTitleInput.value = json.flashcard.title || '';
                        populatePairs(editPairsContainer, parsed.terms, parsed.definitions, {
                            autoAppend: false
                        });
                        addPairRow(editPairsContainer, '', '', {
                            autoAppend: false
                        });
                        editFlashcardModal.show();
            } else {
                        showSnackbar(json.message || 'Failed to load flashcard. Please try again.', 'error');
                    }
                } catch (error) {
                    showSnackbar('An error occurred while loading the flashcard. Please try again.', 'error');
                    console.error('Error:', error);
                }
            });

            function renderFlashcard(flashcardData) {
                const data = Array.isArray(flashcardData) ? flashcardData[0] : flashcardData;

            let term = Array.isArray(data.term) ? data.term[0] : data.term;
            let definition = Array.isArray(data.definition) ? data.definition[0] : data.definition;

            try {
                term = JSON.parse(term);
                definition = JSON.parse(definition);
            } catch (e) {
                // ignore if not valid JSON
            }

            terms = term.split('\n').filter(t => t.trim() !== '');
            definitions = definition.split('\n').filter(d => d.trim() !== '');

            console.log('Parsed Terms:', terms);
            console.log('Parsed Definitions:', definitions);

                currentIndex = 0;
                flashcard.classList.remove('flipped');
                updateFlashcard();
        }

        function updateFlashcard() {
            if (terms.length > 0) {
                flashcardFront.textContent = terms[currentIndex] || 'No term';
                flashcardBack.textContent = definitions[currentIndex] || 'No definition';
                flashcardCounter.textContent = `${currentIndex + 1} / ${terms.length}`;
                prevBtn.disabled = currentIndex === 0;
                nextBtn.disabled = currentIndex === terms.length - 1;
                flipBtn.disabled = false;
            }
        }

        nextBtn.addEventListener('click', () => {
            if (currentIndex < terms.length - 1) {
                currentIndex++;
                flashcard.classList.remove('flipped');
                updateFlashcard();
            }
                if (currentIndex < terms.length - 1) {
            currentIndex++;
                    flashcard.classList.remove('flipped');
            updateFlashcard();
                }
        });

        prevBtn.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                flashcard.classList.remove('flipped');
                updateFlashcard();
            }
                if (currentIndex > 0) {
            currentIndex--;
                    flashcard.classList.remove('flipped');
            updateFlashcard();
                }
        });

        flipBtn.addEventListener('click', () => {
                if (!terms.length) return;
            flashcard.classList.toggle('flipped');
        });

        generateForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            const submitButton = generateForm.querySelector('#genFlashcards');
            const originalButtonText = submitButton.innerHTML;

            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Generating...';

            const formData = new FormData(generateForm);
                formData.append('flashcardAmount', flashcardAmountRange ? flashcardAmountRange.value : 15);
            formData.append('flashcardType', document.querySelector('input[name="flashcardType"]:checked').value);
            try {
                const response = await fetch(generateForm.action, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success && data.flashcards) {
                    terms = data.flashcards.map(f => f.term);
                    definitions = data.flashcards.map(f => f.definition);

                    currentIndex = 0;
                    flashcard.classList.remove('flipped');
                        flashcardsModal.show();
                    updateFlashcard();
                    
                    location.reload();
                } else {
                        showSnackbar(data.message || 'Failed to generate flashcards. Please try again.', 'error');
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonText;
                    }
                } catch (error) {
                    showSnackbar('An error occurred while generating flashcards. Please try again.', 'error');
                    console.error('Error:', error);
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
            });

            editFlashcardForm?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const title = editFlashcardTitleInput?.value.trim() || '';
                if (!title) {
                    showSnackbar('Please enter a title for your flashcards.', 'error');
                    return;
                }

                let collectedPairs;
                try {
                    collectedPairs = collectPairs(editPairsContainer);
                } catch (error) {
                    showSnackbar(error.message, 'error');
                    return;
                }

                if (!collectedPairs.terms.length) {
                    showSnackbar('Add at least one flashcard before saving.', 'error');
                    return;
                }

                const submitButton = editFlashcardForm.querySelector('button[type="submit"]');
                if (submitButton) submitButton.disabled = true;

                const editFormData = new FormData();
                editFormData.append('flashcard_id', editFlashcardIdInput.value);
                editFormData.append('title', title);
                collectedPairs.terms.forEach((term, index) => {
                    editFormData.append('terms[]', term);
                    editFormData.append('definitions[]', collectedPairs.definitions[index]);
                });

                try {
                    const response = await fetch('<?= UPDATE_FLASHCARD ?>', {
                        method: 'POST',
                        body: editFormData
                    });
                    const data = await response.json();
                    if (data.success) {
                        showSnackbar(data.message || 'Flashcard updated successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showSnackbar(data.message || 'Failed to update flashcard. Please try again.', 'error');
                    }
                } catch (error) {
                    showSnackbar('An error occurred while updating the flashcard. Please try again.', 'error');
                    console.error('Error:', error);
                } finally {
                    if (submitButton) submitButton.disabled = false;
                }
            });

            const handleFlashcardShortcut = (event) => {
                if (!modalVisible) return;
                if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;

                if (event.code === 'Space') {
                    event.preventDefault();
                    if (terms.length) {
                        flashcard.classList.toggle('flipped');
                        updateFlashcard();
                    }
                } else if (event.key === 'ArrowLeft') {
                    event.preventDefault();
                    if (currentIndex > 0) {
                        currentIndex--;
                        flashcard.classList.remove('flipped');
                        updateFlashcard();
                    }
                } else if (event.key === 'ArrowRight') {
                    event.preventDefault();
                    if (currentIndex < terms.length - 1) {
                        currentIndex++;
                        flashcard.classList.remove('flipped');
                        updateFlashcard();
                    }
                }
            };

            flashcardsModalEl.addEventListener('shown.bs.modal', () => {
                modalVisible = true;
                document.addEventListener('keydown', handleFlashcardShortcut);
            });

            flashcardsModalEl.addEventListener('hidden.bs.modal', () => {
                modalVisible = false;
                activeFlashcardId = null;
                flashcard.classList.remove('flipped');
                document.removeEventListener('keydown', handleFlashcardShortcut);
            });

            document.querySelectorAll('.delete-flashcard-form').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    const confirmed = confirm('Are you sure you want to delete this flashcard set? This action cannot be undone.');
                    if (!confirmed) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                });
            });

            function sortFlashcards(sortType = 'asc') {
                if (!flashcardList) return;
                const items = Array.from(flashcardList.querySelectorAll('.flashcard-item'));
                if (!items.length) return;

                items.sort((a, b) => {
                    const titleA = (a.dataset.title || '').toLowerCase();
                    const titleB = (b.dataset.title || '').toLowerCase();
                    const updatedA = parseInt(a.dataset.updated || '0', 10);
                    const updatedB = parseInt(b.dataset.updated || '0', 10);

                    switch (sortType) {
                        case 'desc':
                            return titleB.localeCompare(titleA);
                        case 'latest':
                            return updatedB - updatedA;
                        case 'oldest':
                            return updatedA - updatedB;
                        case 'asc':
                        default:
                            return titleA.localeCompare(titleB);
                    }
                });

                flashcardList.innerHTML = '';
                items.forEach(item => flashcardList.appendChild(item));
            }

            flashcardSortOptions.forEach(option => {
                option.addEventListener('click', (event) => {
                    event.preventDefault();
                    const sortType = option.dataset.sort;
                    if (!sortType) return;
                    sortFlashcards(sortType);
                    if (currentFlashcardSortLabel) {
                        currentFlashcardSortLabel.textContent = `Sorted by: ${option.textContent.trim()}`;
                    }
                });
            });

            // Initial sort
            sortFlashcards('asc');
        });

            editFlashcardForm?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const title = editFlashcardTitleInput?.value.trim() || '';
                if (!title) {
                    showSnackbar('Please enter a title for your flashcards.', 'error');
                    return;
                }

                let collectedPairs;
                try {
                    collectedPairs = collectPairs(editPairsContainer);
                } catch (error) {
                    showSnackbar(error.message, 'error');
                    return;
                }

                if (!collectedPairs.terms.length) {
                    showSnackbar('Add at least one flashcard before saving.', 'error');
                    return;
                }

                const submitButton = editFlashcardForm.querySelector('button[type="submit"]');
                if (submitButton) submitButton.disabled = true;

                const editFormData = new FormData();
                editFormData.append('flashcard_id', editFlashcardIdInput.value);
                editFormData.append('title', title);
                collectedPairs.terms.forEach((term, index) => {
                    editFormData.append('terms[]', term);
                    editFormData.append('definitions[]', collectedPairs.definitions[index]);
                });

                try {
                    const response = await fetch('<?= UPDATE_FLASHCARD ?>', {
                        method: 'POST',
                        body: editFormData
                    });
                    const data = await response.json();
                    if (data.success) {
                        showSnackbar(data.message || 'Flashcard updated successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showSnackbar(data.message || 'Failed to update flashcard. Please try again.', 'error');
                    }
                } catch (error) {
                    showSnackbar('An error occurred while updating the flashcard. Please try again.', 'error');
                    console.error('Error:', error);
                } finally {
                    if (submitButton) submitButton.disabled = false;
                }
            });

            const handleFlashcardShortcut = (event) => {
                if (!modalVisible) return;
                if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;

                if (event.code === 'Space') {
                    event.preventDefault();
                    if (terms.length) {
                        flashcard.classList.toggle('flipped');
                        updateFlashcard();
                    }
                } else if (event.key === 'ArrowLeft') {
                    event.preventDefault();
                    if (currentIndex > 0) {
                        currentIndex--;
                        flashcard.classList.remove('flipped');
                        updateFlashcard();
                    }
                } else if (event.key === 'ArrowRight') {
                    event.preventDefault();
                    if (currentIndex < terms.length - 1) {
                        currentIndex++;
                        flashcard.classList.remove('flipped');
                        updateFlashcard();
                    }
                }
            };

            flashcardsModalEl.addEventListener('shown.bs.modal', () => {
                modalVisible = true;
                document.addEventListener('keydown', handleFlashcardShortcut);
            });

            flashcardsModalEl.addEventListener('hidden.bs.modal', () => {
                modalVisible = false;
                activeFlashcardId = null;
                flashcard.classList.remove('flipped');
                document.removeEventListener('keydown', handleFlashcardShortcut);
            });

            document.querySelectorAll('.delete-flashcard-form').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    const confirmed = confirm('Are you sure you want to delete this flashcard set? This action cannot be undone.');
                    if (!confirmed) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                });
            });

            function sortFlashcards(sortType = 'asc') {
                if (!flashcardList) return;
                const items = Array.from(flashcardList.querySelectorAll('.flashcard-item'));
                if (!items.length) return;

                items.sort((a, b) => {
                    const titleA = (a.dataset.title || '').toLowerCase();
                    const titleB = (b.dataset.title || '').toLowerCase();
                    const updatedA = parseInt(a.dataset.updated || '0', 10);
                    const updatedB = parseInt(b.dataset.updated || '0', 10);

                    switch (sortType) {
                        case 'desc':
                            return titleB.localeCompare(titleA);
                        case 'latest':
                            return updatedB - updatedA;
                        case 'oldest':
                            return updatedA - updatedB;
                        case 'asc':
                        default:
                            return titleA.localeCompare(titleB);
                    }
                });

                flashcardList.innerHTML = '';
                items.forEach(item => flashcardList.appendChild(item));
>>>>>>> 1bc5990 (/..)
            }

            flashcardSortOptions.forEach(option => {
                option.addEventListener('click', (event) => {
                    event.preventDefault();
                    const sortType = option.dataset.sort;
                    if (!sortType) return;
                    sortFlashcards(sortType);
                    if (currentFlashcardSortLabel) {
                        currentFlashcardSortLabel.textContent = `Sorted by: ${option.textContent.trim()}`;
                    }
                });
            });

            // Initial sort
            sortFlashcards('asc');
        });
    </script>
</body>

</html>