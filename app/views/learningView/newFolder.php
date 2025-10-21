<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Folder - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app\views\sidebar.php'; ?>
        <main class="flex-grow-1 p-3">
            <div class="container">
                <?php
                if (isset($_SESSION['message'])):
                ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $_SESSION['message']; ?>
                    </div>
                <?php 
                    unset($_SESSION['message']);
                endif;

                if (isset($_SESSION['error'])):
                ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $_SESSION['error']; ?>
                    </div>
                <?php
                    unset($_SESSION['error']);
                endif;
                ?>
                <h3 class="mb-4">Create New Folder</h3>
                <form action="index.php?url=lm/createFolder" method="POST">
                    <div class="mb-3">
                    <label for="folderName" class="form-label">Folder Name</label>
                        <input type="text" class="form-control" id="folderName" name="folderName" placeholder="Enter folder name" required>
                    </div>
                    <div class="mb-3">
                        <label for="parentFolderId" class="form-label">Add to folder</label>
                        <select class="form-select" id="parentFolderId" name="parentFolderId">
                            <option value="">Choose...</option>
                            <?php if (!empty($folders)): ?>
                                <?php foreach ($folders as $folder): ?>
                                    <option value="<?php echo htmlspecialchars($folder['folderID']); ?>">
                                        <?php echo htmlspecialchars($folder['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>  
                    <button type="submit" class="btn btn-primary">Create Folder</button>
                    <button type="button" class="btn btn-secondary" onclick="window.history.back();">Cancel</button>
                </form>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>