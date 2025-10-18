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
            $uploadedFileName = $file['name']; // Original name with extension

            $documentNameFromPost = $_POST['documentName'] ?? '';

            if (!empty($documentNameFromPost)) {
                // User provided a custom name, append original extension
                $originalFileName = $documentNameFromPost;
            } else {
                // Use the original uploaded file name
                $originalFileName = $uploadedFileName;
            }

            $fileContent = file_get_contents($file['tmp_name']);

            if ($fileContent !== false) {
                try {
                    $this->lmModel->uploadFileToGCS($fileContent, $originalFileName, $userId, $file);
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

    public function allDocument()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth/home');
            exit();
        }

        // Fetch documents from Google Cloud Storage
        $fileList = $this->lmModel->listUserFiles($_SESSION['user_id']);

        // Pass documents to the view
        require_once 'app\views\learningView\allDocument.php';
    }

    public function displayDocument()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth/home');
            exit();
        }

        if (isset($_GET['fileID'])) {
            $fileID = $_GET['fileID'];
            try {
                $documentData = $this->lmModel->getDocumentContent($fileID, $_SESSION['user_id']);
                // Pass documentData to the view
                require_once 'app/views/learningView/displayDocument.php';
            } catch (\Exception $e) {
                // Handle error, e.g., document not found or access denied
                // Redirect or show an error page
                header('Location: index.php?url=lm/allDocument');
                exit();
            }
        } else {
            // If fileID is not provided, redirect to all documents page
            header('Location: index.php?url=lm/allDocument');
            exit();
        }
    }

    public function deleteDocument(){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=auth/home');
            exit();
        }

        if (isset($_GET['fileID'])) {
            $fileID = $_GET['fileID'];
            try {
                if ($this->lmModel->deleteDocument($fileID, $_SESSION['user_id'])) {
                    $_SESSION['message'] = "Document deleted successfully.";
                } else {
                    $_SESSION['error'] = "Failed to delete document.";
                }
            } catch (\Exception $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
            }
        }
        header('Location: index.php?url=lm/allDocument');
        exit();
    }
}
