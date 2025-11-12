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
        .flashcard-container {
            perspective: 1000px;
            width: 100%;
            max-width: 600px;
            height: 400px;
            margin: 0 auto;
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
            background-color: #A855F7;
            color: white;
        }

        .flashcard-back {
            background-color: white;
            color: #333;
            border: 2px solid #A855F7;
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

        .btn-check:checked + .btn-outline-secondary {
            background-color: #A855F7;
            border-color: #A855F7;
        }
        .btn-check:checked + .btn-outline-secondary:hover {
            background-color: #A855F7;
            border-color: #A855F7;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php
    $current_url = $_GET['url'] ?? 'lm/flashcard';
    ?>
    <div class="d-flex flex-grow-1">
        <?php include VIEW_SIDEBAR; ?>
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
            <div class="container">
                <h3 class="mb-4" style="color: #A855F7;">Flashcards</h3>
                <h4 class="mb-4"><?php echo htmlspecialchars($file['name'] ?? 'Document'); ?></h4>
                <?php require_once VIEW_NAVBAR; ?>

                <!-- Generate Flashcards Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form id="generateFlashcardForm" action="<?= GENERATE_FLASHCARDS ?>" method="POST">
                            <input type="hidden" name="file_id" value="<?php echo $fileId ?>">
                            <label for="flashcardAmount" class="form-label">Flashcard Amount</label>
                            <br>
                            <div class="btn-group mb-3" role="group">
                                <input type="radio" class="btn-check" name="flashcardAmount" autcomplete="off" value="fewer (5-10 flashcards)" id="fewerFlashcards">
                                <label class="btn btn-outline-secondary" for="fewerFlashcards">Fewer Flashcards</label>
                                <input type="radio" class="btn-check" name="flashcardAmount" autcomplete="off" checked value="standard (10-20 flashcards)" id="defaultFlashcards">
                                <label class="btn btn-outline-secondary" for="defaultFlashcards">Standard (Default)</label>
                                <input type="radio" class="btn-check" name="flashcardAmount" autcomplete="off" value="more (15-25 flashcards)" id="moreFlashcards">
                                <label class="btn btn-outline-secondary" for="moreFlashcards">More Flashcards</label>
                            </div>
                            <br>
                            <label for="flashcardType" class="form-label">Level of Difficulty</label>
                            <br>
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
                                <input type="text" name="instructions" class="form-control"
                                    placeholder="e.g. Briefly describe restrictions you want to apply.">
                            </div>
                            <button type="submit" id="genFlashcards" class="btn btn-primary" style="background-color: #A855F7; border: none;">
                                <i class="bi bi-lightning-charge me-2"></i>Generate Flashcards
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Flashcards Display -->
                <div id="flashcardsSection" style="display: none;">
                    <div class="card">
                        <div class="card-body">
                            <div class="flashcard-container">
                                <div class="flashcard" id="flashcard">
                                    <div class="flashcard-front">
                                        <div class="flashcard-counter" id="flashcardCounter">1 / 0</div>
                                        <div class="flashcard-content" id="flashcardFront">
                                            Click "Generate Flashcards" to start
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
                                <button class="btn btn-primary" id="flipBtn" style="background-color: #A855F7; border: none;">
                                    <i class="bi bi-arrow-repeat me-2"></i>Flip Card
                                </button>
                                <button class="btn btn-outline-secondary" id="nextBtn">
                                    Next<i class="bi bi-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Generated Flashcards</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" id="flashcardList">
                            <?php if ($flashcardSets): ?>
                                <?php foreach ($flashcardSets as $title => $set): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <strong title="<?php echo htmlspecialchars($title); ?>"><?php echo htmlspecialchars($title); ?></strong>
                                            <small class="text-muted d-block">Created: <?php echo htmlspecialchars($set[0]['createdAt']); ?></small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item view-set-btn" href="#" data-title="<?= htmlspecialchars($title) ?>">View Set</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="<?= BASE_PATH ?>lm/deleteFlashcardSet" style="display: inline;">
                                                        <input type="hidden" name="title" value="<?= htmlspecialchars($title) ?>">
                                                        <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                        <button type="submit" class="dropdown-item">Delete Set</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="list-group-item text-muted text-center">No generated flashcards</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
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
        const generateForm = document.getElementById('generateFlashcardForm');

        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.view-set-btn');
            if (!btn) return;

            const title = btn.dataset.title;
            
            const allFlashcards = <?php echo json_encode($flashcards); ?>;
            const set = allFlashcards.filter(f => f.title === title);

            if (set.length > 0) {
                flashcardsSection.style.display = 'block';
                terms = set.map(f => f.term);
                definitions = set.map(f => f.definition);
                currentIndex = 0;
                updateFlashcard();
            }
        });

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
        });

        prevBtn.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                flashcard.classList.remove('flipped');
                updateFlashcard();
            }
        });

        flipBtn.addEventListener('click', () => {
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
                    flashcardsSection.style.display = 'block';
                    updateFlashcard();
                    
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to generate flashcards'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });
    </script>
</body>

</html>