<?php
// backend/index.php
header('Content-Type: application/json; charset=utf-8');

// Ambil path dari URL
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Routing sederhana
switch ($path) {
    case '/':
        echo json_encode([
            'status' => 'ok',
            'message' => '✅ Play2EarnX Backend is running fine on Render!',
            'time' => date('Y-m-d H:i:s')
        ]);
        break;

    case '/api/test':
        require_once __DIR__ . '/api/test.php';
        break;
	case '/api/dbtest':
        require_once __DIR__ . '/api/dbtest.php';
        break;


    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not Found', 'path' => $path]);
        break;
}
?>
