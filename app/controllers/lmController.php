<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LmModel;
use App\Services\GeminiService;
use App\Services\OCRService;
use PDO;
use App\Services\PiperService;
use App\Services\ExportService;
use App\Config\Database;

class LmController
{

    private $lmModel;
    private $gemini;
    private $userModel;
    private $PiperService;
    private $ocrService;
    private $exportService;


    private const SESSION_CURRENT_FILE_ID = 'current_file_id';

    public function __construct()
    {
        // Increase execution time for long-running operations (e.g. RAG, large file processing)
        set_time_limit(300);

        $this->lmModel = new LmModel();
        $this->gemini = new GeminiService();
        $this->userModel = new UserModel();
        $this->PiperService = new PiperService();
        $this->ocrService = new OCRService();
        $this->exportService = new ExportService();
    }

    // ============================================================================
    // UTILITY METHODS
    // ============================================================================

    /**
     * Checks if user is logged in, redirects or returns JSON error if not
     */
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

    /**
     * Retrieves current logged-in user information
     */
    public function getUserInfo()
    {
        $userId = (int)$_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);
        return $user;
    }

    /**
     * Resolves file ID from POST request or session, optionally persists to session
     */
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
     * Check if file extension is an image type
     */
    private function isImageFile(string $extension): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'tif'];
        return in_array(strtolower($extension), $imageExtensions);
    }

    /**
     * Extract text from file using appropriate method (OCR for images, model method for others)
     */
    private function extractTextFromFile(string $tmpName, string $fileExtension, array $file): string
    {
        if ($this->isImageFile($fileExtension)) {
            $fileName = basename($tmpName);
            error_log("[OCR] Starting OCR processing for image: {$fileName} (Extension: {$fileExtension})");
            
            try {
                $ocrResult = $this->ocrService->recognizeText($tmpName);
                if ($ocrResult['success'] && !empty($ocrResult['text'])) {
                    $extractedText = trim($ocrResult['text']);
                    $engine = $ocrResult['engine'] ?? 'unknown';
                    $confidence = isset($ocrResult['confidence']) ? round($ocrResult['confidence'] * 100, 2) : 'N/A';
                    $processingTime = isset($ocrResult['processing_time']) ? round($ocrResult['processing_time'], 2) : 'N/A';
                    
                    error_log("[OCR] SUCCESS - Image: {$fileName} | Engine: {$engine} | Text length: " . strlen($extractedText) . " chars | Confidence: {$confidence}% | Processing time: {$processingTime}s");
                    return $extractedText;
                } else {
                    $errorMsg = $ocrResult['error'] ?? 'No text detected in image';
                    $engine = $ocrResult['engine'] ?? 'unknown';
                    error_log("[OCR] FAILED - Image: {$fileName} | Engine: {$engine} | Error: {$errorMsg}");
                    // Return empty string but don't throw exception - file will still be saved
                    return '';
                }
            } catch (\Exception $e) {
                error_log("[OCR] EXCEPTION - Image: {$fileName} | Exception: " . $e->getMessage());
                // Return empty string but don't throw exception - file will still be saved
                return '';
            }
        }
        
        return $this->lmModel->extractTextFromFile($tmpName, $fileExtension);
    }

    /**
     * Normalizes file array from $_FILES structure for single or multiple uploads
     */
    private function normalizeFileArray(array $files, int $index): ?array
    {
                if (!is_array($files['name'])) {
            return [
                        'name' => $files['name'],
                        'type' => $files['type'],
                        'tmp_name' => $files['tmp_name'],
                        'error' => $files['error'],
                        'size' => $files['size']
                    ];
        }

        if ($files['error'][$index] !== UPLOAD_ERR_OK) {
            return null;
        }

        return [
            'name' => $files['name'][$index],
            'type' => $files['type'][$index],
            'tmp_name' => $files['tmp_name'][$index],
            'error' => $files['error'][$index],
            'size' => $files['size'][$index]
        ];
    }

    /**
     * Processes single file upload: extracts text, uploads to GCS, creates chunks and embeddings
     */
    private function processFileUpload(array $file, int $userId, ?int $folderId, ?string $documentName): array
    {
        $uploadedFileName = !empty($documentName) ? $documentName : $file['name'];
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $tmpName = $file['tmp_name'];
        $isImage = $this->isImageFile($fileExtension);

        // Extract text using OCR for images, or standard extraction for other files
        $extractedText = $this->extractTextFromFile($tmpName, $fileExtension, $file);
        
        // For images, log OCR result
        if ($isImage) {
            if (empty($extractedText)) {
                error_log("[OCR] WARNING - No text extracted from image: {$uploadedFileName}. File will still be saved.");
            } else {
                error_log("[OCR] COMPLETE - Successfully extracted " . strlen($extractedText) . " characters from image: {$uploadedFileName}");
            }
        }

        // Format the extracted text (even if empty, file will still be saved)
        $formattedText = !empty($extractedText) 
            ? $this->gemini->formatContent($extractedText) 
            : '';

        // Read file content for storage
        $fileContent = file_get_contents($tmpName);
                if ($fileContent === false) {
            throw new \Exception("Error reading file: {$uploadedFileName}");
                }

        // Upload file to GCS and save metadata to database
        // Note: File is saved even if extracted text is empty (for images without text)
        $newFileId = $this->lmModel->uploadFileToGCS($userId, $folderId, $formattedText, $fileContent, $file, $uploadedFileName);

        // Only create chunks and embeddings if text was extracted
        if (!empty($formattedText)) {
                    $chunks = $this->lmModel->splitTextIntoChunks($formattedText, $newFileId);
                    $embeddings = [];
                    foreach ($chunks as $chunk) {
                        $embeddings[] = $this->gemini->generateEmbedding($chunk);
                    }
                    $this->lmModel->saveChunksToDB($chunks, $embeddings, $newFileId);
        } else {
            error_log("No text content to chunk for file {$uploadedFileName} (ID: {$newFileId}). Chunks and embeddings skipped.");
        }

        return ['success' => true, 'fileId' => $newFileId, 'hasText' => !empty($formattedText)];
    }

    /**
     * Handles upload response: sets session messages and redirects based on success/failure
     */
    private function handleUploadResponse(int $uploadedCount, int $failedCount, array $errors, int $userId): void
    {
            if ($uploadedCount > 0 && $failedCount === 0) {
                $_SESSION['message'] = $uploadedCount === 1
                    ? "File uploaded successfully!"
                    : "{$uploadedCount} files uploaded successfully!";

                if ($uploadedCount === 1) {
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

    /**
     * Handles document upload (POST) or displays upload form (GET)
     */
    public function uploadDocument()
    {
        $this->checkSession();
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
            $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'guest';
            $folderId = isset($_POST['folderSelect']) && !empty($_POST['folderSelect']) && $_POST['folderSelect'] != '0'
                ? (int)$_POST['folderSelect']
                : null;

            $files = $_FILES['document'];
            $uploadedCount = 0;
            $failedCount = 0;
            $documentName = isset($_POST['documentName']) ? trim($_POST['documentName']) : null;
            $errors = [];

            $fileCount = is_array($files['name']) ? count($files['name']) : 1;
            
            for ($i = 0; $i < $fileCount; $i++) {
                $file = $this->normalizeFileArray($files, $i);
                
                if ($file === null) {
                    $fileName = is_array($files['name']) ? $files['name'][$i] : $files['name'];
                    $errorCode = is_array($files['error']) ? $files['error'][$i] : $files['error'];
                    $errors[] = "Error uploading {$fileName}: Upload error code {$errorCode}";
                    $failedCount++;
                    continue;
                }

                // Validate file type - reject PPTX files
                $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if ($fileExtension === 'pptx') {
                    $errors[] = "PPTX files are not supported. File: {$file['name']}";
                    $failedCount++;
                    continue;
                }

                try {
                    $this->processFileUpload($file, $userId, $folderId, $documentName);
                    $uploadedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Error uploading {$file['name']}: " . $e->getMessage();
                    $failedCount++;
                }

                // Break loop if single file (non-array)
                if (!is_array($files['name'])) {
                    break;
                }
            }

            $this->handleUploadResponse($uploadedCount, $failedCount, $errors, $userId);
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
     * Displays all documents and folders with search and folder filtering support
     */
    public function displayLearningMaterials()
    {
        $this->checkSession();

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();
        $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : null;
        $currentFolderId = isset($_GET['folder_id']) ? (int)$_GET['folder_id'] : null;

        // Filter out audio files
        $audioFileTypes = ['wav', 'mp3', 'ogg', 'm4a', 'aac', 'flac', 'wma'];
        $filterAudioFiles = function($files) use ($audioFileTypes) {
            return array_filter($files, function($file) use ($audioFileTypes) {
                $fileType = strtolower($file['fileType'] ?? '');
                return !in_array($fileType, $audioFileTypes);
            });
        };

        if (!empty($searchQuery)) {
            $fileList = $this->lmModel->searchFilesAndFolders($userId, $searchQuery);
            // Filter out audio files from search results
            if (isset($fileList['files'])) {
                $fileList['files'] = $filterAudioFiles($fileList['files']);
            }
            $currentFolderName = 'Search Results for "' . htmlspecialchars($searchQuery) . '"';
            $currentFolderPath = [];
        } else {
            $fileList = $this->lmModel->getFoldersAndFiles($userId, $currentFolderId);
            // Filter out audio files
            if (isset($fileList['files'])) {
                $fileList['files'] = $filterAudioFiles($fileList['files']);
            }
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
     * Displays a specific document with its content and metadata
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
     * Deletes a document from database and storage
     */
    public function deleteDocument()
    {
        $this->checkSession();

        $userId = (int)$_SESSION['user_id'];
        $fileId = isset($_POST['file_id']) ? (int)$_POST['file_id'] : 0;

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
     * Displays the new folder creation form
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
     * Creates a new folder with optional parent folder
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
     * Deletes a folder and its contents
     */
    public function deleteFolder()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $folderId = isset($_POST['folder_id']) ? (int)$_POST['folder_id'] : 0;

        if ($folderId === 0) {
            echo json_encode(['success' => false, 'message' => 'Folder ID not provided.']);
            exit();
        }

        try {
            if ($this->lmModel->deleteFolder($folderId, $userId)) {
                echo json_encode(['success' => true, 'message' => 'Folder deleted successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete folder.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * Displays the new document upload form
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
     * Builds folder path breadcrumb array from folder ID to root
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
     * Renames a folder via JSON API
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
     * Renames a file/document via JSON API
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
     * Moves a file/document to another folder via JSON API
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
     * Moves a folder to another folder via JSON API
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
     * Displays all summaries for a document
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
            $summaryList = $this->lmModel->getSummaryByFile($fileId);
            $user = $this->getUserInfo();

            require_once VIEW_SUMMARY;
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }
    }

    /**
     * Deletes a summary from database
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
     * Saves a summary as a new document file
     */
    public function saveSummaryAsFile()
    {
        $this->checkSession();

        $summaryId = isset($_POST['summary_id']) ? (int)$_POST['summary_id'] : 0;
        $fileId = isset($_POST['file_id']) ? (int)$_POST['file_id'] : 0;
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

    /**
     * Generates audio for summary using TTS, returns cached audio if available
     */
    public function audioSummary(){
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();
        $summaryId = isset($_POST['summary_id']) ? (int)$_POST['summary_id'] : 0;

        if ($summaryId === 0) {
            $this->sendJsonError('Summary ID not provided.');
        }

        try {
            $summary = $this->lmModel->getSummaryById($summaryId, $userId);
            if (!$summary) {
                $this->sendJsonError('Summary not found.');
            }

            // Check if audio already exists for this specific summary - if found, return it instead of generating new
            $existingAudioFile = $this->lmModel->getAudioFileForSummary($summaryId, $userId);
            if ($existingAudioFile && !empty($existingAudioFile['audioPath'])) {
                try {
                    $audioUrl = $this->lmModel->getAudioSignedUrl($existingAudioFile['audioPath']);
                    $this->sendJsonSuccess([
                        'audioUrl' => $audioUrl,
                        'cached' => true
                    ]);
                    return; // Stop execution - don't generate new audio
                } catch (\Exception $e) {
                    // If signed URL fails, file might be deleted, so continue to generate new one
                }
            }

            // Generate audio locally (only if no existing audio found or existing audio is inaccessible)
            // Clean markdown symbols before generating audio
            $cleanText = $this->cleanMarkdownForAudio($summary['content']);
            
            // Validate cleaned text is not empty
            if (empty(trim($cleanText))) {
                $this->sendJsonError('No text content available for audio generation after cleaning.');
            }
            
            $localAudioPath = $this->PiperService->synthesizeText($cleanText);
            if (!$localAudioPath || !file_exists($localAudioPath)) {
                // Log detailed error information for debugging
                error_log("[AudioSummary] Failed to generate audio. Summary ID: {$summaryId}, Text length: " . strlen($cleanText));
                error_log("[AudioSummary] PiperService returned: " . ($localAudioPath ?? 'null'));
                
                $errorMsg = 'Failed to generate audio file. ';
                $errorMsg .= 'Please check that Piper TTS is installed and accessible, ';
                $errorMsg .= 'and that the model file exists at the configured path.';
                throw new \RuntimeException($errorMsg);
            }

            // Upload to GCS and save to audio table (unique for this summary)
            $gcsAudioPath = $this->lmModel->uploadAudioFileToGCSForSummary($summaryId, $fileId, $localAudioPath);

            // Get signed URL for streaming
            $audioUrl = $this->lmModel->getAudioSignedUrl($gcsAudioPath);

            $this->sendJsonSuccess([
                'audioUrl' => $audioUrl,
                'cached' => false
            ]);
        } catch (\Exception $e) {
            $this->sendJsonError($e->getMessage());
        }
    }

    /**
     * Generates audio for note using TTS, returns cached audio if available
     */
    public function audioNote(){
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();
        $noteId = isset($_POST['note_id']) ? (int)$_POST['note_id'] : 0;

        if ($noteId === 0) {
            $this->sendJsonError('Note ID not provided.');
        }

        try {
            $note = $this->lmModel->getNoteById($noteId, $userId);
            if (!$note) {
                $this->sendJsonError('Note not found.');
            }

            // Check if audio already exists for this specific note - if found, return it instead of generating new
            $existingAudioFile = $this->lmModel->getAudioFileForNote($noteId, $userId);
            if ($existingAudioFile && !empty($existingAudioFile['audioPath'])) {
                try {
                    $audioUrl = $this->lmModel->getAudioSignedUrl($existingAudioFile['audioPath']);
                    $this->sendJsonSuccess([
                        'audioUrl' => $audioUrl,
                        'cached' => true
                    ]);
                    return; // Stop execution - don't generate new audio
                } catch (\Exception $e) {
                    // If signed URL fails, file might be deleted, so continue to generate new one
                }
            }

            // Generate audio locally (only if no existing audio found or existing audio is inaccessible)
            // Clean markdown symbols before generating audio
            $cleanText = $this->cleanMarkdownForAudio($note['content']);
            
            // Validate cleaned text is not empty
            if (empty(trim($cleanText))) {
                $this->sendJsonError('No text content available for audio generation after cleaning.');
            }
            
            $localAudioPath = $this->PiperService->synthesizeText($cleanText);
            if (!$localAudioPath || !file_exists($localAudioPath)) {
                // Log detailed error information for debugging
                error_log("[AudioNote] Failed to generate audio. Note ID: {$noteId}, Text length: " . strlen($cleanText));
                error_log("[AudioNote] PiperService returned: " . ($localAudioPath ?? 'null'));
                
                $errorMsg = 'Failed to generate audio file. ';
                $errorMsg .= 'Please check that Piper TTS is installed and accessible, ';
                $errorMsg .= 'and that the model file exists at the configured path.';
                throw new \RuntimeException($errorMsg);
            }

            // Upload to GCS and save to audio table (unique for this note)
            $gcsAudioPath = $this->lmModel->uploadAudioFileToGCSForNote($noteId, $fileId, $localAudioPath);

            // Get signed URL for streaming
            $audioUrl = $this->lmModel->getAudioSignedUrl($gcsAudioPath);

            $this->sendJsonSuccess([
                'audioUrl' => $audioUrl,
                'cached' => false
            ]);
        } catch (\Exception $e) {
            $this->sendJsonError($e->getMessage());
        }
    }

    // ============================================================================
    // NOTE PAGE (note.php)
    // ============================================================================

    /**
     * Displays all notes for a document
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
     * Deletes a note from database
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
     * Saves a note as a new document file
     */
    public function saveNoteAsFile()
    {
        $this->checkSession();

        $noteId = isset($_POST['note_id']) ? (int)$_POST['note_id'] : 0;
        $fileId = isset($_POST['file_id']) ? (int)$_POST['file_id'] : 0;
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

    /**
     * Uploads an image for a note and returns image URL via JSON API
     */
    public function uploadNoteImage(){
        header('Content-Type: application/json');

        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();
        $noteId = isset($_POST['note_id']) ? (int)$_POST['note_id'] : 0;

        if ($fileId === 0) {
            echo json_encode(['success' => false, 'message' => 'File ID not provided.']);
            exit();
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No image file uploaded or upload error.']);
            exit();
        }

        try {
            $imageFile = $_FILES['image'];
            $fileExtension = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

            if (!in_array($fileExtension, $allowedExtensions)) {
                echo json_encode(['success' => false, 'message' => 'Invalid image format. Allowed: ' . implode(', ', $allowedExtensions)]);
                exit();
            }

            // Validate file size (max 10MB)
            $maxSize = 10 * 1024 * 1024; // 10MB
            if ($imageFile['size'] > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'Image size exceeds 10MB limit.']);
                exit();
            }

            // Read file content
            $fileContent = file_get_contents($imageFile['tmp_name']);
            if ($fileContent === false) {
                throw new \Exception('Failed to read uploaded file.');
            }

            // Upload to GCS and save to database via model
            $result = $this->lmModel->saveNoteImage($noteId, $fileContent, $fileExtension, $userId);

            echo json_encode([
                'success' => true,
                'imageUrl' => $result['imageUrl'],
                'altText' => pathinfo($imageFile['name'], PATHINFO_FILENAME)
            ]);
        } catch (\Throwable $e) {
            error_log('Error uploading note image: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to upload image: ' . $e->getMessage()]);
        }
        exit();
    }

    // ============================================================================
    // MINDMAP PAGE (mindmap.php)
    // ============================================================================

    /**
     * Displays all mindmaps for a document
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
     * Generates a summary using AI and saves it to database
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
     * Generates notes using AI and saves them to database
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
     * Saves a manually created note to database
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
     * Updates an existing note's title and content
     */
    public function updateNote()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit();
        }

        $noteId = isset($_POST['note_id']) ? (int)$_POST['note_id'] : 0;
        $fileId = $this->resolveFileId();
        $title = isset($_POST['noteTitle']) ? trim($_POST['noteTitle']) : '';
        $content = isset($_POST['noteContent']) ? trim($_POST['noteContent']) : '';
        $userId = (int)$_SESSION['user_id'];

        if ($noteId === 0 || $fileId === 0) {
            echo json_encode(['success' => false, 'message' => 'Note ID or File ID missing.']);
            exit();
        }

        if ($title === '' || $content === '') {
            echo json_encode(['success' => false, 'message' => 'Title and content are required.']);
            exit();
        }

        try {
            $updated = $this->lmModel->updateNote($noteId, $fileId, $userId, $title, $content);
            if (!$updated) {
                echo json_encode(['success' => false, 'message' => 'Unable to update note.']);
                exit();
            }

            $note = $this->lmModel->getNoteById($noteId, $userId);
            echo json_encode([
                'success' => true,
                'note' => [
                    'title' => $note['title'] ?? $title,
                    'content' => $note['content'] ?? $content,
                    'createdAt' => $note['createdAt'] ?? date('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }


    /**
     * Generates a mindmap using AI and saves it to database
     */
    public function generateMindmap()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();

        if ($fileId === 0) {
            $this->sendJsonError('File ID not provided.');
        }

        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file) {
                $this->sendJsonError('File not found.');
            }

            $extractedText = $file['extracted_text'] ?? '';
            if (empty($extractedText)) {
                $this->sendJsonError('No extracted text found.');
            }

            $mindmapMarkdown = $this->gemini->generateMindmapMarkdown($extractedText);
            if (empty($mindmapMarkdown)) {
                throw new \RuntimeException('Failed to generate mindmap markdown.');
            }
            
            $title = $this->generateMindmapTitle($file['name'], $extractedText);
            $mindmapId = $this->saveMindmapData($fileId, $title, $mindmapMarkdown);

            $this->sendJsonSuccess([
                'markdown' => $mindmapMarkdown,
                'mindmapId' => $mindmapId,
                'title' => $title
            ]);
        } catch (\Throwable $e) {
            $this->sendJsonError($e->getMessage());
        }
    }

    /**
     * Retrieves a specific mindmap by ID and returns its data
     */
    public function viewMindmap()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $mindmapId = isset($_POST['mindmap_id']) ? (int)$_POST['mindmap_id'] : 0;
        $fileId = $this->resolveFileId();

        if ($mindmapId === 0) {
            $this->sendJsonError('Mindmap ID not provided.');
        }

        if ($fileId === 0) {
            $this->sendJsonError('File ID not provided.');
        }

        try {
            $payload = $this->buildMindmapResponse($mindmapId, $fileId, $userId);
            $this->sendJsonSuccess($payload);
        } catch (\Exception $e) {
            $this->sendJsonError($e->getMessage());
        }
    }

    /**
     * Loads mindmap structure (alias of viewMindmap)
     */
    public function loadMindmapStructure()
    {
        $this->viewMindmap();
    }

    /**
     * Updates mindmap markdown structure in database
     */
    public function updateMindmapStructure()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $requestData = $this->getJsonRequestData();
        $mindmapId = (int)($requestData['mindmap_id'] ?? 0);
        $fileId = (int)($requestData['file_id'] ?? $this->resolveFileId());
        $userId = (int)$_SESSION['user_id'];
        $markdown = trim($requestData['markdown'] ?? '');

        if ($mindmapId === 0 || $fileId === 0) {
            $this->sendJsonError('Mindmap ID or File ID missing.');
        }

        if (empty($markdown)) {
            $this->sendJsonError('Mindmap markdown is required.');
        }

        try {
            $existing = $this->lmModel->getMindmapById($mindmapId, $fileId, $userId);
            if (!$existing) {
                $this->sendJsonError('Mindmap not found.');
            }

            $updated = $this->lmModel->updateMindmap($mindmapId, $fileId, $userId, ['markdown' => $markdown]);
            if (!$updated) {
                $this->sendJsonError('Failed to update mindmap.');
            }

            $this->sendJsonSuccess();
        } catch (\Throwable $e) {
            $this->sendJsonError($e->getMessage());
        }
    }

    /**
     * Deletes a mindmap from database
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

    /**
     * Normalizes mindmap payload from database (handles JSON or plain markdown)
     */
    private function normalizeMindmapPayload($rawData): array
    {
        if (empty($rawData)) {
            return ['markdown' => '# Mindmap\n'];
        }

        // Database stores JSON string, so try to decode first
        if (is_string($rawData)) {
            $decoded = json_decode($rawData, true);
            
            // If JSON decode succeeded and we have markdown key
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['markdown'])) {
                return ['markdown' => $decoded['markdown']];
            }
            
            // If JSON decode failed or no markdown key, treat rawData as plain markdown
            // (for backward compatibility with old data format)
            if (json_last_error() !== JSON_ERROR_NONE || !isset($decoded['markdown'])) {
                return ['markdown' => $rawData];
            }
        }

        // If rawData is already an array (shouldn't happen, but handle it)
        if (is_array($rawData) && isset($rawData['markdown'])) {
            return ['markdown' => $rawData['markdown']];
        }

        return ['markdown' => '# Mindmap\n'];
    }

    /**
     * Builds standardized mindmap response payload with normalized data
     */
    private function buildMindmapResponse(int $mindmapId, int $fileId, int $userId): array
    {
        $mindmap = $this->lmModel->getMindmapById($mindmapId, $fileId, $userId);
        if (!$mindmap) {
            throw new \RuntimeException('Mindmap not found.');
        }

        $normalized = $this->normalizeMindmapPayload($mindmap['data'] ?? '');

        return [
            'mindmapId' => $mindmap['mindmapID'] ?? $mindmapId,
            'title' => $mindmap['title'] ?? '',
            'markdown' => $normalized['markdown']
        ];
    }

    /**
     * Generates mindmap title using AI from file name and content summary
     */
    private function generateMindmapTitle(string $fileName, string $extractedText): string
    {
        $summary = $this->gemini->generateSummary($extractedText, "A very short summary of the content");
        return $this->gemini->generateTitle($fileName . $summary);
    }

    /**
     * Saves mindmap data to database and returns mindmap ID
     */
    private function saveMindmapData(int $fileId, string $title, string $markdown): int
    {
        $payload = ['markdown' => $markdown];
        $dataJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
        return $this->lmModel->saveMindmap($fileId, $title, $dataJson);
    }

    /**
     * Gets JSON request data from POST or JSON body input
     */
    private function getJsonRequestData(): array
    {
        $rawInput = file_get_contents('php://input');
        $requestData = json_decode($rawInput, true);
        return is_array($requestData) ? $requestData : $_POST;
    }

    /**
     * Sends JSON success response and exits
     */
    private function sendJsonSuccess(array $data = []): void
    {
        echo json_encode(['success' => true] + $data);
        exit();
    }

    /**
     * Sends JSON error response and exits
     */
    private function sendJsonError(string $message): void
    {
        echo json_encode(['success' => false, 'message' => $message]);
        exit();
    }

    /**
     * Extracts correct answer(s) from options array
     * Returns string for single answer, array for multiple (checkbox)
     */
    private function getCorrectAnswerFromOptions(array $options): string|array
    {
        $correctAnswers = [];
        foreach ($options as $option) {
            // Check both boolean true and integer 1 for isCorrect (handles TINYINT from DB)
            $isCorrect = isset($option['isCorrect']) && (
                $option['isCorrect'] === true || 
                $option['isCorrect'] === 1 || 
                $option['isCorrect'] === '1' ||
                (is_string($option['isCorrect']) && strtolower($option['isCorrect']) === 'true')
            );
            if ($isCorrect) {
                $text = trim((string)($option['text'] ?? ''));
                if (!empty($text)) {
                    $correctAnswers[] = $text;
                }
            }
        }
        
        // Return single string for single answer, array for multiple
        if (count($correctAnswers) === 1) {
            return $correctAnswers[0];
        } elseif (count($correctAnswers) > 1) {
            return $correctAnswers;
        }
        
        // Log warning if no correct answer found (for debugging)
        if (empty($options)) {
            error_log('[Quiz Evaluation] No options provided to getCorrectAnswerFromOptions');
        } else {
            error_log('[Quiz Evaluation] No correct answer found in options. Options: ' . json_encode($options));
        }
        return '';
    }

    /**
     * Clean markdown symbols from text for audio generation
     */
    private function cleanMarkdownForAudio(string $text): string
    {
        // Remove markdown headers (# ## ### etc.)
        $text = preg_replace('/^#{1,6}\s+/m', '', $text);
        
        // Remove bold (**text** or __text__)
        $text = preg_replace('/\*\*(.+?)\*\*/', '$1', $text);
        $text = preg_replace('/__(.+?)__/', '$1', $text);
        
        // Remove italic (*text* or _text_)
        $text = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/', '$1', $text);
        $text = preg_replace('/(?<!_)_(?!_)(.+?)(?<!_)_(?!_)/', '$1', $text);
        
        // Remove links [text](url)
        $text = preg_replace('/\['.'([^\\\]]+)\''.'\]\([^\)]+\)/', '$1', $text);
        
        // Remove images ![alt](url)
        $text = preg_replace('/!\['.'([^\\\]*)\\]\([^\)]+\)/', '', $text);
        
        // Remove code blocks ```
        $text = preg_replace('/```[\s\S]*?```/', '', $text);
        
        // Remove inline code `code`
        $text = preg_replace('/`([^`]+)`/', '$1', $text);
        
        // Remove strikethrough ~~text~~
        $text = preg_replace('/~~(.+?)~~/', '$1', $text);
        
        // Remove list markers (-, *, +, 1., 2., etc.)
        $text = preg_replace('/^[
	 ]*[-*+]\s+/m', '', $text);
        $text = preg_replace('/^[
	 ]*\d+\.\s+/m', '', $text);
        
        // Remove blockquotes >
        $text = preg_replace('/^>\s+/m', '', $text);
        
        // Remove horizontal rules ---
        $text = preg_replace('/^[-*]{3,}$/m', '', $text);
        
        // Remove table separators |---|---|
        $text = preg_replace('/\|[\s\-:]+\|/', '', $text);
        
        // Remove table pipes |
        $text = preg_replace('/\|/', ' ', $text);
        
        // Clean up multiple spaces
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Clean up multiple newlines
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        
        // Trim whitespace
        $text = trim($text);
        
        return $text;
    }

    // ============================================================================
    // CREATE SUMMARY PAGE (createSummary.php)
    // ============================================================================

    /**
     * Displays the create summary form
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
     * Exports a summary as PDF file download
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

            $this->exportService->generatePdf($summary['title'], $summary['content']);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . SUMMARY);
            exit();
        }
    }

    /**
     * Exports a summary as DOCX file download
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

            $this->exportService->generateDocx($summary['title'], $summary['content']);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . SUMMARY);
            exit();
        }
    }

    /**
     * Exports a summary as TXT file download
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

            $this->exportService->generateTxt($summary['title'], $summary['content']);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . SUMMARY);
            exit();
        }
    }

    /**
     * Exports a note as PDF file download
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

            $this->exportService->generatePdf($note['title'], $note['content']);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . NOTE);
            exit();
        }
    }

    /**
     * Exports a note as DOCX file download
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

            $this->exportService->generateDocx($note['title'], $note['content']);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . NOTE);
            exit();
        }
    }

    /**
     * Exports a note as TXT file download
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

            $this->exportService->generateTxt($note['title'], $note['content']);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . NOTE);
            exit();
        }
    }


    // ============================================================================
    // FLASHCARD PAGE (flashcard.php)
    // ============================================================================

    /**
     * Displays all flashcards for a document
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

            $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
            $flashcards = $this->lmModel->getFlashcardsByFile($fileId);
            $user = $this->getUserInfo();

            require_once VIEW_FLASHCARD;
            } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }
    }

    /**
     * Generates flashcards using AI and saves them to database
     */
    public function generateFlashcards()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();

        if ($fileId === 0) {
            $this->sendJsonError('File ID not provided.');
        }

        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file) {
                $this->sendJsonError('File not found.');
            }

            $extractedText = $file['extracted_text'] ?? '';
            if (empty($extractedText)) {
                $this->sendJsonError('No extracted text found.');
            }

            $db = new Database();
            $conn = $db->connect();
            $conn->beginTransaction();

            try {
                $flashcardAmount = isset($_POST['flashcardAmount']) ? (int)$_POST['flashcardAmount'] : 15;
                $flashcardType = isset($_POST['flashcardType']) ? trim($_POST['flashcardType']) : 'medium';
                
                $flashcardsJson = $this->gemini->generateFlashcards($extractedText, null, $flashcardAmount, $flashcardType);
                if (empty($flashcardsJson)) {
                    error_log('[Flashcard Generation] Empty response from Gemini API');
                    throw new \Exception('Failed to generate flashcards.');
                }

                // Decode JSON string to array
                $flashcardsData = json_decode($flashcardsJson, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log('[Flashcard Generation] JSON decode error: ' . json_last_error_msg());
                    error_log('[Flashcard Generation] Raw response: ' . substr($flashcardsJson, 0, 500));
                    throw new \Exception('Invalid flashcard data format received: ' . json_last_error_msg());
                }
                
                if (!isset($flashcardsData['flashcards']) || !is_array($flashcardsData['flashcards'])) {
                    error_log('[Flashcard Generation] Missing flashcards array in response');
                    error_log('[Flashcard Generation] Response structure: ' . json_encode(array_keys($flashcardsData ?? [])));
                    throw new \Exception('Invalid flashcard data format received.');
                }

                $flashcards = $flashcardsData['flashcards'];
                if (empty($flashcards)) {
                    throw new \Exception('No flashcards generated.');
                }

                // Limit flashcards to the requested amount (prevent AI from generating too many)
                $flashcards = array_slice($flashcards, 0, $flashcardAmount);
                
                // Log for debugging
                error_log("[Flashcard Generation] Requested: {$flashcardAmount}, Received: " . count($flashcardsData['flashcards']) . ", Saving: " . count($flashcards));

                $title = $this->gemini->generateTitle($file['name']);
                
                // Collect all terms and definitions into arrays
                $terms = [];
                $definitions = [];
                foreach ($flashcards as $card) {
                    if (!isset($card['term']) || !isset($card['definition'])) {
                        continue; // Skip invalid cards
                    }
                    $terms[] = $card['term'];
                    $definitions[] = $card['definition'];
                }
                
                if (empty($terms) || empty($definitions)) {
                    throw new \Exception('No valid flashcards to save.');
                }
                
                // Save all flashcards as JSON in one row
                $flashcardId = $this->lmModel->saveFlashcards($fileId, $title, $terms, $definitions);
                $savedCount = count($terms);
                
                // Log final count
                error_log("[Flashcard Generation] Successfully saved {$savedCount} flashcards for file ID: {$fileId} in one row");

                $conn->commit();
                
                // Get the flashcard to retrieve createdAt timestamp
                $savedFlashcard = $this->lmModel->getFlashcardsById($flashcardId);
                
                // Prepare listItem metadata for frontend list update
                $listItem = [
                    'title' => $title,
                    'flashcardID' => $flashcardId,
                    'createdAt' => $savedFlashcard['createdAt'] ?? date('Y-m-d H:i:s'),
                    'cardCount' => $savedCount
                ];
                
                // Return format expected by frontend: preview.cards and listItem
                $this->sendJsonSuccess([
                    'preview' => [
                        'title' => $title,
                        'cards' => $flashcards
                    ],
                    'listItem' => $listItem,
                    'flashcards' => $flashcards // Keep for backward compatibility
                ]);
            } catch (\Throwable $e) {
                $conn->rollBack();
                $this->sendJsonError($e->getMessage());
            }
        } catch (\Throwable $e) {
            $this->sendJsonError($e->getMessage());
        }
    }

    /**
     * Retrieves a specific flashcard by ID and returns its data
     */
    public function getFlashcard()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $flashcardId = isset($_POST['flashcard_id']) ? (int)$_POST['flashcard_id'] : 0;

        if ($flashcardId === 0) {
            $this->sendJsonError('Flashcard ID not provided.');
        }

        try {
            $flashcard = $this->lmModel->getFlashcardWithOwner($flashcardId, $userId);
            if (!$flashcard) {
                $this->sendJsonError('Flashcard not found.');
            }

            $this->sendJsonSuccess(['flashcard' => $flashcard]);
        } catch (\Throwable $e) {
            $this->sendJsonError($e->getMessage());
        }
    }

    /**
     * Retrieves all flashcards with the same title as the specified flashcard ID
     * Returns them in a format compatible with the view (cards array)
     */
    public function viewFlashcard()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $flashcardId = isset($_POST['flashcard_id']) ? (int)$_POST['flashcard_id'] : 0;
        $fileId = isset($_POST['file_id']) ? (int)$_POST['file_id'] : 0;

        if ($flashcardId === 0) {
            $this->sendJsonError('Flashcard ID not provided.');
        }

        try {
            // Get the flashcard to find its title
            $flashcard = $this->lmModel->getFlashcardWithOwner($flashcardId, $userId);
            if (!$flashcard) {
                $this->sendJsonError('Flashcard not found.');
            }

            // Get the flashcard (stored as comma-separated strings with "," separator in one row per title)
            $title = $flashcard['title'];
            $flashcardFileId = $flashcard['fileID'];
            
            // Get the flashcard set (should be one row with comma-separated strings)
            $flashcardSet = $this->lmModel->getFlashcardsByTitle($title, $flashcardFileId);
            
            if (empty($flashcardSet)) {
                $this->sendJsonError('No flashcards found.');
            }
            
            // Get the first (and should be only) flashcard row
            $cardData = $flashcardSet[0];
            $termString = $cardData['term'] ?? '';
            $definitionString = $cardData['definition'] ?? '';
            
            // Parse comma-separated strings (format: "term1","term2","term3")
            $terms = [];
            $definitions = [];
            
            if (!empty($termString)) {
                // Split by comma, but handle escaped commas and quoted strings
                $currentTerm = '';
                $inQuotes = false;
                $escaped = false;
                
                for ($i = 0; $i < strlen($termString); $i++) {
                    $char = $termString[$i];
                    
                    if ($escaped) {
                        $currentTerm .= $char;
                        $escaped = false;
                    } elseif ($char === '\\') {
                        $currentTerm .= $char;
                        $escaped = true;
                    } elseif ($char === '"') {
                        $inQuotes = !$inQuotes;
                        $currentTerm .= $char;
                    } elseif ($char === ',' && !$inQuotes) {
                        // Found separator
                        $terms[] = trim($currentTerm, '"');
                        $currentTerm = '';
                    } else {
                        $currentTerm .= $char;
                    }
                }
                if (!empty($currentTerm)) {
                    $terms[] = trim($currentTerm, '"');
                }
                
                // Unescape the content
                $terms = array_map(function($term) {
                    return str_replace(['\\"', '\\,', '\\\\'], ['"', ',', '\\'], $term);
                }, $terms);
            }
            
            if (!empty($definitionString)) {
                // Split by comma, but handle escaped commas and quoted strings
                $currentDef = '';
                $inQuotes = false;
                $escaped = false;
                
                for ($i = 0; $i < strlen($definitionString); $i++) {
                    $char = $definitionString[$i];
                    
                    if ($escaped) {
                        $currentDef .= $char;
                        $escaped = false;
                    } elseif ($char === '\\') {
                        $currentDef .= $char;
                        $escaped = true;
                    } elseif ($char === '"') {
                        $inQuotes = !$inQuotes;
                        $currentDef .= $char;
                    } elseif ($char === ',' && !$inQuotes) {
                        // Found separator
                        $definitions[] = trim($currentDef, '"');
                        $currentDef = '';
                    } else {
                        $currentDef .= $char;
                    }
                }
                if (!empty($currentDef)) {
                    $definitions[] = trim($currentDef, '"');
                }
                
                // Unescape the content
                $definitions = array_map(function($def) {
                    return str_replace(['\\"', '\\,', '\\\\'], ['"', ',', '\\'], $def);
                }, $definitions);
            }
            
            // Handle backward compatibility: if empty or single value, treat as single card
            if (empty($terms) && !empty($termString)) {
                $terms = [$termString];
            }
            if (empty($definitions) && !empty($definitionString)) {
                $definitions = [$definitionString];
            }
            
            // Filter out empty items
            $terms = array_filter($terms, function($term) { return !empty(trim($term)); });
            $definitions = array_filter($definitions, function($def) { return !empty(trim($def)); });
            
            // Re-index arrays
            $terms = array_values($terms);
            $definitions = array_values($definitions);
            
            // Format for the view: return as cards array (term on front, definition on back)
            $cards = [];
            $maxLength = max(count($terms), count($definitions));
            for ($i = 0; $i < $maxLength; $i++) {
                $cards[] = [
                    'term' => $terms[$i] ?? '',
                    'definition' => $definitions[$i] ?? ''
                ];
            }
            
            if (empty($cards)) {
                $this->sendJsonError('No valid flashcards found.');
            }

            $this->sendJsonSuccess([
                'flashcard' => [
                    'title' => $title,
                    'cards' => $cards
                ]
            ]);
        } catch (\Throwable $e) {
            $this->sendJsonError($e->getMessage());
        }
    }

    /**
     * Creates flashcards manually with multiple term/definition pairs
     */
    public function createFlashcard()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $fileId = isset($_POST['file_id']) ? (int)$_POST['file_id'] : 0;
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $terms = isset($_POST['terms']) && is_array($_POST['terms']) ? $_POST['terms'] : [];
        $definitions = isset($_POST['definitions']) && is_array($_POST['definitions']) ? $_POST['definitions'] : [];

        if ($fileId === 0) {
            $this->sendJsonError('File ID not provided.');
        }

        if (empty($title)) {
            $this->sendJsonError('Title is required.');
        }

        if (empty($terms) || empty($definitions) || count($terms) !== count($definitions)) {
            $this->sendJsonError('Terms and definitions must be provided in matching pairs.');
        }

        try {
        $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file) {
                $this->sendJsonError('File not found.');
            }

            $db = new Database();
            $conn = $db->connect();
            $conn->beginTransaction();

            try {
                // Filter out empty pairs
                $validTerms = [];
                $validDefinitions = [];
                foreach ($terms as $index => $term) {
                    $term = trim($term);
                    $definition = isset($definitions[$index]) ? trim($definitions[$index]) : '';
                    
                    if (!empty($term) && !empty($definition)) {
                        $validTerms[] = $term;
                        $validDefinitions[] = $definition;
                    }
                }
                
                if (empty($validTerms) || empty($validDefinitions)) {
                    throw new \Exception('No valid flashcard pairs to save.');
                }
                
                // Save all flashcards as JSON in one row
                $this->lmModel->saveFlashcards($fileId, $title, $validTerms, $validDefinitions);

                $conn->commit();
                $this->sendJsonSuccess(['message' => 'Flashcards created successfully.']);
            } catch (\Throwable $e) {
                $conn->rollBack();
                throw $e;
            }
        } catch (\Throwable $e) {
            $this->sendJsonError('Failed to create flashcards: ' . $e->getMessage());
        }
    }

    /**
     * Updates flashcards by deleting all flashcards with the same title and recreating them
     * Handles multiple term/definition pairs
     */
    public function updateFlashcard()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $flashcardId = isset($_POST['flashcard_id']) ? (int)$_POST['flashcard_id'] : 0;
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $terms = isset($_POST['terms']) && is_array($_POST['terms']) ? $_POST['terms'] : [];
        $definitions = isset($_POST['definitions']) && is_array($_POST['definitions']) ? $_POST['definitions'] : [];

        if ($flashcardId === 0) {
            $this->sendJsonError('Flashcard ID missing.');
        }

        if (empty($title)) {
            $this->sendJsonError('Title is required.');
        }

        if (empty($terms) || empty($definitions) || count($terms) !== count($definitions)) {
            $this->sendJsonError('Terms and definitions must be provided in matching pairs.');
        }

        try {
            // Verify ownership of the flashcard
            $flashcard = $this->lmModel->getFlashcardWithOwner($flashcardId, $userId);
            if (!$flashcard) {
                $this->sendJsonError('Flashcard not found or you do not have permission to update it.');
            }

            $fileId = $flashcard['fileID'];
            $oldTitle = $flashcard['title'];

            $db = new Database();
            $conn = $db->connect();
            $conn->beginTransaction();

            try {
                // Delete all flashcards with the old title
                $this->lmModel->deleteFlashcardsByTitle($oldTitle, $fileId);

                // Filter out empty pairs
                $validTerms = [];
                $validDefinitions = [];
                foreach ($terms as $index => $term) {
                    $term = trim($term);
                    $definition = isset($definitions[$index]) ? trim($definitions[$index]) : '';
                    
                    if (!empty($term) && !empty($definition)) {
                        $validTerms[] = $term;
                        $validDefinitions[] = $definition;
                    }
                }
                
                if (empty($validTerms) || empty($validDefinitions)) {
                    throw new \Exception('No valid flashcard pairs to save.');
                }
                
                // Save all flashcards as JSON in one row
                $this->lmModel->saveFlashcards($fileId, $title, $validTerms, $validDefinitions);

                $conn->commit();
                $this->sendJsonSuccess(['message' => 'Flashcards updated successfully.']);
        } catch (\Throwable $e) {
                $conn->rollBack();
                throw $e;
            }
        } catch (\Throwable $e) {
            $this->sendJsonError('Failed to update flashcards: ' . $e->getMessage());
        }
    }

    /**
     * Deletes a single flashcard by ID
     */
    public function deleteFlashcard()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $flashcardId = isset($_POST['flashcard_id']) ? (int)$_POST['flashcard_id'] : 0;
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $fileId = isset($_POST['file_id']) ? (int)$_POST['file_id'] : 0;

        try {
            // If title is provided, delete entire set by title
            if (!empty($title) && $fileId > 0) {
                // Verify file ownership
        $file = $this->lmModel->getFile($userId, $fileId);
                if (!$file) {
                    $this->sendJsonError('File not found or you do not have permission to delete flashcards.');
                }
                
                $deleted = $this->lmModel->deleteFlashcardsByTitle($title, $fileId);
                if (!$deleted) {
                    $this->sendJsonError('Failed to delete flashcard set.');
                }
                
                $this->sendJsonSuccess(['message' => 'Flashcard set deleted successfully.']);
            } 
            // Otherwise, delete single flashcard by ID (backward compatibility)
            else if ($flashcardId > 0) {
                // Verify ownership before deleting
                $flashcard = $this->lmModel->getFlashcardWithOwner($flashcardId, $userId);
                if (!$flashcard) {
                    $this->sendJsonError('Flashcard not found or you do not have permission to delete it.');
                }

                $deleted = $this->lmModel->deleteFlashcardById($flashcardId, $userId);
                if (!$deleted) {
                    $this->sendJsonError('Failed to delete flashcard.');
                }

                $this->sendJsonSuccess(['message' => 'Flashcard deleted successfully.']);
            } else {
                $this->sendJsonError('Flashcard ID or title not provided.');
            }
        } catch (\Throwable $e) {
            $this->sendJsonError('Error deleting flashcard: ' . $e->getMessage());
        }
    }

    /**
     * Deletes all flashcards with a specific title for a file
     */
    public function deleteFlashcards()
    {
        $this->checkSession();

        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $fileId = $this->resolveFileId();

        if (empty($title) || $fileId === 0) {
            $_SESSION['error'] = "Title or File ID not provided.";
            header('Location: ' . FLASHCARD);
            exit();
        }

        try {
            $this->lmModel->deleteFlashcardsByTitle($title, $fileId);
            header('Location: ' . FLASHCARD);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . FLASHCARD);
            exit();
        }
    }

    // ============================================================================
    // QUIZ PAGE (quiz.php)
    // ============================================================================

    /**
     * Displays all quizzes for a document
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

            $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
            $quizList = $this->lmModel->getQuizByFile($fileId);
            $user = $this->getUserInfo();

            require_once VIEW_QUIZ;
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }
    }

    /**
     * Generates a quiz using AI and saves it to database
     */
    public function generateQuiz()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();
        $totalQuestions = isset($_POST['totalQuestions']) ? (int)$_POST['totalQuestions'] : 5;
        $examMode = isset($_POST['examMode']) && $_POST['examMode'] == '1' ? 1 : 0;
        $questionDifficulty = isset($_POST['questionDifficulty']) ? trim($_POST['questionDifficulty']) : 'remember';
        
        // Handle questionDistribution (from frontend) or questionTypes (legacy)
        $distributionRaw = $_POST['questionDistribution'] ?? null;
        if ($distributionRaw) {
            $distribution = is_array($distributionRaw) ? $distributionRaw : json_decode($distributionRaw, true);
            $distribution = is_array($distribution) ? $distribution : [];
            
            // Validate distribution sums to totalQuestions
            $distributionSum = array_sum($distribution);
            if ($distributionSum !== $totalQuestions) {
                error_log('[Quiz Generation] Distribution mismatch: sum=' . $distributionSum . ', total=' . $totalQuestions);
                $this->sendJsonError('Question distribution does not match total questions.');
            }
            
            // Check if distribution is empty
            if (empty($distribution) || $distributionSum === 0) {
                $this->sendJsonError('No questions allocated. Please assign questions to at least one type.');
            }
            } else {
            // Legacy: convert questionTypes array to distribution
            $questionTypes = isset($_POST['questionTypes']) && is_array($_POST['questionTypes']) ? $_POST['questionTypes'] : ['multiple_choice'];
            $distribution = [];
            $questionsPerType = $totalQuestions / count($questionTypes);
            foreach ($questionTypes as $type) {
                $distribution[$type] = (int)round($questionsPerType);
            }
        }

        if ($fileId === 0) {
            $this->sendJsonError('File ID not provided.');
        }

        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file) {
                $this->sendJsonError('File not found.');
            }

            $extractedText = $file['extracted_text'] ?? '';
            if (empty($extractedText)) {
                $this->sendJsonError('No extracted text found.');
            }

            // Get instructions if provided
            $instructions = isset($_POST['instructions']) ? trim($_POST['instructions']) : null;
            
            $quizDataJson = $this->gemini->generateMixedQuiz($extractedText, $distribution, $totalQuestions, $questionDifficulty, $instructions);
            if (empty($quizDataJson)) {
                $this->sendJsonError('Failed to generate quiz.');
            }

            // Decode JSON string to array
            $quizData = json_decode($quizDataJson, true);
            if (json_last_error() !== JSON_ERROR_NONE || !isset($quizData['quiz']) || !is_array($quizData['quiz'])) {
                error_log('[Quiz Generation] JSON decode error: ' . json_last_error_msg());
                error_log('[Quiz Generation] Raw response: ' . substr($quizDataJson, 0, 500));
                $this->sendJsonError('Invalid quiz data format received.');
            }

            $quizData = $quizData['quiz'];
            if (empty($quizData)) {
                $this->sendJsonError('No quiz questions generated.');
            }

            $title = $this->gemini->generateTitle($file['name']);
            // Store Bloom's taxonomy level in questionConfig
            $questionConfig = ['bloomLevel' => $questionDifficulty];
            $quizId = $this->lmModel->saveQuiz($fileId, $totalQuestions, $title, $questionConfig, $examMode);

            foreach ($quizData as $question) {
                $explanation = $question['explanation'] ?? null;
                $questionId = $this->lmModel->saveQuestion($quizId, $question['type'], $question['question'], $explanation);
                if (isset($question['options']) && is_array($question['options'])) {
                    $correctAnswer = $question['answer'] ?? '';
                    $correctAnswers = is_array($correctAnswer) ? $correctAnswer : [$correctAnswer];
                    
                    // Normalize correct answers for comparison (trim and lowercase)
                    $normalizedCorrectAnswers = array_map(function($ans) {
                        return trim(strtolower((string)$ans));
                    }, $correctAnswers);
                    
                    foreach ($question['options'] as $optionText) {
                        // Normalize option text for comparison
                        $normalizedOption = trim(strtolower((string)$optionText));
                        // Check if this option is correct (case-insensitive comparison)
                        $isCorrect = in_array($normalizedOption, $normalizedCorrectAnswers, true);
                        $this->lmModel->saveOption($questionId, $optionText, $isCorrect);
                    }
                }
            }

            $this->sendJsonSuccess(['quizId' => $quizId]);
        } catch (\Throwable $e) {
            $this->sendJsonError($e->getMessage());
        }
    }

    /**
     * Retrieves a specific quiz by ID and returns its data
     */
    public function getQuiz()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $quizId = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;

        if ($quizId === 0) {
            $this->sendJsonError('Quiz ID not provided.');
        }

        try {
            $quiz = $this->lmModel->getQuizById($quizId);
            if (!$quiz) {
                $this->sendJsonError('Quiz not found.');
            }

            $questions = $this->lmModel->getQuestionsByQuiz($quizId);
            foreach ($questions as &$question) {
                $options = $this->lmModel->getOptionsByQuestion($question['questionID']);
                // Transform options from objects to array of strings for frontend
                $question['options'] = array_map(function($option) {
                    return $option['text'] ?? '';
                }, $options);
                // Also include answer field for evaluation
                $question['answer'] = $this->getCorrectAnswerFromOptions($options);
            }

            $this->sendJsonSuccess(['quiz' => $quiz, 'questions' => $questions]);
        } catch (\Exception $e) {
            $this->sendJsonError($e->getMessage());
        }
    }

    /**
     * Views a quiz for taking (checks if already completed)
     */
    public function viewQuiz()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $quizId = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;

        if ($quizId === 0) {
            $this->sendJsonError('Quiz ID not provided.');
        }

        try {
            $quiz = $this->lmModel->getQuizById($quizId);
            if (!$quiz) {
                $this->sendJsonError('Quiz not found.');
            }

            // Check if quiz is already completed
            $hasAttempt = $this->lmModel->hasQuizAttempt($quizId, $userId);
            $alreadyCompleted = ($quiz['status'] ?? 'pending') === 'completed' && $hasAttempt;

            $questions = $this->lmModel->getQuestionsByQuiz($quizId);
            // Get Bloom's taxonomy level from quiz config
            $questionConfig = !empty($quiz['questionConfig']) ? json_decode($quiz['questionConfig'], true) : [];
            $bloomLevel = $questionConfig['bloomLevel'] ?? 'remember';
            
            foreach ($questions as &$question) {
                $options = $this->lmModel->getOptionsByQuestion($question['questionID']);
                // Transform options from objects to array of strings for frontend
                $question['options'] = array_map(function($option) {
                    return $option['text'] ?? '';
                }, $options);
                // Also include answer field for evaluation
                $question['answer'] = $this->getCorrectAnswerFromOptions($options);
                // Add Bloom's taxonomy level for evaluation
                $question['bloomLevel'] = $bloomLevel;
            }

            $this->sendJsonSuccess([
                'quiz' => $questions,
                'examMode' => (int)($quiz['examMode'] ?? 0),
                'alreadyCompleted' => $alreadyCompleted
            ]);
        } catch (\Exception $e) {
            $this->sendJsonError($e->getMessage());
        }
    }

    /**
     * Views a quiz attempt with answers and feedback
     */
    public function viewQuizAttempt()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $quizId = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;

        if ($quizId === 0) {
            $this->sendJsonError('Quiz ID not provided.');
        }

        try {
            $quiz = $this->lmModel->getQuizById($quizId);
            if (!$quiz) {
                $this->sendJsonError('Quiz not found.');
            }

            $questions = $this->lmModel->getQuestionsByQuiz($quizId);
            // Get Bloom's taxonomy level from quiz config
            $questionConfig = !empty($quiz['questionConfig']) ? json_decode($quiz['questionConfig'], true) : [];
            $bloomLevel = $questionConfig['bloomLevel'] ?? 'remember';
            
            foreach ($questions as &$question) {
                $options = $this->lmModel->getOptionsByQuestion($question['questionID']);
                // Transform options from objects to array of strings for frontend
                $question['options'] = array_map(function($option) {
                    return $option['text'] ?? '';
                }, $options);
                // Also include answer field for evaluation
                $question['answer'] = $this->getCorrectAnswerFromOptions($options);
                // Add Bloom's taxonomy level for evaluation
                $question['bloomLevel'] = $bloomLevel;
            }

            // Get latest attempt
            $attempt = $this->lmModel->getLatestQuizAttempt($quizId, $userId);
            $attemptData = null;
            
            if ($attempt) {
                $savedAnswers = json_decode($attempt['answers'] ?? '[]', true);
                $savedAnswers = is_array($savedAnswers) ? $savedAnswers : [];
                
                // Convert answers from questionID-indexed to position-indexed for frontend compatibility
                // Frontend expects answers array indexed by question position (0, 1, 2...)
                $answersByPosition = [];
                foreach ($questions as $index => $question) {
                    $questionId = $question['questionID'];
                    if (isset($savedAnswers[$questionId])) {
                        $answersByPosition[$index] = $savedAnswers[$questionId];
                    }
                }
                
                // Convert feedback array to structured format matching frontend expectations
                $feedbackArray = json_decode($attempt['feedback'] ?? '[]', true);
                $feedbackArray = is_array($feedbackArray) ? $feedbackArray : [];
                
                // Check if structured results are stored in suggestions
                $suggestionsArray = json_decode($attempt['suggestions'] ?? '[]', true);
                $suggestionsArray = is_array($suggestionsArray) ? $suggestionsArray : [];
                $storedResults = null;
                if (isset($suggestionsArray['__results__'])) {
                    $storedResults = json_decode($suggestionsArray['__results__'], true);
                    unset($suggestionsArray['__results__']);
                }
                
                // Build structured feedback array indexed by question position
                $structuredFeedback = [];
                foreach ($questions as $index => $question) {
                    $questionId = $question['questionID'];
                    $userAnswer = $answersByPosition[$index] ?? null;
                    
                    // Use stored results if available (preferred for long answer questions)
                    if ($storedResults && isset($storedResults[$index])) {
                        $result = $storedResults[$index];
                        $structuredFeedback[$index] = [
                            'isCorrect' => $result['isCorrect'] ?? false,
                            'userAnswer' => $result['userAnswer'] ?? $userAnswer,
                            'correctAnswer' => $result['correctAnswer'] ?? null,
                            'suggestion' => $result['suggestion'] ?? '',
                            'explanation' => $result['explanation'] ?? ($question['explanation'] ?? '')
                        ];
                    } else {
                        // Fallback: reconstruct from feedback text
                        $feedbackText = $feedbackArray[$index] ?? '';
                        if (empty($feedbackText) && !empty($feedbackArray)) {
                            // Try to find feedback by question ID in the text
                            foreach ($feedbackArray as $fb) {
                                if (is_string($fb) && strpos($fb, "Question {$questionId}:") === 0) {
                                    $feedbackText = $fb;
                                    break;
                                }
                            }
                        }
                        
                        // Extract suggestion from suggestions array
                        $suggestionText = '';
                        if (!empty($suggestionsArray)) {
                            foreach ($suggestionsArray as $sug) {
                                if (is_string($sug) && strpos($sug, "Question {$questionId}:") === 0) {
                                    $suggestionText = str_replace("Question {$questionId}: ", '', $sug);
                                    break;
                                }
                            }
                        }
                        
                        // Determine if answer was correct based on feedback text
                        $isCorrect = !empty($feedbackText) && strpos($feedbackText, 'Correct') !== false;
                        
                        $structuredFeedback[$index] = [
                            'isCorrect' => $isCorrect,
                            'userAnswer' => $userAnswer,
                            'suggestion' => $suggestionText ?: $feedbackText
                        ];
                    }
                }
                
                $attemptData = [
                    'answers' => $answersByPosition,
                    'feedback' => $structuredFeedback,
                    'suggestions' => json_decode($attempt['suggestions'] ?? '[]', true),
                    'score' => $attempt['score'] ?? 0
                ];
            }

            $this->sendJsonSuccess([
                'quiz' => $questions,
                'attempt' => $attemptData,
                'examMode' => (int)($quiz['examMode'] ?? 0)
            ]);
        } catch (\Exception $e) {
            $this->sendJsonError($e->getMessage());
        }
    }

    /**
     * Submits quiz answers, evaluates them, and returns feedback
     */
    public function submitQuiz()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $quizId = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
        
        // Handle both 'answers' and 'user_answers' (from frontend)
        $answersRaw = $_POST['answers'] ?? $_POST['user_answers'] ?? '[]';
        $answers = is_array($answersRaw) ? $answersRaw : json_decode($answersRaw, true);
        $answers = is_array($answers) ? $answers : [];

        if ($quizId === 0) {
            $this->sendJsonError('Quiz ID not provided.');
        }

        try {
            $quiz = $this->lmModel->getQuizById($quizId);
            if (!$quiz) {
                $this->sendJsonError('Quiz not found.');
            }

            // Get questions and options for evaluation
            $questions = $this->lmModel->getQuestionsByQuiz($quizId);
            // Get Bloom's taxonomy level from quiz config
            $questionConfig = !empty($quiz['questionConfig']) ? json_decode($quiz['questionConfig'], true) : [];
            $bloomLevel = $questionConfig['bloomLevel'] ?? 'remember';
            
            foreach ($questions as &$question) {
                $options = $this->lmModel->getOptionsByQuestion($question['questionID']);
                // Transform options from objects to array of strings for frontend
                $question['options'] = array_map(function($option) {
                    return $option['text'] ?? '';
                }, $options);
                // Also include answer field for evaluation
                $question['answer'] = $this->getCorrectAnswerFromOptions($options);
                // Add Bloom's taxonomy level for evaluation
                $question['bloomLevel'] = $bloomLevel;
            }

            // Map answers from array index (0,1,2...) to questionID
            // Frontend sends answers indexed by question array position, but evaluateAnswers expects questionID keys
            $answersByQuestionId = [];
            $questionIndex = 0;
            foreach ($questions as $question) {
                if (isset($answers[$questionIndex])) {
                    // Normalize user answer (trim and convert to string)
                    $userAnswer = is_array($answers[$questionIndex]) ? $answers[$questionIndex] : trim((string)$answers[$questionIndex]);
                    $answersByQuestionId[$question['questionID']] = $userAnswer;
                }
                $questionIndex++;
            }

            // Debug: Log for troubleshooting
            error_log('[Quiz Evaluation] Total questions: ' . count($questions));
            error_log('[Quiz Evaluation] Answers received: ' . json_encode($answers));
            error_log('[Quiz Evaluation] Answers mapped: ' . json_encode($answersByQuestionId));

            $feedback = $this->gemini->evaluateAnswers($answersByQuestionId, $questions);
            $score = $feedback['score'] ?? 0;

            // Convert feedback and suggestions strings to arrays for saveQuizAttempt
            $feedbackArray = !empty($feedback['feedback']) ? explode("\n", $feedback['feedback']) : [];
            $suggestionsArray = !empty($feedback['suggestions']) ? explode("\n", $feedback['suggestions']) : null;
            
            // Store structured results array for proper review display (especially for long answer questions)
            // Map results from questionID-indexed to position-indexed for storage
            $resultsByPosition = [];
            if (!empty($feedback['results'])) {
                foreach ($questions as $index => $question) {
                    $questionId = $question['questionID'];
                    // Find the result for this question
                    foreach ($feedback['results'] as $result) {
                        if (isset($result['questionID']) && $result['questionID'] == $questionId) {
                            $resultsByPosition[$index] = $result;
                            break;
                        }
                    }
                }
            }

            // Save answers indexed by questionID (not position) for proper retrieval later
            // This ensures answers can be matched to questions even if question order changes
            // Store results in suggestions field as JSON for retrieval
            $suggestionsWithResults = $suggestionsArray;
            if (!empty($resultsByPosition)) {
                $suggestionsWithResults = array_merge($suggestionsArray ?? [], ['__results__' => json_encode($resultsByPosition)]);
            }
            $this->lmModel->saveQuizAttempt($quizId, $userId, $answersByQuestionId, $feedbackArray, $suggestionsWithResults, $score, $quiz['examMode']);
            $this->lmModel->updateQuizStatus($quizId, 'completed', $score);

            // Return feedback with results array for frontend
            $this->sendJsonSuccess([
                'feedback' => $feedback,
                'results' => $feedback['results'] ?? [],
                'percentage' => $feedback['percentage'] ?? $score,
                'score' => $score,
                'correctCount' => $feedback['correctCount'] ?? 0,
                'totalQuestions' => $feedback['totalQuestions'] ?? count($questions),
                'examMode' => (int)($quiz['examMode'] ?? 0)
            ]);
        } catch (\Throwable $e) {
            $this->sendJsonError($e->getMessage());
        }
    }

    /**
     * Deletes a quiz from database
     */
    public function deleteQuiz()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $quizId = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
        $userId = (int)$_SESSION['user_id'];

        if ($quizId === 0) {
            $this->sendJsonError('Quiz ID not provided.');
        }

        try {
            $deleted = $this->lmModel->deleteQuiz($quizId, $userId);
            if ($deleted) {
                $this->sendJsonSuccess(['message' => 'Quiz deleted successfully.']);
            } else {
                $this->sendJsonError('Quiz not found or you do not have permission to delete it.');
            }
        } catch (\Exception $e) {
            $this->sendJsonError('Error deleting quiz: ' . $e->getMessage());
        }
    }

    /**
     * Gets quiz statistics for the current user
     */
    public function getQuizStatistics()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $fileId = isset($_GET['file_id']) ? (int)$_GET['file_id'] : null;

        try {
            $statistics = $this->lmModel->getQuizStatistics($userId, $fileId);
            $this->sendJsonSuccess(['data' => $statistics]);
        } catch (\Exception $e) {
            $this->sendJsonError('Error retrieving quiz statistics: ' . $e->getMessage());
        }
    }

    // ============================================================================
    // CHATBOT PAGE (chatbot.php)
    // ============================================================================

    /**
     * Displays the chatbot interface
     */
    public function chatbot()
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
            if (!$file) {
                $_SESSION['error'] = "File not found.";
                header('Location: ' . DISPLAY_LEARNING_MATERIALS);
                exit();
            }

            $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
            
            // Get chat history for the view
            $questionChats = [];
            $responseChats = [];
            $chatbot = $this->lmModel->getChatBotByFile($fileId);
            if ($chatbot && isset($chatbot['chatbotID'])) {
                $allQuestionChats = $this->lmModel->getQuestionChatByChatbot($chatbot['chatbotID']);
                // Sort by createdAt ASC for chronological order
                if (!empty($allQuestionChats)) {
                    usort($allQuestionChats, function($a, $b) {
                        $dateA = isset($a['createdAt']) ? strtotime($a['createdAt']) : 0;
                        $dateB = isset($b['createdAt']) ? strtotime($b['createdAt']) : 0;
                        return $dateA - $dateB; // ASC order for display
                    });
                    
                    foreach ($allQuestionChats as $questionChat) {
                        $questionChats[] = $questionChat;
                        $responseChat = $this->lmModel->getResponseChatByQuestionChat($questionChat['questionChatID']);
                        // Ensure response is a string, not false/null
                        $responseChats[] = $responseChat !== false && $responseChat !== null ? (string)$responseChat : '';
                    }
                }
            }
            
            $user = $this->getUserInfo();

            require_once VIEW_CHATBOT;
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }
    }

    /**
     * Handles chatbot interaction: gets user question, generates response, and saves chat
     */
    public function chat()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();
        $question = isset($_POST['question']) ? trim($_POST['question']) : '';

        if ($fileId === 0) {
            $this->sendJsonError('File ID not provided.');
        }

        if (empty($question)) {
            $this->sendJsonError('Question is required.');
        }

        try {
            $file = $this->lmModel->getFile($userId, $fileId);
            if (!$file) {
                $this->sendJsonError('File not found.');
            }

            $extractedText = $file['extracted_text'] ?? '';
            if (empty($extractedText)) {
                $this->sendJsonError('No extracted text found.');
            }

            // Use RAG (Retrieval-Augmented Generation) for better context
            $context = $extractedText; // Default to full text
            
            try {
                // Generate embedding for the question
                $queryEmbedding = $this->gemini->generateEmbedding($question);
                
                if (!empty($queryEmbedding)) {
                    // Get document chunks
                    $chunks = $this->lmModel->getChunksByFile($fileId);
                    
                    if (!empty($chunks)) {
                        // Find relevant chunks using cosine similarity
                        $similarities = [];
                        $similarityThreshold = 0.15;
                        
                        foreach ($chunks as $chunk) {
                            $chunkEmbedding = json_decode($chunk['embedding'] ?? '[]', true);
                            
                            if (empty($chunkEmbedding) || !is_array($chunkEmbedding)) {
                                continue;
                            }
                            
                            $similarity = $this->cosineSimilarity($queryEmbedding, $chunkEmbedding);
                            
                            if ($similarity >= $similarityThreshold) {
                                $similarities[] = [
                                    'chunkText' => $chunk['chunkText'] ?? '',
                                    'similarity' => $similarity,
                                ];
                            }
                        }
                        
                        // Sort by similarity and get top chunks
                        if (!empty($similarities)) {
                            usort($similarities, function ($a, $b) {
                                return $b['similarity'] <=> $a['similarity'];
                            });
                            
                            $topK = min(10, count($similarities));
                            $topChunks = array_slice($similarities, 0, $topK);
                            
                            // Combine relevant context
                            $context = '';
                            foreach ($topChunks as $chunk) {
                                $context .= $chunk['chunkText'] . "\n\n";
                            }
                        }
                    }
                }
            } catch (\Throwable $ragError) {
                // If RAG fails, fall back to using full extracted text
                error_log('[Chatbot RAG Error] ' . $ragError->getMessage());
                $context = $extractedText;
            }

            // Get or create chatbot first (before generating response to ensure we can save)
            $chatbot = $this->lmModel->getChatBotByFile($fileId);
            if (!$chatbot) {
                $title = $this->gemini->generateTitle($file['name']);
                $chatbotId = $this->lmModel->saveChatbot($fileId, $title);
            } else {
                $chatbotId = $chatbot['chatbotID'];
            }

            // Save user question first
            $questionChatId = $this->lmModel->saveQuestionChat($chatbotId, $question);

            // Generate response using context (either RAG chunks or full text)
            $response = $this->gemini->generateChatbotResponse($context, $question);
            
            // Ensure response is not empty
            if (empty($response)) {
                $response = 'I apologize, but I was unable to generate a response. Please try asking your question again.';
            }

            // Save bot response
            $this->lmModel->saveResponseChat($questionChatId, $response);

            // Return response to display in UI
            $this->sendJsonSuccess(['response' => $response]);
        } catch (\Throwable $e) {
            $this->sendJsonError($e->getMessage());
        }
    }

    // ============================================================================
    // DOCUMENT HUB PAGE (multidoc.php)
    // ============================================================================

    /**
     * Displays document hub interface for multi-document operations
     */
    public function documentHub()
    {
        $this->checkSession();
        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();
        $allFiles = $this->lmModel->getFilesForUser($userId);
        
        // Filter out audio files from document selection
        $audioFileTypes = ['wav', 'mp3', 'ogg', 'm4a', 'aac', 'flac', 'wma'];
        $fileList = array_values(array_filter($allFiles, function($file) use ($audioFileTypes) {
            $fileType = strtolower($file['fileType'] ?? '');
            return !in_array($fileType, $audioFileTypes);
        }));
        
        // Retrieve checked documents from session
        $checkedDocuments = $this->getCheckedDocuments();
        
        $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
        $user = $this->getUserInfo();
        require_once VIEW_DOCUMENT_HUB;
    }

    /**
     * Saves checked documents to session
     */
    public function saveCheckedDocuments()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $data = json_decode(file_get_contents('php://input'), true);
        $checkedFileIds = isset($data['fileIds']) && is_array($data['fileIds']) 
            ? array_map('intval', $data['fileIds']) 
            : [];
        
        // Store in session
        $_SESSION['document_hub_checked_files'] = $checkedFileIds;
        
        $this->sendJsonSuccess(['message' => 'Checked documents saved.']);
    }

    /**
     * Retrieves checked documents from session
     */
    private function getCheckedDocuments(): array
    {
        return isset($_SESSION['document_hub_checked_files']) && is_array($_SESSION['document_hub_checked_files'])
            ? $_SESSION['document_hub_checked_files']
            : [];
    }

    /**
     * Handles chatbot interaction for document hub: processes question across multiple documents using RAG
     */
    public function sendDocumentHubChat()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $data = json_decode(file_get_contents('php://input'), true);
        $userId = (int)$_SESSION['user_id'];
        
        if (!isset($data['question']) || empty(trim($data['question']))) {
            $this->sendJsonError('Question is required.');
        }

        if (!isset($data['fileIds']) || empty($data['fileIds']) || !is_array($data['fileIds'])) {
            $this->sendJsonError('At least one document must be selected.');
        }

        $question = trim($data['question']);
        $selectedFileIds = $data['fileIds'];

        try {
            // Validate file IDs belong to user
            $validFileIds = [];
            foreach($selectedFileIds as $fileId) {
                $file = $this->lmModel->getFile($userId, (int)$fileId);
                if($file) {
                    $validFileIds[] = (int)$fileId;
                }
            }

            if (empty($validFileIds)) {
                $this->sendJsonError('No valid files found.');
            }

            // Generate embedding for the question
            $queryEmbedding = $this->gemini->generateEmbedding($question);

            if (empty($queryEmbedding)) {
                $this->sendJsonError('Could not generate embedding for the question.');
            }

            // Find relevant chunks using RAG
            $similarities = [];
            $similarityThreshold = 0.15; // Lower threshold for more sensitive search
            
            foreach ($validFileIds as $fileId) {
                $chunks = $this->lmModel->getChunksByFile($fileId);
                
                if (empty($chunks)) {
                    // If no chunks exist, fall back to extracted text
                    $file = $this->lmModel->getFile($userId, $fileId);
                    if ($file && !empty($file['extracted_text'])) {
                        $similarities[] = [
                            'fileId' => $fileId,
                            'chunkText' => $file['extracted_text'],
                            'similarity' => 1.0, // Use full text if no chunks
                        ];
                    }
                    continue;
                }

                foreach ($chunks as $chunk) {
                    $chunkEmbedding = json_decode($chunk['embedding'], true);
                    
                    if (empty($chunkEmbedding) || !is_array($chunkEmbedding)) {
                        continue;
                    }

                    $similarity = $this->cosineSimilarity($queryEmbedding, $chunkEmbedding);
                    
                    if ($similarity >= $similarityThreshold) {
                        $similarities[] = [
                            'fileId' => $fileId,
                            'chunkText' => $chunk['chunkText'] ?? '',
                            'similarity' => $similarity,
                        ];
                    }
                }
            }

            // Sort by similarity and get top chunks
            usort($similarities, function ($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });

            $topK = min(15, count($similarities));
            $topChunks = array_slice($similarities, 0, $topK);

            // Combine relevant context
            $context = '';
            if (!empty($topChunks)) {
                foreach ($topChunks as $chunk) {
                    $context .= $chunk['chunkText'] . "\n\n";
                }
            } else {
                // Fallback: combine all extracted text if no relevant chunks found
                foreach ($validFileIds as $fileId) {
            $file = $this->lmModel->getFile($userId, $fileId);
                    if ($file && !empty($file['extracted_text'])) {
                        $context .= "Document: " . ($file['name'] ?? 'Unknown') . "\n";
                        $context .= $file['extracted_text'] . "\n\n";
                    }
                }
            }

            if (empty($context)) {
                $this->sendJsonError('No content found in selected documents.');
            }

            // Generate chatbot response using combined context
            $response = $this->gemini->generateChatbotResponse($context, $question);

            $this->sendJsonSuccess(['response' => $response]);
        } catch (\Throwable $e) {
            error_log('Document Hub Chat Error: ' . $e->getMessage());
            $this->sendJsonError('An error occurred while processing your question: ' . $e->getMessage());
        }
    }

    /**
     * Synthesizes a new document from multiple selected documents using RAG: finds relevant chunks via embeddings and generates document
     */
    public function synthesizeDocument(){
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $this->checkSession(true);
        $userId = (int)$_SESSION['user_id'];

        if (!isset($data['fileIds']) || empty($data['fileIds'])) {
            echo json_encode(['success' => false, 'message' => 'No files selected.']);
            exit();
        }
        if (!isset($data['description']) || empty(trim($data['description']))) {
            echo json_encode(['success' => false, 'message' => 'Document description is required.']);
            exit();
        }

        $selectedFileIds = $data['fileIds'];
        $description = trim($data['description']);
        $documentType = $data['reportType'] ?? 'briefDocument';

        $validFileIds = [];
        foreach($selectedFileIds as $fileId) {
            $file = $this->lmModel->getFile($userId, $fileId);
            if($file) {
                $validFileIds[] = $fileId;
            }
        }

        if (empty($validFileIds)) {
            echo json_encode(['success' => false, 'message' => 'No valid files found.']);
            exit();
        }

        try {
            $queryEmbedding = $this->gemini->generateEmbedding($description);

            if (empty($queryEmbedding)) {
                echo json_encode(['success' => false, 'message' => 'Could not generate embedding for the description.']);
            exit();
        }

            $similarities = [];
            $totalChunksSearched = 0;
            $similarityThreshold = 0.15; // Lower threshold for more sensitive search

            foreach ($validFileIds as $fileId) {
                $chunks = $this->lmModel->getChunksByFile($fileId);

                if (empty($chunks)) {
                continue;
            }

                foreach ($chunks as $chunk) {
                    $chunkEmbedding = json_decode($chunk['embedding'], true);
                    
                    if (empty($chunkEmbedding) || !is_array($chunkEmbedding)) {
                        continue;
                    }

                    $similarity = $this->cosineSimilarity($queryEmbedding, $chunkEmbedding);
                    $totalChunksSearched++;
                    
                    if ($similarity >= $similarityThreshold) {
                        $similarities[] = [
                            'fileId' => $fileId,
                            'chunkText' => $chunk['chunkText'] ?? '',
                            'similarity' => $similarity,
                        ];
                    }
                }
            }

            usort($similarities, function ($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });

            $topK = min(15, count($similarities));
            $topChunks = array_slice($similarities, 0, $topK);

            if (empty($topChunks)) {
                echo json_encode(['success' => false, 'message' => 'No relevant content found in selected documents that matches your query. Please try a different query or select different documents.']);
            exit();
        }

            $context = '';
            foreach ($topChunks as $index => $chunk) {
                $context .= $chunk['chunkText'] . "\n\n";
            }

            if (empty($context)) {
                echo json_encode(['success' => false, 'message' => 'No relevant content found in selected documents.']);
            exit();
        }

            $document = $this->gemini->synthesizeDocument($context, $description);

            if (empty($document)) {
                echo json_encode(['success' => false, 'message' => 'Failed to synthesize document.']);
            exit();
        }

            $formattedDocument = $this->gemini->formatContent($document);

            $documentTypeNames = [
                'studyGuide' => 'Study Guide',
                'briefDocument' => 'Brief Document',
                'keyPoints' => 'Key Points'
            ];

            $documentTypeName = $documentTypeNames[$documentType] ?? 'Document';
            $timestamp = date('Y-m-d_H-i-s');
            $fileName = $documentTypeName . '_' . $timestamp . '.txt';
            $fileContent = $formattedDocument;

            $savedFileId = $this->lmModel->uploadFileToGCS(
                $userId,
                null,
                $formattedDocument,
                $fileContent,
                null,
                $fileName
            );

            if ($savedFileId) {
                try {
                    $chunks = $this->lmModel->splitTextIntoChunks($formattedDocument, $savedFileId);
                    $embeddings = [];
                    foreach ($chunks as $chunk) {
                        $embeddings[] = $this->gemini->generateEmbedding($chunk);
                    }
                    $this->lmModel->saveChunksToDB($chunks, $embeddings, $savedFileId);
                } catch (\Exception $e) {
                    // Failed to generate chunks for saved document
                }
            }

            echo json_encode([
                'success' => true,
                'content' => $document,
                'fileId' => $savedFileId,
                'fileName' => $fileName,
                'chunksUsed' => count($topChunks),
                'totalChunksSearched' => $totalChunksSearched
            ]);

        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'An error occurred while synthesizing the document.']);
        }

        exit();
    }

    /**
     * Calculates cosine similarity between two vectors
     * @param array $vectorA First vector
     * @param array $vectorB Second vector
     * @return float Cosine similarity value between 0 and 1
     */
    private function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        if (count($vectorA) !== count($vectorB)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $normA += $vectorA[$i] * $vectorA[$i];
            $normB += $vectorB[$i] * $vectorB[$i];
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dotProduct / ($normA * $normB);
    }

    /**
     * Knowledge Base Search: Search across all user documents using RAG
     * Returns top matching chunks with file metadata
     */
    public function searchKnowledgeBase()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $data = json_decode(file_get_contents('php://input'), true);
        $userId = (int)$_SESSION['user_id'];
        
        if (!isset($data['query']) || empty(trim($data['query']))) {
            $this->sendJsonError('Search query is required.');
        }

        $query = trim($data['query']);
        
        // Enforce query length limit
        if (strlen($query) > 500) {
            $this->sendJsonError('Search query is too long. Maximum 500 characters.');
        }

        try {
            // Log search query
            error_log("[KnowledgeBase Search] User ID: {$userId}, Query: {$query}");
            
            // Generate embedding for the search query
            $queryEmbedding = $this->gemini->generateEmbedding($query);

            if (empty($queryEmbedding)) {
                error_log("[KnowledgeBase Search] Failed to generate embedding for query: {$query}");
                $this->sendJsonError('Could not generate embedding for the search query.');
            }

            // Get all chunks for user's files
            $allChunks = $this->lmModel->getChunksForUserFiles($userId);

            if (empty($allChunks)) {
                error_log("[KnowledgeBase Search] No chunks found for user ID: {$userId}");
                $this->sendJsonSuccess([
                    'results' => [],
                    'message' => 'No documents found in your knowledge base.'
                ]);
            }

            error_log("[KnowledgeBase Search] Total chunks to search: " . count($allChunks));

            // Calculate similarity for each chunk
            $similarities = [];
            $similarityThreshold = 0.15; // Using same threshold as other RAG searches
            
            foreach ($allChunks as $chunk) {
                $chunkEmbedding = json_decode($chunk['embedding'], true);
                
                if (empty($chunkEmbedding) || !is_array($chunkEmbedding)) {
                    continue;
                }

                $similarity = $this->cosineSimilarity($queryEmbedding, $chunkEmbedding);
                
                if ($similarity >= $similarityThreshold) {
                    $fileID = (int)$chunk['fileID'];
                    $fileName = $chunk['fileName'] ?? 'Unknown';
                    $chunkText = $chunk['chunkText'] ?? '';
                    $chunkID = (int)$chunk['documentChunkID'];
                    $roundedSimilarity = round($similarity, 4);
                    
                    // Log each matching result with confidence level and content preview
                    $contentPreview = strlen($chunkText) > 100 
                        ? substr($chunkText, 0, 100) . '...' 
                        : $chunkText;
                    error_log("[KnowledgeBase Search] Match found - File: {$fileName} (ID: {$fileID}), Chunk ID: {$chunkID}, Confidence: {$roundedSimilarity} (" . ($roundedSimilarity * 100) . "%), Content Preview: " . str_replace(["\n", "\r"], " ", $contentPreview));
                    
                    $similarities[] = [
                        'fileID' => $fileID,
                        'fileName' => $fileName,
                        'chunkText' => $chunkText,
                        'similarity' => $roundedSimilarity,
                        'documentChunkID' => $chunkID
                    ];
                }
            }

            // Sort by similarity (highest first)
            usort($similarities, function ($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });

            // Get top 10 results
            $topK = min(10, count($similarities));
            $results = array_slice($similarities, 0, $topK);

            // Log summary
            error_log("[KnowledgeBase Search] Search completed - Total matches: " . count($similarities) . ", Returning top: " . count($results));
            if (!empty($results)) {
                error_log("[KnowledgeBase Search] Top result - File: {$results[0]['fileName']}, Confidence: {$results[0]['similarity']} (" . ($results[0]['similarity'] * 100) . "%)");
            }

            $this->sendJsonSuccess([
                'results' => $results,
                'totalMatches' => count($similarities),
                'returned' => count($results)
            ]);
        } catch (\Throwable $e) {
            error_log('Knowledge Base Search Error: ' . $e->getMessage());
            $this->sendJsonError('An error occurred while searching: ' . $e->getMessage());
        }
    }

    // ============================================================================
    // HOMEWORK HELPER PAGE (homeworkHelper.php)
    // ============================================================================

    /**
     * Displays the homework helper interface
     */
    public function homeworkHelper()
    {
        $this->checkSession();
        
        try {
            $userId = (int)$_SESSION['user_id'];
            $user = $this->getUserInfo();
            $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
            
            // Get all homework entries for the user
            $homeworkEntries = $this->lmModel->getHomeworkHelpersByUser($userId);
            
            require_once VIEW_HOMEWORK_HELPER;
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . DISPLAY_LEARNING_MATERIALS);
            exit();
        }
    }

    /**
     * Processes homework upload: extracts text, identifies question, gets answer from Gemini
     */
    public function processHomework()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['homework_file'])) {
            $this->sendJsonError('No file uploaded.');
        }

        $file = $_FILES['homework_file'];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->sendJsonError('File upload error: ' . $file['error']);
        }

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            $this->sendJsonError('Invalid file type. Please upload a PDF or image file.');
        }

        try {
            // Extract text from file
            $tmpName = $file['tmp_name'];
            $extractedText = $this->extractTextFromFile($tmpName, $fileExtension, $file);
            
            if (empty($extractedText)) {
                $this->sendJsonError('Could not extract text from the file. Please ensure the file contains readable text or questions.');
            }

            // Upload file to GCS
            $fileContent = file_get_contents($tmpName);
            if ($fileContent === false) {
                throw new \Exception("Error reading file: {$file['name']}");
            }

            // Generate unique filename
            $uniqueFileName = uniqid('homework_', true) . '.' . $fileExtension;
            $gcsObjectName = 'user_upload/' . $userId . '/homework/' . $uniqueFileName;

            // Upload to GCS
            $bucket = $this->lmModel->getStorage()->bucket($this->lmModel->getBucketName());
            $contentType = $fileExtension === 'pdf' ? 'application/pdf' : 'image/' . ($fileExtension === 'jpg' ? 'jpeg' : $fileExtension);
            
            $bucket->upload($fileContent, [
                'name' => $gcsObjectName,
                'metadata' => ['contentType' => $contentType]
            ]);

            // Save to database with pending status
            $homeworkId = $this->lmModel->saveHomeworkHelper(
                $userId,
                $file['name'],
                $fileExtension,
                $gcsObjectName,
                $extractedText,
                null,
                null,
                'processing'
            );

            // Process with Gemini
            try {
                $result = $this->gemini->answerHomeworkQuestion($extractedText);
                
                $status = $result['hasQuestion'] ? 'completed' : 'no_question';
                $question = $result['question'] ?? null;
                $answer = $result['answer'];

                // Update database with results
                $this->lmModel->updateHomeworkHelper($homeworkId, null, $question, $answer, $status);

                $this->sendJsonSuccess([
                    'homeworkId' => $homeworkId,
                    'hasQuestion' => $result['hasQuestion'],
                    'question' => $question,
                    'answer' => $answer,
                    'status' => $status,
                    'fileName' => $file['name']
                ]);
            } catch (\Exception $geminiError) {
                // Update status to indicate error
                $this->lmModel->updateHomeworkHelper($homeworkId, null, null, null, 'pending');
                error_log('Homework Helper Gemini Error: ' . $geminiError->getMessage());
                $this->sendJsonError('Failed to process homework question: ' . $geminiError->getMessage());
            }
        } catch (\Exception $e) {
            error_log('Homework Helper Processing Error: ' . $e->getMessage());
            $this->sendJsonError('An error occurred while processing the homework: ' . $e->getMessage());
        }
    }
}