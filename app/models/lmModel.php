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

    public function generateUniqueFileName($fileExtension)
    {
        $uuid = Uuid::uuid4()->toString();
        $uniqueFileName = $uuid . '.' . $fileExtension;
        return $uniqueFileName;
    }

    public function uploadFileToGCS($fileContent, $userId, $folderId, $file, $extractedText, $originalFileName = null)
    {
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uniqueFileName = $this->generateUniqueFileName($fileExtension);

        // Ensure $folderId is truly null if it's 0 or empty string from form
        if (empty($folderId) || $folderId == 0) {
            $folderId = null;
        }

        $logicalFolderPath = '';
        if ($folderId !== null) {
            $logicalFolderPath = $this->getLogicalFolderPath($folderId);
        }

        $gcsObjectName = 'user_upload/' . $userId . '/content/' . $logicalFolderPath . $uniqueFileName;
        $fileName =  !empty($originalFileName) ? $originalFileName : $file['name'];

        $bucket = $this->storage->bucket($this->bucketName);
        $bucket->upload($fileContent, [
            'name' => $gcsObjectName
        ]);

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
                'content' => $object->signedUrl(new \DateTime('+1 hour'), ['version' => 'v4']),
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

    public function getFolderInfo($folderId)
    {
        $conn = $this->db->connect();
        $query = "SELECT folderID, name, parentFolderId FROM folder WHERE folderID = :folderID";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':folderID', $folderId);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

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

        //GCS Delete Folder
        $bucket = $this->storage->bucket($this->bucketName);
        $logicalFolderPath = $this->getLogicalFolderPath($folderId);

        error_log("Folder ID to delete: " . $folderId);
        error_log("Logical folder path: " . $logicalFolderPath);

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
            error_log("Parent Folder Info: " . print_r($parentFolderInfo, true));
            if (!$parentFolderInfo) {
                throw new \Exception("Parent folder not found or access denied.");
            }

            $logicalPath = $this->getLogicalFolderPath($parentFolderId);
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

    public function getLogicalFolderPath($folderId)
    {
        $conn = $this->db->connect();
        $path = [];
        $currentFolderId = $folderId;

        while ($currentFolderId !== null) {
            $query = "SELECT folderID, name, parentFolderId FROM folder WHERE folderID = :folderID";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':folderID', $currentFolderId);
            $stmt->execute();
            $folder = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($folder) {
                array_unshift($path, $folder['name']); // Add to the beginning of the array
                $currentFolderId = $folder['parentFolderId'];
            } else {
                $currentFolderId = null; // Folder not found, stop
            }
        }
        return implode('/', $path) . '/'; // Return path like "RootFolder/SubFolder/TargetFolder/"
    }
}
