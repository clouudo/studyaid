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
        .score-display {
            font-size: 2rem;
            font-weight: bold;
            color: #A855F7;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php
    $current_url = $_GET['url'] ?? 'lm/quiz';
    ?>
    <div class="d-flex flex-grow-1">
        <?php include VIEW_SIDEBAR; ?>
        <main class="flex-grow-1 p-3">
            <div class="container">
                <h3 class="mb-4" style="color: #A855F7;">Quiz</h3>
                <h4 class="mb-4"><?php echo htmlspecialchars($file['name'] ?? 'Document'); ?></h4>
                <?php require_once VIEW_NAVBAR; ?>

                <!-- Generate Quiz Form -->
                <div class="card mb-4" id="generateQuizCard">
                    <div class="card-body">
                        <form id="generateQuizForm" action="<?= GENERATE_QUIZ ?>" method="POST">
                            <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
                            <div class="mb-3">
                                <label class="form-label">Instructions (optional)</label>
                                <input type="text" name="instructions" class="form-control" 
                                       placeholder="e.g. 10 questions, multiple choice, focus on key concepts">
                            </div>
                            <button type="submit" class="btn btn-primary" style="background-color: #A855F7; border: none;">
                                <i class="bi bi-question-circle me-2"></i>Generate Quiz
                            </button>
                        </form>
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
                                <button class="btn btn-primary" id="submitQuizBtn" style="background-color: #A855F7; border: none;">
                                    <i class="bi bi-check-circle me-2"></i>Submit Quiz
                                </button>
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
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        let quizData = [];
        let userAnswers = {};

        const generateQuizCard = document.getElementById('generateQuizCard');
        const quizSection = document.getElementById('quizSection');
        const quizQuestions = document.getElementById('quizQuestions');
        const questionCounter = document.getElementById('questionCounter');
        const submitQuizBtn = document.getElementById('submitQuizBtn');
        const resetQuizBtn = document.getElementById('resetQuizBtn');
        const resultsCard = document.getElementById('resultsCard');
        const generateQuizForm = document.getElementById('generateQuizForm');
        const newQuizBtn = document.getElementById('newQuizBtn');

        // Generate quiz
        generateQuizForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(generateQuizForm);
            
            try {
                const response = await fetch(generateQuizForm.action, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success && data.quiz && Array.isArray(data.quiz)) {
                    quizData = data.quiz;
                    userAnswers = {};
                    renderQuiz();
                    generateQuizCard.style.display = 'none';
                    quizSection.style.display = 'block';
                    resultsCard.style.display = 'none';
                } else {
                    alert('Error: ' + (data.message || 'Failed to generate quiz'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
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

            questionCounter.textContent = `Question 1 of ${quizData.length}`;

            quizData.forEach((question, index) => {
                const questionDiv = document.createElement('div');
                questionDiv.className = 'quiz-question';
                questionDiv.id = `question-${index}`;

                questionDiv.innerHTML = `
                    <h5>Question ${index + 1}</h5>
                    <p class="mb-3">${question.question || ''}</p>
                    <div class="quiz-options">
                        ${question.options.map((option, optIndex) => `
                            <div class="quiz-option" data-question="${index}" data-option="${option}">
                                ${option}
                            </div>
                        `).join('')}
                    </div>
                `;

                quizQuestions.appendChild(questionDiv);
            });

            // Add click handlers for options
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

        // Submit quiz
        submitQuizBtn.addEventListener('click', () => {
            if (Object.keys(userAnswers).length < quizData.length) {
                if (!confirm('You have not answered all questions. Submit anyway?')) {
                    return;
                }
            }

            // Calculate score client-side
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
            displayResults(score, total, percentage);
        });

        // Display results
        function displayResults(score, total, percentage) {
            document.getElementById('quizScore').textContent = score;
            document.getElementById('totalQuestions').textContent = total;
            document.getElementById('scorePercentage').textContent = `You scored ${percentage}%`;
            
            const progressBar = document.getElementById('scoreProgressBar');
            progressBar.style.width = percentage + '%';
            progressBar.textContent = percentage + '%';

            // Highlight correct/incorrect answers
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

            resultsCard.style.display = 'block';
            submitQuizBtn.disabled = true;
        }

        // Reset quiz
        resetQuizBtn.addEventListener('click', () => {
            userAnswers = {};
            renderQuiz();
            resultsCard.style.display = 'none';
            submitQuizBtn.disabled = false;
        });

        // New quiz
        newQuizBtn.addEventListener('click', () => {
            quizData = [];
            userAnswers = {};
            generateQuizCard.style.display = 'block';
            quizSection.style.display = 'none';
            resultsCard.style.display = 'none';
        });
    </script>
</body>
</html>

