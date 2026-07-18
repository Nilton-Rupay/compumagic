<?php
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$uri = trim($uri, '/');

if ($uri === '') {
    $target = __DIR__ . '/../proyecto/index.php';
} elseif (preg_match('#^admin(?:/|$)#', $uri)) {
    $relative = substr($uri, 6);
    $relative = ltrim($relative, '/');
    if ($relative === '' || $relative === 'index.php') {
        $target = __DIR__ . '/../proyecto/admin/index.php';
    } else {
        $target = __DIR__ . '/../proyecto/admin/' . $relative;
    }
} else {
    $target = __DIR__ . '/../proyecto/index.php';
}

$_SERVER['SCRIPT_NAME'] = '/' . $uri;
$_SERVER['PHP_SELF'] = '/' . $uri;

if (!file_exists($target)) {
    http_response_code(404);
    echo 'Página no encontrada';
    exit;
}

require $target;
