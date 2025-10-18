<?php

namespace App\Controllers;

use App\Models\LmModel;

class LmController
{

    private $lmModel;
    public function __construct()
    {
        $this->lmModel = new LmModel();
    }

    public function uploadDocument()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {

            //Must have a file to upload
            if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                echo "<div class=\"alert alert-danger\">Missing Information. Please try again.</div>";
                require_once 'app\views\learningView\newDocument.php';
                return;
            }

            $userId = 'guest';
            if (isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
            }

            $file = $_FILES['document'];
            $originalFileName = !empty($_POST['documentName']) ? $_POST['documentName'] : $_FILES['document']['name'];
            $fileContent = file_get_contents($file['tmp_name']);

            if ($fileContent !== false) {
                try {
                    $this->lmModel->uploadFileToGCS($fileContent, $originalFileName, $userId);
                    $message = "File '{$originalFileName}' uploaded successfully to Google Cloud Storage.";
                    $messageType = 'success';
                } catch (\Exception $e) {
                    $message = "Error uploading file: " . $e->getMessage();
                    $messageType = 'danger';
                }
            } else {
                $message = "Error reading uploaded file.";
                $messageType = 'danger';
            }

            echo "<div class=\"alert alert-{$messageType}\">{$message}</div>";
        }
        require_once 'app\views\learningView\newDocument.php';
    }
}
