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

    // ============================================================================
    // UTILITY METHODS
    // ============================================================================

    public function checkSession($isJsonResponse = false){
        if (!isset($_SESSION['user_id'])) {
            if ($isJsonResponse) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'User not logged in.']);
                exit();
            } else {
                header('Location: ' . BASE_PATH . 'auth/home');
                exit();
            }
        }
    }

    // ============================================================================
    // NEW DOCUMENT PAGE (newDocument.php)
    // ============================================================================

    /**
     * VIEW: Display the new document upload form
     * ACTION: Handle document upload (POST)
     */
    public function uploadDocument()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
            if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = "Missing Information. Please try again.";
                header('Location: ' . BASE_PATH . 'lm/newDocument');
                exit();
            }
            
            $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'guest';
            $folderId = isset($_POST['folderSelect']) && !empty($_POST['folderSelect']) && $_POST['folderSelect'] != '0' 
                ? (int)$_POST['folderSelect'] 
                : null;
            
            $file = $_FILES['document'];
            $uploadedFileName = $file['name'];
            $fileExtension = pathinfo($uploadedFileName, PATHINFO_EXTENSION);
            $tmpName = $file['tmp_name'];
            
            $documentNameFromPost = isset($_POST['documentName']) ? trim($_POST['documentName']) : '';
            $originalFileName = !empty($documentNameFromPost) ? $documentNameFromPost : $uploadedFileName;
            
            $extractedText = $this->lmModel->extractTextFromFile($tmpName, $fileExtension);
            $fileContent = file_get_contents($tmpName);
            
            if ($fileContent === false) {
                $_SESSION['error'] = "Error reading uploaded file.";
                header('Location: ' . BASE_PATH . 'lm/newDocument');
                exit();
            }
            
            try {
                $newFileId = $this->lmModel->uploadFileToGCS($fileContent, $userId, $folderId, $file, $extractedText, $originalFileName);
                $_SESSION['message'] = "File uploaded successfully!";
                header('Location: ' . BASE_PATH . 'lm/displayDocument?fileID=' . $newFileId);
                exit();
            } catch (\Exception $e) {
                $_SESSION['error'] = "Error uploading file: " . $e->getMessage();
                header('Location: ' . BASE_PATH . 'lm/newDocument');
                exit();
            }
        }
        
        require_once __DIR__ . '/../views/learningView/newDocument.php';
    }

    // ============================================================================
    // ALL DOCUMENTS PAGE (allDocument.php)
    // ============================================================================

    /**
     * VIEW: Display all documents and folders
     */
    public function displayLearningMaterials()
    {
        $this->checkSession();
        
        $userId = (int)$_SESSION['user_id'];
        $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : null;
        $currentFolderId = isset($_GET['folder_id']) ? (int)$_GET['folder_id'] : null;
        
        if (!empty($searchQuery)) {
            $fileList = $this->lmModel->searchFilesAndFolders($userId, $searchQuery);
            $currentFolderName = 'Search Results for "' . htmlspecialchars($searchQuery) . '"';
            $currentFolderPath = [];
        } else {
            $fileList = $this->lmModel->getFoldersAndFiles($userId, $currentFolderId);
            $currentFolderName = 'Home';
            $currentFolderPath = [];
            
            if ($currentFolderId !== null) {
                $currentFolderPath = $this->_buildFolderPath($currentFolderId);
                $folderInfo = $this->lmModel->getFolderInfo($currentFolderId);
                if ($folderInfo && is_array($folderInfo)) {
                    $currentFolderName = $folderInfo['name'];
                }
            }
        }
        
        $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
        
        require_once __DIR__ . '/../views/learningView/allDocument.php';
    }

    /**
     * VIEW: Display a specific document
     */
    public function displayDocument()
    {
        $this->checkSession();
        
        $userId = (int)$_SESSION['user_id'];
        $fileId = isset($_GET['fileID']) ? (int)$_GET['fileID'] : 0;
        
        if ($fileId === 0) {
            $_SESSION['error'] = "File ID not provided.";
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
            exit();
        }
        
        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file || !is_array($file)) {
                $_SESSION['error'] = "File not found.";
                header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
                exit();
            }
            
            $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
            $documentData = $this->lmModel->getDocumentContent($fileId, $userId);
            
            require_once __DIR__ . '/../views/learningView/displayDocument.php';
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
            exit();
        }
    }

    /**
     * ACTION: Delete a document
     */
    public function deleteDocument()
    {
        $this->checkSession();
        
        $userId = (int)$_SESSION['user_id'];
        $fileId = isset($_GET['fileID']) ? (int)$_GET['fileID'] : 0;
        
        if ($fileId === 0) {
            $_SESSION['error'] = "File ID not provided.";
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
            exit();
        }
        
        try {
            if ($this->lmModel->deleteDocument($fileId, $userId)) {
                $_SESSION['message'] = "Document deleted successfully.";
            } else {
                $_SESSION['error'] = "Failed to delete document.";
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
        
        header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
        exit();
    }

    // ============================================================================
    // NEW FOLDER PAGE (newFolder.php)
    // ============================================================================

    /**
     * VIEW: Display the new folder creation form
     */
    public function newFolder()
    {
        $this->checkSession();
        
        $userId = (int)$_SESSION['user_id'];
        $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
        
        require_once __DIR__ . '/../views/learningView/newFolder.php';
    }

    /**
     * ACTION: Create a new folder (POST)
     */
    public function createFolder()
    {
        $this->checkSession();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['folderName'])) {
            header('Location: ' . BASE_PATH . 'lm/newFolder');
            exit();
        }
        
        $userId = (int)$_SESSION['user_id'];
        $folderName = trim($_POST['folderName']);
        $parentFolderId = !empty($_POST['parentFolderId']) ? (int)$_POST['parentFolderId'] : null;
        
        if (empty($folderName)) {
            $_SESSION['error'] = "Folder name cannot be empty.";
            header('Location: ' . BASE_PATH . 'lm/newFolder');
            exit();
        }
        
        try {
            $this->lmModel->createFolder($userId, $folderName, $parentFolderId);
            $_SESSION['message'] = "Folder created successfully.";
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
            exit();
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error creating folder: " . $e->getMessage();
            header('Location: ' . BASE_PATH . 'lm/newFolder');
            exit();
        }
    }

    /**
     * ACTION: Delete a folder
     */
    public function deleteFolder()
    {
        $this->checkSession();
        
        $userId = (int)$_SESSION['user_id'];
        $folderId = isset($_GET['folderID']) ? (int)$_GET['folderID'] : 0;
        
        if ($folderId === 0) {
            $_SESSION['error'] = "Folder ID not provided.";
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
            exit();
        }
        
        try {
            if ($this->lmModel->deleteFolder($folderId, $userId)) {
                $_SESSION['message'] = "Folder deleted successfully.";
            } else {
                $_SESSION['error'] = "Failed to delete folder.";
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
        
        header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
        exit();
    }

    /**
     * VIEW: Display the new document upload form
     */
    public function newDocument()
    {
        $this->checkSession();
        
        $userId = (int)$_SESSION['user_id'];
        $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
        
        require_once __DIR__ . '/../views/learningView/newDocument.php';
    }

    // ============================================================================
    // HELPER METHODS
    // ============================================================================

    /**
     * Helper: Build folder path breadcrumb
     */
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

    // ============================================================================
    // JSON API ACTIONS (Used by allDocument.php)
    // ============================================================================

    /**
     * ACTION (JSON API): Rename a folder
     */
    public function renameFolder()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['folderId']) || !isset($_POST['newName'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            exit();
        }
        
        $userId = (int)$_SESSION['user_id'];
        $folderId = (int)$_POST['folderId'];
        $newName = trim($_POST['newName']);
        
        if (empty($newName)) {
            echo json_encode(['success' => false, 'message' => 'Folder name cannot be empty.']);
            exit();
        }
        
        try {
            $success = $this->lmModel->renameFolder($folderId, $newName, $userId);
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to rename folder.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * ACTION (JSON API): Rename a file/document
     */
    public function renameFile()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['fileId']) || !isset($_POST['newName'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            exit();
        }
        
        $userId = (int)$_SESSION['user_id'];
        $fileId = (int)$_POST['fileId'];
        $newName = trim($_POST['newName']);
        
        if (empty($newName)) {
            echo json_encode(['success' => false, 'message' => 'Document name cannot be empty.']);
            exit();
        }
        
        try {
            $success = $this->lmModel->renameFile($fileId, $newName, $userId);
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to rename document.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * ACTION (JSON API): Move a file/document to another folder
     */
    public function moveFile()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['fileId']) || !isset($_POST['newFolderId'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            exit();
        }
        
        $userId = (int)$_SESSION['user_id'];
        $fileId = (int)$_POST['fileId'];
        $newFolderId = $_POST['newFolderId'] == '0' ? null : (int)$_POST['newFolderId'];
        
        try {
            $success = $this->lmModel->moveFile($fileId, $newFolderId, $userId);
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to move document.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * ACTION (JSON API): Move a folder to another folder
     */
    public function moveFolder()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['folderId']) || !isset($_POST['newFolderId'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            exit();
        }
        
        $userId = (int)$_SESSION['user_id'];
        $folderId = (int)$_POST['folderId'];
        $newFolderId = $_POST['newFolderId'] == '0' ? null : (int)$_POST['newFolderId'];
        
        try {
            $success = $this->lmModel->moveFolder($folderId, $newFolderId, $userId);
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to move folder.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    // ============================================================================
    // SUMMARY PAGE (summary.php)
    // ============================================================================

    /**
     * VIEW: Display summaries for a document
     */
    public function summary()
    {
        $this->checkSession();
        
        $userId = (int)$_SESSION['user_id'];
        $fileId = isset($_GET['fileID']) ? (int)$_GET['fileID'] : 0;
        
        if ($fileId === 0) {
            $_SESSION['error'] = "File ID not provided.";
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
            exit();
        }
        
        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file || !is_array($file)) {
                $_SESSION['error'] = "File not found.";
                header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
                exit();
            }
            
            $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
            $summaryList = $this->lmModel->getSummaryByFile($fileId, $userId);
            
            require_once __DIR__ . '/../views/learningView/summary.php';
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
            exit();
        }
    }

     /**
     * ACTION: Delete summary from database
     */
    public function deleteSummary(){
        $this->checkSession();
        $summaryId = isset($_GET['summaryID']) ? (int)$_GET['summaryID'] : 0;
        $fileId = isset($_GET['fileID']) ? (int)$_GET['fileID'] : 0;

        if ($summaryId === 0) {
            $_SESSION['error'] = "Summary ID not provided.";
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
            exit();
        }

        try{
            $this->lmModel->deleteSummary($summaryId);
            header('Location: ' . BASE_PATH . 'lm/summary?fileID=' . $fileId);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . BASE_PATH . 'lm/summary?fileID=' . $fileId);
            exit();
        }
    }

    public function saveSummaryAsFile(){
        $this->checkSession();
        $summaryId = isset($_GET['summaryID']) ? (int)$_GET['summaryID'] : 0;
        $fileId = isset($_GET['fileID']) ? (int)$_GET['fileID'] : 0;
        $folderId = isset($_GET['folderID']) ? (int)$_GET['folderID'] : 0;

        if ($summaryId === 0) {
            $_SESSION['error'] = "Summary ID not provided.";
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
            exit();
        }

        try{
            $this->lmModel->saveSummaryAsFile($summaryId, $fileId, $folderId);
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . BASE_PATH . 'lm/summary?fileID=' . $fileId);
            exit();
        }
    }

    // ============================================================================
    // NOTE PAGE (note.php)
    // ============================================================================

    /**
     * VIEW: Display notes for a document
     */
    public function note()
    {
        $this->checkSession();
        
        $userId = (int)$_SESSION['user_id'];
        $fileId = isset($_GET['fileID']) ? (int)$_GET['fileID'] : 0;
        
        if ($fileId === 0) {
            $_SESSION['error'] = "File ID not provided.";
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
            exit();
        }
        
        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file || !is_array($file)) {
                $_SESSION['error'] = "File not found.";
                header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
                exit();
            }
            
            $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
            $noteList = $this->lmModel->getNotesByFile($fileId);
            
            require_once __DIR__ . '/../views/learningView/note.php';
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
            exit();
        }
    }

     /**
     * ACTION: Delete note from database
     */
    public function deleteNote(){
        $this->checkSession();
        $noteId = isset($_GET['noteID']) ? (int)$_GET['noteID'] : 0;
        $fileId = isset($_GET['fileID']) ? (int)$_GET['fileID'] : 0;

        if ($noteId === 0) {
            $_SESSION['error'] = "Note ID not provided.";
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
            exit();
        }

        try{
            $this->lmModel->deleteNote($noteId);
            header('Location: ' . BASE_PATH . 'lm/note?fileID=' . $fileId);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . BASE_PATH . 'lm/note?fileID=' . $fileId);
            exit();
        }
    }

     /**
     * ACTION: Save note as file
     */
    public function saveNoteAsFile(){
        $this->checkSession();
        $noteId = isset($_GET['noteID']) ? (int)$_GET['noteID'] : 0;
        $fileId = isset($_GET['fileID']) ? (int)$_GET['fileID'] : 0;
        $folderId = isset($_GET['folderID']) ? (int)$_GET['folderID'] : 0;

        if ($noteId === 0) {
            $_SESSION['error'] = "Note ID not provided.";
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
            exit();
        }

        try{
            $this->lmModel->saveNoteAsFile($noteId, $fileId, $folderId);
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . BASE_PATH . 'lm/note?fileID=' . $fileId);
            exit();
        }
    }

    // ============================================================================
    // MINDMAP PAGE (mindmap.php)
    // ============================================================================

    /**
     * VIEW: Display mindmaps for a document
     */
    public function mindmap()
    {
        $this->checkSession();
        
        $userId = (int)$_SESSION['user_id'];
        $fileId = isset($_GET['fileID']) ? (int)$_GET['fileID'] : 0;
        
        if ($fileId === 0) {
            $_SESSION['error'] = "File ID not provided.";
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
            exit();
        }
        
        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file || !is_array($file)) {
                $_SESSION['error'] = "File not found.";
                header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
                exit();
            }
            
            $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
            $mindmapList = $this->lmModel->getMindmapByFile($fileId) ?? [];
            
            require_once __DIR__ . '/../views/learningView/mindmap.php';
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
            exit();
        }
    }

    /**
     * ACTION (JSON API): Generate a summary using AI
     */
    public function generateSummary()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);
        
        $userId = (int)$_SESSION['user_id'];
        $fileId = isset($_GET['fileID']) ? (int)$_GET['fileID'] : 0;
        $instructions = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instructions'])) {
            $instructions = trim($_POST['instructions']);
        }
        
        if ($fileId === 0) {
            echo json_encode(['success' => false, 'message' => 'File ID not provided.']);
            exit();
        }
        
        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file || !is_array($file)) {
                echo json_encode(['success' => false, 'message' => 'File not found.']);
                exit();
            }
            
            $extractedText = $file['extracted_text'] ?? '';
            if (empty($extractedText)) {
                echo json_encode(['success' => false, 'message' => 'No extracted text found.']);
                exit();
            }
            
            $context = !empty($instructions) ? $instructions : "In paragraph format";
            $generatedSummary = $this->gemini->generateSummary($extractedText, $context);
            $this->lmModel->saveSummary($fileId, $userId, 'Summary - ' . $file['name'], $generatedSummary);
            
            echo json_encode(['success' => true, 'content' => $generatedSummary]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    /**
     * ACTION (JSON API): Generate notes using AI
     */
    public function generateNotes()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);
        
        $userId = (int)$_SESSION['user_id'];
        $fileId = isset($_GET['fileID']) ? (int)$_GET['fileID'] : 0;
        $instructions = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instructions'])) {
            $instructions = trim($_POST['instructions']);
        }
        
        if ($fileId === 0) {
            echo json_encode(['success' => false, 'message' => 'File ID not provided.']);
            exit();
        }
        
        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file || !is_array($file)) {
                echo json_encode(['success' => false, 'message' => 'File not found.']);
                exit();
            }
            
            $extractedText = $file['extracted_text'] ?? '';
            if (empty($extractedText)) {
                echo json_encode(['success' => false, 'message' => 'No extracted text found.']);
                exit();
            }
            
            $context = !empty($instructions) ? $instructions : '';
            $generatedNote = $this->gemini->generateNotes($extractedText, $context);
            $this->lmModel->saveNotes($fileId, $file['name'], $generatedNote, $userId);
            
            echo json_encode(['success' => true, 'content' => $generatedNote]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    /**
     * ACTION (JSON API): Save a manually created note
     */
    public function saveNote()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['noteTitle']) || !isset($_POST['noteContent'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            exit();
        }
        
        $userId = (int)$_SESSION['user_id'];
        $fileId = isset($_GET['fileID']) ? (int)$_GET['fileID'] : 0;
        $title = trim($_POST['noteTitle']);
        $content = trim($_POST['noteContent']);
        
        if ($fileId === 0) {
            echo json_encode(['success' => false, 'message' => 'File ID not provided.']);
            exit();
        }
        
        if (empty($title) || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Title and content are required.']);
            exit();
        }
        
        try {
            $this->lmModel->saveNotes($fileId, $title, $content, $userId);
            echo json_encode(['success' => true, 'message' => $content]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    /**
     * ACTION (JSON API): Generate a mindmap using AI
     */
    public function generateMindmap()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);
        
        $userId = (int)$_SESSION['user_id'];
        $fileId = isset($_GET['fileID']) ? (int)$_GET['fileID'] : 0;
        $instructions = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instructions'])) {
            $instructions = trim($_POST['instructions']);
        }
        
        if ($fileId === 0) {
            echo json_encode(['success' => false, 'message' => 'File ID not provided.']);
            exit();
        }
        
        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file || !is_array($file)) {
                echo json_encode(['success' => false, 'message' => 'File not found.']);
                exit();
            }
            
            $extractedText = $file['extracted_text'] ?? '';
            if (empty($extractedText)) {
                echo json_encode(['success' => false, 'message' => 'No extracted text found.']);
                exit();
            }
            
            $mindmapMarkdown = $this->gemini->generateMindmapMarkdown($extractedText, $instructions);
            $mindmapJson = json_encode($mindmapMarkdown, JSON_UNESCAPED_UNICODE);
            $this->lmModel->saveMindmap($fileId, 'Mindmap - ' . $file['name'], $mindmapJson);
            
            echo json_encode(['success' => true, 'markdown' => $mindmapMarkdown]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    /**
     * ACTION (JSON API): Retrieve a specific mindmap by ID
     */
    public function viewMindmap()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);
        
        $userId = (int)$_SESSION['user_id'];
        $mindmapId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $fileId = isset($_GET['fileID']) ? (int)$_GET['fileID'] : 0;
        
        if ($mindmapId === 0) {
            echo json_encode(['success' => false, 'message' => 'Mindmap ID not provided.']);
            exit();
        }
        
        if ($fileId === 0) {
            echo json_encode(['success' => false, 'message' => 'File ID not provided.']);
            exit();
        }
        
        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file || !is_array($file)) {
                echo json_encode(['success' => false, 'message' => 'File not found.']);
                exit();
            }
            
            $mindmap = $this->lmModel->getMindmapById($mindmapId, $fileId);
            if (!$mindmap || !is_array($mindmap)) {
                echo json_encode(['success' => false, 'message' => 'Mindmap not found.']);
                exit();
            }
            
            $markdown = json_decode($mindmap['data'], true);
            if ($markdown === null) {
                $markdown = $mindmap['data'];
            }
            
            echo json_encode(['success' => true, 'markdown' => $markdown]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * ACTION: Delete mindmap from database
     */
    public function deleteMindmap(){
        $this->checkSession();
        $mindmapId = isset($_GET['mindmapID']) ? (int)$_GET['mindmapID'] : 0;
        $fileId = isset($_GET['fileID']) ? (int)$_GET['fileID'] : 0;

        if ($mindmapId === 0) {
            $_SESSION['error'] = "Mindmap ID not provided.";
            header('Location: ' . BASE_PATH . 'lm/displayLearningMaterials');
            exit();
        }

        try{
            $this->lmModel->deleteMindmap($mindmapId);
            header('Location: ' . BASE_PATH . 'lm/mindmap?fileID=' . $fileId);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . BASE_PATH . 'lm/mindmap?fileID=' . $fileId);
            exit();
        }
    }

    // ============================================================================
    // CREATE SUMMARY PAGE (createSummary.php)
    // ============================================================================

    /**
     * VIEW: Display the create summary form
     */
    public function createSummary()
    {
        $this->checkSession();
        
        $userId = (int)$_SESSION['user_id'];
        $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
        
        require_once __DIR__ . '/../views/learningView/createSummary.php';
    }
}
