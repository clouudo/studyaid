<?php

namespace App\Models;

use App\Config\Database;

class LmModel {

    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Add your methods here
}
