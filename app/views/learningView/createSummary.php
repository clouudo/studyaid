<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Summary - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include SIDEBAR ?>
        <main class="flex-grow-1 p-3">
            <div class="container">
                <h3 class="mb-4" style="color: #A855F7;">Generate Summary</h3>

                <div class="card mb-3">
                    <div class="card-body">
                        <form id="createSummaryForm">
                            <div class="mb-3">
                                <label class="form-label">File ID (optional)</label>
                                <input type="number" name="fileID" class="form-control" placeholder="e.g. 12" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Or paste text</label>
                                <textarea name="text" rows="6" class="form-control" placeholder="Paste content here..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Instructions (optional)</label>
                                <input type="text" name="instructions" class="form-control" placeholder="e.g. In paragraph format" />
                            </div>
                            <button type="submit" class="btn btn-primary" style="background-color: #A855F7; border: none;">Generate Summary</button>
                        </form>
                    </div>
                </div>

                <div class="mt-3">
                    <h5>Result</h5>
                    <pre id="createSummaryResult" class="p-3 bg-light border" style="white-space: pre-wrap;">Awaiting generation...</pre>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        document.getElementById('createSummaryForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const output = document.getElementById('createSummaryResult');
            output.textContent = 'Generating...';
            try {
                const res = await fetch('<?= BASE_PATH ?>lm/generateNotes', {
                    method: 'POST',
                    body: new FormData(form)
                });
                const json = await res.json();
                output.textContent = json.success ? json.content : ('Error: ' + json.message);
            } catch (error) {
                output.textContent = 'Error: ' + error.message;
            }
        });
    </script>
</body>

</html>
