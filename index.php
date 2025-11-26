<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set custom session save path to avoid permission issues
$sessionPath = __DIR__ . '/temp/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
// Set permissions to allow web server to write
chmod($sessionPath, 0777);
// Set umask to allow write permissions for session files
umask(0000);
ini_set('session.save_path', $sessionPath);
session_start();

require_once 'autoloader.php';
require_once 'app/config/paths.php';

// A simple router
$url = isset($_GET['url']) ? $_GET['url'] : 'auth/home';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Get controller and method
$controllerName = !empty($url[0]) ? ucfirst($url[0]) . 'Controller' : 'AuthController';
$methodName = isset($url[1]) ? $url[1] : 'login';
$controllerClass = 'App\\Controllers\\' . $controllerName;

if (class_exists($controllerClass)) {
    $controller = new $controllerClass;

    if (method_exists($controller, $methodName)) {
        // Get parameters
        $params = array_slice($url, 2);
        call_user_func_array([$controller, $methodName], $params);
    } else {
        echo "Method not found: " . $methodName;
    }
} else {
    echo "Controller not found: " . $controllerName;
}

?>
