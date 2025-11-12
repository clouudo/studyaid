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




        <div class="row g-4">
            <!-- Box 1: Quantities -->
            <div class="col-lg-6">
                <div class="card p-4 h-100 card-click" id="activityCard" title="Click to view report">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Your Activity</h5>
                        <span class="stat-badge">Overview</span>
                    </div>
                    <div class="row g-3">

                        <div class="col-12">
                            <div class="p-3 bg-light rounded">

                                <div class="stat-title"><i class="bi bi-journal-text" style="font-size: 1.5rem;"></i> Uploaded Documents</div>
                                <div class="stat-value"><?= isset($documentsCount) ? (int)$documentsCount : 0 ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="stat-title">Generated Notes</div>
                                <div class="stat-value"><?= isset($notesCount) ? (int)$notesCount : 0 ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="stat-title">Generated Summaries</div>
                                <div class="stat-value"><?= isset($summariesCount) ? (int)$summariesCount : 0 ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="stat-title">Generated Flashcards</div>
                                <div class="stat-value"><?= isset($flashcardsCount) ? (int)$flashcardsCount : 0 ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="stat-title">Generated Mindmaps</div>
                                <div class="stat-value"><?= isset($mindmapsCount) ? (int)$mindmapsCount : 0 ?></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Box 2: Line chart for MCQ quiz score history -->
            <div class="col-lg-6">
                <div class="card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Quiz Score History (MCQ)</h5>
                        <div class="dropdown">
                            <button class="btn-icon" id="filterDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false" title="Filter by period">
                                <i class="bi bi-funnel-fill" style="font-size: 1.1rem;"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdownBtn">
                                <li><a class="dropdown-item chart-filter" data-range="day" href="#">By Day</a></li>
                                <li><a class="dropdown-item chart-filter" data-range="month" href="#">By Month</a></li>
                                <li><a class="dropdown-item chart-filter" data-range="year" href="#">By Year</a></li>
                            </ul>
                        </div>
                    </div>
                    <canvas id="quizChart" height="120"></canvas>
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

    <!-- Report Modal (first box) -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">Activity Report</h5>
                    <button type="button" class="btn-back" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x"></i></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table align-middle report-table" id="reportTable">
                            <thead>
                                <tr>
                                    <th style="width: 60%;">Metric</th>
                                    <th class="text-end">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Uploaded Documents</td>
                                    <td class="text-end"><?= isset($documentsCount) ? (int)$documentsCount : 0 ?></td>
                                </tr>
                                <tr>
                                    <td>Generated Notes</td>
                                    <td class="text-end"><?= isset($notesCount) ? (int)$notesCount : 0 ?></td>
                                </tr>
                                <tr>
                                    <td>Generated Summaries</td>
                                    <td class="text-end"><?= isset($summariesCount) ? (int)$summariesCount : 0 ?></td>
                                </tr>
                                <tr>
                                    <td>Generated Flashcards</td>
                                    <td class="text-end"><?= isset($flashcardsCount) ? (int)$flashcardsCount : 0 ?></td>
                                </tr>
                                <tr>
                                    <td>Generated Mindmaps</td>
                                    <td class="text-end"><?= isset($mindmapsCount) ? (int)$mindmapsCount : 0 ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-purple text-white" id="downloadReportBtn"><i class="bi bi-download me-2"></i>Download PDF</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        function goToLatestDocument() {
            var latestId = <?= isset($latestDocumentId) ? (int)$latestDocumentId : 0 ?>;
            if (latestId > 0) {
                document.getElementById('latestDocForm').submit();
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
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF();
                    
                    // Set theme colors
                    const headerColor = [212, 181, 255]; // #d4b5ff
                    const rowColor1 = [231, 213, 255]; // #e7d5ff
                    const rowColor2 = [255, 255, 255]; // white
                    const textColor = [90, 50, 163]; // #5a32a3
                    
                    // Title
                    doc.setFontSize(18);
                    doc.setTextColor(textColor[0], textColor[1], textColor[2]);
                    doc.text('Activity Report', 14, 20);
                    
                    // Get table data
                    const table = document.getElementById('reportTable');
                    const rows = table.querySelectorAll('tbody tr');
                    
                    // Table header
                    doc.setFillColor(headerColor[0], headerColor[1], headerColor[2]);
                    doc.setTextColor(textColor[0], textColor[1], textColor[2]);
                    doc.rect(14, 30, 180, 10, 'F');
                    doc.setFontSize(12);
                    doc.setFont(undefined, 'bold');
                    doc.text('Metric', 16, 37);
                    doc.text('Quantity', 180, 37, { align: 'right' });
                    
                    // Table rows
                    let yPos = 45;
                    doc.setFont(undefined, 'normal');
                    doc.setFontSize(10);
                    doc.setTextColor(0, 0, 0);
                    
                    rows.forEach((row, index) => {
                        const cells = row.querySelectorAll('td');
                        const metric = cells[0].textContent.trim();
                        const quantity = cells[1].textContent.trim();
                        
                        // Alternate row colors
                        if (index % 2 === 0) {
                            doc.setFillColor(rowColor1[0], rowColor1[1], rowColor1[2]);
                        } else {
                            doc.setFillColor(rowColor2[0], rowColor2[1], rowColor2[2]);
                        }
                        doc.rect(14, yPos - 5, 180, 8, 'F');
                        
                        doc.text(metric, 16, yPos);
                        doc.text(quantity, 180, yPos, { align: 'right' });
                        
                        yPos += 10;
                    });
                    
                    // Footer
                    const date = new Date().toLocaleDateString();
                    doc.setFontSize(8);
                    doc.setTextColor(128, 128, 128);
                    doc.text('Generated on: ' + date, 14, yPos + 10);
                    
                    // Save PDF
                    const username = '<?= isset($user) && isset($user["username"]) ? htmlspecialchars($user["username"], ENT_QUOTES) : "User" ?>';
                    doc.save('Activity_Report_' + username + '_' + date.replace(/\//g, '-') + '.pdf');
                });
            }

            // Chart with filter (day/month/year)
            const ctx = document.getElementById('quizChart');
            if (!ctx) return;

            const rawHistory = [
                <?php
                if (!empty($quizHistory)) {
                    foreach ($quizHistory as $row) {
                        $ts = !empty($row['markAt']) ? date('c', strtotime($row['markAt'])) : '';
                        $score = is_numeric($row['totalScore']) ? (float)$row['totalScore'] : 0;
                        echo "{ ts: '" . $ts . "', score: " . $score . " },";
                    }
                }
                ?>
            ];

            function aggregate(history, range) {
                const map = new Map();
                for (const item of history) {
                    if (!item.ts) continue;
                    const d = new Date(item.ts);
                    let key = '';
                    if (range === 'year') key = d.getFullYear().toString();
                    else if (range === 'month') key = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
                    else key = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                    if (!map.has(key)) map.set(key, []);
                    map.get(key).push(item.score);
                }
                const labels = Array.from(map.keys()).sort();
                const data = labels.map(k => {
                    const arr = map.get(k);
                    return arr.length ? (arr.reduce((a, b) => a + b, 0) / arr.length) : 0;
                });
                return {
                    labels,
                    data
                };
            }

            let currentRange = 'day';
            let agg = aggregate(rawHistory, currentRange);
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: agg.labels,
                    datasets: [{
                        label: 'Score (%)',
                        data: agg.data,
                        borderColor: '#6f42c1',
                        backgroundColor: 'rgba(111,66,193,0.15)',
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#6f42c1'
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                stepSize: 20
                            }
                        }
                    }
                }
            });

            document.querySelectorAll('.chart-filter').forEach(el => {
                el.addEventListener('click', (e) => {
                    e.preventDefault();
                    const range = e.currentTarget.getAttribute('data-range') || 'day';
                    currentRange = range;
                    const res = aggregate(rawHistory, currentRange);
                    chart.data.labels = res.labels;
                    chart.data.datasets[0].data = res.data;
                    chart.update();
                });
            });
        })();
    </script>
</body>

</html>