<?php ob_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($currentFolderName ?? 'Folder'); ?> - StudyAid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../public/css/style.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <div class="d-flex flex-grow-1">
        <?php include 'app/views/sidebar.php'; ?>
        <main class="flex-grow-1 p-3">
            <div class="container">
                <?php
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }

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

                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?url=lm/displayLearningMaterials">Home</a></li>
                        <?php
                        // Assuming $currentFolderPath is an array of {id, name} for breadcrumbs
                        // Example: [['id' => 1, 'name' => 'Folder A'], ['id' => 2, 'name' => 'Subfolder B']]
                        if (isset($currentFolderPath) && is_array($currentFolderPath)) {
                            foreach ($currentFolderPath as $pathItem) {
                                echo '<li class="breadcrumb-item"><a href="index.php?url=lm/displayLearningMaterials&folder_id=' . $pathItem['id'] . '">' . htmlspecialchars($pathItem['name']) . '</a></li>';
                            }
                        }
                        ?>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($currentFolderName ?? 'Current Folder'); ?></li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0"><?php echo htmlspecialchars($currentFolderName ?? 'Current Folder'); ?> Contents</h3>
                    <div>
                        <a href="index.php?url=lm/newFolder" class="btn btn-primary me-2">New Folder</a>
                        <a href="index.php?url=lm/newDocument" class="btn btn-success">Upload File</a>
                    </div>
                </div>

                <div class="list-group">
                    <?php if (!empty($fileList['folders']) || !empty($fileList['files'])): ?>
                        <?php foreach ($fileList['folders'] as $folder): ?>
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <a class="text-decoration-none text-dark flex-grow-1" href="index.php?url=lm/displayLearningMaterials&folder_id=<?php echo $folder['folderID'] ?>">
                                    <i class="bi bi-folder-fill me-2"></i>
                                    <strong><?php echo htmlspecialchars($folder['name']); ?></strong>
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownFolderActions<?php echo $folder['folderID']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownFolderActions<?php echo $folder['folderID']; ?>">
                                        <li><a class="dropdown-item" href="#">Rename</a></li>
                                        <li><a class="dropdown-item" href="index.php?url=lm/deleteFolder&folderID=<?php echo $folder['folderID'] ?>">Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php foreach ($fileList['files'] as $file): ?>
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <a class="text-decoration-none text-dark flex-grow-1" href="index.php?url=lm/displayDocument&fileID=<?php echo $file['fileID'] ?>">
                                    <?php
                                    $fileIcon = 'bi-file-earmark'; // Default icon
                                    $fileTypeLower = strtolower($file['fileType']);
                                    if ($fileTypeLower == 'pdf') {
                                        $fileIcon = 'bi-file-earmark-pdf';
                                    } elseif ($fileTypeLower == 'doc' || $fileTypeLower == 'docx') {
                                        $fileIcon = 'bi-file-earmark-word';
                                    } elseif ($fileTypeLower == 'xls' || $fileTypeLower == 'xlsx') {
                                        $fileIcon = 'bi-file-earmark-excel';
                                    } elseif ($fileTypeLower == 'ppt' || $fileTypeLower == 'pptx') {
                                        $fileIcon = 'bi-file-earmark-ppt';
                                    } elseif ($fileTypeLower == 'jpg' || $fileTypeLower == 'jpeg' || $fileTypeLower == 'png' || $fileTypeLower == 'gif') {
                                        $fileIcon = 'bi-file-earmark-image';
                                    } elseif ($fileTypeLower == 'txt') {
                                        $fileIcon = 'bi-file-earmark-text';
                                    } elseif ($fileTypeLower == 'zip') {
                                        $fileIcon = 'bi-file-earmark-zip';
                                    }
                                    ?>
                                    <i class="bi <?php echo $fileIcon; ?> me-2"></i>
                                    <strong><?php echo htmlspecialchars($file['name']); ?></strong>
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownFileActions<?php echo $file['fileID']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownFileActions<?php echo $file['fileID']; ?>">
                                        <li><a class="dropdown-item" href="index.php?url=lm/displayDocument&fileID=<?php echo $file['fileID'] ?>">View</a></li>
                                        <li><a class="dropdown-item" href="#">Rename</a></li>
                                        <li><a class="dropdown-item" href="index.php?url=lm/deleteDocument&fileID=<?php echo $file['fileID'] ?>">Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>This folder is empty.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

</html>
<?php ob_end_flush(); ?>