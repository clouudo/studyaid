<?php

namespace App\Models;

use App\Config\Database;

class UserModel {

    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Add your methods here
}
