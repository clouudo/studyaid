<?php

namespace App\Models;

use App\Config\Database;
use Error;
use Google\Cloud\Storage\StorageClient;
use Ramsey\Uuid\Uuid;

class LmModel
{

    private $db;
    private $storage;
    private $bucketName;

    public function __construct()
    {
        $this->db = new Database();
        $config = require __DIR__ . '/../config/cloud_storage.php';

        $this->storage = new StorageClient([
            'keyFilePath' => $config['key_file_path']
        ]);
        $this->bucketName = $config['bucket_name'];
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
     */
    public function getLogicalFolderPath($folderId, $userId)
    {
        $conn = $this->db->connect();
        $path = [];
        $currentFolderId = $folderId;

        while ($currentFolderId !== null) {
            $query = "SELECT folderID, name, parentFolderId FROM folder WHERE folderID = :folderID AND userID = :userID";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':folderID', $currentFolderId);
            $stmt->bindParam(':userID', $userId);
            $stmt->execute();
            $folder = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($folder) {
                array_unshift($path, $folder['name']);
                $currentFolderId = $folder['parentFolderId'];
            } else {
                throw new \Exception("Folder hierarchy broken or access denied.");
            }
        }
        return implode('/', $path) . '/';
    }

    /**
     * Get folder information by ID
     */
    public function getFolderInfo($folderId)
    {
        $conn = $this->db->connect();
        $query = "SELECT folderID, name, parentFolderId FROM folder WHERE folderID = :folderID";
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
     * Upload file to Google Cloud Storage and save metadata to database
     */
    public function uploadFileToGCS($userId, $folderId, $extractedText, $fileContent = null, $file = null, $originalFileName = null)
    {
        if($file != null) {
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

        if($fileContent != null) { //If file is uploaded, upload to GCS 
            $options = [
                'name' => $gcsObjectName,
                'metadata' => ['contentType' => 'text/plain']
            ];
        }else{
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
        $folderQuery = "SELECT folderID, name, parentFolderId FROM folder WHERE userID = :userID AND parentFolderId " . ($parentId === null ? "IS NULL" : "= :parentFolderId");
        $folderStmt = $conn->prepare($folderQuery);
        $folderStmt->bindParam(':userID', $userId);
        if ($parentId !== null) {
            $folderStmt->bindParam(':parentFolderId', $parentId);
        }
        $folderStmt->execute();
        $results['folders'] = $folderStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get files
        $fileQuery = "SELECT fileID, name, fileType FROM file WHERE userID = :userID AND folderID " . ($parentId === null ? "IS NULL" : "= :folderID");
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

        $bucket = $this->storage->bucket($this->bucketName);
        $logicalFolderPath = $this->getLogicalFolderPath($folderId, $userId);


        $prefix = 'user_upload/' . $userId . '/content/' . $logicalFolderPath;
        
        $objects = $bucket->objects(['prefix' => $prefix]);
        // Delete all objects under the folder
        foreach($objects as $obj){
            $obj->delete();
        }

        // Delete the folder
        $deleteQuery = "DELETE FROM folder WHERE folderID = :folderID";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bindParam(':folderID', $folderId);
        return $deleteStmt->execute();
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
        $folderInfoQuery = "SELECT name, parentFolderId FROM folder WHERE folderID = :folderID AND userID = :userID";
        $folderInfoStmt = $conn->prepare($folderInfoQuery);
        $folderInfoStmt->bindParam(':folderID', $folderId);
        $folderInfoStmt->bindParam(':userID', $userId);
        $folderInfoStmt->execute();
        $folderInfo = $folderInfoStmt->fetch(\PDO::FETCH_ASSOC);

        if (!$folderInfo) {
            throw new \Exception("Could not retrieve folder information for path construction.");
        }

        // Build parent folder path
        $parentFolderPath = '';
        if ($folderInfo['parentFolderId'] !== null) {
            $parentFolderPath = $this->getLogicalFolderPath($folderInfo['parentFolderId'], $userId);
        }

        // Construct old and new prefixes for GCS objects
        $oldLogicalPath = $parentFolderPath . $folderInfo['name'] . '/';
        $oldPrefix = 'user_upload/' . $userId . '/content/' . $oldLogicalPath;
        $newPrefix = 'user_upload/' . $userId . '/content/' . $parentFolderPath . $newName . '/';

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
        return $updateStmt->execute();
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
        
        $oldParentPath = '';
        if ($folderInfo['parentFolderId'] !== null) {
            $oldParentPath = $this->getLogicalFolderPath($folderInfo['parentFolderId'], $userId);
        }
        $oldPrefix = 'user_upload/' . $userId . '/content/' . $oldParentPath . $folderInfo['name'] . '/';

        // Get new path information
        $newParentPath = '';
        if ($newParentId !== null) {
            $newParentPath = $this->getLogicalFolderPath($newParentId, $userId);
        }
        $newPrefix = 'user_upload/' . $userId . '/content/' . $newParentPath . $folderInfo['name'] . '/';

        // Move all objects in GCS
        $bucket = $this->storage->bucket($this->bucketName);
        $objects = $bucket->objects(['prefix' => $oldPrefix]);
        foreach ($objects as $object) {
            $newObjectName = str_replace($oldPrefix, $newPrefix, $object->name());
            $object->copy($bucket, ['name' => $newObjectName]);
            $object->delete();
        }

        // Update database
        $updateQuery = "UPDATE folder SET parentFolderId = :newParentId WHERE folderID = :folderID AND userID = :userID";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindParam(':newParentId', $newParentId, $newParentId === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
        $updateStmt->bindParam(':folderID', $folderId);
        $updateStmt->bindParam(':userID', $userId);
        
        return $updateStmt->execute();
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

        if ($parentFolderId == null) {
            $gcsFolderName = 'user_upload/' . $userId . '/content/' . $folderName . '/';

            if(!$bucket->object($gcsFolderName)->exists()) {
                $bucket->upload('', ['name' => $gcsFolderName]);
            }
        }

        if($parentFolderId != null) {
            $parentFolderInfo = $this->getFolderInfo($parentFolderId);
            if (!$parentFolderInfo) {
                throw new \Exception("Parent folder not found or access denied.");
            }

            $logicalPath = $this->getLogicalFolderPath($parentFolderId, $userId);
            $gcsFolderName = 'user_upload/' . $userId . '/content/' . $logicalPath . $folderName . '/';

            if(!$bucket->object($gcsFolderName)->exists()) {
                $bucket->upload('', ['name' => $gcsFolderName]);
            }
        }

        $conn = $this->db->connect();
        $query = "INSERT INTO folder (userID, parentFolderId, name) VALUES (:userID, :parentFolderId, :name)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userID', $userId);
        $stmt->bindParam(':parentFolderId', $parentFolderId);
        $stmt->bindParam(':name', $folderName);
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
     * Get a specific summary by ID and user ID
     */
    public function getSummaryById(int $summaryId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM summary WHERE summaryID = :summaryID");
        $stmt->bindParam(':summaryID', $summaryId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

     /**
     * Delete summary from database
     */
    public function deleteSummary(int $summaryId){
        $conn = $this->db->connect();
        $stmt = $conn->prepare("DELETE FROM summary WHERE summaryID = :summaryID");
        $stmt->bindParam(':summaryID', $summaryId);
        return $stmt->execute();
    }

     /**
     * Save summary as file from database and upload to GCS
     */
    public function saveSummaryAsFile(int $summaryId, $filePath, $folderId){
        $conn = $this->db->connect();
        $summary = $conn->prepare("SELECT * FROM summary WHERE summaryID = :summaryID");
        $summary->bindParam(':summaryID', $summaryId);
        $summary->execute();
        $summaryData = $summary->fetch(\PDO::FETCH_ASSOC);

        $sourceText = $summaryData['content'];
        $title = $summaryData['title'];

        //Upload to GCS
        $this->uploadFileToGCS($folderId, $sourceText, null, null, $title);
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
     * Get a specific note by ID and user ID
     */
    public function getNoteById(int $noteId, int $userId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM note WHERE noteID = :noteID AND userID = :userID");
        $stmt->bindParam(':noteID', $noteId);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

     /**
     * Delete note from database
     */
    public function deleteNote(int $noteId){
        $conn = $this->db->connect();
        $stmt = $conn->prepare("DELETE FROM note WHERE noteID = :noteID");
        $stmt->bindParam(':noteID', $noteId);
        return $stmt->execute();
    }

     /**
     * Save note as file from database and upload to GCS
     */
    public function saveNoteAsFile(int $noteId, $filePath, $folderId){
        $conn = $this->db->connect();
        $note = $conn->prepare("SELECT * FROM note WHERE noteID = :noteID");
        $note->bindParam(':noteID', $noteId);
        $note->execute();
        $noteData = $note->fetch(\PDO::FETCH_ASSOC);

        $sourceText = $noteData['content'];
        $title = $noteData['title'];
        $userId = $noteData['userID'];

        //Upload to GCS
        $this->uploadFileToGCS($userId, $folderId, $sourceText, null, null, $title);
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
     * Get a specific mindmap by ID and file ID
     */
    public function getMindmapById(int $mindmapId, int $fileId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM mindmap WHERE mindmapID = :mindmapID AND fileID = :fileID");
        $stmt->bindParam(':mindmapID', $mindmapId);
        $stmt->bindParam(':fileID', $fileId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

     /**
     * Delete mindmap from database
     */
    public function deleteMindmap(int $mindmapId){
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
    public function saveFlashcards(int $fileId, string $title, string $term, string $definition): int
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("INSERT INTO flashcard (fileID, title, term, definition) VALUES (:fileID, :title, :term, :definition)");    
        $stmt->bindParam(':fileID', $fileId);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':term', $term);
        $stmt->bindParam(':definition', $definition);
        $stmt->execute();
        return (int)$conn->lastInsertId();
    }

    /**
     * Get all flashcards for a specific file
     */
    public function getFlashcardsByFile(int $fileId)
    {
        $conn = $this->db->connect();
        $stmt = $conn->prepare("SELECT * FROM flashcard WHERE fileID = :fileID ORDER BY createdAt DESC");
        $stmt->bindParam(':fileID', $fileId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
}
