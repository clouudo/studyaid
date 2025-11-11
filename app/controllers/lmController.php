<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LmModel;
use App\Services\GeminiService;

class LmController
{

    private $lmModel;
    private $gemini;
    private $userModel;

    private const SESSION_CURRENT_FILE_ID = 'current_file_id';

    public function __construct()
    {
        $this->lmModel = new LmModel();
        $this->gemini = new GeminiService();
        $this->userModel = new UserModel();
    }

    // ============================================================================
    // UTILITY METHODS
    // ============================================================================

    public function checkSession($isJsonResponse = false)
    {
        if (!isset($_SESSION['user_id'])) {
            if ($isJsonResponse) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'User not logged in.']);
                exit();
            } else {
                header('Location: ' . HOME);
                exit();
            }
        }
    }

    public function getUserInfo()
    {
        $userId = (int)$_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);
        return $user;
    }

    private function resolveFileId(bool $persist = true): int
    {
        $fileId = 0;

        if (isset($_POST['file_id'])) {
            $fileId = (int)$_POST['file_id'];
        } elseif (isset($_SESSION[self::SESSION_CURRENT_FILE_ID])) {
            $fileId = (int)$_SESSION[self::SESSION_CURRENT_FILE_ID];
        }

        if ($persist && $fileId > 0) {
            $_SESSION[self::SESSION_CURRENT_FILE_ID] = $fileId;
        }

        return $fileId;
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
            $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'guest';
            $folderId = isset($_POST['folderSelect']) && !empty($_POST['folderSelect']) && $_POST['folderSelect'] != '0'
                ? (int)$_POST['folderSelect']
                : null;

            $files = $_FILES['document'];
            $uploadedCount = 0;
            $failedCount = 0;
            $errors = [];

            // Handle multiple files
            $fileCount = is_array($files['name']) ? count($files['name']) : 1;

            for ($i = 0; $i < $fileCount; $i++) {
                // Check if this is a single file upload (backward compatibility)
                if (!is_array($files['name'])) {
                    $file = [
                        'name' => $files['name'],
                        'type' => $files['type'],
                        'tmp_name' => $files['tmp_name'],
                        'error' => $files['error'],
                        'size' => $files['size']
                    ];
                    $i = $fileCount; // Exit loop after processing single file
                } else {
                    // Multiple files
                    if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                        $errors[] = "Error uploading {$files['name'][$i]}: Upload error code {$files['error'][$i]}";
                        $failedCount++;
                        continue;
                    }

                    $file = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ];
                }

                $uploadedFileName = $file['name'];
                $fileExtension = pathinfo($uploadedFileName, PATHINFO_EXTENSION);
                $tmpName = $file['tmp_name'];

                // Use the original filename (no custom document name for multiple uploads)
                $originalFileName = $uploadedFileName;

                $extractedText = $this->lmModel->extractTextFromFile($tmpName, $fileExtension);
                $fileContent = file_get_contents($tmpName);

                if ($fileContent === false) {
                    $errors[] = "Error reading file: {$uploadedFileName}";
                    $failedCount++;
                    continue;
                }

                try {
                    $newFileId = $this->lmModel->uploadFileToGCS($userId, $folderId, $extractedText, $fileContent, $file, $originalFileName);
                    $uploadedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Error uploading {$uploadedFileName}: " . $e->getMessage();
                    $failedCount++;
                }
            }

            // Set appropriate session messages
            if ($uploadedCount > 0 && $failedCount === 0) {
                $_SESSION['message'] = $uploadedCount === 1 
                    ? "File uploaded successfully!" 
                    : "{$uploadedCount} files uploaded successfully!";
                
                // If only one file was uploaded, redirect to display it
                if ($uploadedCount === 1) {
                    // Get the last uploaded file ID
                    $lastFile = $this->lmModel->getLatestFileForUser($userId);
                    if ($lastFile) {
                        $_SESSION[self::SESSION_CURRENT_FILE_ID] = $lastFile['fileID'];
                        header('Location: ' . DISPLAY_DOCUMENT);
                        exit();
                    }
                }
            } elseif ($uploadedCount > 0 && $failedCount > 0) {
                $_SESSION['message'] = "{$uploadedCount} file(s) uploaded successfully, {$failedCount} failed.";
                if (!empty($errors)) {
                    $_SESSION['error'] = implode('<br>', $errors);
                }
            } else {
                $_SESSION['error'] = !empty($errors) 
                    ? implode('<br>', $errors) 
                    : "Failed to upload files. Please try again.";
            }

            header('Location: ' . NEW_DOCUMENT);
            exit();
        }

        $this->checkSession();
        $userId = (int)$_SESSION['user_id'];
        $user = $this->getUserInfo();
        $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
        require_once VIEW_NEW_DOCUMENT;
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
        $fileId = $this->resolveFileId();
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
        $user = $this->getUserInfo();

        require_once VIEW_ALL_DOCUMENT;
    }

    /**
     * VIEW: Display a specific document
     */
    public function displayDocument()
    {
        $this->checkSession();

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();

        if ($fileId === 0) {
            $_SESSION['error'] = "File ID not provided.";
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }

        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file || !is_array($file)) {
                $_SESSION['error'] = "File not found.";
                header('Location: ' . DISPLAY_LEARNING_MATERIALS);
                exit();
            }

            $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
            $documentData = $this->lmModel->getDocumentContent($fileId, $userId);
            $user = $this->getUserInfo();

            require_once VIEW_DISPLAY_DOCUMENT;
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
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
        $fileId = $this->resolveFileId();

        if ($fileId === 0) {
            $_SESSION['error'] = "File ID not provided.";
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
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

        header('Location: ' . DISPLAY_LEARNING_MATERIALS);
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
        $user = $this->getUserInfo();

        require_once VIEW_NEW_FOLDER;
    }

    /**
     * ACTION: Create a new folder (POST)
     */
    public function createFolder()
    {
        $this->checkSession();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['folderName'])) {
            header('Location: ' . NEW_FOLDER);
            exit();
        }

        $userId = (int)$_SESSION['user_id'];
        $folderName = trim($_POST['folderName']);
        $parentFolderId = !empty($_POST['parentFolderId']) ? (int)$_POST['parentFolderId'] : null;

        if (empty($folderName)) {
            $_SESSION['error'] = "Folder name cannot be empty.";
            header('Location: ' . NEW_FOLDER);
            exit();
        }

        try {
            $this->lmModel->createFolder($userId, $folderName, $parentFolderId);
            $_SESSION['message'] = "Folder created successfully.";
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error creating folder: " . $e->getMessage();
            header('Location: ' . NEW_FOLDER);
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
        $folderId = isset($_POST['folder_id']) ? (int)$_POST['folder_id'] : 0;

        if ($folderId === 0) {
            $_SESSION['error'] = "Folder ID not provided.";
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
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

        header('Location: ' . DISPLAY_LEARNING_MATERIALS);
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
        $user = $this->getUserInfo();

        require_once VIEW_NEW_DOCUMENT;
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
        $fileId = $this->resolveFileId();

        if ($fileId === 0) {
            $_SESSION['error'] = "File ID not provided.";
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }

        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file || !is_array($file)) {
                $_SESSION['error'] = "File not found.";
                header('Location: ' . DISPLAY_LEARNING_MATERIALS);
                exit();
            }

            $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
            $summaryList = $this->lmModel->getSummaryByFile($fileId, $userId);
            $user = $this->getUserInfo();

            require_once VIEW_SUMMARY;
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }
    }

    /**
     * ACTION: Delete summary from database
     */
    public function deleteSummary()
    {
        $this->checkSession();

        $summaryId = isset($_POST['summary_id']) ? (int)$_POST['summary_id'] : 0;

        if ($summaryId === 0) {
            $_SESSION['error'] = "Summary ID not provided.";
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }

        try {
            $this->lmModel->deleteSummary($summaryId);
            header('Location: ' . SUMMARY);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . SUMMARY);
            exit();
        }
    }

    /**
     * ACTION: Save summary as file
     */
    public function saveSummaryAsFile()
    {
        $this->checkSession();

        $summaryId = isset($_POST['summary_id']) ? (int)$_POST['summary_id'] : 0;
        $fileId = $this->resolveFileId();
        $folderId = isset($_POST['folder_id']) ? (int)$_POST['folder_id'] : 0;

        if ($summaryId === 0) {
            $_SESSION['error'] = "Summary ID not provided.";
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }

        try {
            $this->lmModel->saveSummaryAsFile($summaryId, $fileId, $folderId);
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . SUMMARY);
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
        $fileId = $this->resolveFileId();

        if ($fileId === 0) {
            $_SESSION['error'] = "File ID not provided.";
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }

        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file || !is_array($file)) {
                $_SESSION['error'] = "File not found.";
                header('Location: ' . DISPLAY_LEARNING_MATERIALS);
                exit();
            }

            $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
            $noteList = $this->lmModel->getNotesByFile($fileId);
            $user = $this->getUserInfo();

            require_once VIEW_NOTE;
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }
    }

    /**
     * ACTION: Delete note from database
     */
    public function deleteNote()
    {
        $this->checkSession();

        $noteId = isset($_POST['note_id']) ? (int)$_POST['note_id'] : 0;

        if ($noteId === 0) {
            $_SESSION['error'] = "Note ID not provided.";
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }

        try {
            $this->lmModel->deleteNote($noteId);
            header('Location: ' . NOTE);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . NOTE);
            exit();
        }
    }

    /**
     * ACTION: Save note as file
     */
    public function saveNoteAsFile()
    {
        $this->checkSession();

        $noteId = isset($_POST['note_id']) ? (int)$_POST['note_id'] : 0;
        $fileId = $this->resolveFileId();
        $folderId = isset($_POST['folder_id']) ? (int)$_POST['folder_id'] : 0;

        if ($noteId === 0) {
            $_SESSION['error'] = "Note ID not provided.";
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }

        try {
            $this->lmModel->saveNoteAsFile($noteId, $fileId, $folderId);
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . NOTE);
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
        $fileId = $this->resolveFileId();

        if ($fileId === 0) {
            $_SESSION['error'] = "File ID not provided.";
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }

        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file || !is_array($file)) {
                $_SESSION['error'] = "File not found.";
                header('Location: ' . DISPLAY_LEARNING_MATERIALS);
                exit();
            }

            $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
            $mindmapList = $this->lmModel->getMindmapByFile($fileId) ?? [];
            $user = $this->getUserInfo();

            require_once VIEW_MINDMAP;
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
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
        $fileId = $this->resolveFileId();
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

            $title = $this->gemini->generateTitle($file['name'] . $generatedSummary);
            $this->lmModel->saveSummary($fileId, $title, $generatedSummary);

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
        $fileId = $this->resolveFileId();
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
            $generateSummary = $this->gemini->generateSummary($extractedText, "A very short summary of the content");
            $title = $this->gemini->generateTitle($file['name'] . $generateSummary);
            $this->lmModel->saveNotes($fileId, $title, $generatedNote);

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
        $fileId = $this->resolveFileId();
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
            $this->lmModel->saveNotes($fileId, $title, $content);
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
        $fileId = $this->resolveFileId();

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

            $mindmapMarkdown = $this->gemini->generateMindmapMarkdown($extractedText);
            $mindmapJson = json_encode($mindmapMarkdown, JSON_UNESCAPED_UNICODE);
            $generateSummary = $this->gemini->generateSummary($extractedText, "A very short summary of the content");
            $title = $this->gemini->generateTitle($file['name'] . $generateSummary);
            $this->lmModel->saveMindmap($fileId, $title, $mindmapJson);

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
        $mindmapId = isset($_POST['mindmap_id']) ? (int)$_POST['mindmap_id'] : 0;
        $fileId = $this->resolveFileId();

        if ($mindmapId === 0) {
            echo json_encode(['success' => false, 'message' => 'Mindmap ID not provided.']);
            exit();
        }

        if ($fileId === 0) {
            echo json_encode(['success' => false, 'message' => 'File ID not provided.']);
            exit();
        }

        try {
            $mindmap = $this->lmModel->getMindmapById($mindmapId, $fileId, $userId);
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
    public function deleteMindmap()
    {
        $this->checkSession();

        $mindmapId = isset($_POST['mindmap_id']) ? (int)$_POST['mindmap_id'] : 0;

        if ($mindmapId === 0) {
            $_SESSION['error'] = "Mindmap ID not provided.";
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }

        try {
            $this->lmModel->deleteMindmap($mindmapId);
            header('Location: ' . MINDMAP);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . MINDMAP);
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
        $user = $this->getUserInfo();

        require_once VIEW_CREATE_SUMMARY;
    }

    // ============================================================================
    // EXPORT FUNCTIONALITY
    // ============================================================================

    /**
     * ACTION: Export summary as PDF
     */
    public function exportSummaryAsPdf()
    {
        $this->checkSession();

        $userId = (int)$_SESSION['user_id'];
        $summaryId = isset($_POST['summary_id']) ? (int)$_POST['summary_id'] : 0;

        if ($summaryId === 0) {
            $_SESSION['error'] = "Summary ID not provided.";
            header('Location: ' . SUMMARY);
            exit();
        }

        try {
            $summary = $this->lmModel->getSummaryById($summaryId, $userId);
            if (!$summary) {
                $_SESSION['error'] = "Summary not found.";
                header('Location: ' . SUMMARY);
                exit();
            }

            $this->_generatePdf($summary['title'], $summary['content']);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . SUMMARY);
            exit();
        }
    }

    /**
     * ACTION: Export summary as DOCX
     */
    public function exportSummaryAsDocx()
    {
        $this->checkSession();

        $userId = (int)$_SESSION['user_id'];
        $summaryId = isset($_POST['summary_id']) ? (int)$_POST['summary_id'] : 0;

        if ($summaryId === 0) {
            $_SESSION['error'] = "Summary ID not provided.";
            header('Location: ' . SUMMARY);
            exit();
        }

        try {
            $summary = $this->lmModel->getSummaryById($summaryId, $userId);
            if (!$summary) {
                $_SESSION['error'] = "Summary not found.";
                header('Location: ' . SUMMARY);
                exit();
            }

            $this->_generateDocx($summary['title'], $summary['content']);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . SUMMARY);
            exit();
        }
    }

    /**
     * ACTION: Export summary as TXT
     */
    public function exportSummaryAsTxt()
    {
        $this->checkSession();

        $userId = (int)$_SESSION['user_id'];
        $summaryId = isset($_POST['summary_id']) ? (int)$_POST['summary_id'] : 0;

        if ($summaryId === 0) {
            $_SESSION['error'] = "Summary ID not provided.";
            header('Location: ' . SUMMARY);
            exit();
        }

        try {
            $summary = $this->lmModel->getSummaryById($summaryId, $userId);
            if (!$summary) {
                $_SESSION['error'] = "Summary not found.";
                header('Location: ' . SUMMARY);
                exit();
            }

            $this->_generateTxt($summary['title'], $summary['content']);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . SUMMARY);
            exit();
        }
    }

    /**
     * ACTION: Export note as PDF
     */
    public function exportNoteAsPdf()
    {
        $this->checkSession();

        $userId = (int)$_SESSION['user_id'];
        $noteId = isset($_POST['note_id']) ? (int)$_POST['note_id'] : 0;

        if ($noteId === 0) {
            $_SESSION['error'] = "Note ID not provided.";
            header('Location: ' . NOTE);
            exit();
        }

        try {
            $note = $this->lmModel->getNoteById($noteId, $userId);
            if (!$note) {
                $_SESSION['error'] = "Note not found.";
                header('Location: ' . NOTE);
                exit();
            }

            $this->_generatePdf($note['title'], $note['content']);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . NOTE);
            exit();
        }
    }

    /**
     * ACTION: Export note as DOCX
     */
    public function exportNoteAsDocx()
    {
        $this->checkSession();

        $userId = (int)$_SESSION['user_id'];
        $noteId = isset($_POST['note_id']) ? (int)$_POST['note_id'] : 0;

        if ($noteId === 0) {
            $_SESSION['error'] = "Note ID not provided.";
            header('Location: ' . NOTE);
            exit();
        }

        try {
            $note = $this->lmModel->getNoteById($noteId, $userId);
            if (!$note) {
                $_SESSION['error'] = "Note not found.";
                header('Location: ' . NOTE);
                exit();
            }

            $this->_generateDocx($note['title'], $note['content']);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . NOTE);
            exit();
        }
    }

    /**
     * ACTION: Export note as TXT
     */
    public function exportNoteAsTxt()
    {
        $this->checkSession();

        $userId = (int)$_SESSION['user_id'];
        $noteId = isset($_POST['note_id']) ? (int)$_POST['note_id'] : 0;

        if ($noteId === 0) {
            $_SESSION['error'] = "Note ID not provided.";
            header('Location: ' . NOTE);
            exit();
        }

        try {
            $note = $this->lmModel->getNoteById($noteId, $userId);
            if (!$note) {
                $_SESSION['error'] = "Note not found.";
                header('Location: ' . NOTE);
                exit();
            }

            $this->_generateTxt($note['title'], $note['content']);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . NOTE);
            exit();
        }
    }

    /**
     * Helper: Generate PDF file
     */
    private function _generatePdf($title, $content)
    {
        // Try to use dompdf directly if available via Composer
        if (class_exists('\Dompdf\Dompdf')) {
            try {
                $dompdf = new \Dompdf\Dompdf();
                $options = $dompdf->getOptions();
                $options->set('defaultFont', 'Arial');
                $options->set('isHtml5ParserEnabled', true);
                $options->set('isRemoteEnabled', false);

                $html = $this->_convertContentToHtml($title, $content);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) . '.pdf';

                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');

                echo $dompdf->output();
                exit();
            } catch (\Exception $e) {
                // Fallback to PHPWord PDF writer
                $this->_generatePdfWithPhpWord($title, $content);
            }
        } else {
            // Try PHPWord PDF writer as fallback
            $this->_generatePdfWithPhpWord($title, $content);
        }
    }

    /**
     * Helper: Generate PDF using PHPWord (requires PDF renderer)
     */
    private function _generatePdfWithPhpWord($title, $content)
    {
        // Try to use PHPWord's PDF writer if PDF renderer is available
        $dompdfPath = __DIR__ . '/../../vendor/dompdf/dompdf';
        if (file_exists($dompdfPath)) {
            try {
                \PhpOffice\PhpWord\Settings::setPdfRendererName(\PhpOffice\PhpWord\Settings::PDF_RENDERER_DOMPDF);
                \PhpOffice\PhpWord\Settings::setPdfRendererPath($dompdfPath);

                $phpWord = new \PhpOffice\PhpWord\PhpWord();
                $section = $phpWord->addSection();

                $section->addTitle($title, 1);
                $section->addTextBreak(1);

                // Convert markdown-like content to plain text and add paragraphs
                $paragraphs = preg_split('/\n\s*\n/', $content);
                foreach ($paragraphs as $paragraph) {
                    $paragraph = trim($paragraph);
                    if (!empty($paragraph)) {
                        $section->addText($paragraph);
                        $section->addTextBreak(1);
                    }
                }

                $writer = new \PhpOffice\PhpWord\Writer\PDF($phpWord);
                $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) . '.pdf';

                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                $writer->save('php://output');
                exit();
            } catch (\Exception $e) {
                // Final fallback: HTML that prompts user to save as PDF
                $this->_generateSimplePdf($title, $content);
            }
        } else {
            // Final fallback: HTML that prompts user to save as PDF
            $this->_generateSimplePdf($title, $content);
        }
    }

    /**
     * Helper: Convert content to HTML for PDF generation
     */
    private function _convertContentToHtml($title, $content)
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . htmlspecialchars($title) . '</title>';
        $html .= '<style>
            body {
                font-family: Arial, sans-serif;
                padding: 20px;
                line-height: 1.6;
            }
            h1 {
                color: #333;
                border-bottom: 2px solid #A855F7;
                padding-bottom: 10px;
                margin-top: 0;
            }
            p {
                margin: 10px 0;
                text-align: justify;
            }
            strong {
                font-weight: bold;
            }
            em {
                font-style: italic;
            }
            ul, ol {
                margin: 10px 0;
                padding-left: 30px;
            }
            li {
                margin: 5px 0;
            }
        </style></head><body>';
        $html .= '<h1>' . htmlspecialchars($title) . '</h1>';

        // Convert markdown-like content to HTML paragraphs
        $lines = explode("\n", $content);
        $inList = false;
        $listType = '';

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                if ($inList) {
                    $html .= '</' . $listType . '>';
                    $inList = false;
                }
                continue;
            }

            // Check for unordered list
            if (preg_match('/^[-*]\s+(.+)$/', $line, $matches)) {
                if (!$inList || $listType !== 'ul') {
                    if ($inList) $html .= '</' . $listType . '>';
                    $html .= '<ul>';
                    $inList = true;
                    $listType = 'ul';
                }
                $item = htmlspecialchars($matches[1]);
                $item = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $item);
                $item = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $item);
                $html .= '<li>' . $item . '</li>';
                continue;
            }

            // Check for ordered list
            if (preg_match('/^\d+\.\s+(.+)$/', $line, $matches)) {
                if (!$inList || $listType !== 'ol') {
                    if ($inList) $html .= '</' . $listType . '>';
                    $html .= '<ol>';
                    $inList = true;
                    $listType = 'ol';
                }
                $item = htmlspecialchars($matches[1]);
                $item = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $item);
                $item = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $item);
                $html .= '<li>' . $item . '</li>';
                continue;
            }

            // Regular paragraph
            if ($inList) {
                $html .= '</' . $listType . '>';
                $inList = false;
            }

            $paragraph = htmlspecialchars($line);
            $paragraph = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $paragraph);
            $paragraph = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $paragraph);
            $html .= '<p>' . $paragraph . '</p>';
        }

        if ($inList) {
            $html .= '</' . $listType . '>';
        }

        $html .= '</body></html>';
        return $html;
    }

    /**
     * Helper: Generate simple HTML-based PDF (fallback when no PDF library available)
     * This will show a message and provide download instructions
     */
    private function _generateSimplePdf($title, $content)
    {
        // Convert markdown to HTML with better formatting
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . htmlspecialchars($title) . '</title>';
        $html .= '<style>
            @media print {
                body { margin: 0; padding: 15mm; }
                .info-box { display: none; }
            }
            body {
                font-family: Arial, sans-serif;
                padding: 20px;
                line-height: 1.6;
                max-width: 800px;
                margin: 0 auto;
            }
            .info-box {
                background-color: #fff3cd;
                border: 1px solid #ffc107;
                border-radius: 5px;
                padding: 15px;
                margin-bottom: 20px;
            }
            h1 {
                color: #333;
                border-bottom: 2px solid #A855F7;
                padding-bottom: 10px;
                margin-top: 0;
            }
            p {
                margin: 10px 0;
                text-align: justify;
            }
            strong {
                font-weight: bold;
            }
            em {
                font-style: italic;
            }
            ul, ol {
                margin: 10px 0;
                padding-left: 30px;
            }
            li {
                margin: 5px 0;
            }
        </style></head><body>';

        $html .= '<div class="info-box">
            <strong>Note:</strong> PDF library not installed. Please use your browser\'s "Print to PDF" feature:
            <ol>
                <li>Press Ctrl+P (or Cmd+P on Mac)</li>
                <li>Select "Save as PDF" as the destination</li>
                <li>Click "Save"</li>
            </ol>
        </div>';

        $html .= '<h1>' . htmlspecialchars($title) . '</h1>';

        // Convert markdown-like content to HTML paragraphs
        $lines = explode("\n", $content);
        $inList = false;
        $listType = '';

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                if ($inList) {
                    $html .= '</' . $listType . '>';
                    $inList = false;
                }
                continue;
            }

            // Check for unordered list
            if (preg_match('/^[-*]\s+(.+)$/', $line, $matches)) {
                if (!$inList || $listType !== 'ul') {
                    if ($inList) $html .= '</' . $listType . '>';
                    $html .= '<ul>';
                    $inList = true;
                    $listType = 'ul';
                }
                $item = htmlspecialchars($matches[1]);
                $item = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $item);
                $item = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $item);
                $html .= '<li>' . $item . '</li>';
                continue;
            }

            // Check for ordered list
            if (preg_match('/^\d+\.\s+(.+)$/', $line, $matches)) {
                if (!$inList || $listType !== 'ol') {
                    if ($inList) $html .= '</' . $listType . '>';
                    $html .= '<ol>';
                    $inList = true;
                    $listType = 'ol';
                }
                $item = htmlspecialchars($matches[1]);
                $item = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $item);
                $item = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $item);
                $html .= '<li>' . $item . '</li>';
                continue;
            }

            // Regular paragraph
            if ($inList) {
                $html .= '</' . $listType . '>';
                $inList = false;
            }

            $paragraph = htmlspecialchars($line);
            $paragraph = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $paragraph);
            $paragraph = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $paragraph);
            $html .= '<p>' . $paragraph . '</p>';
        }

        if ($inList) {
            $html .= '</' . $listType . '>';
        }

        $html .= '</body></html>';

        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) . '.pdf';

        // Output HTML that can be printed to PDF using browser's print functionality
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        echo $html;
        echo '<script>
            window.onload = function() {
                setTimeout(function() {
                    if (confirm("PDF library not installed. Would you like to open the print dialog to save as PDF?")) {
                        window.print();
                    }
                }, 500);
            };
        </script>';
        exit();
    }

    /**
     * Helper: Generate DOCX file
     */
    private function _generateDocx($title, $content)
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();

        $section->addTitle($title, 1);
        $section->addTextBreak(1);

        // Convert markdown-like content to plain text and add paragraphs
        $paragraphs = preg_split('/\n\s*\n/', $content);
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (!empty($paragraph)) {
                $section->addText($paragraph);
                $section->addTextBreak(1);
            }
        }

        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) . '.docx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save('php://output');
        exit();
    }

    /**
     * Helper: Generate TXT file
     */
    private function _generateTxt($title, $content)
    {
        // Clean filename
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) . '.txt';

        // Prepare text content
        $text = $title . "\n";
        $text .= str_repeat('=', strlen($title)) . "\n\n";
        $text .= $content . "\n";

        // Set headers for download
        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        // Output text with BOM for UTF-8 compatibility
        echo "\xEF\xBB\xBF" . $text;
        exit();
    }

    // ============================================================================
    // CHATBOT PAGE (chatbot.php)
    // ============================================================================

    /**
     * VIEW: Display chatbot interface for a document
     */
    public function chatbot()
    {
        $this->checkSession();

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();
        $file = $this->lmModel->getFile($userId, $fileId);
        $user = $this->getUserInfo();
        $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
        $newChatbot = $this->saveChatbot($fileId, $userId);
        $chatbot = $this->lmModel->getChatBotByFile($fileId);
        $responseChats = [];

        if ($chatbot) {
            $chatbotId = $chatbot['chatbotID'];
            if ($chatbotId) {
                $questionChats = $this->lmModel->getQuestionChatByChatbot($chatbotId);
                foreach ($questionChats as $questionChat) {
                    $responseChats[] = $this->lmModel->getResponseChatByQuestionChat($questionChat['questionChatID']) . "\n";
                }
            }
        } else {
            $_SESSION['error'] = "Failed to save chatbot";
            header('Location: ' . DISPLAY_DOCUMENT);
            exit();
        }

        require_once VIEW_CHATBOT;
    }

    /**
     * ACTION (JSON API): Send a question to the chatbot and get response
     */
    public function sendQuestionChat()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();
        $question = isset($_POST['question']) ? trim($_POST['question']) : '';

        if ($fileId === 0) {
            echo json_encode(['success' => false, 'message' => 'File ID not provided.']);
            exit();
        }

        if (empty($question)) {
            echo json_encode(['success' => false, 'message' => 'Question cannot be empty.']);
            exit();
        }

        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file || !is_array($file)) {
                echo json_encode(['success' => false, 'message' => 'File not found.']);
                exit();
            }

            $chatbot = $this->lmModel->getChatBotByFile($fileId);
            if (!$chatbot) {
                echo json_encode(['success' => false, 'message' => 'Chatbot not found.']);
                exit();
            }

            $chatHistory = $this->lmModel->chatHistory($fileId);
            $userQuestions = $chatHistory['questions'];
            $aiResponse = $chatHistory['responseChats'];

            $compressedChatHistory = $this->gemini->compressChatHistory($userQuestions, $aiResponse);

            $questionChatId = $this->lmModel->saveQuestionChat($chatbot['chatbotID'], $question);
            $response = $this->gemini->generateChatbotResponse($file['extracted_text'], $question, $compressedChatHistory);
            $responseChatId = $this->lmModel->saveResponseChat($questionChatId, $response);
            $response = $this->lmModel->getResponseChatById($responseChatId);

            echo json_encode(['success' => true, 'response' => $response['response']]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    /**
     * Helper: Save or retrieve chatbot for a file
     */
    private function saveChatbot($fileId, $userId)
    {
        $file = $this->lmModel->getFile($userId, $fileId);
        $chatbotId = $this->lmModel->getChatBotByFile($fileId);

        if (!$chatbotId) {
            $generateSummary = $this->gemini->generateSummary($file['extracted_text'], "A very short summary of the content");
            $title = $this->gemini->generateTitle($generateSummary);
            $chatbotId = $this->lmModel->saveChatbot($fileId, $title);
        }

        return $chatbotId;
    }

    // ============================================================================
    // QUIZ PAGE (quiz.php)
    // ============================================================================

    /**
     * VIEW: Display quizzes for a document
     */
    public function quiz()
    {
        $this->checkSession();

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();

        if ($fileId === 0) {
            $_SESSION['error'] = "File ID not provided.";
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }

        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file || !is_array($file)) {
                $_SESSION['error'] = "File not found.";
                header('Location: ' . DISPLAY_LEARNING_MATERIALS);
                exit();
            }

            $user = $this->getUserInfo();
            $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
            $quizList = $this->lmModel->getQuizByFile($fileId);

            require_once VIEW_QUIZ;
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }
    }

    /**
     * ACTION (JSON API): Save quiz score
     */
    public function saveScore()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $quizId = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
        $percentageScore = isset($_POST['percentage_score']) ? trim($_POST['percentage_score']) : '0';

        if ($quizId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid quiz ID']);
            exit();
        }

        try {
            $result = $this->lmModel->saveScore($quizId, $percentageScore);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Score saved successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save score']);
            }
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * ACTION (JSON API): Retrieve a specific quiz by ID
     */
    public function viewQuiz()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();
        $quizId = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;

        if ($quizId === 0) {
            echo json_encode(['success' => false, 'message' => 'Quiz ID not provided.']);
            exit();
        }

        try {
            $questions = $this->lmModel->getQuestionByQuiz($quizId);

            if (!$questions || empty($questions['question'])) {
                echo json_encode(['success' => false, 'message' => 'No questions found']);
                exit();
            }

            $quesArray = json_decode($questions['question'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(['success' => false, 'message' => 'Invalid quiz data format']);
                exit();
            }

            $quizData = isset($quesArray['quiz']) ? $quesArray['quiz'] : $quesArray;

            echo json_encode(['success' => true, 'quiz' => $quizData]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * ACTION (JSON API): Generate a quiz using AI
     */
    public function generateQuiz()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();
        $questionAmount = '';
        $questionDifficulty = '';
        $instructions = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['questionAmount'])) {
            $questionAmount = trim($_POST['questionAmount']);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['questionDifficulty'])) {
            $questionDifficulty = trim($_POST['questionDifficulty']);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instructions'])) {
            $instructions = trim($_POST['instructions']);
        }if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['questionType'])) {
            $questionType = trim($_POST['questionType']);
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

            $sourceText = $file['extracted_text'] ?? '';
            if (empty($sourceText)) {
                echo json_encode(['success' => false, 'message' => 'No extracted text found.']);
                exit();
            }

            $context = !empty($instructions) ? $instructions : '';
            if($questionType == 'mcq') {
                $quizData = $this->gemini->generateMCQ($sourceText, $context, $questionAmount, $questionDifficulty);
            }elseif($questionType == 'shortQuestion') {
                $quizData = $this->gemini->generateShortQuestion($sourceText, $context, $questionAmount, $questionDifficulty);
            }

            $decodedQuiz = json_decode($quizData, true);
            $totalQuestions = count($decodedQuiz['quiz']);            

            $generatedSummary = $this->gemini->generateSummary($sourceText, "A very short summary of the content");
            $title = $this->gemini->generateTitle($file['name'] . $generatedSummary);
            $quizId = $this->lmModel->saveQuiz($fileId, $totalQuestions, $title);
            $encodedQuiz = json_encode($decodedQuiz['quiz']);
            if($questionType == 'mcq') {
                $this->lmModel->saveQuestion($quizId, 'MCQ', $encodedQuiz);
            }elseif($questionType == 'shortQuestion') {
                $this->lmModel->saveQuestion($quizId, 'Short Question', $encodedQuiz);
            }

            $quizArray = $decodedQuiz['quiz'] ?? $decodedQuiz['questions'] ?? [];
            if($questionType == 'mcq') {
                echo json_encode(['success' => true, 'mcq' => $decodedQuiz['quiz'], 'quizId' => $quizId]);
            }elseif($questionType == 'shortQuestion') {
                echo json_encode(['success' => true, 'shortQuestion' => $decodedQuiz['quiz'], 'quizId' => $quizId]);
            }
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    public function submitQuiz(){
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $quizId = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
        $userAnswers = isset($_POST['user_answers']) ? json_decode($_POST['user_answers'], true) : [];

        if ($quizId === 0) {
            echo json_encode(['success' => false, 'message' => 'Quiz ID not provided.']);
            exit();
        }

        if (empty($userAnswers)) {
            echo json_encode(['success' => false, 'message' => 'User answers not provided.']);
            exit();
        }

        try {
            $questionData = $this->lmModel->getQuestionByQuiz($quizId);
            if (!$questionData) {
                echo json_encode(['success' => false, 'message' => 'Quiz questions not found.']);
                exit();
            }

            $questions = json_decode($questionData['question'], true);
            $quizArray = isset($questions['quiz']) ? $questions['quiz'] : $questions;

            foreach ($userAnswers as $index => $answer) {
                if (isset($quizArray[$index])) {
                    $this->lmModel->saveUserAnswer($questionData['questionID'], json_encode([
                        'questionIndex' => $index,
                        'userAnswer' => $answer
                    ]));
                }
            }

            echo json_encode(['success' => true, 'message' => 'Answers saved successfully']);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    // ============================================================================
    // FLASHCARD PAGE (flashcard.php)
    // ============================================================================

    /**
     * VIEW: Display flashcards for a document
     */
    public function flashcard()
    {
        $this->checkSession();

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();

        if ($fileId === 0) {
            $_SESSION['error'] = "File ID not provided.";
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }

        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file || !is_array($file)) {
                $_SESSION['error'] = "File not found.";
                header('Location: ' . DISPLAY_LEARNING_MATERIALS);
                exit();
            }

            $user = $this->getUserInfo();
            $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
            $flashcards = $this->lmModel->getFlashcardsByFile($fileId);

            require_once VIEW_FLASHCARD;
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }
    }

    /**
     * ACTION (JSON API): Generate flashcards using AI
     */
    public function generateFlashcards()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();
        $flashcardAmount = '';
        $flashcardType = '';
        $instructions = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flashcardAmount'])) {
            $flashcardAmount = trim($_POST['flashcardAmount']);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flashcardType'])) {
            $flashcardType = trim($_POST['flashcardType']);
        }
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

            $sourceText = $file['extracted_text'] ?? '';
            if (empty($sourceText)) {
                echo json_encode(['success' => false, 'message' => 'No extracted text found.']);
                exit();
            }

            $context = !empty($instructions) ? $instructions : '';
            $flashcards = $this->gemini->generateFlashcards($sourceText, $context, $flashcardAmount, $flashcardType);
            $generatedSummary = $this->gemini->generateSummary($sourceText, "A very short summary of the content");
            $decodedFlashcards = json_decode($flashcards, true);
            $term = '';
            $definition = '';

            foreach ($decodedFlashcards['flashcards'] as $flashcard) {
                $term .= $flashcard['term'] . "\n";
                $definition .= $flashcard['definition'] . "\n";
            }

            $terms = json_encode($term);
            $definitions = json_encode($definition);
            $title = $this->gemini->generateTitle($file['name'] . $generatedSummary);
            $flashcardId = $this->lmModel->saveFlashcards($fileId, $title, $terms, $definitions);

            echo json_encode(['success' => true, 'term' => $terms, 'definition' => $definitions]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    /**
     * ACTION (JSON API): Retrieve a specific flashcard by ID
     */
    public function viewFlashcard()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();
        $flashcardId = isset($_POST['flashcard_id']) ? (int)$_POST['flashcard_id'] : 0;

        if ($flashcardId === 0) {
            echo json_encode(['success' => false, 'message' => 'Flashcard ID not provided.']);
            exit();
        }

        try {
            $flashcard = $this->lmModel->getFlashcardsById($flashcardId);
            if (!$flashcard) {
                echo json_encode(['success' => false, 'message' => 'Flashcard not found.']);
                exit();
            }

            echo json_encode(['success' => true, 'flashcard' => $flashcard]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }
}
