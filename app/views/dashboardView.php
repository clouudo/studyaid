<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - StudyAids</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .upload-container {
            background-color: #f8f9fa;
            padding: 30px;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.06);
        }

        .card-click {
            cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .card-click:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 16px rgba(0, 0, 0, 0.08);
        }

        .card-hover-theme {
            transition: all .3s ease;
            background-color: #ffffff;
        }

        .card-hover-theme:hover {
            background-color: #d4b5ff !important;
            border: 2px dashed #5a32a3 !important;
            box-shadow: 0 10px 20px rgba(90, 50, 163, 0.3);
            transform: translateY(-2px);
        }

        .card-hover-theme:hover .card-title-theme {
            color: #5a32a3 !important;
        }

        .card-hover-theme:hover .card-text {
            color: #ffffff !important;
        }

        .card-hover-theme:hover .bi {
            color: #5a32a3 !important;
        }

        .stat-title {
            color: #6c757d;
            font-weight: 600;
            font-size: .9rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
        }

        .stat-badge {
            background: #6f42c1;
            color: #fff;
            font-size: .75rem;
            padding: 4px 10px;
            border-radius: 999px;
        }

        .btn-purple {
            background-color: #6f42c1;
            border-color: #6f42c1;
        }

        .btn-purple:hover {
            background-color: #5a32a3;
            border-color: #5a32a3;
        }

        .card-title-theme {
            color: #6f42c1;
        }

        .btn-icon {
            background: transparent;
            border: none;
            color: #6c757d;
        }

        .btn-icon:hover {
            color: #6f42c1;
        }

        .dropdown-menu {
            min-width: 8rem;
        }

        .card-text {
            color: #6c757d;
        }

        .card-text:hover {
            color: #ffffff;
        }

        .welcome-message {
            background-color: #e7d5ff;
            padding: 15px 25px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }

        .welcome-message h2 {
            color: #6b42c2;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1.5rem;
        }

        .welcome-message p {
            color: #5b4782;
            font-size: 0.95rem;
            margin: 0;
        }

        /* Table theme colors */
        .report-table thead tr {
            background-color: #d4b5ff !important;
        }

        .report-table thead th {
            background-color: #d4b5ff !important;
            color: #5a32a3 !important;
            font-weight: 600;
            border: none;
        }

        .report-table tbody tr{
            background-color: #e7d5ff !important;
        }
        .report-table tbody td {
            border: none;
            color: #212529;
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
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        @media print {
            body * {
                visibility: hidden;
            }

            #reportModal.show,
            #reportModal.show * {
                visibility: visible;
            }

            #reportModal .modal-dialog {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                margin: 0;
            }

            #reportModal .modal-content {
                border: none;
                box-shadow: none;
            }

            #reportModal .modal-footer {
                display: none;
            }
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
    <div class="d-flex flex-grow-1">
        <?php include VIEW_SIDEBAR; ?>
        <main class="flex-grow-1 p-3">
            <div class="container-fluid upload-container">
                <h3 class="mb-4" style="color: #212529; font-size: 1.5rem; font-weight: 600;">Dashboard</h3>
                <?php if (isset($user) && isset($user['username'])): ?>
                    <div class="welcome-message pb-4">
                        <h2 class="mb-0" style="color: #6f42c1;">👋 Welcome back, <?= htmlspecialchars($user['username']) ?>!</h2>
                        <p class="mb-0" style="color: #495057;">Access your courses, start learning, and enjoy your journey.</p>
                    </div>
                <?php endif; ?>




        <div class="row g-4 mb-4">
            <!-- Box 1: Quantities -->
            <div class="col-lg-6">
                <div class="card p-4 h-100 card-click" id="activityCard" title="Click to view report">
                    <div class="d-flex align-items-center mb-4">
                        <div class="me-3">
                            <i class="bi bi-activity" style="font-size: 2rem; color: #6f42c1;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-0" style="color: #6f42c1; font-weight: 600;">Your Activity</h5>
                            <small class="text-muted">Overview of your learning materials</small>
                        </div>
                        <span class="stat-badge">Overview</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="p-3 bg-light rounded">
                                <div class="stat-title"><i class="bi bi-journal-text me-2" style="font-size: 1.2rem; color: #6f42c1;"></i>Uploaded Documents</div>
                                <div class="stat-value"><?= isset($documentsCount) ? (int)$documentsCount : 0 ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="stat-title"><i class="bi bi-file-text me-2" style="font-size: 1.2rem; color: #6f42c1;"></i>Generated Notes</div>
                                <div class="stat-value"><?= isset($notesCount) ? (int)$notesCount : 0 ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="stat-title"><i class="bi bi-file-earmark-text me-2" style="font-size: 1.2rem; color: #6f42c1;"></i>Generated Summaries</div>
                                <div class="stat-value"><?= isset($summariesCount) ? (int)$summariesCount : 0 ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="stat-title"><i class="bi bi-card-text me-2" style="font-size: 1.2rem; color: #6f42c1;"></i>Generated Flashcards</div>
                                <div class="stat-value"><?= isset($flashcardsCount) ? (int)$flashcardsCount : 0 ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="stat-title"><i class="bi bi-diagram-3 me-2" style="font-size: 1.2rem; color: #6f42c1;"></i>Generated Mindmaps</div>
                                <div class="stat-value"><?= isset($mindmapsCount) ? (int)$mindmapsCount : 0 ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Box 2: Line chart for MCQ quiz score history -->
            <div class="col-lg-6">
                <!-- Quiz Statistics Chart and Report -->
                <div class="card h-100" id="quizStatsCard">
                    <div class="card-header bg-white border-bottom p-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-graph-up-arrow" style="font-size: 2rem; color: #6f42c1;"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1" style="color: #6f42c1; font-weight: 600;">Quiz Performance Analytics</h5>
                                <small class="text-muted">Track your progress across practice and exam mode quizzes</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-12">
                                <canvas id="quizPerformanceChart" height="80"></canvas>
                            </div>
                        </div>
                        <div class="row g-4 mt-2" id="quizReportSection">
                            <!-- Report will be populated here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Box 3: Navigate to newDocument.php -->
            <div class="col-lg-6">
                <a href="<?= NEW_DOCUMENT ?>" class="text-decoration-none">
                    <div class="card p-4 h-100 card-click card-hover-theme">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-file-earmark-plus-fill" style="font-size: 2rem; color: #6f42c1;"></i>
                            </div>
                            <div>
                                <h5 class="mb-1 card-title-theme">Create New Document</h5>
                                <p class="mb-0 card-text">Upload or create a new learning document</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Box 4: Navigate to latest document -->
            <div class="col-lg-6">
                <div class="card p-4 h-100 card-click card-hover-theme" onclick="goToLatestDocument()">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-clock-history" style="font-size: 2rem; color: #6f42c1;"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 card-title-theme">Latest Document</h5>
                            <p class="mb-0 card-text"><?= htmlspecialchars(isset($latestDocumentName) ? $latestDocumentName : 'No documents yet') ?></p>
                        </div>
                    </div>
                </div>
                <form id="latestDocForm" action="<?= DISPLAY_DOCUMENT ?>" method="post" class="d-none">
                    <input type="hidden" name="file_id" value="<?= isset($latestDocumentId) ? (int)$latestDocumentId : 0 ?>">
                </form>
            </div>
        </div>
        </main>
    </div>
    </div>
    </div>

    <!-- Snackbar Container -->
    <div id="snackbar" class="snackbar">
        <i class="snackbar-icon" id="snackbarIcon"></i>
        <span class="snackbar-message" id="snackbarMessage"></span>
    </div>

    <!-- Report Modal (first box) -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #f6efff, #ffffff); border-bottom: 2px solid #d4b5ff;">
                    <div class="d-flex align-items-center flex-grow-1">
                        <i class="bi bi-file-earmark-text me-3" style="font-size: 1.5rem; color: #6f42c1;"></i>
                        <div>
                            <h5 class="modal-title mb-0" id="reportModalLabel" style="color: #6f42c1; font-weight: 600;">StudyAid - Activity Report</h5>
                            <small class="text-muted"><?= isset($user) && isset($user['username']) ? htmlspecialchars($user['username']) : 'User' ?> • <?= date('F j, Y \a\t g:i A') ?></small>
                        </div>
                    </div>
                    <button type="button" class="btn-back" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x"></i></button>
                </div>
                <div class="modal-body" style="background-color: #f8f9fa;">
                    <div class="mb-4">
                        <h6 style="color: #6f42c1; font-weight: 600; margin-bottom: 1rem;">
                            <i class="bi bi-journal-text me-2"></i>Learning Materials Overview
                        </h6>
                        <div class="table-responsive">
                            <table class="table align-middle report-table mb-0" id="reportTable">
                            <thead>
                                <tr>
                                    <th style="width: 60%;">Metric</th>
                                    <th class="text-end">Quantity</th>
                                </tr>
                            </thead>
                            <tbody id="reportTableBody">
                                <tr>
                                    <td><i class="bi bi-journal-text me-2"></i>Uploaded Documents</td>
                                    <td class="text-end"><?= isset($documentsCount) ? (int)$documentsCount : 0 ?></td>
                                </tr>
                                <tr>
                                    <td><i class="bi bi-file-text me-2"></i>Generated Notes</td>
                                    <td class="text-end"><?= isset($notesCount) ? (int)$notesCount : 0 ?></td>
                                </tr>
                                <tr>
                                    <td><i class="bi bi-file-earmark-text me-2"></i>Generated Summaries</td>
                                    <td class="text-end"><?= isset($summariesCount) ? (int)$summariesCount : 0 ?></td>
                                </tr>
                                <tr>
                                    <td><i class="bi bi-card-text me-2"></i>Generated Flashcards</td>
                                    <td class="text-end"><?= isset($flashcardsCount) ? (int)$flashcardsCount : 0 ?></td>
                                </tr>
                                <tr>
                                    <td><i class="bi bi-diagram-3 me-2"></i>Generated Mindmaps</td>
                                    <td class="text-end"><?= isset($mindmapsCount) ? (int)$mindmapsCount : 0 ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    </div>
                    <div id="quizPerformanceSection" class="mt-4" style="display: none;">
                        <h6 style="color: #6f42c1; font-weight: 600; margin-bottom: 1rem;">
                            <i class="bi bi-graph-up-arrow me-2"></i>Quiz Performance Analysis
                        </h6>
                        <div class="table-responsive">
                            <table class="table align-middle report-table mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 60%;">Performance Metric</th>
                                        <th class="text-end">Value</th>
                                    </tr>
                                </thead>
                                <tbody id="quizStatsTableBody">
                                    <tr>
                                        <td colspan="2" class="text-center py-3">
                                            <small class="text-muted">Loading quiz statistics...</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background-color: #f8f9fa; border-top: 2px solid #d4b5ff;">
                    <button type="button" class="btn btn-purple text-white" id="downloadReportBtn"><i class="bi bi-download me-2"></i>Download PDF</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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

        // Confirmation dialog function
        function showConfirmation(message, onConfirm, onCancel = null) {
            if (confirm(message)) {
                if (onConfirm) onConfirm();
            } else {
                if (onCancel) onCancel();
            }
        }

        function goToLatestDocument() {
            var latestId = <?= isset($latestDocumentId) ? (int)$latestDocumentId : 0 ?>;
            if (latestId > 0) {
                document.getElementById('latestDocForm').submit();
            } else {
                showSnackbar('No documents available yet. Please upload a document first.', 'error');
            }
        }

        (function() {
            // First box: open report modal
            const activityCard = document.getElementById('activityCard');
            const reportModalEl = document.getElementById('reportModal');
            const reportModal = reportModalEl ? new bootstrap.Modal(reportModalEl) : null;
            if (activityCard && reportModal) {
                activityCard.addEventListener('click', () => reportModal.show());
            }
            // Download report as PDF
            const downloadBtn = document.getElementById('downloadReportBtn');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', () => {
                    showConfirmation(
                        'Are you sure you want to download the activity report as PDF?',
                        () => {
                            try {
                                const { jsPDF } = window.jspdf;
                    const doc = new jsPDF();
                    
                    // Set theme colors
                    const headerColor = [212, 181, 255]; // #d4b5ff
                    const rowColor1 = [231, 213, 255]; // #e7d5ff
                    const rowColor2 = [255, 255, 255]; // white
                    const textColor = [90, 50, 163]; // #5a32a3
                    const accentColor = [111, 66, 193]; // #6f42c1
                    
                    let yPos = 20;
                    const pageWidth = 210;
                    const margin = 14;
                    const contentWidth = pageWidth - (margin * 2);
                    
                    // Title Section
                    doc.setFontSize(20);
                    doc.setTextColor(accentColor[0], accentColor[1], accentColor[2]);
                    doc.setFont(undefined, 'bold');
                    doc.text('StudyAid - Activity Report', margin, yPos);
                    
                    yPos += 8;
                    const username = '<?= isset($user) && isset($user["username"]) ? htmlspecialchars($user["username"], ENT_QUOTES) : "User" ?>';
                    doc.setFontSize(12);
                    doc.setTextColor(100, 100, 100);
                    doc.setFont(undefined, 'normal');
                    doc.text('User: ' + username, margin, yPos);
                    
                    yPos += 6;
                    const date = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                    const time = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                    doc.setFontSize(10);
                    doc.text('Generated on: ' + date + ' at ' + time, margin, yPos);
                    
                    yPos += 12;
                    
                    // Section 1: Learning Materials Overview
                    doc.setFontSize(14);
                    doc.setTextColor(accentColor[0], accentColor[1], accentColor[2]);
                    doc.setFont(undefined, 'bold');
                    doc.text('Learning Materials Overview', margin, yPos);
                    
                    yPos += 8;
                    doc.setFillColor(headerColor[0], headerColor[1], headerColor[2]);
                    doc.setTextColor(textColor[0], textColor[1], textColor[2]);
                    doc.rect(margin, yPos - 5, contentWidth, 8, 'F');
                    doc.setFontSize(11);
                    doc.setFont(undefined, 'bold');
                    doc.text('Activity Type', margin + 2, yPos);
                    doc.text('Count', margin + contentWidth - 2, yPos, { align: 'right' });
                    
                    yPos += 6;
                    doc.setFont(undefined, 'normal');
                    doc.setFontSize(10);
                    doc.setTextColor(0, 0, 0);
                    
                    const table = document.getElementById('reportTable');
                    const rows = table.querySelectorAll('tbody tr');
                    let rowIndex = 0;
                    
                    rows.forEach((row, index) => {
                        // Skip quiz section header row
                        if (row.classList.contains('table-group-divider')) {
                            return;
                        }
                        
                        const cells = row.querySelectorAll('td');
                        if (cells.length < 2) return;
                        
                        let metric = cells[0].textContent.trim();
                        // Remove icon text if present
                        metric = metric.replace(/^\S+\s+/, '');
                        const quantity = cells[1].textContent.trim();
                        
                        // Check if we need a new page
                        if (yPos > 270) {
                            doc.addPage();
                            yPos = 20;
                        }
                        
                        // Alternate row colors
                        if (rowIndex % 2 === 0) {
                            doc.setFillColor(rowColor1[0], rowColor1[1], rowColor1[2]);
                        } else {
                            doc.setFillColor(rowColor2[0], rowColor2[1], rowColor2[2]);
                        }
                        doc.rect(margin, yPos - 5, contentWidth, 7, 'F');
                        
                        doc.text(metric, margin + 2, yPos);
                        doc.text(quantity, margin + contentWidth - 2, yPos, { align: 'right' });
                        
                        yPos += 7;
                        rowIndex++;
                    });
                    
                    yPos += 8;
                    
                    // Section 2: Quiz Performance Analysis
                    if (quizStatisticsData && quizStatisticsData.statistics) {
                        const stats = quizStatisticsData.statistics;
                        const totalQuizzes = parseInt(stats.totalQuizzes) || 0;
                        
                        if (totalQuizzes > 0) {
                            // Check if we need a new page
                            if (yPos > 250) {
                                doc.addPage();
                                yPos = 20;
                            }
                            
                            doc.setFontSize(14);
                            doc.setTextColor(accentColor[0], accentColor[1], accentColor[2]);
                            doc.setFont(undefined, 'bold');
                            doc.text('Quiz Performance Analysis', margin, yPos);
                            
                            yPos += 8;
                            doc.setFillColor(headerColor[0], headerColor[1], headerColor[2]);
                            doc.setTextColor(textColor[0], textColor[1], textColor[2]);
                            doc.rect(margin, yPos - 5, contentWidth, 8, 'F');
                            doc.setFontSize(11);
                            doc.setFont(undefined, 'bold');
                            doc.text('Performance Metric', margin + 2, yPos);
                            doc.text('Value', margin + contentWidth - 2, yPos, { align: 'right' });
                            
                            yPos += 6;
                            doc.setFont(undefined, 'normal');
                            doc.setFontSize(10);
                            doc.setTextColor(0, 0, 0);
                            
                            const quizMetrics = [
                                ['Total Quizzes Completed', totalQuizzes],
                                ['Exam Mode Quizzes', parseInt(stats.examQuizzes) || 0],
                                ['Practice Mode Quizzes', parseInt(stats.practiceQuizzes) || 0],
                                ['Overall Average Score', parseFloat(stats.avgScore) > 0 ? parseFloat(stats.avgScore).toFixed(1) + '%' : 'N/A'],
                                ['Exam Mode Average', parseFloat(stats.avgExamScore) > 0 ? parseFloat(stats.avgExamScore).toFixed(1) + '%' : 'N/A'],
                                ['Practice Mode Average', parseFloat(stats.avgPracticeScore) > 0 ? parseFloat(stats.avgPracticeScore).toFixed(1) + '%' : 'N/A'],
                                ['Highest Score', parseFloat(stats.maxScore) > 0 ? parseFloat(stats.maxScore).toFixed(1) + '%' : 'N/A'],
                                ['Lowest Score', (stats.minScore !== null && stats.minScore !== undefined) ? parseFloat(stats.minScore).toFixed(1) + '%' : 'N/A']
                            ];
                            
                            quizMetrics.forEach((metric, index) => {
                                if (yPos > 270) {
                                    doc.addPage();
                                    yPos = 20;
                                }
                                
                                if (index % 2 === 0) {
                                    doc.setFillColor(rowColor1[0], rowColor1[1], rowColor1[2]);
                                } else {
                                    doc.setFillColor(rowColor2[0], rowColor2[1], rowColor2[2]);
                                }
                                doc.rect(margin, yPos - 5, contentWidth, 7, 'F');
                                
                                doc.text(metric[0], margin + 2, yPos);
                                doc.text(String(metric[1]), margin + contentWidth - 2, yPos, { align: 'right' });
                                
                                yPos += 7;
                            });
                            
                            // Add date range if quizzes exist
                            if (quizStatisticsData.quizzes && quizStatisticsData.quizzes.length > 0) {
                                yPos += 5;
                                const quizzes = quizStatisticsData.quizzes;
                                const dates = quizzes.map(q => new Date(q.markAt || q.createdAt || q.attemptDate)).filter(d => !isNaN(d.getTime()));
                                
                                if (dates.length > 0) {
                                    dates.sort((a, b) => a - b);
                                    const earliest = dates[0];
                                    const latest = dates[dates.length - 1];
                                    
                                    if (yPos > 270) {
                                        doc.addPage();
                                        yPos = 20;
                                    }
                                    
                                    doc.setFontSize(10);
                                    doc.setTextColor(100, 100, 100);
                                    doc.text('Activity Period: ' + earliest.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + 
                                            ' to ' + latest.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }), margin, yPos);
                                }
                            }
                        }
                    }
                    
                    yPos += 15;
                    
                    // Section 3: Progress Summary
                    if (yPos > 250) {
                        doc.addPage();
                        yPos = 20;
                    }
                    
                    doc.setFontSize(14);
                    doc.setTextColor(accentColor[0], accentColor[1], accentColor[2]);
                    doc.setFont(undefined, 'bold');
                    doc.text('Progress Summary', margin, yPos);
                    
                    yPos += 8;
                    doc.setFontSize(10);
                    doc.setTextColor(0, 0, 0);
                    doc.setFont(undefined, 'normal');
                    
                    const totalDocs = <?= isset($documentsCount) ? (int)$documentsCount : 0 ?>;
                    const totalNotes = <?= isset($notesCount) ? (int)$notesCount : 0 ?>;
                    const totalSummaries = <?= isset($summariesCount) ? (int)$summariesCount : 0 ?>;
                    const totalFlashcards = <?= isset($flashcardsCount) ? (int)$flashcardsCount : 0 ?>;
                    const totalMindmaps = <?= isset($mindmapsCount) ? (int)$mindmapsCount : 0 ?>;
                    const totalActivities = totalDocs + totalNotes + totalSummaries + totalFlashcards + totalMindmaps;
                    
                    doc.text('Total Learning Activities: ' + totalActivities, margin, yPos);
                    yPos += 6;
                    doc.text('Documents Uploaded: ' + totalDocs, margin, yPos);
                    yPos += 6;
                    doc.text('Study Materials Generated: ' + (totalNotes + totalSummaries + totalFlashcards + totalMindmaps), margin, yPos);
                    
                    if (quizStatisticsData && quizStatisticsData.statistics) {
                        const totalQuizzes = parseInt(quizStatisticsData.statistics.totalQuizzes) || 0;
                        if (totalQuizzes > 0) {
                            yPos += 6;
                            doc.text('Quizzes Completed: ' + totalQuizzes, margin, yPos);
                        }
                    }
                    
                    // Footer on last page
                    const totalPages = doc.internal.getNumberOfPages();
                    for (let i = 1; i <= totalPages; i++) {
                        doc.setPage(i);
                        doc.setFontSize(8);
                        doc.setTextColor(128, 128, 128);
                        doc.text('Page ' + i + ' of ' + totalPages, pageWidth / 2, 290, { align: 'center' });
                        doc.text('StudyAid Learning Platform', margin, 290);
                        doc.text('Generated: ' + date + ' ' + time, pageWidth - margin, 290, { align: 'right' });
                    }
                    
                                // Save PDF
                                const filename = 'StudyAid_Activity_Report_' + username.replace(/\s+/g, '_') + '_' + new Date().toISOString().split('T')[0] + '.pdf';
                                doc.save(filename);
                                showSnackbar('Activity report downloaded successfully!', 'success');
                            } catch (error) {
                                console.error('Error generating PDF:', error);
                                showSnackbar('Failed to generate PDF. Please try again.', 'error');
                            }
                        },
                        () => {
                            // User cancelled
                        }
                    );
                });
            }

            // Load and render quiz statistics chart and report
            let quizChart = null;
            let quizStatisticsData = null; // Store for PDF generation
            
            async function loadQuizStatistics() {
                try {
                    const url = '<?= GET_QUIZ_STATISTICS ?>';
                    const response = await fetch(url);
                    const result = await response.json();
                    
                    if (result.success && result.data) {
                        quizStatisticsData = result.data;
                        renderQuizChart(result.data);
                        renderQuizReport(result.data);
                        updateReportModal(result.data);
                    } else {
                        document.getElementById('quizStatsCard').style.display = 'none';
                        updateReportModal(null);
                    }
                } catch (error) {
                    console.error('Error loading quiz statistics:', error);
                    document.getElementById('quizStatsCard').style.display = 'none';
                    updateReportModal(null);
                }
            }

            function updateReportModal(data) {
                const quizStatsTableBody = document.getElementById('quizStatsTableBody');
                const quizPerformanceSection = document.getElementById('quizPerformanceSection');
                
                if (!quizStatsTableBody || !quizPerformanceSection) return;

                if (!data || !data.statistics) {
                    quizStatsTableBody.innerHTML = '<tr><td colspan="2" class="text-center py-2"><small class="text-muted">No quiz data available</small></td></tr>';
                    quizPerformanceSection.style.display = 'none';
                    return;
                }

                const stats = data.statistics;
                const totalQuizzes = parseInt(stats.totalQuizzes) || 0;
                const examQuizzes = parseInt(stats.examQuizzes) || 0;
                const practiceQuizzes = parseInt(stats.practiceQuizzes) || 0;
                const avgScore = parseFloat(stats.avgScore) || 0;
                const avgExamScore = parseFloat(stats.avgExamScore) || 0;
                const avgPracticeScore = parseFloat(stats.avgPracticeScore) || 0;
                const maxScore = parseFloat(stats.maxScore) || 0;
                const minScore = parseFloat(stats.minScore) !== null && parseFloat(stats.minScore) !== undefined ? parseFloat(stats.minScore) : null;

                if (totalQuizzes === 0) {
                    quizStatsTableBody.innerHTML = '<tr><td colspan="2" class="text-center py-2"><small class="text-muted">No quizzes completed yet</small></td></tr>';
                    quizPerformanceSection.style.display = 'none';
                    return;
                }

                let html = '';
                html += `<tr><td><i class="bi bi-clipboard-check me-2"></i>Total Quizzes Completed</td><td class="text-end">${totalQuizzes}</td></tr>`;
                html += `<tr><td><i class="bi bi-shield-check me-2"></i>Exam Mode Quizzes</td><td class="text-end">${examQuizzes}</td></tr>`;
                html += `<tr><td><i class="bi bi-book me-2"></i>Practice Mode Quizzes</td><td class="text-end">${practiceQuizzes}</td></tr>`;
                html += `<tr><td><i class="bi bi-graph-up-arrow me-2"></i>Overall Average Score</td><td class="text-end">${avgScore > 0 ? avgScore.toFixed(1) + '%' : 'N/A'}</td></tr>`;
                html += `<tr><td><i class="bi bi-shield-check me-2"></i>Exam Mode Average</td><td class="text-end">${avgExamScore > 0 ? avgExamScore.toFixed(1) + '%' : 'N/A'}</td></tr>`;
                html += `<tr><td><i class="bi bi-book me-2"></i>Practice Mode Average</td><td class="text-end">${avgPracticeScore > 0 ? avgPracticeScore.toFixed(1) + '%' : 'N/A'}</td></tr>`;
                html += `<tr><td><i class="bi bi-arrow-up-circle me-2"></i>Highest Score</td><td class="text-end">${maxScore > 0 ? maxScore.toFixed(1) + '%' : 'N/A'}</td></tr>`;
                html += `<tr><td><i class="bi bi-arrow-down-circle me-2"></i>Lowest Score</td><td class="text-end">${(minScore !== null && minScore !== undefined) ? minScore.toFixed(1) + '%' : 'N/A'}</td></tr>`;
                
                quizStatsTableBody.innerHTML = html;
                quizPerformanceSection.style.display = 'block';
            }

            function renderQuizChart(data) {
                const ctx = document.getElementById('quizPerformanceChart');
                if (!ctx) return;

                const quizzes = data.quizzes || [];
                const stats = data.statistics || {};

                // Group quizzes by date and mode
                const practiceData = {};
                const examData = {};
                const dateMap = {};
                
                quizzes.forEach(quiz => {
                    const originalDate = new Date(quiz.markAt || quiz.createdAt);
                    const date = originalDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
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
                    return scores.length > 0 ? parseFloat((scores.reduce((a, b) => a + b, 0) / scores.length).toFixed(1)) : null;
                });

                const examScores = dates.map(date => {
                    const scores = examData[date] || [];
                    return scores.length > 0 ? parseFloat((scores.reduce((a, b) => a + b, 0) / scores.length).toFixed(1)) : null;
                });

                if (quizChart) {
                    quizChart.destroy();
                }

                quizChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: dates,
                        datasets: [
                            {
                                label: 'Practice Mode',
                                data: practiceScores,
                                borderColor: '#6f42c1',
                                backgroundColor: 'rgba(111, 66, 193, 0.1)',
                                tension: 0.4,
                                fill: true,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                pointBackgroundColor: '#6f42c1',
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
                                pointBorderWidth: 2,
                                spanGaps: true,
                                showLine: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        elements: {
                            line: {
                                spanGaps: true
                            }
                        },
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
                const recentAvg = recentQuizzes.length > 0 
                    ? recentQuizzes.reduce((sum, q) => sum + parseFloat(q.totalScore || q.attemptScore || 0), 0) / recentQuizzes.length 
                    : 0;
                const olderAvg = olderQuizzes.length > 0 
                    ? olderQuizzes.reduce((sum, q) => sum + parseFloat(q.totalScore || q.attemptScore || 0), 0) / olderQuizzes.length 
                    : 0;
                const improvement = recentAvg > 0 && olderAvg > 0 ? ((recentAvg - olderAvg) / olderAvg * 100).toFixed(1) : 0;

                reportSection.innerHTML = `
                    <div class="col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f6efff, #ffffff);">
                            <div class="card-body text-center">
                                <div class="mb-2">
                                    <i class="bi bi-clipboard-check" style="font-size: 2rem; color: #6f42c1;"></i>
                                </div>
                                <h3 class="mb-1" style="color: #6f42c1;">${totalQuizzes}</h3>
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
                                    <i class="bi bi-book" style="font-size: 2rem; color: #6f42c1;"></i>
                                </div>
                                <h3 class="mb-1" style="color: #6f42c1;">${practiceQuizzes}</h3>
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
                                <h6 class="mb-3" style="color: #6f42c1; font-weight: 600;">
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
                                            <strong style="color: #6f42c1;">${avgPracticeScore > 0 ? avgPracticeScore.toFixed(1) + '%' : 'N/A'}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted">Score Range:</span>
                                            <strong>${(minScore !== null && minScore !== undefined) ? minScore.toFixed(1) + '%' : 'N/A'} - ${(maxScore !== null && maxScore !== undefined && maxScore > 0) ? maxScore.toFixed(1) + '%' : 'N/A'}</strong>
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
        })();
    </script>
</body>

</html>