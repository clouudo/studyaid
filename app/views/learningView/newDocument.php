<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Document - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'app\views\sidebar.php'; ?>
        <main class="flex-grow-1 p-3">
            <div class="container">
                <h3 class="mb-4">Create New Document</h3>
                <form>
                    <div class="mb-3">
                        <label for="documentName" class="form-label">Document Name</label>
                        <input type="text" class="form-control" id="documentName" placeholder="Enter document name">
                    </div>
                    <div class="mb-3">
                        <label for="folderSelect" class="form-label">Add to Folder</label>
                        <select class="form-select" id="folderSelect">
                            <option selected>Choose...</option>
                            <option value="1">Folder 1</option>
                            <option value="2">Folder 2</option>
                            <option value="3">Folder 3</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="dragDropArea" class="form-label">Drag and Drop Document</label>
                        <div id="dragDropArea" class="border rounded p-5 text-center bg-light" style="min-height: 200px;">
                            Drag and drop your files here or click to upload
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create</button>
                    <button type="button" class="btn btn-secondary">Cancel</button>
                </form>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>