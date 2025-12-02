<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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

        .quiz-question {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border: 1px solid var(--sa-card-border);
            border-radius: 1rem;
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.06);
        }

        .quiz-option {
            padding: 0.75rem;
            margin: 0.5rem 0;
            border: 2px solid rgba(111, 66, 193, 0.15);
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .quiz-option:hover {
            border-color: var(--sa-primary);
            background-color: var(--sa-accent);
        }

        .quiz-option.selected {
            border-color: var(--sa-primary);
            background-color: var(--sa-primary);
            color: white;
        }

        .quiz-option.correct {
            border-color: #28a745;
            background-color: #d4edda;
            color: #000000;
        }

        .quiz-option.incorrect {
            border-color: #dc3545;
            background-color: #f8d7da;
            color: #000000;
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
            border-color: var(--sa-primary);
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
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
            color: var(--sa-primary);
        }

        .btn-check:checked+.btn-outline-secondary {
            background-color: var(--sa-primary);
            border-color: var(--sa-primary);
        }

        .btn-check:checked+.btn-outline-secondary:hover {
            background-color: var(--sa-primary-dark);
            border-color: var(--sa-primary-dark);
        }

        .upload-container {
            background-color: #f8f9fa;
            padding: 30px;
        }

        h4[onclick] {
            transition: color 0.2s;
        }

        h4[onclick]:hover {
            color: var(--sa-primary) !important;
            text-decoration: underline;
        }

        .quiz-meta-card {
            background: linear-gradient(135deg, #f8f1ff, #ffffff);
            border: 1px solid var(--sa-card-border);
            border-radius: 16px;
            box-shadow: inset 0 0 0 1px rgba(111, 66, 193, 0.08);
        }

        .quiz-meta-card .icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background-color: #efe4ff;
            color: var(--sa-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .form-range {
            height: 0.6rem;
            background: linear-gradient(90deg, #ede1ff 0%, #d4b5ff 100%);
            border-radius: 999px;
            accent-color: var(--sa-primary);
        }

        .form-range::-webkit-slider-thumb {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background-color: var(--sa-primary);
            border: 3px solid #ffffff;
            box-shadow: 0 6px 12px rgba(111, 66, 193, 0.3);
            margin-top: -7px;
        }

        .form-range::-moz-range-thumb {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background-color: var(--sa-primary);
            border: 3px solid #ffffff;
            box-shadow: 0 6px 12px rgba(111, 66, 193, 0.3);
        }

        .question-distribution {
            background-color: #fdfbff !important;
            border: 1px solid rgba(111, 66, 193, 0.15);
        }

        .question-type-card {
            border: 1px solid rgba(111, 66, 193, 0.12);
            border-radius: 16px;
            padding: 1rem 1.25rem;
            background-color: #fff;
            box-shadow: 0 6px 20px rgba(111, 66, 193, 0.05);
        }

        .quantity-input-wrapper {
            display: flex;
            align-items: center;
        }

        .question-type-input {
            border: 1px solid rgba(111, 66, 193, 0.3);
            border-radius: 8px;
            font-weight: 600;
            color: var(--sa-primary-dark);
        }

        .question-type-input:focus {
            border-color: var(--sa-primary);
            box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
        }

        /* Bloom's Taxonomy per question type */
        .bloom-taxonomy-section {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px dashed rgba(111, 66, 193, 0.2);
            display: none;
        }

        .bloom-taxonomy-section.active {
            display: block;
        }

        .bloom-select {
            font-size: 0.85rem;
            border: 1px solid rgba(111, 66, 193, 0.25);
            border-radius: 8px;
            padding: 0.4rem 0.6rem;
            background-color: #fdfbff;
            color: var(--sa-primary-dark);
            transition: all 0.2s;
        }

        .bloom-select:focus {
            border-color: var(--sa-primary);
            box-shadow: 0 0 0 0.15rem rgba(111, 66, 193, 0.2);
            outline: none;
        }

        .bloom-label {
            font-size: 0.8rem;
            color: var(--sa-muted);
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0.4rem;
        }

        .bloom-label i {
            color: var(--sa-primary);
        }

        .bloom-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .bloom-badge.remember { background-color: #e3f2fd; color: #1565c0; }
        .bloom-badge.understand { background-color: #e8f5e9; color: #2e7d32; }
        .bloom-badge.apply { background-color: #fff3e0; color: #ef6c00; }
        .bloom-badge.analysis { background-color: #fce4ec; color: #c2185b; }
        .bloom-badge.evaluate { background-color: #f3e5f5; color: #7b1fa2; }
        .bloom-badge.create { background-color: #e0f2f1; color: #00695c; }

        .question-type-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .question-type-info {
            flex: 1;
            min-width: 150px;
        }

        .question-type-controls {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .quiz-list-item {
            border-left: 4px solid transparent;
        }

        .quiz-list-item[data-status="completed"] {
            border-left-color: #28a745;
        }

        .answer-panel {
            border: 1px solid rgba(111, 66, 193, 0.15);
            border-radius: 12px;
            padding: 1rem;
            background-color: #fff;
        }

        .answer-panel.emphasis {
            border-color: rgba(111, 66, 193, 0.35);
            box-shadow: 0 6px 18px rgba(111, 66, 193, 0.08);
        }

        .answer-panel.success {
            border-color: #28a745;
            background-color: #e9f7ef;
        }

        .answer-panel.danger {
            border-color: #dc3545;
            background-color: #fef1f2;
        }

        .review-question-card {
            border: 1px solid rgba(111, 66, 193, 0.1);
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 6px 20px rgba(111, 66, 193, 0.06);
            background-color: #ffffff;
        }

        .review-summary-card {
            border: 1px solid rgba(111, 66, 193, 0.15);
            border-radius: 16px;
            padding: 1.25rem;
            background-color: #f8f4ff;
        }

        #quizEvaluationPanel .review-question-card {
            margin-bottom: 1rem;
        }

        .quiz-modal-nav button {
            min-width: 120px;
        }

        .action-btn {
            background-color: transparent;
            border: none;
            color: #6c757d;
            padding: 8px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            z-index: 1;
        }

        .action-btn:hover {
            background-color: #e7d5ff;
            color: #6f42c1;
        }

        /* Quiz Builder Layout */
        .quiz-builder-left {
            flex: 0 0 40%;
            max-width: 40%;
        }

        .quiz-builder-right {
            flex: 0 0 60%;
            max-width: 60%;
        }

        /* Responsive layout for quiz builder */
        @media (max-width: 991.98px) {

            .quiz-builder-left,
            .quiz-builder-right {
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
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
            color: var(--sa-primary);
            background-color: var(--sa-accent);
        }

        .btn-back {
            background-color: transparent;
            border: none;
            color: var(--sa-primary);
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
            background-color: var(--sa-primary);
            color: white;
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
            background-color: #e7d5ff;
            color: #6f42c1;
        }

        /* Dropdown menu styling */
        main .dropdown-menu,
        .upload-container .dropdown-menu {
            position: absolute !important;
            inset: auto auto auto auto !important;
            top: calc(100% + 8px) !important;
            right: 0 !important;
            left: auto !important;
            margin: 0 !important;
            border-radius: 12px !important;
            border: 1px solid #d4b5ff !important;
            box-shadow: 0 10px 24px rgba(90, 50, 163, 0.12) !important;
            background-color: #ffffff !important;
            min-width: 180px !important;
            width: 180px !important;
            max-width: 180px !important;
            padding: 8px 0 !important;
            overflow: hidden !important;
            transform: none !important;
            z-index: 2147483647 !important;
        }

        .dropdown-menu li {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .dropdown-menu li + li {
            border-top: none;
        }

        .list-group-item .dropdown.show .dropdown-menu {
            z-index: 2147483647 !important;
            width: 180px !important;
            min-width: 180px !important;
            max-width: 180px !important;
        }

        #quizList .list-group-item .dropdown {
            flex-shrink: 0;
            position: relative;
            z-index: 2;
        }

        #quizList .list-group-item:hover .dropdown {
            z-index: 11;
        }

        #quizList .list-group-item .dropdown-menu {
            z-index: 1050 !important;
        }

        .dropdown {
            position: relative;
            z-index: 2147483646;
        }

        .dropdown.show {
            z-index: 2147483646 !important;
        }

        main .dropdown.show .dropdown-menu,
        .upload-container .dropdown.show .dropdown-menu {
            z-index: 2147483647 !important;
            display: block !important;
            position: absolute !important;
            top: calc(100% + 8px) !important;
            right: 0 !important;
            left: auto !important;
            transform: none !important;
            width: 180px !important;
            min-width: 180px !important;
            max-width: 180px !important;
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
    $current_url = $_GET['url'] ?? 'lm/quiz';
    ?>
    <div class="d-flex flex-grow-1">
        <?php include 'app/views/sidebar.php'; ?>
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
            <div class="container-fluid upload-container">
                <h3 style="color: #212529; font-size: 1.5rem; font-weight: 600; margin-bottom: 30px;">Quiz</h3>
                <form method="POST" action="<?= DISPLAY_DOCUMENT ?>" style="display: inline;">
                    <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
                    <h4 style="color: #212529; font-size: 1.25rem; font-weight: 500; margin-bottom: 20px; cursor: pointer; display: inline-block;" onclick="this.closest('form').submit();"><?php echo htmlspecialchars($file['name'] ?? 'Document'); ?></h4>
                </form>
                <?php require_once VIEW_NAVBAR; ?>

                <!-- Quiz Builder Row -->
                <div class="row g-4 mb-4 align-items-stretch">
                    <div class="col-12 col-lg-5 quiz-builder-left">
                        <div class="card h-100" id="generateQuizCard">
                            <div class="card-body">
                                <?php $defaultQuestionTotal = 10; ?>
                                <form id="generateQuizForm" action="<?= GENERATE_QUIZ ?>" method="POST">
                                    <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
                                    <div class="mb-3">
                                        <label class="form-label d-flex justify-content-between align-items-center">
                                            <span>Total Questions</span>
                                            <span id="questionCountLabel" class="badge rounded-pill" style="background-color: var(--sa-accent); color: var(--sa-primary-dark); font-size: 1rem; min-width: 48px;"><?php echo $defaultQuestionTotal; ?></span>
                                        </label>
                                        <input type="range" class="form-range" id="questionCountSlider" min="1" max="25" value="<?php echo $defaultQuestionTotal; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Question Distribution</label>
                                        <div class="question-distribution-container">
                                            <small class="text-muted">
                                                Assigned: <span id="questionTypeTotal" class="fw-semibold"><?php echo $defaultQuestionTotal; ?></span> /
                                                <span id="questionTypeQuota" class="fw-semibold"><?php echo $defaultQuestionTotal; ?></span>
                                            </small>
                                        </div>
                                        <?php
                                        $questionTypes = [
                                            'multiple_choice' => [
                                                'label' => 'Multiple Choice',
                                                'hint' => 'One correct answer per question.',
                                                'icon' => 'bi-circle-fill'
                                            ],
                                            'checkbox' => [
                                                'label' => 'Checkbox (Multi-select)',
                                                'hint' => 'Choose all answers that apply.',
                                                'icon' => 'bi-check-square-fill'
                                            ],
                                            'true_false' => [
                                                'label' => 'True / False',
                                                'hint' => 'Classify statements quickly.',
                                                'icon' => 'bi-toggle-on'
                                            ],
                                            'short_answer' => [
                                                'label' => 'Short Answer',
                                                'hint' => '1-2 sentence responses.',
                                                'icon' => 'bi-chat-left-text-fill'
                                            ],
                                            'long_answer' => [
                                                'label' => 'Long Answer',
                                                'hint' => 'Detailed explanation.',
                                                'icon' => 'bi-file-text-fill'
                                            ]
                                        ];
                                        $bloomLevels = [
                                            'remember' => ['label' => 'Remember', 'desc' => 'Recall facts and basic concepts'],
                                            'understand' => ['label' => 'Understand', 'desc' => 'Explain ideas or concepts'],
                                            'apply' => ['label' => 'Apply', 'desc' => 'Use information in new situations'],
                                            'analysis' => ['label' => 'Analysis', 'desc' => 'Draw connections among ideas'],
                                            'evaluate' => ['label' => 'Evaluate', 'desc' => 'Justify a stand or decision'],
                                            'create' => ['label' => 'Create', 'desc' => 'Produce new or original work']
                                        ];
                                        foreach ($questionTypes as $typeKey => $meta):
                                            $defaultValue = $typeKey === 'multiple_choice' ? $defaultQuestionTotal : 0;
                                        ?>
                                            <div class="question-type-card mb-2" data-question-type-card="<?= $typeKey ?>">
                                                <div class="question-type-header">
                                                    <div class="question-type-info">
                                                        <p class="mb-0 fw-semibold d-flex align-items-center gap-2">
                                                            <i class="bi <?= $meta['icon'] ?>" style="color: var(--sa-primary); font-size: 0.9rem;"></i>
                                                            <?= $meta['label'] ?>
                                                        </p>
                                                        <small class="text-muted"><?= $meta['hint'] ?></small>
                                                    </div>
                                                    <div class="question-type-controls">
                                                        <input type="number" class="form-control question-type-input" min="0" max="25" value="<?= $defaultValue ?>" data-question-type="<?= $typeKey ?>" style="width: 80px; text-align: center;">
                                                    </div>
                                                </div>
                                                <div class="bloom-taxonomy-section <?= $defaultValue > 0 ? 'active' : '' ?>" data-bloom-section="<?= $typeKey ?>">
                                                    <label class="bloom-label">
                                                        <i class="bi bi-mortarboard-fill"></i>
                                                        Bloom's Level
                                                    </label>
                                                    <select class="bloom-select w-100" data-bloom-type="<?= $typeKey ?>">
                                                        <?php foreach ($bloomLevels as $levelKey => $level): ?>
                                                            <option value="<?= $levelKey ?>" <?= $levelKey === 'remember' ? 'selected' : '' ?>>
                                                                <?= $level['label'] ?> - <?= $level['desc'] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Set the quantity and Bloom's Taxonomy level for each question type.
                                        </small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label d-block">Exam Mode</label>
                                        
                                        <div class="btn-group" role="group">
                                            <input type="radio" class="btn-check" name="examMode" id="examModeOff" value="0" checked>
                                            <label class="btn btn-outline-secondary" for="examModeOff">Practice</label>
                                            <input type="radio" class="btn-check" name="examMode" id="examModeOn" value="1">
                                            <label class="btn btn-outline-secondary" for="examModeOn">Exam Mode</label>
                                            
                                        </div>
                                        <small class="text-muted d-block">Exam Mode record the score for performance tracking.</small>

                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Instructions (optional)</label>
                                        <input type="text" name="instructions" class="form-control" placeholder="Describe your instruction">
                                    </div>
                                    <button type="submit" id="genQuiz" class="btn btn-primary">Generate Quiz</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-7 quiz-builder-right">
                        <div class="card h-100" id="quizListCard">
                            <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-1">Generated Quizzes</h5>
                                    <small class="text-muted d-block" id="currentQuizSortLabel">Sorted by: Latest to Oldest</small>
                                </div>
                                <div class="dropdown">
                                    <button class="btn-icon" type="button" id="quizSortDropdown" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" title="Sort quizzes">
                                        <i class="bi bi-funnel-fill" style="font-size: 1.1rem;"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="quizSortDropdown">
                                        <li><a class="dropdown-item quiz-sort-option" href="#" data-sort="asc">A to Z</a></li>
                                        <li><a class="dropdown-item quiz-sort-option" href="#" data-sort="desc">Z to A</a></li>
                                        <li><a class="dropdown-item quiz-sort-option" href="#" data-sort="latest">Latest to Oldest</a></li>
                                        <li><a class="dropdown-item quiz-sort-option" href="#" data-sort="oldest">Oldest to Latest</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush" id="quizList">
                                    <?php foreach ($quizList as $quiz): ?>
                                        <?php
                                        $status = $quiz['status'] ?? 'pending';
                                        $isCompleted = $status === 'completed';
                                        $examModeEnabled = isset($quiz['examMode']) ? (int)$quiz['examMode'] === 1 : false;
                                        ?>
                                        <?php
                                        $normalizedTitle = strtolower($quiz['title'] ?? '');
                                        $createdTimestamp = strtotime($quiz['createdAt'] ?? '') ?: 0;
                                        ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center quiz-list-item"
                                            data-quiz-id="<?= htmlspecialchars($quiz['quizID']) ?>"
                                            data-status="<?= htmlspecialchars($status) ?>"
                                            data-exam-mode="<?= $examModeEnabled ? '1' : '0' ?>"
                                            data-title="<?= htmlspecialchars($normalizedTitle) ?>"
                                            data-created="<?= $createdTimestamp ?>">
                                            <div class="flex-grow-1" style="min-width: 0;">
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <strong title="<?php echo htmlspecialchars($quiz['title']); ?>"><?php echo htmlspecialchars($quiz['title']); ?></strong>
                                                    <?php if ($examModeEnabled): ?>
                                                        <span class="badge rounded-pill bg-warning text-dark">Exam</span>
                                                    <?php endif; ?>
                                                    <span class="badge rounded-pill <?= $isCompleted ? 'bg-success' : 'bg-secondary' ?>" data-status-badge="true">
                                                        <?= $isCompleted ? 'Completed' : 'Pending' ?>
                                                    </span>
                                                </div>
                                                <small class="text-muted d-block">
                                                    Created: <?php echo htmlspecialchars($quiz['createdAt']); ?>
                                                    <?php if ($isCompleted && !empty($quiz['markAt'])): ?>
                                                        · Completed at <?php echo htmlspecialchars($quiz['markAt']); ?>
                                                    <?php endif; ?>
                                                </small>
                                                <?php if ($isCompleted && !empty($quiz['totalScore'])): ?>
                                                    <small class="text-muted d-block">Score: <?= htmlspecialchars($quiz['totalScore']); ?>%</small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="d-flex gap-2 flex-shrink-0 align-items-center">
                                                <button class="btn btn-primary btn-sm start-quiz-btn"
                                                    data-quiz-id="<?= htmlspecialchars($quiz['quizID']) ?>"
                                                    <?= $isCompleted ? 'disabled' : '' ?>>
                                                    Start Quiz
                                                </button>
                                                <div class="dropdown">
                                                    <button class="action-btn" type="button" id="dropdownQuizActions<?php echo $quiz['quizID']; ?>" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" title="Quiz actions">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownQuizActions<?php echo $quiz['quizID']; ?>">
                                                        <li>
                                                            <a class="dropdown-item review-quiz-btn" href="#" data-quiz-id="<?= htmlspecialchars($quiz['quizID']) ?>" <?= $isCompleted ? '' : 'style="pointer-events: none; opacity: 0.5;"' ?>>
                                                                Review
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item delete-quiz-btn" href="#" data-quiz-id="<?= htmlspecialchars($quiz['quizID']) ?>" data-file-id="<?= htmlspecialchars($file['fileID']) ?>">Delete</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

    </div>
    </main>
    </div>

    <!-- Take Quiz Modal -->
    <div class="modal fade" id="takeQuizModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Active Quiz Session</h5>
                        <small class="text-muted">Complete the quiz to exit. Navigation outside the window is disabled.</small>
                    </div>
                    <button type="button" class="btn-back" data-bs-dismiss="modal" aria-label="Close" id="quizEvaluationCloseBtn" style="display: none;">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fw-semibold">Progress: <span id="quizModalCounter">0 / 0</span></div>
                        <div>
                            <span class="badge bg-secondary" id="quizModeBadge">Practice Mode</span>
                        </div>
                    </div>

                    <div id="activeQuizQuestion" class="mb-3"></div>
                    <div id="quizEvaluationPanel" class="d-none"></div>
                </div>
                <div class="modal-footer d-flex justify-content-between" id="quizFooterControls">
                    <button type="button" class="btn btn-outline-secondary" id="quizPrevBtn" disabled>
                        <i class="bi bi-chevron-left me-2"></i>Previous
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" id="quizNextBtn">
                            Next<i class="bi bi-chevron-right ms-2"></i>
                        </button>
                        <button type="button" class="btn btn-primary" id="quizSubmitBtn">
                            Submit Quiz
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Quiz Modal -->
    <div class="modal fade" id="reviewQuizModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quiz Review</h5>
                    <button type="button" class="btn-back" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="reviewSummary" class="review-summary-card mb-3 d-none"></div>
                    <div id="reviewQuizBody"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <style>
        /* Prevent dropdowns from being clipped by list container */
        .list-group-item { 
            overflow: visible; 
        }
        #quizList .list-group-item .dropdown-menu { 
            z-index: 1050 !important; 
        }
    </style>
    <script>
        // Color constants matching CSS variables
        const SA_PRIMARY = '#6f42c1';
        const SA_PRIMARY_DARK = '#593093';
        const SA_ACCENT = '#e7d5ff';
        const SA_ACCENT_STRONG = '#d4b5ff';
        const SA_MUTED = '#6c757d';

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
            }, 5000);
        }

        // Confirmation dialog function (kept for backward compatibility, but uses modal)
        function showConfirmation(message, onConfirm, onCancel = null) {
            showConfirmModal({
                message: message,
                title: 'Confirm Action',
                confirmText: 'Confirm',
                cancelText: 'Cancel',
                danger: false,
                onConfirm: onConfirm,
                onCancel: onCancel
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const generateQuizForm = document.getElementById('generateQuizForm');
            const questionCountSlider = document.getElementById('questionCountSlider');
            const questionCountLabel = document.getElementById('questionCountLabel');
            const questionTypeInputs = document.querySelectorAll('.question-type-input');
            const questionTypeTotal = document.getElementById('questionTypeTotal');
            const questionTypeQuota = document.getElementById('questionTypeQuota');
            const takeQuizModalEl = document.querySelector('#takeQuizModal');
            const takeQuizModal = new bootstrap.Modal(takeQuizModalEl);
            const reviewQuizModal = new bootstrap.Modal(document.querySelector('#reviewQuizModal'));
            const quizModalCounter = document.getElementById('quizModalCounter');
            const quizModeBadge = document.getElementById('quizModeBadge');
            const quizPrevBtn = document.getElementById('quizPrevBtn');
            const quizNextBtn = document.getElementById('quizNextBtn');
            const quizSubmitBtn = document.getElementById('quizSubmitBtn');
            const quizCloseAfterSubmit = document.getElementById('quizCloseAfterSubmit');
            const quizEvaluationCloseBtn = document.getElementById('quizEvaluationCloseBtn');
            const quizEvaluationPanel = document.getElementById('quizEvaluationPanel');
            const activeQuizQuestion = document.getElementById('activeQuizQuestion');
            const quizTimerAlert = document.getElementById('quizTimerAlert');

            let activeQuizId = null;
            let activeExamMode = 0;
            let activeQuizQuestions = [];
            let userResponses = {};
            let currentQuestionIndex = 0;
            let quizSessionActive = false;
            let quizFinished = false;
            let quizChart = null;

            // Load and render quiz statistics chart and report
            async function loadQuizStatistics() {
                try {
                    const fileId = <?= isset($file['fileID']) ? $file['fileID'] : 'null' ?>;
                    const url = '<?= GET_QUIZ_STATISTICS ?>' + (fileId ? `?file_id=${fileId}` : '');
                    const response = await fetch(url);
                    const result = await response.json();

                    if (result.success && result.data) {
                        renderQuizChart(result.data);
                        renderQuizReport(result.data);
                    } else {
                        document.getElementById('quizStatsCard').style.display = 'none';
                    }
                } catch (error) {
                    console.error('Error loading quiz statistics:', error);
                    document.getElementById('quizStatsCard').style.display = 'none';
                }
            }

            function renderQuizChart(data) {
                const ctx = document.getElementById('quizPerformanceChart');
                if (!ctx) return;

                const quizzes = data.quizzes || [];
                const stats = data.statistics || {};

                // Group quizzes by date and mode, keeping original date for sorting
                const practiceData = {};
                const examData = {};
                const dateMap = {}; // Map formatted date to original date

                quizzes.forEach(quiz => {
                    const originalDate = new Date(quiz.markAt || quiz.createdAt);
                    const date = originalDate.toLocaleDateString('en-US', {
                        month: 'short',
                        day: 'numeric'
                    });
                    const score = parseFloat(quiz.totalScore || quiz.attemptScore || 0);

                    if (!dateMap[date]) {
                        dateMap[date] = originalDate;
                    }

                    if (parseInt(quiz.examMode) === 1) {
                        if (!examData[date]) examData[date] = [];
                        examData[date].push(score);
                    } else {
                        if (!practiceData[date]) practiceData[date] = [];
                        practiceData[date].push(score);
                    }
                });

                // Calculate averages per date, sorted by original date
                const allDates = [...new Set(Object.keys(practiceData).concat(Object.keys(examData)))];
                const dates = allDates.sort((a, b) => {
                    return dateMap[a] - dateMap[b];
                });

                const practiceScores = dates.map(date => {
                    const scores = practiceData[date] || [];
                    return scores.length > 0 ? (scores.reduce((a, b) => a + b, 0) / scores.length).toFixed(1) : null;
                });

                const examScores = dates.map(date => {
                    const scores = examData[date] || [];
                    return scores.length > 0 ? (scores.reduce((a, b) => a + b, 0) / scores.length).toFixed(1) : null;
                });

                if (quizChart) {
                    quizChart.destroy();
                }

                quizChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: dates,
                        datasets: [{
                                label: 'Practice Mode',
                                data: practiceScores,
                                borderColor: SA_PRIMARY,
                                backgroundColor: 'rgba(111, 66, 193, 0.1)',
                                tension: 0.4,
                                fill: true,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                pointBackgroundColor: SA_PRIMARY,
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2
                            },
                            {
                                label: 'Exam Mode',
                                data: examScores,
                                borderColor: '#ffc107',
                                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                                tension: 0.4,
                                fill: true,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                pointBackgroundColor: '#ffc107',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15,
                                    font: {
                                        size: 13,
                                        weight: '500'
                                    }
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: {
                                    size: 14,
                                    weight: '600'
                                },
                                bodyFont: {
                                    size: 13
                                },
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.y + '%';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    },
                                    font: {
                                        size: 12
                                    }
                                },
                                grid: {
                                    color: 'rgba(111, 66, 193, 0.1)'
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 12
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            function renderQuizReport(data) {
                const reportSection = document.getElementById('quizReportSection');
                if (!reportSection) return;

                const stats = data.statistics || {};
                const quizzes = data.quizzes || [];

                const totalQuizzes = parseInt(stats.totalQuizzes) || 0;
                const examQuizzes = parseInt(stats.examQuizzes) || 0;
                const practiceQuizzes = parseInt(stats.practiceQuizzes) || 0;
                const avgScore = parseFloat(stats.avgScore) || 0;
                const avgExamScore = parseFloat(stats.avgExamScore) || 0;
                const avgPracticeScore = parseFloat(stats.avgPracticeScore) || 0;
                const maxScore = parseFloat(stats.maxScore) || 0;
                const minScore = parseFloat(stats.minScore) || 0;

                // Calculate improvement trend
                const recentQuizzes = quizzes.slice(0, 5);
                const olderQuizzes = quizzes.slice(5, 10);
                const recentAvg = recentQuizzes.length > 0 ?
                    recentQuizzes.reduce((sum, q) => sum + parseFloat(q.totalScore || q.attemptScore || 0), 0) / recentQuizzes.length :
                    0;
                const olderAvg = olderQuizzes.length > 0 ?
                    olderQuizzes.reduce((sum, q) => sum + parseFloat(q.totalScore || q.attemptScore || 0), 0) / olderQuizzes.length :
                    0;
                const improvement = recentAvg > 0 && olderAvg > 0 ? ((recentAvg - olderAvg) / olderAvg * 100).toFixed(1) : 0;

                reportSection.innerHTML = `
                    <div class="col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f6efff, #ffffff);">
                            <div class="card-body text-center">
                                <div class="mb-2">
                                    <i class="bi bi-clipboard-check" style="font-size: 2rem; color: var(--sa-primary);"></i>
                        </div>
                                <h3 class="mb-1" style="color: var(--sa-primary);">${totalQuizzes}</h3>
                                <p class="text-muted mb-0 small">Total Completed</p>
                                </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff8e1, #ffffff);">
                            <div class="card-body text-center">
                                <div class="mb-2">
                                    <i class="bi bi-shield-check" style="font-size: 2rem; color: #ffc107;"></i>
                                </div>
                                <h3 class="mb-1" style="color: #ffc107;">${examQuizzes}</h3>
                                <p class="text-muted mb-0 small">Exam Mode</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #ede1ff, #ffffff);">
                            <div class="card-body text-center">
                                <div class="mb-2">
                                    <i class="bi bi-book" style="font-size: 2rem; color: var(--sa-primary);"></i>
                                </div>
                                <h3 class="mb-1" style="color: var(--sa-primary);">${practiceQuizzes}</h3>
                                <p class="text-muted mb-0 small">Practice Mode</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #e8f5e9, #ffffff);">
                            <div class="card-body text-center">
                                <div class="mb-2">
                                    <i class="bi bi-graph-up-arrow" style="font-size: 2rem; color: #28a745;"></i>
                                </div>
                                <h3 class="mb-1" style="color: #28a745;">${avgScore.toFixed(1)}%</h3>
                                <p class="text-muted mb-0 small">Average Score</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f8f9fa, #ffffff);">
                            <div class="card-body">
                                <h6 class="mb-3" style="color: var(--sa-primary); font-weight: 600;">
                                    <i class="bi bi-bar-chart me-2"></i>Performance Breakdown
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted">Exam Mode Average:</span>
                                            <strong style="color: #ffc107;">${avgExamScore > 0 ? avgExamScore.toFixed(1) + '%' : 'N/A'}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted">Practice Mode Average:</span>
                                            <strong style="color: var(--sa-primary);">${avgPracticeScore > 0 ? avgPracticeScore.toFixed(1) + '%' : 'N/A'}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted">Score Range:</span>
                                            <strong>${minScore > 0 ? minScore.toFixed(1) : 'N/A'}% - ${maxScore > 0 ? maxScore.toFixed(1) : 'N/A'}%</strong>
                                        </div>
                                    </div>
                                </div>
                                ${improvement != 0 ? `
                                <div class="mt-3 pt-3 border-top">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Recent Performance Trend:</span>
                                        <strong class="${parseFloat(improvement) > 0 ? 'text-success' : 'text-danger'}">
                                            ${parseFloat(improvement) > 0 ? '+' : ''}${improvement}%
                                            <i class="bi bi-arrow-${parseFloat(improvement) > 0 ? 'up' : 'down'}-right ms-1"></i>
                                        </strong>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                        </div>
                    `;
            }

            // Load statistics on page load
            loadQuizStatistics();

            function updateDistributionTotals(changedInput = null) {
                const quota = parseInt(questionCountSlider.value, 10);
                let sum = 0;
                questionTypeInputs.forEach(input => {
                    const val = parseInt(input.value || '0', 10);
                    sum += isNaN(val) ? 0 : val;
                });
                if (sum > quota) {
                    const excess = sum - quota;
                    const targetInput = changedInput || questionTypeInputs[0];
                    const currentVal = parseInt(targetInput.value || '0', 10);
                    targetInput.value = Math.max(0, currentVal - excess);
                    sum -= excess;
                }
                questionTypeTotal.textContent = sum;
                questionTypeQuota.textContent = quota;
                return sum;
            }

            questionCountSlider.addEventListener('input', () => {
                questionCountLabel.textContent = questionCountSlider.value;
                updateDistributionTotals();
            });

            // Function to toggle Bloom's Taxonomy section visibility
            function toggleBloomSection(typeKey, show) {
                const section = document.querySelector(`[data-bloom-section="${typeKey}"]`);
                if (section) {
                    if (show) {
                        section.classList.add('active');
                    } else {
                        section.classList.remove('active');
                    }
                }
            }

            questionTypeInputs.forEach(input => {
                input.addEventListener('input', () => {
                    const quota = parseInt(questionCountSlider.value, 10);
                    let currentTotal = 0;
                    questionTypeInputs.forEach(inp => {
                        if (inp !== input) {
                            const val = parseInt(inp.value || '0', 10);
                            currentTotal += isNaN(val) ? 0 : val;
                        }
                    });

                    let value = parseInt(input.value || '0', 10);
                    if (isNaN(value)) value = 0;
                    if (value < 0) value = 0;
                    if (value > 25) value = 25;

                    const remaining = quota - currentTotal;
                    if (value > remaining) {
                        value = Math.max(0, remaining);
                    }

                    input.value = value;
                    updateDistributionTotals(input);

                    // Toggle Bloom's Taxonomy section
                    const typeKey = input.dataset.questionType;
                    toggleBloomSection(typeKey, value > 0);
                });
            });

            updateDistributionTotals();
            
            // Initialize Bloom sections visibility on page load
            questionTypeInputs.forEach(input => {
                const typeKey = input.dataset.questionType;
                const value = parseInt(input.value || '0', 10);
                toggleBloomSection(typeKey, value > 0);
            });

            function buildDistributionPayload() {
                const distribution = {};
                questionTypeInputs.forEach(input => {
                    const type = input.dataset.questionType;
                    distribution[type] = parseInt(input.value || '0', 10) || 0;
                });
                return distribution;
            }

            function buildBloomLevelsPayload() {
                const bloomLevels = {};
                const bloomSelects = document.querySelectorAll('.bloom-select[data-bloom-type]');
                bloomSelects.forEach(select => {
                    const type = select.dataset.bloomType;
                    const count = parseInt(document.querySelector(`[data-question-type="${type}"]`)?.value || '0', 10);
                    if (count > 0) {
                        bloomLevels[type] = select.value;
                    }
                });
                return bloomLevels;
            }

            generateQuizForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const totalQuestions = parseInt(questionCountSlider.value, 10);
                const assigned = updateDistributionTotals();
                if (assigned !== totalQuestions) {
                    showSnackbar('Please allocate exactly ' + totalQuestions + ' questions across the selected types.', 'error');
                    return;
                }

                const submitButton = generateQuizForm.querySelector('#genQuiz');
                const originalText = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Generating...';

                try {
                    const formData = new FormData(generateQuizForm);
                    formData.append('totalQuestions', totalQuestions);
                    formData.append('questionDistribution', JSON.stringify(buildDistributionPayload()));
                    
                    // Get Bloom's Taxonomy levels per question type
                    const bloomLevels = buildBloomLevelsPayload();
                    formData.append('bloomLevels', JSON.stringify(bloomLevels));
                    
                    // Get examMode with null check and default
                    const examModeInput = document.querySelector('input[name="examMode"]:checked');
                    const examMode = examModeInput ? examModeInput.value : '0';
                    formData.append('examMode', examMode);

                    const response = await fetch(generateQuizForm.action, {
                        method: 'POST',
                        body: formData
                    });

                    // Get response text first (can only read once)
                    const responseText = await response.text();

                    // Check if response is ok
                    if (!response.ok) {
                        console.error('HTTP Error:', response.status, responseText);
                        showSnackbar(`Server error (${response.status}): ${responseText.substring(0, 100)}`, 'error');
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;
                        return;
                    }

                    // Try to parse JSON response
                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch (parseError) {
                        console.error('JSON Parse Error:', parseError);
                        console.error('Response text:', responseText);
                        showSnackbar('Invalid response from server. Please check console for details.', 'error');
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;
                        return;
                    }

                    if (!data.success) {
                        showSnackbar(data.message || 'Failed to generate quiz. Please try again.', 'error');
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;
                        return;
                    }
                    showSnackbar('Quiz generated successfully! You can now start taking the quiz.', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } catch (error) {
                    console.error('Quiz Generation Error:', error);
                    console.error('Error details:', {
                        message: error.message,
                        stack: error.stack,
                        name: error.name
                    });
                    showSnackbar(`Error: ${error.message || 'An error occurred while generating the quiz. Please try again.'}`, 'error');
                } finally {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            });

            function normalizeType(type) {
                return (type || 'multiple_choice').toString().toLowerCase().replace(/\s+/g, '_');
            }

            function persistCurrentAnswer() {
                const question = activeQuizQuestions[currentQuestionIndex];
                if (!question) return;
                const type = normalizeType(question.type);
                if (type === 'multiple_choice' || type === 'true_false') {
                    const selected = document.querySelector('input[name="question-option-' + currentQuestionIndex + '"]:checked');
                    if (selected) {
                        userResponses[currentQuestionIndex] = selected.value;
                    }
                } else if (type === 'checkbox') {
                    const selected = Array.from(document.querySelectorAll('input[name="question-checkbox-' + currentQuestionIndex + '"]:checked'))
                        .map(input => input.value);
                    if (selected.length) {
                        userResponses[currentQuestionIndex] = selected;
                    } else {
                        delete userResponses[currentQuestionIndex];
                    }
                } else {
                    const textarea = document.querySelector('#question-textarea-' + currentQuestionIndex);
                    if (textarea && textarea.value.trim() !== '') {
                        userResponses[currentQuestionIndex] = textarea.value.trim();
                    } else {
                        delete userResponses[currentQuestionIndex];
                    }
                }
            }

            function renderActiveQuestion() {
                const question = activeQuizQuestions[currentQuestionIndex];
                if (!question) return;
                if (quizModalCounter) {
                    quizModalCounter.textContent = `${currentQuestionIndex + 1} / ${activeQuizQuestions.length}`;
                }
                const type = normalizeType(question.type);
                let content = `<h5 class="mb-3">Question ${currentQuestionIndex + 1}</h5>`;
                content += `<p>${question.question || ''}</p>`;
                if (type === 'multiple_choice' || type === 'true_false') {
                    const options = Array.isArray(question.options) ? question.options : ['True', 'False'];
                    content += '<div class="list-group">';
                    options.forEach((option, index) => {
                        const id = `option-${currentQuestionIndex}-${index}`;
                        const checked = userResponses[currentQuestionIndex] === option ? 'checked' : '';
                        content += `
                            <label class="list-group-item d-flex gap-2 align-items-center">
                                <input class="form-check-input me-2" type="radio" name="question-option-${currentQuestionIndex}" value="${option}" id="${id}" ${checked}>
                                <span>${option}</span>
                            </label>
                        `;
                    });
                    content += '</div>';
                } else if (type === 'checkbox') {
                    const options = Array.isArray(question.options) ? question.options : [];
                    const selected = Array.isArray(userResponses[currentQuestionIndex]) ? userResponses[currentQuestionIndex] : [];
                    content += '<div class="list-group">';
                    options.forEach((option, index) => {
                        const id = `checkbox-${currentQuestionIndex}-${index}`;
                        const checked = selected.includes(option) ? 'checked' : '';
                        content += `
                            <label class="list-group-item d-flex gap-2 align-items-center">
                                <input class="form-check-input me-2" type="checkbox" name="question-checkbox-${currentQuestionIndex}" value="${option}" id="${id}" ${checked}>
                                <span>${option}</span>
                            </label>
                        `;
                    });
                    content += '</div>';
                } else {
                    const rows = type === 'long_answer' ? 6 : 4;
                    const existing = userResponses[currentQuestionIndex] || '';
                    content += `
                        <div class="mb-3">
                            <label class="form-label">Your Answer</label>
                            <textarea class="form-control" rows="${rows}" id="question-textarea-${currentQuestionIndex}" placeholder="Write your answer here...">${existing}</textarea>
                        </div>
                    `;
                }
                if (activeQuizQuestion) {
                    activeQuizQuestion.innerHTML = content;
                }
                if (quizPrevBtn) {
                    quizPrevBtn.disabled = currentQuestionIndex === 0;
                }
                if (quizNextBtn) {
                    quizNextBtn.disabled = currentQuestionIndex >= activeQuizQuestions.length - 1;
                }
            }

            async function launchQuiz(quizId) {
                try {
                    const formData = new FormData();
                    formData.append('quiz_id', quizId);
                    const response = await fetch('<?= VIEW_QUIZ_ROUTE ?>', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    if (!data.success) {
                        showSnackbar(data.message || 'Unable to load quiz. Please try again.', 'error');
                        return;
                    }
                    if (data.alreadyCompleted) {
                        showSnackbar('This quiz has already been completed. You can review your answers instead.', 'error');
                        updateQuizListEntry(quizId, true, data.examMode ? data.examMode : 0);
                        return;
                    }
                    activeQuizId = quizId;
                    activeExamMode = data.examMode ? 1 : 0;
                    activeQuizQuestions = data.quiz || [];
                    userResponses = {};
                    currentQuestionIndex = 0;
                    quizFinished = false;
                    quizSessionActive = true;
                    if (activeQuizQuestion) activeQuizQuestion.classList.remove('d-none');
                    if (quizEvaluationPanel) quizEvaluationPanel.classList.add('d-none');
                    if (quizPrevBtn) quizPrevBtn.classList.remove('d-none');
                    if (quizNextBtn) quizNextBtn.classList.remove('d-none');
                    if (quizSubmitBtn) quizSubmitBtn.classList.remove('d-none');
                    if (quizCloseAfterSubmit) quizCloseAfterSubmit.classList.add('d-none');
                    if (quizEvaluationCloseBtn) quizEvaluationCloseBtn.style.display = 'none';
                    if (quizTimerAlert) quizTimerAlert.classList.toggle('d-none', !activeExamMode);
                    if (quizModeBadge) {
                        quizModeBadge.className = 'badge ' + (activeExamMode ? 'bg-warning text-dark' : 'bg-secondary');
                        quizModeBadge.textContent = activeExamMode ? 'Exam Mode' : 'Practice Mode';
                    }
                    renderActiveQuestion();
                    if (takeQuizModal) {
                        takeQuizModal.show();
                    }
                    showSnackbar('Quiz started successfully. Good luck!', 'success');
                } catch (error) {
                    showSnackbar('Failed to start quiz. Please try again.', 'error');
                    console.error('Error launching quiz:', error);
                }
            }

            async function submitActiveQuiz() {
                const totalQuestions = activeQuizQuestions.length;
                const answered = Object.keys(userResponses).length;
                if (answered !== totalQuestions) {
                    showSnackbar(`Please answer all ${totalQuestions} questions before submitting. You have answered ${answered} out of ${totalQuestions} questions.`, 'error');
                    return;
                }
                quizSubmitBtn.disabled = true;
                quizSubmitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Submitting...';
                try {
                    const formData = new FormData();
                    formData.append('quiz_id', activeQuizId);
                    formData.append('user_answers', JSON.stringify(userResponses));
                    const response = await fetch('<?= SUBMIT_QUIZ ?>', {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    if (!data.success) {
                        showSnackbar(data.message || 'Failed to submit quiz. Please try again.', 'error');
                        return;
                    }
                    renderEvaluationPanel(data);
                    updateQuizListEntry(activeQuizId, true, data.examMode, data.percentage);
                    quizFinished = true;
                    quizSessionActive = false;
                    if (quizPrevBtn) quizPrevBtn.classList.add('d-none');
                    if (quizNextBtn) quizNextBtn.classList.add('d-none');
                    if (quizSubmitBtn) quizSubmitBtn.classList.add('d-none');
                    if (quizCloseAfterSubmit) quizCloseAfterSubmit.classList.remove('d-none');
                    // Show the close button in modal header when quiz is completed
                    if (quizEvaluationCloseBtn) quizEvaluationCloseBtn.style.display = '';
                } catch (error) {
                    showSnackbar('An error occurred while submitting the quiz. Please try again.', 'error');
                    console.error('Error submitting quiz:', error);
                } finally {
                    quizSubmitBtn.disabled = false;
                    quizSubmitBtn.innerHTML = 'Submit Quiz';
                }
            }

            function renderEvaluationPanel(data) {
                activeQuizQuestion.classList.add('d-none');
                quizEvaluationPanel.classList.remove('d-none');
                const results = data.results || [];
                const score = data.percentage ?? 0;
                const totalQuestions = results.length;
                const correctCount = results.filter(r => r.isCorrect).length;
                const incorrectCount = totalQuestions - correctCount;
                const modeBadge = activeExamMode ?
                    '<span class="badge px-3 py-2" style="background-color: var(--sa-primary); color: white;"><i class="bi bi-shield-check me-1"></i>Exam Mode</span>' :
                    '<span class="badge px-3 py-2" style="background-color: var(--sa-accent); color: var(--sa-primary-dark);"><i class="bi bi-book me-1"></i>Practice Mode</span>';

                let html = `
                    <div class="review-summary-card mb-4 p-4">
                        <div class="d-flex flex-wrap gap-4 justify-content-between align-items-center mb-3">
                            <div>
                                <p class="text-muted mb-1 small">Quiz Evaluation Complete</p>
                                <h2 class="mb-0" style="color: ${score >= 70 ? '#28a745' : score >= 50 ? '#ffc107' : '#dc3545'}; font-size: 2.5rem; font-weight: 700;">${score.toFixed(1)}%</h2>
                            </div>
                            <div class="text-end">
                                ${modeBadge}
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-6 col-md-3">
                                <div class="text-center p-2 rounded" style="background-color: #ffffff; border: 2px solid ${SA_ACCENT};">
                                    <p class="mb-1 small" style="color: ${SA_MUTED};">Total</p>
                                    <p class="mb-0 fw-bold" style="font-size: 1.5rem; color: ${SA_PRIMARY}">${totalQuestions}</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-center p-2 rounded" style="background-color: #d4edda; border: 2px solid #28a745;">
                                    <p class="text-muted mb-1 small" style="color: #155724;">Correct</p>
                                    <p class="mb-0 fw-bold" style="font-size: 1.5rem; color: #28a745;">${correctCount}</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-center p-2 rounded" style="background-color: #f8d7da; border: 2px solid #dc3545;">
                                    <p class="text-muted mb-1 small" style="color: #721c24;">Incorrect</p>
                                    <p class="mb-0 fw-bold" style="font-size: 1.5rem; color: #dc3545;">${incorrectCount}</p>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-center p-2 rounded" style="background-color: ${SA_ACCENT}; border: 2px solid ${SA_PRIMARY};">
                                    <p class="mb-1 small" style="color: ${SA_MUTED};">Accuracy</p>
                                    <p class="mb-0 fw-bold" style="font-size: 1.5rem; color: ${SA_PRIMARY};">${totalQuestions > 0 ? ((correctCount / totalQuestions) * 100).toFixed(0) : 0}%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                results.forEach((result, index) => {
                    const questionStub = {
                        question: result.question,
                        type: result.type,
                        answer: result.correctAnswer,
                        options: result.options || (activeQuizQuestions[index] ? activeQuizQuestions[index].options : null),
                        explanation: result.explanation || ''
                    };
                    html += buildReviewCard(questionStub, result, index);
                });
                quizEvaluationPanel.innerHTML = html;
            }

            function updateQuizListEntry(quizId, completed, examMode, score = null) {
                const row = document.querySelector(`.quiz-list-item[data-quiz-id="${quizId}"]`);
                if (!row) return;
                row.dataset.status = completed ? 'completed' : 'pending';
                const badge = row.querySelector('[data-status-badge="true"]');
                if (badge) {
                    badge.className = 'badge rounded-pill ' + (completed ? 'bg-success' : 'bg-secondary');
                    badge.textContent = completed ? 'Completed' : 'Pending';
                }
                const startBtn = row.querySelector('.start-quiz-btn');
                const reviewBtn = row.querySelector('.review-quiz-btn');
                if (startBtn) startBtn.disabled = completed;
                if (reviewBtn) {
                    if (completed) {
                        reviewBtn.style.pointerEvents = 'auto';
                        reviewBtn.style.opacity = '1';
                    } else {
                        reviewBtn.style.pointerEvents = 'none';
                        reviewBtn.style.opacity = '0.5';
                    }
                }
                if (completed && score !== undefined && score !== null) {
                    let scoreLabel = row.querySelector('.quiz-score-label');
                    if (!scoreLabel) {
                        scoreLabel = document.createElement('small');
                        scoreLabel.className = 'text-muted d-block quiz-score-label';
                        const infoContainer = row.querySelector('.flex-grow-1');
                        if (infoContainer) {
                            infoContainer.appendChild(scoreLabel);
                        }
                    }
                    scoreLabel.textContent = `Score: ${score}%`;
                }
            }

            function buildReviewCard(question, feedback = null, index = 0) {
                const type = normalizeType(question.type);
                const isCorrect = feedback ? feedback.isCorrect : false;
                // Use correctAnswer from feedback if available (for long answer questions), otherwise use question.answer
                const correctAnswer = feedback && feedback.correctAnswer !== undefined ?
                    (Array.isArray(feedback.correctAnswer) ? feedback.correctAnswer : [feedback.correctAnswer ?? '']) :
                    (Array.isArray(question.answer) ? question.answer : [question.answer ?? '']);
                const userAnswer = feedback && feedback.userAnswer !== undefined ?
                    (Array.isArray(feedback.userAnswer) ? feedback.userAnswer : [feedback.userAnswer ?? '']) :
                    [];
                const suggestion = feedback ? (feedback.suggestion || '') : '';
                const explanation = feedback && feedback.explanation ? feedback.explanation : (question.explanation || '');

                const stateBadge = feedback ?
                    `<span class="badge ${isCorrect ? 'bg-success' : 'bg-danger'} px-3 py-2">
                        <i class="bi ${isCorrect ? 'bi-check-circle-fill' : 'bi-x-circle-fill'} me-1"></i>
                        ${isCorrect ? 'Correct' : 'Incorrect'}
                       </span>` :
                    '';
                const typeBadge = `<span class="badge" style="background-color: var(--sa-primary); color: white; text-transform: uppercase;">${type.replace('_', ' ')}</span>`;

                // Build options display for MCQ/Checkbox/TrueFalse
                let optionsHtml = '';
                if (question.options && Array.isArray(question.options) && question.options.length > 0) {
                    optionsHtml = '<div class="mt-3"><p class="text-muted mb-2 small fw-semibold">Available Options:</p><div class="list-group">';
                    question.options.forEach((option, optIndex) => {
                        const isCorrectOpt = correctAnswer.includes(option);
                        const isUserSelected = userAnswer.includes(option);
                        let optionClass = 'list-group-item';
                        let icon = '';

                        if (isCorrectOpt && isUserSelected) {
                            optionClass += ' bg-success bg-opacity-10 border-success border-2';
                            icon = '<i class="bi bi-check-circle-fill text-success me-2"></i>';
                        } else if (isCorrectOpt && !isUserSelected) {
                            optionClass += ' bg-light border-2';
                            icon = '<i class="bi bi-circle text-muted me-2"></i>';
                        } else if (isUserSelected && !isCorrectOpt) {
                            optionClass += ' bg-danger bg-opacity-10 border-danger border-2';
                            icon = '<i class="bi bi-x-circle-fill text-danger me-2"></i>';
                        } else {
                            optionClass += ' bg-light';
                            icon = '<i class="bi bi-circle text-muted me-2"></i>';
                        }

                        optionsHtml += `<div class="${optionClass} d-flex align-items-center">
                            ${icon}
                            <span>${option}</span>
                            ${isCorrectOpt ? '<span class="badge bg-success ms-auto">Correct</span>' : ''}
                        </div>`;
                    });
                    optionsHtml += '</div></div>';
                }

                // Build answer comparison section
                let answerComparisonHtml = '';
                if (type === 'multiple_choice' || type === 'true_false' || type === 'checkbox') {
                    // For MCQ/Checkbox, options are shown above, so just show summary
                    answerComparisonHtml = `
                        <div class="row g-3 mt-3">
                            <div class="col-12 col-md-6">
                                <div class="answer-panel ${isCorrect ? 'success' : 'danger'}">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-person-fill me-2" style="color: var(--sa-primary);"></i>
                                        <p class="text-muted mb-0 small fw-semibold">Your Answer</p>
                        </div>
                                    <p class="mb-0 fw-semibold">${userAnswer.length > 0 ? userAnswer.join(', ') : 'Not answered'}</p>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="answer-panel success">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        <p class="text-muted mb-0 small fw-semibold">Correct Answer</p>
                                    </div>
                                    <p class="mb-0 fw-semibold">${correctAnswer.join(', ')}</p>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    // For short/long answer, show side by side comparison
                    answerComparisonHtml = `
                        <div class="row g-3 mt-3">
                            <div class="col-12 col-md-6">
                                <div class="answer-panel ${isCorrect ? 'success' : 'danger'}">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-person-fill me-2" style="color: var(--sa-primary);"></i>
                                        <p class="text-muted mb-0 small fw-semibold">Your Answer</p>
                                    </div>
                                    <p class="mb-0" style="white-space: pre-wrap;">${userAnswer.length > 0 ? userAnswer.filter(a => a !== null && a !== undefined && a !== '').join('\n') : 'Not answered'}</p>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-6">
                                <div class="answer-panel success">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        <p class="text-muted mb-0 small fw-semibold">Expected Answer</p>
                                    </div>
                                    <p class="mb-0" style="white-space: pre-wrap;">${correctAnswer.join('\n')}</p>
                                    ${suggestion ? `
                                        <div class="mt-3 pt-3 border-top">
                                            <p class="mb-0" style="white-space: pre-wrap; color: #495057;">${suggestion}</p>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                }

                return `
                    <div class="review-question-card mb-4 ${isCorrect ? 'border-success' : 'border-danger'}" style="border-left: 4px solid ${isCorrect ? '#28a745' : '#dc3545'};">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge px-3 py-2" style="background-color: var(--sa-primary); color: white; font-size: 0.9rem;">Question ${index + 1}</span>
                                ${typeBadge}
                            </div>
                            ${stateBadge}
                        </div>
                        
                        <div class="question-text mb-3 p-3 bg-light rounded">
                            <p class="mb-0 fw-semibold" style="font-size: 1.05rem; line-height: 1.6;">${question.question || ''}</p>
                        </div>
                        
                        ${optionsHtml}
                        ${answerComparisonHtml}
                        
                        ${explanation ? `
                            <div class="mt-3 p-3" style="background-color: var(--sa-accent); border-left: 3px solid var(--sa-primary); border-radius: 8px;">
                                <div class="d-flex align-items-start mb-2">
                                    <i class="bi bi-info-circle-fill me-2 mt-1" style="color: var(--sa-primary);"></i>
                                    <p class="text-muted mb-0 small fw-semibold">Explanation</p>
                                </div>
                                <p class="mb-0" style="white-space: pre-wrap;">${explanation}</p>
                            </div>
                        ` : ''}
                    </div>
                `;
            }

            function formatDateTime(value) {
                if (!value) return '-';
                const parsed = new Date(value);
                if (Number.isNaN(parsed.getTime())) {
                    return value;
                }
                return parsed.toLocaleString();
            }

            async function showReviewModal(quizId, previewOnly = false) {
                try {
                    const endpoint = previewOnly ? '<?= VIEW_QUIZ_ROUTE ?>' : '<?= VIEW_QUIZ_ATTEMPT ?>';
                    const formData = new FormData();
                    formData.append('quiz_id', quizId);
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        body: formData
                    });
                    const data = await response.json();
                    if (!data.success) {
                        showSnackbar(data.message || 'Unable to load quiz details. Please try again.', 'error');
                        return;
                    }
                    const questions = data.quiz || [];
                    const feedback = previewOnly ? [] : (data.attempt?.feedback || []);
                    const answers = previewOnly ? [] : (data.attempt?.answers || []);
                    let html = '';
                    if (!questions.length) {
                        html = '<p class="text-muted">No questions available.</p>';
                    } else {
                        questions.forEach((question, index) => {
                            const feedbackEntry = feedback[index] ?? null;
                            // Ensure userAnswer is set from answers array if not in feedback
                            if (feedbackEntry) {
                                if (!feedbackEntry.userAnswer && answers[index] !== undefined) {
                                    feedbackEntry.userAnswer = answers[index];
                                }
                                // Ensure userAnswer is an array for consistency
                                if (feedbackEntry.userAnswer !== undefined && !Array.isArray(feedbackEntry.userAnswer)) {
                                    feedbackEntry.userAnswer = [feedbackEntry.userAnswer];
                                }
                            } else if (answers[index] !== undefined) {
                                // Create feedback entry if it doesn't exist but answer does
                                const tempFeedback = {
                                    userAnswer: Array.isArray(answers[index]) ? answers[index] : [answers[index]],
                                    isCorrect: false,
                                    suggestion: ''
                                };
                                html += buildReviewCard(question, tempFeedback, index);
                                return;
                            }
                            html += buildReviewCard(question, previewOnly ? null : feedbackEntry, index);
                        });
                    }
                    document.getElementById('reviewQuizBody').innerHTML = html;

                    if (!previewOnly && data.attempt) {
                        const attempt = data.attempt;
                        reviewSummary.classList.remove('d-none');
                        reviewSummary.innerHTML = `
                            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1">${attempt.examMode ? 'Exam Mode Attempt' : 'Practice Attempt'}</p>
                                    <h3 class="mb-0" style="color: var(--sa-primary);">${attempt.score !== null ? attempt.score + '%' : '—'}</h3>
                        </div>
                                <div class="text-end">
                                    <p class="text-muted mb-1">Completed</p>
                                    <p class="mb-0">${formatDateTime(attempt.createdAt)}</p>
                                </div>
                            </div>
                            <div class="mt-3 d-flex gap-2 flex-wrap align-items-center">
                                <span class="badge" style="background-color: var(--sa-accent); color: var(--sa-primary-dark);">Questions: ${questions.length}</span>
                                <span class="badge" style="background-color: var(--sa-primary); color: white;">
                                    ${attempt.examMode ? 'Exam Mode' : 'Practice'}
                                </span>
                        </div>
                    `;
                    } else {
                        reviewSummary.classList.add('d-none');
                        reviewSummary.innerHTML = '';
                    }

                    reviewQuizModal.show();
                } catch (error) {
                    showSnackbar('An error occurred while loading quiz data. Please try again.', 'error');
                    console.error('Error loading quiz data:', error);
                }
            }

            // Quiz sorting functionality
            function sortQuizzes(sortType = 'latest') {
                const quizList = document.getElementById('quizList');
                if (!quizList) return;
                const items = Array.from(quizList.querySelectorAll('.quiz-list-item'));

                items.sort((a, b) => {
                    switch (sortType) {
                        case 'asc':
                            return (a.dataset.title || '').localeCompare(b.dataset.title || '');
                        case 'desc':
                            return (b.dataset.title || '').localeCompare(a.dataset.title || '');
                        case 'latest':
                            return (parseInt(b.dataset.created) || 0) - (parseInt(a.dataset.created) || 0);
                        case 'oldest':
                            return (parseInt(a.dataset.created) || 0) - (parseInt(b.dataset.created) || 0);
                        default:
                            return 0;
                    }
                });

                items.forEach(item => quizList.appendChild(item));
            }

            const quizSortOptions = document.querySelectorAll('.quiz-sort-option');
            const currentQuizSortLabel = document.getElementById('currentQuizSortLabel');

            quizSortOptions.forEach(option => {
                option.addEventListener('click', (e) => {
                    e.preventDefault();
                    const sortType = option.dataset.sort;
                    if (!sortType) return;
                    sortQuizzes(sortType);
                    if (currentQuizSortLabel) {
                        currentQuizSortLabel.textContent = `Sorted by: ${option.textContent.trim()}`;
                    }
                });
            });

            // Initial sort
            sortQuizzes('latest');

            document.addEventListener('click', (event) => {
                const startBtn = event.target.closest('.start-quiz-btn');
                if (startBtn) {
                    if (startBtn.disabled) return;
                    persistCurrentAnswer();
                    const quizTitle = startBtn.closest('tr')?.querySelector('.quiz-title')?.textContent?.trim() || 'this quiz';
                    showConfirmModal({
                        message: 'Are you sure you want to start the quiz "' + quizTitle + '"? Once you begin, you\'ll need to complete all questions before submitting.',
                        title: 'Start Quiz',
                        confirmText: 'Start',
                        cancelText: 'Cancel',
                        danger: false,
                        onConfirm: () => {
                            launchQuiz(startBtn.dataset.quizId);
                        }
                    });
                    return;
                }
                const reviewBtn = event.target.closest('.review-quiz-btn');
                if (reviewBtn) {
                    event.preventDefault();
                    const quizId = reviewBtn.dataset.quizId;
                    if (!quizId || reviewBtn.style.pointerEvents === 'none') return;
                    showReviewModal(quizId, false);
                    return;
                }
                const deleteBtn = event.target.closest('.delete-quiz-btn');
                if (deleteBtn) {
                    event.preventDefault();
                    const quizId = deleteBtn.dataset.quizId;
                    const fileId = deleteBtn.dataset.fileId;
                    const quizTitle = deleteBtn.closest('tr')?.querySelector('.quiz-title')?.textContent?.trim() || 'this quiz';
                    if (!quizId || !fileId) return;
                    showConfirmModal({
                        message: 'Are you sure you want to delete the quiz "' + quizTitle + '"? This action cannot be undone.',
                        title: 'Delete Quiz',
                        confirmText: 'Delete',
                        cancelText: 'Cancel',
                        danger: true,
                        onConfirm: async () => {
                            try {
                                const formData = new FormData();
                                formData.append('quiz_id', quizId);
                                formData.append('file_id', fileId);
                                const response = await fetch('<?= DELETE_QUIZ ?>', {
                                    method: 'POST',
                                    body: formData
                                });
                                const data = await response.json();
                                if (data.success) {
                                    showSnackbar('Quiz deleted successfully!', 'success');
                                    setTimeout(() => {
                                        location.reload();
                                    }, 1000);
                                } else {
                                    showSnackbar(data.message || 'Failed to delete quiz. Please try again.', 'error');
                                }
                            } catch (error) {
                                showSnackbar('An error occurred while deleting the quiz. Please try again.', 'error');
                                console.error('Error deleting quiz:', error);
                            }
                        }
                    });
                    return;
                }
            });

            quizPrevBtn.addEventListener('click', () => {
                persistCurrentAnswer();
                if (currentQuestionIndex > 0) {
                    currentQuestionIndex--;
                    renderActiveQuestion();
                }
            });

            quizNextBtn.addEventListener('click', () => {
                persistCurrentAnswer();
                if (currentQuestionIndex < activeQuizQuestions.length - 1) {
                    currentQuestionIndex++;
                    renderActiveQuestion();
                }
            });

            quizSubmitBtn.addEventListener('click', () => {
                persistCurrentAnswer();
                submitActiveQuiz();
            });

            if (quizCloseAfterSubmit) {
                quizCloseAfterSubmit.addEventListener('click', () => {
                    takeQuizModal.hide();
                });
            }

            if (quizEvaluationCloseBtn) {
                quizEvaluationCloseBtn.addEventListener('click', () => {
                    takeQuizModal.hide();
                });
            }

            takeQuizModalEl.addEventListener('hide.bs.modal', (event) => {
                if (quizSessionActive && !quizFinished) {
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    quizSessionActive = false;
                }
            });
        });
    </script>
    <?php include VIEW_CONFIRM; ?>
</body>

</html>