<?php

namespace App\Controllers;

use App\Models\LmModel;

class LmController
{

    private $lmModel;
    public function __construct()
    {
        $this->lmModel = new LmModel();
    }

    public function uploadDocument()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {

            if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = "Missing Information. Please try again.";
                header('Location: index.php?url=lm/newDocument');
                exit();
            }

            $userId = 'guest';
            if (isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
            }

            // Retrieve folderId from POST, default to null if not set or empty
            $folderId = $_POST['folderSelect'] ?? null;
            if (empty($folderId) || $folderId == 0) { // Ensure it's truly null if no folder selected
                $folderId = null;
            }

            $file = $_FILES['document'];
            $uploadedFileName = $file['name'];
            $fileExtension = pathinfo($uploadedFileName, PATHINFO_EXTENSION);
            $tmpName = $file['tmp_name'];

            $documentNameFromPost = $_POST['documentName'] ?? '';
            $originalFileName = !empty($documentNameFromPost) ? $documentNameFromPost : $uploadedFileName;

            $extractedText = $this->lmModel->extractTextFromFile($tmpName, $fileExtension);

            $fileContent = file_get_contents($tmpName);

            if ($fileContent !== false) {
                try {
                    $newFileID = $this->lmModel->uploadFileToGCS($fileContent, $userId, $folderId, $file, $extractedText, $originalFileName);
                    $_SESSION['message'] = "File uploaded successfully!";
                    header('Location: index.php?url=lm/displayDocument&fileID=' . $newFileID);
                    exit();
                } catch (\Exception $e) {
                    $_SESSION['error'] = "Error uploading file: " . $e->getMessage();
                    header('Location: index.php?url=lm/newDocument');
                    exit();
                }
            } else {
                $_SESSION['error'] = "Error reading uploaded file.";
                header('Location: index.php?url=lm/newDocument');
                exit();
            }
        }
        require_once __DIR__ . '/../views/learningView/newDocument.php';
    }

    public function displayLearningMaterials()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth/home');
            exit();
        }

        $userId = $_SESSION['user_id'];
        $currentFolderId = $_GET['folder_id'] ?? null;

        // Fetch folders and files for the specified parent folder
        $fileList = $this->lmModel->getFoldersAndFiles($userId, $currentFolderId);

        // Prepare data for breadcrumbs
        $currentFolderName = 'Home';
        $currentFolderPath = [];
        if ($currentFolderId !== null) {
            $currentFolderPath = $this->_buildFolderPath($currentFolderId);
            // Get the name of the current folder for display
            $folderInfo = $this->lmModel->getFolderInfo($currentFolderId); // Assuming a method to get folder details
            if ($folderInfo) {
                $currentFolderName = $folderInfo['name'];
            }
        }

        // Pass data to the view
        require_once __DIR__ . '/../views/learningView/allDocument.php';
    }

    public function displayDocument()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth/home');
            exit();
        }

        if (isset($_GET['fileID'])) {
            $fileID = $_GET['fileID'];
            try {
                $documentData = $this->lmModel->getDocumentContent($fileID, $_SESSION['user_id']);
                require_once __DIR__ . '/../views/learningView/displayDocument.php';
            } catch (\Exception $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                header('Location: index.php?url=lm/displayLearningMaterials');
                exit();
            }
        } else {
            $_SESSION['error'] = "File ID not provided.";
            header('Location: index.php?url=lm/displayLearningMaterials');
            exit();
        }
    }

    public function deleteDocument(){
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth/home');
            exit();
        }

        if (isset($_GET['fileID'])) {
            $fileID = $_GET['fileID'];
            try {
                if ($this->lmModel->deleteDocument($fileID, $_SESSION['user_id'])) {
                    $_SESSION['message'] = "Document deleted successfully.";
                } else {
                    $_SESSION['error'] = "Failed to delete document.";
                }
            } catch (\Exception $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
            }
        }
        header('Location: index.php?url=lm/displayLearningMaterials');
        exit();
    }

    public function newFolder(){
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth/home');
            exit();
        }
        $folders = $this->lmModel->getFoldersAndFiles($_SESSION['user_id'])['folders']; // Get only folders
        require_once __DIR__ . '/../views/learningView/newFolder.php';
    }

    public function createFolder(){

        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth/home');
            exit();
        }

        if(isset($_POST['folderSelect'])){
            $parentFolderId = $_POST['folderSelect'];
        } else {
            $parentFolderId = null;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['folderName'])) {
            $folderName = trim($_POST['folderName']);
            if (!empty($folderName)) {
                $parentFolderId = !empty($_POST['parentFolderId']) ? $_POST['parentFolderId'] : null;
                try {
                    $this->lmModel->createFolder($_SESSION['user_id'], $folderName, $parentFolderId);
                    $_SESSION['message'] = "Folder created successfully.";
                    header('Location: index.php?url=lm/displayLearningMaterials');
                    exit();
                } catch (\Exception $e) {
                    $_SESSION['error'] = "Error creating folder: " . $e->getMessage();
                    header('Location: index.php?url=lm/newFolder');
                    exit();
                }
            } else {
                $_SESSION['error'] = "Folder name cannot be empty.";
                header('Location: index.php?url=lm/newFolder');
                exit();
            }
        }
        require_once __DIR__ . '/../views/learningView/newFolder.php';
    }

    public function deleteFolder(){
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth/home');
            exit();
        }

        if (isset($_GET['folderID'])) {
            $folderID = $_GET['folderID'];
            try {
                if ($this->lmModel->deleteFolder($folderID, $_SESSION['user_id'])) { // Assuming deleteFolder in model
                    $_SESSION['message'] = "Folder deleted successfully.";
                } else {
                    $_SESSION['error'] = "Failed to delete folder.";
                }
            } catch (\Exception $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
            }
        }
        header('Location: index.php?url=lm/displayLearningMaterials');
        exit();
    }

    public function newDocument(){
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth/home');
            exit();
        }

        $userId = $_SESSION['user_id'];
        $folders = $this->lmModel->getFoldersAndFiles($userId)['folders']; // Get only folders

        require_once __DIR__ . '/../views/learningView/newDocument.php';
    }

    private function _buildFolderPath($folderId)
    {
        $path = [];
        $currentId = $folderId;
        while ($currentId !== null) {
            $folder = $this->lmModel->getFolderInfo($currentId); // Assuming getFolderInfo in model
            if ($folder) {
                array_unshift($path, ['id' => $folder['folderID'], 'name' => $folder['name']]);
                $currentId = $folder['parentFolderId'];
            } else {
                break;
            }
        }
        return $path;
    }
    
}
