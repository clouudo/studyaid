<?php

namespace App\Models;

use App\Config\Database;
use Error;
use Google\Cloud\Storage\StorageClient;
use Ramsey\Uuid\Uuid;
use App\Services\OCRService;

class LmModel
{

    private $db;
    private $storage;
    private $bucketName;
    private $ocrService;

    public function __construct()
    {
        $this->db = new Database();
        $config = require __DIR__ . '/../config/cloud_storage.php';

        $this->storage = new StorageClient([
            'keyFilePath' => $config['key_file_path']
        ]);
        $this->bucketName = $config['bucket_name'];
        $this->ocrService = new OCRService();
        // Set custom temp directory for php://temp to avoid permission issues
        $this->setCustomTempDir();
    }

    /**
     * Set custom temp directory for PHP to avoid permission issues with system temp
     */
    private function setCustomTempDir(): void
    {
        $customTempDir = __DIR__ . '/../../temp';
        if (!is_dir($customTempDir)) {
            @mkdir($customTempDir, 0777, true);
        }
        if (is_dir($customTempDir) && is_writable($customTempDir)) {
            putenv('TMPDIR=' . $customTempDir);
            // Also set it in PHP's ini if possible
            if (function_exists('ini_set')) {
                @ini_set('sys_temp_dir', $customTempDir);
            }
        }
    }

    /**
     * Ensure quiz schema has required columns/tables.
     */
    private function ensureQuizSchema(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        try {
            $conn = $this->db->connect();
            $columnSql = [
                'examMode' => "ALTER TABLE quiz ADD COLUMN examMode TINYINT(1) NOT NULL DEFAULT 0 AFTER totalQuestions",
                'status' => "ALTER TABLE quiz ADD COLUMN status ENUM('pending','completed') NOT NULL DEFAULT 'pending' AFTER examMode",
                'questionConfig' => "ALTER TABLE quiz ADD COLUMN questionConfig LONGTEXT NULL AFTER title"
            ];

            foreach ($columnSql as $column => $sql) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'quiz' AND COLUMN_NAME = :column");
                $stmt->bindParam(':column', $column);
                $stmt->execute();
                if ((int)$stmt->fetchColumn() === 0) {
                    $conn->exec($sql);
                }
            }

            // Ensure markAt allows NULL
            $conn->exec("ALTER TABLE quiz MODIFY markAt DATETIME NULL DEFAULT NULL");

            // Ensure question table has explanation column
            $stmt = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'question' AND COLUMN_NAME = 'explanation'");
            $stmt->execute();
            if ((int)$stmt->fetchColumn() === 0) {
                $conn->exec("ALTER TABLE question ADD COLUMN explanation TEXT NULL AFTER question");
            }

            // Ensure quiz_attempt table exists
            $conn->exec(" 
                CREATE TABLE IF NOT EXISTS quiz_attempt (
                    attemptID INT(11) NOT NULL AUTO_INCREMENT,
                    quizID INT(11) NOT NULL,
                    userID INT(11) NOT NULL,
                    answers LONGTEXT NOT NULL,
                    feedback LONGTEXT NULL,
                    suggestions LONGTEXT NULL,
                    score DECIMAL(5,2) NULL,
                    examMode TINYINT(1) NOT NULL DEFAULT 0,
                    createdAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (attemptID),
                    KEY quiz_attempt_quiz_idx (quizID),
                    KEY quiz_attempt_user_idx (userID)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            // Ensure question table exists
            $conn->exec(" 
                CREATE TABLE IF NOT EXISTS question (
                    questionID INT(11) NOT NULL AUTO_INCREMENT,
                    quizID INT(11) NOT NULL,
                    type VARCHAR(50) NOT NULL,
                    question TEXT NOT NULL,
                    PRIMARY KEY (questionID),
                    KEY question_quiz_idx (quizID)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            // Ensure option table exists
            $conn->exec(" 
                CREATE TABLE IF NOT EXISTS option (
                    optionID INT(11) NOT NULL AUTO_INCREMENT,
                    questionID INT(11) NOT NULL,
                    text TEXT NOT NULL,
                    isCorrect TINYINT(1) NOT NULL DEFAULT 0,
                    PRIMARY KEY (optionID),
                    KEY option_question_idx (questionID)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } catch (\Throwable $e) {
            error_log('Quiz schema ensure failed: ' . $e->getMessage());
        }

        $checked = true;
    }

    // ============================================================================ 
    // UTILITY/HELPER METHODS
    // ============================================================================ 

    /**
     * Extract text content from uploaded file based on file extension
     */
    public function extractTextFromFile($tmpName, $fileExtension)
    {
        $extractedText = '';
        switch (strtolower($fileExtension)) {
            case 'txt':
                $extractedText = file_get_contents($tmpName);
                break;
            case 'pdf':
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($tmpName);
                $extractedText = $pdf->getText();
                break;
            case 'doc':
            case 'docx':
                $phpWord = \PhpOffice\PhpWord\IOFactory::load($tmpName);
                $htmlWriter = new \PhpOffice\PhpWord\Writer\HTML($phpWord);
                $html = $htmlWriter->getContent();

                // Remove style block
                $html = preg_replace('/<style.*?>(.*?)<\/style>/is', '', $html);

                // Convert HTML to plain text, preserving line breaks
                $html = str_replace('</p>', "</p>\n", $html);
                $html = str_replace('<br />', "\n", $html);
                $extractedText = strip_tags($html);
                break;
        }

        // Replace 2 or more newlines with a single newline and trim whitespace
        $extractedText = preg_replace('/\n{2,}/', "\n", trim($extractedText));

        return $extractedText;
    }

    /**
     * Generate a unique filename using UUID
     */
    public function generateUniqueFileName($fileExtension)
    {
        $uuid = Uuid::uuid4()->toString();
        $uniqueFileName = $uuid . '.' . $fileExtension;
        return $uniqueFileName;
    }

    /**
     * Get logical folder path for GCS storage
     * Uses stored folderPath from database if available, otherwise builds and stores it
     */
    public function getLogicalFolderPath($folderId, $userId)
    {
        $conn = $this->db->connect();
        
        // First, try to get folderPath from database
        $query = "SELECT folderPath, parentFolderId FROM folder WHERE folderID = :folderID AND userID = :userID";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':folderID', $folderId);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        $folder = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$folder) {
            throw new \Exception("Folder hierarchy broken or access denied.");
        }

        // If folderPath exists and is not null, return it
        if (!empty($folder['folderPath'])) {
            return $folder['folderPath'];
        }

        // Otherwise, build the path by traversing parent folders
        $path = [];
        $currentFolderId = $folderId;

        while ($currentFolderId !== null) {
            $query = "SELECT folderID, name, parentFolderId FROM folder WHERE folderID = :folderID AND userID = :userID";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':folderID', $currentFolderId);
            $stmt->bindParam(':userID', $userId);
            $stmt->execute();
            $currentFolder = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($currentFolder) {
                array_unshift($path, $currentFolder['name']);
                $currentFolderId = $currentFolder['parentFolderId'];
            } else {
                throw new \Exception("Folder hierarchy broken or access denied.");
            }
        }
        
        $builtPath = implode('/', $path) . '/';
        
        // Store the built path in database for future use
        $updateQuery = "UPDATE folder SET folderPath = :folderPath WHERE folderID = :folderID AND userID = :userID";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':folderPath', $builtPath);
        $updateStmt->bindParam(':folderID', $folderId);
        $updateStmt->bindParam(':userID', $userId);
        $updateStmt->execute();
        
        return $builtPath;
    }

    /**
     * Get folder information by ID
     */
    public function getFolderInfo($folderId)
    {
        $conn = $this->db->connect();
        $query = "SELECT folderID, name, parentFolderId, folderPath FROM folder WHERE folderID = :folderID";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':folderID', $folderId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all folders for a user (for dropdowns/modals)
     */
    public function getAllFoldersForUser($userId)
    {
        $conn = $this->db->connect();
        $query = "SELECT folderID, name, parentFolderId FROM folder WHERE userID = :userID ORDER BY name ASC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get file information by user ID and file ID
     */
    public function getFile($userId, $fileId)
    {
        $conn = $this->db->connect();
        $query = "SELECT * FROM file WHERE userID = :userID AND fileID = :fileID";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userID', $userId);
        $stmt->bindParam(':fileID', $fileId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    // ============================================================================ 
    // NEW DOCUMENT PAGE (newDocument.php)
    // ============================================================================ 

    /**
     * Upload audio file to Google Cloud Storage and save metadata to audio table for summary
     */
    public function uploadAudioFileToGCSForSummary(int $summaryId, int $sourceFileId, string $localAudioPath): string
    {
        if(empty($localAudioPath) || !file_exists($localAudioPath)){
            throw new \Exception("Audio file not found locally.");
        }

        $audioExtension = pathinfo($localAudioPath, PATHINFO_EXTENSION) ?: 'wav';
        $uniqueFileName = 'summary_' . $summaryId . '_' . uniqid('', true) . '.' . $audioExtension;
        
        // Get file info to determine folder structure
        $conn = $this->db->connect();
        $fileStmt = $conn->prepare("SELECT userID, folderID FROM file WHERE fileID = :fileID");
        $fileStmt->bindParam(':fileID', $sourceFileId, \PDO::PARAM_INT);
        $fileStmt->execute();
        $fileInfo = $fileStmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$fileInfo) {
            throw new \Exception("Source file not found.");
        }
        
        $userId = (int)$fileInfo['userID'];
        $folderId = $fileInfo['folderID'];
        
        $logicalFolderPath = '';
        if ($folderId !== null) {
            $logicalFolderPath = $this->getLogicalFolderPath($folderId, $userId);
        }

        $gcsObjectName = 'user_upload/' . $userId . '/content/' . $logicalFolderPath . $uniqueFileName;

        $bucket = $this->storage->bucket($this->bucketName);
        
        // Use file stream instead of file_get_contents to avoid temp file permission issues
        $audioStream = fopen($localAudioPath, 'rb');
        if (!$audioStream) {
            throw new \Exception("Failed to open audio file for reading.");
        }
        
        $options = [
            'name' => $gcsObjectName,
            'metadata' => ['contentType' => 'audio/' . ($audioExtension === 'wav' ? 'wav' : 'x-wav')]
        ];
        
        $bucket->upload($audioStream, $options);
        // Note: GCS upload() automatically closes the stream, so no need to fclose()

        // Clean up local file
        @unlink($localAudioPath);
        
        // Save to audio table using only summaryID (audio is based on summary content, not file content)
        $stmt = $conn->prepare("INSERT INTO audio (summaryID, audioPath) VALUES (:summaryID, :audioPath) ON DUPLICATE KEY UPDATE audioPath = :audioPath");
        $stmt->bindParam(':summaryID', $summaryId, \PDO::PARAM_INT);
        $stmt->bindParam(':audioPath', $gcsObjectName);
        $stmt->execute();
        
        return $gcsObjectName;
    }

    /**
     * Upload audio file to Google Cloud Storage and save metadata to audio table for note
     */
    public function uploadAudioFileToGCSForNote(int $noteId, int $sourceFileId, string $localAudioPath): string
    {
        if(empty($localAudioPath) || !file_exists($localAudioPath)){
            throw new \Exception("Audio file not found locally.");
        }

        $audioExtension = pathinfo($localAudioPath, PATHINFO_EXTENSION) ?: 'wav';
        $uniqueFileName = 'note_' . $noteId . '_' . uniqid('', true) . '.' . $audioExtension;
        
        // Get file info to determine folder structure
        $conn = $this->db->connect();
        $fileStmt = $conn->prepare("SELECT userID, folderID FROM file WHERE fileID = :fileID");
        $fileStmt->bindParam(':fileID', $sourceFileId, \PDO::PARAM_INT);
        $fileStmt->execute();
        $fileInfo = $fileStmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$fileInfo) {
            throw new \Exception("Source file not found.");
        }
        
        $userId = (int)$fileInfo['userID'];
        $folderId = $fileInfo['folderID'];
        
        $logicalFolderPath = '';
        if ($folderId !== null) {
            $logicalFolderPath = $this->getLogicalFolderPath($folderId, $userId);
        }

        $gcsObjectName = 'user_upload/' . $userId . '/content/' . $logicalFolderPath . $uniqueFileName;

        $bucket = $this->storage->bucket($this->bucketName);
        
        // Use file stream instead of file_get_contents to avoid temp file permission issues
        $audioStream = fopen($localAudioPath, 'rb');
        if (!$audioStream) {
            throw new \Exception("Failed to open audio file for reading.");
        }
        
        $options = [
            'name' => $gcsObjectName,
            'metadata' => ['contentType' => 'audio/' . ($audioExtension === 'wav' ? 'wav' : 'x-wav')]
        ];
        
        $bucket->upload($audioStream, $options);
        // Note: GCS upload() automatically closes the stream, so no need to fclose()

        // Clean up local file
        @unlink($localAudioPath);
        
        // Save to audio table using only noteID (audio is based on note content, not file content)
        $stmt = $conn->prepare("INSERT INTO audio (noteID, audioPath) VALUES (:noteID, :audioPath) ON DUPLICATE KEY UPDATE audioPath = :audioPath");
        $stmt->bindParam(':noteID', $noteId, \PDO::PARAM_INT);
        $stmt->bindParam(':audioPath', $gcsObjectName);
        $stmt->execute();
        
        return $gcsObjectName;
    }

    /**
     * Get audio file for a specific summary ID from audio table
     */
    public function getAudioFileForSummary(int $summaryId, int $userId): ?array
    {
        $conn = $this->db->connect();
        
        // Verify summary belongs to user and get audio record directly using summaryID
        $query = "SELECT a.* FROM audio a 
                  INNER JOIN summary s ON a.summaryID = s.summaryID
                  INNER JOIN file f ON s.fileID = f.fileID 
                  WHERE a.summaryID = :summaryID AND f.userID = :userID";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':summaryID', $summaryId, \PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get audio file for a specific note ID from audio table
     */
    public function getAudioFileForNote(int $noteId, int $userId): ?array
    {
        $conn = $this->db->connect();
        
        // Verify note belongs to user and get audio record directly using noteID
        $query = "SELECT a.* FROM audio a 
                  INNER JOIN note n ON a.noteID = n.noteID
                  INNER JOIN file f ON n.fileID = f.fileID 
                  WHERE a.noteID = :noteID AND f.userID = :userID";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':noteID', $noteId, \PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get signed URL for audio file from GCS
     */
    public function getAudioSignedUrl(string $gcsPath): string
    {
        $bucket = $this->storage->bucket($this->bucketName);
        $object = $bucket->object($gcsPath);
        
        if (!$object->exists()) {
            throw new \Exception("Audio file not found in Google Cloud Storage.");
        }

        return $object->signedUrl(new \DateTimeImmutable('+1 hour'), ['version' => 'v4']);
    }




    /**
     * Upload file to Google Cloud Storage and save metadata to database
     */
    public function uploadFileToGCS($userId, $folderId, $extractedText, $fileContent = null, $file = null, $originalFileName = null)
    {
        if ($file != null) {
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $uniqueFileName = $this->generateUniqueFileName($fileExtension);
        } else {
            $fileExtension = 'txt';
            $uniqueFileName = $this->generateUniqueFileName($fileExtension);
        }

        // Ensure $folderId is truly null if it's 0 or empty string from form
        if (empty($folderId) || $folderId == 0) {
            $folderId = null;
        }

        $logicalFolderPath = '';
        if ($folderId !== null) {
            $logicalFolderPath = $this->getLogicalFolderPath($folderId, $userId);
        }

        $gcsObjectName = 'user_upload/' . $userId . '/content/' . $logicalFolderPath . $uniqueFileName;
        $fileName = !empty($originalFileName) ? $originalFileName : ($file != null ? $file['name'] : $uniqueFileName);

        if ($fileContent != null) { // If file is uploaded, upload to GCS
            $options = [
                'name' => $gcsObjectName,
                'metadata' => ['contentType' => $file['type'] ?? 'text/plain']
            ];
        } else {
            $options = [
                'name' => $gcsObjectName
            ];
        }
        $bucket = $this->storage->bucket($this->bucketName);
        $bucket->upload($fileContent, $options);

        $conn = $this->db->connect();
        $query = "INSERT INTO file (userID, folderID, name, fileType, filePath, extracted_text) VALUES (:userID, :folderID, :name, :fileType, :filePath, :extractedText)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userID', $userId);
        // Explicitly bind folderID as NULL if it's null, otherwise as INT
        $stmt->bindParam(':folderID', $folderId, $folderId === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
        $stmt->bindParam(':name', $fileName);
        $stmt->bindParam(':fileType', $fileExtension);
        $stmt->bindParam(':filePath', $gcsObjectName);
        $stmt->bindParam(':extractedText', $extractedText);
        $stmt->execute();
        return $conn->lastInsertId();
    }

    /**
     * Get the latest uploaded file for a user
     */
    public function getLatestFileForUser($userId)
    {
        $conn = $this->db->connect();
        $query = "SELECT * FROM file WHERE userID = :userID ORDER BY fileID DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all files for a user, ordered by most recent first
     */
    public function getFilesForUser($userId)
    {
        $conn = $this->db->connect();
        $query = "SELECT * FROM file WHERE userID = :userID ORDER BY fileID DESC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ============================================================================ 
    // ALL DOCUMENTS PAGE (allDocument.php)
    // ============================================================================ 

    /**
     * Get folders and files for a specific parent folder
     */
    public function getFoldersAndFiles($userId, $parentId = null)
    {
        $conn = $this->db->connect();
        $results = ['folders' => [], 'files' => []];

        // Get folders
        $folderQuery = "SELECT folderID, name, parentFolderId FROM folder WHERE userID = :userID AND " . ($parentId === null ? "parentFolderId IS NULL" : "parentFolderId = :parentFolderId");
        $folderStmt = $conn->prepare($folderQuery);
        $folderStmt->bindParam(':userID', $userId);
        if ($parentId !== null) {
            $folderStmt->bindParam(':parentFolderId', $parentId);
        }
        $folderStmt->execute();
        $results['folders'] = $folderStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get files
        $fileQuery = "SELECT fileID, name, fileType FROM file WHERE userID = :userID AND " . ($parentId === null ? "folderID IS NULL" : "folderID = :folderID");
        $fileStmt = $conn->prepare($fileQuery);
        $fileStmt->bindParam(':userID', $userId);
        if ($parentId !== null) {
            $fileStmt->bindParam(':folderID', $parentId);
        }
        $fileStmt->execute();
        $results['files'] = $fileStmt->fetchAll(\PDO::FETCH_ASSOC);

        return $results;
    }

    /**
     * Search folders and files by name
     */
    public function searchFilesAndFolders($userId, $searchQuery)
    {
        $conn = $this->db->connect();
        $results = ['folders' => [], 'files' => []];
        $searchParam = '%' . $searchQuery . '%';

        // Search folders
        $folderQuery = "SELECT folderID, name, parentFolderId FROM folder WHERE userID = :userID AND name LIKE :searchQuery";
        $folderStmt = $conn->prepare($folderQuery);
        $folderStmt->bindParam(':userID', $userId);
        $folderStmt->bindParam(':searchQuery', $searchParam);
        $folderStmt->execute();
        $results['folders'] = $folderStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Search files
        $fileQuery = "SELECT fileID, name, fileType FROM file WHERE userID = :userID AND name LIKE :searchQuery";
        $fileStmt = $conn->prepare($fileQuery);
        $fileStmt->bindParam(':userID', $userId);
        $fileStmt->bindParam(':searchQuery', $searchParam);
        $fileStmt->execute();
        $results['files'] = $fileStmt->fetchAll(\PDO::FETCH_ASSOC);

        return $results;
    }

    /**
     * Get document content from GCS (returns file content or signed URL for images)
     */
    public function getDocumentContent($fileId, $userId)
    {
        $conn = $this->db->connect();
        $query = "SELECT filePath, fileType, extracted_text FROM file WHERE fileID = :fileID AND userID = :userID";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':fileID', $fileId);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        $fileData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$fileData) {
            throw new \Exception("File not found or access denied.");
        }

        $gcsObjectName = $fileData['filePath'];
        $fileType = $fileData['fileType'];
        $extractedText = $fileData['extracted_text'];

        $bucket = $this->storage->bucket($this->bucketName);
        $object = $bucket->object($gcsObjectName);

        if (!$object->exists()) {
            throw new \Exception("File not found in Google Cloud Storage.");
        }

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

        if (in_array(strtolower($fileType), $imageExtensions)) {
            return [
                'type' => 'image',
                'content' => $object->signedUrl(new \DateTimeImmutable('+1 hour'), ['version' => 'v4']),
                'extracted_text' => $extractedText
            ];
        } else {
            return [
                'type' => 'text',
                'content' => $object->downloadAsString(),
                'extracted_text' => $extractedText
            ];
        }
    }

    /**
     * Delete a document from GCS and database
     */
    public function deleteDocument($fileId, $userId)
    {
        $conn = $this->db->connect();
        $query = "SELECT filePath FROM file WHERE fileID = :fileID AND userID = :userID";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':fileID', $fileId);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        $fileData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$fileData) {
            throw new \Exception("File not found or access denied.");
        }

        $gcsObjectName = $fileData['filePath'];

        // Delete from GCS
        $bucket = $this->storage->bucket($this->bucketName);
        $object = $bucket->object($gcsObjectName);

        if ($object->exists()) {
            $object->delete();
        }

        // Delete from database
        $deleteQuery = "DELETE FROM file WHERE fileID = :fileID AND userID = :userID";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bindParam(':fileID', $fileId);
        $deleteStmt->bindParam(':userID', $userId);
        return $deleteStmt->execute();
    }

    /**
     * Delete a folder and all its contents from GCS and database
     */
    public function deleteFolder($folderId, $userId)
    {
        $conn = $this->db->connect();
        // First, check if the folder belongs to the user
        $checkQuery = "SELECT folderID FROM folder WHERE folderID = :folderID AND userID = :userID";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindParam(':folderID', $folderId);
        $checkStmt->bindParam(':userID', $userId);
        $checkStmt->execute();
        if (!$checkStmt->fetch()) {
            throw new \Exception("Folder not found or access denied.");
        }

        $this->recursivelyDeleteFolder($folderId, $userId);

        return true;
    }

    private function recursivelyDeleteFolder($folderId, $userId)
    {
        $conn = $this->db->connect();
        $bucket = $this->storage->bucket($this->bucketName);

        // Get all files in the current folder
        $filesQuery = "SELECT fileID, filePath FROM file WHERE folderID = :folderID AND userID = :userID";
        $filesStmt = $conn->prepare($filesQuery);
        $filesStmt->bindParam(':folderID', $folderId);
        $filesStmt->bindParam(':userID', $userId);
        $filesStmt->execute();
        $files = $filesStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Delete all files in the current folder
        foreach ($files as $file) {
            $this->deleteDocument($file['fileID'], $userId);
        }

        // Get all subfolders in the current folder
        $foldersQuery = "SELECT folderID FROM folder WHERE parentFolderId = :folderID AND userID = :userID";
        $foldersStmt = $conn->prepare($foldersQuery);
        $foldersStmt->bindParam(':folderID', $folderId);
        $foldersStmt->bindParam(':userID', $userId);
        $foldersStmt->execute();
        $folders = $foldersStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Recursively delete all subfolders
        foreach ($folders as $folder) {
            $this->recursivelyDeleteFolder($folder['folderID'], $userId);
        }

        // Delete the folder from GCS
        $logicalFolderPath = $this->getLogicalFolderPath($folderId, $userId);
        $prefix = 'user_upload/' . $userId . '/content/' . $logicalFolderPath;
        $objects = $bucket->objects(['prefix' => $prefix]);
        foreach ($objects as $obj) {
            $obj->delete();
        }

        // Delete the folder from the database
        $deleteQuery = "DELETE FROM folder WHERE folderID = :folderID";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bindParam(':folderID', $folderId);
        $deleteStmt->execute();
    }

    /**
     * Helper function to recursively update folderPath for a folder and all its children
     */
    private function updateFolderPathRecursive($folderId, $newFolderPath, $userId, $conn)
    {
        // Update current folder's path
        $updateQuery = "UPDATE folder SET folderPath = :folderPath WHERE folderID = :folderID AND userID = :userID";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':folderPath', $newFolderPath);
        $updateStmt->bindParam(':folderID', $folderId);
        $updateStmt->bindParam(':userID', $userId);
        $updateStmt->execute();

        // Get folder name for child paths
        $nameQuery = "SELECT name FROM folder WHERE folderID = :folderID";
        $nameStmt = $conn->prepare($nameQuery);
        $nameStmt->bindParam(':folderID', $folderId);
        $nameStmt->execute();
        $folder = $nameStmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$folder) {
            return;
        }

        // Update all child folders recursively
        $childrenQuery = "SELECT folderID, name FROM folder WHERE parentFolderId = :folderID AND userID = :userID";
        $childrenStmt = $conn->prepare($childrenQuery);
        $childrenStmt->bindParam(':folderID', $folderId);
        $childrenStmt->bindParam(':userID', $userId);
        $childrenStmt->execute();
        $children = $childrenStmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($children as $child) {
            $childPath = $newFolderPath . $child['name'] . '/';
            $this->updateFolderPathRecursive($child['folderID'], $childPath, $userId, $conn);
        }
    }

    /**
     * Rename a folder in GCS and database
     */
    public function renameFolder($folderId, $newName, $userId)
    {
        $conn = $this->db->connect();
        
        // Check if folder belongs to user
        $checkQuery = "SELECT folderID FROM folder WHERE folderID = :folderID AND userID = :userID";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindParam(':folderID', $folderId);
        $checkStmt->bindParam(':userID', $userId);
        $checkStmt->execute();
        if (!$checkStmt->fetch()) {
            throw new \Exception("Folder not found or access denied.");
        }

        // Get folder info to build paths
        $folderInfoQuery = "SELECT name, parentFolderId, folderPath FROM folder WHERE folderID = :folderID AND userID = :userID";
        $folderInfoStmt = $conn->prepare($folderInfoQuery);
        $folderInfoStmt->bindParam(':folderID', $folderId);
        $folderInfoStmt->bindParam(':userID', $userId);
        $folderInfoStmt->execute();
        $folderInfo = $folderInfoStmt->fetch(\PDO::FETCH_ASSOC);

        if (!$folderInfo) {
            throw new \Exception("Could not retrieve folder information for path construction.");
        }

        // Build parent folder path (use stored folderPath if available)
        $parentFolderPath = '';
        if ($folderInfo['parentFolderId'] !== null) {
            $parentFolderPath = $this->getLogicalFolderPath($folderInfo['parentFolderId'], $userId);
        }

        // Construct old and new prefixes for GCS objects
        $oldLogicalPath = $parentFolderPath . $folderInfo['name'] . '/';
        $oldPrefix = 'user_upload/' . $userId . '/content/' . $oldLogicalPath;
        $newLogicalPath = $parentFolderPath . $newName . '/';
        $newPrefix = 'user_upload/' . $userId . '/content/' . $newLogicalPath;

        // Rename all objects in GCS
        $bucket = $this->storage->bucket($this->bucketName);
        $objects = $bucket->objects(['prefix' => $oldPrefix]);
        foreach ($objects as $object) {
            $oldObjectName = $object->name();
            $newObjectName = str_replace($oldPrefix, $newPrefix, $oldObjectName);
            $object->copy($bucket, ['name' => $newObjectName]);
            $object->delete();
        }

        // Update folder name in database
        $updateQuery = "UPDATE folder SET name = :newName WHERE folderID = :folderID";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':newName', $newName);
        $updateStmt->bindParam(':folderID', $folderId);
        $updateStmt->execute();

        // Update folderPath for this folder and all children
        $this->updateFolderPathRecursive($folderId, $newLogicalPath, $userId, $conn);
        
        return true;
    }

    /**
     * Rename a file/document in database
     */
    public function renameFile($fileId, $newName, $userId)
    {
        $conn = $this->db->connect();
        
        // Check if file belongs to user
        $checkQuery = "SELECT fileID FROM file WHERE fileID = :fileID AND userID = :userID";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindParam(':fileID', $fileId);
        $checkStmt->bindParam(':userID', $userId);
        $checkStmt->execute();
        if (!$checkStmt->fetch()) {
            throw new \Exception("File not found or access denied.");
        }

        // Update file name in database
        $updateQuery = "UPDATE file SET name = :newName WHERE fileID = :fileID";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':newName', $newName);
        $updateStmt->bindParam(':fileID', $fileId);
        return $updateStmt->execute();
    }

    /**
     * Move a file to another folder in GCS and database
     */
    public function moveFile($fileId, $newFolderId, $userId)
    {
        $conn = $this->db->connect();
        
        // Get current file path
        $pathQuery = "SELECT filePath FROM file WHERE fileID = :fileID AND userID = :userID";
        $pathStmt = $conn->prepare($pathQuery);
        $pathStmt->bindParam(':fileID', $fileId);
        $pathStmt->bindParam(':userID', $userId);
        $pathStmt->execute();
        $fileData = $pathStmt->fetch(\PDO::FETCH_ASSOC);

        if (!$fileData) {
            throw new \Exception("File not found or access denied.");
        }
        
        $currentFilePath = $fileData['filePath'];

        // Build new folder path
        $logicalFolderPath = '';
        if ($newFolderId !== null) {
            $logicalFolderPath = $this->getLogicalFolderPath($newFolderId, $userId);
        }
        
        $fileName = basename($currentFilePath);
        $newFilePath = 'user_upload/' . $userId . '/content/' . $logicalFolderPath . $fileName;

        // Move file in GCS
        $bucket = $this->storage->bucket($this->bucketName);
        $object = $bucket->object($currentFilePath);
        if ($object->exists()) {
            $object->copy($bucket, ['name' => $newFilePath]);
            $object->delete();
        } else {
            throw new \Exception("File not found in Google Cloud Storage.");
        }

        // Update database
        $updateQuery = "UPDATE file SET folderID = :newFolderID, filePath = :newFilePath WHERE fileID = :fileID AND userID = :userID";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':newFolderID', $newFolderId, $newFolderId === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
        $updateStmt->bindParam(':newFilePath', $newFilePath);
        $updateStmt->bindParam(':fileID', $fileId);
        $updateStmt->bindParam(':userID', $userId);
        return $updateStmt->execute();
    }

    /**
     * Move a folder to another parent folder in GCS and database
     */
    public function moveFolder($folderId, $newParentId, $userId)
    {
        // Validate against moving into self or child
        if ($folderId == $newParentId) {
            throw new \Exception("Cannot move a folder into itself.");
        }

        $currentId = $newParentId;
        while ($currentId !== null) {
            if ($currentId == $folderId) {
                throw new \Exception("Cannot move a folder into one of its own subfolders.");
            }
            $parentInfo = $this->getFolderInfo($currentId);
            $currentId = $parentInfo ? $parentInfo['parentFolderId'] : null;
        }

        $conn = $this->db->connect();

        // Get old path information
        $folderInfo = $this->getFolderInfo($folderId);
        if (!$folderInfo) {
            throw new \Exception("Folder not found or access denied.");
        }
        
        // Use stored folderPath if available, otherwise build it
        $oldParentPath = '';
        if ($folderInfo['parentFolderId'] !== null) {
            $oldParentPath = $this->getLogicalFolderPath($folderInfo['parentFolderId'], $userId);
        }
        $oldLogicalPath = $oldParentPath . $folderInfo['name'] . '/';
        $oldPrefix = 'user_upload/' . $userId . '/content/' . $oldLogicalPath;

        // Get new path information
        $newParentPath = '';
        if ($newParentId !== null) {
            $newParentPath = $this->getLogicalFolderPath($newParentId, $userId);
        }
        $newLogicalPath = $newParentPath . $folderInfo['name'] . '/';
        $newPrefix = 'user_upload/' . $userId . '/content/' . $newLogicalPath;

        // Move all objects in GCS
        $bucket = $this->storage->bucket($this->bucketName);
        $objects = $bucket->objects(['prefix' => $oldPrefix]);
        foreach ($objects as $object) {
            $newObjectName = str_replace($oldPrefix, $newPrefix, $object->name());
            $object->copy($bucket, ['name' => $newObjectName]);
            $object->delete();
        }

        // Update database - set new parent
        $updateQuery = "UPDATE folder SET parentFolderId = :newParentId WHERE folderID = :folderID AND userID = :userID";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':newParentId', $newParentId, $newParentId === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
        $updateStmt->bindParam(':folderID', $folderId);
        $updateStmt->bindParam(':userID', $userId);
        $updateStmt->execute();

        // Update folderPath for this folder and all children
        $this->updateFolderPathRecursive($folderId, $newLogicalPath, $userId, $conn);
        
        return true;
    }

    // ============================================================================ 
    // NEW FOLDER PAGE (newFolder.php)
    // ============================================================================ 

    /**
     * Create a new folder in GCS and database
     */
    public function createFolder($userId, $folderName, $parentFolderId = null)
    {
        $bucket = $this->storage->bucket($this->bucketName);
        
        // Calculate folderPath
        $folderPath = '';
        if ($parentFolderId != null) {
            $parentFolderInfo = $this->getFolderInfo($parentFolderId);
            if (!$parentFolderInfo) {
                throw new \Exception("Parent folder not found or access denied.");
            }
            
            // Get parent folderPath (will build if missing)
            $parentPath = $this->getLogicalFolderPath($parentFolderId, $userId);
            $folderPath = $parentPath . $folderName . '/';
            $gcsFolderName = 'user_upload/' . $userId . '/content/' . $folderPath;
        } else {
            $folderPath = $folderName . '/';
            $gcsFolderName = 'user_upload/' . $userId . '/content/' . $folderPath;
        }

        if (!$bucket->object($gcsFolderName)->exists()) {
            $bucket->upload('', ['name' => $gcsFolderName]);
        }

        $conn = $this->db->connect();
        $query = "INSERT INTO folder (userID, parentFolderId, name, folderPath) VALUES (:userID, :parentFolderId, :name, :folderPath)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userID', $userId);
        $stmt->bindParam(':parentFolderId', $parentFolderId, $parentFolderId === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
        $stmt->bindParam(':name', $folderName);
        $stmt->bindParam(':folderPath', $folderPath);
        $stmt->execute();
        return $conn->lastInsertId();
    }

    // ============================================================================ 
    // SUMMARY PAGE (summary.php)
    // ============================================================================ 

    /**
     * Save a summary to database
     */
    public function saveSummary(int $fileId, string $title, string $content): int
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("INSERT INTO summary (fileID, title, content) VALUES (:fileID, :title, :content)");
        $stmt->bindParam(':fileID', $fileId);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->execute();
        return (int)$conn->lastInsertId();
    }

    /**
     * Get all summaries for a specific file
     */
    public function getSummaryByFile(int $fileId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM summary WHERE fileID = :fileID ORDER BY createdAt DESC");
        $stmt->bindParam(':fileID', $fileId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get a specific summary by ID and user ID (verifies ownership through file)
     */
    public function getSummaryById(int $summaryId, int $userId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT s.* FROM summary s 
                                INNER JOIN file f ON s.fileID = f.fileID 
                                WHERE s.summaryID = :summaryID AND f.userID = :userID");
        $stmt->bindParam(':summaryID', $summaryId);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Delete summary from database
     */
    public function deleteSummary(int $summaryId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("DELETE FROM summary WHERE summaryID = :summaryID");
        $stmt->bindParam(':summaryID', $summaryId);
        return $stmt->execute();
    }

    /**
     * Save summary as file from database and upload to GCS
     */
    public function saveSummaryAsFile(int $summaryId, $fileId, $folderId)
    {
        $conn = $this->db->connect();
        // Get summary with file info to get userId
        $summary = $conn->prepare("SELECT s.*, f.userID FROM summary s 
                                    INNER JOIN file f ON s.fileID = f.fileID 
                                    WHERE s.summaryID = :summaryID");
        $summary->bindParam(':summaryID', $summaryId);
        $summary->execute();
        $summaryData = $summary->fetch(\PDO::FETCH_ASSOC);

        $sourceText = $summaryData['content'];
        $title = $summaryData['title'];
        $userId = $summaryData['userID'];

        // Upload to GCS
        $this->uploadFileToGCS($userId, $folderId, $sourceText, $sourceText, null, $title);
    }

    // ============================================================================ 
    // NOTE PAGE (note.php)
    // ============================================================================ 

    /**
     * Save a note to database
     */
    public function saveNotes(int $fileId, string $title, string $content): int
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("INSERT INTO note (fileID, title, content) VALUES (:fileID, :title, :content)");
        $stmt->bindParam(':fileID', $fileId);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->execute();
        return (int)$conn->lastInsertId();
    }

    /**
     * Get all notes for a specific file
     */
    public function getNotesByFile(int $fileId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM note WHERE fileID = :fileID ORDER BY createdAt DESC");
        $stmt->bindParam(':fileID', $fileId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get a specific note by ID and user ID (checks ownership through file)
     */
    public function getNoteById(int $noteId, int $userId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT n.* FROM note n 
                                INNER JOIN file f ON n.fileID = f.fileID 
                                WHERE n.noteID = :noteID AND f.userID = :userID");
        $stmt->bindParam(':noteID', $noteId);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Delete note from database
     */
    public function deleteNote(int $noteId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("DELETE FROM note WHERE noteID = :noteID");
        $stmt->bindParam(':noteID', $noteId);
        return $stmt->execute();
    }

    /**
     * Save note as file from database and upload to GCS
     */
    public function saveNoteAsFile(int $noteId, $fileId, $folderId)
    {
        $conn = $this->db->connect();
        // Get note with file info to get userId
        $note = $conn->prepare("SELECT n.*, f.userID FROM note n 
                                INNER JOIN file f ON n.fileID = f.fileID 
                                WHERE n.noteID = :noteID");
        $note->bindParam(':noteID', $noteId);
        $note->execute();
        $noteData = $note->fetch(\PDO::FETCH_ASSOC);

        $sourceText = $noteData['content'];
        $title = $noteData['title'];
        $userId = $noteData['userID'];

        //Upload to GCS
        $this->uploadFileToGCS($userId, $folderId, $sourceText, $sourceText, null, $title);
    }

    /**
     * Update note content and title (inline editing)
     */
    public function updateNote(int $noteId, int $fileId, int $userId, string $title, string $content): bool
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("UPDATE note n 
                                INNER JOIN file f ON n.fileID = f.fileID 
                                SET n.title = :title, n.content = :content
                                WHERE n.noteID = :noteID AND n.fileID = :fileID AND f.userID = :userID");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':noteID', $noteId, \PDO::PARAM_INT);
        $stmt->bindParam(':fileID', $fileId, \PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Upload note image to GCS and save metadata to database, returns image info with signed URL
     */
    public function saveNoteImage(int $noteId, string $fileContent, string $fileExtension, int $userId): array
    {
        // Generate unique filename
        $uniqueFileName = uniqid('note_img_', true) . '.' . $fileExtension;
        $gcsObjectName = 'user_upload/' . $userId . '/note_images/' . $uniqueFileName;

        // Upload to GCS
        $bucket = $this->storage->bucket($this->bucketName);

        // Determine content type
        $contentType = 'image/' . ($fileExtension === 'jpg' ? 'jpeg' : $fileExtension);

        $bucket->upload($fileContent, [
            'name' => $gcsObjectName,
            'metadata' => ['contentType' => $contentType]
        ]);

        // Generate signed URL (valid for 7 days - maximum allowed by GCS)
        $object = $bucket->object($gcsObjectName);
        $signedUrl = $object->signedUrl(new \DateTimeImmutable('+7 days'), ['version' => 'v4']);

        // Save image path to database
        $conn = $this->db->connect();
        $stmt = $conn->prepare("INSERT INTO image (noteID, imagePath) VALUES (:noteID, :imagePath)");
        $stmt->bindParam(':noteID', $noteId, \PDO::PARAM_INT);
        $stmt->bindParam(':imagePath', $gcsObjectName);
        $stmt->execute();

        return [
            'imageId' => (int)$conn->lastInsertId(),
            'imageUrl' => $signedUrl,
            'imagePath' => $gcsObjectName
        ];
    }

    // ============================================================================ 
    // MINDMAP PAGE (mindmap.php)
    // ============================================================================ 

    /**
     * Save a mindmap to database
     */
    public function saveMindmap(int $fileId, string $title, string $data): int
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("INSERT INTO mindmap (fileID, title, data) VALUES (:fileID, :title, :data)");
        $stmt->bindParam(':fileID', $fileId);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':data', $data);
        $stmt->execute();
        return (int)$conn->lastInsertId();
    }

    /**
     * Update mindmap data payload
     */
    public function updateMindmap(int $mindmapId, int $fileId, int $userId, array $payload): bool
    {
        $conn = $this->db->connect();
        $dataJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
        
        $stmt = $conn->prepare("UPDATE mindmap m 
                                INNER JOIN file f ON m.fileID = f.fileID 
                                SET m.data = :data
                                WHERE m.mindmapID = :mindmapID 
                                AND m.fileID = :fileID 
                                AND f.userID = :userID");
        $stmt->bindParam(':data', $dataJson);
        $stmt->bindParam(':mindmapID', $mindmapId, \PDO::PARAM_INT);
        $stmt->bindParam(':fileID', $fileId, \PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    /**
     * Get all mindmaps for a specific file
     */
    public function getMindmapByFile(int $fileId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM mindmap WHERE fileID = :fileID ORDER BY createdAt DESC");
        $stmt->bindParam(':fileID', $fileId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get a specific mindmap by ID and file ID (checks ownership through file)
     */
    public function getMindmapById(int $mindmapId, int $fileId, ?int $userId = null)
    {
        $conn = $this->db->connect();
        
        if ($userId !== null) {
            // Verify ownership through file table
            $stmt = $conn->prepare("SELECT m.* FROM mindmap m 
                                    INNER JOIN file f ON m.fileID = f.fileID 
                                    WHERE m.mindmapID = :mindmapID 
                                    AND m.fileID = :fileID 
                                    AND f.userID = :userID");
            $stmt->bindParam(':mindmapID', $mindmapId, \PDO::PARAM_INT);
            $stmt->bindParam(':fileID', $fileId, \PDO::PARAM_INT);
            $stmt->bindParam(':userID', $userId, \PDO::PARAM_INT);
        } else {
            // Basic query without ownership check
            $stmt = $conn->prepare("SELECT * FROM mindmap 
                                    WHERE mindmapID = :mindmapID 
                                    AND fileID = :fileID");
            $stmt->bindParam(':mindmapID', $mindmapId, \PDO::PARAM_INT);
            $stmt->bindParam(':fileID', $fileId, \PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Delete mindmap from database
     */
    public function deleteMindmap(int $mindmapId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("DELETE FROM mindmap WHERE mindmapID = :mindmapID");
        $stmt->bindParam(':mindmapID', $mindmapId);
        return $stmt->execute();
    }

    // ============================================================================ 
    // FLASHCARD PAGE (flashcard.php)
    // ============================================================================ 

    /**
     * Save flashcards to database
     */
    /**
     * Saves flashcards as comma-separated strings (one row per title/set)
     * Uses "," as separator between cards (format: "term1","term2","term3")
     * @param int $fileId
     * @param string $title
     * @param array $terms Array of term strings
     * @param array $definitions Array of definition strings
     * @return int The flashcard ID
     */
    public function saveFlashcards(int $fileId, string $title, array $terms, array $definitions): int
    {
        try {
            $conn = $this->db->connect();
            
            // Escape quotes and commas in terms, then wrap each in quotes and join with ","
            $escapedTerms = array_map(function($term) {
                // Escape quotes and commas within the content
                $term = str_replace('\\', '\\\\', $term); // Escape backslashes first
                $term = str_replace('"', '\\"', $term);  // Escape quotes
                $term = str_replace(',', '\\,', $term);   // Escape commas
                return '"' . $term . '"';
            }, $terms);
            $termString = implode(',', $escapedTerms);
            
            // Escape quotes and commas in definitions, then wrap each in quotes and join with ","
            $escapedDefinitions = array_map(function($def) {
                // Escape quotes and commas within the content
                $def = str_replace('\\', '\\\\', $def); // Escape backslashes first
                $def = str_replace('"', '\\"', $def);    // Escape quotes
                $def = str_replace(',', '\\,', $def);    // Escape commas
                return '"' . $def . '"';
            }, $definitions);
            $definitionString = implode(',', $escapedDefinitions);
            
            $stmt = $conn->prepare("INSERT INTO flashcard (fileID, title, term, definition) VALUES (:fileID, :title, :term, :definition)");    
            $stmt->bindParam(':fileID', $fileId);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':term', $termString);
            $stmt->bindParam(':definition', $definitionString);
            $stmt->execute();
            $lastId = (int)$conn->lastInsertId();
            if ($lastId === 0) {
                error_log('Warning: saveFlashcards returned 0 lastInsertId');
            }
            return $lastId;
        } catch (\PDOException $e) {
            error_log('Error saving flashcard: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all flashcards for a specific file, grouped by title (one entry per title/set)
     * Now calculates card count from JSON arrays
     */
    public function getFlashcardsByFile(int $fileId)
    {
        $conn = $this->db->connect();
        // Get distinct titles with their most recent flashcard info
        $stmt = $conn->prepare("
            SELECT 
                title,
                fileID,
                MAX(createdAt) as createdAt,
                MIN(flashcardID) as flashcardID,
                term,
                definition
            FROM flashcard 
            WHERE fileID = :fileID 
            GROUP BY title, fileID, term, definition
            ORDER BY createdAt DESC
        ");
        $stmt->bindParam(':fileID', $fileId);
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Calculate card count from comma-separated strings
        foreach ($results as &$result) {
            $termString = $result['term'] ?? '';
            $definitionString = $result['definition'] ?? '';
            
            // Parse comma-separated strings (format: "term1","term2","term3")
            $termString = trim($termString, '"');
            $definitionString = trim($definitionString, '"');
            
            $termCount = 0;
            $defCount = 0;
            
            if (!empty($termString)) {
                // Count cards by counting opening quotes (each opening quote = one card)
                // Format: "term1","term2","term3"
                $inQuotes = false;
                $escaped = false;
                $cardCount = 0;
                
                for ($i = 0; $i < strlen($termString); $i++) {
                    $char = $termString[$i];
                    
                    if ($escaped) {
                        $escaped = false;
                        continue;
                    }
                    
                    if ($char === '\\') {
                        $escaped = true;
                        continue;
                    }
                    
                    if ($char === '"') {
                        if (!$inQuotes) {
                            // Opening quote - start of a new card
                            $cardCount++;
                        }
                        $inQuotes = !$inQuotes;
                    }
                }
                $termCount = $cardCount;
            }
            
            if (!empty($definitionString)) {
                // Count cards by counting opening quotes (each opening quote = one card)
                // Format: "def1","def2","def3"
                $inQuotes = false;
                $escaped = false;
                $cardCount = 0;
                
                for ($i = 0; $i < strlen($definitionString); $i++) {
                    $char = $definitionString[$i];
                    
                    if ($escaped) {
                        $escaped = false;
                        continue;
                    }
                    
                    if ($char === '\\') {
                        $escaped = true;
                        continue;
                    }
                    
                    if ($char === '"') {
                        if (!$inQuotes) {
                            // Opening quote - start of a new card
                            $cardCount++;
                        }
                        $inQuotes = !$inQuotes;
                    }
                }
                $defCount = $cardCount;
            }
            
            $result['cardCount'] = max($termCount, $defCount, 1);
            
            // Remove term and definition from result (not needed in list)
            unset($result['term'], $result['definition']);
        }
        
        return $results;
    }

    /**
     * Get a specific flashcard by ID
     */
    public function getFlashcardsById(int $flashcardId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM flashcard WHERE flashcardID = :flashcardID");
        $stmt->bindParam(':flashcardID', $flashcardId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all flashcards with a specific title for a file
     */
    public function getFlashcardsByTitle(string $title, int $fileId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM flashcard WHERE title = :title AND fileID = :fileID ORDER BY flashcardID ASC");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':fileID', $fileId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Delete all flashcards with a specific title for a file
     */
    public function deleteFlashcardsByTitle(string $title, int $fileId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("DELETE FROM flashcard WHERE title = :title AND fileID = :fileID");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':fileID', $fileId);
        return $stmt->execute();
    }

    /**
     * Delete a single flashcard by ID with ownership check
     */
    public function deleteFlashcardById(int $flashcardId, int $userId): bool
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("DELETE fc FROM flashcard fc
                                INNER JOIN file f ON fc.fileID = f.fileID
                                WHERE fc.flashcardID = :flashcardID AND f.userID = :userID");
        $stmt->bindParam(':flashcardID', $flashcardId);
        $stmt->bindParam(':userID', $userId);
        return $stmt->execute();
    }

    /**
     * Get a specific flashcard ensuring it belongs to the user
     */
    public function getFlashcardWithOwner(int $flashcardId, int $userId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT fc.* 
                                FROM flashcard fc
                                INNER JOIN file f ON fc.fileID = f.fileID
                                WHERE fc.flashcardID = :flashcardID AND f.userID = :userID");
        $stmt->bindParam(':flashcardID', $flashcardId);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Update a flashcard (title, term, definition) with ownership check
     */
    public function updateFlashcard(int $flashcardId, string $title, string $term, string $definition, int $userId): bool
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("UPDATE flashcard fc
                                INNER JOIN file f ON fc.fileID = f.fileID
                                SET fc.title = :title, fc.term = :term, fc.definition = :definition
                                WHERE fc.flashcardID = :flashcardID AND f.userID = :userID");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':term', $term);
        $stmt->bindParam(':definition', $definition);
        $stmt->bindParam(':flashcardID', $flashcardId);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // ============================================================================ 
    // QUIZ PAGE (quiz.php)
    // ============================================================================ 

    /**
     * Save a quiz to database
     */
    public function saveQuiz(
        int $fileId,
        int $totalQuestions,
        string $title,
        array $questionConfig = [],
        int $examMode = 0,
        ?int $totalScore = null
    ): int {
        $this->ensureQuizSchema();
        $conn = $this->db->connect();
        $configJson = !empty($questionConfig) ? json_encode($questionConfig) : null;
        $stmt = $conn->prepare("INSERT INTO quiz (fileID, totalQuestions, examMode, status, title, questionConfig, totalScore) VALUES (:fileID, :totalQuestions, :examMode, 'pending', :title, :questionConfig, :totalScore)");
        $stmt->bindParam(':fileID', $fileId);
        $stmt->bindParam(':totalQuestions', $totalQuestions);
        $stmt->bindParam(':examMode', $examMode, \PDO::PARAM_INT);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':questionConfig', $configJson);
        $stmt->bindParam(':totalScore', $totalScore);
        $stmt->execute();
        return (int)$conn->lastInsertId();
    }

    /**
     * Save a question to database
     */
    public function saveQuestion(int $quizId, string $type, string $question, ?string $explanation = null): int
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("INSERT INTO question (quizID, type, question, explanation) VALUES (:quizID, :type, :question, :explanation)");
        $stmt->bindParam(':quizID', $quizId);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':question', $question);
        $stmt->bindParam(':explanation', $explanation);
        $stmt->execute();
        return (int)$conn->lastInsertId();
    }

    /**
     * Save a user answer to database
     */
    public function saveUserAnswer(int $questionId, string $userAnswer): int
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("INSERT INTO useranswer (questionID, userAnswer) VALUES (:questionID, :userAnswer)");
        $stmt->bindParam(':questionID', $questionId);
        $stmt->bindParam(':userAnswer', $userAnswer);
        $stmt->execute();
        return (int)$conn->lastInsertId();
    }

    /**
     * Get all quizzes for a specific file
     */
    public function getQuizByFile(int $fileId)
    {
        $this->ensureQuizSchema();
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM quiz WHERE fileID = :fileID ORDER BY createdAt DESC");
        $stmt->bindParam(':fileID', $fileId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get a specific quiz by ID
     */
    public function getQuizById(int $quizId)
    {
        $this->ensureQuizSchema();
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM quiz WHERE quizID = :quizID");
        $stmt->bindParam(':quizID', $quizId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get question data for a specific quiz
     */
    public function getQuestionByQuiz(int $quizId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM question WHERE quizID = :quizID");
        $stmt->bindParam(':quizID', $quizId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all questions for a specific quiz
     */
    public function getQuestionsByQuiz(int $quizId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM question WHERE quizID = :quizID ORDER BY questionID ASC");
        $stmt->bindParam(':quizID', $quizId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Save an option for a question
     */
    public function saveOption(int $questionId, string $text, bool $isCorrect): int
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("INSERT INTO option (questionID, text, isCorrect) VALUES (:questionID, :text, :isCorrect)");
        $stmt->bindParam(':questionID', $questionId);
        $stmt->bindParam(':text', $text);
        $stmt->bindParam(':isCorrect', $isCorrect, \PDO::PARAM_BOOL);
        $stmt->execute();
        return (int)$conn->lastInsertId();
    }

    /**
     * Get all options for a specific question
     */
    public function getOptionsByQuestion(int $questionId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM option WHERE questionID = :questionID ORDER BY optionID ASC");
        $stmt->bindParam(':questionID', $questionId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Save quiz score to database
     */
    public function saveScore(int $quizId, string $percentageScore): bool
    {
        $this->ensureQuizSchema();
        $conn = $this->db->connect();
        $stmt = $conn->prepare("UPDATE quiz SET totalScore = :totalScore, markAt = NOW() WHERE quizID = :quizID");
        $stmt->bindParam(':quizID', $quizId);
        $stmt->bindParam(':totalScore', $percentageScore);
        return $stmt->execute();
    }

    public function updateQuizStatus(int $quizId, string $status = 'completed', ?string $score = null): bool
    {
        $this->ensureQuizSchema();
        $conn = $this->db->connect();
        $sql = "UPDATE quiz SET status = :status";
        if ($score !== null) {
            $sql .= ", totalScore = :totalScore, markAt = NOW()";
        }
        $sql .= " WHERE quizID = :quizID";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':status', $status);
        if ($score !== null) {
            $stmt->bindParam(':totalScore', $score);
        }
        $stmt->bindParam(':quizID', $quizId);
        return $stmt->execute();
    }

    public function saveQuizAttempt(
        int $quizId,
        int $userId,
        array $answers,
        array $feedback = [],
        ?array $suggestions = null,
        ?float $score = null,
        int $examMode = 0
    ): int {
        $this->ensureQuizSchema();
        $conn = $this->db->connect();
        $stmt = $conn->prepare("INSERT INTO quiz_attempt (quizID, userID, answers, feedback, suggestions, score, examMode) VALUES (:quizID, :userID, :answers, :feedback, :suggestions, :score, :examMode)");
        $answersJson = json_encode($answers);
        $feedbackJson = !empty($feedback) ? json_encode($feedback) : null;
        $suggestionsJson = $suggestions ? json_encode($suggestions) : null;
        $stmt->bindParam(':quizID', $quizId);
        $stmt->bindParam(':userID', $userId);
        $stmt->bindParam(':answers', $answersJson);
        $stmt->bindParam(':feedback', $feedbackJson);
        $stmt->bindParam(':suggestions', $suggestionsJson);
        $stmt->bindParam(':score', $score);
        $stmt->bindParam(':examMode', $examMode, \PDO::PARAM_INT);
        $stmt->execute();
        return (int)$conn->lastInsertId();
    }

    public function getLatestQuizAttempt(int $quizId, int $userId)
    {
        $this->ensureQuizSchema();
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM quiz_attempt WHERE quizID = :quizID AND userID = :userID ORDER BY createdAt DESC LIMIT 1");
        $stmt->bindParam(':quizID', $quizId);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function hasQuizAttempt(int $quizId, int $userId): bool
    {
        $this->ensureQuizSchema();
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT COUNT(*) FROM quiz_attempt WHERE quizID = :quizID AND userID = :userID");
        $stmt->bindParam(':quizID', $quizId);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Get quiz statistics for a user
     */
    public function getQuizStatistics(int $userId, ?int $fileId = null): array
    {
        $this->ensureQuizSchema();
        $conn = $this->db->connect();
        
        $fileCondition = $fileId ? "AND q.fileID = :fileID" : "";
        $params = [':userID' => $userId];
        if ($fileId) {
            $params[':fileID'] = $fileId;
        }
        
        // Get all completed quizzes with their attempts
        // Use COALESCE to prefer totalScore from quiz table, fall back to attempt score
        $sql = "SELECT 
                    q.quizID,
                    q.title,
                    q.examMode,
                    COALESCE(q.totalScore, qa.score) as totalScore,
                    q.createdAt,
                    COALESCE(q.markAt, qa.createdAt) as markAt,
                    q.status,
                    qa.score as attemptScore,
                    qa.createdAt as attemptDate
                FROM quiz q
                LEFT JOIN quiz_attempt qa ON q.quizID = qa.quizID AND qa.userID = :userID
                WHERE q.fileID IN (SELECT fileID FROM file WHERE userID = :userID) $fileCondition
                AND q.status = 'completed'
                AND (q.totalScore IS NOT NULL OR qa.score IS NOT NULL)
                ORDER BY COALESCE(q.markAt, qa.createdAt, q.createdAt) DESC";
        
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Get overall statistics
        // Use COALESCE to prefer totalScore, fall back to attempt score for practice quizzes
        $statsSql = "SELECT 
                        COUNT(DISTINCT q.quizID) as totalQuizzes,
                        COUNT(DISTINCT CASE WHEN q.examMode = 1 THEN q.quizID END) as examQuizzes,
                        COUNT(DISTINCT CASE WHEN q.examMode = 0 THEN q.quizID END) as practiceQuizzes,
                        AVG(COALESCE(q.totalScore, qa.score)) as avgScore,
                        AVG(CASE WHEN q.examMode = 1 THEN COALESCE(q.totalScore, qa.score) ELSE NULL END) as avgExamScore,
                        AVG(CASE WHEN q.examMode = 0 THEN COALESCE(q.totalScore, qa.score) ELSE NULL END) as avgPracticeScore,
                        MAX(COALESCE(q.totalScore, qa.score)) as maxScore,
                        MIN(COALESCE(q.totalScore, qa.score)) as minScore
                    FROM quiz q
                    LEFT JOIN quiz_attempt qa ON q.quizID = qa.quizID AND qa.userID = :userID
                    WHERE q.fileID IN (SELECT fileID FROM file WHERE userID = :userID) $fileCondition
                    AND q.status = 'completed'
                    AND (q.totalScore IS NOT NULL OR qa.score IS NOT NULL)";
        
        $statsStmt = $conn->prepare($statsSql);
        foreach ($params as $key => $value) {
            $statsStmt->bindValue($key, $value);
        }
        $statsStmt->execute();
        $stats = $statsStmt->fetch(\PDO::FETCH_ASSOC);
        
        return [
            'quizzes' => $results,
            'statistics' => $stats
        ];
    }

    /**
     * Get suggested answers for a question
     */
    public function getSuggestedAnswers(int $questionId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT question FROM suggestedAnswers WHERE questionID = :questionID");
        $stmt->bindParam(':questionID', $questionId);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($result && isset($result['question'])) {
            $answer = json_decode($result['question'], true);
            return $answer;
        }
        
        return null;
    }

    /**
     * Delete a quiz and all related data (questions, answers, etc.)
     * CASCADE constraints in database will handle related records
     */
    public function deleteQuiz(int $quizId, int $userId): bool
    {
        $this->ensureQuizSchema();
        $conn = $this->db->connect();
        
        // Verify ownership before deletion
        $stmt = $conn->prepare(" 
            SELECT q.quizID 
            FROM quiz q
            INNER JOIN file f ON q.fileID = f.fileID
            WHERE q.quizID = :quizID AND f.userID = :userID
        ");
        $stmt->bindParam(':quizID', $quizId, \PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        
        if (!$stmt->fetch(\PDO::FETCH_ASSOC)) {
            return false; // Quiz not found or user doesn't own it
        }
        
        // Delete the quiz (CASCADE will delete related questions, answers, etc.)
        $deleteStmt = $conn->prepare("DELETE FROM quiz WHERE quizID = :quizID");
        $deleteStmt->bindParam(':quizID', $quizId, \PDO::PARAM_INT);
        $deleteStmt->execute();
        
        return $deleteStmt->rowCount() > 0;
    }

    // ============================================================================ 
    // CHATBOT PAGE (chatbot.php)
    // ============================================================================ 

    /**
     * Save a chat message to database
     */
    public function saveChatbot(int $fileId, string $title)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("INSERT INTO chatbot (fileID, title) VALUES (:fileID, :title)");
        $stmt->bindParam(':fileID', $fileId);
        $stmt->bindParam(':title', $title);
        $stmt->execute();
        return (int)$conn->lastInsertId();
    }

    /**
     * Save a user question in chat
     */
    public function saveQuestionChat(int $chatbotId, string $question)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("INSERT INTO questionChat (chatbotID, userQuestion) VALUES (:chatbotID, :userQuestion)");
        $stmt->bindParam(':chatbotID', $chatbotId);
        $stmt->bindParam(':userQuestion', $question);
        $stmt->execute();
        return (int)$conn->lastInsertId();
    }

    /**
     * Save a chatbot response
     */
    public function saveResponseChat(int $questionChatId, string $response)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("INSERT INTO responseChat (questionChatID, response) VALUES (:questionChatID, :response)");
        $stmt->bindParam(':questionChatID', $questionChatId);
        $stmt->bindParam(':response', $response);
        $stmt->execute();
        return (int)$conn->lastInsertId();
    }

    /**
     * Get chatbot by file ID
     */
    public function getChatBotByFile(int $fileId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM chatbot WHERE fileID = :fileID");
        $stmt->bindParam(':fileID', $fileId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all question chats for a specific chatbot
     */
    public function getQuestionChatByChatbot(int $chatbotId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM questionChat WHERE chatbotID = :chatbotID");
        $stmt->bindParam(':chatbotID', $chatbotId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get response chat by question chat ID
     */
    public function getResponseChatByQuestionChat(int $questionChatId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT response FROM responseChat WHERE questionChatID = :questionChatID");
        $stmt->bindParam(':questionChatID', $questionChatId);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Get response chat by ID
     */
    public function getResponseChatById(int $responseChatId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM responseChat WHERE responseChatID = :responseChatID");
        $stmt->bindParam(':responseChatID', $responseChatId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get chat history for a file
     */
    public function chatHistory($fileId, int $limit = 5)
    {
        $chatbot = $this->getChatBotByFile($fileId);
        
        if (!$chatbot || !isset($chatbot['chatbotID'])) {
            return [
                'questions' => [],
                'responseChats' => []
            ];
        }

        $chatbotId = $chatbot['chatbotID'];
        $questions = [];
        $responseChats = [];

        if ($chatbotId) {
            $questionChats = $this->getQuestionChatByChatbot($chatbotId);
            
            if ($questionChats && is_array($questionChats)) {
                // Sort by createdAt DESC to get latest first
                usort($questionChats, function($a, $b) {
                    $dateA = isset($a['createdAt']) ? strtotime($a['createdAt']) : (isset($a['questionChatID']) ? $a['questionChatID'] : 0);
                    $dateB = isset($b['createdAt']) ? strtotime($b['createdAt']) : (isset($b['questionChatID']) ? $b['questionChatID'] : 0);
                    return $dateB - $dateA; // DESC order
                });
                
                // Limit to the latest few chats
                $limitedChats = array_slice($questionChats, 0, $limit);
                
                foreach ($limitedChats as $questionChat) {
                    if (isset($questionChat['userQuestion'])) {
                        $questions[] = $questionChat['userQuestion'];
                    }
                    
                    $responseChat = $this->getResponseChatByQuestionChat($questionChat['questionChatID']);
                    if ($responseChat) {
                        $responseChats[] = $responseChat;
                    }
                }
            }
        }

        return [
            'questions' => $questions,
            'responseChats' => $responseChats
        ];
    }

    // ============================================================================ 
    // RAG UTILITY
    // ============================================================================ 

    /**
     * Split text into overlapping chunks for RAG processing
     * Prioritizes maximum chunk size threshold first, then finds nearest sentence boundary
     */
    public function splitTextIntoChunks(string $text, int $fileID, int $chunkSize = 2000, int $overlap = 400): array{
        $chunks = [];
        $length = strlen($text);
        $i = 0;
        
        while ($i < $length) {
            $chunkStart = $i;
            // First priority: set maximum end point based on chunk size threshold
            $maxEnd = min($i + $chunkSize, $length);
            
            // Second priority: look backwards from threshold to find nearest sentence boundary
            $sentenceEnd = false;
            
            // Search backwards from maxEnd to find the nearest sentence boundary
            $lookbackStart = $chunkStart;
            $searchText = substr($text, $lookbackStart, $maxEnd - $lookbackStart);
            
            // Find all sentence boundaries (., !, ?) in the search range
            $lastPeriod = strrpos($searchText, '.');
            $lastExclamation = strrpos($searchText, '!');
            $lastQuestion = strrpos($searchText, '?');
            
            // Get the latest (closest to threshold) sentence boundary
            $candidates = array_filter([$lastPeriod, $lastExclamation, $lastQuestion], function($pos) {
                return $pos !== false;
            });
            
            if (!empty($candidates)) {
                $boundaryPos = max($candidates); // Get the latest boundary (closest to threshold)
                $absoluteBoundaryPos = $chunkStart + $boundaryPos;
                $nextCharPos = $absoluteBoundaryPos + 1;
                
                // Verify it's followed by whitespace or is at end of text
                if ($nextCharPos >= $length) {
                    // End of text - valid sentence boundary
                    $sentenceEnd = $absoluteBoundaryPos + 1;
                } else {
                    $nextChar = $text[$nextCharPos];
                    // Check if followed by whitespace (space, tab, newline)
                    if (in_array($nextChar, [' ', "\t", "\n", "\r"])) {
                        $sentenceEnd = $absoluteBoundaryPos + 1;
                    }
                }
            }
            
            // Determine chunk end position
            if ($sentenceEnd !== false && $sentenceEnd > $chunkStart) {
                // Found a valid sentence boundary - use it
                $chunkEnd = $sentenceEnd;
            } else {
                // No sentence boundary found - use threshold or end of text
                $chunkEnd = $maxEnd;
            }
            
            // Extract chunk
            $chunk = substr($text, $chunkStart, $chunkEnd - $chunkStart);
            $chunk = trim($chunk);
            
            if (!empty($chunk)) {
                $chunks[] = $chunk;
            }
            
            // Move to next position with overlap
            if ($chunkEnd >= $length) {
                break; // Reached end of text
            }
            
            // Calculate overlap start - go back by overlap amount, but find nearest sentence boundary
            $overlapStart = max($chunkStart, $chunkEnd - $overlap);
            
            // Find sentence boundary before overlap start to ensure we start at sentence beginning
            if ($overlapStart > $chunkStart) {
                $overlapSearch = substr($text, $chunkStart, $overlapStart - $chunkStart);
                $lastSentenceBoundary = false;
                
                $lastPeriod = strrpos($overlapSearch, '.');
                $lastExclamation = strrpos($overlapSearch, '!');
                $lastQuestion = strrpos($overlapSearch, '?');
                
                $overlapCandidates = array_filter([$lastPeriod, $lastExclamation, $lastQuestion], function($pos) {
                    return $pos !== false;
                });
                
                if (!empty($overlapCandidates)) {
                    $boundaryInOverlap = max($overlapCandidates);
                    $nextCharPos = $chunkStart + $boundaryInOverlap + 1;
                    
                    if ($nextCharPos >= $length || in_array($text[$nextCharPos] ?? '', [' ', "\t", "\n", "\r"])) {
                        $lastSentenceBoundary = $chunkStart + $boundaryInOverlap + 1;
                    }
                }
                
                if ($lastSentenceBoundary !== false) {
                    $i = $lastSentenceBoundary;
                } else {
                    $i = $overlapStart;
                }
            } else {
                $i = $chunkEnd;
            }
        }
        
        return $chunks;
    }

    /**
     * Save text chunks and their embeddings to database for RAG retrieval
     */
    public function saveChunksToDB(array $chunks, array $embeddings, int $fileID): void{
        $conn = $this->db->connect();
        foreach ($chunks as $index => $chunk) {
            $stmt = $conn->prepare("INSERT INTO documentchunks (fileID, chunkText, embedding) VALUES (:fileID, :chunkText, :embedding)");
            $stmt->bindValue(':fileID', $fileID);
            $stmt->bindValue(':chunkText', $chunk);
            if(!empty($embeddings[$index])) {
                $stmt->bindValue(':embedding', json_encode($embeddings[$index]));
            } else {
                $stmt->bindValue(':embedding', '[]');
            }
            $stmt->execute();
        }
    }

    /**
     * Get all document chunks and embeddings for a specific file
     */
    public function getChunksByFile(int $fileID): array
    {
        try {
            $conn = $this->db->connect();
            $stmt = $conn->prepare("SELECT * FROM documentchunks WHERE fileID = :fileID");
            $stmt->bindParam(':fileID', $fileID);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Table doesn't exist or other database error - return empty array
            error_log('Error getting chunks: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all document chunks with file metadata for a user's files
     * Returns chunks with fileID, fileName, chunkText, and embedding
     */
    public function getChunksForUserFiles(int $userId): array
    {
        try {
            $conn = $this->db->connect();
            $query = "SELECT 
                        dc.documentChunkID,
                        dc.fileID,
                        dc.chunkText,
                        dc.embedding,
                        f.name as fileName
                      FROM documentchunks dc
                      INNER JOIN file f ON dc.fileID = f.fileID
                      WHERE f.userID = :userID
                      ORDER BY dc.fileID, dc.documentChunkID";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':userID', $userId, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Table doesn't exist or other database error - return empty array
            error_log('Error getting chunks for user files: ' . $e->getMessage());
            return [];
        }
    }

    // ============================================================================
    // HOMEWORK HELPER PAGE (homeworkHelper.php)
    // ============================================================================

    /**
     * Ensures homework_helper table exists in database
     */
    private function ensureHomeworkHelperSchema(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        try {
            $conn = $this->db->connect();
            
            // Check if table exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'homework_helper'");
            $stmt->execute();
            
            if ((int)$stmt->fetchColumn() === 0) {
                // Table doesn't exist, create it
                $conn->exec("
                    CREATE TABLE `homework_helper` (
                        `homeworkID` INT(11) NOT NULL AUTO_INCREMENT,
                        `userID` INT(11) NOT NULL,
                        `fileName` VARCHAR(255) NOT NULL,
                        `fileType` VARCHAR(50) NOT NULL,
                        `filePath` TEXT NOT NULL,
                        `extractedText` TEXT DEFAULT NULL,
                        `question` TEXT DEFAULT NULL,
                        `answer` TEXT DEFAULT NULL,
                        `status` ENUM('pending', 'processing', 'completed', 'no_question') DEFAULT 'pending',
                        `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        `updatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (`homeworkID`),
                        KEY `idx_userID` (`userID`),
                        KEY `idx_createdAt` (`createdAt`),
                        CONSTRAINT `homework_helper_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            }
        } catch (\Throwable $e) {
            error_log('Homework Helper schema ensure failed: ' . $e->getMessage());
        }

        $checked = true;
    }

    /**
     * Save homework helper entry
     */
    public function saveHomeworkHelper(int $userId, string $fileName, string $fileType, string $filePath, ?string $extractedText = null, ?string $question = null, ?string $answer = null, string $status = 'pending', ?string $instruction = null): int
    {
        $this->ensureHomeworkHelperSchema();
        $conn = $this->db->connect();
        $stmt = $conn->prepare("INSERT INTO homework_helper (userID, fileName, fileType, filePath, extractedText, question, answer, status, instruction) VALUES (:userID, :fileName, :fileType, :filePath, :extractedText, :question, :answer, :status, :instruction)");
        $stmt->bindParam(':userID', $userId, \PDO::PARAM_INT);
        $stmt->bindParam(':fileName', $fileName);
        $stmt->bindParam(':fileType', $fileType);
        $stmt->bindParam(':filePath', $filePath);
        $stmt->bindParam(':extractedText', $extractedText);
        $stmt->bindParam(':question', $question);
        $stmt->bindParam(':answer', $answer);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':instruction', $instruction);
        $stmt->execute();
        return (int)$conn->lastInsertId();
    }

    /**
     * Update homework helper entry
     */
    public function updateHomeworkHelper(int $homeworkId, ?string $extractedText = null, ?string $question = null, ?string $answer = null, ?string $status = null): bool
    {
        $conn = $this->db->connect();
        $updates = [];
        $params = [':homeworkID' => $homeworkId];

        if ($extractedText !== null) {
            $updates[] = "extractedText = :extractedText";
            $params[':extractedText'] = $extractedText;
        }
        if ($question !== null) {
            $updates[] = "question = :question";
            $params[':question'] = $question;
        }
        if ($answer !== null) {
            $updates[] = "answer = :answer";
            $params[':answer'] = $answer;
        }
        if ($status !== null) {
            $updates[] = "status = :status";
            $params[':status'] = $status;
        }

        if (empty($updates)) {
            return false;
        }

        $query = "UPDATE homework_helper SET " . implode(', ', $updates) . " WHERE homeworkID = :homeworkID";
        $stmt = $conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        return $stmt->execute();
    }

    /**
     * Get all homework helper entries for a user
     */
    public function getHomeworkHelpersByUser(int $userId): array
    {
        $this->ensureHomeworkHelperSchema();
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM homework_helper WHERE userID = :userID ORDER BY createdAt DESC");
        $stmt->bindParam(':userID', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get homework helper entry by ID
     */
    public function getHomeworkHelperById(int $homeworkId, int $userId): ?array
    {
        $this->ensureHomeworkHelperSchema();
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM homework_helper WHERE homeworkID = :homeworkID AND userID = :userID");
        $stmt->bindParam(':homeworkID', $homeworkId, \PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userId, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get storage client (for controller use)
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Get bucket name (for controller use)
     */
    public function getBucketName()
    {
        return $this->bucketName;
    }
}