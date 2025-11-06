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
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app\views\sidebar.php'; ?>
        <main class="flex-grow-1 p-3">
            <div class="container">
                <h3 class="mb-4" style="color: #A855F7;">Mindmap</h3>
                <h4 class="mb-4"><?php echo $file['name']; ?></h4>
                <?php require_once 'app\views\learningView\navbar.php'; ?>

                <!-- Generate Mindmap Form -->
                <div class="card">
                    <div class="card-body">
                        <form id="mindmapForm"
                            action="<?= BASE_PATH ?>lm/generateMindmap?fileID=<?= isset($_GET['fileID']) ? htmlspecialchars($_GET['fileID']) : '' ?>"
                            method="POST">
                            <div class="mb-3">
                                <label class="form-label">Instructions (optional)</label>
                                <input type="text" name="instructions" class="form-control" placeholder="e.g. 3 levels depth" />
                            </div>
                            <button type="submit" class="btn btn-primary" style="background-color: #A855F7; border: none;">Generate Mindmap</button>
                        </form>
                    </div>
                </div>

                <!-- Mindmap Display -->
                <div class="mt-3">
                    <div id="mindmap-container">
                        <!-- Mindmap will be injected here -->
                    </div>
                </div>

                <!-- Saved Mindmaps -->
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Saved Mindmaps</h5>
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
                                                <li><a class="dropdown-item" href="<?= BASE_PATH ?>lm/deleteMindmap?mindmapID=<?= htmlspecialchars($mindmap['mindmapID'])?>&fileID=<?= htmlspecialchars($file['fileID']) ?>">Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <div class="list-group-item text-muted text-center">No saved mindmaps yet</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/markmap-autoloader@0.18"></script>

    <script>
        // Handle generating new mindmap
        document.getElementById('mindmapForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const data = new FormData(form);
            const container = document.getElementById('mindmap-container');
            container.innerHTML = '<p class="text-center p-3">Generating mindmap...</p>';

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: data
                });
                const json = await res.json();

                if (json.success && json.markdown) {
                    renderAutoloadMindmap(json.markdown);
                } else {
                    container.innerHTML = `<div class="alert alert-danger">Error: ${json.message || 'Failed to generate mindmap'}</div>`;
                }
            } catch (err) {
                container.innerHTML = `<div class="alert alert-danger">Error: ${err.message}</div>`;
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const selectedMindmap = document.getElementById('mindmap-container');
            if (selectedMindmap.innerHTML) {
                selectedMindmap.innerHTML = '<p class="text-center p-3"> Select a mindmap</p>';
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
                const res = await fetch(`<?= BASE_PATH ?>lm/viewMindmap?id=${id}&fileID=<?= isset($_GET['fileID']) ? htmlspecialchars($_GET['fileID']) : '' ?>`);
                const json = await res.json();

                if (json.success && json.markdown) {
                    renderAutoloadMindmap(json.markdown);
                } else {
                    container.innerHTML = `<div class="alert alert-danger">Error: ${json.message || 'Failed to load mindmap'}</div>`;
                }
            } catch (err) {
                container.innerHTML = `<div class="alert alert-danger">Error: ${err.message}</div>`;
            }
        });

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