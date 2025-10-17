<?php

// A simple router
$url = isset($_GET['url']) ? $_GET['url'] : 'auth/login';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Get controller and method
$controllerName = !empty($url[0]) ? $url[0] . 'Controller' : 'authController';
$methodName = isset($url[1]) ? $url[1] : 'login';

// Include the controller file
$controllerFile = 'app/controllers/' . $controllerName . '.php';
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    // Instantiate the controller
    $controller = new $controllerName;

    // Call the method
    if (method_exists($controller, $methodName)) {
        // Get parameters
        $params = array_slice($url, 2);
        call_user_func_array([$controller, $methodName], $params);
    } else {
        echo "Method not found.";
    }
} else {
    echo "Controller not found.";
}

?>
