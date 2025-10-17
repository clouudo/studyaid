<?php

namespace App\Controllers;

class UserController {

        

    public function dashboard() {
        if(!isset($_SESSION['user_id'])) {
            header('Location: ' . LOGIN);
            return;
        }
        // If the user is logged in, load the dashboard view
        require_once 'app/views/dashboardView.php';
    }
}
