<?php
header('Content-Type: application/json; charset=utf-8');

$response = [
    'endpoint' => '/api/test',
    'message' => '🎯 API test endpoint works perfectly!',
    'timestamp' => time(),
    'server' => gethostname(),
    'php_version' => phpversion()
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
