<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extracted Text - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app\views\sidebar.php'; ?>
        <main class="flex-grow-1 p-3">
            <div class="container">
                <h3 class="mb-4">Summary</h3>
                <?php require_once 'app\views\learningView\navbar.php'; ?>
                <div class="card">
                    <div class="card-body">
                        <a id="genSummary" href="<?= BASE_PATH ?>lm/generateSummary?fileID=<?php echo $_GET['fileID']; ?>" class="btn btn-primary mb-3">Summarize</a>
                        <div id="checkBox" class="button-group" role="group">
                            <input type="checkbox" class="btn-check" id="bulletpoint" autocomplete="off">
                            <label for="bulletpoint" class="btn btn-outline-primary">Bullet Point</label>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <h5>Result</h5>
                    <pre id="summaryResult" class="p-3 bg-light border" style="white-space: pre-wrap;"><?php echo 'Generate Summary To View Result'; ?></pre>
                    <?php if ($summaryList): ?>
                        <?php foreach ($summaryList as $summary): ?>
                            <div class="card mb-3">
                                <div class="card-header">
                                    <?php echo $summary['title'] . ' - ' . $summary['createdAt']; ?>
                                </div>
                                <div class="card-body">
                                    <div class="summaryContent" style="white-space: pre-wrap;"><?php echo htmlspecialchars($summary['content']); ?></div>
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
        document.getElementById('genSummary').addEventListener('click', async (e) => {
            e.preventDefault();
            const bulletpoint = document.getElementById('bulletpoint').checked;
            const apiUrl = `<?= BASE_PATH ?>lm/generateSummary?fileID=<?= (int)$_GET['fileID']; ?>&bulletpoint=${bulletpoint}`;
            const output = document.getElementById('summaryResult');
            output.textContent = "Generating...";
            try {
                const res = await fetch(apiUrl);
                const json = await res.json();
                output.textContent = json.success ? "Finished Generating Summary" : ('Error: ' + json.message);
                location.reload();
            } catch (error) {
                output.textContent = 'Error: ' + error.message;
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.summaryContent').forEach(function(div) {
                div.innerHTML = marked.parse(div.textContent);
            });
        });
    </script>
</body>

</html>