<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mindmap - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css" />
    <style>
        :root {
            --sa-primary: #6f42c1;
            --sa-primary-dark: #593093;
            --sa-accent: #e7d5ff;
            --sa-accent-strong: #d4b5ff;
            --sa-muted: #6c757d;
            --sa-card-border: #ede1ff;
        }

        #mindmap-container {
            width: 100%;
            min-height: 50px;
            border: 1px solid var(--sa-card-border);
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(111, 66, 193, 0.08);
            background-color: #fff;
        }

        .markmap {
            width: 100%;
            height: 600px;
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
    <div class="d-flex flex-grow-1">
        <?php include 'app/views/sidebar.php'; ?>
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
            <div class="container-fluid upload-container">
                <h3 style="color: #212529; font-size: 1.5rem; font-weight: 600; margin-bottom: 30px;">Mindmap</h3>
                <form method="POST" action="<?= DISPLAY_DOCUMENT ?>" style="display: inline;">
                    <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
                    <h4 style="color: #212529; font-size: 1.25rem; font-weight: 500; margin-bottom: 20px; cursor: pointer; display: inline-block;" onclick="this.closest('form').submit();"><?php echo htmlspecialchars($file['name']); ?></h4>
                </form>
                <?php require_once VIEW_NAVBAR; ?>

                <!-- Generate Mindmap Form -->
                <div class="card">
                    <div class="card-body">
                        <form id="mindmapForm" action="<?= GENERATE_MINDMAP ?>" method="POST">
                            <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">

                            <button type="submit" id="genMindmap" class="btn btn-primary">Generate Mindmap</button>
                        </form>
                    </div>
                </div>

                <!-- Mindmap Display -->
                <div class="mt-3">
                    <div id="mindmap-container" style="display: none;">
                        <!-- Mindmap will be injected here -->
                    </div>
                </div>

                <!-- Saved Mindmaps -->
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Generated Mindmaps</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" id="mindmapList">
                            <?php if (!empty($mindmapList)) : ?>
                                <?php foreach ($mindmapList as $mindmap) : ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <strong><?= htmlspecialchars($mindmap['title']) ?></strong><br>
                                            <small class="text-muted">Updated: <?= htmlspecialchars($mindmap['createdAt']) ?></small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownFileActions<?php echo $mindmap['mindmapID']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownFileActions<?php echo $mindmap['mindmapID']; ?>">
                                                <li><a class="dropdown-item view-btn" href="#" data-id="<?= htmlspecialchars($mindmap['mindmapID']) ?>">View</a></li>
                                                <li><a class="dropdown-item export-mindmap-image-btn" href="#" data-id="<?= htmlspecialchars($mindmap['mindmapID']) ?>">Export as Image</a></li>
                                                <li><a class="dropdown-item export-mindmap-pdf-btn" href="#" data-id="<?= htmlspecialchars($mindmap['mindmapID']) ?>">Export as PDF</a></li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <form method="POST" action="<?= DELETE_MINDMAP ?>" style="display: inline;">
                                                        <input type="hidden" name="mindmap_id" value="<?= htmlspecialchars($mindmap['mindmapID']) ?>">
                                                        <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                        <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Delete</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <div class="list-group-item text-muted text-center">No generated mindmaps</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/markmap-autoloader@0.18"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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

        let currentViewedMindmapId = null; // Track which mindmap is currently being viewed

        // Function to update export button states
        function updateExportButtonStates(viewedMindmapId) {
            // Disable all export buttons
            document.querySelectorAll('.export-mindmap-image-btn, .export-mindmap-pdf-btn').forEach(btn => {
                btn.style.pointerEvents = 'none';
                btn.style.opacity = '0.5';
                btn.style.cursor = 'not-allowed';
            });

            // Enable export buttons only for the currently viewed mindmap
            if (viewedMindmapId) {
                document.querySelectorAll(`.export-mindmap-image-btn[data-id="${viewedMindmapId}"], .export-mindmap-pdf-btn[data-id="${viewedMindmapId}"]`).forEach(btn => {
                    btn.style.pointerEvents = 'auto';
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                });
            }
        }

        // Initialize: disable all export buttons on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateExportButtonStates(null);
            
            const selectedMindmap = document.getElementById('mindmap-container');
            if (selectedMindmap.innerHTML) {
                selectedMindmap.innerHTML = '<p class="text-center p-3"> Select a mindmap</p>';
            }
        });

        // Handle generating new mindmap
        document.getElementById('mindmapForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            const form = e.target;
            const submitButton = form.querySelector('#genMindmap');
            const originalButtonText = submitButton.textContent;
            const container = document.getElementById('mindmap-container');

            // Disable button and show loading state
            submitButton.disabled = true;
            submitButton.textContent = 'Generating...';
            container.innerHTML = '<p class="text-center p-3">Generating mindmap...</p>';

            try {
                const data = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: data
                });
                const json = await res.json();

                if (json.success && json.markdown) {
                    container.style.display = 'block';
                    renderAutoloadMindmap(json.markdown);
                    showSnackbar('Mindmap generated successfully!', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showSnackbar(json.message || 'Failed to generate mindmap. Please try again.', 'error');
                    container.innerHTML = '<p class="text-center p-3 text-muted">Failed to generate mindmap</p>';
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                }
            } catch (err) {
                showSnackbar('An error occurred while generating the mindmap. Please try again.', 'error');
                console.error('Error:', err);
                container.innerHTML = '<p class="text-center p-3 text-muted">Error generating mindmap</p>';
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            }
        });


        // Handle viewing saved mindmaps
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.view-btn');
            if (!btn) return;

            const id = btn.dataset.id;
            const container = document.getElementById('mindmap-container');
            container.innerHTML = '<p class="text-center p-3">Loading mindmap...</p>';

            try {
                const formData = new FormData();
                formData.append('mindmap_id', id);
                formData.append('file_id', '<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>');

                const res = await fetch('<?= VIEW_MINDMAP_ROUTE ?>', {
                    method: 'POST',
                    body: formData
                });
                const json = await res.json();

                if (json.success && json.markdown) {
                    container.style.display = 'block';
                    renderAutoloadMindmap(json.markdown);
                    // Set current viewed mindmap and update button states
                    currentViewedMindmapId = id;
                    updateExportButtonStates(id);
                } else {
                    showSnackbar(json.message || 'Failed to load mindmap. Please try again.', 'error');
                    container.innerHTML = '<p class="text-center p-3 text-muted">Failed to load mindmap</p>';
                    currentViewedMindmapId = null;
                    updateExportButtonStates(null);
                }
            } catch (err) {
                showSnackbar('An error occurred while loading the mindmap. Please try again.', 'error');
                console.error('Error:', err);
                container.innerHTML = '<p class="text-center p-3 text-muted">Error loading mindmap</p>';
                currentViewedMindmapId = null;
                updateExportButtonStates(null);
            }
        });

        // Handle exporting mindmap as image
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.export-mindmap-image-btn');
            if (!btn) return;

            const id = btn.dataset.id;

            // Check if this mindmap is currently being viewed
            if (currentViewedMindmapId !== id) {
                showSnackbar('Please view this mindmap first before exporting.', 'error');
                return;
            }

            const container = document.getElementById('mindmap-container');
            const markmapDiv = container.querySelector('.markmap');

            // Verify mindmap is displayed
            if (!markmapDiv || markmapDiv.children.length === 0) {
                showSnackbar('Mindmap is not fully loaded. Please wait and try again.', 'error');
                return;
            }

            // Export the currently displayed mindmap
            exportMindmapAsImage(id);
        });

        // Handle exporting mindmap as PDF
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.export-mindmap-pdf-btn');
            if (!btn) return;

            const id = btn.dataset.id;

            // Check if this mindmap is currently being viewed
            if (currentViewedMindmapId !== id) {
                showSnackbar('Please view this mindmap first before exporting.', 'error');
                return;
            }

            const container = document.getElementById('mindmap-container');
            const markmapDiv = container.querySelector('.markmap');

            // Verify mindmap is displayed
            if (!markmapDiv || markmapDiv.children.length === 0) {
                showSnackbar('Mindmap is not fully loaded. Please wait and try again.', 'error');
                return;
            }

            // Export the currently displayed mindmap
            exportMindmapAsPdf(id);
        });

        // Helper function to download canvas as image using data URL
        function downloadCanvasAsImage(canvas, mindmapId) {
            try {
                const dataURL = canvas.toDataURL('image/png');
                const link = document.createElement('a');
                link.href = dataURL;
                link.download = `mindmap_${mindmapId}_${new Date().getTime()}.png`;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                setTimeout(() => {
                    if (document.body.contains(link)) {
                        document.body.removeChild(link);
                    }
                }, 200);
            } catch (err) {
                console.error('Data URL download error:', err);
                showSnackbar('Failed to download mindmap image. Please try again.', 'error');
                console.error('Error downloading image:', err);
            }
        }

        // Function to export mindmap as image
        function exportMindmapAsImage(mindmapId) {
            const container = document.getElementById('mindmap-container');
            const markmapDiv = container.querySelector('.markmap');

            if (!markmapDiv) {
                showSnackbar('Please view the mindmap first before exporting.', 'error');
                return;
            }

            // Check if mindmap has content
            if (!markmapDiv.children || markmapDiv.children.length === 0) {
                showSnackbar('Mindmap is not fully loaded. Please wait and try again.', 'error');
                return;
            }

            // Show loading indicator
            const originalDisplay = container.style.display;
            container.style.display = 'block';

            // Use html2canvas to capture the mindmap
            html2canvas(markmapDiv, {
                backgroundColor: '#ffffff',
                scale: 2,
                logging: false,
                useCORS: true,
                allowTaint: true,
                width: markmapDiv.scrollWidth,
                height: markmapDiv.scrollHeight
            }).then(canvas => {
                try {
                    // Convert canvas to blob and download
                    if (canvas.toBlob) {
                        canvas.toBlob(function(blob) {
                            if (!blob) {
                                // Fallback to data URL method
                                downloadCanvasAsImage(canvas, mindmapId);
                                return;
                            }
                            
                            try {
                                const url = URL.createObjectURL(blob);
                                const link = document.createElement('a');
                                link.href = url;
                                link.download = `mindmap_${mindmapId}_${new Date().getTime()}.png`;
                                link.style.display = 'none';
                                link.setAttribute('download', link.download);
                                
                                document.body.appendChild(link);
                                
                                // Trigger download
                                link.click();
                                
                                // Cleanup after a short delay
                                setTimeout(() => {
                                    if (document.body.contains(link)) {
                                        document.body.removeChild(link);
                                    }
                                    URL.revokeObjectURL(url);
                                }, 200);
                            } catch (err) {
                                console.error('Download link error:', err);
                                // Fallback to data URL method
                                downloadCanvasAsImage(canvas, mindmapId);
                            }
                        }, 'image/png', 1.0);
                    } else {
                        // Fallback if toBlob is not supported
                        downloadCanvasAsImage(canvas, mindmapId);
                    }
                } catch (err) {
                    console.error('Export error:', err);
                    showSnackbar('Failed to create download. Please try again.', 'error');
                console.error('Error creating download:', err);
                }
            }).catch(err => {
                console.error('html2canvas error:', err);
                showSnackbar('Failed to export mindmap. Please try again.', 'error');
                console.error('Error exporting mindmap:', err);
            });
        }

        // Function to export mindmap as PDF
        function exportMindmapAsPdf(mindmapId) {
            const container = document.getElementById('mindmap-container');
            const markmapDiv = container.querySelector('.markmap');

            if (!markmapDiv) {
                showSnackbar('Please view the mindmap first before exporting.', 'error');
                return;
            }

            // Check if jsPDF is available
            if (typeof window.jspdf === 'undefined') {
                showSnackbar('PDF library not loaded. Please refresh the page and try again.', 'error');
                return;
            }

            // Use html2canvas to capture the mindmap with higher quality
            html2canvas(markmapDiv, {
                backgroundColor: '#ffffff',
                scale: 3, // Higher scale for better quality
                logging: false,
                useCORS: true,
                width: markmapDiv.scrollWidth,
                height: markmapDiv.scrollHeight
            }).then(canvas => {
                try {
                    const {
                        jsPDF
                    } = window.jspdf;
                    const imgData = canvas.toDataURL('image/png', 1.0);

                    // PDF dimensions (A4 size in mm)
                    const pdfWidth = 210; // A4 width in mm
                    const pdfHeight = 297; // A4 height in mm

                    // Reduced margins for bigger mindmap (5mm on each side = 10mm total)
                    const margin = 5;
                    const availableWidth = pdfWidth - (margin * 2);
                    const availableHeight = pdfHeight - (margin * 2);

                    // Calculate image dimensions maintaining aspect ratio
                    const imgWidth = canvas.width;
                    const imgHeight = canvas.height;
                    const ratio = imgWidth / imgHeight;

                    // Determine orientation based on mindmap aspect ratio
                    let finalWidth, finalHeight, orientation, pageWidth, pageHeight;

                    if (ratio > 1) {
                        // Landscape mindmap - use landscape orientation
                        orientation = 'landscape';
                        pageWidth = pdfHeight; // Swap for landscape
                        pageHeight = pdfWidth;
                        const landscapeAvailableWidth = pageWidth - (margin * 2);
                        const landscapeAvailableHeight = pageHeight - (margin * 2);

                        finalWidth = landscapeAvailableWidth;
                        finalHeight = finalWidth / ratio;

                        // If height exceeds page, scale down
                        if (finalHeight > landscapeAvailableHeight) {
                            finalHeight = landscapeAvailableHeight;
                            finalWidth = finalHeight * ratio;
                        }
                    } else {
                        // Portrait mindmap - use portrait orientation
                        orientation = 'portrait';
                        pageWidth = pdfWidth;
                        pageHeight = pdfHeight;

                        finalWidth = availableWidth;
                        finalHeight = finalWidth / ratio;

                        // If height exceeds page, scale down
                        if (finalHeight > availableHeight) {
                            finalHeight = availableHeight;
                            finalWidth = finalHeight * ratio;
                        }
                    }

                    // Center the image
                    const xOffset = (pageWidth - finalWidth) / 2;
                    const yOffset = (pageHeight - finalHeight) / 2;

                    // Create PDF with appropriate orientation
                    const pdf = new jsPDF(orientation, 'mm', 'a4');
                    pdf.addImage(imgData, 'PNG', xOffset, yOffset, finalWidth, finalHeight);

                    // Save PDF
                    pdf.save(`mindmap_${mindmapId}_${new Date().getTime()}.pdf`);
                } catch (err) {
                    showSnackbar('Failed to export mindmap as PDF. Please try again.', 'error');
                    console.error('Error exporting mindmap as PDF:', err);
                }
            }).catch(err => {
                showSnackbar('Failed to capture mindmap. Please try again.', 'error');
                console.error('Error capturing mindmap:', err);
            });
        }

        //Render using autoloader 
        function renderAutoloadMindmap(markdown) {
            const container = document.getElementById('mindmap-container');

            // Clear container
            container.innerHTML = '';

            // Create markmap div using DOM methods to avoid template literal issues
            const markmapDiv = document.createElement('div');
            markmapDiv.className = 'markmap';

            // Create script element for template
            const scriptEl = document.createElement('script');
            scriptEl.type = 'text/template';
            scriptEl.textContent = markdown;

            markmapDiv.appendChild(scriptEl);
            container.appendChild(markmapDiv);

            // Re-run autoloader to render the new block
            if (window.markmap && window.markmap.autoLoader) {
                window.markmap.autoLoader.renderAll();
            }
        }
    </script>
</body>

</html>