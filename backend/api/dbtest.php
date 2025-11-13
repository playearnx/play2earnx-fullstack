<?php
header('Content-Type: application/json; charset=utf-8');

// Ambil variabel environment dari Render
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'play2earnx';

$response = [
    'endpoint' => '/api/dbtest',
    'server' => gethostname(),
    'database' => $dbName,
    'host' => $dbHost,
    'status' => '',
    'message' => ''
];

try {
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $query = $conn->query("SELECT NOW() as server_time");
    $row = $query->fetch_assoc();

    $response['status'] = 'connected';
    $response['message'] = '✅ Database connection successful!';
    $response['server_time'] = $row['server_time'];

    $conn->close();

} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
