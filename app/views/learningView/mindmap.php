<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mindmap - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css" />
    <style>
        #mindmap-container {
            width: 100%;
            min-height: 50px;
            border: 1px solid #ccc;
        }

        .markmap {
            width: 100%;
            height: 600px;
        }
        .upload-container {
            background-color: #f8f9fa;
            padding: 30px;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app/views/sidebar.php'; ?>
        <main class="flex-grow-1 p-3" style="background-color: #f8f9fa;">
            <div class="container-fluid upload-container">
                <h3 style="color: #212529; font-size: 1.5rem; font-weight: 600; margin-bottom: 30px;">Mindmap</h3>
                <h4 style="color: #212529; font-size: 1.25rem; font-weight: 500; margin-bottom: 20px;"><?php echo htmlspecialchars($file['name']); ?></h4>
                <?php require_once VIEW_NAVBAR; ?>

                <!-- Generate Mindmap Form -->
                <div class="card">
                    <div class="card-body">
                        <form id="mindmapForm" action="<?= GENERATE_MINDMAP ?>" method="POST">
                            <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">

                            <button type="submit" id="genMindmap" class="btn btn-primary" style="background-color: #A855F7; border: none;">Generate Mindmap</button>
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
                    location.reload();
                } else {
                    container.innerHTML = `<div class="alert alert-danger">Error: ${json.message || 'Failed to generate mindmap'}</div>`;
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                }
            } catch (err) {
                container.innerHTML = `<div class="alert alert-danger">Error: ${err.message}</div>`;
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
                    container.innerHTML = `<div class="alert alert-danger">Error: ${json.message || 'Failed to load mindmap'}</div>`;
                    currentViewedMindmapId = null;
                    updateExportButtonStates(null);
                }
            } catch (err) {
                container.innerHTML = `<div class="alert alert-danger">Error: ${err.message}</div>`;
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
                alert('Please view this mindmap first before exporting.');
                return;
            }

            const container = document.getElementById('mindmap-container');
            const markmapDiv = container.querySelector('.markmap');

            // Verify mindmap is displayed
            if (!markmapDiv || markmapDiv.children.length === 0) {
                alert('Mindmap is not fully loaded. Please wait and try again.');
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
                alert('Please view this mindmap first before exporting.');
                return;
            }

            const container = document.getElementById('mindmap-container');
            const markmapDiv = container.querySelector('.markmap');

            // Verify mindmap is displayed
            if (!markmapDiv || markmapDiv.children.length === 0) {
                alert('Mindmap is not fully loaded. Please wait and try again.');
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
                alert('Error downloading image: ' + err.message);
            }
        }

        // Function to export mindmap as image
        function exportMindmapAsImage(mindmapId) {
            const container = document.getElementById('mindmap-container');
            const markmapDiv = container.querySelector('.markmap');

            if (!markmapDiv) {
                alert('Please view the mindmap first before exporting.');
                return;
            }

            // Check if mindmap has content
            if (!markmapDiv.children || markmapDiv.children.length === 0) {
                alert('Mindmap is not fully loaded. Please wait and try again.');
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
                    alert('Error creating download: ' + err.message);
                }
            }).catch(err => {
                console.error('html2canvas error:', err);
                alert('Error exporting mindmap: ' + err.message);
            });
        }

        // Function to export mindmap as PDF
        function exportMindmapAsPdf(mindmapId) {
            const container = document.getElementById('mindmap-container');
            const markmapDiv = container.querySelector('.markmap');

            if (!markmapDiv) {
                alert('Please view the mindmap first before exporting.');
                return;
            }

            // Check if jsPDF is available
            if (typeof window.jspdf === 'undefined') {
                alert('PDF library not loaded. Please refresh the page and try again.');
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
                    alert('Error exporting mindmap as PDF: ' + err.message);
                }
            }).catch(err => {
                alert('Error capturing mindmap: ' + err.message);
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