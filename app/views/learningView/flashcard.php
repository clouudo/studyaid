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

        /* Ensure dropdowns can render outside of list/card without being clipped */
        .card { overflow: visible; }
        .card-body { overflow: visible; }
        #flashcardList { overflow: visible; }

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
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php
    $current_url = $_GET['url'] ?? 'lm/flashcard';
    ?>
    <div class="d-flex flex-grow-1">
        <?php include VIEW_SIDEBAR; ?>
        <main class="flex-grow-1 p-3">
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
                    <div class="card-header de-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Generated Flashcards</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" id="flashcardList">
                            <?php if ($flashcards): ?>
                                <?php foreach ($flashcards as $flashcard): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <strong title="<?php echo htmlspecialchars($flashcard['title']); ?>"><?php echo htmlspecialchars($flashcard['title']); ?></strong>
                                            <small class="text-muted d-block">Updated: <?php echo htmlspecialchars($flashcard['createdAt']); ?></small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownFileActions<?php echo $flashcard['flashcardID']; ?>" data-bs-toggle="dropdown" aria-expanded="false" data-bs-boundary="viewport">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownFileActions<?php echo $flashcard['flashcardID']; ?>">
                                                <li><a class="dropdown-item view-btn" href="#" data-id="<?= htmlspecialchars($flashcard['flashcardID']) ?>">View</a></li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <form method="POST" action="#" style="display: inline;">
                                                        <input type="hidden" name="flashcard_id" value="<?= htmlspecialchars($flashcard['flashcardID']) ?>">
                                                        <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                        <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Delete</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                        </div>
                <?php else: ?>
                    <div class="list-group-item text-muted text-center">No generated flashcards</div>
                <?php endif; ?>
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

        //View flashcards
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.view-btn');
            if (!btn) return;

            const id = btn.dataset.id;
            const container = document.getElementById('flashcardsSection');

            const formData = new FormData();
            formData.append('flashcard_id', id);
            formData.append('file_id', '<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>');

            const res = await fetch('<?= VIEW_FLASHCARD_ROUTE ?>', {
                method: 'POST',
                body: formData
            });

            const json = await res.json();
            if (json.success && json.flashcard) {
                flashcardsSection.style.display = 'block';
                renderFlashcard(json.flashcard);
            } else {
                container.innerHTML = `<div class="alert alert-danger">Error: ${json.message || 'Failed to load flashcards'}</div>`;
            }
        });

        function renderFlashcard(flashcard) {
            const data = Array.isArray(flashcard) ? flashcard[0] : flashcard;

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


            flashcardFront.textContent = terms[currentIndex] || 'No term';
            flashcardBack.textContent = definitions[currentIndex] || 'No definition';
            flashcardCounter.textContent = `${currentIndex + 1} / ${terms.length}`;

            prevBtn.disabled = currentIndex === 0;
            nextBtn.disabled = currentIndex === terms.length - 1;
            flipBtn.disabled = false;
        }

        function updateFlashcard() {
            flashcardFront.textContent = terms[currentIndex] || 'No term';
            flashcardBack.textContent = definitions[currentIndex] || 'No definition';
            flashcardCounter.textContent = `${currentIndex + 1} / ${terms.length}`;
            prevBtn.disabled = currentIndex === 0;
            nextBtn.disabled = currentIndex === terms.length - 1;
            flipBtn.disabled = false;
        }

        nextBtn.addEventListener('click', () => {
            currentIndex++;
            updateFlashcard();
        });

        prevBtn.addEventListener('click', () => {
            currentIndex--;
            updateFlashcard();
        });

        flipBtn.addEventListener('click', () => {
            flashcard.classList.toggle('flipped');
            updateFlashcard();
        });


        // Generate flashcards
        generateForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            const submitButton = generateForm.querySelector('#genFlashcards');
            const originalButtonText = submitButton.innerHTML;

            // Disable button and show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Generating...';

            const formData = new FormData(generateForm);
            formData.append('flashcardAmount', document.querySelector('input[name="flashcardAmount"]:checked').value);
            formData.append('flashcardType', document.querySelector('input[name="flashcardType"]:checked').value);
            try {
                const response = await fetch(generateForm.action, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success && data.term && data.definition) {
                    // Parse JSON strings from the response
                    let termString = data.term;
                    let definitionString = data.definition;

                    try {
                        // The controller returns JSON-encoded strings, so parse them first
                        termString = JSON.parse(data.term);
                        definitionString = JSON.parse(data.definition);
                    } catch (e) {
                        // If parsing fails, use the strings directly
                        termString = data.term;
                        definitionString = data.definition;
                    }

                    // Split by newlines and filter out empty strings
                    terms = termString.split('\n').filter(t => t.trim() !== '');
                    definitions = definitionString.split('\n').filter(d => d.trim() !== '');

                    // Reset to first card
                    currentIndex = 0;
                    flashcard.classList.remove('flipped');
                    flashcardsSection.style.display = 'block';
                    updateFlashcard();
                    
                    // Restore button
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                } else {
                    alert('Error: ' + (data.message || 'Failed to generate flashcards'));
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
            } catch (error) {
                alert('Error: ' + error.message);
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });
    </script>
</body>

</html>