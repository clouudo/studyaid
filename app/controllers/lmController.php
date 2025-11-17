<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LmModel;
use App\Services\GeminiService;
use App\Services\OCRService;
use PDO;
use App\Services\PiperService;

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
    private function extractTextFromFile(string $tmpName, string $fileExtension): string
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
     * Normalize file array from $_FILES structure
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
     * Process a single file upload
     */
    private function processFileUpload(array $file, int $userId, ?int $folderId): array
    {
        $uploadedFileName = $file['name'];
        $fileExtension = pathinfo($uploadedFileName, PATHINFO_EXTENSION);
        $tmpName = $file['tmp_name'];
        $isImage = $this->isImageFile($fileExtension);

        // Extract text using OCR for images, or standard extraction for other files
        $extractedText = $this->extractTextFromFile($tmpName, $fileExtension);
        
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
     * Handle upload response and redirect
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
                    $this->processFileUpload($file, $userId, $folderId);
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
     * VIEW: Display all documents and folders
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
            $localAudioPath = $this->PiperService->synthesizeText($cleanText);
            if (!$localAudioPath || !file_exists($localAudioPath)) {
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
            $localAudioPath = $this->PiperService->synthesizeText($cleanText);
            if (!$localAudioPath || !file_exists($localAudioPath)) {
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
     * ACTION (JSON API): Update an existing note inline
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
     * ACTION (JSON API): Generate a mindmap using AI
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
     * ACTION (JSON API): Load the structured mindmap payload (alias of viewMindmap)
     */
    public function loadMindmapStructure()
    {
        $this->viewMindmap();
    }

    /**
     * ACTION (JSON API): Persist updated mindmap structure
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

    /**
     * Extract markdown from stored mindmap payload
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
     * Build standardized mindmap response payload
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
     * Generate mindmap title from file name and content
     */
    private function generateMindmapTitle(string $fileName, string $extractedText): string
    {
        $summary = $this->gemini->generateSummary($extractedText, "A very short summary of the content");
        return $this->gemini->generateTitle($fileName . $summary);
    }

    /**
     * Save mindmap data to database
     */
    private function saveMindmapData(int $fileId, string $title, string $markdown): int
    {
        $payload = ['markdown' => $markdown];
        $dataJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
        return $this->lmModel->saveMindmap($fileId, $title, $dataJson);
    }

    /**
     * Get JSON request data from POST or JSON body
     */
    private function getJsonRequestData(): array
    {
        $rawInput = file_get_contents('php://input');
        $requestData = json_decode($rawInput, true);
        return is_array($requestData) ? $requestData : $_POST;
    }

    /**
     * Send JSON success response
     */
    private function sendJsonSuccess(array $data = []): void
    {
        echo json_encode(['success' => true] + $data);
        exit();
    }

    /**
     * Send JSON error response
     */
    private function sendJsonError(string $message): void
    {
        echo json_encode(['success' => false, 'message' => $message]);
        exit();
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
        $text = preg_replace('/\[([^\]]+)\]\([^\)]+\)/', '$1', $text);
        
        // Remove images ![alt](url)
        $text = preg_replace('/!\[([^\]]*)\]\([^\)]+\)/', '', $text);
        
        // Remove code blocks ```
        $text = preg_replace('/```[\s\S]*?```/', '', $text);
        
        // Remove inline code `code`
        $text = preg_replace('/`([^`]+)`/', '$1', $text);
        
        // Remove strikethrough ~~text~~
        $text = preg_replace('/~~(.+?)~~/', '$1', $text);
        
        // Remove list markers (-, *, +, 1., 2., etc.)
        $text = preg_replace('/^[\s]*[-*+]\s+/m', '', $text);
        $text = preg_replace('/^[\s]*\d+\.\s+/m', '', $text);
        
        // Remove blockquotes >
        $text = preg_replace('/^>\s+/m', '', $text);
        
        // Remove horizontal rules --- or ***
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
     * Helper: Generate PDF using PHPWord (requires PDF renderer)
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

        $paragraphs = preg_split('/\n\s*\n/', $content);
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph); 
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
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title) . '.txt';

        $text = $title . "\n";
        $text .= str_repeat('=', strlen($title)) . "\n\n";
        $text .= $content . "\n";

        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

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

            $sourceText = $file['extracted_text'] ?? '';
            if (empty($sourceText)) {
                echo json_encode(['success' => false, 'message' => 'No document content available.']);
                exit();
            }

            $chatHistory = $this->lmModel->chatHistory($fileId);
            $userQuestions = $chatHistory['questions'];
            $aiResponse = $chatHistory['responseChats'];
            $compressedChatHistory = $this->gemini->compressChatHistory($userQuestions, $aiResponse);

            $questionChatId = $this->lmModel->saveQuestionChat($chatbot['chatbotID'], $question);
            $response = $this->gemini->generateChatbotResponse($sourceText, $question, $compressedChatHistory);
            $responseChatId = $this->lmModel->saveResponseChat($questionChatId, $response);
            $response = $this->lmModel->getResponseChatById($responseChatId);

            echo json_encode(['success' => true, 'response' => $response['response']]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    /**
     * ACTION (JSON API): Send a question to document hub chatbot with RAG
     */
    public function sendDocumentHubChat()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $userId = (int)$_SESSION['user_id'];
        $data = json_decode(file_get_contents('php://input'), true);
        $question = isset($data['question']) ? trim($data['question']) : '';
        $fileIds = isset($data['fileIds']) && is_array($data['fileIds']) ? $data['fileIds'] : [];

        if (empty($fileIds)) {
            echo json_encode(['success' => false, 'message' => 'No documents selected.']);
            exit();
        }

        if (empty($question)) {
            echo json_encode(['success' => false, 'message' => 'Question cannot be empty.']);
            exit();
        }

        try {
            $validFileIds = [];
            foreach ($fileIds as $fileId) {
                $file = $this->lmModel->getFile($userId, $fileId);
                if ($file) {
                    $validFileIds[] = $fileId;
                }
            }

            if (empty($validFileIds)) {
                echo json_encode(['success' => false, 'message' => 'No valid documents found.']);
                exit();
            }

            $questionEmbedding = $this->gemini->generateEmbedding($question);

            if (empty($questionEmbedding)) {
                echo json_encode(['success' => false, 'message' => 'Could not generate embedding for the question.']);
                exit();
            }

            $similarities = [];
            $similarityThreshold = 0.3;
            
            foreach ($validFileIds as $fileId) {
                $chunks = $this->lmModel->getChunksByFile($fileId);
                
                foreach ($chunks as $index => $chunk) {
                    $chunkEmbedding = json_decode($chunk['embedding'], true);
                    
                    if (empty($chunkEmbedding) || !is_array($chunkEmbedding)) {
                        continue;
                    }

                    if (count($chunkEmbedding) !== count($questionEmbedding)) {
                        continue;
                    }

                    $similarity = $this->cosineSimilarity($questionEmbedding, $chunkEmbedding);
                    
                    if ($similarity >= $similarityThreshold) {
                        $similarities[] = [
                            'fileId' => $fileId,
                            'chunk' => $chunk['chunkText'] ?? '',
                            'similarity' => $similarity,
                        ];
                    }
                }
            }

            usort($similarities, function ($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });

            $topChunks = array_slice($similarities, 0, 5);

            if (empty($topChunks)) {
                echo json_encode(['success' => false, 'message' => 'No relevant content found for your question across the selected documents. Please try rephrasing or selecting different documents.']);
                exit();
            }

            $context = '';
            foreach ($topChunks as $chunk) {
                $context .= $chunk['chunk'] . "\n\n";
            }

            $response = $this->gemini->generateChatbotResponse($context, $question, null);

            if (empty($response)) {
                echo json_encode(['success' => false, 'message' => 'Failed to generate response.']);
                exit();
            }

            echo json_encode(['success' => true, 'response' => $response]);
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

    private function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0.0;
        $aMagnitude = 0.0;
        $bMagnitude = 0.0;

        $count = count($a);
        if ($count !== count($b)) {
            return 0.0; // Vectors must be the same size
        }

        for ($i = 0; $i < $count; $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $aMagnitude += $a[$i] * $a[$i];
            $bMagnitude += $b[$i] * $b[$i];
        }

        if ($aMagnitude == 0 || $bMagnitude == 0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($aMagnitude) * sqrt($bMagnitude));
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
        $questionType = 'mcq';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['questionAmount'])) {
            $questionAmount = trim($_POST['questionAmount']);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['questionDifficulty'])) {
            $questionDifficulty = trim($_POST['questionDifficulty']);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instructions'])) {
            $instructions = trim($_POST['instructions']);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['questionType'])) {
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
            
            $geminiConfig = require __DIR__ . '/../config/gemini.php';
            $maxRetries = $geminiConfig['rate_limiting']['max_retries'] ?? 3;
            $retryDelay = $geminiConfig['rate_limiting']['retry_delay'] ?? 2;
            $delayBetweenCalls = $geminiConfig['rate_limiting']['delay_between_calls'] ?? 0.5;
            
            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    if ($questionType == 'mcq') {
                        $quizData = $this->gemini->generateMCQ($sourceText, $context, $questionAmount, $questionDifficulty);
                    } elseif ($questionType == 'shortQuestion') {
                        $quizData = $this->gemini->generateShortQuestion($sourceText, $context, $questionAmount, $questionDifficulty);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Invalid question type.']);
                        exit();
                    }
                    break;
                } catch (\RuntimeException $e) {
                    $errorMessage = $e->getMessage();
                    if ((strpos($errorMessage, 'overloaded') !== false || strpos($errorMessage, 'rate limit') !== false) && $attempt < $maxRetries) {
                        sleep($retryDelay * $attempt);
                        continue;
                    }
                    if (strpos($errorMessage, 'overloaded') !== false) {
                        $errorMessage = 'The AI service is currently busy. Please wait a moment and try again.';
                    }
                    echo json_encode(['success' => false, 'message' => $errorMessage]);
                    exit();
                }
            }

            if (empty($quizData)) {
                echo json_encode(['success' => false, 'message' => 'Failed to generate quiz data. The AI service returned an empty response.']);
                exit();
            }

            $decodedQuiz = json_decode($quizData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(['success' => false, 'message' => 'Invalid JSON response from AI: ' . json_last_error_msg()]);
                exit();
            }

            if (!isset($decodedQuiz['quiz']) || !is_array($decodedQuiz['quiz']) || empty($decodedQuiz['quiz'])) {
                echo json_encode(['success' => false, 'message' => 'Quiz data structure is invalid.']);
                exit();
            }

            $totalQuestions = count($decodedQuiz['quiz']);

            try {
                usleep((int)($delayBetweenCalls * 1000000));
                $title = $this->gemini->generateTitle($file['name'] . ' Quiz');
            } catch (\RuntimeException $e) {
                $title = $file['name'] . ' - Quiz';
            }
            
            $quizId = $this->lmModel->saveQuiz($fileId, $totalQuestions, $title);
            if (!$quizId || $quizId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Failed to save quiz to database.']);
                exit();
            }

            $encodedQuiz = json_encode($decodedQuiz['quiz']);
            $questionId = null;
            if ($questionType == 'mcq') {
                $questionId = $this->lmModel->saveQuestion($quizId, 'MCQ', $encodedQuiz);
            } elseif ($questionType == 'shortQuestion') {
                $questionId = $this->lmModel->saveQuestion($quizId, 'Short Question', $encodedQuiz);
            }

            if ($questionId === null || !$questionId || $questionId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Failed to save questions to database.']);
                exit();
            }

            if ($questionType == 'mcq') {
                echo json_encode(['success' => true, 'mcq' => $decodedQuiz['quiz'], 'quizId' => $quizId]);
            } elseif ($questionType == 'shortQuestion') {
                echo json_encode(['success' => true, 'shortQuestion' => $decodedQuiz['quiz'], 'quizId' => $quizId]);
            } else {
                echo json_encode(['success' => true, 'quiz' => $decodedQuiz['quiz'], 'quizId' => $quizId]);
            }
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    public function submitQuiz()
    {
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
            $flashcardsData = $this->gemini->generateFlashcards($sourceText, $context, $flashcardAmount, $flashcardType);
            $decodedFlashcards = json_decode($flashcardsData, true);

            $title = $this->gemini->generateTitle($file['name'] . " Flashcards");

            foreach ($decodedFlashcards['flashcards'] as $flashcard) {
                $this->lmModel->saveFlashcards($fileId, $title, $flashcard['term'], $flashcard['definition']);
            }

            echo json_encode(['success' => true, 'flashcards' => $decodedFlashcards['flashcards']]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    public function viewFlashcardSet()
    {
        header('Content-Type: application/json');
        $this->checkSession(true);

        $fileId = $this->resolveFileId();
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';

        if (empty($title)) {
            echo json_encode(['success' => false, 'message' => 'Title not provided.']);
            exit();
        }

        try {
            $flashcards = $this->lmModel->getFlashcardsByTitle($title, $fileId);
            echo json_encode(['success' => true, 'flashcards' => $flashcards]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    public function deleteFlashcardSet()
    {
        $this->checkSession();

        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $fileId = $this->resolveFileId();

        if (empty($title)) {
            $_SESSION['error'] = "Title not provided.";
            header('Location: ' . FLASHCARD);
            exit();
        }

        try {
            $this->lmModel->deleteFlashcardsByTitle($title, $fileId);
            $_SESSION['message'] = "Flashcard set deleted successfully.";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }

        header('Location: ' . FLASHCARD);
        exit();
    }

    // ============================================================================
    // DOCUMENT HUB PAGE (multidoc.php)
    // ============================================================================

    public function documentHub()
    {
        $this->checkSession();

        $userId = (int)$_SESSION['user_id'];
        $fileId = $this->resolveFileId();
        $allFiles = $this->lmModel->getFilesForUser($userId);
        
        // Filter out audio files from document selection
        $audioFileTypes = ['wav', 'mp3', 'ogg', 'm4a', 'aac', 'flac', 'wma'];
        $fileList = array_filter($allFiles, function($file) use ($audioFileTypes) {
            $fileType = strtolower($file['fileType'] ?? '');
            return !in_array($fileType, $audioFileTypes);
        });
        
        $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);

        $user = $this->getUserInfo();

        require_once VIEW_DOCUMENT_HUB;
    }

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

}
