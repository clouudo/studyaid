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
}
