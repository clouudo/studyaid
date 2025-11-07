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
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php
    $current_url = $_GET['url'] ?? 'lm/flashcard';
    ?>
    <div class="d-flex flex-grow-1">
        <?php include 'app\views\sidebar.php'; ?>
        <main class="flex-grow-1 p-3">
            <div class="container">
                <h3 class="mb-4" style="color: #A855F7;">Flashcards</h3>
                <h4 class="mb-4"><?php echo htmlspecialchars($file['name'] ?? 'Document'); ?></h4>
                <?php require_once 'app\views\learningView\navbar.php'; ?>

                <!-- Generate Flashcards Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form id="generateFlashcardForm" action="<?= BASE_PATH ?>lm/generateFlashcards?fileID=<?= htmlspecialchars($_GET['fileID'] ?? '') ?>" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Instructions (optional)</label>
                                <input type="text" name="instructions" class="form-control" 
                                       placeholder="e.g. Focus on key terms, 10 flashcards">
                            </div>
                            <button type="submit" class="btn btn-primary" style="background-color: #A855F7; border: none;">
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
                                <button class="btn btn-outline-secondary" id="prevBtn" disabled>
                                    <i class="bi bi-chevron-left me-2"></i>Previous
                                </button>
                                <button class="btn btn-primary" id="flipBtn" style="background-color: #A855F7; border: none;">
                                    <i class="bi bi-arrow-repeat me-2"></i>Flip Card
                                </button>
                                <button class="btn btn-outline-secondary" id="nextBtn" disabled>
                                    Next<i class="bi bi-chevron-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        let flashcards = [];
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

        // Flip card
        flipBtn.addEventListener('click', () => {
            flashcard.classList.toggle('flipped');
        });

        // Update flashcard display
        function updateFlashcard() {
            if (flashcards.length === 0) return;

            const card = flashcards[currentIndex];
            flashcardFront.textContent = card.term || card.front || 'Term';
            flashcardBack.textContent = card.definition || card.back || 'Definition';
            flashcardCounter.textContent = `${currentIndex + 1} / ${flashcards.length}`;

            // Reset flip state
            flashcard.classList.remove('flipped');

            // Update navigation buttons
            prevBtn.disabled = currentIndex === 0;
            nextBtn.disabled = currentIndex === flashcards.length - 1;
        }

        // Navigate flashcards
        prevBtn.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex--;
                updateFlashcard();
            }
        });

        nextBtn.addEventListener('click', () => {
            if (currentIndex < flashcards.length - 1) {
                currentIndex++;
                updateFlashcard();
            }
        });

        // Generate flashcards
        generateForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(generateForm);
            const container = document.querySelector('.flashcard-container');
            
            try {
                const response = await fetch(generateForm.action, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success && data.flashcards) {
                    flashcards = data.flashcards;
                    currentIndex = 0;
                    flashcardsSection.style.display = 'block';
                    updateFlashcard();
                } else {
                    alert('Error: ' + (data.message || 'Failed to generate flashcards'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    </script>
</body>
</html>

