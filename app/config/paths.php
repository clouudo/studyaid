<?php

namespace App\Config;

define('BASE_PATH', '/studyaid/');

define('DASHBOARD', BASE_PATH . 'user/dashboard');
define('LOGIN', BASE_PATH . 'auth/login');
define('REGISTER', BASE_PATH . 'auth/register');
define('HOME', BASE_PATH . 'auth/home');

// Public assets
define('PUBLIC_PATH', BASE_PATH . 'public/');
define('CSS_PATH', PUBLIC_PATH . 'css/');
define('ASSET_PATH', PUBLIC_PATH . 'asset/');

// Images
define('IMG_SETTING', ASSET_PATH . 'setting.png');
