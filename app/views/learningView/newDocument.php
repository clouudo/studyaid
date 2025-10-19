<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Document - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app\views\sidebar.php'; ?>
        <main class="flex-grow-1 p-3">
            <div class="container">
                <h3 class="mb-4">Upload Document</h3>
                <form action="index.php?url=lm/uploadDocument" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="documentName" class="form-label">Document Name</label>
                        <input type="text" class="form-control" id="documentName" name="documentName" placeholder="Enter document name">
                    </div>
                    <div class="mb-3">
                        <label for="folderSelect" class="form-label">Add to Folder</label>
                        <select class="form-select" id="folderSelect" name="folderSelect">
                            <option selected>Choose...</option>
                            <option value="1">Folder 1</option>
                            <option value="2">Folder 2</option>
                            <option value="3">Folder 3</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="dragDropArea" class="form-label">Drag and Drop Document</label>
                        <div id="dragDropArea" class="border rounded p-5 text-center bg-light d-flex flex-column justify-content-center align-items-center" style="min-height: 550px; cursor: pointer;" onclick="document.getElementById('documentFile').click();">
                            <span id="fileNameDisplay">Drag and drop your files here or click to upload</span>
                            <input type="file" id="documentFile" name="document" style="display: none;" accept="image/*,.pdf,.txt,.doc,.docx">
                            <p class="mt-3">Or</p>
                            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('documentFile').click();">Browse Files</button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                    <button type="button" class="btn btn-secondary">Cancel</button>
                </form>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script>
        document.getElementById('documentFile').addEventListener('change', function() {
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            if (this.files && this.files.length > 0) {
                fileNameDisplay.textContent = this.files[0].name;
            } else {
                fileNameDisplay.textContent = 'Drag and drop your files here or click to upload';
            }
        });
    </script>
</body>
</html>