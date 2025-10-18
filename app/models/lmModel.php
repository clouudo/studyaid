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

    public function generateUniqueFileName($fileExtension, $userId)
    {
        $uuid = Uuid::uuid4()->toString();
        $uniqueFileName = $uuid . '.' . $fileExtension;
        $gscObjectName = 'user_upload/' . $userId . '/' . $uniqueFileName;
        return $gscObjectName;
    }

    public function uploadFileToGCS($fileContent, $objectName, $userId)
    {
        $fileExtension = pathinfo($objectName, PATHINFO_EXTENSION);
        $uniqueFileName = $this->generateUniqueFileName($fileExtension, $userId);
        $bucket = $this->storage->bucket($this->bucketName);
        $object = $bucket->upload($fileContent, [
            'name' => $uniqueFileName
        ]);

        $conn = $this->db->connect();
        $query = "INSERT INTO file (userID, name, fileType, filePath) VALUES (:userID, :objectName, :fileExtension, :uniqueFileName)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userID', $userId);
        $stmt->bindParam(':objectName', $objectName);
        $stmt->bindParam(':fileExtension', $fileExtension);
        $stmt->bindParam(':uniqueFileName', $uniqueFileName);
        return $stmt->execute();
    }

    public function listUserFiles($userId){
        $conn = $this->db->connect();
        $query = "SELECT * FROM file WHERE userID = :userID";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userID', $userId);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getUserDocumentFromGCS($userId)
    {
        $bucket = $this->storage->bucket($this->bucketName);
        $prefix = 'user_upload/' . $userId . '/';
        $objects = $bucket->objects(['prefix' => $prefix]);
        $fileList = [];
        foreach ($objects as $object) {
            $fileList[] = $object->name();
        }
        return $fileList;
    }

    public function getDocumentContent($fileID, $userId)
    {
        $conn = $this->db->connect();
        $query = "SELECT filePath, fileType FROM file WHERE fileID = :fileID AND userID = :userID";
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

        $bucket = $this->storage->bucket($this->bucketName);
        $object = $bucket->object($gscObjectName);

        if (!$object->exists()) {
            throw new \Exception("File not found in Google Cloud Storage.");
        }

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

        if (in_array(strtolower($fileType), $imageExtensions)) {
            // For images, return the public URL
            return [
                'type' => 'image',
                'content' => $object->signedUrl(new \DateTime('+1 hour')) // Signed URL for temporary access
            ];
        } else {
            // For other file types, download as string
            return [
                'type' => 'text',
                'content' => $object->downloadAsString()
            ];
        }
    }
}
