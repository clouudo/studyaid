<?php

namespace App\Controllers;

use App\Models\LmModel;
use App\Services\GeminiService;

class LmController
{

    private $lmModel;
    private $gemini;
    public function __construct()
    {
        $this->lmModel = new LmModel();
        $this->gemini = new GeminiService();
    }

    public function uploadDocument()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {

            if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = "Missing Information. Please try again.";
                header('Location: ' . BASE_PATH . 'lm/newDocument');
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
                    header('Location: ' . BASE_PATH . 'lm/displayDocument?fileID=' . $newFileID);
                    exit();
                } catch (\Exception $e) {
                    $_SESSION['error'] = "Error uploading file: " . $e->getMessage();
                    header('Location: ' . BASE_PATH . 'lm/newDocument');
                    exit();
                }
            } else {
                $_SESSION['error'] = "Error reading uploaded file.";
                header('Location: ' . BASE_PATH . 'lm/newDocument');
                exit();
            }
        }
        require_once __DIR__ . '/../views/learningView/newDocument.php';
    }

    public function displayLearningMaterials()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . 'auth/home');
            exit();
        }

        $userId = $_SESSION['user_id'];
        $searchQuery = $_GET['search'] ?? null;

        if ($searchQuery) {
            $fileList = $this->lmModel->searchFilesAndFolders($userId, $searchQuery);
            $currentFolderName = 'Search Results for "' . htmlspecialchars($searchQuery) . '"';
            $currentFolderId = null;
            $currentFolderPath = [];
        } else {
            $currentFolderId = $_GET['folder_id'] ?? null;
            $fileList = $this->lmModel->getFoldersAndFiles($userId, $currentFolderId);
            $currentFolderName = 'Home';
            $currentFolderPath = [];
            if ($currentFolderId !== null) {
                $currentFolderPath = $this->_buildFolderPath($currentFolderId);
                $folderInfo = $this->lmModel->getFolderInfo($currentFolderId);
                if ($folderInfo) {
                    $currentFolderName = $folderInfo['name'];
                }
            }
        }

        // Fetch all folders for the modals
        $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);

        // Pass data to the view
        require_once __DIR__ . '/../views/learningView/allDocument.php';
    }

    public function displayDocument()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . 'auth/home');
            exit();
        }

        if (isset($_GET['fileID'])) {
            $fileID = $_GET['fileID'];
            try {
                $file = $this->lmModel->getFile($_SESSION['user_id'], $fileID);
                $documentData = $this->lmModel->getDocumentContent($fileID, $_SESSION['user_id']);
                require_once __DIR__ . '/../views/learningView/displayDocument.php';
            } catch (\Exception $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
                header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
                exit();
            }
        } else {
            $_SESSION['error'] = "File ID not provided.";
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
            exit();
        }
    }

    public function deleteDocument()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . 'auth/home');
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
        header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
        exit();
    }

    public function newFolder()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . 'auth/home');
            exit();
        }
        $userId = $_SESSION['user_id'];
        $folders = $this->lmModel->getAllFoldersForUser($userId);
        require_once __DIR__ . '/../views/learningView/newFolder.php';
    }

    public function createFolder()
    {

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . 'auth/home');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['folderName'])) {
            $folderName = trim($_POST['folderName']);
            $parentFolderId = !empty($_POST['parentFolderId']) ? $_POST['parentFolderId'] : null;

            if (empty($folderName)) {
                $_SESSION['error'] = "Folder name cannot be empty.";
                header('Location: ' . BASE_PATH . 'lm/newFolder');
                exit();
            }

            try {
                $this->lmModel->createFolder($_SESSION['user_id'], $folderName, $parentFolderId);
                $_SESSION['message'] = "Folder created successfully.";
                header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
                exit();
            } catch (\Exception $e) {
                $_SESSION['error'] = "Error creating folder: " . $e->getMessage();
                header('Location: ' . BASE_PATH . 'lm/newFolder');
                exit();
            }
        } else {
            // If not a POST request, just show the form
            header('Location: ' . BASE_PATH . 'lm/newFolder');
            exit();
        }
    }

    public function deleteFolder()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . 'auth/home');
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
        header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
        exit();
    }

    public function newDocument()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . 'auth/home');
            exit();
        }

        $userId = $_SESSION['user_id'];
        $folders = $this->lmModel->getAllFoldersForUser($userId);

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

    public function renameFolder()
    {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['folderId']) && isset($_POST['newName'])) {
            $folderId = $_POST['folderId'];
            $newName = trim($_POST['newName']);
            $userId = $_SESSION['user_id'];

            if (empty($newName)) {
                echo json_encode(['success' => false, 'message' => 'Folder name cannot be empty.']);
                exit();
            }

            try {
                $success = $this->lmModel->renameFolder($folderId, $newName, $userId);
                if ($success) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to rename folder in database.']);
                }
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        }
        exit();
    }

    public function renameFile()
    {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fileId']) && isset($_POST['newName'])) {
            $fileId = $_POST['fileId'];
            $newName = trim($_POST['newName']);
            $userId = $_SESSION['user_id'];

            if (empty($newName)) {
                echo json_encode(['success' => false, 'message' => 'Document name cannot be empty.']);
                exit();
            }

            try {
                $success = $this->lmModel->renameFile($fileId, $newName, $userId);
                if ($success) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to rename document in database.']);
                }
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        }
        exit();
    }

    public function moveFile()
    {
        // error_log("moveFile controller method triggered.");
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            // error_log("moveFile error: User not logged in.");
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fileId']) && isset($_POST['newFolderId'])) {
            $fileId = $_POST['fileId'];
            $newFolderId = $_POST['newFolderId'] == '0' ? null : $_POST['newFolderId'];
            $userId = $_SESSION['user_id'];
            // error_log("moveFile params: fileId={$fileId}, newFolderId={$newFolderId}, userId={$userId}");

            try {
                $success = $this->lmModel->moveFile($fileId, $newFolderId, $userId);
                if ($success) {
                    // error_log("moveFile success for fileId: {$fileId}");
                    echo json_encode(['success' => true]);
                } else {
                    // error_log("moveFile failure for fileId: {$fileId}");
                    echo json_encode(['success' => false, 'message' => 'Failed to move document in database.']);
                }
            } catch (\Exception $e) {
                // error_log("moveFile exception for fileId: {$fileId}. Error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
        } else {
            // error_log("moveFile error: Invalid request method or missing parameters.");
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        }
        exit();
    }

    public function moveFolder()
    {
        // error_log("moveFolder controller method triggered.");
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            // error_log("moveFolder error: User not logged in.");
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['folderId']) && isset($_POST['newFolderId'])) {
            $folderId = $_POST['folderId'];
            $newFolderId = $_POST['newFolderId'] == '0' ? null : $_POST['newFolderId']; // Allow moving to root
            $userId = $_SESSION['user_id'];
            // error_log("moveFolder params: folderId={$folderId}, newFolderId={$newFolderId}, userId={$userId}");

            try {
                $success = $this->lmModel->moveFolder($folderId, $newFolderId, $userId);
                if ($success) {
                    // error_log("moveFolder success for folderId: {$folderId}");
                    echo json_encode(['success' => true]);
                } else {
                    // error_log("moveFolder failure for folderId: {$folderId}");
                    echo json_encode(['success' => false, 'message' => 'Failed to move folder.']);
                }
            } catch (\Exception $e) {
                // error_log("moveFolder exception for folderId: {$folderId}. Error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            }
        } else {
            // error_log("moveFolder error: Invalid request method or missing parameters.");
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
        }
        exit();
    }

    public function summary()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . 'auth/home');
            exit();
        }
        $fileId = (int)$_GET['fileID'];
        $userId = (int)$_SESSION['user_id'];
        $file = $this->lmModel->getFile($userId, $fileId);

        $summaryList = $this->lmModel->getSummaryByFile($fileId, $userId);

        require_once __DIR__ . '/../views/learningView/summary.php';
    }

    public function note()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . 'auth/home');
            exit();
        }

        require_once __DIR__ . '/../views/learningView/note.php';
    }

    public function mindmap()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . 'auth/home');
            exit();
        }

        require_once __DIR__ . '/../views/learningView/mindmap.php';
    }

    public function generateSummary()
    {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit();
        }

        $userId = (int)$_SESSION['user_id'];
        $fileId = (int)$_GET['fileID'];

        $instructions = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['instructions'])) {
                $instructions = trim($_POST['instructions']);
            }
        }
        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            $extracted_text = $file['extracted_text'];
            if (!$extracted_text) {
                echo json_encode(['success' => false, 'message' => 'No extracted text found']);
                exit();
            }

            if ($instructions !== '') {
                $context = $instructions;
            } else {
                $context = "In paragraph format";
            }

            $generatedSummary = $this->gemini->generateSummary($extracted_text, $context);
            $this->lmModel->saveSummary($fileId, $userId, 'Summary - ' . $file['name'], $generatedSummary);
            echo json_encode(['success' => true, 'content' => $generatedSummary]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    public function generateNotes()
    {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit();
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
            exit();
        }
        $userId = (int)$_SESSION['user_id'];
        $fileId = isset($_POST['fileID']) ? (int)$_POST['fileID'] : 0;
        $instructions = $_POST['instructions'] ?? null;
        $rawText = $_POST['text'] ?? null;
        try {
            $sourceText = $rawText;
            if (!$sourceText) {
                $doc = $this->lmModel->getDocumentContent($fileId, $userId);
                $sourceText = $doc['extracted_text'] ?: (is_string($doc['content']) ? $doc['content'] : '');
            }
            $result = $this->gemini->generateNotes($sourceText, $instructions);
            $id = 0;
            if ($fileId) {
                $file = $this->lmModel->getFile($userId, $fileId);
                $title = 'Notes - ' . ($file ? $file['name'] : 'Untitled');
                $id = $this->lmModel->saveNotes($fileId, $title, $result);
            }
            echo json_encode(['success' => true, 'id' => $id, 'content' => $result]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    public function generateMindmap()
    {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in.']);
            exit();
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
            exit();
        }
        $userId = (int)$_SESSION['user_id'];
        $fileId = isset($_POST['fileID']) ? (int)$_POST['fileID'] : 0;
        $instructions = $_POST['instructions'] ?? null;
        $rawText = $_POST['text'] ?? null;
        try {
            $sourceText = $rawText;
            if (!$sourceText) {
                $doc = $this->lmModel->getDocumentContent($fileId, $userId);
                $sourceText = $doc['extracted_text'] ?: (is_string($doc['content']) ? $doc['content'] : '');
            }
            $json = $this->gemini->generateMindmapJson($sourceText, $instructions);
            $id = 0;
            if ($fileId) {
                $file = $this->lmModel->getFile($userId, $fileId);
                $title = 'Mindmap - ' . ($file ? $file['name'] : 'Untitled');
                $id = $this->lmModel->saveMindmap($fileId, $title, $json, '');
            }
            echo json_encode(['success' => true, 'id' => $id, 'json' => $json]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }
}
