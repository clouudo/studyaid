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
        <?php include 'app\views\sidebar.php'; ?>
        <main class="flex-grow-1 p-3">
            <div class="container">
                <h3 class="mb-4" style="color: #A855F7;">Note</h3>
                <h4 class="mb-4"><?php echo $file['name']; ?></h4>
                <?php require_once 'app\views\learningView\navbar.php'; ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <form id="noteForm" action="<?= BASE_PATH ?>lm/generateNotes?fileID=<?php echo $_GET['fileID']; ?>" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Instructions (optional)</label>
                                <input type="text" class="form-control mb-3" id="instructions" name="instructions" placeholder="Describe your instructions">
                            </div>
                            <button type="submit" class="btn btn-primary" style="background-color: #A855F7; border: none;">Generate Note</button>
                        </form>
                    </div>
                </div>
                <div class="card mb-3">
                    <form id="noteEditor" action="<?= BASE_PATH ?>lm/saveNote?fileID=<?php echo $_GET['fileID']; ?>" method="POST">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <input type="text" class="form-control" id="noteTitle" name="noteTitle" placeholder="Enter note title">
                            <a class="btn btn-sm me-2 btn-toggle" data-bs-toggle="collapse" aria-expanded="true" data-bs-target="#noteEditorPanel"><i class="bi bi-chevron-down"></i></a>
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
                                            <li><a class="dropdown-item" href="<?= BASE_PATH ?>lm/deleteNote?noteID=<?= htmlspecialchars($note['noteID']) ?>">Delete</a></li>
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

        <?php include 'app\views\learningView\editor.js'; ?>
    </script>
</body>

</html>