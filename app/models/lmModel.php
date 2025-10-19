<?php

namespace App\Models;

use App\Config\Database;
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

    public function generateUniqueFileName($fileExtension, $userId)
    {
        $uuid = Uuid::uuid4()->toString();
        $uniqueFileName = $uuid . '.' . $fileExtension;
        $gscObjectName = 'user_upload/' . $userId . '/' . $uniqueFileName;
        return $gscObjectName;
    }

    public function uploadFileToGCS($fileContent, $objectName, $userId, $file, $extractedText)
    {
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uniqueFileName = $this->generateUniqueFileName($fileExtension, $userId);
        $bucket = $this->storage->bucket($this->bucketName);
        $bucket->upload($fileContent, [
            'name' => $uniqueFileName
        ]);

    $conn = $this->db->connect();
        $query = "INSERT INTO file (userID, name, fileType, filePath, extracted_text) VALUES (:userID, :objectName, :fileExtension, :uniqueFileName, :extractedText)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userID', $userId);
        $stmt->bindParam(':objectName', $objectName);
        $stmt->bindParam(':fileExtension', $fileExtension);
        $stmt->bindParam(':uniqueFileName', $uniqueFileName);
        $stmt->bindParam(':extractedText', $extractedText);
        $stmt->execute();
        return $conn->lastInsertId();
    }

    public function listUserFiles($userId){
        $conn = $this->db->connect();
        $query = "SELECT * FROM file WHERE userID = :userID";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getDocumentContent($fileID, $userId)
    {
        $conn = $this->db->connect();
        $query = "SELECT filePath, fileType, extracted_text FROM file WHERE fileID = :fileID AND userID = :userID";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':fileID', $fileID);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        $fileData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$fileData) {
            throw new \Exception("File not found or access denied.");
        }

        $gscObjectName = $fileData['filePath'];
        $fileType = $fileData['fileType'];
        $extractedText = $fileData['extracted_text'];

        $bucket = $this->storage->bucket($this->bucketName);
        $object = $bucket->object($gscObjectName);

        if (!$object->exists()) {
            throw new \Exception("File not found in Google Cloud Storage.");
        }

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

        if (in_array(strtolower($fileType), $imageExtensions)) {
            return [
                'type' => 'image',
                'content' => $object->signedUrl(new \DateTime('+1 hour')),
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

    public function deleteDocument($fileID, $userId)
    {
        $conn = $this->db->connect();
        $query = "SELECT filePath FROM file WHERE fileID = :fileID AND userID = :userID";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':fileID', $fileID);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        $fileData = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$fileData) {
            throw new \Exception("File not found or access denied.");
        }

        $gscObjectName = $fileData['filePath'];

        // Delete from GCS
        $bucket = $this->storage->bucket($this->bucketName);
        $object = $bucket->object($gscObjectName);

        if ($object->exists()) {
            $object->delete();
        }

        // Delete from database
        $deleteQuery = "DELETE FROM file WHERE fileID = :fileID AND userID = :userID";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bindParam(':fileID', $fileID);
        $deleteStmt->bindParam(':userID', $userId);
        return $deleteStmt->execute();
    }
}
