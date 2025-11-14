<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mindmap - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mind-elixir/dist/mind-elixir.css" />
    <style>
        #mindmap-container {
            width: 100%;
            min-height: 500px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background-color: #ffffff;
            overflow: hidden;
        }

        .upload-container {
            background-color: #f8f9fa;
            padding: 30px;
        }

        .mindmap-toolbar .btn {
            min-width: 130px;
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
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center gap-2 mindmap-toolbar mb-3" id="mindmapToolbar" style="display: none;">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addChildNodeBtn">Add child branch</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addSiblingNodeBtn">Add sibling branch</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="addDescriptionBtn">Add description</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="deleteNodeBtn">Delete branch</button>
                            <div class="ms-auto d-flex gap-2 align-items-center">
                                <span class="badge bg-secondary" id="mindmapStatusBadge">No changes</span>
                                <button type="button" class="btn btn-sm btn-success" id="saveMindmapBtn" disabled>Save Changes</button>
                            </div>
                        </div>
                        <div id="mindmap-container" style="display: none;">
                            <!-- Mindmap editor -->
                        </div>
                        <small class="text-muted d-block mt-2">Tip: select a branch to drag it, add new branches with the toolbar or context menu, and use “Save Changes” to persist edits.</small>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script type="module">
        import MindElixir from 'https://cdn.jsdelivr.net/npm/mind-elixir/dist/mind-elixir.js';
        window.MindElixir = MindElixir;
    </script>

    <script>
        const fileId = '<?= isset($file['fileID']) ? htmlspecialchars($file['fileID']) : '' ?>';
        let currentViewedMindmapId = null;
        let mindmapInstance = null;
        let mindmapDirty = false;

        const container = document.getElementById('mindmap-container');
        const toolbar = document.getElementById('mindmapToolbar');
        const saveButton = document.getElementById('saveMindmapBtn');
        const statusBadge = document.getElementById('mindmapStatusBadge');
        const addChildBtn = document.getElementById('addChildNodeBtn');
        const addSiblingBtn = document.getElementById('addSiblingNodeBtn');
        const addDescriptionBtn = document.getElementById('addDescriptionBtn');
        const deleteNodeBtn = document.getElementById('deleteNodeBtn');

        document.addEventListener('DOMContentLoaded', () => {
            updateExportButtonStates(null);
            container.innerHTML = '<p class="text-center text-muted my-4">Select or generate a mindmap to begin editing.</p>';
        });

        function updateExportButtonStates(viewedMindmapId) {
            document.querySelectorAll('.export-mindmap-image-btn, .export-mindmap-pdf-btn').forEach(btn => {
                btn.style.pointerEvents = 'none';
                btn.style.opacity = '0.5';
                btn.style.cursor = 'not-allowed';
            });

            if (viewedMindmapId) {
                document.querySelectorAll(`.export-mindmap-image-btn[data-id="${viewedMindmapId}"], .export-mindmap-pdf-btn[data-id="${viewedMindmapId}"]`).forEach(btn => {
                    btn.style.pointerEvents = 'auto';
                    btn.style.opacity = '1';
                    btn.style.cursor = 'pointer';
                });
            }
        }

        function setStatus(text, variant = 'secondary') {
            if (!statusBadge) return;
            statusBadge.textContent = text;
            statusBadge.className = `badge bg-${variant}`;
        }

        function toggleSaveButton(enabled) {
            if (!saveButton) return;
            saveButton.disabled = !enabled;
        }

        function markDirty() {
            mindmapDirty = true;
            toggleSaveButton(true);
            setStatus('Unsaved changes', 'warning');
        }

        function ensureEditorAvailable() {
            const MindElixirClass = window.MindElixir;
            if (!MindElixirClass) {
                container.innerHTML = '<div class="alert alert-warning">Mindmap editor failed to load. Please refresh the page.</div>';
                return null;
            }
            return MindElixirClass;
        }

        function renderMindmap(structure, markdownFallback) {
            const MindElixirClass = ensureEditorAvailable();
            if (!MindElixirClass) return;

            container.style.display = 'block';
            container.innerHTML = '';
            toolbar.style.display = 'flex';

            mindmapInstance = new MindElixirClass({
                el: '#mindmap-container',
                direction: MindElixirClass.SIDE,
                nodeMenu: true,
                contextMenu: true,
                toolBar: true,
                allowUndo: true,
                draggable: true,
                editable: true
            });

            const dataToLoad = structure && structure.nodeData ? structure : markdownToStructure(markdownFallback);
            mindmapInstance.init(dataToLoad);
            mindmapInstance.enableEdit();
            mindmapInstance.bus.addListener('operation', () => {
                markDirty();
            });

            mindmapDirty = false;
            toggleSaveButton(false);
            setStatus('No changes', 'secondary');
        }

        document.getElementById('mindmapForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            const form = e.target;
            const submitButton = form.querySelector('#genMindmap');
            const originalText = submitButton.textContent;
            container.innerHTML = '<p class="text-center p-3">Generating mindmap...</p>';

            submitButton.disabled = true;
            submitButton.textContent = 'Generating...';

            try {
                const data = new FormData(form);
                const res = await fetch(form.action, { method: 'POST', body: data });
                const json = await res.json();

                if (!json.success || !json.mindmapId) {
                    throw new Error(json.message || 'Failed to generate mindmap');
                }

                currentViewedMindmapId = String(json.mindmapId);
                renderMindmap(json.structure, json.markdown);
                updateExportButtonStates(currentViewedMindmapId);
            } catch (error) {
                container.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                currentViewedMindmapId = null;
                updateExportButtonStates(null);
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        });

        document.addEventListener('click', async (e) => {
            const viewBtn = e.target.closest('.view-btn');
            if (!viewBtn) return;

            e.preventDefault();
            const id = viewBtn.dataset.id;
            container.innerHTML = '<p class="text-center p-3">Loading mindmap...</p>';

            try {
                const formData = new FormData();
                formData.append('mindmap_id', id);
                formData.append('file_id', fileId);

                const res = await fetch('<?= LOAD_MINDMAP_STRUCTURE ?>', { method: 'POST', body: formData });
                const json = await res.json();
                if (!json.success) {
                    throw new Error(json.message || 'Failed to load mindmap');
                }

                currentViewedMindmapId = String(json.mindmapId);
                renderMindmap(json.structure, json.markdown);
                updateExportButtonStates(currentViewedMindmapId);
            } catch (error) {
                container.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                currentViewedMindmapId = null;
                updateExportButtonStates(null);
            }
        });

        saveButton.addEventListener('click', async () => {
            if (!mindmapInstance || !currentViewedMindmapId) {
                alert('No mindmap loaded.');
                return;
            }

            try {
                toggleSaveButton(false);
                setStatus('Saving…', 'secondary');

                const structure = mindmapInstance.getData();
                const markdown = structureToMarkdown(structure.nodeData);

                const res = await fetch(`<?= UPDATE_MINDMAP_STRUCTURE ?>?file_id=${encodeURIComponent(fileId)}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        mindmap_id: currentViewedMindmapId,
                        file_id: fileId,
                        structure,
                        markdown
                    })
                });
                const json = await res.json();
                if (!json.success) {
                    throw new Error(json.message || 'Failed to save mindmap');
                }

                mindmapDirty = false;
                setStatus('Saved', 'success');
                toggleSaveButton(false);
            } catch (error) {
                toggleSaveButton(true);
                setStatus('Save failed', 'danger');
                alert('Error saving mindmap: ' + error.message);
            }
        });

        addChildBtn.addEventListener('click', () => {
            if (!mindmapInstance) return;
            mindmapInstance.addChild();
        });

        addSiblingBtn.addEventListener('click', () => {
            if (!mindmapInstance) return;
            mindmapInstance.insertSibling('after');
        });

        addDescriptionBtn.addEventListener('click', () => {
            if (!mindmapInstance || !mindmapInstance.currentNode) {
                alert('Select a branch first.');
                return;
            }
            const nodeObj = mindmapInstance.currentNode.nodeObj;
            const current = nodeObj.data?.description || '';
            const next = prompt('Enter a short description for this branch:', current);
            if (next === null) return;
            nodeObj.data = nodeObj.data || {};
            nodeObj.data.description = next.trim();
            mindmapInstance.refresh();
            markDirty();
        });

        deleteNodeBtn.addEventListener('click', () => {
            if (!mindmapInstance || !mindmapInstance.currentNodes || mindmapInstance.currentNodes.length === 0) {
                alert('Select the branch you want to delete.');
                return;
            }
            mindmapInstance.removeNodes(mindmapInstance.currentNodes);
        });

        document.addEventListener('click', (e) => {
            const imageBtn = e.target.closest('.export-mindmap-image-btn');
            if (imageBtn) {
                handleExport(imageBtn.dataset.id, exportMindmapAsImage);
                return;
            }
            const pdfBtn = e.target.closest('.export-mindmap-pdf-btn');
            if (pdfBtn) {
                handleExport(pdfBtn.dataset.id, exportMindmapAsPdf);
            }
        });

        function handleExport(mindmapId, exporter) {
            if (currentViewedMindmapId !== mindmapId) {
                alert('Please view this mindmap first before exporting.');
                return;
            }
            const target = getMindmapCanvasTarget();
            if (!target) {
                alert('Mindmap is not fully loaded. Please wait and try again.');
                return;
            }
            exporter(mindmapId, target);
        }

        function getMindmapCanvasTarget() {
            return document.querySelector('#mindmap-container .map-container') || container;
        }

        function downloadCanvasAsImage(canvas, mindmapId) {
            const dataURL = canvas.toDataURL('image/png');
            const link = document.createElement('a');
            link.href = dataURL;
            link.download = `mindmap_${mindmapId}_${new Date().getTime()}.png`;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function exportMindmapAsImage(mindmapId, target) {
            html2canvas(target, {
                backgroundColor: '#ffffff',
                scale: 2,
                logging: false,
                useCORS: true,
                width: target.scrollWidth,
                height: target.scrollHeight
            }).then(canvas => {
                if (canvas) {
                    downloadCanvasAsImage(canvas, mindmapId);
                }
            }).catch(error => alert('Error exporting mindmap: ' + error.message));
        }

        function exportMindmapAsPdf(mindmapId, target) {
            if (typeof window.jspdf === 'undefined') {
                alert('PDF library not loaded. Please refresh the page and try again.');
                return;
            }
            html2canvas(target, {
                backgroundColor: '#ffffff',
                scale: 3,
                logging: false,
                useCORS: true,
                width: target.scrollWidth,
                height: target.scrollHeight
            }).then(canvas => {
                const { jsPDF } = window.jspdf;
                const imgData = canvas.toDataURL('image/png', 1.0);
                const pdf = new jsPDF('landscape', 'mm', 'a4');
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();
                const imgProps = pdf.getImageProperties(imgData);
                const ratio = imgProps.width / imgProps.height;
                let finalWidth = pageWidth - 10;
                let finalHeight = finalWidth / ratio;
                if (finalHeight > pageHeight - 10) {
                    finalHeight = pageHeight - 10;
                    finalWidth = finalHeight * ratio;
                }
                const xOffset = (pageWidth - finalWidth) / 2;
                const yOffset = (pageHeight - finalHeight) / 2;
                pdf.addImage(imgData, 'PNG', xOffset, yOffset, finalWidth, finalHeight);
                pdf.save(`mindmap_${mindmapId}_${new Date().getTime()}.pdf`);
            }).catch(error => alert('Error exporting mindmap as PDF: ' + error.message));
        }

        function markdownToStructure(markdown) {
            const title = (markdown || '').split('\n')[0]?.replace(/^#+\s*/, '').trim() || 'Mindmap';
            const root = MindElixirNode(title);
            const stack = [{ level: 1, node: root }];
            (markdown || '').split(/\r?\n/).map(line => line.trim()).filter(Boolean).forEach(line => {
                const match = line.match(/^(#+)\s+(.*)$/);
                if (!match) return;
                const level = match[1].length;
                const topic = match[2].trim();
                if (level === 1) return;
                const node = MindElixirNode(topic);
                while (stack.length && stack[stack.length - 1].level >= level) {
                    stack.pop();
                }
                const parent = stack[stack.length - 1]?.node || root;
                parent.children = parent.children || [];
                parent.children.push(node);
                stack.push({ level, node });
            });
            return { nodeData: root };
        }

        function MindElixirNode(topic) {
            return {
                id: Math.random().toString(16).slice(2),
                topic: topic || 'New Topic',
                children: [],
                data: {}
            };
        }

        function structureToMarkdown(node, depth = 1) {
            if (!node) return '';
            const prefix = '#'.repeat(Math.min(depth, 6));
            let markdown = `${prefix} ${node.topic}\n`;
            if (node.data?.description) {
                markdown += `${node.data.description}\n`;
            }
            (node.children || []).forEach(child => {
                markdown += '\n' + structureToMarkdown(child, depth + 1);
            });
            return markdown;
        }
    </script>
</body>

</html>