<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get the latest queue number for today
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT no_antrian, tanggal 
        FROM antrian 
        WHERE id_loket = ? AND DATE(tanggal) = ? 
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([$id_loket, $today]);
    $currentQueue = $stmt->fetch();
    
    if ($currentQueue) {
        echo json_encode([
            'success' => true,
            'currentNumber' => $currentQueue['no_antrian'],
            'timestamp' => $currentQueue['tanggal']
        ]);
    } else {
        // No queue for today yet, return default
        echo json_encode([
            'success' => true,
            'currentNumber' => 'F000',
            'timestamp' => null
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>