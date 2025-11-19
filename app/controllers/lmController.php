<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LmModel;
use App\Services\GeminiService;
use App\Services\OCRService;
use PDO;
use App\Services\PiperService;
use App\Config\Database;

class LmController
{

    private $lmModel;
    private $gemini;
    private $userModel;
    private $PiperService;
    private $ocrService;


    private const SESSION_CURRENT_FILE_ID = 'current_file_id';

    public function __construct()
    {
        $this->lmModel = new LmModel();
        $this->gemini = new GeminiService();
        $this->userModel = new UserModel();
        $this->PiperService = new PiperService();
        $this->ocrService = new OCRService();
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

            $this->_generatePdf($summary['title'], $summary['content']);
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

            $this->_generateDocx($summary['title'], $summary['content']);
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

            $this->_generateTxt($summary['title'], $summary['content']);
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

            $this->_generatePdf($note['title'], $note['content']);
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

            $this->_generateDocx($note['title'], $note['content']);
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

            $this->_generateTxt($note['title'], $note['content']);
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            header('Location: ' . NOTE);
            exit();
        }
    }

    /**
     * Generates PDF file from title and content using DomPDF or fallback methods
     */
    private function _generatePdf($title, $content)
    {
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
                $this->_generatePdfWithPhpWord($title, $content);
            }
        } else {
            $this->_generatePdfWithPhpWord($title, $content);
        }
    }

    /**
     * Generates PDF using PHPWord with DomPDF renderer as fallback
     */
    private function _generatePdfWithPhpWord($title, $content)
    {
        $dompdfPath = __DIR__ . '/../../vendor/dompdf/dompdf';
        if (file_exists($dompdfPath)) {
            try {
                \PhpOffice\PhpWord\Settings::setPdfRendererName(\PhpOffice\PhpWord\Settings::PDF_RENDERER_DOMPDF);
                \PhpOffice\PhpWord\Settings::setPdfRendererPath($dompdfPath);

                $phpWord = new \PhpOffice\PhpWord\PhpWord();
                $section = $phpWord->addSection();

                $section->addTitle($title, 1);
                $section->addTextBreak(1);

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
                $this->_generateSimplePdf($title, $content);
            }
        } else {
            $this->_generateSimplePdf($title, $content);
        }
    }

    /**
     * Converts markdown-like content to HTML for PDF generation
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
     * Generates simple HTML-based PDF fallback with print dialog instructions
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
        $html .= '<div class="info-box"><strong>Note:</strong> Your document is ready. Please use your browser\\ print function (Ctrl+P or Cmd+P) and select "Save as PDF" to download.</div>';
        $html .= $this->_convertContentToHtml($title, $content);
        $html .= '</body></html>';

        echo $html;
        exit();
    }

    /**
     * Generates DOCX file from title and content using PHPWord
     */
    private function _generateDocx($title, $content)
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();

        $section->addTitle($title, 1);
        $section->addTextBreak(1);

        $paragraphs = preg_split('/\n\s*\n/', $content);
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (!empty($paragraph)) {
                $section->addText($paragraph);
                $section->addTextBreak(1);
            }
        }

        $writer = new \PhpOffice\PhpWord\Writer\Word2007($phpWord);
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) . '.docx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit();
    }

    /**
     * Generates TXT file from title and content
     */
    private function _generateTxt($title, $content)
    {
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) . '.txt';
        $textContent = $title . "\n\n" . $content;

        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $textContent;
        exit();
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
                $savedCount = 0;
                foreach ($flashcards as $card) {
                    if (!isset($card['term']) || !isset($card['definition'])) {
                        continue; // Skip invalid cards
                    }
                    $this->lmModel->saveFlashcards($fileId, $title, $card['term'], $card['definition']);
                    $savedCount++;
                }
                
                // Log final count
                error_log("[Flashcard Generation] Successfully saved {$savedCount} flashcards for file ID: {$fileId}");

                $conn->commit();
                
                // Return format expected by frontend: preview.cards
                $this->sendJsonSuccess([
                    'preview' => [
                        'cards' => $flashcards
                    ],
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

            // Get all flashcards with the same title
            $title = $flashcard['title'];
            $flashcardFileId = $flashcard['fileID'];
            
            $allFlashcards = $this->lmModel->getFlashcardsByTitle($title, $flashcardFileId);
            
            if (empty($allFlashcards)) {
                $this->sendJsonError('No flashcards found.');
            }

            // Format for the view: return as cards array
            $cards = array_map(function($card) {
                return [
                    'term' => $card['term'] ?? '',
                    'definition' => $card['definition'] ?? ''
                ];
            }, $allFlashcards);

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
                foreach ($terms as $index => $term) {
                    $term = trim($term);
                    $definition = isset($definitions[$index]) ? trim($definitions[$index]) : '';
                    
                    if (empty($term) || empty($definition)) {
                        continue; // Skip empty pairs
                    }

                    $this->lmModel->saveFlashcards($fileId, $title, $term, $definition);
                }

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

                // Create new flashcards with the updated title and terms/definitions
                foreach ($terms as $index => $term) {
                    $term = trim($term);
                    $definition = isset($definitions[$index]) ? trim($definitions[$index]) : '';
                    
                    if (empty($term) || empty($definition)) {
                        continue; // Skip empty pairs
                    }

                    $this->lmModel->saveFlashcards($fileId, $title, $term, $definition);
                }

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
        $questionDifficulty = isset($_POST['questionDifficulty']) ? trim($_POST['questionDifficulty']) : 'medium';
        
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

            $quizDataJson = $this->gemini->generateMixedQuiz($extractedText, $distribution, $totalQuestions, $questionDifficulty);
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
            $quizId = $this->lmModel->saveQuiz($fileId, $totalQuestions, $title, $quizData, $examMode);

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
            foreach ($questions as &$question) {
                $options = $this->lmModel->getOptionsByQuestion($question['questionID']);
                // Transform options from objects to array of strings for frontend
                $question['options'] = array_map(function($option) {
                    return $option['text'] ?? '';
                }, $options);
                // Also include answer field for evaluation
                $question['answer'] = $this->getCorrectAnswerFromOptions($options);
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
            foreach ($questions as &$question) {
                $options = $this->lmModel->getOptionsByQuestion($question['questionID']);
                // Transform options from objects to array of strings for frontend
                $question['options'] = array_map(function($option) {
                    return $option['text'] ?? '';
                }, $options);
                // Also include answer field for evaluation
                $question['answer'] = $this->getCorrectAnswerFromOptions($options);
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
                
                // Build structured feedback array indexed by question position
                $structuredFeedback = [];
                foreach ($questions as $index => $question) {
                    $questionId = $question['questionID'];
                    $userAnswer = $answersByPosition[$index] ?? null;
                    
                    // Try to extract feedback for this question
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
                    
                    // Determine if answer was correct based on feedback text
                    $isCorrect = !empty($feedbackText) && strpos($feedbackText, 'Correct') !== false;
                    
                    $structuredFeedback[$index] = [
                        'isCorrect' => $isCorrect,
                        'userAnswer' => $userAnswer,
                        'suggestion' => $feedbackText
                    ];
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
            foreach ($questions as &$question) {
                $options = $this->lmModel->getOptionsByQuestion($question['questionID']);
                // Transform options from objects to array of strings for frontend
                $question['options'] = array_map(function($option) {
                    return $option['text'] ?? '';
                }, $options);
                // Also include answer field for evaluation
                $question['answer'] = $this->getCorrectAnswerFromOptions($options);
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

            // Save answers indexed by questionID (not position) for proper retrieval later
            // This ensures answers can be matched to questions even if question order changes
            $this->lmModel->saveQuizAttempt($quizId, $userId, $answersByQuestionId, $feedbackArray, $suggestionsArray, $score, $quiz['examMode']);
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
            $chatHistory = $this->lmModel->chatHistory($fileId);
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

            $response = $this->gemini->generateChatbotResponse($extractedText, $question);

            $chatbot = $this->lmModel->getChatBotByFile($fileId);
            if (!$chatbot) {
                $title = $this->gemini->generateTitle($file['name']);
                $chatbotId = $this->lmModel->saveChatbot($fileId, $title);
            } else {
                $chatbotId = $chatbot['chatbotID'];
            }

            $questionChatId = $this->lmModel->saveQuestionChat($chatbotId, $question);
            $this->lmModel->saveResponseChat($questionChatId, $response);

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
            $similarityThreshold = 0.25;
            
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
}