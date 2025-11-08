<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extracted Text - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.7/dist/purify.min.js"></script>
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include VIEW_SIDEBAR; ?>
        <main class="flex-grow-1 p-3">
            <div class="container">
                <h3 class="mb-4" style="color: #A855F7;">Note</h3>
                <h4 class="mb-4"><?php echo $file['name']; ?></h4>
                <?php require_once VIEW_NAVBAR; ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <form id="noteForm" action="<?= GENERATE_NOTES ?>" method="POST">
                            <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
                            <div class="mb-3">
                                <label class="form-label">Instructions (optional)</label>
                                <input type="text" class="form-control mb-3" id="instructions" name="instructions" placeholder="Describe your instructions">
                            </div>
                            <button type="submit" class="btn btn-primary" style="background-color: #A855F7; border: none;">Generate Note</button>
                        </form>
                    </div>
                </div>
                <div class="card mb-3">
                    <form id="noteEditor" action="<?= SAVE_NOTE ?>" method="POST">
                        <input type="hidden" name="file_id" value="<?php echo isset($file['fileID']) ? htmlspecialchars($file['fileID']) : ''; ?>">
                        <div class="card-header">
                            <label for="noteTitle" class="form-label">Add Note</label>
                            <div class="d-flex justify-content-between align-items-center">
                                <input type="text" class="form-control" id="noteTitle" name="noteTitle" placeholder="Enter note title">
                                <a class="btn btn-sm me-2 btn-toggle" data-bs-toggle="collapse" aria-expanded="true" data-bs-target="#noteEditorPanel"><i class="bi bi-chevron-down"></i></a>
                            </div>
                        </div>
                        <div class="card-body collapse" id="noteEditorPanel">
                            <div class="btn-toolbar mb-2" role="toolbar" id="toolbar">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"><i class="bi bi-type-bold"></i></button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"><i class="bi bi-type-italic"></i></button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"><i class="bi bi-type-h1"></i></button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ul"></i></button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"><i class="bi bi-list-ol"></i></button>
                                </div>
                            </div>
                            <textarea class="form-control mb-3" id="noteContent" name="noteContent" placeholder="Enter note content" style="min-height:120px; overflow:hidden; resize:none;"></textarea>
                            <div id="preview" class="bg-light border px-2 py-2 mb-3" style="min-height:120px"></div>
                            <button type="submit" class="btn btn-primary" style="background-color: #A855F7; border: none;">Save Note</button>
                        </div>
                    </form>
                </div>
                <div class="mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Saved Notes</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush" id="noteList">
                            <?php if ($noteList): ?>
                                <?php foreach ($noteList as $note): ?>
                                    <div class="card mb-3">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <?php echo $note['title'] ?>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownFileActions<?php echo $note['noteID']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownFileActions<?php echo $note['noteID']; ?>">
                                                        <li>
                                                            <form method="POST" action="<?= EXPORT_NOTE_PDF ?>" style="display: inline;">
                                                                <input type="hidden" name="note_id" value="<?= htmlspecialchars($note['noteID']) ?>">
                                                                <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                                <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Export as PDF</button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="POST" action="<?= EXPORT_NOTE_DOCX ?>" style="display: inline;">
                                                                <input type="hidden" name="note_id" value="<?= htmlspecialchars($note['noteID']) ?>">
                                                                <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                                <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Export as DOCX</button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="POST" action="<?= EXPORT_NOTE_TXT ?>" style="display: inline;">
                                                                <input type="hidden" name="note_id" value="<?= htmlspecialchars($note['noteID']) ?>">
                                                                <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                                <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Export as TXT</button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                        <li>
                                                            <form method="POST" action="<?= DELETE_NOTE ?>" style="display: inline;">
                                                                <input type="hidden" name="note_id" value="<?= htmlspecialchars($note['noteID']) ?>">
                                                                <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                                <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Delete</button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="POST" action="<?= SAVE_NOTE_AS_FILE ?>" style="display: inline;">
                                                                <input type="hidden" name="note_id" value="<?= htmlspecialchars($note['noteID']) ?>">
                                                                <input type="hidden" name="file_id" value="<?= htmlspecialchars($file['fileID']) ?>">
                                                                <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">Save as File</button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <button class="btn btn-sm me-2 view-btn" data-bs-toggle="collapse" aria-expanded="false" data-bs-target="#noteContent-<?php echo $note['noteID']; ?>"><i class=" bi bi-chevron-down"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body collapse" id="noteContent-<?php echo $note['noteID']; ?>">
                                            <div class="noteText"><?php echo htmlspecialchars($note['content']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        document.getElementById('noteEditor').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;

            try {
                const data = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: data
                });
                const json = await res.json();
                if (json.success) {
                    location.reload();
                } else {
                    alert("Title or content missing!");
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });

        document.getElementById('noteForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            try {
                const data = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    body: data
                });
                const json = await res.json();
                if (json.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (json.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        })

        // Auto-resize textarea function
        function autoResizeTextarea(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const noteContent = document.getElementById('noteContent');
            if (noteContent) {
                // Initial resize
                autoResizeTextarea(noteContent);

                // Resize on input
                noteContent.addEventListener('input', function() {
                    autoResizeTextarea(this);
                });
            }

            document.querySelectorAll('.noteText')
                .forEach(function(div) {
                    div.innerHTML = marked.parse(div.textContent);
                })
        })
    </script>
</body>

</html>