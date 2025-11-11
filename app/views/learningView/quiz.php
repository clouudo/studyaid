<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        /* Ensure dropdowns are not clipped and float above cards */
        .list-group-item { overflow: visible; }
        .dropdown-menu { z-index: 1060; }
        .quiz-question {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            background-color: white;
        }

        .quiz-option {
            padding: 0.75rem;
            margin: 0.5rem 0;
            border: 2px solid #dee2e6;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .quiz-option:hover {
            border-color: #A855F7;
            background-color: #f8f9fa;
        }

        .quiz-option.selected {
            border-color: #A855F7;
            background-color: #A855F7;
            color: white;
        }

        .quiz-option.correct {
            border-color: #28a745;
            background-color: #d4edda;
        }

        .quiz-option.incorrect {
            border-color: #dc3545;
            background-color: #f8d7da;
        }

        .short-answer-input {
            width: 100%;
            min-height: 100px;
            padding: 0.75rem;
            border: 2px solid #dee2e6;
            border-radius: 0.375rem;
            font-size: 1rem;
            resize: vertical;
        }

        .short-answer-input:focus {
            border-color: #A855F7;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(168, 85, 247, 0.25);
        }

        .answer-comparison {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 0.375rem;
        }

        .answer-comparison.user-answer {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
        }

        .answer-comparison.correct-answer {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
        }

        .score-display {
            font-size: 2rem;
            font-weight: bold;
            color: #A855F7;
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
    $current_url = $_GET['url'] ?? 'lm/quiz';
    ?>
    <div class="d-flex flex-grow-1">
        <?php include VIEW_SIDEBAR; ?>
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
            <div class="container">
                <h3 class="mb-4" style="color: #A855F7;">Quiz</h3>
                <h4 class="mb-4"><?php echo htmlspecialchars($file['name'] ?? 'Document'); ?></h4>
                <?php require_once VIEW_NAVBAR; ?>

                <!-- Generate Quiz Form -->
                <div class="card mb-4" id="generateQuizCard">
                <div class="card-body">
                        <form id="generateQuizForm" action="<?= GENERATE_QUIZ ?>" method="POST">
                            <input type="hidden" name="file_id" value="<?php echo $fileId ?>">
                            <label for="questionAmount" class="form-label">Question Amount</label>
                            <br>
                            <div class="btn-group mb-3" role="group">
                                <input type="radio" class="btn-check" name="questionAmount" autcomplete="off" value="fewer (5-10 questions)" id="fewerQuestions">
                                <label class="btn btn-outline-secondary" for="fewerQuestions">Fewer Questions</label>
                                <input type="radio" class="btn-check" name="questionAmount" autcomplete="off" checked value="standard (10-20 questions)" id="defaultQuestions">
                                <label class="btn btn-outline-secondary" for="defaultQuestions">Standard (Default)</label>
                                <input type="radio" class="btn-check" name="questionAmount" autcomplete="off" value="more (15-25 questions)" id="moreQuestions">
                                <label class="btn btn-outline-secondary" for="moreQuestions">More Questions</label>
                            </div>
                            <br>
                            <label for="questionDifficulty" class="form-label">Level of Difficulty</label>
                            <br>
                            <div class="btn-group mb-3" role="group">
                                <input type="radio" class="btn-check" name="questionDifficulty" autcomplete="off" value="easy" id="easy">
                                <label class="btn btn-outline-secondary" for="easy">Easy</label>
                                <input type="radio" class="btn-check" name="questionDifficulty" autcomplete="off" checked value="medium" id="medium">
                                <label class="btn btn-outline-secondary" for="medium">Medium (Default)</label>
                                <input type="radio" class="btn-check" name="questionDifficulty" autcomplete="off" value="hard" id="hard">
                                <label class="btn btn-outline-secondary" for="hard">Hard</label>
                            </div>
                            <br>
                            <label for="questionType" class="form-label">Type of Question</label>
                            <br>
                            <div class="btn-group mb-3" role="group">
                                <input type="radio" class="btn-check" name="questionType" autcomplete="off" checked value="mcq" id="multipleChoice">
                                <label class="btn btn-outline-secondary" for="multipleChoice">Multiple Choice (Default)</label>
                                <input type="radio" class="btn-check" name="questionType" autcomplete="off" value="shortQuestion" id="shortQuestion">
                                <label class="btn btn-outline-secondary" for="shortQuestion">Short Question</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Instructions (optional)</label>
                                <input type="text" name="instructions" class="form-control"
                                    placeholder="e.g. Briefly describe restrictions you want to apply.">
                            </div>
                            <button type="submit" id="genQuiz" class="btn btn-primary" style="background-color: #A855F7; border: none;">
                                <i class="bi bi-lightning-charge me-2"></i>Generate Quiz
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card mb-4" id="quizListCard">
                    <div class="card-header">
                        <h5 class="card-title">Generated Quizzes</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" id="quizList">
                            <?php if ($quizList): ?>
                            <?php foreach ($quizList as $quiz): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1" style="min-width: 0;">
                                        <strong title="<?php echo htmlspecialchars($quiz['title']); ?>"><?php echo htmlspecialchars($quiz['title']); ?></strong>
                                        <small class="text-muted d-block">Updated: <?php echo htmlspecialchars($quiz['createdAt']); ?></small>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownQuizActions<?php echo $quiz['quizID']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownQuizActions<?php echo $quiz['quizID']; ?>">
                                            <li><a class="dropdown-item view-btn" href="#" data-id="<?= htmlspecialchars($quiz['quizID']) ?>">View</a></li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <form method="POST" action="#" style="display: inline;">
                                                    <input type="hidden" name="quiz_id" value="<?= htmlspecialchars($quiz['quizID']) ?>">
                                                    <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                    <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Delete</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                                <div class="list-group-item text-muted text-center">No generated quizzes</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quiz Display -->
                <div id="quizSection" style="display: none;">
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: #A855F7; color: white;">
                            <h5 class="mb-0">Quiz Questions</h5>
                            <span id="questionCounter">Question 1 of 0</span>
                        </div>
                        <div class="card-body" id="quizQuestions">
                            <!-- Quiz questions will be rendered here -->
                        </div>
                    </div>

                    <!-- Quiz Actions -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-outline-secondary" id="resetQuizBtn">
                                    <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Quiz
                                </button>
                                <div>
                                    <button class="btn btn-outline-info me-2" id="checkAnswersBtn" style="display: none;">
                                        <i class="bi bi-lightbulb me-2"></i>Check Suggested Answers
                                    </button>
                                    <button class="btn btn-primary" id="submitQuizBtn" style="background-color: #A855F7; border: none;">
                                        <i class="bi bi-check-circle me-2"></i>Submit Quiz
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results Display -->
                    <div class="card mt-4" id="resultsCard" style="display: none;">
                        <div class="card-header" style="background-color: #A855F7; color: white;">
                            <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Quiz Results</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="score-display mb-3">
                                <span id="quizScore">0</span> / <span id="totalQuestions">0</span>
                            </div>
                            <div class="mb-3">
                                <div class="progress" style="height: 30px;">
                                    <div class="progress-bar" id="scoreProgressBar" role="progressbar" style="width: 0%; background-color: #A855F7;"></div>
                                </div>
                            </div>
                            <p id="scorePercentage" class="mb-3"></p>
                            <button class="btn btn-primary" id="newQuizBtn" style="background-color: #A855F7; border: none;">
                                <i class="bi bi-plus-circle me-2"></i>Generate New Quiz
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
        let quizData = [];
        let userAnswers = {};
        let currentQuizId = null;
        let isShortQuestion = false;

        const generateQuizCard = document.getElementById('generateQuizCard');
        const quizSection = document.getElementById('quizSection');
        const quizQuestions = document.getElementById('quizQuestions');
        const questionCounter = document.getElementById('questionCounter');
        const submitQuizBtn = document.getElementById('submitQuizBtn');
        const resetQuizBtn = document.getElementById('resetQuizBtn');
        const checkAnswersBtn = document.getElementById('checkAnswersBtn');
        const resultsCard = document.getElementById('resultsCard');
        const generateQuizForm = document.getElementById('generateQuizForm');
        const newQuizBtn = document.getElementById('newQuizBtn');
        const quizListCard = document.getElementById('quizListCard');
        // Generate quiz
        generateQuizForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            const submitButton = generateQuizForm.querySelector('#genQuiz');
            const originalButtonText = submitButton.innerHTML;

            // Disable button and show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Generating...';

            const formData = new FormData(generateQuizForm);
            formData.append('questionAmount', document.querySelector('input[name="questionAmount"]:checked').value);
            formData.append('questionDifficulty', document.querySelector('input[name="questionDifficulty"]:checked').value);
            formData.append('questionType', document.querySelector('input[name="questionType"]:checked').value);
            formData.append('instructions', document.querySelector('input[name="instructions"]').value);

            try {
                const response = await fetch(generateQuizForm.action, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Handle both MCQ and short question formats from controller
                    if (data.mcq && Array.isArray(data.mcq)) {
                        quizData = data.mcq;
                        isShortQuestion = false;
                    } else if (data.shortQuestion && Array.isArray(data.shortQuestion)) {
                        quizData = data.shortQuestion;
                        isShortQuestion = true;
                    } else if (data.quiz && Array.isArray(data.quiz)) {
                        quizData = data.quiz;
                        // Detect type by checking if first question has options
                        isShortQuestion = !quizData[0] || !quizData[0].options || quizData[0].options.length === 0;
                    } else {
                        alert('Error: Invalid quiz data format');
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonText;
                        return;
                    }
                    
                    currentQuizId = data.quizId || null; // Store quiz ID if provided
                    userAnswers = {};
                    renderQuiz();
                    generateQuizCard.style.display = 'none';
                    quizSection.style.display = 'block';
                    resultsCard.style.display = 'none';
                    
                    // Show/hide check answers button based on quiz type
                    if (isShortQuestion) {
                        checkAnswersBtn.style.display = 'inline-block';
                    } else {
                        checkAnswersBtn.style.display = 'none';
                    }
                    
                    // Restore button (though it's hidden now)
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                } else {
                    alert('Error: ' + (data.message || 'Failed to generate quiz'));
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
            } catch (error) {
                alert('Error: ' + error.message);
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });

        // Render quiz questions
        function renderQuiz() {
            quizQuestions.innerHTML = '';

            if (!quizData || quizData.length === 0) {
                quizQuestions.innerHTML = '<p class="text-muted">No questions available.</p>';
                questionCounter.textContent = 'Question 0 of 0';
                return;
            }

            // Detect quiz type if not already set
            if (isShortQuestion === undefined || isShortQuestion === null) {
                isShortQuestion = !quizData[0] || !quizData[0].options || quizData[0].options.length === 0;
            }

            questionCounter.textContent = `Question 1 of ${quizData.length}`;

            quizData.forEach((question, index) => {
                const questionDiv = document.createElement('div');
                questionDiv.className = 'quiz-question';
                questionDiv.id = `question-${index}`;

                if (isShortQuestion) {
                    // Render short answer question with text area
                    questionDiv.innerHTML = `
                        <h5>Question ${index + 1}</h5>
                        <p class="mb-3">${question.question || ''}</p>
                        <div class="mb-3">
                            <label for="answer-${index}" class="form-label">Your Answer:</label>
                            <textarea 
                                class="short-answer-input" 
                                id="answer-${index}" 
                                data-question="${index}"
                                placeholder="Write your answer here..."
                                rows="4">${userAnswers[index] || ''}</textarea>
                        </div>
                    `;
                } else {
                    // Render multiple choice question
                    questionDiv.innerHTML = `
                        <h5>Question ${index + 1}</h5>
                        <p class="mb-3">${question.question || ''}</p>
                        <div class="quiz-options">
                            ${question.options.map((option, optIndex) => `
                                <div class="quiz-option ${userAnswers[index] === option ? 'selected' : ''}" 
                                     data-question="${index}" 
                                     data-option="${option}">
                                    ${option}
                                </div>
                            `).join('')}
                        </div>
                    `;
                }

                quizQuestions.appendChild(questionDiv);
            });

            if (isShortQuestion) {
                // Add input handlers for short answer questions
                document.querySelectorAll('.short-answer-input').forEach(textarea => {
                    textarea.addEventListener('input', function() {
                        const questionId = parseInt(this.dataset.question);
                        userAnswers[questionId] = this.value.trim();
                    });
                });
            } else {
                // Add click handlers for multiple choice options
                document.querySelectorAll('.quiz-option').forEach(option => {
                    option.addEventListener('click', function() {
                        const questionId = parseInt(this.dataset.question);
                        const selectedOption = this.dataset.option;

                        // Remove selected class from other options in same question
                        document.querySelectorAll(`[data-question="${questionId}"]`).forEach(opt => {
                            opt.classList.remove('selected');
                        });

                        // Add selected class to clicked option
                        this.classList.add('selected');
                        userAnswers[questionId] = selectedOption;
                    });
                });
            }
        }

        // Check Suggested Answers button handler (for short questions)
        checkAnswersBtn.addEventListener('click', () => {
            if (!isShortQuestion) return;
            
            // Show suggested answers (correct answers) for each question
            quizData.forEach((question, index) => {
                const questionDiv = document.getElementById(`question-${index}`);
                if (!questionDiv) return;
                
                // Check if answer comparison already exists
                const existingComparison = questionDiv.querySelector('.answer-comparison');
                if (existingComparison) {
                    // Toggle visibility
                    existingComparison.style.display = existingComparison.style.display === 'none' ? 'block' : 'none';
                    return;
                }
                
                const correctAnswer = question.answer;
                const userAnswer = userAnswers[index] || '';
                
                // Create answer comparison div
                const comparisonDiv = document.createElement('div');
                comparisonDiv.className = 'answer-comparison';
                comparisonDiv.innerHTML = `
                    <div class="answer-comparison correct-answer mt-3">
                        <strong><i class="bi bi-check-circle me-2"></i>Suggested Answer:</strong>
                        <p class="mb-0">${correctAnswer}</p>
                    </div>
                `;
                
                // Insert after the textarea
                const textarea = questionDiv.querySelector('.short-answer-input');
                if (textarea) {
                    textarea.parentNode.insertBefore(comparisonDiv, textarea.nextSibling);
                } else {
                    questionDiv.appendChild(comparisonDiv);
                }
            });
        });

        // Submit quiz
        submitQuizBtn.addEventListener('click', async () => {
            if (Object.keys(userAnswers).length < quizData.length) {
                if (!confirm('You have not answered all questions. Submit anyway?')) {
                    return;
                }
            }

            if (isShortQuestion) {
                // For short questions: Pass answers to server, no score calculation, no results display
                if (currentQuizId) {
                    await submitAnswers(currentQuizId, userAnswers);
                }
                // Show answer comparison inline without results card
                showAnswerComparison();
            } else {
                // For MCQ: Calculate score and save it
                let score = 0;
                const total = quizData.length;

                quizData.forEach((question, index) => {
                    const correctAnswer = question.answer;
                    const userAnswer = userAnswers[index];

                    if (userAnswer && userAnswer === correctAnswer) {
                        score++;
                    }
                });

                const percentage = total > 0 ? Math.round((score / total) * 100) : 0;
                if (currentQuizId) {
                    await saveScore(currentQuizId, percentage);
                }
                displayResults(score, total, percentage, false);
            }
        });

        function showAnswerComparison() {
            // Show answer comparison for short questions without results card
            quizData.forEach((question, index) => {
                const correctAnswer = question.answer;
                const userAnswer = userAnswers[index] || 'No answer provided';
                const questionDiv = document.getElementById(`question-${index}`);
                
                if (!questionDiv) return;
                
                // Hide the textarea
                const textarea = questionDiv.querySelector('.short-answer-input');
                if (textarea) {
                    textarea.style.display = 'none';
                }
                
                // Remove existing comparison if any
                const existingComparison = questionDiv.querySelector('.answer-comparison');
                if (existingComparison) {
                    existingComparison.remove();
                }
                
                // Add answer comparison
                const comparisonDiv = document.createElement('div');
                comparisonDiv.innerHTML = `
                    <div class="answer-comparison user-answer">
                        <strong>Your Answer:</strong>
                        <p class="mb-0">${userAnswer}</p>
                    </div>
                    <div class="answer-comparison correct-answer">
                        <strong>Correct Answer:</strong>
                        <p class="mb-0">${correctAnswer}</p>
                    </div>
                `;
                questionDiv.appendChild(comparisonDiv);
            });
            
            // Disable submit button
            submitQuizBtn.disabled = true;
        }

        async function submitAnswers(quizId, answers) {
            try {
                const formData = new FormData();
                formData.append('quiz_id', quizId);
                formData.append('user_answers', JSON.stringify(answers));

                const response = await fetch('<?= SUBMIT_QUIZ ?>', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    console.log('Answers saved successfully');
                } else {
                    console.error('Failed to save answers:', result.message);
                }
            } catch (error) {
                console.error('Error saving answers:', error);
            }
        }

        async function saveScore(quizId, percentageScore) {
            try {
                const formData = new FormData();
                formData.append('quiz_id', quizId);
                formData.append('percentage_score', percentageScore.toString());

                const response = await fetch('<?= SAVE_SCORE ?>', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    console.log('Score saved successfully:', percentageScore + '%');
                } else {
                    console.error('Failed to save score:', result.message);
                }
            } catch (error) {
                console.error('Error saving score:', error);
            }
        }

        // Display results
        function displayResults(score, total, percentage, isShortQuestion = false) {
            document.getElementById('quizScore').textContent = score;
            document.getElementById('totalQuestions').textContent = total;
            
            if (isShortQuestion) {
                document.getElementById('scorePercentage').textContent = 'Review your answers `below. Compare your responses with the correct answers.';
            } else {
                document.getElementById('scorePercentage').textContent = `You scored ${percentage}%`;
            }

            const progressBar = document.getElementById('scoreProgressBar');
            progressBar.style.width = percentage + '%';
            progressBar.textContent = percentage + '%';

            if (isShortQuestion) {
                // Display answer comparison for short questions
                quizData.forEach((question, index) => {
                    const correctAnswer = question.answer;
                    const userAnswer = userAnswers[index] || 'No answer provided';
                    const questionDiv = document.getElementById(`question-${index}`);
                    
                    // Hide the textarea
                    const textarea = questionDiv.querySelector('.short-answer-input');
                    if (textarea) {
                        textarea.style.display = 'none';
                    }
                    
                    // Add answer comparison
                    const comparisonDiv = document.createElement('div');
                    comparisonDiv.innerHTML = `
                        <div class="answer-comparison user-answer">
                            <strong>Your Answer:</strong>
                            <p class="mb-0">${userAnswer}</p>
                        </div>
                        <div class="answer-comparison correct-answer">
                            <strong>Correct Answer:</strong>
                            <p class="mb-0">${correctAnswer}</p>
                        </div>
                    `;
                    questionDiv.appendChild(comparisonDiv);
                });
            } else {
                // Highlight correct/incorrect answers for multiple choice
                quizData.forEach((question, index) => {
                    const correctAnswer = question.answer;
                    const userAnswer = userAnswers[index];

                    document.querySelectorAll(`[data-question="${index}"]`).forEach(option => {
                        if (option.dataset.option === correctAnswer) {
                            option.classList.add('correct');
                        } else if (option.dataset.option === userAnswer && userAnswer !== correctAnswer) {
                            option.classList.add('incorrect');
                        }
                    });
                });
            }

            resultsCard.style.display = 'block';
            submitQuizBtn.disabled = true;
        }

        // Reset quiz
        resetQuizBtn.addEventListener('click', () => {
            userAnswers = {};
            // Remove any answer comparison divs from short questions
            document.querySelectorAll('.answer-comparison').forEach(div => {
                div.remove();
            });
            // Show textareas again if they were hidden
            document.querySelectorAll('.short-answer-input').forEach(textarea => {
                textarea.style.display = 'block';
            });
            renderQuiz();
            resultsCard.style.display = 'none';
            submitQuizBtn.disabled = false;
            // Show check answers button if it's a short question quiz
            if (isShortQuestion) {
                checkAnswersBtn.style.display = 'inline-block';
            }
        });

        // New quiz
        newQuizBtn.addEventListener('click', () => {
            quizData = [];
            userAnswers = {};
            currentQuizId = null;
            isShortQuestion = false;
            checkAnswersBtn.style.display = 'none';
            generateQuizCard.style.display = 'block';
            quizSection.style.display = 'none';
            resultsCard.style.display = 'none';
        });

        //View quiz
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.view-btn');
            if (!btn) return;

            const id = btn.dataset.id;
            const container = document.getElementById('quizSection');

            const formData = new FormData();
            formData.append('quiz_id', id);
            formData.append('file_id', '<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>');

            const res = await fetch('<?= VIEW_QUIZ_ROUTE ?>', {
                method: 'POST',
                body: formData
            });

            const json = await res.json();
            console.log('Quiz data:', json);
            if (json.success && json.quiz) {
                quizData = json.quiz;
                // Detect quiz type by checking if first question has options
                isShortQuestion = !quizData[0] || !quizData[0].options || quizData[0].options.length === 0;
                currentQuizId = id; // Store the quiz ID for saving score
                userAnswers = {};
                renderQuiz();
                quizSection.style.display = 'block';
                quizListCard.style.display = 'none';
                generateQuizCard.style.display = 'none';
                resultsCard.style.display = 'none';
                
                // Show/hide check answers button based on quiz type
                if (isShortQuestion) {
                    checkAnswersBtn.style.display = 'inline-block';
                } else {
                    checkAnswersBtn.style.display = 'none';
                }
            } else {
                container.innerHTML = `<div class="alert alert-danger">Error: ${json.message || 'Failed to load quiz'}</div>`;
            }
        });
    </script>
</body>

</html>