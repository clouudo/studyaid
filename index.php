<?php

require_once 'autoloader.php';

// A simple router
$url = isset($_GET['url']) ? $_GET['url'] : 'auth/login';
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
