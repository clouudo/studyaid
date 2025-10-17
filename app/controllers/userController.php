<?php

class UserController {

    public function dashboard() {
        // Here you can load data for the dashboard and pass it to the view
        require_once 'app/views/dashboardView.php';
    }
}
