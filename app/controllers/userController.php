<?php

namespace App\Controllers;

use App\Models\LmModel;

class UserController {

    private $lmModel;

    public function __construct() {
        $this->lmModel = new LmModel();
    }
        

    public function dashboard() {
        if(!isset($_SESSION['user_id'])) {
            header('Location: ' . LOGIN);
            return;
        }
        $userId = $_SESSION['user_id'];
        $allUserFolders = $this->lmModel->getAllFoldersForUser($userId);
        // If the user is logged in, load the dashboard view
        require_once 'app/views/dashboardView.php';
    }
}
